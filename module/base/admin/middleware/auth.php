<?php

namespace base\admin\middleware;

class auth {

    public function handle($next) {
        echo 'admin_auth';
        $next();
    }
}