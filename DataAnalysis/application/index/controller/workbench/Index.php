<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/15
 * Time: 17:17
 */

namespace app\index\controller\workbench;

use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\SourceAnalysis;
use app\index\model\UrlCount;
use app\index\model\UrlCountDetail;
use think\Request;
use think\Session;

class Index extends Base
{
    public function index()
    {
        $model_uc = new UrlCount();
        $bench = $model_uc->getBench();
        $result['LLLineChart']=$bench;

//        $model_ucd = new UrlCountDetail();
//        $result['visitorTrendData']=$model_ucd->getBench();
//        $result['sourseDomainData']=SourceAnalysis::getBench();
        $result['buttonDisable']=buttonDisable($this->permission,$this->route['workbench']);
        return json($result);
    }
    public function add()
    {
        $request = Request::instance()->post();
    }

    public function changeselecttype()
    {
        $request = Request::instance()->get();
        if ($request['type'] === "M"){
            Session::set("type","M");
        } else {
            Session::set("type","PC");
        }
        return json("成功切换！！");
    }
}
