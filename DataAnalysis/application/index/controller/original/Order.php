<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/30
 * Time: 11:29
 */
namespace app\index\controller\original;

use app\index\controller\Base;
use app\index\model\Mobserveorder;

class Order extends Base
{
    public function index()
    {
        $model = new Mobserveorder();
        return json($model->getlist());
    }
}