<?php
namespace app\api\controller;

use addons\wechat\library\Config as ConfigService;
use app\common\controller\Api;
use EasyWeChat\Factory;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order\ServiceProvider;
use think\Cache;
use think\Db;



class Payment extends Api{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    /**
     * 下单信息
     * @param int   member_id 用户ID
     * @param int   course_id   当前课程ID
     */
    public function payment(){


            if(Request()->isPost()){

                $res = input('');
                $res['order_num'] = $this->getNumberCode(12);
                $res['createtime'] = time();

//                dump($res);exit;
                $data_id = Db::name('order')->insertGetId($res);

                if(!$data_id) return $this->return_msg(400,'缺失参数');

                $info = Db::name('order')
                    ->alias('o')
                    // ->join('goods g','g.id = o.goods_id')
                    ->join('member m','m.id = o.member_id')
                    ->where('o.id',$data_id)
                    ->field('o.*,m.openid,m.nickname,m.id as mid,m.topid')
                    ->find();



                $attributes = [
                    'trade_type'       => 'JSAPI',
                    'body'             =>$info['order_num'],
                    'detail'           => $info['order_num'],
                    'out_trade_no'     => $info['order_num'],
                    // 'total_fee'        => $info['price'] * 100,
                    'total_fee'        => $info['price'] * 100,
                    'notify_url'       => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify',
                    'openid'           => $info['openid']
                ];
                $config = $this->prePay($attributes);//唤起支付
//                dump($config);
                if($config) {
                    return $this->return_msg(1,'success',$config);
                }
            }

    }
    public function payment_hf(){


        if(Request()->isPost()){

            $res = input('');
            $res['order_num'] = $this->getNumberCode(12);
            $res['createtime'] = time();

//                dump($res);exit;
            $data_id = Db::name('hf_list')->insertGetId($res);

            if(!$data_id) return $this->return_msg(400,'缺失参数');

            $info  = Db::name('hf_list')
                ->alias('h')
                ->join('member m','m.id = h.member_id')
                ->where('h.id',$data_id)
                ->field('m.openid,h.*')
                ->find();



            $attributes = [
                'trade_type'       => 'JSAPI',
                'body'             =>$info['order_num'],
                'detail'           => $info['order_num'],
                'out_trade_no'     => $info['order_num'],
                // 'total_fee'        => $info['price'] * 100,
                'total_fee'        => $info['price'] * 100,
                'notify_url'       => 'https://'.$_SERVER['SERVER_NAME'].'/addons/wechat/index/notify_hf',
                'openid'           => $info['openid']
            ];
            $config = $this->prePay_hf($attributes);//唤起支付
//                dump($config);
            if($config) {
                return $this->return_msg(1,'success',$config,$data_id);
            }
        }

    }
    /**
     * 获取 prepay_id   并返回支付验证参数
     * @return mixed
     */
    public function prePay($attributes){

//        Application(ConfigService::load())
//        $app = new \EasyWeChat\Payment\Application(ConfigService::load());
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
    $payment = $app->jssdk;
//        dump($payment);
    $order = $app->order->unify($attributes);

    if ($order['return_code'] == 'SUCCESS' && $order['return_code'] == 'SUCCESS'){

        $prepay_id = $order['prepay_id'];

        $json = $payment->sdkConfig($prepay_id);
        return $json;

    }else if($order['err_code_des'] == '该订单已支付' && $order['err_code'] == 'ORDERPAID'){

        $this->error('该订单已支付');
    }
}
    public function prePay_hf($attributes){

//        Application(ConfigService::load())
//        $app = new \EasyWeChat\Payment\Application(ConfigService::load());
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
        $payment = $app->jssdk;
//        dump($payment);
        $order = $app->order->unify($attributes);

        if ($order['return_code'] == 'SUCCESS' && $order['return_code'] == 'SUCCESS'){

            $prepay_id = $order['prepay_id'];

            $json = $payment->sdkConfig($prepay_id);
            return $json;

        }else if($order['err_code_des'] == '该订单已支付' && $order['err_code'] == 'ORDERPAID'){

            $this->error('该订单已支付');
        }
    }

    /**
     * 提现
     */
    public function withdrawalAdmin(){
//        if($this->checkToken()){
        if(Request()->isPost()){
            $res = input('');
            $admin = Db::name('admin')->where('id',$res['auth_id'])->find();
            if(empty($admin['code'])) return $this->return_msg(0,'提现身份码为空，请设置！');
            $info = Db::name('member')->where('code',$admin['code'])->find();

            $res['order_num'] = $this->getNumberCode(12);
            $res['createtime'] = time();
            $res['member_id'] = $info['id'];
            $auth_id = $res['auth_id'];
            unset($res['auth_id']);
//            dump($res);die;
            if(Cache::get('str') == $res['str']) return $this->return_msg(0,'请勿重复点击！');
            Cache::set('str',$res['str']);
            unset($res['str']);
            $data_id = Db::name('tx')->insertGetId($res);


            if(!$data_id) return $this->return_msg(400,'缺失参数');

            $info = Db::name('tx')
                ->alias('o')
//                ->join('course c','c.id = o.course_id')
                ->join('member m','m.id = o.member_id')
                ->where('o.id',$data_id)
//                ->field('o.*,c.title,c.description,c.price,m.openid')
                ->find();

            if($admin['money'] < $res['money']) return $this->return_msg(0,'余额不足！');
//                dump(111);
//                dump(111);
            //Db::startTrans();
            try{

                $aa =$this->txFunc($info['openid_app'],$info['order_num'],$res['money']);
//                    dump($aa);
                if(isset($aa['err_code'])){
                    // dump(111);
                    return $this->return_msg(0,$aa['err_code_des']);
                }else{

                    $save = Db::name('admin')->where('id',$auth_id)->update(['money' => 0]);
                    if($save) return $this->return_msg(1,'提现成功！',0);
                }
                // Db::commit();
            }catch (\Exception $e){
                // Db::rollback();
                return $this->return_msg(2,'服务器错误');
            }
        }
//        }
    }

