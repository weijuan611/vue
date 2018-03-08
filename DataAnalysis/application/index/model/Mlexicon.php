<?php

namespace app\index\model;
use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\db\Query;
use think\Log;
use think\Request;
use think\Session;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11
 * Time: 11:13
 */
class Mlexicon extends Base
{
    public function setting_list()
    {
        $data = [];
        $request = Request::instance()->post();
        $lname = empty($request["tagsOperateData"]['searchedTag'])?"":trim($request["tagsOperateData"]['searchedTag']);
        $query = Db::table("labels");
        if ($lname != "") {
            $query = $query->where("l_name like '%{$lname}%'");
        }
        $result = $this->autoPaginate($query)->toArray();
        foreach ($result['data'] as $key=>$value) {
            $data[$key]['id'] = $value['l_id'];
            $data[$key]['name'] = $value['l_name'];
        }
        return ["tagsData"=>$data,'tagsTotal'=>$result['total']];
    }

    public function setting_add()
    {
        $request = Request::instance()->post();
        $lname = empty($request['name'])?"":trim($request['name']);
        $data = ['l_name'=>$lname,];
        if ($lname == "")
            return ['type'=>false,"info"=>"添加标签内容不能为空!!"];
        $result = Db::table("labels")->where("l_name",$lname)->find();
        if (!empty($result)) {
            return ['type'=>false,"info"=>"数据库中已有[{$lname}]标签"];
        } else {
            Db::table("labels")->insert($data);
            return ['type'=>true,"info"=>"添加[{$lname}]标签成功!"];
        }
    }

    public function setting_edit()
    {
        $request = Request::instance()->post();
        $lid = empty($request['id'])?"":trim($request['id']);
        $lname = empty($request['name'])?"":trim($request['name']);
        $loldname = empty($request['oldname'])?"":trim($request['oldname']);
        if ($lid != "" && $lname != "") {
            $data = ['l_name'=>$lname,];
            $result = Db::table("labels")->where("l_name",$lname)->find();
            if (!empty($result)) {
                return ['type'=>false,"info"=>"数据库中已有[{$lname}]标签"];
            } else {
                Db::table("labels")->where("l_id",$lid)->update($data);
                return ['type'=>true,"info"=>"成功将[{$loldname}]标签修改为[{$lname}]标签!"];
            }
        } elseif($lname == "") {
            return ['type'=>false,"info"=>"修改标签内容不能为空!!"];
        } else {
            return ['type'=>false,"info"=>"未获取到所修改的标签内容!!"];
        }
    }

    public function setting_delete()
    {
        $request = Request::instance()->post();
        $lid = empty($request['id'])?"":trim($request['id']);
        $lname = empty($request['name'])?"":trim($request['name']);
        if ($lid != ""){
            Db::table("labels")->where("l_id",$lid)->delete();
            return ['type'=>true,"info"=>"成功删除[{$lname}]标签!"];
        }else{
            return ['type'=>false,"info"=>"未获取到所删除的标签内容!!"];
        }
    }

