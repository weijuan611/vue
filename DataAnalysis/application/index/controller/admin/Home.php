<?php
/**
 * 基本登入登出以及功能列表
 */
namespace app\index\controller\admin;

use app\common\Permission;
use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\Menu;
use app\index\model\User;
use think\Request;
use think\Session;

class Home extends Base
{
    public function index()
    {
        return 'hello tp5';
    }
    public function test()
    {
        return 'hello test';
    }

    public function menu()
    {
        $user_id = Session::get('org_user_id');
        if ($user_id==1){
            $menu_info=[];
            $menu_top = Menu::getTopMenu();
            if (!empty($menu_top)) {
                foreach ($menu_top as $k => $v) {
                    $child_menu= Menu::getChildMenu($v['menu_id']);
                    if(!empty($child_menu)){
                        $menu_top[$k]['subs']=$child_menu;
                        $menu_info[]=$menu_top[$k];
                    }
                }
            }
            return json($menu_info);
        }else{
            $menu_info=[];
            $menu_top = Menu::getTopMenu1($user_id);
            if (!empty($menu_top)) {
                foreach ($menu_top as $k => $v) {
                    $child_menu= Menu::getChildMenu1($v['menu_id'],$user_id);
                    if(!empty($child_menu)){
                        $menu_top[$k]['subs']=$child_menu;
                        $menu_info[]=$menu_top[$k];
                    }
                }
            }
            return json($menu_info);
        }
    }

    public function login()
    {
        $pass= $this->validate($this->request->post(),[
            'username'=>'require|min:5|max:30',
            'password'=>'require|min:6|max:30'
        ]);

        if(true !== $pass){
            // 验证失败 输出错误信息
            return $pass;
        }

        return json(User::login($this->request->post('username'),$this->request->post('password')));
    }

    public function logout()
    {
        User::logout();
        return json('退出登录成功！');
    }

    public function super_login(){
        $user_id = $this->request->get('id',0);
        if(Session::has('is_super_user')&&Session('is_super_user') == SUPER_USER_ID){
            if($user_id == 0){
                $user = User::get(['user_id'=>SUPER_USER_ID])->toArray();
                Session('org_user_id',$user['user_id']);
                Session('org_user_name',$user['user_name']);
                Session::delete('is_super_user');
                return json(['error'=>0,'msg'=>$user['user_name']]);
            }else{
                return json(['error'=>1,'msg'=>'请先返回超级管理！']);
            }
        }else{
            if($user_id <=0){
                return json(['error'=>1,'msg'=>'编号错误！请刷新']);
            }
            if(Session('org_user_id') == SUPER_USER_ID){
                $user = User::get(['user_id'=>$user_id])->toArray();
                Session('org_user_id',$user['user_id']);
                Session('org_user_name',$user['user_name']);
                Session('is_super_user',SUPER_USER_ID);
                return json(['error'=>0,'msg'=>$user['user_name']]);
            }else{
                return json(['error'=>1,'msg'=>'只有超级管理员才可以进行模拟登录!']);
            }
        }

    }

}
