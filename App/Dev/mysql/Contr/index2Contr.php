<?php

declare(strict_types = 1);
namespace App\Dev\mysql\Contr;

use \Main\Core\Controller\HttpController;
use \App\Dev\mysql\Model;

/*
 * 数据库开发测试类
 */

class index2Contr extends HttpController {

    private $fun_array = [
        '多行查询, 参数为数组形式, 非参数绑定' => 'test_1',
        '单行查询, select参数为string形式, (?)参数绑定' => 'test_2',
        '多条件分组查询, 参数为数组形式, 聚合表达式, 非参数绑定' => 'test_3',
        '简易单行更新, 参数为数组形式, 参数绑定, 返回受影响的行数' => 'test_4',
        '简易单行插入, 参数为数组形式, 参数绑定, 返回bool' => 'test_5',
        '静态调用model, where参数数量为2, 3, where的in条件, where闭包' => 'test_6',
        '静态调用model, where的between条件, having条件, 参数绑定,' => 'test_7',
        '静态调用model, whereRaw(不处理)的and or嵌套条件, 参数绑定,' => 'test_8',
        '闭包事务,' => 'test_9',
        'union,3种链接写法' => 'test_10',
        'where exists,3种链接写法' => 'test_11',
        'model中的pdo使用(原始sql)' => 'test_12',
        'model中的pdo使用 使用pdo的参数绑定, 以不同的参数重复执行同一语句' => 'test_13',
        '链式操作 参数绑定, 以不同的参数重复执行同一语句' => 'test_14',
    ];

    public function indexDo() {
        $i = 1;
        echo '<pre> ';
        foreach ($this->fun_array as $k => $v) {
            echo $i.' . '.$k . ' : <br>';
//            $this->$v();          // 执行
            run($this, $v);         // 依赖注入执行
            echo '<br><hr>';
            $i++;
        }
    }
    
    private function test_1() {
        $obj = obj(Model\visitorInfoDev::class);
        $sql = $obj->select(['id', 'name', 'phone'])
            ->where( 'id', '>', '101')
            ->where('id' ,'<', '104')
            ->order('id','desc')
            ->getAllToSql();
        var_dump($sql);

        $res = $obj->select(['id', 'name', 'phone'])
            ->where( 'id', '>', '101')
            ->where('id' ,'<', '104')
            ->order('id','desc')
            ->getAll();
        var_dump($res);
    }

    private function test_2() {
        $obj = obj(Model\visitorInfoDev::class);
      
        $sql = $obj->select('id,name,phone')
            ->where( 'scene', '&', '?' )
            ->getRowToSql(['1']);
        var_dump($sql);

        $res = $obj->select('id,name,phone')
            ->where( 'scene', '&', '?' )
            ->getRow(['1']);
        var_dump($res);
    }
    private function test_3() {
        $obj = obj(Model\visitorInfoDev::class);
      
        $sql = $obj->select(['id', 'name', 'phone','count(id)','sum(id) as sum'])
            ->where('scene' , '&', '1' )
            ->where('name','like', '%t%')
            ->group(['phone'])
            ->getAllToSql();
        var_dump($sql);

        $res = $obj->select(['id', 'name', 'phone','count(id)','sum(id) as sum'])
            ->where('scene' , '&', '1' )
            ->where('name','like', '%t%')
            ->group(['phone'])
            ->getAll();
        var_dump($res);
    }
    private function test_4() {
        $obj = obj(Model\visitorInfoDev::class);
      
        $sql = $obj
            ->data(['name' => 'autoUpdate'])
            ->where('scene' ,'&', ':scene_1' )
            ->limit(1)
            ->updateToSql([':scene_1' => '1']);
        var_dump($sql);

        $res = $obj
            ->data(['name' => 'autoUpdate'])
            ->where('scene' ,'&', ':scene_1' )
            ->limit(1)
            ->update([':scene_1' => '1']);
        var_dump($res);
    }
    private function test_5() {
        $obj = obj(Model\visitorInfoDev::class);
      
        $sql = $obj
            ->data(['name' => ':autoUpdate'])
            ->insertToSql([':autoUpdate' => 'autoInsertName']);
        var_dump($sql);

        $res = $obj
            ->data(['name' => ':autoUpdate'])
            ->insert([':autoUpdate' => 'autoInsertName']);
        var_dump($res);
    }
    private function test_6() {
        $res = Model\visitorInfoDev::select(['id', 'name', 'phone'])
            ->where( 'scene', '&', ':scene_1')
            ->where( 'phone', '13849494949')
            ->whereIn('id',['100','101','102','103'])
            ->orWhere(function($queryBuiler){
                $queryBuiler->where('id', '102')->where('name','xuteng')
                        ->orWhere(function($re){
                            $re->where('phone','13849494949')
                                    ->whereNotNull('id');
                        });
            })
            ->getAll([':scene_1' => '1']);
        $sql = Model\visitorInfoDev::getLastSql();
        
        var_dump($sql);
        var_dump($res);
    }
    private function test_7() {
        $res = Model\visitorInfoDev::select(['id', 'name', 'phone'])
            ->where( 'scene', '&', ':scene_1')
            ->whereBetween('id', ['100','103' ])
            ->havingIn('id',['100','102'])
            ->getAll([':scene_1' => '1']);
        $sql = Model\visitorInfoDev::getLastSql();
        
        var_dump($sql);
        var_dump($res);
    }
    private function test_8() {
        $res = Model\visitorInfoDev::select(['id', 'name', 'phone'])
            ->whereBetween('id','100','103')
            ->whereRaw('id = "106"AND `name` = "xuteng1" OR ( note = "12312312321"AND `name` = "xuteng") OR (id != "103"AND `name` = "xuteng")')
            ->getAll();
        $sql = Model\visitorInfoDev::getLastSql();
        
        var_dump($sql);
        var_dump($res);
    }
    private function test_9(Model\visitorInfoDev $visitorInfo){
        $res = $visitorInfo->transaction(function($obj){
            $obj->data(['name' => ':autoInsertName'])
                ->insert([':autoInsertName' => 'autoInsertName transaction']);
            $obj->data(['name' => ':autoInsertName'])
                ->insert([':autoInsertName' => 'autoInsertName transaction2']);
            $obj->data(['id' => ':autoInsertNam'])
                ->insert([':autoInsertNam' => '12']);
        },3);
        var_dump($res);
    }
    private function test_10(Model\visitorInfoDev $visitorInfo){
        $first = $visitorInfo->select(['id', 'name', 'phone'])
            ->whereBetween('id','100','103');
        
        $res = Model\visitorInfoDev::select(['id', 'name', 'phone'])
            ->whereBetween('id','100','103')
            ->union($first)
            ->union(function($obj){
                $obj->select(['id', 'name', 'phone'])
                ->whereBetween('id','100','103');
            })
            ->unionAll($first->getAllToSql())
            ->getAll();
        $sql = Model\visitorInfoDev::getLastSql();
        
        var_dump($sql);
        var_dump($res);
    }
    private function test_11(Model\visitorInfoDev $visitorInfo){
        $first = $visitorInfo->select(['id', 'name', 'phone'])
            ->whereBetween('id','100','103');
        
        $res = Model\visitorInfoDev::select(['id', 'name', 'phone'])
            ->whereBetween('id','100','103')
            ->whereExists($first)
            ->whereExists(function($obj){
                $obj->select(['id', 'name', 'phone'])
                ->whereBetween('id','100','103');
            })
            ->whereExists($first->getAllToSql())
            ->getAll();
        $sql = Model\visitorInfoDev::getLastSql();
        
        var_dump($sql);
        var_dump($res);
    }
    
