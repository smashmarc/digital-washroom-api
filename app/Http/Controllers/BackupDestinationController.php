<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackupDestination;
use App\Services\BackupTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackupDestinationController extends Controller
{
    public function __construct(private BackupTransferService $transferService) {}

    public function index(): JsonResponse
    {
        $destinations = BackupDestination::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $destinations->map(fn($d) => $this->format($d)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label'     => 'required|string|max:100',
            'type'      => 'required|in:s3,sftp,email,google_drive',
            'config'    => 'required|array',
            'is_active' => 'boolean',
        ]);

        $destination = BackupDestination::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Destination created',
            'data'    => $this->format($destination),
        ], 201);
    }

    public function update(Request $request, BackupDestination $destination): JsonResponse
    {
        $data = $request->validate([
            'label'     => 'sometimes|string|max:100',
            'type'      => 'sometimes|in:s3,sftp,email,google_drive',
            'config'    => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $destination->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Destination updated',
            'data'    => $this->format($destination->fresh()),
        ]);
    }

    public function destroy(BackupDestination $destination): JsonResponse
    {
        $destination->delete();
        return response()->json([
            'success' => true,
            'message' => 'Destination deleted',
            'data'    => [],
        ]);
    }

    public function send(Request $request, BackupDestination $destination): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $success = $this->transferService->transfer($destination, $request->filename);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'File transferred successfully' : $destination->fresh()->last_error,
            'data'    => $this->format($destination->fresh()),
        ]);
    }

    public function sendToAll(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $results = $this->transferService->transferToAll($request->filename);
        $allOk   = !in_array(false, $results, true);

        return response()->json([
            'success' => $allOk,
            'message' => $allOk ? 'Transferred to all destinations' : 'Some transfers failed',
            'data'    => $results,
        ]);
    }

    private function format(BackupDestination $d): array
    {
        return [
            'id'           => $d->id,
            'label'        => $d->label,
            'type'         => $d->type,
            'is_active'    => $d->is_active,
            'last_used_at' => $d->last_used_at?->toDateTimeString(),
            'last_status'  => $d->last_status,
            'last_error'   => $d->last_error,
            'created_at'   => $d->created_at->toDateTimeString(),
            // config intentionally omitted from responses for security
        ];
    }
}