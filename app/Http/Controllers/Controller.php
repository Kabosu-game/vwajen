<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function success($data = null, string $message = 'Succès', int $status = 200, ?array $meta = null): \Illuminate\Http\JsonResponse
    {
        $payload = ['success' => true, 'message' => $message, 'data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }

    protected function error(string $message = 'Erreur', int $status = 400, $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors) $response['errors'] = $errors;
        return response()->json($response, $status);
    }

    protected function paginated($paginator, string $message = 'Succès'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
