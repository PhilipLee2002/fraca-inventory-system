<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Success response method.
     */
    protected function sendSuccess($data = null, $message = null, $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     */
    protected function sendError($message, $errors = [], $code = 400)
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

    /**
     * Resource created response.
     */
    protected function sendCreated($data = null, $message = 'Resource created successfully')
    {
        return $this->sendSuccess($data, $message, 201);
    }

    /**
     * Resource updated response.
     */
    protected function sendUpdated($data = null, $message = 'Resource updated successfully')
    {
        return $this->sendSuccess($data, $message, 200);
    }

    /**
     * Resource deleted response.
     */
    protected function sendDeleted($message = 'Resource deleted successfully')
    {
        return $this->sendSuccess(null, $message, 200);
    }

    /**
     * Paginated response.
     */
    protected function sendPaginated($paginator, $message = null)
    {
        $data = [
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ];

        return $this->sendSuccess($data, $message);
    }
}
