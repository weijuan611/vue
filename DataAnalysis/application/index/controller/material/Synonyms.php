<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 13:55
 */
namespace app\index\controller\material;

use app\index\controller\Base;
use app\index\model\MSynonyms;

class Synonyms extends Base
{
    public function init()
    {
        $model = new MSynonyms();
        $data = $model->getlist();
        $data['buttonControl'] = buttonDisable($this->permission,$this->route['material_synonyms']);
        return json($data);
    }
    public function material_changetype()
    {
        $model = new MSynonyms();
        return json($model->material_changetype());
    }
    public function material_addSynonyms()
    {
        $model = new MSynonyms();
        return json($model->material_addSynonyms());
    }
}