<?php

declare(strict_types = 1);
namespace Main\Core\Middleware;
defined('IN_SYS') || exit('ACC Denied');

use Main\Core\Middleware;
use Main\Core\Session;

/**
 * 开启 session
 */
class StartSession extends Middleware {

    protected $except = []; 
    
    public function handle(Session $Session) {
        
    }
}
