<?php

namespace Polashmahmud\Dishari\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $table = 'dishari_menu_items';

    protected $fillable = [
        'menu_group_id',
        'parent_id',
        'title',
        'url',
        'route',
        'icon',
        'order',
        'is_active',
        'permission_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the group this item belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }

    /**
     * Get the parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menus.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope to get only root menus (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Scope to get only active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format menu item for frontend.
     */
    public static function formatMenuItem($menu)
    {
        $item = [
            'id' => $menu->id,
            'title' => $menu->title,
            'href' => $menu->url,
            'route' => $menu->route,
            'icon' => $menu->icon,
            'isActive' => $menu->is_active,
            'permission' => $menu->permission_name,
        ];

        if ($menu->children->isNotEmpty()) {
            $item['items'] = $menu->children->map(function ($child) {
                return static::formatMenuItem($child);
            })->toArray();
        }

        return $item;
    }
}
