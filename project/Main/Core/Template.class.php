<?php
/**
 * 引入html公用组件, 原则上动态赋值应该有且仅有$_SESSION & $_COOKIE 及公用值(如时间,天气)
 * User: Administrator
 * Date: 2016/1/21 0021
 * Time: 19:17
 */
namespace Main\Core;
class Template {
    // 跳转中间页面
    private   $jumpTo =  'jumpTo';
    // ajax发送中的蒙层
    private   $ajaxSending =  'ajaxSending';
    // 数据检测
    private   $submitData =  'submitData';

    public   function show($file){
        include ROOT.'Application/'.APP.'/View/template/'.$file.'.html';
        return true;
    }
    // 跳转中间页
    public   function jumpTo($message, $jumpUrl='index?path=index/index/indexDo/'){
        $waitSecond = 3;
        include ROOT.'Main/Views/tpl/'.$this->jumpTo.'.html';
        exit;
    }
    // 自动加载静态文件
    public   function includeFiles(){
        // 声明于 Conf.class.php->includeFiles() ;
        echo VIEW_INCLUDE;
        $this->ajaxSending();
    }
    /**
     *  ajax发送过程蒙层
     *  Controller->display();
     */
    public   function ajaxSending(){
        include ROOT.'Main/Views/tpl/'.$this->ajaxSending.'.html';
    }
    /**
     *
     */
    public   function submitData(){
        include ROOT.'Main/Views/tpl/'.$this->submitData.'.html';
    }
}