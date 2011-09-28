<?
/*
 * TwitPic API for PHP
 * Copyright 2010 Ryan LeFevre - @meltingice
 * PHP version 5.3.0+
 *
 * Licensed under the New BSD License, more info in LICENSE file
 * included with this software.
 *
 * Source code is hosted at http://github.com/meltingice/TwitPic-API-for-PHP
 */
 
class HeelloAPI {
	private $api, $config;
	private $category = null;
	private $method = null;
	private $format = null;
	
	public function __construct(HeelloConfig $config) {
		$this->config = $config;
		
		$xmlApi = simplexml_load_file(dirname(__FILE__) . '../api/api.xml');
		foreach ($xmlApi->category as $category) {
			$catName = (string)$category['name'];
			$this->api[$catName] = array();
			foreach ($category->endpoint as $endpoint) {
				$this->api[$catName][(string)$endpoint['name']] = $endpoint;
			}
		}
	}
	
	/*
	 * Executes an API call only after the category and method
	 * have been validated.
	 */
	private function execute($args) {
		if(count($args) >= 1) {
			$method_args = array_shift($args);
		} else {
			$method_args = array();
		}
		
		$this->options = $args[0];
		if($this->api_call()->attributes()->method == 'POST') {
			return $this->executePOST($method_args);
		} else { // assume GET?
			return $this->executeGET($method_args);	
		}
	}
	
	/*
	 * Performs a GET API method.
	 */
	private function executeGET($method_args) {
		$this->validate_args($method_args);

		$this->format = $this->get_format();
		
		$url = $this->get_request_url();
		$args = http_build_query($method_args);
		$url .= "?$args";
		
		$r = new Http_Request2($url, Http_Request2::METHOD_GET);
		$res = $r->send();
		
		if($res->getStatus() == 200) {
			return $this->respond($res->getBody());
		} else {
			throw new HeelloAPIException($res->getBody());
		}
	}
	
	/*
	 * Performs a POST API method
	 */
	private function executePOST($method_args) {
		$this->validate_args($method_args);
		$this->check_for_authentication();
		$header = $this->build_header();
		
		$url = $this->get_request_url();
		$r = new Http_Request2($url, Http_Request2::METHOD_POST);
		$r->setHeader('X-Verify-Credentials-Authorization', $header);
		$r->addPostParameter('key', TwitPic_Config::getAPIKey());
		foreach($method_args as $arg=>$val) {
			$r->addPostParameter($arg, $val);
		}
		
		$res = $r->send();
		if($res->getStatus() == 200) {
			if(strlen($res->getBody() > 0)) {
				return $this->respond($res->getBody());
			} else {
				return true;
			}
		} else {
			throw new TwitPicAPIException($res->getBody());
		}
	}
	
	/*
	 * Validates all of the arguments given to the API
	 * call and makes sure that required args are present.
	 */
	private function validate_args($args) {
		$api_call = $this->api_call();
		foreach($api_call->param as $param) {
			$attrs = $param->attributes();
			if(isset($attrs->required)) {
				if($attrs->required == 'true' && !array_key_exists((string)$attrs->name, $args)) {
					throw new TwitPicAPIException("Missing required parameter '{$attrs->name}' for {$this->category}/{$this->method}");
				}
			}
		}
	}
	
	/*
	 * Checks to see if this API call requires authentication,
	 * and if we have all the required info.
	 */
	private function check_for_authentication() {
		/* if auth_required is not set, assume false */
		if(!isset($this->api_call()->attributes()->auth_required)){ return; }
		
		if($this->api_call()->attributes()->auth_required == 'true' && TwitPic::mode() == TwitPic_Config::MODE_READONLY){
			throw new TwitPicAPIException("API call {$this->category}/{$this->method} requires an API key and OAuth credentials");
		}
	}
	
