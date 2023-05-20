<?php

namespace base\middleware;
use req;

class request {

    private function trim(&$data) {
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                self::trim($data[$key]);
            }
            else {
                $data[$key] = trim($val);
            }
        }
    }

    public function handle($next) {

        if ($_REQUEST) {
            // self::trim($_GET);
            self::trim($_POST);
            self::trim($_REQUEST);
        }

        if ($_FILES) {
            // validate, boot storage, ...
            \base\helper\upload::run();
        }
        
        $next();
    }
}