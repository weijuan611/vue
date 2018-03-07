<?php
namespace app\index\controller\admin;

use app\index\controller\Base;
use app\common\Permission;
use app\index\model\CategoryRoles;
use app\index\model\Role;
use app\index\controller\Zlog;
use think\Exception;
use think\Request;
use think\Db;

class Roles extends Base
{

    public function index()
    {
        $m = new Role();
        $data = $m->getList();
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['adminRole']);
        return json($data);
    }

    public function add()
    {
        $m = new Role();
        return json($m->add());
    }
    public function edit()
    {
        $m = new Role();
        $post = Request::instance()->post();
        if (isset($post['type'])){
            return json($m->editShow($post['id']));
        }else{
            return json($m->editSave($post));
        }
    }

    /**
     * 给用户分配组
     * @return \think\response\Json
     */
    public function usertorole()
    {
        $m = new Role();
        $post = Request::instance()->post();
        if (isset($post['type'])){
            return json($m->roleShow($post['id']));
        }else{
            return json($m->roleSave($post));
        }
    }
    public function delete()
    {
        $result = Request::instance()->post();
        $id = isset($result["id"])?$result["id"]:"";
        if ($id != ""){
            try{
                Db::table("org_roles")->where("ID",$id)->delete();
                Db::table("org_rolepermissions")->where("RoleID",$id)->delete();
                Db::table("org_userroles")->where("RoleID",$id)->delete();
            }catch (Exception $e){
                return json('操作数据库失败！',201);
            }
            return json('删除用户成功！');
        }else{
            return json('人员编号错误！',201);
        }
    }

    public function category(){
        $model = new CategoryRoles();
        if($this->request->method() == 'GET'){
           return json($model->getCategoryByUser($this->request->get('id',0)));
        }else{
            $user_id = $this->request->post('user_id',0);
            if($user_id >0){
                return json($model->saveCategoryByUser($user_id,$this->request->post('cate/a',[])));
            }
        }
    }

}
