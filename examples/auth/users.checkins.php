<?
  // Note: PHP Examples use the Heello PHP Client library
  // https://github.com/Heello/heello-php
  require_once dirname(dirname(dirname(__FILE__))) . '/Heello.php';

  // You can get an Application Key and Secret by visiting:
  // http://developer.heello.com/apps
  $api_application_key = "APPLICATION_KEY";
  $api_application_secret = "APPLICATION_SECRET";

  // You can get an access token and refresh token by implementing the auth
  // flow described at (or use the demo provided):
  // http://developer.heello.com/guides/authentication
  $access_token = "ACCESS_TOKEN";

  $api = new Heello\Client($api_application_key, $api_application_secret);
  Heello\Client::config()->set_access_token($access_token);

  try{
    // Returns a list of Ping(s) that have been designated as a checkin
    $checkins = $api->users->checkins();

    print_r($checkins);
  } catch (Exception $e){
    print $e->getMessage();
  }
