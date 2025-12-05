<?php

namespace Polashmahmud\Dishari\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Polashmahmud\Dishari\Models\MenuGroup;
use Polashmahmud\Dishari\Models\MenuItem;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index()
    {
        $groups = MenuGroup::orderBy('order')
            ->with(['items' => function ($query) {
                $query->roots()->with('descendants'); // Recursive load
            }])
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'key' => $group->key,
                    'order' => $group->order,
                    'is_active' => $group->is_active,
                    'items' => $group->items->map(function ($item) {
                        return $this->formatMenuForTree($item);
                    })
                ];
            });

        $dirName = config('dishari.directory_name', 'dishari');

        return Inertia::render("{$dirName}/Index", [
            'menuGroups' => $groups,
        ]);
    }

    /**
     * Store a newly created menu group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:dishari_menu_groups,key',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        MenuGroup::create($validated);

        return redirect()->back()->with('success', 'Menu Group created successfully.');
    }

    /**
     * Update the specified menu group.
     */
    public function updateGroup(Request $request, MenuGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'nullable|string|max:255|unique:dishari_menu_groups,key,' . $group->id,
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $group->update($validated);

        return redirect()->back()->with('success', 'Menu Group updated successfully.');
    }

    /**
     * Remove the specified menu group.
     */
    public function destroyGroup(MenuGroup $group)
    {
        if ($group->items()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete group with menu items.');
        }

        $group->delete();

        return redirect()->back()->with('success', 'Menu Group deleted successfully.');
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_group_id' => 'nullable|exists:dishari_menu_groups,id',
            'parent_id' => 'nullable|exists:dishari_menu_items,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'permission_name' => 'nullable|string|max:255',
        ]);

        MenuItem::create($validated);

        return redirect()->back()->with('success', 'Menu created successfully.');
    }

    /**
     * Update the specified menu item.
     */
    public function update(Request $request, MenuItem $menu)
    {
        $validated = $request->validate([
            'menu_group_id' => 'nullable|exists:dishari_menu_groups,id',
            'parent_id' => 'nullable|exists:dishari_menu_items,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'permission_name' => 'nullable|string|max:255',
        ]);

        // Prevent circular reference
        if ($validated['parent_id'] === $menu->id) {
            return back()->withErrors(['parent_id' => 'A menu cannot be its own parent.']);
        }

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy(MenuItem $menu)
    {
        $menu->delete();

        return redirect()->back()->with('success', 'Menu deleted successfully.');
    }

    /**
     * Update menu order.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'groups' => 'nullable|array',
            'groups.*.id' => 'required|exists:dishari_menu_groups,id',
            'groups.*.order' => 'required|integer|min:0',

            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:dishari_menu_items,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:dishari_menu_items,id',
            'items.*.menu_group_id' => 'nullable|exists:dishari_menu_groups,id',
        ]);

        if (isset($validated['groups'])) {
            foreach ($validated['groups'] as $groupData) {
                MenuGroup::where('id', $groupData['id'])->update(['order' => $groupData['order']]);
            }
        }

        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                MenuItem::where('id', $itemData['id'])->update([
                    'order' => $itemData['order'],
                    'parent_id' => $itemData['parent_id'] ?? null,
                    'menu_group_id' => $itemData['menu_group_id'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Order updated successfully.');
    }

    /**
     * Format menu for tree display.
     */
    protected function formatMenuForTree($menu)
    {
        $data = [
            'id' => $menu->id,
            'title' => $menu->title,
            'url' => $menu->url,
            'route' => $menu->route,
            'icon' => $menu->icon,
            'order' => $menu->order,
            'is_active' => $menu->is_active,
            'permission_name' => $menu->permission_name,
            'menu_group_id' => $menu->menu_group_id,
            'parent_id' => $menu->parent_id,
            'children' => [],
        ];

        // Use descendants if loaded (to avoid N+1), otherwise fall back to children
        $children = $menu->relationLoaded('descendants')
            ? $menu->descendants
            : $menu->children;

        if ($children->isNotEmpty()) {
            $data['children'] = $children->map(function ($child) {
                return $this->formatMenuForTree($child);
            })->toArray();
        }

        return $data;
    }
}