	/*
	 * When making an authenticated API call, we need
	 * to build the header that is sent with the authorization
	 * information.
	 */
	private function build_header($tweet=false) {
		$consumer = TwitPic_Config::getConsumer();
		$oauth = TwitPic_Config::getOAuth();
		
		$signature = HTTP_OAuth_Signature::factory('HMAC_SHA1');
		$timestamp = gmdate('U');
		$nonce = uniqid();
		$version = '1.0';
		
		if(is_string($tweet)) {
			$params = array( 'oauth_consumer_key' => $consumer['key'] ,
				'oauth_signature_method' => 'HMAC-SHA1' ,
				'oauth_token' =>  $oauth['token'],
				'oauth_timestamp' => $timestamp ,
				'oauth_nonce' => $nonce ,
				'oauth_version' => $version,
				'status' => $tweet);
	
			$sig_text = $signature->build( 'POST',"http://api.twitter.com/1/statuses/update.{$this->format}", $params, $consumer['secret'], $oauth['secret'] );
	
			$params['oauth_signature'] = $sig_text;
		} else {
			$params = array( 'oauth_consumer_key' => $consumer['key'] ,
				'oauth_signature_method' => 'HMAC-SHA1' ,
				'oauth_token' =>  $oauth['token'],
				'oauth_timestamp' => $timestamp ,
				'oauth_nonce' => $nonce ,
				'oauth_version' => $version );
	
			$sig_text = $signature->build( 'GET','https://api.twitter.com/1/account/verify_credentials.json', $params, $consumer['secret'], $oauth['secret'] );
	
			$params['oauth_signature'] = $sig_text;
		}
		

		$realm = 'http://api.twitter.com/';
		$header = 'OAuth realm="' . $realm . '"';
		foreach ($params as $name => $value) {
			$header .= ", " . HTTP_OAuth::urlencode($name) . '="' . HTTP_OAuth::urlencode($value) . '"';
		}

		return $header;
	}
	
	/*
	 * Builds the TwitPic API request URL
	 */
	private function get_request_url() {
		$this->format = $this->get_format();
		return "http://api.twitpic.com/2/{$this->category}/{$this->method}.json";
	}
	
	/*
	 * Processes the API response and converts it to
	 * an object if requested (default). The response
	 * can be forced to be returned in its raw format using
	 * the 'process' argument.
	 */
	private function respond($data) {
		if((isset($this->options['process']) && $this->options['process'] == true) || !isset($this->options['process'])) {
			if($this->format == 'json') {
				$data = json_decode($data);
			} elseif($this->format == 'xml') {
				$data = simplexml_load_string($data);
			}
		}
		
		$this->reset_settings();
		
		return $data;
	}
	
	private function truncate($msg) {
		return mb_substr($msg, 0, 114, 'UTF-8');
	}
	
	/*
	 * Resets the variables in this class to avoid
	 * any conflicts if multiple API calls are made.
	 */
	 private function reset_settings() {
	 	$this->category = null;
	 	$this->method = null;
	 	$this->format = null;
	 	$this->options = null;
	 }
	
	/*
	 * Gets the current API call
	 */
	private function api_call() {
		return $this->api[$this->category][$this->method];
	}
	
	/*
	 * Checks to make sure the API category in use
	 * is valid and returns $this.
	 */
	private function api_category($category) {
		if(!isset($this->api[$category])) {
			throw new TwitPicAPIException('API category not found');
		}
		
		$this->category = $category;
		
		return $this;
	}
	
	/*
	 * Checks to make sure the API method for this category
	 * is defined and begins the execution of the API call.
	 */
	private function api_method($method, $args) {
		if($method == 'upload') { // need to make an exception for upload
			return $this->upload_photo($args);
		} elseif(is_null($this->category)) {
			throw new TwitPicAPIException('WTF is goin on yo?');
		} elseif(!isset($this->api[$this->category][$method])) {
			throw new TwitPicAPIException("API method not found for category {$this->category}");
		}
		
		$this->method = $method;
		
		return $this->execute($args);
	}
	
	/*
	 * This function handles the API category that is
	 * being called and if valid, returns $this so that
	 * the __call() function can be called next.
	 */
	public function __get($key) {
		return $this->api_category($key);
	}
	
	/*
	 * The __call() function handles accessing the API
	 * method.
	 */
	public function __call($method, $args) {
		return $this->api_method($method, $args);
	}
	
}
