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
}

class ExpiredAccessTokenException extends \Exception {}