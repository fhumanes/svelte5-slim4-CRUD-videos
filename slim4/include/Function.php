<?php
/*********************** USEFULL FUNCTIONS **************************************/

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;


/**
 * Verificando los parametros requeridos en el método
 */
function verifyRequiredParams($required_fields, $request_params
        // ,  Request  $request, Response $response
        )
{
    global $errorMessages;
    $error = false;
    $error_fields = "";

    // $request_params = $request->getParsedBody();

    foreach ($required_fields as $field) {
        // if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
        if (!isset($request_params[$field])) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $responseBody = array();
        // $responseBody["error"] = true;
        // $responseBody["message_num"] = '003';
        $responseBody["message"] = str_replace("{1}",substr($error_fields, 0, -2),$errorMessages['003']); 

        return $responseBody;
    }
    return true;
}

/**
 * Revisa si la consulta contiene un Header "Authorization" para validar
 */
function authenticate(Request $request, Response $response)
{
    global $errorMessages;
    // Getting request headers
    $headers = $request->getHeaders();
    // Verifying Authorization || authorization Header
    if (isset($headers['Authorization'])|| isset($headers['authorization']) ) {
        // get the api key
        if (isset($headers['Authorization']) ) $token = $headers['Authorization'];
        if (isset($headers['authorization']) ) $token = $headers['authorization'];
        
        // validating api key
        if (!($token[0] == API_KEY)) { //API_KEY declarada en Config.php

            // api key is not present in users table
            // $responseBody["error"] = true;
            //$responseBody["message_num"] = '002';
            $responseBody["message"] = $errorMessages['002']; 
            // Error 401
            return $responseBody;
        } else {
            //procede utilizar el recurso o metodo del llamado
            return true;
        }
    } else {
        // api key is missing in header
        // $responseBody["error"] = true;
        // $responseBody["message_num"] = '001';
        $responseBody["message"] = $errorMessages['001']; 
        // error 400
        return $responseBody;
    }
}


