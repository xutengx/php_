<?php

namespace App\admin\Contr;
use \Main\Core\Controller;
defined('IN_SYS') || exit('ACC Denied');

class indexContr extends Controller\HttpController {
    public function construct(){
        $re = obj('adminObj');
    }
    public function indexDo() {
//        $a = obj('menuTemplate')->getCate();
        
        $this->assign('session', $_SESSION);
        $this->display();
    }
}