<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;


class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Cek apakah sudah ada kunjungan dari IP dan user agent ini hari ini
        $alreadyVisited = Visitor::where('ip_address', $ip)
            ->where('user_agent', $userAgent)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if (! $alreadyVisited) {
            Visitor::create([
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        }

        return $next($request);
    }

}
