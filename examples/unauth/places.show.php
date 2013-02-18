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
    // Array of notifications for the auth'd user
    $place = $api->places->show(array(
      "id" => "322-0cab6928-e62b-4d48-a005-5199c61264d3"
    ));

    print_r($place);
  }
  catch (Exception $e){
    print $e->getMessage();
  }
