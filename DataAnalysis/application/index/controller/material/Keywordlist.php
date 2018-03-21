<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 9:33
 */
namespace app\index\controller\material;

use app\index\controller\Base;
use app\index\model\MMaterialKeywordList;

class Keywordlist extends Base
{
    public function init()
    {
        $model = new MMaterialKeywordList();
        $data = $model->getlist();
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['material_keywordlist']);
        return json($data);
    }
    public function material_changetype()
    {
        $model = new MMaterialKeywordList();
        return json($model->material_changetype());
    }
}