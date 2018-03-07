<?php
namespace app\index\controller\sourceAnalysis;

use app\index\controller\Base;
use app\index\model\SourceAnalysis;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26
 * Time: 9:54
 */
class OriginAnalysisSort extends Base
{
    public function index()
    {
        $result = (new SourceAnalysis())->getOriginAnalysisSortList();
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['sourceAnalysisOriginSort']);
        return json($result);
    }
    public function export()
    {
        if($this->request->post('type') == 1){
            echo json_encode('ok');exit;
        }
        (new SourceAnalysis())->exportOriginAnalysisSort();
        return json(true);
    }
}