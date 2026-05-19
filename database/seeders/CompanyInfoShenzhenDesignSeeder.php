<?php

namespace Database\Seeders;

use App\Models\CompanyInfo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * 手動維護的深圳地區設計/勘察類企業名錄（非巨潮代碼），code 使用前綴 IMPSZ 避免與採集數據衝突。
 *
 * 执行：php artisan db:seed --class=CompanyInfoShenzhenDesignSeeder
 */
class CompanyInfoShenzhenDesignSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $rows = self::rows();

        foreach ($rows as $index => $row) {
            $code = 'IMPSZ'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
            $nature = '地址：'.$row['address']."\n主营业务：".$row['business'];

            CompanyInfo::query()->updateOrCreate(
                ['code' => $code],
                [
                    'abbreviation' => $row['name'],
                    'contact_number' => $row['phone'],
                    'nature_business' => $nature,
                    'capture_time' => $now,
                ]
            );
        }
    }

    /**
     * @return list<array{name: string, phone: string, address: string, business: string}>
     */
    private static function rows(): array
    {
        $path = database_path('data/shenzhen_design_companies.json');
        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
