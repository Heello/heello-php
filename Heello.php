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

	public static function config() { return self::$config; }

	public function me() {
		if (empty($this->me)) {
			$this->me = $this->api->users->me();
		}

		return $this->me;
	}

	public function get_tokens() {
		if (self::$config->get_tokens()) {
			return self::$config->get_tokens();
		}

		$request = $this->prepare_token_request();
		$response = $request->send();

		self::$config->set_access_token($response->access_token);
		self::$config->set_refresh_token($response->refresh_token);

		return self::$config->get_tokens();
		}

	/**
	 * Throw the request over to the API class to handle
	 */
	public function __get($key) {
		return $this->api->{$key};
	}
}
