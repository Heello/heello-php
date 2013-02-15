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
    // Get an array of Heello user ids, use a username lookup instead of the id of the user
    $users = $api->users->listening(array(
      "id" => "heello",
      "count" => 10,
      "username" => 1
    ));

    // Note that $users isn't an array of user objects, to get a list of user objects
    // use the user lookup api endpoint. ids takes a comma separated list of user ids
    $users = $api->users->lookup(array(
      "ids" => implode(",",$users)
    ));

    print_r($users);
  } catch (Exception $e){
    print $e->getMessage();
  }
