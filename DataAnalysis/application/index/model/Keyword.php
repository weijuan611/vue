<?php

namespace app\index\model;
use think\Db;

/**
 * 关键词类 wdy
 * Class Keyword
 * @package app\index\model
 */
class Keyword extends Base
{
    public function getListCommon(){
        $query = Db::table('keywords k')
            ->join('keywords_check kc','kc.kw_id = k.kw_id','left')
            ->join('categories c','c.c_id = k.c_id','left')
            ->join('users u','k.user_id = u.user_id','left')
            ->field('k.kw_id,k.keyword,c.catename')
            ->where('k.status','<>',3)
            ->where('kc.status','=',1);
        $this->checkSearch($query,['keyword'=>['k.keyword','like']]);
        $this->checkRange($query,'u.dp_id','u.user_id');
        return $this->autoPaginate($query)->toArray();
    }
}