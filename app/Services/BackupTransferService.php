<?php

namespace App\Services;

use App\Models\BackupDestination;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Net\SFTP;

class BackupTransferService
{
    /**
     * Transfer a backup file to a destination.
     */
    public function transfer(BackupDestination $destination, string $filename): bool
    {
        $localPath = Storage::disk('local')->path("backups/{$filename}");

        if (!file_exists($localPath)) {
            $this->markFailed($destination, "File not found: {$filename}");
            return false;
        }

        try {
            $result = match ($destination->type) {
                's3'           => $this->transferToS3($destination, $localPath, $filename),
                'sftp'         => $this->transferToSftp($destination, $localPath, $filename),
                'email'        => $this->transferViaEmail($destination, $localPath, $filename),
                'google_drive' => $this->transferToGoogleDrive($destination, $localPath, $filename),
                default        => throw new \RuntimeException("Unknown type: {$destination->type}"),
            };

            $destination->update([
                'last_used_at' => now(),
                'last_status'  => 'success',
                'last_error'   => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->markFailed($destination, $e->getMessage());
            return false;
        }
    }

    /**
     * Transfer to all active destinations.
     */
    public function transferToAll(string $filename): array
    {
        $destinations = BackupDestination::where('is_active', true)->get();
        $results = [];

        foreach ($destinations as $destination) {
            $results[$destination->id] = $this->transfer($destination, $filename);
        }

        return $results;
    }

    // ── S3 / MinIO ─────────────────────────────────────────────────────────
    private function transferToS3(BackupDestination $dest, string $localPath, string $filename): bool
    {
        $config = $dest->config;

        $disk = Storage::build([
            'driver'                  => 's3',
            'key'                     => $config['key'],
            'secret'                  => $config['secret'],
            'region'                  => $config['region'] ?? 'us-east-1',
            'bucket'                  => $config['bucket'],
            'url'                     => $config['url'] ?? null,
            'endpoint'                => $config['endpoint'] ?? null,
            'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
        ]);

        $folder = $config['folder'] ?? 'backups';
        $disk->put("{$folder}/{$filename}", fopen($localPath, 'r'));

        return true;
    }

    // ── SFTP ───────────────────────────────────────────────────────────────
    private function transferToSftp(BackupDestination $dest, string $localPath, string $filename): bool
    {
        $config = $dest->config;

        $disk = Storage::build([
            'driver'   => 'sftp',
            'host'     => $config['host'],
            'username' => $config['username'],
            'password' => $config['password'] ?? null,
            'privateKey' => $config['private_key'] ?? null,
            'port'     => $config['port'] ?? 22,
            'root'     => $config['path'] ?? '/backups',
        ]);

        $disk->put($filename, fopen($localPath, 'r'));

        return true;
    }

    // ── Email ──────────────────────────────────────────────────────────────
    private function transferViaEmail(BackupDestination $dest, string $localPath, string $filename): bool
    {
        $config = $dest->config;

        Mail::raw("Please find the attached database backup: {$filename}", function ($message) use ($config, $localPath, $filename) {
            $message->to($config['email'])
                    ->subject("Database Backup: {$filename}")
                    ->attach($localPath, ['as' => $filename]);
        });

        return true;
    }

    // ── Google Drive ───────────────────────────────────────────────────────
    private function transferToGoogleDrive(BackupDestination $dest, string $localPath, string $filename): bool
    {
        // Requires: composer require google/apiclient
        $config = $dest->config;

        $client = new \Google\Client();
        $client->setAuthConfig(json_decode($config['service_account_json'], true));
        $client->addScope(\Google\Service\Drive::DRIVE_FILE);

        $driveService = new \Google\Service\Drive($service);

        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name'    => $filename,
            'parents' => [$config['folder_id']],
        ]);

        $driveService->files->create($fileMetadata, [
            'data'       => file_get_contents($localPath),
            'mimeType'   => 'application/gzip',
            'uploadType' => 'multipart',
        ]);

        return true;
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    private function markFailed(BackupDestination $destination, string $error): void
    {
        $destination->update([
            'last_used_at' => now(),
            'last_status'  => 'failed',
            'last_error'   => $error,
        ]);
    }
}