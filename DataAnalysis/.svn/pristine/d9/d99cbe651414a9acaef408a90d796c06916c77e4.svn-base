<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/21
 * Time: 13:53
 */

namespace app\index\model;


use think\Db;

class Area extends Base
{
    public function getListCommon(){
        $query = Db::table('sys_area')
            ->field('Id area_id,AreaName area_name')->where('isdel','=',1);
        $this->checkSearch($query,['area_name'=>['AreaName','like']]);
        return $this->autoPaginate($query)->toArray();
    }
}