<?
  // Note: PHP Examples use the Heello PHP Client library
  // https://github.com/Heello/heello-php
  require_once dirname(dirname(dirname(__FILE__))) . '/Heello.php';

  // You can get an Application Key and Secret by visiting:
  // http://developer.heello.com/apps
  $api_application_key = "APPLICATION_KEY";
  $api_application_secret = "APPLICATION_SECRET";

  $api = new Heello\Client($api_application_key, $api_application_secret);

  try{
    // checkins->show is essentially an alias to pings->show
    // difference here is if you supply a valid ping id that is not a checkin
    // a 404 is returned.
    $ping = $api->checkins->show(array(
      "id" => 1
    ));

    print_r($ping);
  } catch (Exception $e){
    print $e->getMessage();
  }
