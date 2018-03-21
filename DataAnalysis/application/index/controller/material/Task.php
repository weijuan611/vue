<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/3/10
 * Time: 13:59
 */

namespace app\index\controller\material;


use app\index\controller\Base;
use app\index\model\Tasks;
use app\script\model\TaskGenerator;

class Task extends Base
{
    public function getList(){
        $model = new Tasks();
        $result = $model->getList();
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['material_task']);
        return json(['data'=>$result['data'],'total'=>$result['total'],'buttonControl'=>$result['buttonControl']]);
    }

    public function getOne(){
        $model= new Tasks();
        $t_id = $this->request->get('t_id',0);
        return json($model->getOne($t_id));
    }

    public function add(){
        $post = $this->request->post();
        $model = new Tasks();
        $result = $model->addTask($post);
        if($result){
            return json(['type'=>'success','message'=>'任务添加成功！']);
        }else{
            return json(['type'=>'error','message'=>$model->getMessage()]);
        }
    }

    public function edit(){
        $post = $this->request->post();
        $model = new Tasks();
        $result = $model->editTask($post);
        if($result){
            return json(['type'=>'success','message'=>'任务添加成功！']);
        }else{
            return json(['type'=>'error','message'=>$model->getMessage()]);
        }
    }

    public function delete(){
        $post = $this->request->post();
        $model = new Tasks();
        $result = $model->deleteTask($post);
        if($result){
            return json(['type'=>true,'message'=>'任务取消成功！']);
        }else{
            return json(['type'=>false,'message'=>$model->getMessage()]);
        }
    }

    public function back(){
        $post = $this->request->post();
        $model = new Tasks();
        $result = $model->backTask($post);
        if($result){
            return json(['type'=>true,'message'=>'任务回复成功！']);
        }else{
            return json(['type'=>false,'message'=>$model->getMessage()]);
        }
    }

    public function release(){
        $post = $this->request->post();
        $model = new TaskGenerator();
        $result = $model->deliver($post['id']);
        if($result){
            return json(['type'=>true,'message'=>'发布成功！']);
        }else{
            return json(['type'=>false,'message'=>$model->getMessage()]);
        }
    }
}