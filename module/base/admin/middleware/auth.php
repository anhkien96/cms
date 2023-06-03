<?php

namespace Base\Admin\Middleware;

class Auth {

    public function handle($next) {
        echo 'admin_auth';
        $next();
    }
}