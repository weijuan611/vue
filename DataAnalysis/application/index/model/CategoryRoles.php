<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/1/29
 * Time: 14:23
 */
namespace app\index\model;

use think\Db;
use think\Exception;

class CategoryRoles extends Base
{
    protected $table = 'category_roles';

    public function getCategoryByUser($user_id){
        $result = [];
        $two_cid = $three_cid  = $one_arr_true = $two_arr_true = $ttwo_arr_true = $three_arr_true = [];
        $one_arr = Db::table("categories")->field("c_id as id,pc_id,catename as value")->where("pc_id","0")->select();
        foreach ($one_arr as $key => $value) {
            $two_cid[] = $value['id'];
        }
        $two_query = Db::table("categories")->field("c_id as id,pc_id,catename as value");
        $two_query = $this->createquery($two_query,"pc_id",$two_cid);
        $two_arr = $two_query->order("pc_id","asc")->order("c_id","asc")->select();
        foreach ($two_arr as $key => $value) {
            $three_cid[] = $value['id'];
        }
        $three_query = Db::table("categories")->field("c_id as id,pc_id,catename as value");
        $three_query = $this->createquery($three_query,"pc_id",$three_cid);
        $three_arr = $three_query->order("pc_id","asc")->order("c_id","asc")->select();
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
        $result['classList'] = $one_arr_true;
        $result['cateSelected'] = Db::table('category_roles')->where('user_id','=',$user_id)->column('c_id');
        return $result;
    }

    public function saveCategoryByUser($user_id,$c_id_arr){
        try{
            Db::table($this->table)->where('user_id','=',$user_id)->delete();
            if(!empty($c_id_arr)){
                foreach ($c_id_arr as $key=>$value){
                    $c_id_arr[$key]=[
                        'user_id'=>$user_id,
                        'c_id'=>$value,
                        'add_time'=>date('Y-m-d H:i:s')
                    ];
                }
                Db::table($this->table)->insertAll($c_id_arr);
            }
        }catch (Exception $e){
            return $e->getMessage();
        }
        return '添加成功！';
    }
}