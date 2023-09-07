<?php

namespace App\Models;

class CustomResponse {
    public int $code;
    public $message;
    public $response;
    
    function __construct($response = null, int $code = 200, $msg = null) {
        $this->code = $code;
        $this->message = $msg;
        $this->response = $response;
    }

    public static function build($response = null, int $code = 200, $msg = null) {
        $instance = new CustomResponse($response, $code, $msg);
        return $instance;
    }

    public static function unAuthorized($msg = null) {
        $instance = new CustomResponse($msg, 401, "UNAUTHORIZED");
        return $instance;
    }

    public static function forbidden($msg = null) {
        $instance = new CustomResponse($msg, 403, "FORBIDDEN");
        return $instance;
    }

    public static function badRequest($msg = null) {
        $instance = new CustomResponse($msg, 400, "BAD_REQUEST");
        return $instance;
    }

    public static function ok($msg = null) {
        $instance = new CustomResponse($msg, 200, "OK");
        return $instance;
    }

    public static function intertalServerError($msg = null) {
        $instance = new CustomResponse($msg, 500, "INTERNAL_SERVER_ERROR");
        return $instance;
    }

}