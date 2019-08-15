## Setup

### .htaccess
Redirect all to your main file
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ api.php [L]
```

### Main file
api.php
```php
require 'Router.php';
$router = new Router();

$router -> get('/hello/<string>', function($name){
  echo 'Hello, '.$name;
});
  
$router -> run();
```

## Usage
### Methods
- GET
- POST
- PUT
- DELETE

### Patterns
- \<all> All chars without "/" char,
- \<string> Alphabetic characters,
- \<int> Digits,
- \<char> Alphanumeric characters,
- \<url> URL format characters (Alphanumeric characters, with "_" and "-" characters)
- \<*> All characters

### Example
```php
$router -> get('/user/<string>', function($userName){ ... });
$router -> post('/user', function(){ ... });
$router -> put('/user', function(){ ... });
$router -> delete('/user/<int>', 'Some/Namespace/Class::method');
```
### Grouping
```php
 $router -> group('/admin', function($router){
   $router -> get('/users', function(){ ... });
   $router -> post('/user', function(){ ... });
 });
```
### Multi grouping
```php
 $router -> group('/admin', function($router){
  $router -> group('/users', function($router){
    $router -> get('/profile/<int>', function($id){ ... });
  });
  
  $router -> group('/admins', function($router){
    $router -> get('/profile/<int>', function($id){ ... });
  });
 });
```
### Guards for groups
Guard function returns TRUE or FALSE
```php
$router -> group('/admin', function($router){
  $router -> get('/users', function(){ ... });
}) -> guard(function(){
  return false;
});
```
Guard can be a Class, boolean and function
```
-> guard('Namespace/Class::method');
-> guard(false);
-> guard(function(){ ... });
```
### Status codes
- 404 - cannot find a route
- 403 - guard return false
```
$router -> status('404', 'Namespace/Class::method');
$router -> status('403', function(){ ... });
```
