<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        'api/forgot-password',
        '/api/login',
        '/api/register',
        '/api/logout',
        '/api/events',
        '/api/events/*',
        '/api/attendeed/upload',
        '/api/attendees',
        '/api/attendees/*',
        '/api/feedbacks',
        '/api/feedbacks/*',
        '/api/logout'
    ];
}
