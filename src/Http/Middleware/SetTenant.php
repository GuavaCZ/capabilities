<?php

namespace Guava\Capabilities\Http\Middleware;

use Closure;

class SetTenant {
    /**
     * Set the proper Bouncer scope for the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


        return $next($request);
    }
}
