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

  // Update a user account, associated with access token
  // Note: The attached background, avatar or cover is a file path to an image file that
  // exists on the local file system
  try{
    $user = $api->accounts->update(array(
      "user" => array(
        "name" => "New Name",
        "gender" => "Female",
        "bio" => "Heello Developer Extraordinairre",
        "cover" => realpath("sample_upload.jpg"),
      )
    ));

    print_r($user);
  }
  catch (Exception $e){
    print $e->getMessage();
  }
