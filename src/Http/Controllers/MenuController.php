<?php

namespace Polashmahmud\Menu\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Polashmahmud\Menu\Models\Menu;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index()
    {
        $menus = Menu::with('children')
            ->roots()
            ->get()
            ->map(function ($menu) {
                return $this->formatMenuForTree($menu);
            });

        return Inertia::render('Menu::Index', [
            'menuList' => $menus,
        ]);
    }

    /**
     * Store a newly created menu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menus,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($validated);

        return redirect()->back()->with('success', 'Menu created successfully.');
    }

    /**
     * Update the specified menu.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menus,id',
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Prevent circular reference
        if ($validated['parent_id'] === $menu->id) {
            return back()->withErrors(['parent_id' => 'A menu cannot be its own parent.']);
        }

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(Menu $menu)
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
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:menus,id',
            'menus.*.order' => 'required|integer|min:0',
            'menus.*.parent_id' => 'nullable|exists:menus,id',
        ]);

        foreach ($validated['menus'] as $menuData) {
            Menu::where('id', $menuData['id'])->update([
                'order' => $menuData['order'],
                'parent_id' => $menuData['parent_id'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Menu order updated successfully.');
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
            'icon' => $menu->icon,
            'order' => $menu->order,
            'is_active' => $menu->is_active,
            'children' => [],
        ];

        if ($menu->children->isNotEmpty()) {
            $data['children'] = $menu->children->map(function ($child) {
                return $this->formatMenuForTree($child);
            })->toArray();
        }

        return $data;
    }
}
