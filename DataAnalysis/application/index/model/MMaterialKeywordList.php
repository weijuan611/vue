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
        'km_id'=>['km.km_id','='],
        'keyword'=>['k.keyword','like'],
        'title'=>['km.title','like'],
        'url'=>['km.url','like'],
        'status_model'=>['km.status','='],
        'add_time'=>['km.add_time','between','time'],
    ];
    public function getlist()
    {
        $query = Db::table("keywords_material")->alias("km")
            ->join('users u','km.user_id = u.user_id','left')
            ->join('keywords k','k.kw_id=km.kw_id','left')
            ->join('categories c','c.c_id=k.c_id','left')
            ->field("km.*,k.keyword,c.catename");
        $this->checkSearch($query);
        $this->checkRange($query,'u.dp_id','u.user_id');
        $data = $this->autoPaginate($query)->toArray();
        foreach ($data['data'] as $key=>$val) {
            if (stristr($val['url'],"手动录入")) {
                $data['data'][$key]['url'] = stristr($val['url'], '[', TRUE);
            } elseif (stristr($val['url'],"已手工编辑")){
                $data['data'][$key]['url'] = stristr($val['url'], '[', TRUE);
            } else {
                continue;
            }
        }
        return $data;
    }

    public function edit()
    {
        $request = Request::instance()->post();
        $km_id = empty($request['km_id'])?"":$request['km_id'];
        if ($km_id == "")
            return ['type'=>"error","未获取到数据"];
        $data = [
          'kw_id'=>$request['kw_id'],
          'url'=>'已手工编辑[' . ($request['use_num'] + 1) . ']' . rand(1, 10000),
          'title'=>$request['title'],
          'author'=>$request['author'],
          'cover_img'=>$request['cover_img'],
          'content'=>$request['content'],
          'type'=>$request['type'],
          'area_id'=>$request['area_id'],
          'points'=>$request['points'],
        ];
        $query = Db::table("keywords_material")->where("km_id","=",$km_id)->update($data);
        return ['type'=>"success","修改成功"];
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

    public function getInfo($km_id){
        return Db::table('keywords_material')->alias("km")->join("sys_area sa","sa.Id=km.area_id","left")->where('km_id','=',$km_id)->find();
    }
}