<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 14:04
 */

namespace app\index\model;


use think\Db;
use think\Log;
use think\Model;
use think\Request;
use think\Session;

class MDenyWords extends Base
{
    protected $searchColumn = [
        'word'=>['dw.word','like'],
        'add_user_name'=>['u.user_name','like'],
        'status'=>['dw.status','like'],
        'add_time'=>['dw.add_time','between','time'],
    ];

    public function getlist()
    {
        $query = Db::table("deny_words")->alias("dw")->field("dw.*,u.user_name as add_user_name,uu.user_name as update_user_name,c.dp_name as add_dp_name,cc.dp_name as update_dp_name")
            ->join('users u','u.user_id=dw.add_user_id','left')
            ->join('users uu','uu.user_id=dw.update_user_id','left')
            ->join('departments c','c.dp_id=u.dp_id','left')
            ->join('departments cc','cc.dp_id=uu.dp_id','left');
        $this->checkSearch($query);
        $data = $this->autoPaginate($query)->toArray();
        return $data;
    }

    public function material_changetype()
    {
        $request = Request::instance()->post();
        $type = !empty($request['type'])?$request['type']:0;
        if (is_array($request['id']) && count($request['id']) != 0) {
            $id = !empty($request['id'])?$request['id']:[];
            Db::table("deny_words")->where("dw_id","IN",$id)->update(['status'=>$type]);
        } elseif (is_int((int)$request['id']) && !empty($request['id'])) {
            $id = !empty($request['id'])?$request['id']:"";
            Db::table("deny_words")->where("dw_id","=",$id)->update(['status'=>$type]);
        } else {
            return ['type'=>false,"info"=>"修改失败,未获取到相关信息!!"];
        }
        return ['type'=>true,"info"=>"修改成功!!"];
    }

    public function material_addDenyword()
    {
        $request = Request::instance()->post();
        $word = empty($request['search']['word'])?"":$request['search']['word'];
        if ($word == "")
            return ['type'=>false,"info"=>"未获取到违禁词"];
        $data = Db::table("deny_words")->where("word","=",$word)
            ->where("status","=","1")->find();
        if (empty($data)) {
            $user = Session::get("org_user_id");
            Db::table("deny_words")->insert(['word'=>$word,"add_user_id"=>$user,"add_time"=>date("Y-m-d H:i:s")]);
            return ['type'=>"success","info"=>"添加违禁词成功"];
        } else {
            return ['type'=>false,"info"=>"数据库存在该违禁词"];
        }
    }
}