<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/11/1
 * Time: 11:28
 */
namespace app\index\model;

use app\index\controller\Zlog;
use think\Db;
use think\Session;
use think\Request;
use think\Log;

class User extends Base
{
    protected $table = 'users';

    public static function getTopMenu(){
        return self::all(function ($query){
            $query->where('disabled',0)->where('parent_id',0)->order('display_order','asc')->field("menu_id,name as title,tableid as name,pnode_id,icon");
        });
    }

    public static function getChildMenu($parent_id){

        return self::all(function ($query)use($parent_id){
            $query->where('disabled',0)->where('parent_id',$parent_id)->order('display_order','asc')
            ->field('name as title,tableid as name,pnode_id,icon');
        });

    }

    public static function login($login_name,$password){
        $user = self::where('login_name',$login_name)->where('status',1)->find();
        if(!empty($user->data) && $user->password === md5($password)){
            Session::set('org_user_id',$user->user_id);
            Session::set('org_user_name',$user->user_name);
            Session::set('org_dp_id',$user->dp_id);
            Session::set('org_user_sn',$user->user_sn);
            Session::set('type',"PC");
            Log::log('登录成功！'.Session::get('org_user_id'));
            self::where('login_name',$login_name)->where('status',1)->update(['update_time'=>date('Y-m-d H:i:s')]);
            return $user->user_name;
        }else{
            return false;
        }
    }

    public static function logout(){
        Session::delete('org_user_id');
        Session::delete('org_user_name');
        Session::delete('org_dp_id');
    }

    public static function getUserlist()
    {
        $request = Request::instance()->post();
        $userid = isset($request["search"]["user_id"])?trim($request["search"]["user_id"]):"";
        $username = isset($request["search"]["user_name"])?trim($request["search"]["user_name"]):"";
        $limit = isset($request["page"]["pageSize"])?trim($request["page"]["pageSize"]):"50";
        $page = isset($request["page"]["currentPage"])?trim($request["page"]["currentPage"]):"1";
        $query = Db::table("users")->field('`user_id`,`user_name`,`login_name`,`email`,`update_time`,`status`')->where("status",1);
        if (!empty($userid)){
            $query = $query->where("user_id",$userid);
        }
        if (!empty($username)){
            $query = $query->where("user_name",'like','%'.$username.'%');
        }
        $sql = $query->page("{$page},{$limit}")->fetchSql(true)->select();
        $countsql = str_replace('`user_id`,`user_name`,`login_name`,`email`,`update_time`,`status`', 'COUNT(*) AS total', $sql);
        $countsql = stristr($countsql,"LIMIT",TRUE);
        $result = Db::query("{$sql}");
        $count = Db::query("{$countsql}");
        foreach ($result as $key=>$value){
            $value["status"] == 1?$result[$key]["status"] = "正常":$result[$key]["status"] = "停用";
        }
        return [
            "tableData"=>$result,
            "total"=>$count[0]["total"],
        ];
    }

    public function getListCommon(){
        $query = Db::table('users u')->join('departments d','d.dp_id = u.dp_id','left')
            ->field('u.user_id,u.user_name,d.dp_id,d.dp_name')->where('u.status','=',1);
        $this->checkSearch($query,['user_name'=>['u.user_name','like']]);
        $this->checkRange($query,'u.dp_id','u.user_id');
        return $this->autoPaginate($query)->toArray();
    }
}
