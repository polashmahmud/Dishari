<?php

namespace Polashmahmud\Dishari\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuGroup extends Model
{
    protected $table = 'dishari_menu_groups';

    protected $fillable = [
        'name',
        'key',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the menu items for the group.
     * Only root items (parent_id is null) are fetched directly,
     * as children are nested under them.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_group_id')
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Scope to get only active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get menu tree structure grouped by Menu Groups.
     */
    public static function getTree()
    {
        return static::active()
            ->orderBy('order')
            ->with(['items' => function ($query) {
                $query->active()->with('activeDescendants');
            }])
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'key' => $group->key,
                    'items' => $group->items->map(function ($item) {
                        return MenuItem::formatMenuItem($item);
                    })->toArray()
                ];
            });
    }
}
