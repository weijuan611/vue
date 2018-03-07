<?php

namespace app\script\command;

use app\common\Utility;
use app\index\controller\Zlog;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Log;
use think\Db;

class ExportCheck extends Command
{
    protected $time;

    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('exportCheck')->setDescription('export everyone add keywords num');
        $this->addOption('time', 't', Argument::OPTIONAL, 'export date', date('Y-m-d', strtotime('-1day')));
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        ini_set('memory_limit', '1024M');
        $this->time = $input->getOption('time');
        $this->output->writeln('time:' . $this->time);
        $this->output->writeln("Task start!");
        $this->handle();
        $this->output->writeln("Task end!");
    }


    private function handle()
    {
        $path = $this->getPath() . 'keywordCheck_' . $this->time.'.csv';
        $csv_header = ['姓名', '部门', '任务数量', '添加数量',];
        $csv_body = [];
        $users = Db::query('SELECT a.user_id,a.user_name,b.dp_name,max(d.KeywordNum) as maxKeyNum from users as a
LEFT JOIN departments as b on a.dp_id=b.dp_id
LEFT JOIN org_userroles as c on a.user_id=c.UserID
LEFT JOIN org_roles as d on c.RoleID=d.ID
GROUP BY user_id');
        foreach ($users as $user){
            if ($user['maxKeyNum']>0){
                $tmp=[];
                $tmp[] = $user['user_name'];
                $tmp[] = $user['dp_name'];
                $tmp[] = $user['maxKeyNum'];
                $num = Db::query("SELECT COUNT(*) from keywords
where user_id=? AND date_format(add_time,'%Y-%m-%d')=?",[$user['user_id'],$this->time]);
                $tmp[] = $num[0]['COUNT(*)'];
                $csv_body[] = $tmp;
            }
        }
        $fp = fopen($path,'w');
        $header = implode(',', $csv_header) . PHP_EOL;
        $content = '';
        foreach ($csv_body as $k => $v) {
            $content .= implode(',', $v) . PHP_EOL;
        }
        $csv = $header.$content;
        fwrite($fp, $csv);
        fclose($fp);
    }

    private function getPath()
    {
        $path = DOCROOT . 'public' . DIRECTORY_SEPARATOR.'outside'.DIRECTORY_SEPARATOR;
        $this->time = strtotime($this->time);
        $path = $path . date('Y', $this->time) . DIRECTORY_SEPARATOR . date('m', $this->time)
            . DIRECTORY_SEPARATOR . date('d', $this->time) . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 777, true);
        }
        $this->time = date('Y-m-d',$this->time);
        return $path;
    }

}