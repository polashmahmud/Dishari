<?php

namespace Polashmahmud\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'url',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the child menus.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
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
     * Get menu tree structure.
     */
    public static function getTree()
    {
        return static::roots()
            ->active()
            ->with('descendants')
            ->get()
            ->map(function ($menu) {
                return static::formatMenuItem($menu);
            });
    }

    /**
     * Format menu item for frontend.
     */
    protected static function formatMenuItem($menu)
    {
        $item = [
            'id' => $menu->id,
            'title' => $menu->title,
            'href' => $menu->url,
            'icon' => $menu->icon,
            'isActive' => $menu->is_active,
        ];

        if ($menu->children->isNotEmpty()) {
            $item['items'] = $menu->children->map(function ($child) {
                return static::formatMenuItem($child);
            })->toArray();
        }

        return $item;
    }
}
