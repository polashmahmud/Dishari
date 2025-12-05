<?php

namespace Polashmahmud\Menu\Models;

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
}
