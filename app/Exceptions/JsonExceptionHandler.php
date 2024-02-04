<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class JsonExceptionHandler extends ExceptionHandler
{
    public function render($request, \Throwable $e)
    {
        if ($e instanceof RouteNotFoundException) {
            return response()->json(['error' => ['message' => 'The token is expired or incorrect, please log in']], 500);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(['error' => ['message' => 'The method is not supported for route']], 405);
        }
        if ($e instanceof JWTException) {
            return response()->json(['error' => ['message' => 'Token could not be parsed from the request']], 500);
        }
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json(['error' => ['message' => 'Too Many Requests']], 429);
        }

        return parent::render($request, $e);
    }
}