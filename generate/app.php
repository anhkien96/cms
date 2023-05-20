<?php

app::admin_middleware('base', 'auth');

app::middleware('base', 'request');

app::map('hi/(\d+)/([-\w]+)', 'kien/index/index/id/$1/name/$2');

// dùng route cache lấy từ các module

// middle ware cho admin

// có $_FILES, middle ware cho file

// $_POST middleware validate các trường post

// route map có thể quản trị từ quản trị lưu trong bảo cấu hình rồi render cache ra