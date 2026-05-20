<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\ForbiddenWord\ForbiddenContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 前台会员编辑页实时违禁词扫描（M6/M8）。
 */
class ForbiddenContentScanController extends Controller
{
    public function store(Request $request, ForbiddenContentService $service): JsonResponse
    {
        $validated = $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable', 'string'],
            'context' => ['required', 'string', 'in:'.implode(',', config('forbidden_words.contexts', []))],
        ]);

        return response()->json(
            $service->scan($validated['fields'], $validated['context'])->toArray()
        );
    }
}
