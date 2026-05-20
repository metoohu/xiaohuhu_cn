<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScanForbiddenContentRequest;
use App\Services\ForbiddenWord\ForbiddenContentService;
use Illuminate\Http\JsonResponse;

/**
 * 后台编辑页实时违禁词扫描（M6/M8）。
 */
class ForbiddenWordScanController extends Controller
{
    public function store(ScanForbiddenContentRequest $request, ForbiddenContentService $service): JsonResponse
    {
        $fields = $request->validated('fields');
        $context = $request->validated('context');

        return response()->json(
            $service->scan($fields, $context)->toArray()
        );
    }
}
