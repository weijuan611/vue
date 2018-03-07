<?php
namespace app\script\test;

use app\common\Utility;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Db;

class Test extends Command
{
    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('test')->setDescription('test');
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $time = time();
        $this->output->writeln(date('Y-m-d H:i:s'));
//        file_put_contents(DOCROOT.'test.txt',date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);

        $a = $this->getCommissionByXSJL(101349);
        $b = $this->getCommissionByXSJL(101349-4114);
        $c = $a-$b;
        $this->output->writeln($a);
        $this->output->writeln($b);
        $this->output->writeln($c);

        $time = time()-$time;
        $this->output->writeln("all steps end!".round($time/60).':'.round($time/60));
    }

    private function getCommissionByXSJL($profit)
    {
        $commission = 0;
        $position_money = [
            'commission' => [
                '0' => '0.02',
                '40000' => '0.05',
                '50000' => '0.08',
                '70000' => '0.09',
                '100000' => '0.1'
            ]
        ];
        if ($profit > 40000) {
            $commission += 40000 * $position_money['commission']['0'];
            if ($profit > 50000) {
                $commission += 10000 * $position_money['commission']['40000'];
                if ($profit > 70000) {
                    $commission += 20000 * $position_money['commission']['50000'];
                    if ($profit > 100000) {
                        $commission += 30000 * $position_money['commission']['70000'];
                        $commission += ($profit - 100000) * $position_money['commission']['100000'];
                    } else {
                        $commission += ($profit - 70000) * $position_money['commission']['70000'];
                    }
                } else {
                    $commission += ($profit - 50000) * $position_money['commission']['50000'];
                }
            } else {
                $commission += ($profit - 40000) * $position_money['commission']['40000'];
            }
        } else {
            $commission += $profit * $position_money['commission']['0'];
        }
        return $commission;
    }

}