<?php
namespace app\common;

/**
 *  Bui前端框架Ajax回调函数
 *
 *  用法Demo：
 *  (new Bjui())->setMessage('错误消息')->ajaxError();
 *  (new Bjui())->setMessage('正确')->ajaxResponse();
 *  (new Bjui())->setMessage('登录超时')->ajaxTimeout();
 *  (new Bjui())->setMessage('关闭当前窗口')->setCloseCurrent(true)->ajaxResponse();
 *  (new Bjui())->setForwardConfirm('URL跳转提示')->setForward('/admin/xxx/')->ajaxResponse();
 *
 *
 * @author: ZDW
 * @date: 2015-05-02
 * @version: $Id: Bjui.php 5164 2015-06-15 09:38:57Z husonghai $
 */
class Bjui
{
    /**
     * 必选。状态码(ok = 200, error = 300, timeout = 301)，可以在BJUI.init时配置三个参数的默认值。
     * @var int
     */
    private $statusCode = 200;

    /**
     * 可选。信息内容。
     * @var string
     */
    private $message = '';

    /**
     * 可选。待刷新navtab id，多个id以英文逗号分隔开，当前的navtab id不需要填写，填写后可能会导致当前navtab重复刷新。
     * @var string
     */
    private $tabid = '';

    /**
     * 可选。待刷新dialog id，多个id以英文逗号分隔开，请不要填写当前的dialog id，要控制刷新当前dialog，请设置dialog中表单的reload参数。
     * @var string
     */
    private $dialogid = '';

    /**
     * 可选。待刷新div id，多个id以英文逗号分隔开，请不要填写当前的div id，要控制刷新当前div，请设置该div中表单的reload参数。
     * @var string
     */
    private $divid = '';

    /**
     * 可选。是否关闭当前窗口(navtab或dialog)。
     * @var bool
     */
    private $closeCurrent = false;

    /**
     * 可选。跳转到某个url。
     * @var string
     */
    private $forward = '';

    /**
     * 可选。跳转url前的确认提示信息。
     * @var string
     */
    private $forwardConfirm = '';

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = (int)$statusCode;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $tabid
     * @return $this
     */
    public function setTabid($tabid)
    {
        $this->tabid = $tabid;
        return $this;
    }

    /**
     * @param $dialogid
     * @return $this
     */
    public function setDialogid($dialogid)
    {
        $this->dialogid = $dialogid;
        return $this;
    }

    /**
     * @param $divid
     * @return $this
     */
    public function setDivid($divid)
    {
        $this->divid = $divid;
        return $this;
    }

    /**
     * @param $closeCurrent
     * @return $this
     */
    public function setCloseCurrent($closeCurrent)
    {
        $this->closeCurrent = (bool)$closeCurrent;
        return $this;
    }

    /**
     * @param $forward
     * @return $this
     */
    public function setForward($forward)
    {
        $this->forward = $forward;
        return $this;
    }

    /**
     * @param $forwardConfirm
     * @return $this
     */
    public function setForwardConfirm($forwardConfirm)
    {
        $this->forwardConfirm = $forwardConfirm;
        return $this;
    }

    /**
     * ajax输出JSON:会话超时
     */
    public function ajaxTimeout()
    {
        if (301 !== $this->statusCode) $this->statusCode = 301;
        if ($this->message === '')
            $this->message = '会话超时';
        echo json_encode(get_object_vars($this));
        exit();
    }

    /**
     * ajax输出JSON
     */
    public function ajaxResponse()
    {
        if (200 !== $this->statusCode) $this->statusCode = 200;
        echo json_encode(get_object_vars($this));
        exit();
    }
    /**
     * ajax输出JSON错误
     */
    public function ajaxError()
    {
        if (300 !== $this->statusCode) $this->statusCode = 300;
        echo json_encode(get_object_vars($this));
        exit();
    }
}