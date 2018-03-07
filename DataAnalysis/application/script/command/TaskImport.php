<?php
namespace app\script\command;

use app\common\Constant;
use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

class TaskImport extends Command
{
    private static $BASE_PATH ;
    private static $FTP_SERVER = [
        [
            'host'=>'123.56.128.37',
            'username'=>'hxanalysis37',
            'password'=>'falzEWfN80B@',
//            'mode'=>true
        ],
        [
            'host'=>'123.57.227.94',
            'username'=>'hxanalysis94',
            'password'=>'mwfvwJMKXO9_8',
//            'mode'=>true
        ]
    ];
    private $time;
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('taskImport')->setDescription('Download the same day log file from ftpServer');
        $this->addOption('time','t',Argument::OPTIONAL,'download date',date('Y-m-d',strtotime('-1day')));
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        ini_set('memory_limit', '2048M');
        $this->time = strtotime($input->getOption('time'));
        self::$BASE_PATH = DOCROOT .'dailycsv'.DIRECTORY_SEPARATOR;
        $this->output->writeln("input time :".$input->getOption('time'));
        $this->output->writeln("Task start!");
        Log::log('taskImport start!');
        if($this->downloadFiles()){
            $this->importSQL();
        }
        $this->output->writeln("Task end!");
        Log::log('taskImport end!');
    }


    // +----------------------------------------------------------------------
    // | 将统计文件中从服务器下载
    // +----------------------------------------------------------------------

    /**
     * @判断文件存放目录是否存放，如果不存在就创建
     * @param $time
     * @return string
     */
    public function getLocalPath()
    {
        if(!is_writable(self::$BASE_PATH)){
            exit('php无写入权限['.self::$BASE_PATH.']');
        }

        $dir = self::$BASE_PATH. date('Y',$this->time).DIRECTORY_SEPARATOR.date('m',$this->time)
            .DIRECTORY_SEPARATOR.date('d',$this->time).DIRECTORY_SEPARATOR;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
            chmod($dir,0777);
        }
        return $dir;

    }


    /**
     * @从指定服务器下载文件
     * @param $file_path
     * @return string
     */
    public function downloadFiles()
    {
        $local_path=$this->getLocalPath();
        $remote_path ='/web/dailycsv/'.date('Y',$this->time).'/'.date('m',$this->time)
            .'/'.date('d',$this->time).'/';
        $this->output->writeln("ftp: target dir readied!");
        $ftp_time = time();
        $return=false;
        foreach (self::$FTP_SERVER as $config){
            $config['port']=isset($config['port'])&&$config['port']!=''?$config['port']:'21';
            $ftp_con = ftp_connect($config['host'],$config['port']);
            if(!$ftp_con){
                Log::error("TaskImport：ftp connect error [".var_export($config,1)."]");
                continue;
            }
            $is_login = ftp_login($ftp_con, $config['username'], $config['password']);
            if(!$is_login){
                Log::error("TaskImport：ftp login error [".var_export($config,1)."]");
                continue;
            }
            if(isset($config['mode'])&&$config['mode']==true)
            {
                ftp_pasv($ftp_con,true);
            }
            $raw_list = ftp_nlist($ftp_con,$remote_path);
            if(!$raw_list){
                $this->output->writeln('获取ftp目录列表失败');
                Log::error("获取ftp目录列表失败");
            }
            foreach ($raw_list as $raw){
                if($raw !='.'&&$raw!='..'){
                    $this->output->writeln('get start file:'.$raw);
                    if(!ftp_get($ftp_con,$local_path.$raw,$remote_path.$raw,FTP_BINARY)){
                        Log::error("TaskImport：ftp get error [".var_export($config,1)."] ".$raw);
                    }else{
                        $this->output->writeln('get end file:'.$raw);
                        $return = true;
                    }
                }
            }
            ftp_close($ftp_con);
        }
        $ftp_time=time() - $ftp_time;
        $this->output->writeln('ftp:close!'.(int)$ftp_time/60 .':'.$ftp_time%60);
        Log::log('TaskImport:ftp download successful!'.(int)$ftp_time/60 .':'.$ftp_time%60);
        return $return;
    }


    // +----------------------------------------------------------------------
    // | 将CSV文件中的数据导入到数据库中
    // +----------------------------------------------------------------------

    /**
     * @将文件内容存入数据库
     */
    public function importSQL(){  //倒入数据库
        $path = self::$BASE_PATH. date('Y',$this->time).DIRECTORY_SEPARATOR.date('m',$this->time)
            .DIRECTORY_SEPARATOR.date('d',$this->time).DIRECTORY_SEPARATOR;
        $stream = opendir($path);
        $file_arr = [];
        while($file =readdir($stream)){
            if($file !="." && $file !=".."&&!strpos($file,'.old')){
                $file_arr[] = $path.$file;
            }
        }
        if(empty($file_arr)){
            $this->output->writeln("Without file need import!!");
            return false;
        }
        foreach($file_arr as $v){
            $result = $this->ImportMysql($v);
            Log::error('TaskImport:handle file ['.$v.']!'.var_export($result,1));
        }
        return true;
    }

    /**
     * @从文件插入数据
     * @param $path
     * @return array|bool
     */
    public function ImportMysql($path){
        $this->output->writeln('CSV:handle file start ['.$path.']');
        $handle = fopen($path, 'r');
        $out = array();
        while ($data = fgetcsv($handle)) {
            $out[]=$data;
            if(count($out) == 1000){
                $this->handleData($out);
                $out=[];
            }
        }
        if(count($out) > 0){
            $this->output->writeln('% ['.count($out).']');
            $this->handleData($out);
        }
        fclose($handle);
        $new_filename = str_replace(".csv",".old.csv",$path);
        rename($path,$new_filename);
        $this->output->writeln('CSV:handle file end ['.$path.']');
        Log::log('CSV:handle file end ['.$path.']');
        return true;
    }

    public function getSearchEngines($userRefer)
    {
        if(stristr($userRefer,"baidu.com")){
            return 3;//"百度";
        }elseif(stristr($userRefer,"so.com")){
            return 2;//"360";
        }elseif(stristr($userRefer,"sogou.com")){
            return 4;//"搜狗";
        }elseif(stristr($userRefer,"yahoo.com")){
            return 9;//"雅虎";
        }elseif(stristr($userRefer,".bing.com")){
            return 6;//"必应";
        }elseif(stristr($userRefer,".youdao.com")){
            return 10;//"有道";
        }elseif(stristr($userRefer,".sina.com")){
            return 5;//"新浪";
        }elseif(stristr($userRefer,".chinaso.com")){
            return 11;//"中国搜索";
        }elseif(stristr($userRefer,".google.com")){
            return 7;//"谷歌";
        }elseif(stristr($userRefer,".wikipedia.org")){
            return 12;//"维基百科";
        }elseif(stristr($userRefer,".sm.cn")){
            return 8;//"神马";
        }elseif(stristr($userRefer,".houxue.com")){
            return 1;//"站内搜索";
        }elseif(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $userRefer)>0){
            return 13;//手动输入或者书签
        }else{
            return 0;
        }
    }

    /**
     * 插入数据库
     * @param $result
     * @return bool
     */
    public function handleData($result)
    {
        $len_result = count($result);
        $rst = array();
        $session_time=[];
            for ($i = 0; $i < $len_result; $i++) { //循环获取各字段值
                if (isset($result[$i])&&$result[$i][0]!='访问时间') {
                    try {
                        $rst[$i]['log_time'] = $result[$i][0];
                        $ips = explode(',', $result[$i][1]);
                        $ip = isset($ips[0]) ? $ips[0] : '';
                        $rst[$i]['ip_address'] = $ip;
                        $rst[$i]['url_from'] = $result[$i][2];
                        $rst[$i]['domain_name'] = $result[$i][3];
                        $rst[$i]['session_id'] = $result[$i][4];
                        $rst[$i]['long_time'] = abs($result[$i][5] - strtotime($result[$i][0]) + 1);
                        $rst[$i]['cookie'] = $result[$i][6];
                        $rst[$i]['web_from'] = $result[$i][7];
                        $rst[$i]['domain_from'] = $result[$i][8];
                        $rst[$i]['keyworks'] = $result[$i][9] == '没有检索到搜索引擎' || $result[$i][9] == '' ? addslashes($this->getKeyword($result[$i][9])) : addslashes($result[$i][9]);
                        $rst[$i]['user_agent'] = $result[$i][10];
                        $rst[$i]['browser_type'] = (int)array_search($result[$i][11], Constant::$browser_type);
                        $rst[$i]['search_engines'] = $this->getSearchEngines($result[$i][2]);
                        $rst[$i]['display_size'] = $result[$i][15];
                        $arr_domain = explode('.', $result[$i][3]);
                        $d_a_i = 0;
                        if (isset($arr_domain[1]) && $arr_domain[1] == 'houxue') {
                            $arr_domain[0] = trim($arr_domain[0]);
                            if (in_array($arr_domain[0], ['www', 'm', 'http://www', 'https://www', 'http://m', 'https://m'])) {
                                $s = strpos($result[$i][7], 'com/');
                                $e = strpos($result[$i][7], '/', $s + 4);
                                $d_a_i = DB::table('sys_area')->where('Level', '<>', 0)
                                    ->where('Domain', '=', substr($result[$i][7], $s + 4, $e - $s - 4))->value('Id', 0);
                            } else {
                                $d_a_i = DB::table('sys_area')->where('Level', '<>', 0)
                                    ->where('Domain', '=', $arr_domain[0])->value('Id', 0);
                            }
                            if ($d_a_i == 0) {
//                            Log::log('3-'.var_export($arr_domain,1));
                            }
                        }
                        $rst[$i]['domain_area_id'] = $d_a_i;

                        if ($ip != '') {
                            $area = explode(' ', Utility::GetArea($ip));
                            if ($result[$i][16] == '南京') {
                                $tmp = Utility::getAreaId($area[0]);
                                $tmp = '%' . $tmp . '%';
                                $rst[$i]['area_id'] = DB::table('sys_area')->where('AreaName', 'like', $tmp)->value('Id', 0);
                            } else {
                                $tmp = '%' . $result[$i][16] . '%';
                                $rst[$i]['area_id'] = DB::table('sys_area')->where('AreaName', 'like', $tmp)->value('Id', 0);
                                if ($rst[$i]['area_id'] == 0) {
                                    $tmp = Utility::getAreaId($area[0]);
                                    $tmp = '%' . $tmp . '%';
                                    $rst[$i]['area_id'] = DB::table('sys_area')->where('AreaName', 'like', $tmp)->value('Id', 0);
                                }
                            }
                            if (isset($area[1])) {
                                $rst[$i]['network_access'] = (int)array_search(substr($area[1], 0, 6), Constant::$network_access);
                            } else {
                                $rst[$i]['network_access'] = 0;
                            }
                        } else {
                            $rst[$i]['area_id'] = 0;
                            $rst[$i]['network_access'] = 0;
                        }
                        $rst[$i]['dstype'] = strpos($result[$i][7], "m.houxue.com") !== FALSE ? 2 : 1;
                        $rst[$i]['source_from'] = (int)array_search($result[$i][14], Constant::$source_from);
                        $rst[$i]['operating_system'] = (int)array_search(Utility::get_user_os($result[$i][10]), Constant::$operating_system);
                        //统计session超时
                        if (isset($session_time[$result[$i][4]])) {
                            if (strtotime($result[$i][0]) - strtotime($session_time[$result[$i][4]]) > 30 * 60) {
                                $rst[$i]['session_live'] = 1;
                            } else {
                                $rst[$i]['session_live'] = 0;
                            }
                        } else {
                            $num = Db::table('url_statis_log' . Utility::getLogSuffix(date('Y-m-01', $this->time), false))->force('log_time')->where('log_time', '>', date('Y-m-d H:i:s', strtotime($result[$i][0]) - 30 * 60))
                                ->where('dstype', '=', $rst[$i]['dstype'])->where('session_id', '=', $result[$i][4])->count();
                            if ($num > 0) {
                                $rst[$i]['session_live'] = 0;
                            } else {
                                $rst[$i]['session_live'] = 1;
                            }
                        }
                        $session_time[$result[$i][4]] = $result[$i][0];
                    } catch (Exception $e) {
                        Log::error($e->getMessage() .PHP_EOL. $e->getTraceAsString());
                    }
                }
            }
            if(count($rst) >0){
                try {
                    Db::startTrans();
                    $sql = $this->getInsertAllSql("url_statis_log".Utility::getLogSuffix(date('Y-m-01',$this->time),false),$rst);
                    $data = Db::execute($sql);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    Log::log($e->getMessage().PHP_EOL.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString());
                    return false;
                }
            }
            $this->output->writeln('handle sql:'.$len_result);
            return true;
    }

    public function getInsertAllSql($tableName, $data)
    {
        $data = array_values($data);
        $fields = implode(',', array_keys($data[0]));

        $tmp = array();
        foreach($data as $value)
        {
            $tmp[] = "'" . implode("','", $value) . "'";
        }

        $values = "(" . implode("),(", $tmp) . ")";

        $sql = "INSERT INTO {$tableName} ({$fields}) VALUES {$values}";

        return $sql;
    }

    /**
     * @获取关键字
     * @param $url
     * @return string
     */
    public function getKeyword($url)
    {
        $urls = parse_url($url);
        // 检查关键字参数是否存在
        if (empty($urls["query"])){
            return '站内访问';
        }
        $params = array();
        parse_str($urls['query'], $params);
        if (isset($params['keyword'])) {
            $keywords = $params['keyword'];
        }elseif(isset($params['wd'])){
            $keywords = $params['wd'];
        }elseif(isset($params['word'])){
            $keywords = $params['word'];
        }elseif(isset($params['query'])){
            $keywords = $params['query'];
        }elseif(isset($params['q'])){
            $keywords = $params['q'];
        }else{
            $keywords ='';
        }
        // 检查编码
        $encoding = mb_detect_encoding($keywords, 'utf-8,gbk');
        if ($encoding != 'utf-8') {
            $keywords = iconv($encoding, 'utf-8', $keywords);
        }
        return $keywords;
    }

}