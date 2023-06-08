<?php

App::admin_middleware('Base', 'Auth');

App::middleware('Base', 'Request');
// App::middleware('Base', '1');
// App::middleware('Base', '2');
// App::middleware('Base', '3', -1);

App::map('hi/(\d+)/([-\w]+)', 'kien/index/index/id/$1/name/$2');

// dùng route cache lấy từ các module

// middle ware cho admin

// có $_FILES, middle ware cho file

// $_POST middleware validate các trường post

// route map có thể quản trị từ quản trị lưu trong bảo cấu hình rồi render cache ra

// hệ thống role & permissions
// hệ thống validate
// hệ thống model with validate message
// hệ thống dababase
// hệ thống migrate