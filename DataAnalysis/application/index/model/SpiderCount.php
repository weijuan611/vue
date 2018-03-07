<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/29
 * Time: 9:51
 */
namespace app\index\model;

use think\Log;

class SpiderCount extends Base
{
    public $date_time =[];
    public $total = 0;
    public $pcPicData=[];
    public $mPicData=[];
    public $spiderBarChartTitle=[];

    public function getTableData($dstype = 1){
        $query = self::table('spider_count')->where('dstype','=',$dstype)
            ->where('create_time','between',$this->date_time)->group('create_time')
            ->field('date_format(create_time, \'%Y-%m-%d\') AS date,SUM(num)AS total')
            ->field('SUM(CASE WHEN se_id = 2 THEN num ELSE 0 END) AS zz360')
            ->field('SUM(CASE WHEN se_id = 3 THEN num ELSE 0 END) AS baidu')
            ->field('SUM(CASE WHEN se_id = 4 THEN num ELSE 0 END) AS sougou')
            ->field('SUM(CASE WHEN se_id = 8 THEN num ELSE 0 END) AS shenma')
            ->field('SUM(CASE WHEN se_id = 8 or se_id=4 or se_id=3 or se_id=2 THEN 0 ELSE num END) AS other');
        $data = $this->autoPaginate($query)->toArray();
        $this->total = (int)$data['total'];
        $sum=['date'=>'总计','total'=>0,'zz360'=>0,'baidu'=>0,'sougou'=>0,'shenma'=>0,'other'=>0];
        foreach ($data['data'] as $v){
            $sum['total']+=$v['total'];
            $sum['zz360']+=$v['zz360'];
            $sum['baidu']+=$v['baidu'];
            $sum['sougou']+=$v['sougou'];
            $sum['shenma']+=$v['shenma'];
            $sum['other']+=$v['other'];
        }
        array_unshift($data['data'],$sum);
        return $data['data'];
    }

    public function getSpiderBarChartData($dstype = 1){
        $query = self::table('spider_count')->where('dstype','=',$dstype)
            ->where('create_time','between',$this->date_time)->group('create_time')
            ->field('create_time AS date,SUM(num)AS total')
            ->field('SUM(CASE WHEN se_id = 2 THEN num ELSE 0 END) AS zz360')
            ->field('SUM(CASE WHEN se_id = 3 THEN num ELSE 0 END) AS baidu')
            ->field('SUM(CASE WHEN se_id = 4 THEN num ELSE 0 END) AS sougou')
            ->field('SUM(CASE WHEN se_id = 8 THEN num ELSE 0 END) AS shenma')
            ->field('SUM(CASE WHEN se_id = 8 or se_id=4 or se_id=3 or se_id=2 THEN 0 ELSE num END) AS other');
        $data = $this->autoPaginate($query)->toArray();
        $this->total = (int)$data['total'];
        $sum=['total'=>[],'baidu'=>[],'zz360'=>[],'sougou'=>[],'shenma'=>[],'other'=>[]];
        foreach ($data['data'] as $v){
            $sum['total'][]=(int)$v['total'];
            $sum['baidu'][]=(int)$v['baidu'];
            $sum['zz360'][]=(int)$v['zz360'];
            $sum['sougou'][]=(int)$v['sougou'];
            $sum['shenma'][]=(int)$v['shenma'];
            $sum['other'][]=(int)$v['other'];
            $this->spiderBarChartTitle[]=substr($v['date'],0,10);
        }
        return $sum;
    }

    public function getSpiderDataDetail(){
        $pc = self::table('spider_count')->where('create_time','between',$this->date_time)->where('dstype','=',1)
            ->group('se_id')->field('SUM(num)AS number,se_id')->select();
        $m = self::table('spider_count')->where('create_time','between',$this->date_time)->where('dstype','=',2)
            ->group('se_id')->field('SUM(num)AS number,se_id')->select();
        $result =[
            'PC'=>$this->countSE($pc),
            'M'=>$this->countSE($m)
        ];
        $this->setSpiderPieChartData($result);
        return $result;
    }

    private function setSpiderPieChartData($data){
        foreach ($data['PC'] as $k=>$v){
            if($k>0&&$data['PC'][0]['number']>0){
               $rate = round($v['number']/$data['PC'][0]['number'],2) * 100;
               $this->pcPicData[]=['name'=>$v['title'].':'.$v['number'].'('.$rate.'%)','value'=>$v['number']];
            }
        }
        foreach ($data['M'] as $k=>$v){
            if($k>0&&$data['M'][0]['number']>0){
                $rate = round($v['number']/$data['M'][0]['number'],2) * 100;
                $this->mPicData[]=['name'=>$v['title'].':'.$v['number'].'('.$rate.'%)','value'=>$v['number']];
            }
        }
    }

    private function countSE($data){
        $result=[
            ['title'=>'蜘蛛抓取总量','number'=>0],
            ['title'=>'百度蜘蛛','number'=>0],
            ['title'=>'360蜘蛛','number'=>0],
            ['title'=>'搜狗蜘蛛','number'=>0],
            ['title'=>'神马蜘蛛','number'=>0],
            ['title'=>'其它','number'=>0],
        ];

        if (!empty($data)){
            foreach ($data as $item){
                $result[0]['number']+=$item['number'];
                if($item['se_id'] == 3){
                    $result[1]['number']+=$item['number'];
                }elseif ($item['se_id'] == 2){
                    $result[2]['number']+=$item['number'];
                }elseif ($item['se_id'] == 4){
                    $result[3]['number']+=$item['number'];
                }elseif ($item['se_id'] == 8){
                    $result[4]['number']+=$item['number'];
                }else{
                    $result[5]['number']+=$item['number'];
                }
            }
        }
        return $result;
    }
}