    private function test_12(Model\visitorInfoDev $visitorInfo){
        $sql = 'select * from visitor_info limit 1';
        $pdo = $visitorInfo->db->query($sql);
        $res = ($pdo->fetchall(\PDO::FETCH_ASSOC));
        
        var_dump($sql);
        var_dump($res);
    }
    
    private function test_13(Model\visitorInfoDev $visitorInfo){
        $sql = 'select * from visitor_info limit :number';
        $PDOStatement = $visitorInfo->db->prepare($sql);
        
        $PDOStatement->execute([':number' => 1]);
        $res = ($PDOStatement->fetchall(\PDO::FETCH_ASSOC));
        
        $PDOStatement->execute([':number' => 2]);
        $res2 = ($PDOStatement->fetchall(\PDO::FETCH_ASSOC));
        
        $PDOStatement->execute([':number' => 3]);
        $res3 = ($PDOStatement->fetchall(\PDO::FETCH_ASSOC));
        
        var_dump($sql);
        var_dump($res);
        var_dump($res2);
        var_dump($res3);

    }
    
    private function test_14(Model\visitorInfoDev $visitorInfo){
        $p = $visitorInfo->where('id',':id')->selectPrepare();
        
        var_dump($p->getRow([':id' => '12']));
        var_dump($p->getRow([':id' => '11']));
        var_dump($p->getRow([':id' => '102']));
        
        $p2 = $visitorInfo->where('id',':id')->data('name','prepare')->updatePrepare();
        
        var_dump($p2->update([':id' => '12']));
        var_dump($p2->update([':id' => '11']));
        var_dump($p2->update([':id' => '102']));
        
        $p3 = $visitorInfo->data('name',':name')->insertPrepare();
        
        var_dump($p3->insert([':name' => 'prepare']));
        var_dump($p3->insert([':name' => 'prepare']));
        var_dump($p3->insert([':name' => 'prepare']));
        
        $p4 = $visitorInfo->where('name',':name')->limit(1)->deletePrepare();
        
        var_dump($p4->delete([':name' => 'prepare']));
        var_dump($p4->delete([':name' => 'prepare']));
        var_dump($p4->delete([':name' => 'prepare']));
        
        
        exit;
    }
    
    
    
    public function __destruct() {
        
        var_export(statistic());
    }
    
    public function test(Model\visitorInfoDev $visitorInfo){
        echo '<pre>';
        
        $res = $visitorInfo->transaction(function($obj){

            
            $obj->data(['name' => ':autoInsertName'])
                ->insert([':autoInsertName' => 'autoInsertName transaction']);
            
            
            $obj->data(['name' => ':autoInsertName'])
                ->insert([':autoInsertName' => 'autoInsertName transaction2']);
            $obj->data(['id' => ':autoInsertNam'])
                ->insert([':autoInsertNam' => '432']);
        },3);
        
        var_dump($res);
        exit('ok');
    }
}