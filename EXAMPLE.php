<?
include('Heello.php');

/*
 * All of this info is needed only for API calls that
 * require authentication. However, if the API call doesn't
 * require authentication and you provide the information anyways,
 * it won't make a difference.
 */
$client_id = "abc123";
$client_secret = "def456";

########################
## Pre-Authentication ##
########################
$heello = new Heello\Client($client_id, $client_secret, '/oauth/finish');
//$heello->authorization_redirect();

########################
## Post-Auth Redirect ##
########################
//$heello = new Heello\Client($client_id, $client_secret);
//$heello->finish_authorization();

//$auth_user = $heello->me();

$user = $heello->users->show(array('id' => 'meltingice', 'username' => true));
