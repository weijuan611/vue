<?php
namespace app\script\command;

use app\common\Constant;
use app\common\Utility;
use think\Db;
use think\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

class SpiderImport extends Command
{
    protected $log_suffix='';
    public static $BASE_PATH ;
    public static $FTP_SERVER = [
        37=>[
            'host'=>'123.56.128.37',
            'username'=>'hxanalysis37',
            'password'=>'falzEWfN80B@',
//            'mode'=>true
        ],
        94=>
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
        $this->setName('spiderImport')->setDescription('import spider log');
        $this->addOption('time','t',Argument::OPTIONAL,'download date',date('Y-m-d',strtotime('-1day')));
        $this->addOption('step','s',Argument::OPTIONAL,'step:1.download,2.import',1);
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
        $this->log_suffix = Utility::getLogSuffix($input->getOption('time'),false);
        self::$BASE_PATH = ROOT_PATH .'spider'.DS;
        $this->output->writeln("input time :".$input->getOption('time'));
        $this->output->writeln("Task start!".$input->getOption('step'));
        if($input->getOption('step') == 1){
            Log::log('spiderImport start!');
            $this->downloadFiles();
            Log::log('spiderImport download over!');
        }elseif($input->getOption('step') == 2){
            $this->importSQL();
            Log::log('spiderImport import over!');
            $this->countSpider();
            Log::log('spiderImport end!');
        }
        $this->output->writeln("Task end!");
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
        $remote_path ='/web/spiderlogs/';
        $this->output->writeln("ftp: target dir readied!");
        $ftp_time = time();
        $return=false;
        foreach (self::$FTP_SERVER as $id=>$config){
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
//            $raw_list = ftp_nlist($ftp_con,$remote_path);
//            if(!$raw_list){
//                $this->output->writeln('获取ftp目录列表失败');
//                Log::error("获取ftp目录列表失败");
//            }
            $raw_list = ['pc-'.date('Y-m-d',$this->time).'-'.$id.'.php','m-'.date('Y-m-d',$this->time).'-'.$id.'.php'];
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
            if(!$result){
                Log::error('TaskImport:handle file error ['.$v.']!');
            }
        }
        return true;
    }

    /**
     * @从文件插入数据
     * @param $path
     * @return array|bool
     */
    public function ImportMysql($path){
        $this->output->writeln('Log:handle file start ['.$path.']');
        if(substr($path,strrpos($path,DS)+1,1) == 'm'){
            $dstype = 2;
        }else{
            $dstype = 1;
        }
        try{
            $handle = fopen($path, 'r');
            $out = array();
            while ($data = fgets($handle)) {
                $date_start = strpos($data,'[')+1;
                $date_end = strpos($data,']');
                $arr['log_time'] = substr($data,$date_start,$date_end - $date_start);
                $ip_end=strpos($data,'^^');
                $arr['source_ip'] = substr($data,$date_end+2,$ip_end - $date_end -2);
                $url_start = strpos($data,'^^',$ip_end+1)+2;
                $url_end = strpos($data,'^^',$url_start);
                $arr['target_url'] =addslashes( substr($data,$url_start,$url_end - $url_start));
                $user_agent = substr($data,$url_end+2);
                $arr['dstype'] = $dstype;
                $arr['spider_type'] = $this->getSpiderID($user_agent,$arr['source_ip']);
                $arr['user_agent'] =addslashes(substr($user_agent,0,255));
                $out[]=$arr;
                if(count($out) == 100){
                    DB::table('spider_log'.$this->log_suffix)->insertAll($out);
                    $this->output->writeln('log insert successful!'.count($out));
                    $out=[];
                }
            }
            if(count($out) > 0){
                DB::table('spider_log'.$this->log_suffix)->insertAll($out);
                $this->output->writeln('log insert successful!'.count($out));
            }
            fclose($handle);
            $new_filename = str_replace(".php",".old.php",$path);
            rename($path,$new_filename);
            $this->output->writeln('log:handle file end ['.$path.']');
            return true;
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            return false;
        }
    }

    public function getSpiderID($user_agent,$ip)
    {
        if(stristr($user_agent,"Baiduspider")){
            return 3;//"百度";
        }elseif(stristr($user_agent,"360Spider")){
            return 2;//"360";
        }elseif(stristr($user_agent,"Sogou web spider")){
            return 4;//"搜狗";
        }elseif(stristr($user_agent,"Yahoo! Slurp")){
            return 9;//"雅虎";
        }elseif(stristr($user_agent,"bingbot")){
            return 6;//"必应";
        }elseif(stristr($user_agent,"YoudaoBot")){
            return 10;//"有道";
        }elseif(stristr($user_agent,"Googlebot")){
            return 7;//"谷歌";
        }elseif(stristr($user_agent,"YisouSpider")){
            return 8;//"神马";
        }else{
            //检查ip段
            if($this->isSpider360IP($ip)){
                return 2;//360
            }
            return 0;
        }
    }

