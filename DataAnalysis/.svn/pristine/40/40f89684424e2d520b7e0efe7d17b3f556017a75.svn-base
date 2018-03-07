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
use think\Model;

class Menu extends Model
{
    protected $table = 'sys_menu';

    public static function getTopMenu(){
        return self::all(function ($query){
            $query->where('disabled',0)->where('parent_id',0)->order('display_order','asc')->field("menu_id,name as title,tableid as name,pnode_id,icon,iconColor");
        });
    }
    public static function getTopMenu1($user_id){
        $permissions = getAllPermissions($user_id);
        $parent_id = Db::table('sys_menu')->distinct(true)->field(['parent_id'])->where('permission_id','in',$permissions)->column('parent_id');
        return self::all(function ($query) use ($parent_id){
            $query->where('menu_id','in',$parent_id)->order('display_order','asc')->field("menu_id,name as title,tableid as name,pnode_id,icon,iconColor");
        });
    }
    public static function getChildMenu($parent_id){

        return self::all(function ($query)use($parent_id){
            $query->where('disabled',0)->where('parent_id',$parent_id)->order('display_order','asc')
            ->field('name as title,tableid as name,pnode_id,icon');
        });

    }
    public static function getChildMenu1($parent_id,$user_id){
        $permissions = getAllPermissions($user_id);
        return self::all(function ($query)use($parent_id,$permissions){
            $query->where('disabled',0)->where('permission_id','in',$permissions)->where('parent_id',$parent_id)->order('display_order','asc')
                ->field('name as title,tableid as name,pnode_id,icon');
        });

    }
}
