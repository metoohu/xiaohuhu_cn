<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        $backupPath = storage_path('app/backups');
        $files = [];

        if (File::isDirectory($backupPath)) {
            $items = File::files($backupPath);
            foreach ($items as $file) {
                if ($file->getExtension() === 'sql') {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'size' => $file->getSize(),
                        'time' => $file->getMTime(),
                    ];
                }
            }
            usort($files, fn ($a, $b) => $b['time'] <=> $a['time']);
        }

        return view('admin.backups.index', compact('files'));
    }

    public function store(Request $request): RedirectResponse
    {
        $backupPath = storage_path('app/backups');
        if (! File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupPath . '/' . $filename;

        $driver = config('database.default');
        $config = config("database.connections.{$driver}");

        if ($driver === 'mysql') {
            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                $config['username'],
                $config['password'],
                $config['database'],
                $filepath
            );
            exec($command);
        } else {
            $tables = DB::select('SHOW TABLES');
            $dump = '';
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $dump .= "-- Table: {$tableName}\n";
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $dump .= "INSERT INTO `{$tableName}` VALUES (" . implode(',', array_map(fn ($v) => "'" . addslashes($v) . "'", (array) $row)) . ");\n";
                }
            }
            File::put($filepath, $dump);
        }

        if (File::exists($filepath)) {
            return back()->with('success', '备份创建成功');
        }

        return back()->withErrors(['error' => '备份失败，请检查 PHP 环境是否支持 mysqldump 或数据库权限']);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        if (! str_ends_with($filename, '.sql')) {
            return back()->withErrors(['error' => '无效的文件']);
        }

        $filepath = storage_path('app/backups/' . $filename);
        if (File::exists($filepath)) {
            File::delete($filepath);

            return back()->with('success', '备份已删除');
        }

        return back()->withErrors(['error' => '文件不存在']);
    }

    public function download(string $filename): StreamedResponse|RedirectResponse
    {
        $filename = basename($filename);
        $filepath = storage_path('app/backups/' . $filename);

        if (! File::exists($filepath)) {
            return back()->withErrors(['error' => '文件不存在']);
        }

        return response()->streamDownload(function () use ($filepath) {
            echo File::get($filepath);
        }, $filename, ['Content-Type' => 'application/sql']);
    }
}
