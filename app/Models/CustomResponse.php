<?php

namespace App\Models;

class CustomResponse {
    public int $code;
    public mixed $message;
    public mixed $response;
    
    function __construct($response = null, int $code = 200, $msg = null) {
        $this->code = $code;
        $this->message = $msg;
        $this->response = $response;
    }

    public static function build($response = null, int $code = 200, $msg = null): CustomResponse
    {
        return new CustomResponse($response, $code, $msg);
    }

    public static function unAuthorized($msg = null): CustomResponse
    {
        return new CustomResponse($msg, 401, "UNAUTHORIZED");
    }

    public static function forbidden($msg = null): CustomResponse
    {
        return new CustomResponse($msg, 403, "FORBIDDEN");
    }

    public static function badRequest($msg = null): CustomResponse
    {
        return new CustomResponse($msg, 400, "BAD_REQUEST");
    }

    public static function ok($msg = null): CustomResponse
    {
        return new CustomResponse($msg, 200, "OK");
    }

    public static function intertalServerError($msg = null): CustomResponse
    {
        return new CustomResponse($msg, 500, "INTERNAL_SERVER_ERROR");
    }

}