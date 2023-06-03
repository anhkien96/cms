<?php

namespace base\helper;

class file {

    private $data = [];

    private function __construct() {}

    public static function create($tmp_name, $name, $size, $type) {
        $info = pathinfo($name);
        $file = new self();

        $file->tmp_name = $tmp_name;
        $file->size = $size;
        $file->ext = strtolower($info['extension']);
        $file->base = $info['filename'];
        $file->name = $file->base . '.' . $file->ext;
        $file->mime = mime_content_type($file->tmp_name);
        $file->type = $type;

        return $file;
    }

    public function __set($key, $val) {
        $this->data[$key] = $val;
    }

    public function __get($key) {
        return $this->data[$key];
    }
}

class file_manager {

    private static $list = [], $group = [];

    private static function toGroup($type, $file) {
        if (empty(self::$group[$type])) {
            self::$group[$type] = [];
        }
        self::$group[$type][] = $file;
    }

    // public static function getGroup() {
    //     return self::$group;
    // }

    public static function add($key, $file) {
        self::$list[$key] = $file;
        // if (is_array($file)) {
        //     foreach ($file as $sub_file) {
        //         self::toGroup($sub_file->type, $sub_file);
        //     }
        // }
        // else {
        //     self::toGroup($file->type, $file);
        // }
    }

    public static function getAll() {
        return self::$list;
    }
}

class validate {

    private static $rule = [], $allow = '#.*?#', $max_size = [], $map = ['jpg' => 'jpeg'], $error = [];

    public static function run() {
        self::initFileManager();
        var_dump(file_manager::getAll());
    }

    private static function initFileManager() {
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                $len = count($file['name']);
                $file_list = [];
                for ($i=0; $i<$len; $i++) {
                    if (!is_array($file['name'][$i])) {
                        $file_list[] = file::create($file['tmp_name'][$i], $file['name'][$i], $file['size'][$i], $file['type'][$i]);
                    }
                }
                file_manager::add($key, $file_list);
            }
            else {
                file_manager::add($key, file::create($file['tmp_name'], $file['name'], $file['size'], $file['type']));
            }
        }
    }

    public static function rule($key, $rule = []) {
        self::$rule[$key] = $rule;
    }

    public static function validate() {
        foreach (self::$rule as $key => $rule) {

        }
    }
}