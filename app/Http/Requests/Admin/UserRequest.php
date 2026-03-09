<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admin_users', 'email')->ignore($userId),
            ],
            'password' => $userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'status' => 'required|in:0,1',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:admin_roles,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '用户名',
            'email' => '邮箱',
            'password' => '密码',
            'status' => '状态',
        ];
    }
}
