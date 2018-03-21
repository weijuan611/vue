<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/8
 * Time: 11:05
 */
namespace app\index\model;

use think\Db;
use think\Log;
use think\Request;
use think\Session;

class MMaterialKeywordList extends Base
{
    protected $searchColumn = [
        'keyword'=>['k.keyword','like'],
        'title'=>['km.title','like'],
        'url'=>['km.url','like'],
        'status_model'=>['km.status','='],
        'add_time'=>['km.add_time','between','time'],
    ];
    public function getlist()
    {
        $query = Db::table("keywords_material")->alias("km")->field("km.*,k.keyword,c.catename")
            ->join('keywords k','k.kw_id=km.kw_id','left')
            ->join('categories c','c.c_id=k.c_id','left');
        $this->checkSearch($query);
        $data = $this->autoPaginate($query)->toArray();
        return $data;
    }

    public function material_changetype()
    {
        $request = Request::instance()->post();
        $id = !empty($request['id'])?$request['id']:"";
        $type = !empty($request['type'])?$request['type']:0;
        if ($id == "")
            return ['type'=>false,"info"=>"删除失败,未获取到相关信息!!"];
        Db::table("keywords_material")->where("km_id","=",$id)->update(['status'=>$type]);
        return ['type'=>true,"info"=>"修改成功!!"];
    }
}