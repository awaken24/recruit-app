<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function success_response(string $message, int $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], $status);
    }

    protected function error_response(string $message, $errors = null, int $status = 500)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

}