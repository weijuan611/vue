<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 11:12
 * Info: 原始单概况
 */
namespace app\index\controller\original;


use app\index\controller\Base;
use app\index\model\Moriginal;
use think\Log;
use think\Request;

class Original extends base
{
    public function index()
    {
        $model = new Moriginal();
        return json($model->getlist());
    }
}