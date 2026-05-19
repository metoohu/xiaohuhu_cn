<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportShenzhenDesignCompaniesCsvCommand extends Command
{
    protected $signature = 'export:shenzhen-design-companies-csv
                            {--path= : 输出路径（相对项目根或绝对路径），默认 docs/深圳勘察设计企业名录.csv}';

    protected $description = '将深圳勘察设计企业名录导出为 UTF-8 BOM CSV，并生成空格分列的 TXT（Excel 可用「数据-分列」）';

    public function handle(): int
    {
        $raw = $this->option('path');
        $path = $raw ?: 'docs/深圳勘察设计企业名录.csv';
        if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        $dataFile = database_path('data/shenzhen_design_companies.json');
        if (! File::exists($dataFile)) {
            $this->error("数据文件不存在：{$dataFile}");

            return self::FAILURE;
        }

        $rows = json_decode(File::get($dataFile), true);
        if (! is_array($rows)) {
            $this->error('JSON 解析失败');

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($path));

        $fp = fopen($path, 'wb');
        if ($fp === false) {
            $this->error("无法写入：{$path}");

            return self::FAILURE;
        }

        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($fp, ['公司名称', '联系电话', '地址', '主营业务']);

        foreach ($rows as $r) {
            fputcsv($fp, [$r['name'], $r['phone'], $r['address'], $r['business']]);
        }

        fclose($fp);

        $spacePath = preg_replace('/\.csv$/i', '-空格分列.txt', $path);
        if (is_string($spacePath) && $spacePath !== $path) {
            $lines = [];
            $lines[] = '公司名称 联系电话 地址 主营业务';
            foreach ($rows as $r) {
                $lines[] = implode(' ', [
                    $r['name'],
                    $r['phone'],
                    $r['address'],
                    $r['business'],
                ]);
            }
            File::put($spacePath, implode("\n", $lines));
            $this->info("空格分列文本：{$spacePath}");
        }

        $this->info("已写入 {$path}（共 ".count($rows).' 行）');

        return self::SUCCESS;
    }
}
