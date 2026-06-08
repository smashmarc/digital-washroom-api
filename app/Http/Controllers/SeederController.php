<?php

namespace App\Http\Controllers;

use App\Constants\Role as RoleConstant;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeederController extends Controller
{
    private array $seeders = [
        [
            'key'         => 'AdminUserSeeder',
            'label'       => 'Admin User',
            'description' => 'Creates or ensures admin@washroom.test exists with the administrator role.',
            'icon'        => 'shield-account',
        ],
        [
            'key'         => 'PermissionSeeder',
            'label'       => 'Permissions',
            'description' => 'Creates all system permission records from PermissionConstant.',
            'icon'        => 'lock',
        ],
        [
            'key'         => 'RoleSeeder',
            'label'       => 'Roles',
            'description' => 'Creates 10 roles: administrator, manager, supervisor, inspector, staff, cleaner, auditor, viewer, coordinator, technician.',
            'icon'        => 'shield',
        ],
        [
            'key'         => 'LocationSeeder',
            'label'       => 'Locations',
            'description' => 'Seeds 10 fake locations with names and addresses.',
            'icon'        => 'map-marker',
        ],
        [
            'key'         => 'UserSeeder',
            'label'       => 'Users',
            'description' => 'Seeds 20 users including one guaranteed admin@washroom.test. Requires Locations and Roles.',
            'icon'        => 'account-multiple',
        ],
        [
            'key'         => 'RoomSeeder',
            'label'       => 'Rooms',
            'description' => 'Seeds 200 rooms (20 per location). Requires Locations.',
            'icon'        => 'door-open',
        ],
        [
            'key'         => 'CriteriaCategorySeeder',
            'label'       => 'Criteria Categories',
            'description' => 'Seeds 6 QA criteria categories: Cleanliness, Hygiene, Maintenance, Safety, Equipment, Odor Control.',
            'icon'        => 'tag-multiple',
        ],
        [
            'key'         => 'CriteriaSeeder',
            'label'       => 'Criteria',
            'description' => 'Seeds 27 QA criteria distributed across all categories. Requires Criteria Categories.',
            'icon'        => 'help-circle',
        ],
        [
            'key'         => 'EvaluationTemplateSeeder',
            'label'       => 'Evaluation Templates',
            'description' => 'Seeds 3 templates: Monthly Inspection, Daily Quick Check, Quarterly Audit. Requires Criteria.',
            'icon'        => 'file-document-multiple',
        ],
        [
            'key'         => 'LogSeeder',
            'label'       => 'Logs',
            'description' => 'Seeds ~1,220 cleaning logs (20/day for 2 months). Requires Rooms and Users.',
            'icon'        => 'text-box',
        ],
        [
            'key'         => 'EvaluationSeeder',
            'label'       => 'Evaluations',
            'description' => 'Seeds 180 evaluations (all users × all templates × 3 months). Requires Users and Templates.',
            'icon'        => 'clipboard-check',
        ],
    ];

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole(RoleConstant::ADMINISTRATOR)) {
            return ApiResponse::error('Forbidden', 403);
        }

        return ApiResponse::success('Seeders listed', $this->seeders);
    }

    public function run(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole(RoleConstant::ADMINISTRATOR)) {
            return ApiResponse::error('Forbidden', 403);
        }

        $key = $request->input('seeder');

        if ($key === 'all') {
            return $this->runAll();
        }

        $valid = collect($this->seeders)->pluck('key')->contains($key);
        if (! $valid) {
            return ApiResponse::error("Unknown seeder: {$key}", 422);
        }

        return $this->runOne($key);
    }

    private function runOne(string $key): JsonResponse
    {
        try {
            $exitCode = Artisan::call('db:seed', ['--class' => $key, '--force' => true]);
            $output   = trim(Artisan::output());

            return ApiResponse::success("Seeder '{$key}' completed.", [
                'seeder'    => $key,
                'exit_code' => $exitCode,
                'output'    => $output ?: "Seeder ran with no output.",
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error("Seeder '{$key}' failed: " . $e->getMessage(), 500);
        }
    }

    private function runAll(): JsonResponse
    {
        $results = [];
        $hasError = false;

        foreach ($this->seeders as $seeder) {
            try {
                $exitCode  = Artisan::call('db:seed', ['--class' => $seeder['key'], '--force' => true]);
                $output    = trim(Artisan::output());
                $results[] = [
                    'seeder'    => $seeder['key'],
                    'label'     => $seeder['label'],
                    'exit_code' => $exitCode,
                    'output'    => $output ?: "Ran with no output.",
                    'status'    => $exitCode === 0 ? 'success' : 'error',
                ];
            } catch (\Throwable $e) {
                $hasError  = true;
                $results[] = [
                    'seeder'    => $seeder['key'],
                    'label'     => $seeder['label'],
                    'exit_code' => 1,
                    'output'    => $e->getMessage(),
                    'status'    => 'error',
                ];
            }
        }

        $message = $hasError ? 'Some seeders completed with errors.' : 'All seeders completed successfully.';

        return ApiResponse::success($message, $results);
    }
}
