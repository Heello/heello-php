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

  // Do a basic search
  try{
    $places = $api->places->search(array(
      "lat" => 32.78167,
      "long" => -79.93433
    ));
  } catch (Exception $e){
    print $e->getMessage();
  }

  // Narrow it down
  try{
    $places = $api->places->search(array(
      "lat" => 32.78167,
      "long" => -79.93433,
      "category" => "coffee"
    ));
  } catch (Exception $e){
    print $e->getMessage();
  }

  // Finding places "near" you
  try{
    // Note: max_distance when paired with "english" units, is in miles
    $places = $api->places->search(array(
      "lat" => 32.78167,
      "long" => -79.93433,
      "category" => "coffee",
      "max_distance" => 2,
      "count" => 5,
      "distance_units" => "english"
    ));

    // or for those who love metric

    // Note: max_distance when paired with "metric" units, is in km
    $places = $api->places->search(array(
      "lat" => 32.78167,
      "long" => -79.93433,
      "category" => "coffee",
      "max_distance" => 2,
      "count" => 5,
      "distance_units" => "metric"
    ));

  } catch (Exception $e){
    print $e->getMessage();
  }

