<?php
namespace App\index\Contr;
use \Main\Core\Controller\Module;
defined('IN_SYS')||exit('ACC Denied');
class webScoketContr{
    /**
     * 设置Session
     */
//    use Module\SessionModule;
    public function __construct(){
        php_sapi_name() === 'cli'||exit('error!');

//        $this->SessionModuleConstruct();
    }
    public function indexDo(){
//        obj('chatScoket',false,'websocket://0.0.0.0:2345', 2);

        $work = obj('\Main\Core\Workerman',false,'websocket://0.0.0.0:2345');

        $work->count = 1;
        $work->onConnect =  function($conn){
            $conn->onWebSocketConnect = function($connection , $http_header){
                session_start();
                $d = obj('userModel')->selRow(' `account`="'.$_SESSION['account'].'" and `passwd`="'.$_SESSION['passwd'].'" ');
                $_SESSION['passwd'] = 3333;
                session_commit();
                if(!$d)
                    $connection->close();
            };
        };
        $work->onMessage = function($ttt, $data){
            $data = json_decode($data,true);
            if($data['act'] === 'login'){
                $d = obj('userModel')->selRow(' `account`="'.$data['account'].'" and `passwd`="'.$data['passwd'].'" ');
                if($d){
                    $ttt->send('登入成功!');
                    $ttt->loginSign = true;
                }else {
                    $ttt->send('登入失败!');
                    $ttt->close();
                }
                return true;
            }

            if(isset($ttt->loginSign)){
                if($data['act'] === 'select'){
                    $ttt->send('select success!');
                }
            }else $ttt->send('未登入!');
//            statistic();
        };
        $work->runAll();
    }
}