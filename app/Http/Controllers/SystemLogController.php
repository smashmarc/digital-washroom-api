<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    /** GET /api/system-log */
    public function index(Request $request): JsonResponse
    {
        try {
            if (!file_exists($this->logPath)) {
                return ApiResponse::success('No log file found.', [
                    'entries'       => [],
                    'file_size'     => 0,
                    'human_size'    => '0 B',
                    'last_modified' => null,
                    'total_entries' => 0,
                ]);
            }

            $level = strtoupper((string) $request->input('level', ''));
            $limit = (int) $request->input('limit', 200);

            $content = $this->readTail($this->logPath, 1024 * 1024); // last 1 MB
            $entries = $this->parseEntries($content);

            if ($level && $level !== 'ALL') {
                $entries = array_values(array_filter($entries, fn($e) => $e['level'] === $level));
            }

            $total  = count($entries);
            $entries = array_slice(array_reverse($entries), 0, $limit);
            $size   = filesize($this->logPath);

            return ApiResponse::success('Log fetched.', [
                'entries'       => $entries,
                'file_size'     => $size,
                'human_size'    => $this->humanSize($size),
                'last_modified' => date('Y-m-d H:i:s', filemtime($this->logPath)),
                'total_entries' => $total,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to read log: ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /api/system-log */
    public function clear(): JsonResponse
    {
        try {
            if (file_exists($this->logPath)) {
                file_put_contents($this->logPath, '');
            }
            return ApiResponse::success('Log cleared.');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to clear log: ' . $e->getMessage(), 500);
        }
    }

    private function readTail(string $path, int $maxBytes): string
    {
        $size   = filesize($path);
        $handle = fopen($path, 'r');
        if ($size > $maxBytes) {
            fseek($handle, -$maxBytes, SEEK_END);
            fgets($handle); // skip partial first line
        }
        $content = stream_get_contents($handle);
        fclose($handle);
        return $content;
    }

    private function parseEntries(string $content): array
    {
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.*?)(?=^\[\d{4}-\d{2}-\d{2}|\z)/ms';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        return array_map(fn($m) => [
            'timestamp'   => $m[1],
            'environment' => $m[2],
            'level'       => strtoupper($m[3]),
            'message'     => trim($m[4]),
        ], $matches);
    }

    private function humanSize(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) return round($bytes, 1) . ' ' . $unit;
            $bytes /= 1024;
        }
        return round($bytes, 1) . ' TB';
    }
}
