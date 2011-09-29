# About the Project

The Heello API library provides simple-to-use access to the Heello API and takes care of all the OAuth headaches for you.

## Dependencies

* HTTP_Request2

## Example Usage

```php
<?

$heello = new Heello\Client($client_id, $client_secret);
$user = $heello->users->show(array('id' => 3));

echo "Username: {$user->username}, Name: {$user->name}";
```

## Contributors

* [Ryan LeFevre](http://heello.com/meltingice)
* [Casey Mees](http://heello.com/muzzlefur)