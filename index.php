<?php

error_reporting(-1);
ini_set('display_errors', 1);

const ROOT = __DIR__ . '/';
const SYS = ROOT . 'system/';
const MOD = ROOT . 'module/';

require (SYS.'core.php');

App::run();

// hệ thống role & permissions
// hệ thống validate
// hệ thống model with validate message
// hệ thống dababase
// hệ thống migrate

// $result = $next()
// -> trả về kết quả
/*
... before next

$result = $next();

... after next

return $result;

*/

// xem cần thiết không

// tách action ra explode('__')
// check action + '__' + (post|get|put|delete)
// càn thiết ko
// hay dùng
// use App
// App::allow('post', 'get', 'put', 'delete');