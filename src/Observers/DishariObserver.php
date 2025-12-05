<?php

namespace Polashmahmud\Dishari\Observers;

use Illuminate\Support\Facades\Cache;

class DishariObserver
{
    /**
     * Handle events after all transactions are committed.
     */
    public $afterCommit = true;

    public function saved($model)
    {
        $this->clearCache();
    }

    public function deleted($model)
    {
        $this->clearCache();
    }

    protected function clearCache()
    {
        Cache::forget(config('dishari.cache.key', 'dishari_sidebar_menu'));
    }
}
