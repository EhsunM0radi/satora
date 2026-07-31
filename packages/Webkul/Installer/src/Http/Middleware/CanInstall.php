<?php

namespace Webkul\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\Installer\Helpers\DatabaseManager;

class CanInstall
{
    /**
     * Always allow /install as the SaaS signup wizard.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Str::contains($request->getPathInfo(), '/install')) {
            // Always allow installer routes — they serve as the SaaS signup wizard
            return $next($request);
        }

        if (! $this->isAlreadyInstalled()) {
            return redirect()->route('installer.index');
        }

        return $next($request);
    }

    /**
     * Application Already Installed.
     */
    public function isAlreadyInstalled(): bool
    {
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        if (app(DatabaseManager::class)->isInstalled()) {
            touch(storage_path('installed'));
            Event::dispatch('bagisto.installed');

            return true;
        }

        return false;
    }
}
