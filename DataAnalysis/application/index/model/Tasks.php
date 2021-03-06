<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/10
 * Time: 14:01
 */

namespace app\index\model;

use think\Db;
use think\Exception;
use think\Log;

class Tasks extends Base
{
    private $message='';


    protected $searchColumn = [
        'userName'=>['t.user_name','like'],
        'taskTime'=>['t.task_time','between'],
        'status'=>['t.status','='],
    ];

    public function getList(){
        $query = Db::table('task t')->join('departments d','t.dp_id = d.dp_id','left')
        ->field('t.*,d.dp_name');
        $this->checkSearch($query);
        $this->checkRange($query,'t.dp_id','t.user_id');
        return $this->autoPaginate($query)->toArray();
    }

    public function getOne($t_id){
        $task = Db::table('task t')->join('departments d','d.dp_id = t.dp_id','left')
           ->join('categories c','t.c_id = c.c_id','left')
           ->where('t_id','=',$t_id)
           ->field('catename,d.dp_name,user_id,user_name,d.dp_id,c.c_id,type,keyword_num,article_num,kw_id,task_time,memo')->find();
        $task['user_name']=[['user_id'=>$task['user_id'],'user_name'=>$task['user_name'],'dp_id'=>$task['dp_id'],
            'dp_name'=>$task['dp_name']]];
        $task['user_id']=[$task['user_id']];
        $task['category']=$task['catename'];
        $task['kw_id']=$task['kw_id'] !=""?array_map('intval',explode(',',$task['kw_id'])):[];
        $data=Db::table('keywords')->field('kw_id,keyword')->where('kw_id','IN',$task['kw_id'])->select();
        if(!empty($data)){
            foreach ($data as $k =>$v){
                $data[$k]['kw_id']=intval($v['kw_id']);
            }
        }
       $task['keywords']=$data;
       unset($task['catename'],$task['dp_name'],$task['dp_id']);
       return $task;
    }

    public function addTask($post){
        $data = [];
        $keywords =[];
        if(count($post['kw_id'])>0){
            foreach ($post['keywords'] as $value){
                if(in_array($value['kw_id'],$post['kw_id'])){
                    $keywords[]=$value['keyword'];
                }
            }
        }
        foreach ($post['user_name'] as $item){
            if(in_array($item['user_id'],$post['user_id'])){
                $data[] = [
                    'task_time'=>date('Y-m-d 00:00:00',strtotime($post['task_time'])),
                    'user_id'=>$item['user_id'],
                    'user_name'=>$item['user_name'],
                    'dp_id'=>$item['dp_id'],
                    'type'=>$post['type'],
                    'keyword_num'=>$post['keyword_num'],
                    'kw_id'=>implode(',',$post['kw_id']),
                    'keywords'=>implode(',',$keywords),
                    'article_num'=>$post['article_num'],
                    'memo'=>$post['memo'],
                    'c_id'=>$post['c_id'],
                    'category'=>$post['category']
                ];
            }
        }
        try{
            Db::startTrans();
            Db::table('task')->insertAll($data);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->message='一人一天只能创建一个任务，请检查数据';
            return false;
        }
        return true;
    }

    public function editTask($post){
        $data = [];
        $keywords =[];
        if(!is_array($post['t_id'])||empty($post['t_id']))
        {
            $this->message='参数错误，请联系管理员';
            return false;
        }
        if(count($post['t_id'])>1){
            $data = [
                'type'=>$post['type'],
                'keyword_num'=>$post['keyword_num'],
                'article_num'=>$post['article_num'],
                'memo'=>$post['memo'],
                'c_id'=>$post['c_id'],
                'category'=>$post['category']
            ];
        }else{
            if(count($post['kw_id'])>0){
                foreach ($post['keywords'] as $value){
                    if(in_array($value['kw_id'],$post['kw_id'])){
                        $keywords[]=$value['keyword'];
                    }
                }
            }
            foreach ($post['user_name'] as $item){
                if(in_array($item['user_id'],$post['user_id'])){
                    $data = [
                        'task_time'=>$post['task_time'],
                        'user_id'=>$item['user_id'],
                        'user_name'=>$item['user_name'],
                        'dp_id'=>$item['dp_id'],
                        'type'=>$post['type'],
                        'keyword_num'=>$post['keyword_num'],
                        'kw_id'=>implode(',',$post['kw_id']),
                        'keywords'=>implode(',',$keywords),
                        'article_num'=>$post['article_num'],
                        'memo'=>$post['memo'],
                        'c_id'=>$post['c_id'],
                        'category'=>$post['category']
                    ];
                }
            }
        }
        try{
            Db::startTrans();
            Db::table('task')->where('t_id','in',$post['t_id'])->update($data);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->message='数据操作错误！请联系管理员';
            return false;
        }
        return true;
    }


    public function deleteTask($post){
        try{
            Db::table('task')->where('t_id','=',$post['id'])->update(['memo'=>$post['memo'],'status'=>0]);
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->message='数据操作错误！请联系管理员';
            return false;
        }
        return true;
    }

    public function backTask($post){
        try{
            Db::table('task')->where('t_id','=',$post['id'])->update(['status'=>1]);
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->message='数据操作错误！请联系管理员';
            return false;
        }
        return true;
    }


    /**
     * @return $message
     */
    public function getMessage()
    {
        return $this->message;
    }
}