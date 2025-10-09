<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs que serão ignoradas na verificação de CSRF.
     */
    protected $except = [
        'api/*',
    ];
}
