<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/12
 * Time: 13:57
 */
namespace app\index\controller\material;

use app\index\controller\Base;
use app\index\model\MAssessment;

class Assessment extends Base
{
    public function init()
    {
        $model = new MAssessment();
        return json($model->getlist());
    }
}