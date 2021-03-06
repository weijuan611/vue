<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 11:43
 * Info: 趋势分析与对比
 */

namespace app\index\controller\analysis;

use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\MtrendAnalysis;
use think\Request;

class TrendAnalysis extends Base
{
    public function index()
    {
        $result = MtrendAnalysis::getDatainfo();
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['trend']);
        return json($result);
    }

    public function import()
    {
        $model = new MtrendAnalysis();
        if($this->request->post('type') == 1){
            echo json_encode('ok');exit;
        }
        $model->import();
        return json(true);
    }
}