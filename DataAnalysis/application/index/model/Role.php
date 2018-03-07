<?php

namespace app\index\model;

use app\common\Utility;
use app\index\model\Departments;
use app\index\controller\Zlog;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Log;
use think\Model;
use think\Session;
use think\Request;
use app\common\Permission;

class Role extends Model
{
    public  function getList()
    {
        $request = Request::instance()->post();
        $ID = isset($request["search"]["ID"])?trim($request["search"]["ID"]):"";
        $query_a = Db::table('org_roles');
        $query_b = Db::table('org_roles');
        if ($ID){
            $query_a->where('ID','=',$ID);
            $query_b->where('ID','=',$ID);
        }
        $total = $query_a->count();
        $pagesize = isset($request["page"]["pageSize"])?trim($request["page"]["pageSize"]):"50";
        $currentPage = isset($request["page"]["currentPage"])?trim($request["page"]["currentPage"]):"1";
        $result = $query_b->page($currentPage,$pagesize)->select();
        $dp_option =[];
        (new Departments())->getTree($dp_option);
        $dp_option[]=['id'=>0,'label'=>'全部'];
        return [
            "tableData"=>$result,
            "total"=>$total,
            'dp_option'=>$dp_option,
        ];
    }

    /**
     * @return array
     */
    public function add()
    {
        $req = Request::instance()->post();
        $req = $req['formData'];
        if (!$req['desc']){
            return ['status'=>400,'msg'=>'请填写完整！'];
        }
        try{
            Db::table('org_roles')->insert(['Title'=>time(),'Description'=>$req['desc']]);
        }catch (\Exception $e){
            return [
                'status'=>400,'msg'=>'标志重复'
            ];
        }
        return [
          'status'=>200,'msg'=>'添加用户组成功'
        ];
    }

    public function editShow($role_id)
    {
        $test_arr = $new_test_arr = [];
        $opstions = Db::table('org_permissions')->field(['ID as value','Description as label','IDParent as p_id'])->order('label')->select();
        foreach ($opstions as $key=>$value) {
            $prefix = strstr($value['label'], '-', TRUE);
//            $pinyin = Utility::Pinyin($prefix);
            $test_arr[$prefix][$key]['id'] = $value['value'];
            $test_arr[$prefix][$key]['label'] = $value['label'];
            $test_arr[$prefix][$key]['p_id'] = $value['p_id'];
        }
        foreach ($test_arr as $key=>$value) {
            foreach ($value as $list=>$info) {
                if ($info['p_id'] == 0) {
                    $new_test_arr[$key]['id'] = $info['id'];
                    $new_test_arr[$key]['label'] = $key;
                } else{
                    $childrenarr[$key][$list]['id'] = $info['id'];
                    $childrenarr[$key][$list]['label'] = $info['label'];
                    $new_test_arr[$key]['children'] = array_values($childrenarr[$key]);
                }
            }
        }
        $true_arr = array_values($new_test_arr);
        $selected = Db::table('org_rolepermissions')->where('RoleID','=',$role_id)->column('PermissionID');
        $range=Db::table('org_roles')->where('ID','=',$role_id)->value('Range','');
        $dp_id=[];
        if($range !=''){
            $dp_id = explode(',',$range);
            $dp_id=array_map('intval',$dp_id);
        }
        return [
            'options'=>$true_arr,
            'selected'=>$selected,
            'dp_id'=>$dp_id,
        ];
    }
    public function editSave($post)
    {
        $req = $post['table'];
        if (!$req['desc']){
            return ['type'=>'error','msg'=>'请填写完整！'];
        }
        $seleced = $req['selected'];
        $req['dp_id']=array_unique($req['dp_id']);
//        Db::startTrans();
        try{
            Db::table('org_roles')->where('ID','=',$req['id'])
                ->update(['Description'=>$req['desc'],'KeywordNum'=>$req['num'],'Range'=>implode(',',$req['dp_id'])]);
            Db::table('org_rolepermissions')->where('RoleID','=',$req['id'])->delete();
            $objRbac = new Permission();
            if (!empty($seleced)){
                foreach ($seleced as $v){
                    $objRbac->assign($req['id'],$v);
                }
            }
//            Db::commit();
        } catch (\Exception $e) {
//            Db::rollback();
            return ['type'=>'error','msg'=>'数据库操作失败！'];
        }
        return ['type'=>'success','msg'=>'操作成功！'];
    }

    public function roleShow($user_id)
    {
        $opstions = Db::table('org_roles')->field(['ID as value','Description as label'])->select();
        $selected = Db::table('org_userroles')->where('UserID','=',$user_id)->column('RoleID');
        $result = Db::table('users')->where('user_id','=',$user_id)->find();
        return [
            'options'=>$opstions,
            'selected'=>$selected,
            'dp_id'=>$result['dp_id'],
            'user_name'=>$result['user_name'],
            'email'=>$result['email'],
        ];
    }
    public function roleSave($req)
    {
        if (!$req['selected'] || !$req['user_id']){
            return ['type'=>'error','msg'=>'未获取到信息！'];
        }
        try{
            Db::table('org_userroles')->where('UserID','=',$req['user_id'])->delete();
            $objRbac = new Permission();
            foreach ($req['selected'] as $v){
                $objRbac->Users->assign($v,$req['user_id']);
            }
            Db::table('users')->where('user_id','=',$req['user_id'])
                ->update(['dp_id'=>$req['dp_id'][0],"user_name"=>$req['user_name'],"email"=>$req['email']]);
        } catch (\Exception $e) {
            return ['type'=>'error','msg'=>'数据库操作失败！'];
        }
        return ['type'=>'success','msg'=>'操作成功！'];
    }
}
