<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');

        $categories = Category::with('parent')
            ->withCount('articles')
            ->when($status !== null && $status !== '', fn ($q) => $q->where('status', (int) $status))
            ->orderBy('sort')
            ->orderBy('id')
            ->paginate(config('admin.per_page', 10))
            ->withQueryString();

        $parentOptions = Category::getTreeOptions();

        return view('admin.categories.index', compact('categories', 'parentOptions'));
    }

    public function create(): View
    {
        $parents = Category::getTreeOptions();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                'unique:categories,slug',
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'sort' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image|max:2048',
            'status' => 'nullable|in:0,1',
            'ai_tone' => 'nullable|string|in:'.implode(',', Category::aiToneKeys()),
            'user_can_submit' => 'nullable|boolean',
        ]);

        $data = $request->only('name', 'parent_id', 'sort', 'description', 'status', 'ai_tone');
        $data['user_can_submit'] = $request->boolean('user_can_submit');
        $data['sort'] = $data['sort'] ?? 0;
        $data['status'] = isset($data['status']) ? (int) $data['status'] : Category::STATUS_ENABLED;
        if ($request->filled('slug')) {
            $data['slug'] = $request->slug;
        } else {
            $base = Str::slug($data['name']);
            $slug = $base;
            $i = 0;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $base . '-' . (++$i);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store(config('admin.upload_path', 'uploads'), 'public');
        }

        if (empty($data['ai_tone'])) {
            $data['ai_tone'] = null;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', '分类创建成功');
    }

    public function show(Category $category): View
    {
        $category->load(['parent', 'children', 'articles']);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        $parents = Category::getTreeOptions($category->id);

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'parent_id' => 'nullable|exists:categories,id',
            'sort' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image|max:2048',
            'status' => 'nullable|in:0,1',
            'ai_tone' => 'nullable|string|in:'.implode(',', Category::aiToneKeys()),
            'user_can_submit' => 'nullable|boolean',
        ]);

        $data = $request->only('name', 'parent_id', 'sort', 'description', 'status', 'ai_tone');
        $data['user_can_submit'] = $request->boolean('user_can_submit');
        if ($request->filled('slug')) {
            $data['slug'] = $request->slug;
        } else {
            $data['slug'] = Str::slug($data['name']);
        }
        if (isset($data['parent_id']) && $data['parent_id'] == $category->id) {
            $data['parent_id'] = null;
        }
        if (isset($data['status'])) {
            $data['status'] = (int) $data['status'];
        }

        if (array_key_exists('ai_tone', $data) && ($data['ai_tone'] === '' || $data['ai_tone'] === null)) {
            $data['ai_tone'] = null;
        }

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store(config('admin.upload_path', 'uploads'), 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', '分类更新成功');
    }

    public function batchAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,modify,enable,disable,allow_submit,disallow_submit',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $ids = $request->input('ids', []);
        $categories = Category::whereIn('id', $ids)->get();

        if ($request->action === 'allow_submit') {
            $count = Category::whereIn('id', $ids)->update(['user_can_submit' => true]);

            return back()->with('success', "已批量允许用户投稿：共 {$count} 个分类（未自动启用分类，禁用分类仍不会出现在前台投稿下拉）");
        }

        if ($request->action === 'disallow_submit') {
            $count = Category::whereIn('id', $ids)->update(['user_can_submit' => false]);
            $withUserArticles = (int) UserArticle::query()
                ->whereIn('category_id', $ids)
                ->distinct()
                ->count('category_id');
            $msg = "已批量取消用户投稿：共 {$count} 个分类";
            if ($withUserArticles > 0) {
                $msg .= "；其中 {$withUserArticles} 个分类下仍有用户稿件（仅影响新建投稿可选分类）";
            }

            return back()->with('success', $msg);
        }

        if ($request->action === 'enable') {
            foreach ($categories as $category) {
                $category->update(['status' => Category::STATUS_ENABLED]);
            }

            return back()->with('success', '已批量启用 ' . count($categories) . ' 个分类');
        }

        if ($request->action === 'disable') {
            foreach ($categories as $category) {
                $category->update(['status' => Category::STATUS_DISABLED]);
            }

            return back()->with('success', '已批量禁用 ' . count($categories) . ' 个分类');
        }

        if ($request->action === 'delete') {
            $deleted = 0;
            $skipped = [];
            foreach ($categories as $category) {
                if ($category->articles()->exists() || $category->userArticles()->exists()) {
                    $skipped[] = $category->name;
                    continue;
                }
                if ($category->icon) {
                    Storage::disk('public')->delete($category->icon);
                }
                $category->children()->update(['parent_id' => $category->parent_id]);
                $category->delete();
                $deleted++;
            }
            if ($deleted > 0) {
                $msg = "已批量删除 {$deleted} 个分类";
                if (!empty($skipped)) {
                    $msg .= '，以下分类因有关联文章或用户投稿未删除：' . implode('、', $skipped);
                }
                return back()->with('success', $msg);
            }
            return back()->with('error', empty($skipped) ? '请选择要删除的分类' : '所选分类均有关联文章或用户投稿，无法删除');
        }

        // modify
        $parentId = $request->input('parent_id');
        $sort = $request->input('sort');

        $updateData = [];
        if ($parentId !== null && $parentId !== '') {
            $updateData['parent_id'] = $parentId === '0' || $parentId === 0 ? null : (int) $parentId;
        }
        if ($sort !== null && $sort !== '') {
            $updateData['sort'] = (int) $sort;
        }

        if (empty($updateData)) {
            return back()->with('error', '请选择父级分类或填写排序');
        }

        foreach ($categories as $category) {
            if (isset($updateData['parent_id']) && $updateData['parent_id'] == $category->id) {
                continue;
            }
            $category->update($updateData);
        }

        return back()->with('success', '已批量修改 ' . count($categories) . ' 个分类');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->articles()->exists() || $category->userArticles()->exists()) {
            return back()->withErrors(['error' => '该分类下有关联文章或用户投稿，无法删除']);
        }

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->children()->update(['parent_id' => $category->parent_id]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', '分类已删除');
    }
}
