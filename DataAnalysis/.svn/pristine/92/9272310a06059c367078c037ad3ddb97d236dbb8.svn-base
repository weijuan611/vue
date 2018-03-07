<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2018/1/24
 * Time: 16:18
 */
namespace app\common;

use think\Log;
use think\Exception;
use think\Db;

class BULogin {
    private $id=0;
    private $cookie;
    private $token;
    private $user;
    private $passwd;
    private $codeStr = '';
    private $key = '';
    private $gid = '83CC686-2F6A-431B-863B-DE7938F0694C';
    private $traceid='F383EC01';


    public $table='user_account';
    public $cookie_column = 'cookie';
    public $user_column = 'baidu_name';
    public $passwd_column = 'baidu_pwd';
    public $primary = 'id';
    public $err_code =0;
    public $err_msg ='';
    public $code_img = '';

    public function __construct($id)
    {
        $this->id = $id;
        $this->gid = $this->randomString(35);
        $this->traceid = $this->randomString(8);
    }

    /**
     * 获取验证码
     * @param $id  唯一标识
     */
    public function getCode(){
        $this->setUserInfo();
        $this->loginBegin();
        return $this->login();
    }

    public function loginByCode($code){
        $this->loadParam();
        if($this->checkCode($code)){
            return  $this->login($code);
        }else{
            Log::info('验证码错误');
            $this->err_code = 257;
            return false;
        }
    }

    private function loginBegin(){
        //home url
        $ch = curl_init();
        $hurl = 'https://index.baidu.com/';
        $opt = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,//I need some header information
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
            CURLOPT_URL => $hurl,
        );
        curl_setopt_array($ch, $opt);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //get token
        $turl = 'http://passport.baidu.com/v2/api/?getapi&tpl=mn&apiver=v3&tt=' . $this->getTime() . '&class=login&gid=' . $this->gid . '&logintype=basicLogin&traceid='.$this->traceid.'&callback=bd__cbs__g2sxpr';
//        $turl = 'https://passport.baidu.com/v2/api/?getapi&tpl=nx&apiver=v3&tt=1516246631134&class=login&gid=3DF0B93-4555-49C6-A34D-69F1354AAF57&logintype=basicLogin&traceid=&callback=bd__cbs__g2sxpr';
        $opt[CURLOPT_URL]=$turl;
        $opt[CURLOPT_COOKIE]=$this->cookie;
        curl_setopt_array($ch,$opt);
        $get = curl_exec($ch);
        $this->handleCookie($get);
        $pattern = '/(?<="token"\s:\s")\w+/';
        preg_match($pattern, $get, $mat);
        $this->token = $mat[0];

