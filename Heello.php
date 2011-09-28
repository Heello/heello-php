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
 
include dirname(__FILE__) . 'includes/HeelloConfig.php';
include dirname(__FILE__) . 'includes/HeelloAPI.php';
include dirname(__FILE__) . 'includes/HeelloAPIException.php';

require_once 'HTTP/Request2.php';

class Heello {
	const API_DOMAIN = "api.heello.com";
	const API_VERSION = 1;
	
	private $api;
	private static $config;
	
	public function __construct($client_id = null, $client_secret = null, $redirect_url = null) {
		self::$config = new HeelloConfig($client_id, $client_secret);
		if (!empty($redirect_url)) {
			self::$config->set_redirect_url($redirect_url);
		}
		
		$this->api = new HeelloAPI();
	}
	
	public function config() { return self::$config; }
	
	public function get_authorization_url($display = 'full') {
		if ($this->config->mode() == HeelloConfig::NOCREDS) {
			throw new HeelloAPIException("Client ID required to build authorization URL");
		}
		
		$url = "https://" . self::API_DOMAIN;
		$url .= "/" . self::API_VERSION;
		$url .= "/oauth/authorize?";
		
		$client = $this->config->get_client();
		$params = array(
			'client_id' => $client->id,
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
	
	/**
	 * Throw the request over to the API class to handle
	 */
	public function __get($key) {
		return $this->api->{$key};
	}
}