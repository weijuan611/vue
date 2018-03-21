<?php
/**
 * 任务数据处理
 * Created by PhpStorm.
 * User: wdy
 * Date: 2018/3/14
 * Time: 08:50
 */

namespace app\script\model;

use think\Db;
use think\Exception;
use think\exception\DbException;
use think\Log;

class TaskGenerator
{
    protected $yesterday;
    protected $today;
    protected $now;
    protected $message;

    public function __construct()
    {
        $this->yesterday = date("Y-m-d 00:00:00", strtotime("-1 day"));
        $this->today = date("Y-m-d 00:00:00", strtotime("0 day"));
        $this->now = date("Y-m-d H:i:s", time());
    }


    /**
     * 1.任务生成:
     * 将昨日已发布、已取消的任务导入今日任务中。
     */
    public function gen()
    {
        // 生成昨天的任务
        try {
            // 昨天已发布的任务
            $tasks_published = Db::table('task')
                ->where('task_time', 'between', [$this->yesterday, $this->today])
                ->where('status', '=', '2')->select();
            // 昨天已取消的任务
            $tasks_canceled = Db::table('task')
                ->where('task_time', 'between', [$this->yesterday, $this->today])
                ->where('status', '=', '0')->select();
            if (!empty($tasks_published)) {
                foreach ($tasks_published as $k => $v) {
                    $tasks_published[$k]['status'] = 1;
                    $tasks_published[$k]['task_time'] = $this->now;
                    unset($tasks_published[$k]['t_id']);
                    $kw_ids = [];
                    if (!empty($v['kw_id'])) {
                        $kw_ids = explode(',', $v['kw_id']);
                    }
                    $tasks_published[$k]['keyword_num'] = count($kw_ids);
                }
            }
            if (!empty($tasks_canceled)) {
                foreach ($tasks_canceled as $k => $v) {
                    $tasks_canceled[$k]['task_time'] = $this->now;
                    unset($tasks_canceled[$k]['t_id']);
                    $kw_ids = [];
                    if (!empty($v['kw_id'])) {
                        $kw_ids = explode(',', $v['kw_id']);
                    }
                    $tasks_canceled[$k]['keyword_num'] = count($kw_ids);
                }
            }
            $task_gen = array_merge($tasks_published, $tasks_canceled);
            // 处理后插入task
            if (!empty($task_gen)) {
                foreach ($task_gen as $k => $v) {
                    Db::table('task')->insert($v);
                }
            }
        } catch (DbException $e) {
        }
        return true;
    }

