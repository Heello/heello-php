<?
  require_once dirname(dirname(dirname(__FILE__))) . '/Heello.php';

  $api = new Heello\Client("APPLICATION_KEY", "APPLICATION_SECRET", "https://example.com/auth");

  // Show the user basic information
  $user = ($api->users->show(array(
    "id" => 2,
  )));


  print "ID: " . $user->id;
  print "\nName: " . $user->name;
  print "\nUsername: " . $user->username;

  print "\nUser Data:\n";
  print_r($user);