        //logincheck
        //https://passport.baidu.com/v2/api/?logincheck&token=4e3003871b169bfb2eef6800bfae4e4c&tpl=nx&apiver=v3&tt=1516347435849&sub_source=leadsetpwd&username=%E5%B0%8F%E5%98%80323&isphone=false&dv=tk0.30392979979167211516347411510%40wpm0epAWsd9koaBdVV9m-JsJavJ32AGlpdG~f~MurdFZ2i08-wAWpX9kB~B8VV9m-JsJavJ32AGlpdG~fV6GAlSufd0m-wwm0nvCk5gBm-JsJavJ32AGlpdG~f~MurdFZ2i08V-CFqXBvVxBdVV92DhF3S8sJ~TBF7TGxrl0G7C6K~O9kpaBWJuTk5gBm-JsJavJ32AGlpdG~f~MurdFZ2i08V-CFMlBvVX9kqgrp2CD~7hFrw-BOfTSGAOM3aRPKJgBF3XCkDwBF6gBm-JsJavJ32AGlpdG~f~MurdFZ2i08VdBkqaAbV_zpp6CpEFqH6HhEmhB8Vd9k6dumqS4gV9WBVBl3dCFMaCFMaBF6xBWp-AFpuBlsxAkp-AFpVhmqHvDyMkEj9uOY04rX9Z7RHKD~9ZAjP8wgSKa30K0IPZr3HmfAkqgBmV-Blsl9ko-B8V-Bl6V9kMuCmV-Bl6V9kplAWqgAloX&traceid=&callback=bd__cbs__mn67rs
        $turl = 'http://passport.baidu.com/v2/api/?logincheck&token=' . $this->token . '&tpl=nx&apiver=v3&tt=' . $this->getTime() . '&sub_source=leadsetpwd&username=' . urlencode($this->user) . '&isphone=false&dv=tk0.30392979979167211516347411510%40wpm0epAWsd9koaBdVV9m-JsJavJ32AGlpdG~f~MurdFZ2i08-wAWpX9kB~B8VV9m-JsJavJ32AGlpdG~fV6GAlSufd0m-wwm0nvCk5gBm-JsJavJ32AGlpdG~f~MurdFZ2i08V-CFqXBvVxBdVV92DhF3S8sJ~TBF7TGxrl0G7C6K~O9kpaBWJuTk5gBm-JsJavJ32AGlpdG~f~MurdFZ2i08V-CFMlBvVX9kqgrp2CD~7hFrw-BOfTSGAOM3aRPKJgBF3XCkDwBF6gBm-JsJavJ32AGlpdG~f~MurdFZ2i08VdBkqaAbV_zpp6CpEFqH6HhEmhB8Vd9k6dumqS4gV9WBVBl3dCFMaCFMaBF6xBWp-AFpuBlsxAkp-AFpVhmqHvDyMkEj9uOY04rX9Z7RHKD~9ZAjP8wgSKa30K0IPZr3HmfAkqgBmV-Blsl9ko-B8V-Bl6V9kMuCmV-Bl6V9kplAWqgAloX&traceid='.$this->traceid.'&callback=bd__cbs__mn67rs';
        curl_setopt($ch, CURLOPT_URL, $turl);
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //get key
        // https://passport.baidu.com/v2/getpublickey?token=4e3003871b169bfb2eef6800bfae4e4c&tpl=nx&apiver=v3&tt=1516347435856&gid=EE037D1-8FB7-4E91-B96C-123966077710&traceid=&callback=bd__cbs__3dbi8r
        $turl = 'http://passport.baidu.com/v2/getpublickey?token=' . $this->token . '&tpl=nx&apiver=v3&tt=' . $this->getTime() . '&gid=' . $this->gid . '&traceid='.$this->traceid.'&callback=bd__cbs__4kv947';
        curl_setopt($ch, CURLOPT_URL, $turl);
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        $get = stripcslashes(curl_exec($ch));
        preg_match('/-----BEGIN\sPUBLIC\sKEY-----[A-Za-z0-9\/\+\s]+-----END\sPUBLIC\sKEY-----/', $get, $mat);
        openssl_public_encrypt($this->passwd, $this->passwd, $mat[0]);
        $this->passwd = base64_encode($this->passwd);
        preg_match('/key":\'\w+/', $get, $mat);
        $this->key = substr($mat[0], 6);

