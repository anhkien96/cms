<?php

namespace base\controller;

class index {

    public function index() {
        \base\helper\upload::allow_all();
        \base\helper\upload::max_size(['image/png' => 6]);
        \base\helper\upload::validate();
        echo 'index /';
    }
}