# About the Project

The Heello API library provides simple-to-use access to the Heello API and takes care of all the OAuth headaches for you.

** Note: Currently in beta as we build out more support. More documentation and updates coming soon.

## Dependencies

* HTTP_Request2

## Examples

### Getting Some Information from the API

```php
<?
// Get these from https://developer.heello.com/ by logging in and creating an application
$heello = new Heello\Client($client_id, $client_secret);

// If we need to make auth'd calls, and already have a user
// that is logged in, we can do this. Otherwise, it's not required.
Heello\Client::config()->set_access_token($access_token);
Heello\Client::config()->set_refresh_token($refresh_token);

// An auth'd call
$my = $heello->users->me();

// A non-auth'd call
$user = $heello->users->show(array(
  'id' => 3
));
```

## Contributors

* [Ryan LeFevre](http://heello.com/meltingice)
* [Casey Mees](http://heello.com/muzzlefur)
