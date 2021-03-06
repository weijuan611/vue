<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18
 * Time: 17:06
 * Info: 获取用户列表;添加用户;编辑用户;删除用户;
 */
namespace app\index\controller\admin;

use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\User;
use think\Db;
use think\Request;
use app\index\model\Departments;

class Userinfo extends Base
{
    public function index()
    {
        $data = User::getUserlist();
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['adminUser']);
        $model = new Departments();
        $result = [];
        $model->getTree($result);
        $data['dpOptions']=$result;
        $data['is_admin']= session('org_user_id') == SUPER_USER_ID ? true:false;
        return json($data);
    }
    public function add()
    {
        $result = Request::instance()->post();
        $data = [
            'user_name'=>$result["user_name"],
            'login_name'=>$result["login_name"],
            'email'=>$result["email"],
            'password'=>md5($result["password"]),
            'add_time'=>date("Y-m-d H:i:s"),
            'status'=>1,
        ];
        Db::table("users")->insert($data);
        return json("添加用户成功!");
    }
    public function edit()
    {
        $result = Request::instance()->post();
        $data = [];
        $user_name = isset($result["data"]["user_name"])?$result["data"]["user_name"]:"";
        $email = isset($result["data"]["email"])?$result["data"]["email"]:"";
        $password = isset($result["data"]["password"])?$result["data"]["password"]:"";
        $repassword = isset($result["data"]["repassword"])?$result["data"]["repassword"]:"";
        $userid = isset($result["userid"])?$result["userid"]:"";
        if ($user_name != ""){
            $data["user_name"] = $user_name;
        }
        if ($email != ""){
            $data["email"] = $email;
        }
        if ($password != "" && $repassword!= "" && $password === $repassword){
            $data["password"] =md5($password);
        }
        try{
            Db::table("users")->where("user_id",$userid)->update($data);
        }catch(\Exception $e){
            $this->error('执行错误');
        }
        return json("编辑用户成功!");
    }

    public function delete()
    {
        $result = Request::instance()->post();
        $user_id = isset($result["user_id"])?$result["user_id"]:"";
        if($user_id == 1){
            return json('超级管理员账号不能删除！');
        }
        if ($user_id != ""){
            Db::table("users")->where("user_id",$user_id)->delete();
            return json('删除用户成功！');
        }else{
            return json('人员编号错误！',201);
        }
    }
}