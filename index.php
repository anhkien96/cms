<?php

error_reporting(-1);
ini_set('display_errors', 1);

const ROOT = __DIR__ . '/';
const SYS = ROOT . 'system/';
const MOD = ROOT . 'module/';

require (SYS.'core.php');

App::run();