    private function isSpider360IP($ip){
        $ip_arr = explode('.',$ip);
        if(count($ip_arr) == 4){
            $ip_s = $ip_arr[0].'.'.$ip_arr[1].'.'.$ip_arr[2];
            if($ip_arr[0].'.'.$ip_arr[1] =='101.199'){
                return true;
            }
            switch ($ip_s){
                case '101.226.166':
                    if($ip_arr[3]>=195&&$ip_arr[3]<=254){
                        return true;
                    }
                    break;
                case '101.226.167':
                    if($ip_arr[3]>=195&&$ip_arr[3]<=254){
                        return true;
                    }
                    break;
                case '101.226.168':
                    if($ip_arr[3]>=195&&$ip_arr[3]<=254){
                        return true;
                    }
                    break;
                case '101.226.169':
                    if($ip_arr[3]>=195&&$ip_arr[3]<=230){
                        return true;
                    }
                    break;
                case '180.153.236':
                    if($ip_arr[3]>=11&&$ip_arr[3]<=26){
                        return true;
                    }elseif($ip_arr[3]>=35&&$ip_arr[3]<=74){
                        return true;
                    }elseif($ip_arr[3]>=101&&$ip_arr[3]<=196){
                        return true;
                    }
                    break;
                case '182.118.20':
                    if($ip_arr[3]>=201&&$ip_arr[3]<=254){
                        return true;
                    }
                    break;
                case '182.118.21':
                    if($ip_arr[3]>=201&&$ip_arr[3]<=254){
                        return true;
                    }
                    break;
                case '182.118.22':
                    if($ip_arr[3]>=141&&$ip_arr[3]<=149){
                        return true;
                    }elseif($ip_arr[3]>=211&&$ip_arr[3]<=250){
                        return true;
                    }
                    break;
                case '182.118.25':
                    if($ip_arr[3]>=131&&$ip_arr[3]<=245){
                        return true;
                    }
                    break;
                case '182.118.28':
                    return true;
                case '61.55.185':
                    return true;
                case '220.181.126':
                    return true;
                case '182.118.26':
                    if($ip_arr[3]>=110&&$ip_arr[3]<=239){
                        return true;
                    }
                    break;
                case '42.236.99':
                    if($ip_arr[3]>=2&&$ip_arr[3]<=126){
                        return true;
                    }
                    break;
                case '42.236.12':
                    if($ip_arr[3]>=130&&$ip_arr[3]<=190){
                        return true;
                    }
                    break;
                case '42.236.46':
                    if($ip_arr[3]>=66&&$ip_arr[3]<=124){
                        return true;
                    }
                    break;
                case '42.236.54':
                    if($ip_arr[3]>=2&&$ip_arr[3]<=62){
                        return true;
                    }
                    break;
                case '42.236.55':
                    if($ip_arr[3]>=2&&$ip_arr[3]<=60){
                        return true;
                    }
                    break;
                case '42.236.101':
                    if($ip_arr[3]>=194&&$ip_arr[3]<=252){
                        return true;
                    }
                    break;
                case '42.236.102':
                    if($ip_arr[3]>=2&&$ip_arr[3]<=42){
                        return true;
                    }
                    break;
                case '42.236.103':
                    if($ip_arr[3]>=66&&$ip_arr[3]<=125){
                        return true;
                    }
                    break;
                case '180.153.232':
                    if($ip_arr[3]>=170&&$ip_arr[3]<=177){
                        return true;
                    }
                    break;
                case '180.153.234':
                    if($ip_arr[3]>=145&&$ip_arr[3]<=152){
                        return true;
                    }
                    break;
            }
        }
        return false;
    }


    public function countSpider(){
        $start_time = date('Y-m-d 00:00:00',$this->time);
        $end_time = date('Y-m-d 23:59:59',$this->time);
        $data1=DB::table('spider_log'.$this->log_suffix)->where('log_time','between',[$start_time,$end_time])->where('dstype','=',2)
        ->group('spider_type')->field('spider_type as se_id,count(spider_type) as num,dstype,DATE_FORMAT(log_time,\'%Y-%m-%d 00:00:00\') as create_time')->select();
        $data2=DB::table('spider_log'.$this->log_suffix)->where('log_time','between',[$start_time,$end_time])->where('dstype','=',1)
            ->group('spider_type')->field('spider_type as se_id,count(spider_type) as num,dstype,DATE_FORMAT(log_time,\'%Y-%m-%d 00:00:00\') as create_time')->select();
        if($data1 !=null && $data2 !=null){
            try{
                DB::table('spider_count')->insertAll(array_values($data1));
                DB::table('spider_count')->insertAll(array_values($data2));
            }catch (Exception $e){
                Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            }
        }else{
            Log::error('countSpider data have null！');
        }
    }
}