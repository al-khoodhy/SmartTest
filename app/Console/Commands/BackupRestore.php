<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupRestore extends Command
{
    protected $signature = 'db:backup-restore
        {action : Use "backup" or "restore"}
        {filename? : When restoring, the backup file to load (relative to storage/app/backups)}
        {--connection= : Database connection name from config/database.php}
        {--force : Skip the confirmation prompt when restoring}';

    protected $description = 'Create or restore MySQL backups stored under storage/app/backups';

    public function handle(): int
    {
        $action = strtolower($this->argument('action'));

        return match ($action) {
            'backup'  => $this->performBackup(),
            'restore' => $this->performRestore(),
            default   => $this->invalidAction($action),
        };
    }

    protected function performBackup(): int
    {
        $config = $this->resolveConnection();
        if (!$config) {
            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
            $this->error("Unable to create backup directory at {$backupDir}.");
            return self::FAILURE;
        }

        $timestamp   = now()->format('Ymd_His');
        $filename    = "{$config['database']}_{$timestamp}.sql";
        $destination = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $dumpCommand = sprintf(
            'mysqldump --no-tablespaces --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg($config['port'] ?? 3306),
            escapeshellarg($config['username']),
            escapeshellarg($config['password'] ?? ''),
            escapeshellarg($config['database']),
            escapeshellarg($destination)
        );

        $process = Process::fromShellCommandline($dumpCommand);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Database backup failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
            if (file_exists($destination)) {
                unlink($destination);
            }
            return self::FAILURE;
        }

        $this->info("Backup created at: {$destination}");
        return self::SUCCESS;
    }

    protected function performRestore(): int
    {
        $config = $this->resolveConnection();
        if (!$config) {
            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            $this->error("Backup directory not found at {$backupDir}.");
            return self::FAILURE;
        }

        $filename = $this->argument('filename');
        $available = collect(glob($backupDir . DIRECTORY_SEPARATOR . '*.sql'))->sortDesc()->values();

        if ($available->isEmpty()) {
            $this->error('No backup files found in storage/app/backups.');
            return self::FAILURE;
        }

        if (!$filename) {
            $choices = $available->map(fn ($path) => basename($path))->all();
            $filename = $this->choice('Select a backup to restore', $choices);
        }

        $path = $filename;
        if (!$this->isAbsolutePath($filename) && !Str::startsWith($filename, './')) {
            $path = $backupDir . DIRECTORY_SEPARATOR . $filename;
        }

        if (!file_exists($path)) {
            $this->error("Backup file not found at {$path}.");
            return self::FAILURE;
        }

        $realPath   = realpath($path);
        $backupRoot = realpath($backupDir);
        if (!$realPath || !$backupRoot || !Str::startsWith($realPath, $backupRoot)) {
            $this->error('Backup file must reside within the storage/app/backups directory.');
            return self::FAILURE;
        }

        $path = $realPath;

        if (!$this->option('force') && !$this->confirm('Restoring will overwrite the current database. Continue?')) {
            $this->comment('Restore cancelled.');
            return self::SUCCESS;
        }

        $restoreCommand = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg($config['port'] ?? 3306),
            escapeshellarg($config['username']),
            escapeshellarg($config['password'] ?? ''),
            escapeshellarg($config['database']),
            escapeshellarg($path)
        );

        $process = Process::fromShellCommandline($restoreCommand);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Database restore failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
            return self::FAILURE;
        }

        $this->info("Database restored successfully from {$path}");
        return self::SUCCESS;
    }

    protected function resolveConnection(): ?array
    {
        $connectionName = $this->option('connection') ?: config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (!$config) {
            $this->error("Database connection [{$connectionName}] is not configured.");
            return null;
        }

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('Only the MySQL driver is supported by this command.');
            return null;
        }

        return $config;
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Unknown action [{$action}]. Use \"backup\" or \"restore\".");
        return self::INVALID;
    }

    protected function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        return Str::startsWith($path, ['/','\\']) || preg_match('/^[A-Za-z]:\\\\/', $path) === 1;
    }
}
