<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/13
 * Time: 10:55
 */
namespace app\index\controller\common;

use app\index\controller\Base;
use app\index\model\Keyword;
use app\index\model\Mlexicon;
use app\index\model\MMaterialKeywordList;
use app\index\model\User;
use think\Log;

class Dialog extends Base
{
    public function keywordCategory(){
        return json((new Mlexicon())->categories_init());
    }

    public function keyword(){
        $model = new Keyword();
        $result=$model->getListCommon();
        return json(['data'=>$result['data'],'total'=>$result['total']]);
    }

    public function user(){
        $model = new User();
        $result=$model->getListCommon();
        return json(['data'=>$result['data'],'total'=>$result['total']]);
    }

    public function article(){
        $model = new MMaterialKeywordList();
        $result=$model->getlist();
        return json(['data'=>$result['data'],'total'=>$result['total']]);
    }

    public function editerUpload(){
        return json(['imgname' => 'h', 'imgid' => '', 'imgurl' => 'hh']);
    }

    public function editerManage(){
        return json(['imgname' => 'h', 'imgid' => '', 'imgurl' => 'hh']);
    }

    public function editerServer(){
        date_default_timezone_set("Asia/Chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(EXTEND_PATH."uediter".DS."config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = include(EXTEND_PATH."uediter".DS."action_upload.php");
                break;
            /* 列出图片 */
            case 'listimage':
                $result = include(EXTEND_PATH."uediter".DS."action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include(EXTEND_PATH."uediter".DS."action_list.php");
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                $result = include(EXTEND_PATH."uediter".DS."action_crawler.php");
                break;
            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
}