<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/11/1
 * Time: 11:28
 */

namespace app\index\model;

use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\Model;
use think\Session;

class UrlCount extends Model
{
    protected $table = 'url_count_pc';

    public function getBench(){
        $day7 = date('Y-m-d',time()-7*60*60*24);
        return $this->tableData($day7);
    }

    private function tableData($start_day,$end_day = ''){
        $result = [];
        if($end_day == ''){
            $end_day = date('Y-m-d');
        }
        $pc=Db::table('url_count_pc')->where('create_time','between',[$start_day,$end_day])
            ->field('create_time,uv uvPC,pv pvPC,TRUNCATE(num_jump/uv * 100,2) jumpPC,top10 top10PC,top50 top50PC')
            ->select();
        $m=Db::table('url_count_m')->where('create_time','between',[$start_day,$end_day])
            ->field('create_time,uv uvM,pv pvM,TRUNCATE(num_jump/uv * 100,2) jumpM,top10 top10M,top50 top50M')
            ->select();
        $order = Db::table('source_order_count')->where('create_time','between',[$start_day,$end_day])
            ->field('create_time,sum,order_sum_alone,order_rate')
            ->select();
        $data = array_merge($pc,$m,$order);
        $chart=[];
        if(!empty($data)){
            foreach ($data as $item){
                $result[$item['create_time']] = isset($result[$item['create_time']])?array_merge($result[$item['create_time']],$item):$item;
            }
            $week=["日","一","二","三","四","五","六"]; //先定义一个数组
            foreach ($result as $k=>$v){
                $result[$k]['create_time'] = substr($v['create_time'],0,10)
                .'('.$week[date('w',strtotime($v['create_time']))].')';
                if(isset($v['uvPC'])){
                    $result[$k]['uvTotal'] = $v['uvPC']+$v['uvM'];
                    $result[$k]['pvTotal'] = $v['pvPC']+$v['pvM'];
                    $result[$k]['jumpTotal'] = round(($v['jumpPC']+$v['jumpM'])/2,2);
                    $result[$k]['jumpPC'] = $v['jumpPC'].'%';
                    $result[$k]['jumpM'] = $v['jumpM'].'%';
                    $result[$k]['jumpTotal'] = $result[$k]['jumpTotal'].'%';
                    $result[$k]['top10Total'] = $v['top10PC']+$v['top10M'];
                    $result[$k]['top50Total'] = $v['top50PC']+$v['top50M'];
                }

                $result[$k]['order_rate'] = isset($v['order_rate'])? $v['order_rate'].'%':'';
            }
            $chart['data'] = [
                ['name'=>'PC端UV','type'=>'bar','data'=>[]],
                ['name'=>'M端UV','type'=>'bar','data'=>[]],
                ['name'=>'原始单','type'=>'line','yAxisIndex'=>1,'data'=>[]],
                ['name'=>'有效单','type'=>'line','yAxisIndex'=>1,'data'=>[]],
            ];
            foreach ($result as $item){
                $chart['xAxis'][]=substr($item['create_time'],0,10);
                if(isset($item['uvPC'])){
                    $chart['data'][0]['data'][]=$item['uvPC'];
                    $chart['data'][1]['data'][]=$item['uvM'];
                }
                if(isset($item['sum'])){
                    $chart['data'][2]['data'][]=$item['sum'];
                    $chart['data'][3]['data'][]=$item['order_sum_alone'];
                }
            }
        }
        return ['table'=>array_values($result),'chart'=>$chart] ;

    }


   /* public function getBench(){
        if(Session::get('type') != 'PC'){
            $this->setTable('url_count_m');
        }else{
            $this->setTable('url_count_pc');
        }
        $day = date('Y-m-d',strtotime('-1day'));
        $day1 = date('Y-m-d',(strtotime($day)-60*60*24));
        $day7 = date('Y-m-d',strtotime($day)-7*60*60*24);
        $data = self::where('create_time','=',$day)->find();
        $data1 = self::where('create_time','=',$day1)->find();
        $data7 = self::where('create_time','=',$day7)->find();
        return $this->compareAnalysisData($data,$data1,$data7);
    }

    public function compareAnalysisData($data,$data1,$data7)
    {
        $title =[
            'pv'=>'昨日浏览次数( PV )',
            'uv'=>'独立访客( UV )',
            'ip'=>'IP',
            'new_uv'=>'新独立访客',
            'num'=>'访问次数',
            'num_av'=>'访客平均访问频度',
            'time_av'=>'平均访问时长(秒)',
            'deep_av'=>'平均访问深度',
            'user_av'=>'人均浏览页数',
            'num_jump'=>'跳出率',
//            'num_back'=>'当日回头访客占比'
        ];
        $result=[];
        foreach ($title as $key=>$value){

            if($data[$key] == 0){
                $result[]= [
                    'title'=> $value,
                    'number'=> 0,
                    'compareDayTrend'=> 'up',
                    'compareWeekTrend'=> 'up',
                    'compareDay'=> '00.00%',
                    'compareWeek'=>'00.00%'
                ];
            }else{
                if($data['uv'] != 0){
                    if($key == 'num_jump'){
                        $data[$key] = abs(round($data['num_jump']/$data['uv'],4)*100);
                        $data1[$key] = abs(round($data1['num_jump']/$data['uv'],4)*100);
                        $data7[$key] = abs(round($data7['num_jump']/$data['uv'],4)*100);
                    }elseif ($key == 'num_back'){
                        $data[$key] = abs(round($data['num_back']/$data['uv'],4));
                        $data1[$key] = isset($data7['num_back'])?abs(round($data1['num_back']/$data['uv'],4)):0;
                        $data7[$key] =isset($data7['num_back'])?abs(round($data7['num_back']/$data['uv'],4)):0;
                    }
                }
                $compare1 = $data[$key] > $data1[$key]? 'up':'down';
                $compare7 = $data[$key] > $data7[$key]? 'up':'down';
                $num1 = abs(round(($data[$key] - $data1[$key])/$data[$key],4)*100);
                $num7 = abs(round(($data[$key] - $data7[$key])/$data[$key],4)*100);
                if($key == 'num_jump'){
                    $data[$key] .='%';
                }
                $result[]= [
                    'title'=> $value,
                    'number'=> $data[$key],
                    'compareDayTrend'=> $compare1,
                    'compareWeekTrend'=> $compare7,
                    'compareDay'=> $num1.'%',
                    'compareWeek'=> $num7.'%'
                ];
            }
        }
        return $result;
    }*/

}
