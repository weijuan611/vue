<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/1/24
 * Time: 10:53
 */
namespace app\index\model;

use app\common\Spider;
use app\common\Utility;
use think\Db;
use think\Log;

class Account extends Base
{
    protected $table = 'user_account';

    public  function getBaiduIndex($keyword){
        $keyword = urlencode(mb_convert_encoding(trim($keyword),'gb2312','utf-8'));
        $cookie = $this->getCookieAlive();
        if($cookie){
          exec('phantomjs '.EXTEND_PATH.'crawler_baidu_index.js '.$keyword.' "'.$cookie.'" "'.ROOT_PATH.'baidu/index_img/"');
          exec('tesseract '.ROOT_PATH.'baidu/index_img/'.$keyword.'-1.png '.ROOT_PATH.'baidu/index_txt/'.$keyword.'-1');
          $pc_index =(int)str_replace(',','',file_get_contents(ROOT_PATH.'baidu/index_txt/'.$keyword.'-1.txt'));
          exec('tesseract '.ROOT_PATH.'baidu/index_img/'.$keyword.'-2.png '.ROOT_PATH.'baidu/index_txt/'.$keyword.'-2');
          $m_index =(int)str_replace(',','',file_get_contents(ROOT_PATH.'baidu/index_txt/'.$keyword.'-2.txt'));
          return [$pc_index,$m_index];
        }else{
            return false;
        }
    }

    public function getBaiduLoginImg($id){
        $cookie = Db::table($this->table)->where('id','=',(int)$id)->value('cookie');
        $str = Utility::randomString();
        if($cookie){
            exec('phantomjs '.EXTEND_PATH.'crawler_baidu_login.js "'.$cookie.'" "'.ROOT_PATH.'public/index_login/'.$str.'"');
            return 'index_login/'.$str.'.png';
        }else{
            return false;
        }
    }

    private  function getCookieAlive(){
        $id_arr=Db::table($this->table)->where('alive','=',1)->limit(100)->column('id');
        if(count($id_arr)>0){
            $key = rand(0,count($id_arr)-1);
            return Db::table($this->table)->where('id','=',$id_arr[$key])->value('cookie');
        }else{
            return false;
        }
    }

    /**
     * 获取PC/M端百度排名及其原始链接
     * @author wdy
     * @param $keyword
     * @return $arr
     */
    public function getBaiduRank($keyword)
    {
//        $param = urlencode($keyword);
//        $file = DOCROOT . 'scripts/spider.py ' . $param;
//        $cmd = "python " . $file;
//        $descriptorspec = array(
//            0 => array("pipe", "r"),    // stdin
//            1 => array("pipe", "w"),    // stdout
//            2 => array("pipe", "w")     // stderr
//        );
//        $proc = proc_open($cmd, $descriptorspec, $pipes, null, null);
//        if (is_resource($proc)) {
//            $stdout = stream_get_contents($pipes[1]);
//            fclose($pipes[1]);
//            $stderr = stream_get_contents($pipes[2]);
//            fclose($pipes[2]);
//            $status = proc_close($proc);  // 释放proc
//        } else {
//            $stdout='';
//            $stderr = "返回非资源文件";
//            $status = -1;
//        }
//        $msg = array(
//            'out' => $stdout,
//            'info' => $stderr,
//            'code' => $status
//        );
//        Log::write($msg);
//        $result = json_decode($msg["out"], true);
        $result = (new Spider($keyword))->getResults();
        return $result;
    }
}
