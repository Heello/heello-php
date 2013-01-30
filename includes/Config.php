<?
/*
 * Heello API for PHP
 * Copyright 2013 Ryan LeFevre - @meltingice / Casey Mees - @muzzlefur
 * PHP version 5.3.0+
 *
 * Licensed under the New BSD License, more info in LICENSE file
 * included with this software.
 *
 * Source code is hosted at http://github.com/Heello/heello-php
 */

namespace Heello;

class Config {
	# read-only or read-write authentication
	const NOCREDS = 1;
	const READONLY = 2;
	const READWRITE = 3;

	private $client_id, $client_secret;
	private $access_token, $refresh_token;
	private $redirect_url;
	private $state;
	private $refresh_token_callback;

	public function __construct($client_id, $client_secret) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->refresh_token_callback = function ($access_token, $refresh_token) {};
	}

	public function mode() {
		if (empty($this->client_id) || empty($this->client_secret)) return self::NOCREDS;
		elseif (empty($this->access_token)) return self::READONLY;
		else return self::READWRITE;
	}

	public function get_client() {
		return array('id' => $this->client_id, "secret" => $this->client_secret);
	}

	public function get_tokens() {
		if (self::mode() == self::READONLY) return false;
		return array('access' => $this->access_token, 'refresh' => $this->refresh_token);
	}

	public function get_redirect_url() {
		return $this->redirect_url;
	}

	public function get_state() {
		if (empty($this->state)) {
			if (!session_id()) {
				// A session isn't running, so now we don't know what to do.
				throw new APIException("Automatic handling of session state requires an already active session.");
			}

			if (isset($_SESSION['HEELLO_API_STATE'])) {
				$this->state = $_SESSION['HEELLO_API_STATE'];
			} else {
				$this->state = sha1(uniqid('', true));
				$_SESSION['HEELLO_API_STATE'] = $this->state;
			}
		}

		return $this->state;
	}


	public function set_access_token($token) {
		$this->access_token = $token;
	}

	public function set_refresh_token($token) {
		$this->refresh_token = $token;
	}

	public function set_redirect_url($url) {
		$this->redirect_url = $url;
	}

	public function set_state($state) {
		$this->state = $state;
	}

	public function refresh_token_callback($cb) {
		$this->refresh_token_callback = $cb;
	}

	public function execute_refresh_token_callback() {
		$cb = $this->refresh_token_callback;
		$cb($this->access_token, $this->refresh_token);
	}
}
