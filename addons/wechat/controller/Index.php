<?php

namespace addons\wechat\controller;

use addons\wechat\library\Config;
use addons\wechat\model\WechatAutoreply;
use addons\wechat\model\WechatCaptcha;
use addons\wechat\model\WechatContext;
use addons\wechat\model\WechatResponse;
use addons\wechat\model\WechatConfig;

use EasyWeChat\Factory;
use addons\wechat\library\Wechat as WechatService;
use addons\wechat\library\Config as ConfigService;
use think\Db;
use think\Log;

/**
 * 微信接口
 */
class Index extends \think\addons\Controller
{

    public $app = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->app = Factory::officialAccount(Config::load());
    }

    /**
     *
     */
    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    /**
     * 微信API对接接口
     */
    public function api()
    {
        $this->app->server->push(function ($message) {
            $wechatService = new WechatService;

            $matches = null;
            $openid = $message['FromUserName'];
            $to_openid = $message['ToUserName'];

            $unknownMessage = WechatConfig::getValue('default.unknown.message');
            $unknownMessage = $unknownMessage ? $unknownMessage : "";

            switch ($message['MsgType']) {
                case 'event': //事件消息
                    $event = $message['Event'];
                    $eventkey = $message['EventKey'] ? $message['EventKey'] : $message['Event'];
                    //验证码消息
                    if (in_array($event, ['subscribe', 'SCAN']) && preg_match("/^captcha_([a-zA-Z0-9]+)_([0-9\.]+)/", $eventkey, $matches)) {
                        return WechatCaptcha::send($openid, $matches[1], $matches[2]);
                    }
                    switch ($event) {
                        case 'subscribe'://添加关注
                            $subscribeMessage = WechatConfig::getValue('default.subscribe.message');
                            $subscribeMessage = $subscribeMessage ? $subscribeMessage : "欢迎关注我们!";
                            return $subscribeMessage;
                        case 'unsubscribe'://取消关注
                            return '';
                        case 'LOCATION'://获取地理位置
                            return '';
                        case 'VIEW': //跳转链接,eventkey为链接
                            return '';
                        case 'SCAN': //扫码
                            return '';
                        default:
                            break;
                    }

                    $wechatResponse = WechatResponse::where(["eventkey" => $eventkey, 'status' => 'normal'])->find();
                    if ($wechatResponse) {
                        $responseContent = (array)json_decode($wechatResponse['content'], true);
                        $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
                        $data = ['eventkey' => $eventkey, 'command' => '', 'refreshtime' => time(), 'openid' => $openid];
                        if ($wechatContext) {
                            $wechatContext->save($data);
                        } else {
                            $wechatContext = WechatContext::create($data, true);
                        }
                        $result = $wechatService->response($this, $openid, '', $responseContent, $wechatContext);
                        if ($result) {
                            return $result;
                        }
                    }
                    return $unknownMessage;
                case 'text': //文字消息
                case 'image': //图片消息
                case 'voice': //语音消息
                case 'video': //视频消息
                case 'location': //坐标消息
                case 'link': //链接消息
                default: //其它消息
                    //自动回复处理
                    if ($message['MsgType'] == 'text') {
                        $autoreply = null;
                        $autoreplyList = WechatAutoreply::where('status', 'normal')->cache(true)->order('weigh DESC,id DESC')->select();
                        foreach ($autoreplyList as $index => $item) {
                            //完全匹配和正则匹配
                            if ($item['text'] == $message['Content'] || (in_array(mb_substr($item['text'], 0, 1), ['#', '~', '/']) && preg_match($item['text'], $message['Content'], $matches))) {
                                $autoreply = $item;
                                break;
                            }
                        }

                        if ($autoreply) {
                            $wechatResponse = WechatResponse::where(["eventkey" => $autoreply['eventkey'], 'status' => 'normal'])->find();
                            if ($wechatResponse) {
                                $responseContent = (array)json_decode($wechatResponse['content'], true);
                                $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
                                $result = $wechatService->response($this, $openid, $message['Content'], $responseContent, $wechatContext, $matches);
                                if ($result) {
                                    return $result;
                                }
                            }
                        }
                    }
                    return $unknownMessage;
            }
            return ""; //SUCCESS
        });

        $response = $this->app->server->serve();
        // 将响应输出
        $response->send();
        return;
    }

    /**
     * 登录回调
     */
    public function callback()
    {

    }

    /**
     * 支付回调
     */
    public function notify()
    {
        $config =  [
            'app_id' => 'wxa3704686d395d189',
            'mch_id' => '1600262687',
            'key'         => 'nZnZnZnZnZnZnZnZnZnZnZnZnZnZnZnZ',
            'cert_path'   => ROOT_PATH.'/extend/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'    => ROOT_PATH.'/extend/cert/apiclient_key.pem', // XXX: 绝对路径！！！！
            'notify_url' => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify'
            // 'device_info'     => '013467007045764',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
            // ...
        ];
        $app = Factory::payment($config);
        $this->logs('没进来');
        Log::record(file_get_contents('php://input'), "notify");
        $response = $app->handlePaidNotify(function ($message, $fail) {
            // 你的逻辑
            $config =  [
                'app_id' => 'wxa3704686d395d189',
                'mch_id' => '1600262687',
                'key'         => 'nZnZnZnZnZnZnZnZnZnZnZnZnZnZnZnZ',
                'cert_path'   => ROOT_PATH.'/extend/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
                'key_path'    => ROOT_PATH.'/extend/cert/apiclient_key.pem', // XXX: 绝对路径！！！！
                'notify_url' => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify'
                // 'device_info'     => '013467007045764',
                // 'sub_app_id'      => '',
                // 'sub_merchant_id' => '',
                // ...
            ];
            $this->logs(1);
            $app = Factory::payment($config);
            $order = $app->order->queryByTransactionId($message['transaction_id']);
            $this->logs($order);
            if (!$order) { // 如果订单不存在 或者 订单已经支付过了
                $this->logs(2);
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            $this->logs(3);
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                $this->logs(4);
                if ($message['result_code'] === 'SUCCESS') {

                    $orderInfo = Db::name('order')->where('order_num',$message['out_trade_no'])->find();
                    if($orderInfo){
                        DB::startTrans();
                        try {
                            $res = [
                                'createtime' => time(),
                                'status' => 1,
                                'id' => $orderInfo['id']
                            ];
                            $memberInfo = Db::name('member')->where('id',$orderInfo['member_id'])->find();//获取个人信息
                            if($memberInfo['topid'] !== 0){
                                $rewardAmount = 0;
                                if($orderInfo['type'] == 1){//银行卡查询
                                    $reward = \think\Config::get('site')['yhk_reward'];
                                }else{
                                    $reward = \think\Config::get('site')['credit_reward'];
                                }
                                $this->logs($reward);
                                if($reward > 0){
                                    $rewardAmount = $reward;
                                }
                                $topidMoney = Db::name('member')->where('id',$memberInfo['topid'])->value('money');
                                Db::name('member')->where('id',$memberInfo['topid'])->update([
                                    'money' => $topidMoney + $rewardAmount
                                ]);
                            }

                            $aa= Db::name('member')->where('id',$orderInfo['member_id'])->update([
                                'is_zx' => 1,
                            ]);

                            $res = Db::name('order')->update($res);


                            DB::commit();
                        }catch (\Exception $e){
                            DB::rollback();
                            $this->error('pay error');
                        }
                    }
//                    $this->logs('success');
                    // 用户支付失败
                    return true;
                } elseif ($message['result_code'] === 'FAIL') {
                    $this->logs(6);
                    $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });

        $response->send();

        return;
    }
    public function notify_hf()
    {
        $config =  [
            'app_id' => 'wxa3704686d395d189',
            'mch_id' => '1600262687',
            'key'         => 'nZnZnZnZnZnZnZnZnZnZnZnZnZnZnZnZ',
            'cert_path'   => ROOT_PATH.'/extend/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'    => ROOT_PATH.'/extend/cert/apiclient_key.pem', // XXX: 绝对路径！！！！
            'notify_url' => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify_hf'
            // 'device_info'     => '013467007045764',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
            // ...
        ];
        $app = Factory::payment($config);
        $this->logs('没进来');
        Log::record(file_get_contents('php://input'), "notify");
        $response = $app->handlePaidNotify(function ($message, $fail) {
            // 你的逻辑
            $config =  [
                'app_id' => 'wxa3704686d395d189',
                'mch_id' => '1600262687',
                'key'         => 'nZnZnZnZnZnZnZnZnZnZnZnZnZnZnZnZ',
                'cert_path'   => ROOT_PATH.'/extend/cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
                'key_path'    => ROOT_PATH.'/extend/cert/apiclient_key.pem', // XXX: 绝对路径！！！！
                'notify_url' => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify_hf'
                // 'device_info'     => '013467007045764',
                // 'sub_app_id'      => '',
                // 'sub_merchant_id' => '',
                // ...
            ];
            $this->logs(1);
            $app = Factory::payment($config);
            $order = $app->order->queryByTransactionId($message['transaction_id']);
            $this->logs($order);
            if (!$order) { // 如果订单不存在 或者 订单已经支付过了
                $this->logs(2);
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            $this->logs(3);
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                $this->logs(4);
                if ($message['result_code'] === 'SUCCESS') {

                    $orderInfo = Db::name('hf_list')->where('order_num',$message['out_trade_no'])->find();
                    $this->logs('2222');
                    if($orderInfo){

                            $res = [
                                'createtime' => time(),
                                'status' => 1,
                                'id' => $orderInfo['id']
                            ];
                            Db::name('hf_list')->update($res);
//
                    }

//                    $this->logs('success');
                    // 用户支付失败
                } elseif ($message['result_code'] === 'FAIL') {
                    $this->logs(6);
                    $order->status = 'paid_fail';
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });

        $response->send();

        return;
    }
    public function logs($data){
        file_put_contents(ROOT_PATH.'/runtime/log/pay.log',$data);
    }

}
