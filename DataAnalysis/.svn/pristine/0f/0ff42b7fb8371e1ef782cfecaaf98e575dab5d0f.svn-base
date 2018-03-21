<?php
namespace app\index\controller;


class Zlog
{
    /**
     * 　日志文件
     */
    static $logfile = '';
    /**
     *  是否控制台显示
     */
    static $console = false;

    /**
     * 日志记录
     * params mix $info
     * return boolean
     **/
    public static function write($info)
    {
        if (is_object($info) || is_array($info)) {
            $info_text = var_export($info, true);
        } elseif (is_bool($info)) {
            $info_text = $info ? 'true' : 'false';
        } else {
            $info_text = $info;
        }
        $info_text = '[' . date('Y-m-d H:i:s') . '] ' . $info_text;
        if (!empty(self::$logfile)) {
            error_log($info_text . "\r\n", 3, self::$logfile);
        } else error_log($info_text);
        if (self::$console) echo "\n" . $info_text . "\n";
    }

//   public static function write($info){}
}