    public function categories_init()
    {
        $level_1 = $level_2 = $level_3 = [];
        $userid = Session::get("org_user_id");
        $allow_cid = Db::table("category_roles")->field("c_id")->where("user_id",$userid)->select();
        if (empty($allow_cid) OR $userid == 1) {
            $level_1 = $level_2 = $level_3 = [];
        } else {
            foreach ($allow_cid as $key=>$value) {
                $c_arr_1 = Db::table("categories")->where("c_id",$value['c_id'])->find();
                if ($c_arr_1['pc_id'] == 0) {
                    $level_3_cid = [];
                    $level_1[] = $c_arr_1["c_id"];
                    $level_2_arr = Db::table("categories")->where("pc_id",$c_arr_1['c_id'])->select();
                    $query_3 = Db::table("categories");
                    foreach ($level_2_arr as $list => $info) {
                        $level_3_cid[] = $info['c_id'];
                        $level_2[] = $info['c_id'];
                    }
                    $level_3_arr = $this->createquery($query_3,"pc_id",$level_3_cid)->select();
                    foreach ($level_3_arr as $list => $info) {
                        $level_3[] = $info['c_id'];
                    }
                } else{
                    $c_arr_2 = Db::table("categories")->where("c_id",$value['c_id'])->find();
                    $c_arr_3 = Db::table("categories")->where("c_id",$c_arr_2['pc_id'])->find();
                    if ($c_arr_3['pc_id'] != 0) {   //level_3
                        $level_3[] = $c_arr_2['c_id'];
                        $level_2_arr = Db::table("categories")->where("c_id",$c_arr_2['pc_id'])->find();
                        $level_1_arr = Db::table("categories")->field("c_id")->where("c_id",$level_2_arr['pc_id'])->find();
                        $level_2[] = $level_2_arr['c_id'];
                        $level_1[] = $level_1_arr['c_id'];
                    } else {
                        $level_2[] = $c_arr_2['c_id'];
                        $level_1_arr = Db::table("categories")->field("c_id")->where("c_id",$c_arr_2['pc_id'])->find();
                        $level_3_arr = Db::table("categories")->field("c_id")->where("pc_id",$c_arr_2['c_id'])->select();
                        $level_1[] = $level_1_arr['c_id'];
                        foreach ($level_3_arr as $list => $info) {
                            $level_3[] = $info['c_id'];
                        }
                    }
                }
            }
        }
        $two_cid = $three_cid  = $one_arr_true = $two_arr_true = $ttwo_arr_true = $three_arr_true = [];
        $one_query = Db::table("categories")->field("c_id as id,pc_id,catename as value")->where("pc_id","0");
        if (!empty($level_1)) {
            $one_arr = $this->createquery($one_query,"c_id",$level_1)->select();
        } else {
            $one_arr = $one_query->select();
        }
        foreach ($one_arr as $key => $value) {
            $two_cid[] = $value['id'];
        }
        $two_query = Db::table("categories")->field("c_id as id,pc_id,catename as value");
        $two_query = $this->createquery($two_query,"pc_id",$two_cid);
        if (!empty($level_1)) {
            $two_arr = $this->createquery($two_query,"c_id",$level_2)->order("pc_id","asc")->order("c_id","asc")->select();
        } else {
            $two_arr = $two_query->order("pc_id","asc")->order("c_id","asc")->select();
        }
        foreach ($two_arr as $key => $value) {
            $three_cid[] = $value['id'];
        }
        $three_query = Db::table("categories")->field("c_id as id,pc_id,catename as value");
        $three_query = $this->createquery($three_query,"pc_id",$three_cid);
        if (!empty($level_1)) {
            $three_arr = $this->createquery($three_query,"c_id",$level_3)->order("pc_id","asc")->order("c_id","asc")->select();
        } else {
            $three_arr = $three_query->order("pc_id","asc")->order("c_id","asc")->select();
        }
        foreach ($three_arr as $key => $value) {
            $three_arr_true[$value['pc_id']][] = $value;
        }
        foreach ($two_arr as $key => $value) {
            foreach ($three_arr_true as $list => $info) {
                if ($list == $value['id']) {
                    $two_arr_true[$key] = $value;
                    $two_arr_true[$key]['children'] = $info;
                }
            }
        }
        foreach ($two_arr_true as $key => $value) {
            $ttwo_arr_true[$value['pc_id']][] = $value;
        }
        foreach ($one_arr as $key => $value) {
            foreach ($ttwo_arr_true as $list => $info) {
                if ($list == $value['id']) {
                    $one_arr_true[$key] = $value;
                    $one_arr_true[$key]['children'] = $info;
                }
            }
        }
        return $one_arr_true;
    }
    public function keywords_add()
    {
        $request = Request::instance()->post();
        $id = empty($request['id'])?"":trim($request['id']);
        $keyword = empty($request['keyword'])?"":trim($request['keyword']);
        $class = empty($request['class'])?"":$request['class'];
        $tags = empty($request['tags'])?[]:$request['tags'];
        $userid = Session::get("org_user_id");
        $username = Session::get("org_user_name");
        $time = date("Y-m-d H:i:s");
        if ($class == "")
            return ['type'=>false,"info"=>"添加关键字中，分类内容不能为空!!"];
        if ($keyword == "")
            return ['type'=>false,"info"=>"添加关键字中，关键字内容不能为空!!"];
        $result = Db::table("keywords")->where("keyword",$keyword)->find();
        if (!is_null($result))
            return ['type'=>false,"info"=>"数据库中已有[{$keyword}]关键字"];
        $keyword_num = $this->getKeywordsNum();
        if($keyword_num == 0){
            return ['type'=>false,"info"=>"您已经超出每日任务关键词数量！"];
        }
        Db::startTrans();
        try{
            $kw_id = Db::table("keywords")->insertGetId(['keyword' => $keyword, 'c_id' => $class, "user_name" => $username, "user_id" => $userid, "add_time" => $time, "update_time" => $time]);
            $data = [];
            $num = count($tags);
            for ($i=0;$i<$num;$i++) {
                $data[$i]['kw_id'] = $kw_id;
                $data[$i]['l_id'] = $tags[$i]['value'];
                $data[$i]['add_time'] = $time;
                $data[$i]['user_id'] = $userid;
                $data[$i]['type'] = $tags[$i]['type'];
            }
            Db::table("keywords_label")->insertAll($data);
            $info = serialize($tags);
            Utility::keywordsLog($kw_id,0,'添加关键词：'.$keyword);
            Db::table("keywords_check")->insert(['kw_id'=>$kw_id,'status'=>"0","type"=>"0",'c_id'=>$class,"tags"=>$info,"createtime"=>date("Y-m-d H:i:s")]);
            Db::commit();
            return ['type'=>true,"info"=>"[{$keyword}]添加成功，请前往审核列表查看进度！"];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }

    public function getKeywordsNum(){
        $num = Db::table('org_userroles ou')->join('org_roles or','ou.RoleID = or.ID','left')
            ->where('ou.UserID','=',Session('org_user_id'))->max('KeywordNum');
        if($num == 0||Session('org_user_id') === SUPER_USER_ID){
            return -1;
        }
        $num_in = Db::table('keywords k')
            ->join('keywords_check kc','k.kw_id = kc.kw_id')
            ->where('k.user_id','=',Session('org_user_id'))
            ->where('kc.status','<>',2)
            ->where('k.add_time','between',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])
            ->count();
        return $num >$num_in?$num-$num_in:0;
    }

