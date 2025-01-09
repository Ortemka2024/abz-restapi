<?php

namespace App\Helpers\Api\V1;

class ApiResponseHelper
{
    public static function success($data = [], $message = '', $status = 200)
    {

        $response = ['success' => true];

        if (! empty($message)) {
            $response['message'] = $message;
        }

        if (! empty($data)) {
            $response = array_merge($response, $data);
        }

        return response()->json($response, $status);
    }

    public static function error($message, $fails = [], $status = 400)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($fails)) {
            $response['fails'] = $fails;
        }

        return response()->json($response, $status);
    }
}
