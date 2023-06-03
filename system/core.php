<?php
class Req {
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

class App {

    private static $mod, $is_admin, $module, $control, $action, $map = [], $middle = [], $admin_middle = [], $middle_pos = 0, $admin_middle_pos = 0, $is_sort = [false, false];

    public static function run() {
        Req::run();
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

    public static function middleware($module, $name, $pos = null) {
        if ($pos != null) {
            if ($pos > self::$middle_pos) {
                self::$middle_pos = $pos;
            }
        }
        else {
            $pos = (self::$middle_pos = self::$middle_pos + 10);
        }
        self::$middle[] = [$module, $name, $pos];
    }

    public static function admin_middleware($module, $name, $pos = null) {
        if ($pos != null) {
            if ($pos > self::$admin_middle_pos) {
                self::$admin_middle_pos = $pos;
            }
        }
        else {
            $pos = (self::$admin_middle_pos = self::$admin_middle_pos + 10);
        }
        self::$admin_middle[] = [$module, $name, $pos];
    }

    public static function map($path, $dest) {
        self::$map[$path] = $dest;
    }

    public static function set_module($module) {
        self::$module = $module;
    }

    public static function get_module($module) {
        return $module;
    }

    public static function set_controller($control) {
        self::$control = $control;
    }

    public static function get_controller($control) {
        return $control;
    }

    public static function set_action($action) {
        self::$action = $action;
    }

    public static function get_action($action) {
        return $action;
    }

    public static function move($path) {
        self::match($path);
    }

    private static function uri2class($val) {
        $val = str_replace(['-', '_'], ' ', $val);
        $val = ucwords($val);
        $val = str_replace(' ', '', $val);
        return $val;
    }

    private static function route() {

        $next = function() {
            $module = self::uri2class(self::$module);
            $control = self::uri2class(self::$control);
            // $action = self::uri2class(self::$action);
            if (!empty(self::$mod[$module]) && file_exists($file = MOD. $module .(self::$is_admin? '/Admin': ''). '/Controller/'.$control.'.php')) {
                require_once ($file);
                $name = '\\'.$module.(self::$is_admin? '\Admin': '').'\Controller\\'.$control;
                [new $name(), self::$action](); 
            }
        };

        if (self::$is_admin) {
            if (!self::$is_sort[1]) {
                self::$is_sort[1] = true;

                usort(self::$admin_middle, function($m1, $m2) {
                    return $m2[2] - $m1[2];
                });
            }
            
            foreach (self::$admin_middle as $_) {
                $next = function() use ($_, $next) {
                    require_once (MOD. $_[0] .'/Admin/Middleware/'.$_[1].'.php');
                    $name = '\\'.$_[0]. '\Admin\Middleware\\'.$_[1];
                    [new $name(), 'handle']($next);
                };
            }
        }

        if (!self::$is_sort[0]) {
            self::$is_sort[0] = true;

            usort(self::$middle, function($m1, $m2) {
                return $m2[2] - $m1[2];
            });
        }
    
        foreach (self::$middle as $_) {
            $next = function() use ($_, $next) {
                require_once (MOD. $_[0] .'/Middleware/'.$_[1].'.php');
                $name = '\\'.$_[0].'\Middleware\\'.$_[1];
                [new $name(), 'handle']($next);
            };
        }

        $next();
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
                self::$is_admin = true;
            }
            else {
                self::$is_admin = false;
            }
            self::parse($_);
        }
        else {
            self::set_module('base');
            self::set_controller('index');
            self::set_action('index');

            self::route();
        }
    }

    private static function parse($_) {
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
        
        self::set_module($module);
        self::set_controller($control);
        self::set_action($action);

        self::route();
    }
}