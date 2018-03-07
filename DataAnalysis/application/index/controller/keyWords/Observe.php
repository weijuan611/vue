<?php
namespace app\index\controller\keyWords;

use app\index\controller\Base;
use app\index\model\Mobserve;
use think\Request;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11
 * Time: 10:18
 */
class Observe extends Base
{
    public function index()
    {
        $model = new Mobserve();
        return json($model->getkeyWordsInfo());
    }
    public function changetab()
    {
        $request = Request::instance()->post();
        $model = new Mobserve();
        $changeKeyword = !empty($request['keyword'])?trim($request['keyword']):"";
        $changeType = !empty($request['title'])?trim($request['title']):"";
        $dstype = !empty($request['type'])?trim($request['type']):"pc";
        return json($model->getUrlInfo($changeType,$changeKeyword,$dstype));
    }

    public function included()
    {
        $model = new Mobserve();
        return json($model->included());
    }
}