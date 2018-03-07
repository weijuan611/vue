<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/26
 * Time: 14:07
 */
namespace app\index\controller\visitAnalysis;


use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\SourceAreaCount;

class Area extends Base
{
    public function index(){

        $model = new SourceAreaCount();
        $result = $model->entrance();
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['visitAnalysisArea']);
        return json($result);
    }

    public function init()
    {
        $model = new SourceAreaCount();
        $areas = $model->getArea();
        return json(
            ["baseData"=>
                [
                    "provinceCity"=>$areas,
                ]
            ]
        );
    }

    public function changearea()
    {
        $model = new SourceAreaCount();
        $result['areaAnalysis_pieChart_data']= $model->areaAnalysisPieChartData($model->request());
        $result['baseData']['provinceCity']= $model->getArea();
        return json($result);
    }

    public function export()
    {
        $model = new SourceAreaCount();
        if($this->request->post('type') == 1){
            echo json_encode('ok');exit;
        }
        $model->exportAreaCount();
        return json(true);
    }
}