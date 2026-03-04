<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application.
|
*/

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Application Routing Engine (Basic)
|--------------------------------------------------------------------------
|
| Path: public/router.php
| From here we can handle the incoming request and route it to the
| appropriate file.
|
*/

$url  = parse_url($_SERVER['REQUEST_URI']);
$file = __DIR__ . $url['path'];

if (is_file($file)) : return false; endif;

/*
|--------------------------------------------------------------------------
| Routes Definitions
|--------------------------------------------------------------------------
|
| Here we can define our routes and the files they should be routed to.
| The key is the route and the value is the file to be routed to.
|
*/

$routes = [
    '/' => 'index.php',
];

/*
|--------------------------------------------------------------------------
| Error File
|--------------------------------------------------------------------------
|
| If the route is not found, we can route to an error file.
|
*/
$errorFile = __DIR__ . '/404.php';


/*
|--------------------------------------------------------------------------
| Route Request
|--------------------------------------------------------------------------
|
| Here we can route the request to the appropriate file.
|
*/
if (isset($routes[$url['path']])) : require $routes[$url['path']];

    else : require $errorFile;
    header('HTTP/1.0 404 Not Found');
    
endif;