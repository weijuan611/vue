<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/26
 * Time: 10:29
 */

namespace app\index\model;


use app\index\controller\Zlog;
use think\Model;
use think\db\Query;
use think\Request;
use think\Log;
use think\Db;

class Base extends Model
{

    protected function initialize()
    {
        parent::initialize();

    }

    public function autoPaginate(Query &$Query,$listRows = null, $simple = false, $config = [], $prop_p = '', $order_p = '')
    {
        $post = Request::instance()->post();
        $post = isset($post['paginate'])?$post['paginate']:[];

        if(!empty($post)&&$listRows == null){
            $listRows = isset($post['pageSize'])?$post['pageSize']:50;
        }
//        if(!empty($post)&&$simple == false){
//            $simple = isset($post['total'])&&$post['total']>0?$post['total']:null;
//        }
        if(!empty($post)&&empty($config)){
            $config['page']=isset($post['currentPage'])?$post['currentPage']:1;
        }
        if (empty($prop_p) && empty($order_p)) {
            if(!empty($post)&&isset($post['prop'])){
                $column=$post['prop'];
                $order=$post['order'] == 'descending'?'desc':'asc';
                return $Query->order($column,$order)->paginate($listRows, $simple, $config);
            }else{
                return $Query->paginate($listRows, $simple, $config);
            }
        } else {
            return $Query->order($prop_p,$order_p)->paginate($listRows, $simple, $config);
        }
    }

    /**
     * 添加权限范围
     * @param Query $query
     * @param string $dp_column 部门编号字段
     */
    protected function checkRange(Query &$query,$dp_column,$user_column){
        $user_id = session('org_user_id');
        if($user_id != SUPER_USER_ID){
            $data = Db::table('org_userroles ou')->join('org_roles or','ou.RoleID = or.ID','left')
                ->where('ou.UserID','=',$user_id)->column('or.Range');
            if(!empty($data)&&$data[0]!=''){
                $dp_arr=[];
                foreach ($data as $item){
                    $dp_arr =array_merge($dp_arr,explode(',',$item));
                }
                $dp_arr=array_unique($dp_arr);
                if(!in_array(0,$dp_arr)){
                    $query->where($dp_column,'in',$dp_arr);
                }
            }else{
                $query->where($user_column,'=',$user_id);
            }
        }
    }


    public static function arridssort($arrids)
    {
        if ($arrids) {
            $arrids = array_unique($arrids);
            asort($arrids);
            $arrids = array_merge($arrids);
            $newarrids = array();
            $j = 0;
            for ($i = 0; $i < count($arrids); $i++) {
                if (isset($arrids[$i])) {
                    if (!isset($newarrids[$j])) {
                        $newarrids[$j]['startnum'] = $arrids[$i];
                        $newarrids[$j]['endnum'] = $arrids[$i];
                    } else {
                        if ((int)$arrids[$i] - 1 != (int)$arrids[$i - 1]) {
                            $j++;
                            $newarrids[$j]['startnum'] = $arrids[$i];
                            $newarrids[$j]['endnum'] = $arrids[$i];
                        } else {
                            $newarrids[$j]['endnum'] = $arrids[$i];
                        }
                    }


                }
            }
            return $newarrids;
        } else {
            return '';
        }
    }


    public function createquery(Query $query,$colum,$arrids,$type=0)
    {
        $curareaids=self::arridssort($arrids);
        if($curareaids){
            if($type){
                $query->whereOr(function (Query $query)use($curareaids,$colum){
                    foreach($curareaids as $key=>$val){
                        if(isset($val['startnum'])&&isset($val['endnum'])){
                            if($val['startnum']==$val['endnum']){
                                $query->whereOr($colum, '=', $val['startnum']);
                            }else{
                                $query->whereOr($colum, 'between', array($val['startnum'],$val['endnum']));
                            }
                        }
                    }
                });
            }else{
                $query->where(function (Query $query)use($curareaids,$colum){
                    foreach($curareaids as $key=>$val){
                        if(isset($val['startnum'])&&isset($val['endnum'])){
                            if($val['startnum']==$val['endnum']){
                                $query->whereOr($colum, '=', $val['startnum']);
                            }else{
                                $query->whereOr($colum, 'between', array($val['startnum'],$val['endnum']));
                            }
                        }
                    }
                });
            }

        }
        return $query;
    }
}