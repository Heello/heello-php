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
    // Show the user basic information
    $user = $api->users->show(array(
      "id" => 2,
    ));

    $id = $user->id;
    $name = $user->name;

    print_r($user);
  } catch (Exception $e){
    print $e->getMessage();
  }
