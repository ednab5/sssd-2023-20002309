<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/service/UserService.class.php';
require_once __DIR__.'/dao/UserDao.class.php';
Flight::register('userDao','UserDao');
Flight::register('userService', 'UserService');
require_once __DIR__.'/routes/UserRoute.php';

Flight::route('GET /dist.json', function(){
  $openapi = \OpenApi\scan('routes');
  header('Content-Type: application/json');
  echo $openapi->toJson();
});

Flight::route('/*', function(){
    $path = Flight::request()->url;
    if($path == '/login' || $path == '/register' || $path == '/docs.json' || $path = '/verifyemail' || $path = '/verify2fa' || $path = '/qr'|| $path = '/sms' || $path = '/verify2faSMS'  || $path = '/resetpassswordemail' || $path = '/resetpassword')
    {
        return TRUE;
    }
    $headers = getallheaders();
    if(@!$headers['Authorization']){
        Flight::json(["message" => "Unauthorized access"], 401);
        return FALSE;
    }else{
        try {
            return TRUE;
        } catch (\Exception $e) {
            Flight::json(["message" => "Token authorization invalid"], 403);
            return FALSE;
        }
    }
});


Flight::start();
?>
