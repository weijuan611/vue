<?php
namespace app\index\controller\keyWords;

use app\index\controller\Base;
use app\index\controller\Zlog;
use app\index\model\Mlexicon;
use think\Log;
use think\Request;
use app\index\model\Keyword;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11
 * Time: 10:18
 */
class Lexicon extends Base
{
    public function init()
    {
        $search = $this->request->post('searchData/a');
        $model = new Mlexicon();
        $setting_list = $model->setting_list();
        $categories_init = ['classList'=>$model->categories_init()];
        $result = $model->index_list($search);
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['lexicon']);
        $result['keywordNum']=$model->getKeywordsNum();
        return json([$setting_list,$categories_init,$result]);
    }
    public function setting_list()
    {
        $model = new Mlexicon();
        return json($model->setting_list());
    }
    public function setting_add()
    {
        $model = new Mlexicon();
        return json($model->setting_add());
    }
    public function setting_edit()
    {
        $model = new Mlexicon();
        return json($model->setting_edit());
    }
    public function setting_delete()
    {
        $model = new Mlexicon();
        return json($model->setting_delete());
    }
    public function categories_init()
    {
        $model = new Mlexicon();
        return json(['classList'=>$model->categories_init()]);
    }
    public function keywords_add()
    {
        $model = new Mlexicon();
        return json($model->keywords_add());
    }
    public function keywords_edit()
    {
        $model = new Mlexicon();
        return json($model->keywords_edit());
    }
    public function keywords_delete()
    {
        $model = new Mlexicon();
        return json($model->keywords_delete());
    }
    public function keywords_editbench()
    {
        $model = new Mlexicon();
        return json($model->keywords_editbench());
    }
    public function import()
    {
        $model = new Mlexicon();
        $filename = $_FILES['file']['tmp_name'];
        $class_id = (int)$this->request->post('id',0);
        $label1 = (int)$this->request->post('label1','');
        $label2 = (int)$this->request->post('label2','');
        if ($class_id <= 0){
            return json(['type'=>'false',"info"=>"请选择分类"]);
        }
        if (empty ($filename)) {
            return json(['type'=>'false',"info"=>"请选择要导入的CSV文件"]);
        }
        $handle = fopen($filename, 'r');
        $result = $model->input_csv($handle,$class_id,$label1,$label2); //解析csv
        fclose($handle); //关闭指针
        if ($result == 0) {
            return json(['type'=>'false',"info"=>"0条，导入成功！！"]);
        }else{
            return json(['type'=>'true',"info"=>$result."条，导入成功！！"]);
        }

    }

    public function index_list(){
        $search = $this->request->post('search/a');
        $pass=$this->validate($search,[
            'keyword'=>'chsDash|max:32',
            'filterClass'=>'array',
            'filterTags'=>'array',
            'filterIndex'=>'array'
        ]);
        if(true !== $pass){
            // 验证失败 输出错误信息
           return json($pass);
        }
        $model = new Mlexicon();
        $result = $model->index_list($search);
        $result['buttonControl'] = buttonDisable($this->permission,$this->route['lexicon']);
        $result['keywordNum']=$model->getKeywordsNum();
        return json($result);
    }


    public function refresh(){
        $kw=$this->request->get('kw','');
        if(trim($kw) == ''){
            return json(['msg'=>'关键词不可为空！','type'=>false]);
        }
//        Keyword::updateBdIndex($kw);
        $reslt = Keyword::updateBdIndex_new($kw);
        return json($reslt);
    }
    public function trend()
    {
        $model = new Mlexicon();
        return json($model->getTrend());
    }

    public function opponent_init()
    {
        $model = new Mlexicon();
        return json($model->getOpponent());
    }
    public function opponent_list()
    {
        $model = new Mlexicon();
        return json($model->getOpponentList());
    }
    public function opponent_add()
    {
        $model = new Mlexicon();
        return json($model->opponent_add());
    }
    public function opponent_edit()
    {
        $model = new Mlexicon();
        return json($model->opponent_edit());
    }
    public function opponent_delete()
    {
        $model = new Mlexicon();
        return json($model->opponent_delete());
    }
    public function index_query()
    {
        $model = new Mlexicon();
        return json($model->index_query());
    }
    public function index_importquery()
    {
        $model = new Mlexicon();
        return json($model->index_importquery());
    }
}