<?php

namespace App\Http\Traits;

trait ApiResponse
{
    /**
     * Standart başarılı API yanıtı
     */
    protected function successResponse($data = null, $message = null, $code = 200, $meta = [])
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Standart hatalı API yanıtı
     */
    protected function errorResponse($message = null, $code = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
