<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 11:12
 */
namespace app\index\controller\sourceAnalysis;

use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\SourceAnalysis;
use think\Request;

class KeyWord extends Base
{
    public function index()
    {
        $model = new SourceAnalysis();
        $result = $model->getKeyWords();
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['sourceAnalysisKeyWord']);
        return json($result);
    }
    public function export()
    {
        $model = new SourceAnalysis();
        if($this->request->post('type') == 1){
            echo json_encode('ok');exit;
        }
        $model->exportKeyWords();
        return json(true);
    }
}