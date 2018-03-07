<?php
namespace app\index\controller\keyWords;

use app\index\controller\Base;
use app\index\model\KeywordCheck;
use app\index\model\Role;
use app\index\controller\Zlog;
use think\Exception;
use think\Request;
use think\Db;

class Check extends Base
{

    public function index()
    {
        $m = new KeywordCheck();
        $p= buttonDisable($this->permission,$this->route['keyworkdcheck']);
        $data = $m->getList($p);
        $data['buttonControl']=$p;
        return json($data);
    }

    public function check()
    {
        $m = new KeywordCheck();
        $req = Request::instance()->post();
        if (isset($req['type']) && $req['type']=='many'){
            return json($m->checkMany());
        }else{
            return json($m->editSave());
        }
    }

    public function exportdaily()
    {
        if($this->request->post('type') == 1){
            echo json_encode('ok');exit;
        }
        $m = new KeywordCheck();
        $request = Request::instance()->get();
        $time = empty($request['dataTime'])?date("Y-m-d"):$request['dataTime'];
        $m->setDailyTime($time);
        return json($m->exportdaily());
    }

}
