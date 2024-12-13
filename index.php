<?php 

define('BASE_PATH', __DIR__);

define('PAGE_PATH', BASE_PATH . '/pages');

define('DATA_PATH', BASE_PATH . '/data');

define('SUPPORT_PATH', BASE_PATH . '/support');

define('COMPONENT_PATH', BASE_PATH . '/components');

define('ASSET_PATH', BASE_PATH . '/assets');

include_once(SUPPORT_PATH . '/init.php');

// Create router instance with base directory for pages
$router = new Router(BASE_PATH);


// Register routes that map to PHP files
$router->get('/', '/pages/home.php');


// Set fallback file for 404 errors
$router->setFallback('/pages/home.php');

// Resolve current route
$router->resolve($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);