<?php

namespace app\index\model;

use think\Db;
use think\Exception;
use think\Model;

/**
 * 百度收录 wdy
 * Class BaiduArchive
 * @package app\index\model
 */
class BaiduArchive extends Model{
    /**
     * 拉取url提交百度收录
     * @return array|bool
     */
    public static function submitBdUrl()
    {
        // 获取最新百度收录cookie
        $cookie_arr = Db::table('user_account')->where('alive', '=', 1)->column('cookie');
        // 随机取一个
//        $rand_cookie = $cookie_arr[2];
        $rand_cookie = $cookie_arr[array_rand($cookie_arr,1)];
        if(!$rand_cookie){
            return ['type' => 'error', 'msg' => '提交收录失败，未发现有效的cookie'];
        }
        // 需要提交的url
        $data = Db::table('keywords_apply')->where('status', '=', 0)->column('ka_id,kd_id,url');
        $kd_ids = [];
        if(!empty($data)){
            foreach ($data as $k => $v){
                $kd_ids[] = $v['kd_id'];
                //提交百度收录
                $url = 'https://ziyuan.baidu.com/linksubmit/urlsubmit';
                $post_data = ['url'=>$v['url']];
                $res = curl_request($url,$post_data,true,$rand_cookie);
                $res_arr = json_decode($res,true);
                if(!$res_arr){
                    return ['type' => 'error', 'msg' => '提交收录失败，请及时更新cookie'];
                }
            }
        }
        //提交url完成，等待百度通过，标记为已提交
        try {
            Db::table('keywords_apply')->where('kd_id', 'IN', $kd_ids)->update(['status' => 1]);
        } catch (Exception $e) {
            return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage().$e->getTraceAsString()];
        }
        return true;
    }

    /**
     * 检查已提交的url是否已被百度收录
     * @return array|bool
     */
    public static function checkBdUrl()
    {
        // 获取最新百度收录cookie
        $cookie_arr = Db::table('user_account')->where('alive', '=', 1)->column('cookie');
        // 随机取一个
//        $rand_cookie = $cookie_arr[2];
        $rand_cookie = $cookie_arr[array_rand($cookie_arr,1)];
        if(!$rand_cookie){
            return ['type' => 'error', 'msg' => '提交收录失败，未发现有效的cookie'];
        }
        // 需要检查是否被百度收录的url
        $data = Db::table('keywords_apply')->where('status', '=', 1)->column('ka_id,kd_id,url');
        $kd_ids = [];
        $kd_passed_ids = [];
        if(!empty($data)){
            foreach ($data as $k => $v){
                $kd_ids[] = $v['kd_id'];
                //提交百度收录
                $url = 'https://ziyuan.baidu.com/linksubmit/urlsubmit';
                $post_data = ['url'=>$v['url']];
                $res = curl_request($url,$post_data,true,$rand_cookie);
                $res_arr = json_decode($res,true);
                if(!$res_arr){
                    return ['type' => 'error', 'msg' => '提交收录失败，请及时更新cookie'];
                }
                $status = $res_arr['status'];// 是否已被百度收录
                if($status != 0){
                    // 已被收录
                    $kd_passed_ids[] = $v['kd_id'];
                }
            }
        }
        //已被百度收录，更新keywords_apply和keywords_detail表的收录状态
        if(!empty($kd_passed_ids)){
            try {
                Db::table('keywords_apply')->where('kd_id', 'IN', $kd_passed_ids)->update(['status' => 2]);
                Db::table('keywords_detail')->where('id', 'IN', $kd_passed_ids)->update(['is_alive' => 1]);
            } catch (Exception $e) {
                return ['type' => 'error', 'msg' => $e->getLine() . '数据库操作失败!' . $e->getMessage().$e->getTraceAsString()];
            }
        }
        return true;
    }
}