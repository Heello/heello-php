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

namespace Heello;

class API {
	const SECURE = true;
	const DOMAIN = "api.heello.com";
	const VERSION = 1;

	private static $API_PATH;

	private $api = array();
	private $category = null;
	private $method = null;
	private $format = null;

	public function __construct() {
		self::$API_PATH = dirname(__FILE__) . '/../api/api.json';

		$jsonApi = json_decode(file_get_contents(self::$API_PATH));
		foreach ($jsonApi as $endpoint => $actions) {
			$this->api[$endpoint] = array();
			foreach ($actions as $action => $options) {
				$this->api[$endpoint][$action] = $options;
			}
		}
	}

	/**
	 * This is a special API request because it doesn't follow the standard
	 * REST format and is usually called immediately after authorization
	 * finishes. This value is also cached in Heello\Client
	 */
	public function me() {
		$request = new Request(Request::GET, 'me');
		return $request->send(true);
	}

	/*
	 * Executes an API call only after the category and method
	 * have been validated.
	 */
	private function execute($method_args) {
		$this->validate_args($method_args);

		$options = $this->endpoint_options();
		$request = null;

		if ($options->method == 'POST') {
			$request = new Request(Request::POST, $this->get_request_url($method_args));
		} else { // assume GET?
			$request = new Request(Request::GET, $this->get_request_url($method_args));
		}

		$request->addParameters($method_args);
		
		$response = null;
		try {
			$response = $request->send($this->requires_authentication());
		} catch (ExpiredAccessTokenException $e) {
			$this->refresh_access_token();
			$response = $request->send($this->requires_authentication());
		}

		$this->reset_parameters();
		return $response;
	}
	
	private function refresh_access_token() {
		$request = new Request(Request::POST, '/oauth/token');
		$client = Client::config()->get_client();
		$token = Client::config()->get_tokens();
		
		$request->addParameter('client_id', $client['id']);
		$request->addParameter('client_secret', $client['secret']);
		$request->addParameter('redirect_uri', Client::config()->get_redirect_url());
		$request->addParameter('response_type', 'token');
		$request->addParameter('refresh_token', $token['refresh']);
		$request->addParameter('grant_type', 'refresh_token');
		$response = $request->send();
		
		Client::config()->set_access_token($response->access_token);
		Client::config()->set_refresh_token($response->refresh_token);
		
		Client::config()->execute_refresh_token_callback();
	}

	/*
	 * Validates all of the arguments given to the API
	 * call and makes sure that required args are present.
	 */
	private function validate_args($args) {
		$options = $this->endpoint_options();
		if (!isset($options->required_params)) return;

		foreach ($options->required_params as $param) {
			if (!array_key_exists($param, $args)) {
				throw new APIException("Missing required parameter '{$param}' for {$this->category}/{$this->method}");
			}
		}
	}

	/*
	 * Checks to see if this API call requires authentication,
	 * and if we have all the required info.
	 */
	private function requires_authentication() {
		$options = $this->endpoint_options();

		/* if auth_required is not set, assume false */
		if (!isset($options->requires_auth)) return false;
		elseif ($options->requires_auth == false) return false;
		if (Client::config()->mode() != Config::READWRITE) {
			throw new APIException("API call {$this->category}/{$this->method} requires authentication.");
		}

		return true;
	}

	/*
	 * Builds the TwitPic API request URL
	 */
	private function get_request_url($args) {
		$options = $this->endpoint_options();
		if (!isset($options->url)) {
			return "/{$this->category}/{$this->method}";
		}

		$url = "{$this->category}/{$options->url}";
		if (strpos($url, ":") !== false) {
			$url = preg_replace_callback('/(:[A-Za-z]+)/', function ($matches) use ($args) {
					$argname = substr($matches[0], 1);
					return $args[$argname];
				}, $url);
		}

		return $url;
	}

	private function truncate($msg) {
		return mb_substr($msg, 0, self::MAX_PING_LENGTH, 'UTF-8');
	}

	private function reset_parameters() {
		$this->category = null;
		$this->method = null;
	}

	/*
	 * Gets the current API call
	 */
	private function endpoint_options() {
		return $this->api[$this->category][$this->method];
	}

	/*
	 * Checks to make sure the API category in use
	 * is valid and returns $this.
	 */
	private function api_category($category) {
		if (!isset($this->api[$category])) {
			throw new APIException('API category not found');
		}

		$this->category = $category;
		return $this;
	}

	/*
	 * Checks to make sure the API method for this category
	 * is defined and begins the execution of the API call.
	 */
	private function api_method($method, $args) {
		if (is_null($this->category)) {
			throw new APIException('Error');
		} elseif (!isset($this->api[$this->category][$method])) {
			throw new APIException("API method not found for {$this->category} endpoint.");
		}

		$this->method = $method;
		if (count($args) > 0) $args = $args[0];
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
