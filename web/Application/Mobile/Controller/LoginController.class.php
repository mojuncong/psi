<?php

namespace Mobile\Controller;

use Think\Controller;

class LoginController extends Controller {
    public function login(){
        $this->assign("uri", __ROOT__ . "/");
        $this->display();
    }
}