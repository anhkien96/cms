<?php

namespace Base\Helper;

class File {

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

class FileManager {

    private static $list = [], $group = [];

    private static function toGroup($type, $file) {
        if (empty(self::$group[$type])) {
            self::$group[$type] = [];
        }
        self::$group[$type][] = $file;
    }

    public static function getGroup() {
        return self::$group;
    }

    public static function add($key, $file) {
        self::$list[$key] = $file;
        if (is_array($file)) {
            foreach ($file as $sub_file) {
                self::toGroup($sub_file->type, $sub_file);
            }
        }
        else {
            self::toGroup($file->type, $file);
        }
    }

    public static function getAll() {
        return self::$list;
    }
}

class Upload {

    private static $allow = '#.*?#', $max_size = [], $map = ['jpg' => 'jpeg'], $error = [];

    public static function run() {
        self::initManager();

        var_dump(FileManager::getAll());
    }

    private static function initManager() {
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                $len = count($file['name']);
                $file_list = [];
                for ($i=0; $i<$len; $i++) {
                    if (!is_array($file['name'][$i])) {
                        $file_list[] = file::create($file['tmp_name'][$i], $file['name'][$i], $file['size'][$i], $file['type'][$i]);
                    }
                }
                FileManager::add($key, $file_list);
            }
            else {
                FileManager::add($key, file::create($file['tmp_name'], $file['name'], $file['size'], $file['type']));
            }
        }
    }

    private static function map($val) {
        return isset(self::$map[$val])? self::$map[$val]: $val;
    }

    public static function allow($allow = []) {
        // self::$allow = '#' . implode('|', array_map([self, 'map'], $allow)) . '#i';
        self::$allow = array_map(function() {

        }, $allow);

        // sai ngay từ tư duy rồi, validate rule theo từng key, mô hình hóa tổng thể validate theo request
    }

    public static function max_size($max_size = []) {
        self::$max_size = $max_size;
    }

    public static function validate() {

        if (!$_FILES) return false;

        $list = FileManager::getAll();

        for ($list as $val) {
            if (is_array($val)) {

            }
            else {

            }
        }


        // $allow = [];
        // foreach (self::$allow as $type) {
        //     $allow[strtolower($type)] = true;
        // }

        // $group = FileManager::getGroup();

        // foreach ($group as $list) {
        //     foreach ($list as $file) {
        //         if ((self::$allow_all || isset($allow[$file->mime])) && ($file->type == $file->mime)) {

        //         }
        //         else {
        //             // bắn ra error, mô hình hóa error
        //             echo 'định dang không cho phép hoặc sai định dạng<br>';
        //         }
        //     }
        // }
        
        // foreach (self::$max_size as $type => $val) {
        //     $size = 1024 * $val;
        //     $type = strtolower($type);
        //     if (isset($group[$type])) {
        //         foreach ($group[strtolower($type)] as $file) {
        //             if ($file->size > $size) {
        //                 // bắn ra error
        //                 echo 'vượt size<br>';
        //             }
        //         }
        //     }
        // }

    }
}