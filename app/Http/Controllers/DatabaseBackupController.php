<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    /**
     * Stream a full mysqldump of the application database as a .sql download.
     */
    public function download(): StreamedResponse
    {
        // Only Administrador (nivel_id = 1) can download backups
        abort_unless(auth()->user()?->nivel_id === 1, 403);

        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename = $database . '_backup_' . now()->format('Ymd_His') . '.sql';

        $passwordArg = !empty($password) ? '--password=' . escapeshellarg($password) : '';

        // Detect mysqldump path on Windows (Laragon/XAMPP) since it's often not in PATH
        $mysqldumpPath = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $laragonPaths = glob('C:\laragon\bin\mysql\*\bin\mysqldump.exe');
            if (!empty($laragonPaths)) {
                $mysqldumpPath = escapeshellarg($laragonPaths[0]);
            } elseif (file_exists('C:\xampp\mysql\bin\mysqldump.exe')) {
                $mysqldumpPath = escapeshellarg('C:\xampp\mysql\bin\mysqldump.exe');
            }
        }

        // Build the mysqldump command
        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers %s',
            $mysqldumpPath,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordArg,
            escapeshellarg($database)
        );

        return response()->streamDownload(function () use ($command) {
            $process = popen($command, 'r');
            while (!feof($process)) {
                echo fread($process, 8192);
                ob_flush();
                flush();
            }
            pclose($process);
        }, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import an uploaded .sql file into the application database.
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'mimes:sql,txt', 'max:102400'], // max 100 MB
        ]);

        $file = $request->file('sql_file');

        // Extra safety: verify the original extension is .sql
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return back()->with('error', 'El archivo debe tener extensión .sql');
        }

        $sql = file_get_contents($file->getRealPath());

        if (empty(trim($sql))) {
            return back()->with('error', 'El archivo .sql está vacío.');
        }

        try {
            // Disable foreign key checks during import
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
            DB::unprepared($sql);
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }

        return back()->with('success', 'Base de datos restaurada correctamente.');
    }
}
