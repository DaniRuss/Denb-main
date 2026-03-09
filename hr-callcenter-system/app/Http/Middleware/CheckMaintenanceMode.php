<?php
// app/Http/Middleware/CheckMaintenanceMode.php
namespace App\Http\Middleware;

use Closure;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Skip maintenance check for admin routes and login
        if ($request->is('admin*') || $request->is('login*') || $request->is('livewire*')) {
            return $next($request);
        }

        $maintenanceMode = SiteSetting::get('maintenance_mode', false);

        // SiteSetting returns string '1' or '0' for boolean group if not casted or handled
        if ($maintenanceMode === '1' || $maintenanceMode === true || $maintenanceMode === 1) {
            return response()->view('portal.maintenance');
        }

        return $next($request);
    }
}