    public function getNumberCode($length = 6){

        $code = '';
        for ($i = 0;$i<intval($length);$i++) $code .= rand(0,9);

        return $code;
    }
    /**
     * 提现
     */
    public function withdrawal(){
//        if($this->checkToken()){
        if(Request()->isPost()){
            $res = $this->request->except('s');//需传用户ID 拿opeenid

            $res['order_num'] = $this->getNumberCode(12);
            $res['createtime'] = time();

            if(Cache::get('str') == $res['str']) return $this->return_msg(0,'请勿重复点击！');
            Cache::set('str',$res['str']);
            unset($res['str']);
//            $data_id = Db::name('tx')->insertGetId($res);
//
//
//            if(!$data_id) return $this->return_msg(400,'缺失参数');

//            $info = Db::name('tx')
//                ->alias('o')
////                ->join('course c','c.id = o.course_id')
//                ->join('member m','m.id = o.member_id')
//                ->where('o.id',$data_id)
////                ->field('o.*,c.title,c.description,c.price,m.openid')
//                ->find();
            $info = Db::name('member')->where('id',$res['member_id'])->find();

            if($info['money'] < $res['money']) return $this->return_msg(0,'余额不足！');
//                dump($info);
//                dump(111);
            //Db::startTrans();
            try{

//                $aa =$this->txFunc($info['openid'],$info['order_num'],$res['money']);
                $aa =$this->txFunc($info['openid'],time(),$res['money']);
//                    dump($aa);
                if(isset($aa['err_code'])){
                    // dump(111);
                    return $this->return_msg(0,$aa['err_code_des']);
                }else{

                    $save = Db::name('member')->where('id',$info['id'])->update(['money' => 0]);
                    if($save) return $this->return_msg(1,'提现成功！',0);
                }
                // Db::commit();
            }catch (\Exception $e){
                // Db::rollback();
                return $this->return_msg(2,'服务器错误');
            }
        }
//        }
    }
    /**
     * 企业支付（向微信发起企业支付到零钱的请求）
     * @param string $openid 用户openID
     * @param string $trade_no 单号
     * @param string $money 金额(单位分)
     * @param string $desc 描述
     * @param string $appid 协会appid
     * @return string   XML 结构的字符串
     **/
    public function txFunc($openid,$trade_no,$money)
    {
        $data = array(
            'mch_appid' =>'wxa3704686d395d189',//协会appid
            'mchid' => '1600262687',//微信支付商户号
            // device_info
            'nonce_str' => $this->getNonceStr(), //随机字符串
            'partner_trade_no' => $trade_no, //商户订单号，需要唯一
            'openid' => $openid,
            'check_name' => 'NO_CHECK', //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
            'amount' => $money * 100, //付款金额单位为分
            'desc' => '提现',
            // 'spbill_create_ip' => '49.235.38.109',
            //'re_user_name' => 'jorsh', //收款人用户姓名 *选填
            //'device_info' => '1000',  //设备号 *选填
        );
        //生成签名
        $data['sign']=$this->makeSign($data);
        //构造XML数据（数据包要以xml格式进行发送）
        $xmldata = $this->arrToXml($data);
        // dump($xmldata);
        //请求url
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        //发送post请求
        $res = $this->curl_post_ssl($url,$xmldata);
        return $res;
    }
    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 签名
     * @param $data
     * @return string
     */
    public function makeSign($data)
    {
        $key="nZnZnZnZnZnZnZnZnZnZnZnZnZnZnZnZ";//商户秘钥
        // 关联排序
        ksort($data);
        // 字典排序
        $str = http_build_query($data);
        // 添加商户密钥
        $str .= '&key=' . $key;
        // 清理空格
        $str = urldecode($str);
        $str = md5($str);
        // 转换大写
        $result = strtoupper($str);
        return $result;
    }

    /**
     * 数组转XML
     * @param $data
     * @return string
     */
    public function arrToXml($data)
    {
        $xml = "<xml>";
        //  遍历组合
        foreach ($data as $k=>$v){
            $xml.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $xml .= '</xml>';
        return $xml;
    }
    /**
     * XML转数组
     * @param string
     * return $data
     * */
    public function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param  [type]  $url     [发送地址]
     * @param  [type]  $xmldata [发送文件格式]
     * @param  [type]  $second [设置执行最长秒数]
     * @param  [type]  $aHeader [设置头部]
     * @return [type]           [description]
     */
    public function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array()){
        $isdir = $_SERVER['DOCUMENT_ROOT']."/commonAssociation/cert/yfls/";//证书位置;绝对路径
        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH.'/extend/cert/apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH.'/extend/cert/apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);//全部数据使用HTTP协议中的"POST"操作来发送


        $data = curl_exec($ch);//执行回话

        if ($data) {
            curl_close($ch);
            return $this->xmlToArray($data);
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}