    /**
     * 2.任务分发
     */
    public function deliver($t_id = 0)
    {
        try {
            if ($t_id == 0) {
                // 今日待分发的任务(已人工分配关键词)
                $tasks_unpublished_assigned = Db::table('task')
                    ->where('task_time', 'between', [$this->yesterday, $this->today])
                    ->where('status', '=', '1')
                    ->where('kw_id != \'\'')
                    ->select();
                Log::write('今日待分发的任务(已人工分配关键词)');
                Log::write($tasks_unpublished_assigned);
                // 今日待分发的任务(未人工分配，自动分配关键词)
                $tasks_unpublished_unassigned = Db::table('task')
                    ->where('task_time', 'between', [$this->yesterday, $this->today])
                    ->where('status', '=', '1')
                    ->where('kw_id = \'\'')
                    ->select();
                Log::write('今日待分发的任务(未人工分配，自动分配关键词)');
                Log::write($tasks_unpublished_unassigned);
                foreach ($tasks_unpublished_unassigned as $k => $v) {
                    // 自动分配
                    // 1.双匹配
                    $need_num = $v['keyword_num'];
                    $kw_ids_auto = Db::table('keywords')
                        ->where('is_deliver', '=', 0)
                        ->where('user_id', '=', $v['user_id'])
                        ->where('c_id', '=', $v['c_id'])
                        ->limit($need_num)
                        ->column('kw_id');
                    Log::write('双匹配');
                    Log::write($kw_ids_auto);
                    // 2.单项匹配
                    if (count($kw_ids_auto) < $need_num) {
                        $temp = Db::table('keywords')
                            ->where('is_deliver', '=', 0)
                            ->where('(user_id + c_id) != 0')
                            ->where('user_id = ' . $v['user_id'] . ' OR c_id = ' . $v['c_id'])
                            ->limit(($need_num - count($kw_ids_auto)))
                            ->column('kw_id');
                        $kw_ids_auto = array_merge($kw_ids_auto, $temp);
                        Log::write('单项匹配');
                        Log::write($kw_ids_auto);
                    }
                    // 3.无关，只是未分配
                    if (count($kw_ids_auto) < $need_num) {
                        $temp = Db::table('keywords')
                            ->where('is_deliver', '=', 0)
                            ->where('(user_id + c_id) = 0')
                            ->where('user_id', '!=', $v['user_id'])
                            ->where('c_id', '!=', $v['c_id'])
                            ->limit(($need_num - count($kw_ids_auto)))
                            ->column('kw_id');
                        $kw_ids_auto = array_merge($kw_ids_auto, $temp);
                        Log::write('只是未分配');
                        Log::write($kw_ids_auto);
                    }
                    $kw_ids_auto = implode(',', $kw_ids_auto);
                    $tasks_unpublished_unassigned[$k]['kw_id'] = $kw_ids_auto;
                }
                // 合并
                $tasks_unpublished = array_merge($tasks_unpublished_assigned, $tasks_unpublished_unassigned);
                Log::write('合并');
                Log::write($tasks_unpublished);
            } else {
                // 指定任务
                $tasks_unpublished = Db::table('task')->where('t_id', '=', $t_id)->select();
                if(isset($tasks_unpublished[0])&&$tasks_unpublished[0]['kw_id']==''){
                    $temp = Db::table('keywords')
                        ->where('is_deliver', '=', 0)
                        ->limit($tasks_unpublished[0]['keyword_num'])
                        ->column('kw_id');
                    $tasks_unpublished[0]['kw_id']=implode(',',$temp);
                }
                Log::write('指定任务');
                Log::write($tasks_unpublished);
            }
            if (!empty($tasks_unpublished)) {
                foreach ($tasks_unpublished as $k => $v) {
                    $t_id_arr = explode(',', $v['t_id']);
                    foreach ($t_id_arr as $k1 => $v1) {
                        $kw_id_arr = explode(',', $v['kw_id']);
                        foreach ($kw_id_arr as $k2 => $v2) {
                            $arr_insert = [
                                'create_time' => $this->today,
                                'user_id' => $v['user_id'],
                                'kw_id' => $v2,
                                'status' => 0,
                                't_id' => $v1,
                                'article_num' => $v['article_num'],
                                'complete_num' => 0,
                            ];
                            // 处理后插入task_detail
                            Db::table('task_detail')->insert($arr_insert);
                            Log::write('处理后插入task_detail');
                            Log::write($arr_insert);
                        }
                    }
                    $arr_update = [
                        'status' => 2,
                        'release_time' => $this->today,
                        'release_user_id' => session('org_user_id'),
                        'release_user_name' => session('org_user_name')
                    ];
                    // 更新task状态
                    Db::table('task')->where('t_id', '=', $v['t_id'])->update($arr_update);
                    Log::write('更新task状态');
                    Log::write($arr_update);
                }
            }
            return true;
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
            Log::write($e->getMessage());
            return false;
        }
    }

    /**
     *
     * 3.任务完成统计
     */
    public function count()
    {
        // 今日待统计的任务
        $sql = 'SELECT
                    create_time AS task_time,
                    user_id,
                    -- GROUP_CONCAT(kw_id) AS kw_ids,
                    COUNT(kw_id) AS import_num,
                    COUNT(t_id) AS task_import_num,
                    COUNT(CASE WHEN complete_num > 0 THEN 1 ELSE NULL END) AS spread_num,
                    SUM(article_num) AS article_num,
                    SUM(complete_num) AS release_num
                FROM
                  task_detail
                WHERE STATUS = 1
                AND create_time BETWEEN \'' . $this->yesterday . '\' AND \'' . $this->today . '\' GROUP BY user_id';
        $tasks_uncounted = Db::query($sql);
        if (!empty($tasks_uncounted)) {
            foreach ($tasks_uncounted as $k => $v) {
                // 处理后插入task_count
                Db::table('task_count')->insert($v);
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}