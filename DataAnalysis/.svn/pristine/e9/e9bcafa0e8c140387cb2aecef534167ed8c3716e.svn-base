<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/29
 * Time: 9:46
 */
namespace app\index\controller\analysis;

use app\index\controller\Base;
use app\index\model\SpiderCount;

class SpiderStatistics extends Base
{
    public function index(){
        $model = new SpiderCount();
        $post = $this->request->post();
        $yesterday = date('Y-m-d',strtotime('-1 day'));
        $model->date_time = isset($post['search']['dateTime'])?$post['search']['dateTime']:[$yesterday,$yesterday];
        $pieChartMedia = isset($post['search']['pieChartMedia'])&&$post['search']['pieChartMedia'] == 'm'?2:1;
        $tableMedia = isset($post['search']['tableMedia'])&&$post['search']['tableMedia'] == 'm'?2:1;
        $result = [
            'spiderDataDetail'=>$model->getSpiderDataDetail(),
            'spiderPcPieChartData'=>$model->pcPicData,
            'spiderMPieChartData'=>$model->mPicData,
            'spiderBarChartData'=>$model->getSpiderBarChartData($pieChartMedia),
            'spiderBarChartTitle'=>$model->spiderBarChartTitle,
            'tableData'=>$model->getTableData($tableMedia),
            'total'=>$model->total
        ];
        return json($result);
    }
}