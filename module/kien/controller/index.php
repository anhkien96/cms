<?php

namespace kien\controller;
use req;

class index {

    public static function index() {
        req::allow('GET');
        
        echo 'id: '.req::param('id').'; name: '.req::param('name').'<br/>';
        echo 'kien index<br>';
        echo '<br/>';

        /*
        Req::query....


        */
    }
}