<?php 


function get_data(string $path) : ?array 
{
    if( empty( $path ) ){
        return [];
    }

    $absoluteFilePath = DATA_PATH . '/' . $path . '.php';

    if( file_exists($absoluteFilePath) ){
        return include_once($absoluteFilePath);
    }

    return [];
}


function get_component(string $path, $data = []) : void 
{
    if( empty( $path ) ){
        return;
    }

    $cleanPath = ltrim($path, '/');

    if (substr($cleanPath, -4) == '.php') {
        $cleanPath = substr($cleanPath, 0, -4);
    } 

    extract($data);

    include_once(COMPONENT_PATH . '/' . $cleanPath . '.php');
}


function get_page(string $path) : void 
{
    if( empty( $path ) ){
        return;
    }

    $cleanPath = ltrim($path, '/');

    if (substr($cleanPath, -4) == '.php') {
        $cleanPath = substr($cleanPath, 0, -4);
    } 

    include_once(PAGE_PATH . '/' . $cleanPath . '.php');
}


function asset(string $path) : void 
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    $domain = $_SERVER['HTTP_HOST'];

    $baseUrl = $protocol . '://' . $domain;

    if( empty( $path ) ){
        echo $baseUrl;
    }

    $cleanPath = ltrim($path, '/');

    echo $baseUrl . '/' . $cleanPath ;
}


function url(string $path) : void 
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    $domain = $_SERVER['HTTP_HOST'];

    $baseUrl = $protocol . '://' . $domain;

    if( empty( $path ) ){
        echo $baseUrl;
    }

    $cleanPath = ltrim($path, '/');

    echo $baseUrl  . '/' . $cleanPath ;
}