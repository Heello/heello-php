<?
/*
 * Heello API for PHP
 * Copyright 2011 Ryan LeFevre - @meltingice
 * PHP version 5.3.0+
 *
 * Licensed under the New BSD License, more info in LICENSE file
 * included with this software.
 *
 * Source code is hosted at http://github.com/Heello/heello-php
 */ 

namespace Heello;

require_once 'HTTP/Request2.php';

include dirname(__FILE__) . '/includes/API.php';
include dirname(__FILE__) . '/includes/APIException.php';
include dirname(__FILE__) . '/includes/Config.php';
include dirname(__FILE__) . '/includes/Request.php';
include dirname(__FILE__) . '/includes/Util.php';

class Client {
	private $api;
	private static $config;
	private $me;
	
	public function __construct($client_id = null, $client_secret = null, $redirect_url = null) {
		self::$config = new Config($client_id, $client_secret);
		if (!empty($redirect_url)) {
			self::$config->set_redirect_url($redirect_url);
		}
		
		$this->api = new API(self::$config);
	}
	
	public function config() { return self::$config; }
	
	public function get_authorization_url($display = 'full') {
		if ($this->config->mode() == Config::NOCREDS) {
			throw new APIException("Client ID required to build authorization URL");
		}
		
		$url = $this->api->get_request_url_base();
		$url .= "/oauth/authorize?";
		
		$client = $this->config->get_client();
		$params = array(
			'client_id' => $client['id'],
			'response_type' => 'code',
			'redirect_uri' => self::$config->get_redirect_url(),
			'state' => self::$config->get_state(),
			'display' => $display
		);
		
		$url .= http_build_query($params);
		
		return $url;
	}
	
	public function authorization_redirect() {
		header('Location: ' . $this->get_authorization_url()); exit;
	}
	
	public function finish_authorization() {
		if ($this->has_auth_error()) {
			throw new APIException(Util::gpval('error'));
		}
		
		// If we have a valid state...
		if ($this->has_valid_state()) {
			// ... then retrieve the user's access token.
			$access_token = $this->get_access_token();
			self::$config->set_access_token($access_token);
		}
	}
	
	public function me() {
		if (empty($this->me)) {
			$this->me = $this->api->me();	
		}
		
		return $this->me;
	}
	
	private function has_auth_error() {
		return Util::gpval('error', false);
	}
	
	private function has_valid_state() {
		return Util::gpval('state') == self::$config->get_state();
	}
	
	private function get_access_token() {
		if (!self::$config->get_access_token()) {
			return self::$config->get_access_token();	
		}
		
		$request = $this->prepare_token_request();
		
		try {
			$response = $request->send();

			if ($response->getStatus() == 200) {
				$token_info = json_decode($response->getBody());
				return $token_info->access_token;
			} else {
				throw new APIException('Unexpected HTTP status: ' . $response->getStatus() . ' ' . $response->getReasonPhrase());
			}
		} catch (HTTP_Request2_Exception $e) {
				throw new APIException('Error: ' . $e->getMessage());
		}
	}
	
	private function prepare_token_request(){
		$request = new Request(Request::POST, '/oauth/token');

		$client = self::$config->get_client();
		$request->addParameter("client_id", $client['id']);
		$request->addParameter("client_secret", $client['secret']);
		$request->addParameter("redirect_uri", self::$config->get_redirect_url());
		$request->addParameter("code", Util::gpval('code'));
		$request->addParameter("response_type", 'token');
		$request->addParameter("grant_type", 'authorization_code');

		return $request;
	}
	
	/**
	 * Throw the request over to the API class to handle
	 */
	public function __get($key) {
		return $this->api->{$key};
	}
}