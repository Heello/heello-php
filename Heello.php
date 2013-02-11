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

  public function get_authorization_url($display="full"){
  	if (self::$config->mode() == Config::NOCREDS) {
      throw new APIException("Client ID required to build authorization URL");
    }

  	$url = Request::get_auth_request_url_base();
    $url .= "oauth/authorize?";

    $client = self::$config->get_client();
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

	public function finish_authorization() {
    if ($this->has_auth_error()) {
      throw new APIException(Util::gpval('error'));
    }

    // If we have a valid state...
    if ($this->has_valid_state()) {
      // ... then retrieve the user's access token.
      return $this->get_tokens();
    } else {
      throw new APIException("Invalid state");
    }
  }

  /**
	 * Throw the request over to the API class to handle
	 */
	public function __get($key) {
		return $this->api->{$key};
	}

  private function has_auth_error() {
    return Util::gpval('error', false);
  }

	private function has_valid_state() {
    return Util::gpval('state') == self::$config->get_state();
  }

	private function prepare_token_request(){
    $request = new Request(Request::POST, '/oauth/token', true);

    $client = self::$config->get_client();
    $request->addParameter("client_id", $client['id']);
    $request->addParameter("client_secret", $client['secret']);
    $request->addParameter("redirect_uri", self::$config->get_redirect_url());
    $request->addParameter("code", Util::gpval('code'));
    $request->addParameter("response_type", 'token');
    $request->addParameter("grant_type", 'authorization_code');

    return $request;
  }
}
