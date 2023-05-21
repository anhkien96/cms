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

    private static $list = [], $group = [], $map = ['jpg' => 'jpeg'];

    private static function to_group($type, $file) {
        if (empty(self::$group[$type])) {
            self::$group[$type] = [];
        }
        self::$group[$type][] = $file;
    }

    public static function get_group() {
        return self::$group;
    }

    public static function add($key, $file) {
        self::$list[$key] = $file;
        if (is_array($file)) {
            foreach ($file as $sub_file) {
                self::to_group($sub_file->type, $sub_file);
            }
        }
        else {
            self::to_group($file->type, $file);
        }
    }

    public static function debug() {
        return self::$list;
    }
}

class upload {

    private static $allow_all = false, $allow = [], $max_size = [];

    public static function run() {
        self::init_manager();

        var_dump(file_manager::debug());
        echo "\n\n";
    }

    private static function init_manager() {
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

    public static function allow_all() {
        self::$allow_all = true;
    }

    public static function allow($allow = []) {
        self::$allow = $allow;
    }

    public static function max_size($max_size = []) {
        self::$max_size = $max_size;
    }

    public static function validate() {

        if (!$_FILES) return false;

        $allow = [];
        foreach (self::$allow as $type) {
            $allow[strtolower($type)] = true;
        }

        $group = file_manager::get_group();

        foreach ($group as $list) {
            foreach ($list as $file) {
                if ((self::$allow_all || isset($allow[$file->mime])) && ($file->type == $file->mime)) {

                }
                else {
                    // bắn ra error, mô hình hóa error
                    echo 'định dang không cho phép hoặc sai định dạng<br>';
                }
            }
        }
        
        foreach (self::$max_size as $type => $val) {
            $size = 1024 * $val;
            $type = strtolower($type);
            if (isset($group[$type])) {
                foreach ($group[strtolower($type)] as $file) {
                    if ($file->size > $size) {
                        // bắn ra error
                        echo 'vượt size<br>';
                    }
                }
            }
        }

    }
}