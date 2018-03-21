<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/8
 * Time: 11:01
 */
namespace app\index\controller\material;

use app\index\controller\Base;
use app\index\model\MSourceList;
use think\Log;
use think\Request;

class Sourcelist extends Base
{
    public function init()
    {
        $model = new MSourceList();
        $data = $model->getlist();
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['sourceList']);
        return json($data);
    }
    public function material_add()
    {
        $model = new MSourceList();
        return json($model->material_add());
    }
    public function material_edit()
    {
        $model = new MSourceList();
        return json($model->material_edit());
    }
    public function material_delete()
    {
        $model = new MSourceList();
        return json($model->material_delete());
    }
}