        curl_close($ch);
        return true;
    }

    private function login($verify_code=''){
        //curl
        $ch = curl_init();
        $posta = array(
            'staticpage' => 'http://index.baidu.com/static/v3Jump.htm',
            'charset' => 'UTF-8',
            'token' => $this->token,
            'tpl' => 'nx',
            'subpro' => '',
            'apiver' => 'v3',
            'tt' => $this->getTime(),
            'codestring' => $this->codeStr,
            'safeflg' => 0,
            'u' => 'http://index.baidu.com/',
            'isPhone' => 'false',
            'detect' => 1,
            'quick_user' => 0,
            'logintype' => 'basicLogin',
            'logLoginType' => 'pc_loginBasic',
            'idc' => '',
            'loginmerge' => 'true',
            'username' => $this->user,
            'password' => $this->passwd,
            'mem_pass' => 'on',
            'rsakey' => $this->key,
            'crypttype' => 12,
            'ppui_logintime' => 171122,
            'countrycode' => '',
            'fp_uid' => '96199e08a681f39d918a3b8ce436951d',
            'fp_info' => '96199e08a681f39d918a3b8ce436951d002~~~DwDDnNprYxfVcwX_VDD5xpGc8cxcuSWptS_ppGc8cxcuSWctS_QDySs~DySsIDDsJDyK0sp0eDGmkPpuyk4tEkcwDkptGy48BhGm-w4~cw48vWpwvyc83yF~Owp85C48vwc0~yFfGgptEWp83W48fhGxkPqib6GxD_BVDvzVDvtVDvKVDsLp0QyDmUgrmbT9mY39xpz9IYVr3qNGizjrn-b9ibT9mYwYxf~qZqJ9iY6KIpf9MpfGxqNGMs65ZfT5IpL9xqXY3zc5ZY~KIeNrif~qIUt9xyCqIyC2TjxqZ1wKIUV4t3VpnOO2t3kct~JCp0ryqmYi5ZY6ruyt9xzQrIyJ5xbCKIUVGWyiqmB~5tGkqtXgqiEWpxbt58ffpmBtpwSWc0SxpwXOqtDgc85OcmEg4I5x58qjptrfc03z58j~pxExc0GCqI3zuVDsSVDsAVDsGVDsUDDXEprHiSQECO_rpGrIyL9iUW9S__DVDsODDveVDvjVDvYVDvMVDvWVDvopQ283Vp0X-p0EO40Xw40GCpwXCpE__',
            'dv' => '',
            'traceid' => $this->traceid,
            'callback' => 'parent.bd__pcbs__1tc6i7'
        );
        if ($verify_code != '') {
            $posta['verifycode'] = $verify_code;
        }
        $postf = $this->array2urlencode($posta);
        $opt = array(
            CURLOPT_POST => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
            CURLOPT_URL => 'http://passport.baidu.com/v2/api/?login',
            CURLOPT_COOKIE => $this->cookie,
            CURLOPT_POSTFIELDS => $postf,
        );
        curl_setopt_array($ch, $opt);
        $get = curl_exec($ch);
        curl_close($ch);
        //get error
        $pattern = '/(?<="err_no=)\w+/';
        preg_match($pattern, $get, $mat);
        $err = $mat[0];
//        Log::info($get);
        if ($err != 0) {
            $this->err_code = $err;
            if ($err == 257&&$verify_code == '') {
                //try to find codeString
                $pattern = '/(?<=codeString=)[^&]+?(?=&)/';
                preg_match($pattern, $get, $mat);
                //https://passport.baidu.com/cgi-bin/genimage?jxGa207e2270b68c1cd02d1156598019f7b1bc744068c0131b5
                $this->code_img = 'https://passport.baidu.com/cgi-bin/genimage?' . $mat[0];
                $this->codeStr = $mat[0];
                $this->saveParam();
                return $this->code_img;
            }else{
                $this->baiduError();
                Log::info($this->err_code);
                return false;
            }
        }else{
            $this->handleCookie($get);
            $this->loginAfter($get);
            $this->saveCookie();
            return true;
        }
    }
    

    private function checkCode($verify){
        //checkvcode
        //https://passport.baidu.com/v2/?checkvcode&token=0a70c11a5b0b840e00e278766865dd06&tpl=nx&apiver=v3&tt=1516588846173&verifycode=vcfr&codestring=jxG3607c1010c80c10c02db15104301867f9204430713017e21&traceid=C4ADFA01&callback=bd__cbs__pyhz86
        //bd__cbs__pyhz86({"errInfo":{        "no": "0",        "msg": ""    },    "data": {    },    "traceid": "C4ADFA01"})
        $url ='http://passport.baidu.com/v2/?checkvcode&token='.$this->token.'&tpl=nx&apiver=v3&tt='.$this->getTime().'&verifycode='.urlencode($verify).'&codestring='.$this->codeStr.'&traceid='.$this->traceid.'&callback=bd__cbs__pyhz86';
        $opt = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_COOKIE => $this->cookie,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36"
        );
        $ch = curl_init();
        curl_setopt_array($ch,$opt);
        $get = curl_exec($ch);
        $pattern = '/"no": "\w+/';
        preg_match($pattern, $get, $mat);
        curl_close($ch);
        if(substr($mat[0],7,1) != 0){
           return false;
        }else{
           return true;
        }
    }

    private function loginAfter($get){
        $ch = curl_init();
        $opt = array(
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
        );
        curl_setopt_array($ch, $opt);
        //jump
        $url = '';
        $pattern = '/href \+=\s"[A-Za-z0-9_:=%&.\/]+/';
        preg_match($pattern, $get, $mat);
        $url.=$mat[0];
        $pattern = "/var\saccounts\s=\s'[A-Za-z0-9_:=%&.\/]+/";
        preg_match($pattern, $get, $mat);
        $url.=$mat[0];
//        $this->cookie = 'BDUSS=diZkp1QU5EMlNKfmdrdTRrWHFWb0RqLU5yYnBEVEN4NEpoelA2Sy1NNmtLWTFhQUFBQUFBJCQAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAKScZVqknGVaME;  HOSUPPORT=1; UBI=fi_PncwhpxZ; PASSID=MIJC73; HISTORY=ab2f1c6b4150a80e83b04e994ebb16a0574e8b081d0ddc75; PTOKEN=deleted; BDUSS=XZZRE9; SAVEUSERID=20f85986f2c2625ba6b1ac9d14773a28bc5301cd; USERNAMETYPE=2; STOKEN=deleted; UBI=fi_PncwhpxZ; PASSID=55GydU; BAIDUID=830FA20BC11E38408BC33D741F8199DA:FG=1';
//        $url = 'err_no=0&callback=parent.bd__pcbs__1tc6i7&codeString=&userName=915840223%40qq.com&phoneNumber=&mail=&hao123Param=akpaTmxCdWJHMVdjemxxUVRoak1tbzRZMUZIT1VWa2NsRlNOV1F6WmsxVlowMTBWelp2ZEdWLVIzcFpXVFZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBTFBVWmxxejFHWmFV&u=http://index.baidu.com/&tpl=nx&secstate=&gotourl=&authtoken=&loginproxy=&resetpwd=&vcodetype=&lstr=&ltoken=&bckv=1&bcsync=DmK6NaL6zYNLv71Fqz7P%2B1%2FnvoaF%2BY%2F%2FkppqYFqd5rX28VRNXw%2FtMy86ynGBmxKVOs1rWZReIwm8v3O%2FN4iS7YTBasMb4D%2Bq7YwOpOuse8dCPAxdV9oOQ69SFHZk2dFh8bOHItn2UlypJMicBpOduAak9fGcfkwhoZRj9Dbh9Xm2u0CJBmLx4W6%2BQ7%2FRLVO6Wey7S2kEI2V%2BgHmXPDEBwVrhVoJm%2FLcdR6CMb%2BqAhlrhvaiK7OYDJITYtBctJHZWqAQM7IND4MekGMFPj77cKPzjZ9gPS0jUjxtpDz%2FyktApTYE8LvB5KG0LQzQJrPdtEwHCLcjYydZcjFyA4FSRDg%3D%3D&bcchecksum=2436702592&code=&bdToken=&realnameswitch=&setpwdswitch=&bctime=1516688563&bdstoken=&authsid=&jumpset=&appealurl=&realnameverifyemail=0&traceid=&realnameauthsid=';
        $config=[];
        if($url != ''){
            $tmp = explode('&',$url);
            if(!empty($tmp)){
                foreach ($tmp as $item){
                    $pos = strpos($item,'=');
                    if($pos){
                        $config[trim(substr($item,0,$pos))]=trim(substr($item,$pos+1));
                    }
                }
            }
        }

        //http://user.nuomi.com/pclogin/main/crossdomain?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460757
        $curl = "https://user.nuomi.com/pclogin/main/crossdomain?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        try{
            curl_setopt($ch,CURLOPT_URL,$curl);
            curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
            $get = curl_exec($ch);
            $this->handleCookie($get);
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString(),PHP_EOL);
        }

        //http://user.hao123.com/static/crossdomain.php?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460759
        $curl = "http://user.hao123.com/static/crossdomain.php?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //http://tieba.baidu.com?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460757
        $curl = "https://tieba.baidu.com?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //https://ckpass.baidu.com/api/sync?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460758
        $curl = "https://ckpass.baidu.com/api/sync?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //https://www.baifubao.com/api/0/sync_bduss/0?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460758
        $curl = "https://www.baifubao.com/api/0/sync_bduss/0?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //https://passport.qianqian.com/bdpass?bdu=VFJIWm13M1VYUnhRMU5NUTFGNmVXbG5kMjlzY3poek1ETlFVRlJ5U2xWUmIwYzViSGgzVEhSQ09VOVJTVEZoUVZGQlFVRkJKQ1FBQUFBQUFBQUFBQUVBQUFDeTdJUU8wS0hnMWpNeU13QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBRTZ6WlZwT3MyVmFR&t=1516614460758
        $curl = "https://passport.qianqian.com/bdpass?bdu=".$config['hao123Param'].'&t='.$this->GetTime();
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        //http://index.baidu.com/?tpl=trend&word=2017
        $curl = "http://index.baidu.com/?tpl=trend&word=2017";
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        // 获取百度收录cookie
        $curl = "https://ziyuan.baidu.com/linksubmit/url";
        curl_setopt($ch,CURLOPT_URL,$curl);
        curl_setopt($ch,CURLOPT_COOKIE,$this->cookie);
        $get = curl_exec($ch);
        $this->handleCookie($get);

        curl_close($ch);
    }

    private function saveParam(){
        try{
            Db::table($this->table)->where($this->primary,'=',(int)$this->id)
                ->update([$this->cookie_column =>json_encode([
                    'cookie'=>$this->cookie,
                    'token'=>$this->token,
                    'user'=>$this->user,
                    'passwd'=>$this->passwd,
                    'codeStr'=>$this->codeStr,
                    'key'=>$this->key,
                    'gid'=>$this->gid,
                    'traceid'=>$this->traceid
                ])]);
            Log::info('save -- '.var_export([
                'cookie'=>$this->cookie,
                'token'=>$this->token,
                'user'=>$this->user,
                'passwd'=>$this->passwd,
                'codeStr'=>$this->codeStr,
                'key'=>$this->key,
                'gid'=>$this->gid,
                'traceid'=>$this->traceid
            ],1));
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
        }
    }

    private function loadParam(){
       $config= Db::table($this->table)->where($this->primary,'=',(int)$this->id)
            ->value($this->cookie_column);
       if($config){
           $config = json_decode($config,1);
           $this->cookie = $config['cookie'];
           $this->token=$config['token'];
            $this->user=$config['user'];
            $this->passwd=$config['passwd'];
            $this->codeStr=$config['codeStr'];
            $this->key=$config['key'];
            $this->gid=$config['gid'];
            $this->traceid=$config['traceid'];
            Log::info('load -- '.var_export($config,1));
       }
    }

    private function setUserInfo(){
        $user_info = Db::table($this->table)->where($this->primary,'=',(int)$this->id)
            ->field($this->user_column)->field($this->passwd_column)->find();
        if($user_info){
            $this->user=$user_info[$this->user_column];
            $this->passwd=base64_decode(trim($user_info[$this->passwd_column]));
            Log::info('user-info -- '.var_export($user_info,1));
        }
    }

    private function saveCookie(){
        try{
            Db::table($this->table)->where($this->primary,'=',(int)$this->id)
                ->update([$this->cookie_column =>$this->cookie]);
        }catch (Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);
        }
    }

    private function handleCookie($get)
    {
        $pattern = '/Set-Cookie:\s[A-Za-z0-9_:=]+/';
        if (preg_match_all($pattern, $get, $mat)) {
            $arr = array_unique($mat[0]);
            if(!empty($arr)){
                $cookie_arr = $this->cookieToArray($this->cookie);
                foreach ($arr as $item) {
                    $item =substr($item, 12);
                    $pos = strpos($item,'=');
                    $cookie_arr[trim(substr($item,0,$pos))]=trim(substr($item,$pos+1));
                }
                $this->cookie = $this->cookieToStr($cookie_arr);
            }
        }
    }

    private function cookieToArray($str){
        $arr=[];
        if($str != ''){
            $tmp = explode('; ',$str);
            if(!empty($tmp)){
                foreach ($tmp as $item){
                    $pos = strpos($item,'=');
                    if(trim(substr($item,0,$pos)) !=''){
                        $arr[trim(substr($item,0,$pos))]=trim(substr($item,$pos+1));
                    }
                }
            }
        }
        return $arr;
    }

    private function cookieToStr(Array $arr){
        $str ='';
        if(!empty($arr)){
            $key = array_keys($arr);
            foreach ($key as $item){
                $str .= $item.'='.$arr[$item].'; ';
            }
        }
        return $str;
    }

    private function array2urlencode($arr)
    {
        $u = '';
        foreach ($arr as $key => $str) {
            $u = $u . $key . '=' . urlencode($str) . '&';
        }
        $u = substr($u, 0, -1);
        return $u;
    }

    private function getTime()
    {
        //13位时间戳
        $date = microtime(true) * 1000;
        $date = floor($date);
        return $date;
    }

    private function randomString($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function baiduError()
    {
        switch ($this->err_code) {
            case 1:
                $this->err_msg = "您输入的帐号格式不正确";
                break;
            case 2:
                $this->err_msg = "您输入的帐号不存在";
                break;
            case 3:
                $this->err_msg = "验证码不存在或已过期,请重新输入";
                break;
            case 4:
                $this->err_msg = "您输入的帐号或密码有误";
                break;
            case 6:
                $this->err_msg = "您输入的验证码有误";
                break;
            case 7:
                $this->err_msg = "密码错误";
                break;
            case 16:
                $this->err_msg = "您的帐号因安全问题已被限制登录";
                break;
            case 500010:
                $this->err_msg = "登录过于频繁,请24小时后再试";
                break;
        }
    }
}