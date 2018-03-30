<?php
/**
 * 厚学网站地图
 */
namespace app\script\command;

use app\common\Utility;
use app\index\controller\Zlog;
use think\Db;
use think\Log;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

class Map extends Command
{

    protected  $time;
    protected  $time_format;
    protected  $max_num=50000;
    protected  $xml_head ;
    protected  $xml_foot ;
    protected  $xml_url ;
    protected  $remote_root = '/web/createxml/';
    protected  $local_root ;
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('map')->setDescription('Unincluded URL generated map');
        $this->addOption('time','t',Argument::OPTIONAL,'download date',date('Y-m-d',strtotime('-1day')));
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $this->xml_head = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
        $this->xml_foot = '</urlset>';
        $this->xml_url = '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>'.PHP_EOL;
        $this->local_root = ROOT_PATH.'map'.DS;

        $this->time = $input->getOption('time');
        $this->time_format = date('Y/m/d');
        $this->output->writeln("input time :".$input->getOption('time'));
        $this->output->writeln("map start!");
        Log::log('map start!');
        $time = time();
        $this->createMap($this->time.' 00:00:00',$this->time.' 23:59:59');
        $time = time()-$time;
        $this->output->writeln("all steps end!".(int)($time/60) .':'.$time%60);
        Log::log("aiZhang end!".(int)($time/60) .':'.$time%60);
    }

    private function createMap($start,$end){
        $old_map = Db::table('map_log')
            ->where('create_time','=',$start)
            ->where('url_num','<',$this->max_num)
            ->order('ml_id','desc')
            ->find();
        $url_num = $this->max_num;
        $file = $this->create_file();
        $remote_file='';
        if(!empty($old_map)){
            $file = $old_map['file'];
            $head = file_get_contents($file);
            $p = strpos($head,$this->xml_foot);
            $head =substr($head,0,$p);
            $url_num -= $old_map['url_num'];
            $remote_file = $old_map['remote_file'];
        }else{
            $head = $this->xml_head;
        }
        $start_id = 0;
        while (true){
            $data = Db::table('keywords_detail_url')
                ->where('update_time','between',[$start,$end])
                ->where('is_alive','=',0)
                ->order('kdu_id','asc')
                ->limit($start_id,$url_num)
                ->column('url');
            $xml = '';
            $count = count($data);
            if($count>0){
                foreach ($data as $url){
                    $xml.=sprintf($this->xml_url,$url,$this->time_format);
                }
                file_put_contents($file,$head.$xml.$this->xml_foot);
            $remote_file = $this->ftp_upload($file);
            }else{
                if($head != $this->xml_head){
                    file_put_contents($file,$head.$this->xml_foot);
                }else{
                    break;
                }
            }
            if(isset($old_map['url_num'])&&$old_map['url_num']>0){
                $total_num = $old_map['url_num']+$count;
                $old_map['url_num']=0;
            }else{
                $total_num = $count;
            }
            try{
                Db::table('map_log')->insert([
                    'create_time'=>date('Y-m-d'),
                    'url_num'=>$total_num,
                    'file'=>$file,
                    'remote_file'=>$remote_file
                ]);
            }catch (Exception $e){
                error($e);
            }

            if ($count< $url_num){
                break;
            }else{
                $start_id+=$url_num;
                $file = $this->create_file();
                $url_num = $this->max_num;
                $head = $this->xml_head;
            }
        }


    }

    private function ftp_upload($file){
        $config = SpiderImport::$FTP_SERVER[37];
        $ftp_con = ftp_connect($config['host'],21);
        if(!$ftp_con){
            Log::error("Map：ftp connect error [".var_export($config,1)."]");
            exit;
        }
        $is_login=ftp_login($ftp_con, $config['username'], $config['password']);
        if(!$is_login){
            Log::error("Map：ftp login error [".var_export($config,1)."]");
            exit;
        }
        $remote_file = $this->remote_root.str_replace(DS,'/',substr($file,strpos($file,'map')+4));
        $this->checkDirs($file,$ftp_con);
        ftp_put($ftp_con, $remote_file, $file, FTP_BINARY);
        return $remote_file;
    }

    private function checkDirs($file,$ftp_con){
        ftp_chdir($ftp_con,$this->remote_root);
        $path = str_replace($this->local_root,'',$file);//去除根目录
        $path_arr = explode(DS,$path); // 取目录数组
        array_pop($path_arr); // 去除文件名
        foreach($path_arr as $val) // 创建目录
        {
            if(@ftp_chdir($ftp_con,$val) == FALSE)
            {
                $tmp = ftp_mkdir($ftp_con,$val);
                if($tmp == FALSE)
                {
                    Log::error("mkdir failed ,please check permission!($val)");
                    exit;
                }
                ftp_chdir($ftp_con,$val);
            }
        }
    }


    private function create_file(){
        $file = $this->local_root.date('Y').DS.date('m').DS.date('d').DS;
        if(!file_exists($file)){
            mkdir($file,0777,true);
            chmod($file,0777);
        }
        $file.='map'.time().'.xml';
        return $file;
    }
}