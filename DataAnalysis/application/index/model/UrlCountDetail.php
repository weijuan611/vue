<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/12/21
 * Time: 15:36
 */
namespace app\index\model;

use think\Log;
use think\Model;
use think\Session;

class UrlCountDetail extends Model{
    protected $table = 'url_count_pc_detail';

    public function getBench(){
        if(Session::get('type') != 'PC'){
            $this->setTable('url_count_m_detail');
        }else{
            $this->setTable('url_count_pc_detail');
        }
        $data['showPv']='PV';
        $day1 = date("Y-m-d",strtotime('-1day'));
        $day2 = date("Y-m-d",strtotime('-2day'));
        $data['todayDataPV']=$this->getPV($day1);
        $data['yesterdayDataPV']=$this->getPV($day2);
        $data['todayDataUV']=$this->getUV($day1);
        $data['yesterdayDataUV']=$this->getUV($day2);
        return $data;
    }

    public function getPV($day){
        $start_time = $day.' 00:00:00';
        $end_time = $day.' 23:59:59';
        $data=self::where('create_time','between',[$start_time,$end_time])->order('hour asc')->column('pv');
        return $data != null?$data:[];
    }

    public function getUV($day){
        $start_time = $day.' 00:00:00';
        $end_time = $day.' 23:59:59';
        $data=self::where('create_time','between',[$start_time,$end_time])->order('hour asc')->column('uv');
        return $data != null?$data:[];
    }
}