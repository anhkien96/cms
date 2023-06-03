<?php

namespace Kien\Controller;
use Req;

class Index {

    public static function index() {

        \App::move('/admin');
        echo '<hr/>';
        \App::move('/base/index/index/id/222');
        echo '<hr/>';


        Req::allow('GET');
        
        echo 'id: '.Req::param('id').'; name: '.Req::param('name').'<br/>';
        echo 'kien index<br>';
        echo '<br/>';

        /*
        Req::query....


        */
    }
}