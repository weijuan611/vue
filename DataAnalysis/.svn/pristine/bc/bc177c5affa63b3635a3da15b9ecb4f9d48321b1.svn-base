<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/1/24
 * Time: 10:51
 */
namespace app\index\controller\admin;
use app\common\BULogin;
use app\index\controller\Base;

class Account extends Base
{
    public function add(){
        $model = new \app\index\model\Account();
        $name = $this->request->post('baidu_name');
        $pwd = $this->request->post('baidu_pwd');
        $id = $this->request->post('id');
        if($name != null&&trim($name)!=''&&trim($pwd)!=""){
            if($id >0){
                $model->save([
                    'baidu_name'=>trim($name),
                    'baidu_pwd'=>base64_encode(trim($pwd)),
                    'update_time'=>date('Y-m-d H:i:s')
                ],['id'=>$id]);
                return json('账号编辑成功！');
            }else{
                $model->insert([
                    'baidu_name'=>trim($name),
                    'baidu_pwd'=>base64_encode(trim($pwd)),
                    'user_id'=>session('org_user_id'),
                    'add_time'=>date('Y-m-d H:i:s')
                ]);
                return json('账号添加成功！');
            }
        }else{
            return json('账号或密码不可为空！');
        }
    }

    public function index(){
        $model = new \app\index\model\Account();
        $query =$model->getQuery()->field('baidu_name,add_time,update_time,id');
        if(session('org_user_id') != 1){
            $query->where('user_id','=',session('org_user_id'));
        }
        $data=$model->autoPaginate($query)->toArray();
        return json(['tableData'=>$data['data'],'total'=>$data['total']]);
    }

    public function info(){
        $id = $this->request->get('id');
        if($id >0){
            $model = new \app\index\model\Account();
            $info = $model->where('id','=',$id)->field('id,baidu_name,baidu_pwd')->find();
            $info['baidu_pwd']=base64_decode($info['baidu_pwd']);
            return json(['info'=>$info]);
        }else{
            return json('编号错误，请刷新!',400);
        }
    }

    public function login(){
        $id = $this->request->get('id');
        if($id >0){
            $model = new BULogin($id);
            $img=$model->getCode();
            return json(['id'=>$id,'img'=>$img,'msg'=>'']);
        }else{
            return json('编号错误！请刷新',400);
        }
    }

    public function code(){
        $id = $this->request->post('id');
        $code = $this->request->post('code');
        if($id >0){
            $model = new BULogin($id);
            $msg=$model->loginByCode($code);
            if($msg){
                \app\index\model\Account::update(['alive'=>1],['id'=>$id]);
            }
            return $msg?json(['error'=>0,'msg'=>'登录成功！']):json(['error'=>$model->err_code,'msg'=>'登录失败！'.$model->err_msg]);
        }else{
            return json('编号错误！请刷新',400);
        }
    }

    public function img(){
        $id = $this->request->get('id');
        if($id >0){
            $model = new \app\index\model\Account();
            return json($model->getBaiduLoginImg($id));
        }else{
            return json('编号错误！请刷新',400);
        }
    }
}