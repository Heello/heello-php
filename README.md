# About the Project

The Heello API library provides simple-to-use access to the Heello API and takes care of all the OAuth headaches for you.

## Dependencies

* HTTP_Request2

## Examples

### Providing an Authorization Link

```php
<?
$client_id = "abc";
$client_secret = "123";
$heello = new Heello\Client($client_id, $client_secret, 'http://example.com/finish');
?>

<a href="<?=$heello->get_authorization_url()?>">Login with Heello</a>
```

If you'd rather show the authorization page in a popup window, you can get the URL with:

```php
<?=$heello->get_authorization_url('popup')?>
```

### Finishing Authorization

```php
<?
$heello = new Heello\Client($client_id, $client_secret, 'http://example.com/finish');

// Retrieve access/refresh tokens for auth'd user.
// Save these in storage somewhere.
$tokens = $heello->finish_authorization();

// Retrieve information about the currently auth'd user.
// Also recommended you save this info in storage somewhere.
$user = $heello->me();
```

### Handling Refresh Tokens

In OAuth 2, access tokens only last so long before they expire and require re-issuing. This is where the refresh token comes into play. This is handled automatically by the library. To be notified when a new access token is issued, and to save the new token, you can use:

```php
<?
$heello::config()->refresh_token_callback(function ($access_token, $refresh_token) {
	// This will be called whenever the tokens are refreshed.
	// You will want to update your user storage with these new tokens.
});
``` 

### Getting Information from the API

```php
<?
$heello = new Heello\Client($client_id, $client_secret);

// If we need to make auth'd calls, and already have a user
// that is logged in, we can do this. Otherwise, it's not required.
Heello\Client::config()->set_access_token($access_token);
Heello\Client::config()->set_refresh_token($refresh_token);

// An auth'd call
$timeline = $heello->pings->home();

// A non-auth'd call
$user = $heello->users->show(array('id' => 3));
```

## Contributors

* [Ryan LeFevre](http://heello.com/meltingice)
* [Casey Mees](http://heello.com/muzzlefur)