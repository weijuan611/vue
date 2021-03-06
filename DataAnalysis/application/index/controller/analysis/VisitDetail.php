<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 11:43
 * Info: 访问明细
 */

namespace app\index\controller\analysis;


use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\Analysis;

class VisitDetail extends Base
{
    public function init()
    {

        $areas = Analysis::getArea();
        return json(
            ["baseData" =>
                [
                    "areaValue" => $areas,
                ]
            ]
        );
    }

    public function index()
    {
        $view = new Analysis();
        $data = $view->getList();
        return json([
                "tableData" => array_values($data["result"]),
                "total" => (int)$data["total"],
                "show" => $data["show"],
                'buttonControl'=>buttonDisable($this->permission,$this->route['visit_detail'])
            ]
        );
    }

    public function import()
    {
        $model = new Analysis();
        if ($this->request->post('type') == 1) {
            echo json_encode('ok');
            exit;
        }
        $model->importData();
        return json(true);
    }
}