    public function keywords_edit()
    {
        $request = Request::instance()->post();
        $id = empty($request['id'])?"":trim($request['id']);
        $keyword = empty($request['keyword'])?"":trim($request['keyword']);
        $class = empty($request['class'])?"":$request['class'];
        $tags = empty($request['tags'])?[]:$request['tags'];
        $userid = Session::get("org_user_id");
        $time = date("Y-m-d H:i:s");
        if ($id == "")
            return ['type'=>false,"info"=>"未获取到所修改的关键字内容!!"];
        if ($keyword == "")
            return ['type'=>false,"info"=>"修改关键字中，关键字内容不能为空!!"];
        if ($class == "")
            return ['type'=>false,"info"=>"修改关键字中，分类内容不能为空!!"];
        Db::startTrans();
        try{
            if (empty($tags)) {
                $data = [];
            } else {
                $data = [];
                $num = count($tags);
                for ($i=0;$i<$num;$i++) {
                    $data[$i]['kw_id'] = $id;
                    $data[$i]['l_id'] = $tags[$i]['value'];
                    $data[$i]['add_time'] = $time;
                    $data[$i]['user_id'] = $userid;
                    $data[$i]['type'] = $tags[$i]['type'];
                }
            }
            $info = serialize($tags);
            Utility::keywordsLog($id,1,'修改关键词：'.$keyword);
            $result_check = Db::table("keywords_check")->where("kw_id","=",$id)->find();
            if (empty($result_check)) {
                Db::table("keywords_check")->insert(['kw_id'=>$id,'status'=>"0","type"=>"1",'c_id'=>$class,"tags"=>$info,"createtime"=>date("Y-m-d H:i:s")]);
            } else {
                Db::table("keywords_check")->where("kw_id","=",$id)->update(['status'=>"0","type"=>"1",'c_id'=>$class,"tags"=>$info,"createtime"=>date("Y-m-d H:i:s")]);
            }
            Db::commit();
            return ['type'=>true,"info"=>"修改关键字成功，请前往审核列表查看进度！"];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }

    public static function keywords_delete()
    {
        $request = Request::instance()->post();
        $id = empty($request['id'])?"":trim($request['id']);
        $keyword = empty($request['keyword'])?"":trim($request['keyword']);
        if ($id == "" OR $keyword == "")
            return ['type'=>false,"info"=>"未获取到所修改的关键字内容!!"];
        Db::startTrans();
        try{
            Utility::keywordsLog($id,3,'删除关键词：'.$keyword);
            $num = Db::table('keywords_check')->where('kw_id','=',$id)->count();
            if($num>0){
                Db::table('keywords_check')->where('kw_id','=',$id)->update(['status'=>"0","type"=>"3","createtime"=>date("Y-m-d H:i:s")]);
            }else{
                Db::table("keywords_check")->insert(['kw_id'=>$id,'status'=>"0","type"=>"3","createtime"=>date("Y-m-d H:i:s")]);
            }
            Db::commit();
            return ['type'=>true,"info"=>"删除关键字成功，请前往审核列表查看进度！"];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            Log::error($e->getMessage().PHP_EOL.$e->getCode().PHP_EOL.$e->getTraceAsString());
        }
    }

    public function index_list($search_data){
        $query = Db::table('keywords k')
//            ->join('keyword_statistics ks','ks.kw_id = k.kw_id','left')
            ->join('categories c','k.c_id = c.c_id','left')
            ->join('keywords_label kl','kl.kw_id = k.kw_id','left')
            ->join('users u','k.user_id = u.user_id','left')
            ->group('k.kw_id')
            ->field('k.*,c.catename,GROUP_CONCAT(kl.l_id) as l_id')
            ->join('keywords_check kc','k.kw_id = kc.kw_id','left')
            ->where('(kc.status is null or (kc.status = 1 and kc.type=0) or (kc.status=2 and kc.type=3)) or (kc.type=1 and kc.status !=0)')
            ->where('k.kw_id','<>',4)
            ->where('k.is_show','=',0);
        $post = Request::instance()->post();
        if(!isset($post['paginate']['prop'])){
            $query->order('k.add_time','desc');
        }
        if(isset($search_data['keyword'])&&trim($search_data['keyword'])!=""){
            $query->where('k.keyword','like','%'.trim($search_data['keyword']).'%');
        }
        if (isset($search_data['filterClass']['id'])&&$search_data['filterClass']['id']>0){
            $query->where('k.c_id','=',$search_data['filterClass']['id']);
        }
        if (isset($search_data['value9'])&&!empty($search_data['value9'])){//标签
            $type_1 = $type_0 = [];
            foreach ($search_data['value9'] as $key=>$val) {
                if ($val['type'] == "1") {
                    $type_1[] = $val['value'];
                }
                if ($val['type'] == "2") {
                    $type_0[] = $val['value'];
                }
            }
            $query->where(function (Query $query)use($type_0,$type_1){
                if (!empty($type_0)) {
                    $query->where("kl.type","=","0");
                    $this->createquery($query,'kl.l_id',$type_0);
                }
                if (!empty($type_1)) {
                    if (!empty($type_0)) {
                        $query->whereOr("kl.type","=","1");
                    }else{
                        $query->where("kl.type","=","1");
                    }
                    $this->createquery($query,'kl.l_id',$type_1);
                }
            });
        }
        if (isset($search_data['adder'])&&trim($search_data['adder'])!=""){
            $query->where('k.user_name','like','%'.trim($search_data['adder']));
        }
        if (isset($search_data['dateTime'])&&!empty($search_data['dateTime'])){
            $query->where('k.add_time','between',[$search_data['dateTime'][0],$search_data['dateTime'][1]]);
        }
        if (isset($search_data['assess'])&&!empty($search_data['assess'])){
            if ($search_data['assess']['term1']['check'] === true OR
                $search_data['assess']['term1']['media'] != "- - - - -" OR
                $search_data['assess']['term1']['assess'] != "- - - - -" ) {
                $term_arr['one'] = $search_data['assess']['term1'];
            } else {
                $term_arr['one'] = [];
            }
            if ($search_data['assess']['logic'] == "- - - - -" OR
                $search_data['assess']['term2']['check'] !== true OR
                $search_data['assess']['term2']['media'] == "- - - - -" OR
                $search_data['assess']['term2']['assess'] == "- - - - -") {
                $term_arr['two'] = [];
                $term_arr['type'] = "";
            } else {
                $term_arr['two'] = $search_data['assess']['term2'];
                $term_arr['type'] = $search_data['assess']['logic'];
            }
            $query->where(function (Query $query)use($term_arr){
                if (is_array($term_arr['one']) && !empty($term_arr['one'])) {
                    $term = str_replace("评价","",$term_arr['one']['media']).$term_arr['one']['assess'];
                    switch($term) {
                        case "PC已达标":
                            $query->where('k.score_pc',1);
                            break;
                        case "M已达标":
                            $query->where('k.score_m',1);
                            break;
                        case "PC推广中":
                            $query->where('k.score_pc',"between",[2,3]);
                            break;
                        case "M推广中":
                            $query->where('k.score_m',"between",[2,3]);
                            break;
                        case "PC未推广":
                            $query->where('k.score_pc',"between",[4,5]);
                            break;
                        case "M未推广":
                            $query->where('k.score_m',"between",[4,5]);
                            break;
                    }
                }
                if (is_array($term_arr['two']) && !empty($term_arr['two'])) {
                    if ($term_arr['type'] == "且") {
                        $query->where(function (Query $query)use($term_arr){
                            $term = str_replace("评价","",$term_arr['two']['media']).$term_arr['two']['assess'];
                            switch($term) {
                                case "PC已达标":
                                    $query->where('k.score_pc',1);
                                    break;
                                case "M已达标":
                                    $query->where('k.score_m',1);
                                    break;
                                case "PC推广中":
                                    $query->where('k.score_pc',"between",[2,3]);
                                    break;
                                case "M推广中":
                                    $query->where('k.score_m',"between",[2,3]);
                                    break;
                                case "PC未推广":
                                    $query->where('k.score_pc',"between",[4,5]);
                                    break;
                                case "M未推广":
                                    $query->where('k.score_m',"between",[4,5]);
                                    break;
                            }
                        });
                    } else {
                        $query->whereOr(function (Query $query)use($term_arr){
                            foreach ($term_arr as $key=>$value) {
                                if (is_array($value) && !empty($value)) {
                                    $term = str_replace("评价","",$value['media']).$value['assess'];
                                    switch($term) {
                                        case "PC已达标":
                                            $query->where('k.score_pc',1);
                                            break;
                                        case "M已达标":
                                            $query->where('k.score_m',1);
                                            break;
                                        case "PC推广中":
                                            $query->where('k.score_pc',"between",[2,3]);
                                            break;
                                        case "M推广中":
                                            $query->where('k.score_m',"between",[2,3]);
                                            break;
                                        case "PC未推广":
                                            $query->where('k.score_pc',"between",[4,5]);
                                            break;
                                        case "M未推广":
                                            $query->where('k.score_m',"between",[4,5]);
                                            break;
                                    }
                                }
                            }
                        });
                    }
                }
            });
        }
        if(isset($search_data['filterIndex'])&&!empty($search_data['filterIndex'])){
            foreach ($search_data['filterIndex'] as $v){
                if(isset($v['check'],$v['value'],$v['character'])&&$v['check'] == true && $v['character'] != ''){
                    $switch = $v['value'].$v['type'];
                    switch ($switch){
                        case 'searchVolumePC':
                            $query->where('k.kws_count_pc',$v['character'],$v['number']);
                            break;
                        case 'searchVolumeM':
                            $query->where('k.kws_count_m',$v['character'],$v['number']);
                            break;
                        case 'searchVolumeALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query)use($v){
                                    $query->whereOr('k.kws_count_pc',$v['character'],$v['number']);
                                    $query->whereOr('k.kws_count_m',$v['character'],$v['number']);
                                });
                            }
                            break;
                        case 'baiduRankPC':
                            $query->where('k.baidu_rank_pc',$v['character'],$v['number']);
                            break;
                        case 'baiduRankM':
                            $query->where('k.baidu_rank_m',$v['character'],$v['number']);
                            break;
                        case 'baiduRankALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query)use($v){
                                    $query->whereOr('k.baidu_rank_pc',$v['character'],$v['number']);
                                    $query->whereOr('k.baidu_rank_m',$v['character'],$v['number']);
                                });
                            }
                            break;
                        case 'baiduIndexPC':
                            $query->where('k.baidu_index_pc',$v['character'],$v['number']);
                            break;
                        case 'baiduIndexM':
                            $query->where('k.baidu_index_m',$v['character'],$v['number']);
                            break;
                        case 'baiduIndexALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query) use ($v) {
                                    $query->whereOr('k.baidu_index_pc', $v['character'], $v['number']);
                                    $query->whereOr('k.baidu_index_m', $v['character'], $v['number']);
                                });
                            }
                            break;
                        case 'newsIndexALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query) use ($v) {
                                    $query->whereOr('k.news_index', $v['character'], $v['number']);
                                    $query->whereOr('k.mnews_index', $v['character'], $v['number']);
                                });
                            }
                            break;
                        case 'newsIndexPC':
                            $query->where('k.news_index',$v['character'],$v['number']);
                            break;
                        case 'newsIndexM':
                            $query->where('k.mnews_index',$v['character'],$v['number']);
                            break;
                        case 'courseIndexPC':
                            $query->where('k.course_index',$v['character'],$v['number']);
                            break;
                        case 'courseIndexM':
                            $query->where('k.mcourse_index',$v['character'],$v['number']);
                            break;
                        case 'courseIndexALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query) use ($v) {
                                    $query->whereOr('k.course_index', $v['character'], $v['number']);
                                    $query->whereOr('k.mcourse_index', $v['character'], $v['number']);
                                });
                            }
                            break;
                        case 'schoolIndexPC':
                            $query->where('k.school_index',$v['character'],$v['number']);
                            break;
                        case 'schoolIndexM':
                            $query->where('k.mschool_index',$v['character'],$v['number']);
                            break;
                        case 'schoolIndexALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query) use ($v) {
                                    $query->whereOr('k.school_index', $v['character'], $v['number']);
                                    $query->whereOr('k.mschool_index', $v['character'], $v['number']);
                                });
                            }
                            break;
                        case 'knowIndexPC':
                            $query->where('k.zhidao_index',$v['character'],$v['number']);
                            break;
                        case 'knowIndexM':
                            $query->where('k.mzhidao_index',$v['character'],$v['number']);
                            break;
                        case 'knowIndexALL':
                            if ($v['number'] != "") {
                                $query->where(function ($query) use ($v) {
                                    $query->whereOr('k.zhidao_index', $v['character'], $v['number']);
                                    $query->whereOr('k.mzhidao_index', $v['character'], $v['number']);
                                });
                            }
                            break;
                    }
                }
            }
        }
        $this->checkRange($query,'u.dp_id','u.user_id');
        $data=$this->autoPaginate($query)->toArray();
        foreach ($data['data'] as $key=>$value) {
            if (!empty($value['l_id'])){
                $newlidarr = [];
                $lidarr = explode(",",$value['l_id']);
                foreach ($lidarr as $list => $info) {
                    $newlidarr[$list] = (int)$info;
                }
                $data['data'][$key]['l_id'] = array_unique($newlidarr);
            } else {
                $data['data'][$key]['l_id'] = [];
            }
        }
        return ['tableData'=>$data['data'],'total'=>$data['total']];


    }

    public function keywords_editbench()
    {
        $request = Request::instance()->post();
        $idarr = empty($request['data']['id'])?[]:$request['data']['id'];
        $class = empty($request['data']['class'])?"":$request['data']['class']['id'];
        $tags = empty($request['data']['tags'])?[]:array_unique($request['data']['tags']);
        $userid = Session::get("org_user_id");
        $username = Session::get("org_user_name");
        $time = date("Y-m-d H:i:s");
        if (empty($idarr))
            return ['type'=>false,'info'=>'未获取到所选关键词信息'];
        Db::startTrans();
        try{
//            $query = Db::table("keywords");
//            $query = $this->createquery($query,"kw_id",$idarr);
//            $query->update(['c_id'=>$class,'update_time'=>$time,"user_id"=>$userid,"user_name"=>$username]);
//            $query_label = Db::table("keywords_label");
//            $query_label = $this->createquery($query_label,"kw_id",$idarr);
//            $query_label->delete();
            $idnum = count($idarr);
            $tagsnum = count($tags);
            $data = $checkarr = $listarr = [];
            for ($i=0;$i<$idnum;$i++) {
                for ($j=0;$j<$tagsnum;$j++) {
                    $k = $i*$tagsnum+$j;
                    $data[$k]['kw_id'] = $idarr[$i];
                    $data[$k]['l_id'] = $tags[$j];
                    $data[$k]['add_time'] = $time;
                    $data[$k]['user_id'] = $userid;
                }
            }
            foreach ($data as $key=>$val) {
                $listarr[$val['kw_id']][] = $val;
            }
            foreach ($idarr as $key=>$val) {
                $checkarr[$key]['kw_id'] = $val;
                $checkarr[$key]['status'] = "0";
                $checkarr[$key]['type'] = "1";
                $checkarr[$key]['c_id'] = $class;
                $checkarr[$key]['tags'] = serialize($tags);
                $checkarr[$key]['createtime'] = date("Y-m-d H:i:s");
            }
            Utility::keywordsLog($idarr,1,'批量修改关键词');
            Db::table("keywords_check")->insertAll($checkarr);
//            Db::table("keywords_label")->insertAll($data);
            Db::commit();
            return ['type'=>true,"info"=>"批量修改关键词成功，请前往审核列表查看进度！"];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }

    public function input_csv($handle,$class_id,$label1,$label2)
    {
        $out = [];
        $num=0;
        while ($data = fgetcsv($handle)) {
            $data[0]=iconv(mb_detect_encoding(trim($data[0])
                , array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5')), 'utf-8//IGNORE', trim($data[0]));
            $out[]=$data[0];
            if(count($out)>=100){
                $num+=$this->import($out,$class_id,$label1,$label2);
                $out=[];
            }
        }
        if(count($out)>0){
            $num+=$this->import($out,$class_id,$label1,$label2);
        }
        return $num;
    }

    public function import($result,$class_id,$label1,$label2)
    {
        $result = array_map('strtolower',array_unique($result));
        $in_arr=DB::table('keywords')->where('keyword','in',$result)->column('keyword');
        $not_in=array_diff($result,$in_arr);
        $num=$this->getKeywordsNum();
        if($num==0){
            return 0;
        }
        if($num != -1 && count($not_in)>$num){
            return 0;
        }
        if (count($not_in)>0){
            Db::startTrans();
            try {
                foreach ($not_in as $item) { //循环获取各字段值
                    $arr=[
                        'c_id'=>$class_id,
                        'add_time'=>date("Y-m-d H:i:s"),
                        'user_id'=>Session('org_user_id'),
                        'user_name'=>Session('org_user_name'),
                        'keyword'=>$item
                    ];
                    $id= Db::table("keywords")->insertGetId($arr);
                    //插入标签
                    if ($label1){
                        $label1_arr = explode(',',$label1);
                        foreach ($label1_arr as $lv){
                            Db::table('keywords_label')->insert(['kw_id'=>$id,'l_id'=>$lv,'add_time'=>date("Y-m-d H:i:s"),'user_id'=>Session('org_user_id')]);
                        }
                        Db::table('keywords_check')->insert(['kw_id'=>$id,'user_id'=>Session('org_user_id'),'status'=>"0",'c_id'=>$class_id,"createtime"=>date("Y-m-d H:i:s"),"type"=>"0"]);
                    }
                    if ($label2){
                        $label2_arr = explode(',',$label2);
                        foreach ($label2_arr as $lv2){
                            Db::table('keywords_label')->insert(['kw_id'=>$id,'l_id'=>$lv2,'add_time'=>date("Y-m-d H:i:s"),'user_id'=>Session('org_user_id'),'type'=>1]);
                        }
                        Db::table('keywords_check')->insert(['kw_id'=>$id,'user_id'=>Session('org_user_id'),'status'=>"0",'c_id'=>$class_id,"createtime"=>date("Y-m-d H:i:s"),"type"=>"0"]);
                    }
                }
                Db::commit();
                return count($not_in);
            } catch (\Exception $e){
                Db::rollback();
                Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
                return 0;
            }
        }else{
            return 0;
        }
    }

    public function getTrend()
    {
        $request = Request::instance()->post();
        $request['dateTime'][1] = date('Y-m-d',strtotime($request['dateTime'][1])+24*3600);
        $trendData1 = [];
        $trendData2 = [];
        $trend_LineChart_Data = [];

        $kw_arr = Db::table('keywords')->where('keyword',$request['keyword'])->field('kw_id,baidu_rank_pc as mpc,baidu_rank_m as mm')->find();
        $kw_trend1 = Db::table('keywords_crawl_log')->where('kw_id',$kw_arr['kw_id'])->where('op_id',0)
            ->where('create_time','between',$request['dateTime'])->field('min(rank),min(rank_m)')->find();
        $trendData1['value']=$request['keyword'];
        $trend_LineChart_Data['title'][] = $request['keyword'].'_pc';
        $trend_LineChart_Data['title'][] = $request['keyword'].'_m';
        $trendData1['data'] = [
            ['title'=>'当前pc百度排名','number'=>$kw_arr['mpc']],
            ['title'=>'历史pc最高排名','number'=>$kw_trend1['min(rank)']],
            ['title'=>'当前M百度排名','number'=>$kw_arr['mm']],
            ['title'=>'历史M最高排名','number'=>$kw_trend1['min(rank_m)']]
        ];

        $kw_trend2 = Db::table('keywords_crawl_log')->where('kw_id',$kw_arr['kw_id'])->where('op_id','<>',0)
            ->where('create_time','between',$request['dateTime'])->field('min(rank),min(rank_m),op_id')
            ->group('op_id')->select();
        if (!empty($kw_trend2)){
            foreach ($kw_trend2 as $t2){
                $data=[];
                $now_trend2 = Db::table('keywords_crawl_log')->alias('a')->join('opponent b','a.op_id=b.op_id','left')
                    ->where('kw_id',$kw_arr['kw_id'])->where('a.op_id','=',$t2['op_id'])
                    ->where('create_time','between',$request['dateTime'])->order('create_time','desc')->field('op_name,rank,rank_m,create_time')->find();
                $data['value'] = $now_trend2['op_name'];
                $trend_LineChart_Data['title'][] = $now_trend2['op_name'].'_pc';
                $trend_LineChart_Data['title'][] = $now_trend2['op_name'].'_m';
                $data['data'] = [
                    ['title'=>'当前pc百度排名','number'=>$now_trend2['rank']],
                    ['title'=>'历史pc最高排名','number'=>$t2['min(rank)']],
                    ['title'=>'当前M百度排名','number'=>$now_trend2['rank_m']],
                    ['title'=>'历史M最高排名','number'=>$t2['min(rank_m)']]
                ];
                $trendData2[] = $data;
            }
        }
        $trend3_datas = Db::table('keywords_crawl_log')->where('kw_id',$kw_arr['kw_id'])->where('op_id',0)
            ->where('create_time','between',$request['dateTime'])->order('time')
            ->field("op_id,DATE_FORMAT(create_time,'%Y-%m-%d') as time,rank,rank_m")->select();

        $a1=[];
        $a2=[];
        foreach ($trend3_datas as $trend3_data){
            $trend_LineChart_Data['time'][] = $trend3_data['time'];
            $a1[]=$trend3_data['rank'];
            $a2[]=$trend3_data['rank_m'];
        }
        $trend_LineChart_Data['source'][] = ['name'=>$trend_LineChart_Data['title'][0],'type'=>'line','data'=>$a1];
        $trend_LineChart_Data['source'][] = ['name'=>$trend_LineChart_Data['title'][1],'type'=>'line','data'=>$a2];

        foreach ($trend_LineChart_Data['title'] as $k => $v){
            if ($k>1 && $k%2==0){
                $title=explode('_',$v);
                $tmp_data=Db::table('keywords_crawl_log')->alias('a')->join('opponent b','a.op_id=b.op_id','left')
                    ->where('kw_id',$kw_arr['kw_id'])->where('b.op_name',$title[0])
                    ->where('create_time','between',$request['dateTime'])->order('time','asc')
                    ->field("DATE_FORMAT(create_time,'%Y-%m-%d') as time,rank,rank_m")->select();
                $a=[];
                $b=[];
                foreach ($tmp_data as $tmp){
                    $a[] = $tmp['rank'];
                    $b[] = $tmp['rank_m'];
                }
                $trend_LineChart_Data['source'][] = ['name'=>$title[0].'_pc','type'=>'line','data'=>$a];
                $trend_LineChart_Data['source'][] = ['name'=>$title[0].'_m','type'=>'line','data'=>$b];
            }
        }
        return ['trendData1'=>$trendData1,'trendData2'=>$trendData2,'trend_LineChart_Data'=>$trend_LineChart_Data];
    }

    public function getOpponent()
    {
        $return_arr = [];
        $result = Db::table("opponent")->select();
        foreach ($result as $key=>$value) {
            $return_arr[$key]['check'] = false;
            $return_arr[$key]['value'] = $value['op_id'];
            $return_arr[$key]['label'] = $value['op_name'];
            $return_arr[$key]['media'] = $return_arr[$key]['rank'] = "---";
        }
        return ["initTraceCompare"=>$return_arr];
    }

    public static function getOpponentList()
    {
        $result = Db::table("opponent")->select();
        return ["initTraceSet"=>$result];
    }

    public static function opponent_add()
    {
        $request = Request::instance()->post();
        $op_name = empty($request['name'])?"":$request['name'];
        $op_domain = empty($request['url'])?"":$request['url'];
        if ($op_name == "" OR $op_domain == "")
            return ['type'=>false,"info"=>"未获取到添加内容!"];
        try{
            Db::table("opponent")->insert(['op_name'=>$op_name,'op_domain'=>$op_domain]);
            return ['type'=>true,"info"=>"添加新追踪成功!"];
        } catch (\Exception $e) {
            return ['type'=>false,"info"=>"添加新追踪对象失败，请联系管理员!"];
        }
    }

    public static function opponent_edit()
    {
        $request = Request::instance()->post();
        $id = !empty($request['id'])?$request['id']:"";
        $op_name = empty($request['name'])?"":$request['name'];
        $op_domain = empty($request['url'])?"":$request['url'];
        if ($op_name == "" OR $op_domain == "")
            return ['type'=>false,"info"=>"未获取到修改内容!"];
        try{
            $result = Db::table("opponent")->where("op_name","=",$op_name)->find();
            if ($result)
                return ['type'=>false,"info"=>"数据库中已有名为{$op_name}的追踪对象!"];
            Db::table("opponent")->where("op_id","=",$id)->update(['op_name'=>$op_name,'op_domain'=>$op_domain]);
            return ['type'=>true,"info"=>"修改追踪对象成功!"];
        } catch (\Exception $e) {
            return ['type'=>false,"info"=>"修改追踪对象失败，请联系管理员!"];
        }
    }

    public static function opponent_delete()
    {
        $request = Request::instance()->post();
        $id = !empty($request['id'])?$request['id']:"";
        if ($id == "")
            return ['type'=>false,"info"=>"未获取到删除内容!"];
        try{
            Db::table("opponent")->where("op_id","=",$id)->delete();
            return ['type'=>true,"info"=>"删除追踪目标成功!"];
        } catch (\Exception $e) {
            return ['type'=>false,"info"=>"删除追踪对象失败，请联系管理员!"];
        }
    }

    public static function index_query()
    {
        $request = Request::instance()->post();
        $query = !empty($request['query'])?$request['query']:"";
        $type = !empty($request['type'])?$request['type']:"";
        if ($query == "")
            return ['type'=>false,"info"=>"未获取到输入的标签!"];
        if ($type == "2") {
            $data = Db::table("labels")->field("l_name as label,l_id as value,'0' AS type")->where("l_name","=",$query)->select();
        } elseif ($type == "1") {
//            $data_nn = Db::table("labels")->field("l_name as label,l_id as value,'0' AS type")->where("l_name","=",$query)->select();
            $data = self::index_importquery();
        } else{
            return ['type'=>false,"info"=>"未获取到标签类型"];
        }
        return $data;
    }
    public static function index_importquery()
    {
        $request = Request::instance()->post();
        $query = !empty($request['query'])?$request['query']:"";
        if ($query == "")
            return ['type'=>false,"info"=>"未获取到输入的标签!"];
        $data = Db::table("labels_school")->field("l_name as label,s_id as value,'1' AS type")->where("l_name","=",$query)->select();
        if (empty($data)){
            //远程获取
            $data = curl_request('http://api.houxue.com/jsonapi/keywords/getschoolinfo',['name'=>$query]);
            $data = json_decode($data,true);
            if (isset($data['data']['LoginId'])){
                Db::table('labels_school')->where('s_id',$data['data']['LoginId'])->delete();
                Db::table('labels_school')->insert(['s_id'=>$data['data']['LoginId'],'l_name'=>$data['data']['Name']]);
                $data = Db::table("labels_school")->field("l_name as label,s_id as value,'1' AS type")->where("l_name","=",$query)->select();
            }else{
                $data=[];
            }
        }
        return $data;
    }

    public function keywords_schoollabel()
    {
        $request = Request::instance()->post();
        $keyword = !empty($request['keyword'])?$request['keyword']:"";
        if ($keyword == "")
            return ['type'=>false,"info"=>"未获取到关键词信息!"];
        $result = Db::table("keywords")->alias('k')->field("ls.l_name as name")
            ->join('keywords_label kl','k.kw_id=kl.kw_id','left')
            ->join("labels_school ls","ls.s_id=kl.l_id")->where("k.keyword","=",$keyword)->select();
        return $result;
    }
}