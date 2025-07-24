<?php
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../vendor/firebase/php-jwt/src/JWT.php";
require_once __DIR__."/../vendor/firebase/php-jwt/src/Key.php";
require_once __DIR__."/../vendor/firebase/php-jwt/src/ExpiredException.php";
require_once __DIR__."/../vendor/firebase/php-jwt/src/SignatureInvalidException.php";
require_once __DIR__."/../vendor/firebase/php-jwt/src/JWTExceptionWithPayloadInterface.php";

require_once __DIR__ . '/../core/response.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\JWTExceptionWithPayloadInterface;

$algorithm = "HS256";
$keys = require __DIR__ . '/../config/keys.php';
$secret_key = $keys['jwt']['secret_key'];

function generate_jwt($payload, $key, $algorithm = 'HS256') {
    global $secret_key;
    $secret = $key === 'base' ? $secret_key : $key; // base uses the secret_key in keys.php;
    return JWT::encode($payload, $secret, $algorithm);
}

function validate_jwt($token) {
    global $secret_key, $algorithm;
    try {
        return JWT::decode($token, new Key($secret_key, $algorithm));
    } catch(ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['error'=> 'Token expired']);
        exit;
    } catch(SignatureInvalidException $e) {
        http_response_code(401);
        echo json_encode(['error'=> 'Invalid signature']);
        exit;
    } catch(Exception $e) {
        http_response_code(401);
        echo json_encode(['error'=> 'Invalid token']);
        exit;
    }
}

?>