<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ApplyPlayerTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            $databaseName = 'tenant_' . str_replace('-', '_', $user->tenant_id);
            Config::set('database.connections.tenant.database', $databaseName);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');
        } else {
            abort(403, 'Tenant connection failed.');
        }

        return $next($request);
    }
}
