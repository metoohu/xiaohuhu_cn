<?php

namespace App\Exceptions;

use App\Services\ForbiddenWord\Dto\ScanResult;
use Exception;
use Illuminate\Http\Request;

/**
 * 违禁内容拦截异常（携带 ScanResult 供 JSON / 表单回显）。
 */
class ForbiddenContentException extends Exception
{
    public function __construct(public ScanResult $result)
    {
        parent::__construct(implode(' ', $result->messages));
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json($this->result->toArray(), 422);
        }

        return back()
            ->withInput()
            ->withErrors(['forbidden_content' => $this->result->messages])
            ->with('forbidden_scan', $this->result->toArray());
    }
}
