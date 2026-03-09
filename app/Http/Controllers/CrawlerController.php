<?php

namespace App\Http\Controllers;

use App\Models\CompanyInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Throwable;

class CrawlerController extends Controller
{
    /** 每个市场最多采集页数，避免单次运行过久 */
    protected const MAX_PAGES_PER_MARKET = 2;

    /** 单次运行最多为新公司请求概况接口次数，超出后本轮不再请求 */
    protected const MAX_FETCH_PROFILE_PER_RUN = 80;

    /** 请求公司概况后的延迟（秒），降低反爬风险同时控制总时长 */
    protected const DELAY_AFTER_FETCH_SECONDS = 0.4;

    /**
     * 使用巨潮资讯 JSON 接口采集公告列表及公司信息（代码 & 简称）。
     *
     * 说明：
     * - 每市场最多采集 MAX_PAGES_PER_MARKET 页，单次最多为 MAX_FETCH_PROFILE_PER_RUN 家公司请求概况接口；
     * - 数据库中已存在的 code 不再请求概况接口、不重复入库；
     * - 长时间全量采集请使用命令行：php artisan crawl:cninfo
     */
    public function crawlCninfo()
    {
        [$results, $errors] = $this->runCrawlCninfo();

        $success = empty($errors);
        $collected = count($results);
        $errorsCount = count($errors);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? "采集完成，共 {$collected} 条。"
                : "采集结束，共 {$collected} 条，错误 {$errorsCount} 条。",
            'collected' => $collected,
            'errors_count' => $errorsCount,
        ]);
    }

    /**
     * 导出公司列表为 CSV，字段：公司简称、联系电话、经营范围。
     */
    public function exportCompanies()
    {
        $rows = CompanyInfo::query()
            ->orderByDesc('id')
            ->get(['abbreviation', 'contact_number', 'nature_business']);

        $filename = 'company_info_' . date('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM，便于 Excel 正确识别中文
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['公司简称', '联系电话', '经营范围']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->abbreviation ?? '',
                    $row->contact_number ?? '',
                    $row->nature_business ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * 执行巨潮采集逻辑并返回结果（供 Artisan 命令或其它调用方使用，可避免浏览器超时）。
     *
     * @return array{0: array, 1: array} [results, errors]
     */
    public function runCrawlCninfo(): array
    {
        $results = [];
        $errors  = [];
        $profileCache = [];
        $existingCodes = array_flip(CompanyInfo::query()->pluck('code')->all());
        $fetchProfileCount = 0;
        $apiUrl = 'http://www.cninfo.com.cn/new/hisAnnouncement/query';
        $markets = [
            ['column' => 'szse', 'plate' => 'sz;szmb;szcy'],
            ['column' => 'sse', 'plate' => 'sh;shmb;shkcp'],
        ];

        foreach ($markets as $market) {
            try {
                $pageNum = 1;
                $hasMore = true;

                while ($hasMore) {
                    if ($pageNum > static::MAX_PAGES_PER_MARKET) {
                        $hasMore = false;
                        break;
                    }

                    $response = Http::asForm()
                        ->timeout(15)
                        ->post($apiUrl, [
                            'pageNum'   => $pageNum,
                            'pageSize'  => 30,
                            'column'    => $market['column'],
                            'tabName'   => 'fulltext',
                            'plate'     => $market['plate'],
                            'stock'     => '',
                            'searchkey' => '',
                            'secid'     => '',
                            'category'  => '',
                            'trade'     => '',
                            'seDate'    => now()->subYear()->format('Y-m-d') . '~' . now()->format('Y-m-d'),
                            'sortName'  => 'time',
                            'sortType'  => 'desc',
                            'isHLtitle' => 'true',
                        ]);

                    if (! $response->ok()) {
                        $errors[] = ['market' => $market, 'page' => $pageNum, 'message' => '公告接口 HTTP 请求失败，状态码：' . $response->status()];
                        break;
                    }

                    $data = $response->json();
                    if (! is_array($data) || empty($data['announcements'])) {
                        break;
                    }

                    foreach ($data['announcements'] as $item) {
                        $code         = $item['secCode'] ?? '';
                        $abbreviation = $item['secName'] ?? '';
                        $contactNumber  = null;
                        $natureBusiness = null;

                        if (empty($code)) {
                            continue;
                        }
                        if (array_key_exists($code, $existingCodes)) {
                            continue;
                        }

                        $needFetch = ! array_key_exists($code, $profileCache);
                        if ($needFetch && $fetchProfileCount >= static::MAX_FETCH_PROFILE_PER_RUN) {
                            // 已达上限
                        } elseif ($needFetch) {
                            try {
                                [$contactNumber, $natureBusiness] = $this->fetchCompanyIntroduction($code);
                                $profileCache[$code] = [$contactNumber, $natureBusiness];
                                $fetchProfileCount++;
                                usleep((int) (static::DELAY_AFTER_FETCH_SECONDS * 1_000_000));
                            } catch (Throwable $inner) {
                                $errors[] = ['code' => $code, 'abbreviation' => $abbreviation, 'message' => $inner->getMessage()];
                                Log::warning('获取公司概况失败：' . $inner->getMessage(), ['code' => $code, 'abbreviation' => $abbreviation]);
                            }
                        } else {
                            [$contactNumber, $natureBusiness] = $profileCache[$code];
                        }

                        $results[] = [
                            'code'            => $code,
                            'abbreviation'    => $abbreviation,
                            'contact_number'  => $contactNumber,
                            'nature_business' => $natureBusiness,
                            'capture_time'    => now()->format('Y-m-d H:i:s'),
                        ];
                    }

                    $hasMoreFlag = $data['hasMore'] ?? false;
                    $totalPages  = $data['totalpages'] ?? $pageNum;
                    if (! $hasMoreFlag || $pageNum >= $totalPages) {
                        $hasMore = false;
                    } else {
                        $pageNum++;
                    }
                }
            } catch (Throwable $e) {
                $errors[] = ['market' => $market, 'message' => $e->getMessage()];
                Log::warning('公告接口采集失败：' . $e->getMessage(), ['market' => $market, 'exception' => $e]);
            }
        }

        if (! empty($results)) {
            try {
                $uniqueByCode = [];
                foreach ($results as $row) {
                    $code = $row['code'] ?? null;
                    if (empty($code) || array_key_exists($code, $uniqueByCode)) {
                        continue;
                    }
                    $uniqueByCode[$code] = $row;
                }
                if (! empty($uniqueByCode)) {
                    $codes = array_keys($uniqueByCode);
                    $existingInDb = array_flip(CompanyInfo::query()->whereIn('code', $codes)->pluck('code')->all());
                    $rowsToInsert = [];
                    foreach ($uniqueByCode as $code => $row) {
                        if (! array_key_exists($code, $existingInDb)) {
                            $rowsToInsert[] = $row;
                        }
                    }
                    if (! empty($rowsToInsert)) {
                        DB::table('company_info')->insert($rowsToInsert);
                    }
                }
            } catch (Throwable $e) {
                $errors[] = ['message' => '存储到 company_info 表失败：' . $e->getMessage()];
                Log::error('存储 company_info 失败：' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return [$results, $errors];
    }

    /**
     * 调用公司概况 JSON 接口，解析联系电话与经营范围.
     *
     * 使用接口：
     * https://www.cninfo.com.cn/data20/companyOverview/getCompanyIntroduction?scode={code}
     *
     * 关键字段：
     * - F013V：联系电话
     * - F016V：经营范围
     *
     * @param  string  $code  公司代码（如 002511）
     * @return array{0: ?string, 1: ?string} [contactNumber, natureBusiness]
     */
    protected function fetchCompanyIntroduction(string $code): array
    {
        $url = 'https://www.cninfo.com.cn/data20/companyOverview/getCompanyIntroduction';

        $response = Http::timeout(15)->get($url, ['scode' => $code]);

        if (! $response->ok()) {
            throw new \RuntimeException('公司概况接口请求失败，状态码：' . $response->status());
        }

        $json = $response->json();

        // 安全地从多层结构中取出 basicInformation[0]
        $basic = $json['data']['records'][0]['basicInformation'][0] ?? null;

        if (! is_array($basic)) {
            return [null, null];
        }

        // F013V 为联系电话，F016V 为经营范围
        $contactNumber  = $basic['F013V'] ?? null;
        $natureBusiness = $basic['F016V'] ?? null;

        return [
            $contactNumber ?: null,
            $natureBusiness ?: null,
        ];
    }
}

