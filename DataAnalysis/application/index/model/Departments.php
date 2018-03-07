<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/2/1
 * Time: 14:14
 */

namespace app\index\model;


use think\Db;

class Departments extends Base
{
    protected $table = 'departments';

    public function getChild($dp_id,$status= 1){
        return Db::table($this->table)->where('parent_id','=',(int)$dp_id)->where('status','=',$status)
            ->field('dp_id,dp_name,parent_id')->select();
    }

    public function getTree(&$result=[],$dp_id=0,$status=1){
        if(empty($result)){
            $data = $this->getChild($dp_id,$status);
            if(!empty($data)){
                foreach ($data as $key => $value){
                    $data[$key] = ['id'=>(int)$value['dp_id'],'label'=>$value['dp_name']];
                }
                $this->getTree($data,$dp_id,$status);
            }
            $result = $data;
        }else{
            foreach ($result as $key => $value){
                $child = $this->getChild($value['id'],$status);
                if(!empty($child)){
                    foreach ($child as $k => $v){
                        $child[$k] = ['id'=>(int)$v['dp_id'],'label'=>$v['dp_name']];
                    }
                    $this->getTree($child,$dp_id,$status);
                    $result[$key]['children'] =$child;
                }
            }
        }
    }

    public function getAllChild(&$result=[],$dp_id,$status =1){
        $data = $this->getChild($dp_id,$status=1);
        if(!empty($data)){
            foreach ($data as $item){
                $this->getAllChild($result,$item['dp_id']);
                $result[]=$item['dp_id'];
            }
        }
    }
}