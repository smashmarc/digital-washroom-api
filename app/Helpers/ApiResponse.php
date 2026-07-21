<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApiResponse
{
    public static function respond(
        bool $success,
        string $message,
        mixed $payload = null,
        mixed $errors = null,
        int $status = 200,
        ?string $resource = null,
        ?string $errorCode = null
    ): JsonResponse {
        $items = [];
        $meta = [
            'total'        => 0,
            'current_page' => null,
            'per_page'     => null,
            'last_page'    => null,
            'paginated'    => false,
        ];

        if ($payload instanceof LengthAwarePaginator) {
            // Paginated response
            $items = $resource
                ? $resource::collection($payload->items())
                : $payload->items();

            $meta = [
                'total'        => $payload->total(),
                'current_page' => $payload->currentPage(),
                'per_page'     => $payload->perPage(),
                'last_page'    => $payload->lastPage(),
                'paginated'    => true,
            ];
        } elseif ($payload instanceof Collection) {
            // Collection response
            $items = $resource
                ? $resource::collection($payload)
                : $payload;

            $meta['total'] = $payload->count();
        } elseif (is_array($payload)) {
            // Array of items
            $items = $payload;
            $meta['total'] = count($items);
        } elseif (!is_null($payload)) {
            // Single item
            $items = [$payload];
            $meta['total'] = 1;
        }

        $response = [
            'success' => $success,
            'message' => $message,
            'errors'  => $errors,
            'data'    => [
                'items' => $items,
                'meta'  => $meta,
            ],
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $status);
    }

    public static function success(string $message, mixed $payload = null, int $status = 200, ?string $resource = null): JsonResponse
    {
        return self::respond(true, $message, $payload, null, $status, $resource);
    }

    public static function error(string $message, int $status = 400, mixed $errors = null, ?string $errorCode = null): JsonResponse
    {
        return self::respond(false, $message, [], $errors, $status, null, $errorCode);
    }
}
