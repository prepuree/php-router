## Setup

### .htaccess
redirect all to api.php
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
#### GET
```php
$router -> get('/users', function(){ ... });
$router -> get('/user/<string>', function($arg){ ... });
$router -> get('/user/<string>/<int>', function($method, $id){ ... });
$router -> get('/user/<string>', ['Path/To/Class', 'classMethod']);
```
#### POST
```php
$router -> post('/login', function(){ ... });
$router -> post('/logout', ['Path/To/Class', 'classMethod']);
```
### Patterns
- \<all> All chars without "/" char,
- \<string> Alphabetic characters,
- \<int> Digits,
- \<char> Alphanumeric characters,
- \<url> URL format characters (Alphanumeric characters, with "_" and "-" characters)
- \<*> All characters
  
### Grouping
```php
 $router -> group('/admin', function($router){
   $router -> get('/users', function(){ ... });
   $router -> post('/add_user', function(){ ... });
 });
```
### Multi grouping
```php
 $router -> group('/admin', function($router){
  $router -> group('/user', function($router){
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
  if($_SESSION['admin']){
    return true;
  } else {
    return false;
  }
});
```
