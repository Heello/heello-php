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

  // First, create a Ping with just lat / long data
  try{
    $response = $api->pings->create(array(
      "ping" => array(
        "text" => "Glad to be back home.",
        "lat" => 32.77727,
        "long" => -79.93482,
      )
    ));
  } catch (Exception $e){
    print $e->getMessage();
  }

  // Second, create a Ping with a place_id, but we'll search first
  try{
    $place = $api->places->search(array(
      "query" => "Starbucks",
      "count" => 1,
      "lat" => 32.78167,
      "lon" => -79.93433
    ));

    $place_id = $place[0]->id;

    $response = $api->pings->create(array(
      "ping" => array(
        "text" => "Had a great time last night!",
        "place_id" => $place_id
      )
    ));
  } catch (Exception $e){
    print $e->getMessage();
  }

  // Third, create a Ping with a place_id and as a checkin, but we'll search first
  try{
    $place = $api->places->search(array(
      "query" => "Starbucks",
      "count" => 1,
      "lat" => 32.78167,
      "lon" => -79.93433
    ));

    $place_id = $place[0]->id;

    $response = $api->pings->create(array(
      "ping" => array(
        "text" => "Had a great time last night!",
        "place_id" => $place_id,
        "checkin" => true
      )
    ));
  } catch (Exception $e){
    print $e->getMessage();
  }
