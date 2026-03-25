<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminMenuItem;
use App\Models\Admin\AdminOperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AdminMenuItemController extends Controller
{
    public function index(): View
    {
        $tree = AdminMenuItem::managementTree();
        $routeNames = $this->adminRouteNames();

        return view('admin.menu-items.index', compact('tree', 'routeNames'));
    }

    public function create(Request $request): View
    {
        $parentId = $request->integer('parent_id') ?: null;
        $parent = $parentId ? AdminMenuItem::find($parentId) : null;
        $routeNames = $this->adminRouteNames();
        $parentOptions = $this->flatParentOptions();

        return view('admin.menu-items.create', compact('parent', 'parentId', 'routeNames', 'parentOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $parentId = $data['parent_id'];
        $maxSort = AdminMenuItem::query()->where('parent_id', $parentId)->max('sort');
        $data['sort'] = $data['sort'] ?? (($maxSort ?? 0) + 10);

        AdminMenuItem::create($data);

        AdminOperationLog::log('新增后台菜单: '.$data['title'], '菜单管理');

        return redirect()->route('admin.menu-items.index')->with('success', '菜单已创建');
    }

    public function edit(AdminMenuItem $admin_menu_item): View
    {
        $routeNames = $this->adminRouteNames();
        $parentOptions = $this->flatParentOptions(excludeId: $admin_menu_item->id);

        return view('admin.menu-items.edit', ['item' => $admin_menu_item, 'routeNames' => $routeNames, 'parentOptions' => $parentOptions]);
    }

    public function update(Request $request, AdminMenuItem $admin_menu_item): RedirectResponse
    {
        $data = $this->validatedData($request, $admin_menu_item->id);

        if ($data['parent_id'] === $admin_menu_item->id) {
            return back()->withErrors(['parent_id' => '不能将自身设为上级菜单'])->withInput();
        }

        if ($data['parent_id'] !== null && $this->descendantIds($admin_menu_item)->contains((int) $data['parent_id'])) {
            return back()->withErrors(['parent_id' => '不能将下级菜单设为上级'])->withInput();
        }

        $admin_menu_item->update($data);

        AdminOperationLog::log('编辑后台菜单: '.$admin_menu_item->title, '菜单管理');

        return redirect()->route('admin.menu-items.index')->with('success', '菜单已更新');
    }

    public function destroy(AdminMenuItem $admin_menu_item): RedirectResponse
    {
        $title = $admin_menu_item->title;
        DB::transaction(function () use ($admin_menu_item) {
            $this->deleteRecursive($admin_menu_item);
        });

        AdminOperationLog::log('删除后台菜单: '.$title, '菜单管理');

        return redirect()->route('admin.menu-items.index')->with('success', '菜单已删除（含子菜单）');
    }

    public function moveUp(AdminMenuItem $admin_menu_item): RedirectResponse
    {
        $this->swapWithSibling($admin_menu_item, -1);

        return back();
    }

    public function moveDown(AdminMenuItem $admin_menu_item): RedirectResponse
    {
        $this->swapWithSibling($admin_menu_item, 1);

        return back();
    }

    protected function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'parent_id' => $request->filled('parent_id') ? (int) $request->input('parent_id') : null,
        ]);

        $parentRule = 'nullable|exists:admin_menu_items,id';
        if ($ignoreId) {
            $parentRule .= '|not_in:'.$ignoreId;
        }

        $validated = $request->validate([
            'parent_id' => $parentRule,
            'title' => 'nullable|string|max:100',
            'route_name' => 'nullable|string|max:191',
            'url' => 'nullable|string|max:500',
            'active_pattern' => 'nullable|string|max:191',
            'sort' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
            'is_divider' => 'nullable|boolean',
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?? null;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_divider'] = $request->boolean('is_divider');

        if ($validated['is_divider']) {
            $validated['route_name'] = null;
            $validated['url'] = null;
            $validated['active_pattern'] = null;
        }

        if ($validated['title'] === null) {
            $validated['title'] = '';
        }

        return $validated;
    }

    protected function deleteRecursive(AdminMenuItem $item): void
    {
        foreach ($item->children()->get() as $child) {
            $this->deleteRecursive($child);
        }
        $item->delete();
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function descendantIds(AdminMenuItem $item): \Illuminate\Support\Collection
    {
        $ids = collect();
        foreach (AdminMenuItem::query()->where('parent_id', $item->id)->get() as $child) {
            $ids->push($child->id);
            $ids = $ids->merge($this->descendantIds($child));
        }

        return $ids;
    }

    protected function swapWithSibling(AdminMenuItem $item, int $direction): void
    {
        $siblings = AdminMenuItem::query()
            ->where('parent_id', $item->parent_id)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $idx = $siblings->search(fn (AdminMenuItem $m) => $m->id === $item->id);
        if ($idx === false) {
            return;
        }

        $swapIdx = $idx + $direction;
        if ($swapIdx < 0 || $swapIdx >= $siblings->count()) {
            return;
        }

        $other = $siblings[$swapIdx];
        $t = $item->sort;
        $item->sort = $other->sort;
        $other->sort = $t;
        $item->save();
        $other->save();
    }

    /**
     * @return list<string>
     */
    protected function adminRouteNames(): array
    {
        return collect(Route::getRoutes()->getRoutesByName())
            ->keys()
            ->filter(fn ($n) => is_string($n) && str_starts_with($n, 'admin.'))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|null, label: string}>
     */
    protected function flatParentOptions(?int $excludeId = null): array
    {
        $excludeIds = collect();
        if ($excludeId !== null) {
            $ex = AdminMenuItem::find($excludeId);
            if ($ex) {
                $excludeIds = $this->descendantIds($ex)->push($excludeId);
            }
        }

        $options = [['value' => '', 'label' => '（顶级菜单）']];
        $tree = AdminMenuItem::managementTree();

        $walk = function ($nodes, $depth = 0) use (&$options, &$walk, $excludeIds) {
            foreach ($nodes as $node) {
                if ($excludeIds->contains($node->id)) {
                    continue;
                }
                if ($node->is_divider) {
                    continue;
                }
                $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';
                $options[] = [
                    'value' => $node->id,
                    'label' => $prefix.($node->title ?: '（无标题）'),
                ];
                if ($node->children->isNotEmpty()) {
                    $walk($node->children, $depth + 1);
                }
            }
        };

        $walk($tree);

        return $options;
    }
}
