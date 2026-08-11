<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\UserFormOptionsResource;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    public function __construct(protected AnnouncementService $announcementService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', Announcement::class);
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $announcements = $this->announcementService->searchPaginatedList($params);
        return ApiResponse::success('Announcements fetched successfully.', $announcements, 200, AnnouncementResource::class);
    }

    public function store(AnnouncementRequest $request): JsonResponse
    {
        Gate::authorize('create', Announcement::class);
        $announcement = $this->announcementService->create($request->validated());
        return ApiResponse::success('Announcement created successfully.', new AnnouncementResource($announcement), 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        Gate::authorize('view', Announcement::class);
        $announcement->load(['roles', 'locations', 'departments', 'creator']);
        return ApiResponse::success('Announcement fetched successfully.', new AnnouncementResource($announcement));
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        Gate::authorize('update', $announcement);
        $announcement = $this->announcementService->update($request->validated(), $announcement);
        return ApiResponse::success('Announcement updated successfully.', new AnnouncementResource($announcement));
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        Gate::authorize('delete', $announcement);
        $this->announcementService->delete($announcement);
        return ApiResponse::success('Announcement deleted successfully.');
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Announcement::class)) {
            abort(403);
        }
        $formOptions = $this->announcementService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new UserFormOptionsResource($formOptions));
    }
}
