<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('articles')
            ->orderBy('sort')
            ->orderBy('id')
            ->paginate(config('admin.per_page', 10));

        $parentOptions = Category::orderBy('sort')->orderBy('id')->get();

        return view('admin.categories.index', compact('categories', 'parentOptions'));
    }

    public function create(): View
    {
        $parents = Category::whereNull('parent_id')->orderBy('sort')->get();

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
        ]);

        $data = $request->only('name', 'parent_id', 'sort', 'description');
        $data['sort'] = $data['sort'] ?? 0;
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
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('sort')->get();

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
        ]);

        $data = $request->only('name', 'parent_id', 'sort', 'description');
        if ($request->filled('slug')) {
            $data['slug'] = $request->slug;
        } else {
            $data['slug'] = Str::slug($data['name']);
        }
        if (isset($data['parent_id']) && $data['parent_id'] == $category->id) {
            $data['parent_id'] = null;
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
            'action' => 'required|in:delete,modify',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->ids)->get();

        if ($request->action === 'delete') {
            $deleted = 0;
            $skipped = [];
            foreach ($categories as $category) {
                if ($category->articles()->exists()) {
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
                    $msg .= '，以下分类因有关联文章未删除：' . implode('、', $skipped);
                }
                return back()->with('success', $msg);
            }
            return back()->with('error', empty($skipped) ? '请选择要删除的分类' : '所选分类均有关联文章，无法删除');
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
        if ($category->articles()->exists()) {
            return back()->withErrors(['error' => '该分类下有关联文章，无法删除']);
        }

        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->children()->update(['parent_id' => $category->parent_id]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', '分类已删除');
    }
}
