<?php
class req {
    private static $param = [], $method;

    public static function run() {
        self::$method = strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function toNum($num) {
        return preg_replace('/[^0-9]/', '', $num);
    }

    public static function setParam($key, $val) {
        if ($key == 'id' || $key== 'page' || substr($key, -3) == '_id') {
            $val = self::toNum($val);
            if ($key == 'page' && $val < 1) {
                $val = 1;
            }
        }
        self::$param[$key] = $val;
    }

    public static function param($key, $def = '') {
        return isset(self::$param[$key]) ? self::$param[$key]: $def;
    }

    public static function get($key, $def = '') {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $def;
    }

    public static function getAll($key, $def = []) {
        return isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) ? $_REQUEST[$key] : $def;
    }

    public static function post($key, $def = '') {
        return isset($_POST[$key]) ? $_POST[$key] : $def;
    }

    public static function postAll($key, $def = []) {
        return isset($_POST[$key]) && is_array($_POST[$key]) ? $_POST[$key] : $def;
    }

    public static function file($key) {
        return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public static function fileAll($key, $def = []) {
        return isset($_FILES[$key]) && is_array($_FILES[$key]) ? $_FILES[$key] : $def;
    }

    public static function is($method) {
        return self::$method == strtouper($method);
    }

    public static function allow(...$method) {
        if (!in_array(self::$method, array_map('strtoupper', $method))) {
            exit('Method not allowed');
        }
    }
}

class app {

    private static $mod, $map = [], $middle = [], $admin_middle = [];

    public static function run() {
        req::run();
        self::$mod = require(ROOT. 'config/module.php');
        require (ROOT. 'generate/app.php');
        self::autoload();
        self::match($_SERVER['REQUEST_URI']);
    }

    private static function autoload() {
        spl_autoload_register(function ($name) {
            $name = str_replace('\\', '/', $name);
            $_ = explode('/', $name, 2);
            if (!empty(self::$mod[$_[0]])) {
                require (MOD.$name. '.php');
            }
        });
    }

    public static function middleware($module, $name) {
        self::$middle[] = [$module, $name];
    }

    public static function admin_middleware($module, $name) {
        self::$admin_middle[] = [$module, $name];
    }

    public static function map($path, $dest) {
        self::$map[$path] = $dest;
    }

    private static function route($module, $control, $action, $is_admin = false) {

        if (!empty(self::$mod[$module]) && file_exists($file = MOD. $module .($is_admin? '/admin': ''). '/controller/'.$control.'.php')) {
            require ($file);

            $name = '\\'.$module.($is_admin? '\admin': '').'\controller\\'.$control;
            $next = [new $name(), $action];

//            $middle = $is_admin? array_merge(array_reverse(self::$admin_middle), array_reverse(self::$middle)): array_reverse(self::$middle);
//
//            foreach ($middle as $_) {
//                $next = function() use ($_, $next, $module, $control, $action) {
//                    require (MOD. $_[0] .$_[2].'/middleware/'.$_[1].'.php');
//                    $name = '\\'.$_[0].($_[2]? '\admin': '').'\middleware\\'.$_[1];
//                    [new $name(), 'handle']($next);
//                };
//            }

            if ($is_admin) {
                foreach (array_reverse(self::$admin_middle) as $_) {
                    $next = function() use ($_, $next, $module, $control, $action) {
                        require (MOD. $_[0] .'/admin/middleware/'.$_[1].'.php');
                        $name = '\\'.$_[0]. '\admin\middleware\\'.$_[1];
                        [new $name(), 'handle']($next, $module, $control, $action);
                    };
                }
            }

            foreach (array_reverse(self::$middle) as $_) {
                $next = function() use ($_, $next, $module, $control, $action) {
                    require (MOD. $_[0] .'/middleware/'.$_[1].'.php');
                    $name = '\\'.$_[0].'\middleware\\'.$_[1];
                    [new $name(), 'handle']($next, $module, $control, $action);
                };
            }

            $next();
        }
    }

    private static function match($path) {

        $path = trim(explode('?', $path, 2)[0], '/');

        foreach (self::$map as $src => $dest) {
            if (preg_match('#^'.$src.'$#', $path, $_)) {
                for ($i=count($_)-1; $i; $i--) {
                    $dest = str_replace('$'.$i, $_[$i], $dest);
                }
                $path = $dest;
                break;
            }
        }

        $_ = explode('/', $path);

        if ($_[0]) {
            if ($_[0] == 'admin') {
                array_shift($_);
                self::parse($_, true);
            }
            else {
                self::parse($_);
            }
        }
        else {
            self::route('base', 'index', 'index');
        }
    }

    private static function parse($_, $is_admin = false) {
        if (isset($_[0])) {
            $module = str_replace('-', '_', $_[0]);
            if (isset($_[1])) {
                $control = str_replace('_', '_', $_[1]);
                if (isset($_[2])) {
                    $action = $_[2];
                }
                else {
                    $action = 'index';
                }
                $len = count($_);
                if ($len > 3) {
                    if ($len % 2 == 0) {
                        $_[$len++] = '';
                    }
                    for ($i=3; $i < $len; $i+=2) {
                        Req::setParam($_[$i], $_[$i+1]);
                    }
                }
            }
            else {
                $control = 'index';
                $action = 'index';
            }
        }
        else {
            $module = 'base';
            $control = 'index';
            $action = 'index';
        }
        
        self::route($module, $control, $action, $is_admin);
    }
}