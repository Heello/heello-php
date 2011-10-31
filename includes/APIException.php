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
class APIException extends \Exception {
	const EXPIRED_TOKEN = 'expired_token';

	private $error_map = array(
		"access_denied" => "Access to the account was denied by the user",
		"invalid_request" => "Invalid grant_type parameter or parameter missing",
		"invalid_client" => "Client is invalid",
		"unauthorized_client" => "Client is not authorized",
		"redirect_uri_mismatch" => "Redirect URI does not match domain on record",
		"unsupported_response_type" => "Response type not recognized",
		"invalid_scope" => "Scope not recognized",
		"invalid_grant" => "Grant type not recognized",
		"unsupported_grant_type" => "Grant type not supported",
		"invalid_token" => "Access token provided is invalid",
		"expired_token" => "Access token provided is expired",
		"insufficient_scope" => "Scope insufficient for requested action",
		"ssl_required" => "Requests must be made over SSL",
		"invalid_state" => "An invalid response was returned by the authorization server",
	);

	public function translateMessage(){
		return isset($this->error_map[$this->getMessage()]) ? $this->error_map[$this->getMessage()] : $this->getMessage();
	}
}

class ExpiredAccessTokenException extends \Exception {}
