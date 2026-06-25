<?php
namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SwitchTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $databaseName = 'tenant_' . str_replace('-', '_', $tenant->id);
            Config::set('database.connections.tenant.database', $databaseName);
            
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');
        } else {
            DB::setDefaultConnection('mysql');
        }

        return $next($request);
    }
}