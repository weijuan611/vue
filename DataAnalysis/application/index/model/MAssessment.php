<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/12
 * Time: 14:05
 */

namespace app\index\model;


use think\Db;
use think\Log;
use think\Request;

class MAssessment extends Base
{
    protected $searchColumn = [
        'user_name'=>['u.user_name','like'],
        'task_time'=>['a.task_time','between','time'],
    ];
    public function getlist()
    {
        $query = Db::table("assessment")->alias("a")->field("a.*,u.user_name,d.dp_name")
            ->join("users u","a.user_id = u.user_id","left")
            ->join("departments d","d.dp_id = a.dp_id","left");
        $this->checkSearch($query);
        $this->checkRange($query,'d.dp_id','u.user_id');
        $data = $this->autoPaginate($query)->toArray();
        foreach($data['data'] as $key=>$val) {
            $dp_arr = $this->getDpParent($val['dp_id']);
            $data['data'][$key]['dp_arr'] = array_reverse($dp_arr);
        }
        return $data;
    }

    public function getDpParent($dp_id,&$result = [])
    {
        $result[] = $dp_id;
        $dp_data = Db::table("departments")->field("parent_id,dp_id")->where("dp_id","=",$dp_id)->find();
        if ($dp_data['parent_id'] != "") {
            $this->getDpParent($dp_data['parent_id'],$result);
        }
        return $result;
    }
}