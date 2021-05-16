<?php
namespace app\api\controller;

use app\common\controller\Api;
use think\Cache;
use think\Config;
use think\Db;
use think\Loader;
require '../extend/OpenSdk.php';

class personal extends Api{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];


    /**
     * 获取ACCESS_TOKEN
     * @return mixed
     */
    function ACCESS_TOKEN(){
        $appid = 'wxa3704686d395d189'; // 小程序APPID
        $secret = '58fd8d3f9ecbe8522822323d206292e5'; // 小程序secret

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret='.$secret;
        return json_decode($this->curl($url))->access_token;
    }

    public function share(){
        $id = input('id');//绑定上级id
        $Access_token = $this->ACCESS_TOKEN();
//        dump($Access_token);
        $url  = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$Access_token;
        $arr = [
            'scene' => $id,
            'page' => 'pages/index/index'
        ];
//        dump($arr);
        $res = $this->https_request($url,$arr,true);
//        dump($res);
        return $res;

    }




    function https_request($url,$data,$type){
        if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
//            $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
//    public function sign(){
//        $param = input('');
////        $param = [
////            'method' => 'bm.elife.recharge.mobile.getItemInfo',
////            'access_token' => '0d16f87fd744478aa079216e794c10a5',
////            'v' => 1.1,
////            'mobileNo' => 17688866068,
////            'rechargeAmount' => 5
////        ];
//        $url = 'http://api.bm001.com/api';
//
//        $appsecret = '9zFCFKycNGq8wmSIia6Xz4xmOos4yMcp';
//        $param['timestamp'] = date('Y-m-d H:i:s',time());
//        $data = $appsecret.str_replace(array('=', '&'), "", $this->ASCII($param)).$appsecret;
//        $param['sign'] = sha1($data);
//        $res = $this->curl($url,$param);
//        dump($res);
//    }
//
//
//    /**
//     * ASCII排序
//     * @param array $params
//     * @return string
//     */
    public function ASCII($params = array()){
        $params = input('');
        if(!empty($params)){
            $p =  ksort($params);

            if($p){
                $str = '';
                foreach ($params as $k=>$val){
                    $str .= $k .'=' . $val . '&';
                }
                $strs = rtrim($str, '&');
                return $strs;
            }
        }
        return '参数错误';
    }
//
    //查询单个话费直充商品
    public function getItemInfo(){
        $data = $this->sign();
        // 手机号码查询
        $req = new \BmRechargeMobileGetItemInfoRequest;
        $req->setMobileNo("15207251055");
        $req->setRechargeAmount("1");
        $res = $data['client']->execute($req, $data['accessToken']);
        dump($res);
    }
    //充值话费
    public function phoneBill(){
        $param = [
            'mobile' => input('mobile'),
//            'price' => input('price'),
            'amount' => input('amount'),
//            'member_id' => input('member_id'),
//            'createtime' => time(),
            'id' => input('id')
        ];
        $data = $this->sign();
        // 手机号码查询
        $req = new \BmRechargeMobilePayBillRequest;
        $req->setMobileNo($param['mobile']);
        $req->setRechargeAmount($param['amount']);
        $res = $data['client']->execute($req, $data['accessToken']);
//        dump($param);
        unset($param['mobile']);
//        dump($res);
        if(isset($res->payState)){
//            dump($res);
            $param['re_status'] = 1;
            Db::name('hf_list')->update($param);
            return $this->return_msg($res->payState,'success');
        }

        return $this->return_msg(0,'error');

//        if(!isset($res->payState)){
////            dump($res);
//            return $this->return_msg(0,'error');
//        }
//
//        return $this->return_msg($res->payState,'success');
    }
    //查询水电煤类标准商品列表
    public function waterCoal(){
        $data = $this->sign();
        // 手机号码查询
        $req = new \BmDirectRechargeWaterCoalItemListRequest;
        $req->setItemName("江苏");
        $req->setCity("南京");
        $res = $data['client']->execute($req, $data['accessToken']);
        dump($res);
    }
    //查询飞机票标准商品列表
    public function elife(){
        $data = $this->sign();
        // 手机号码查询
        $req = new \AirItemsListRequest;

        $res = $data['client']->execute($req, $data['accessToken']);
        dump($res);
    }
    //交通罚款充值电子单支付订单接口
    public function trafficFine(){
        $data = $this->sign();
        // 手机号码查询
        $req = new \AirItemsListRequest;
        $req = new \BmTrafficFinePayBillRequest;
        $req->setCarNo("苏AJ0111");
        $req->setEngineId("CB653111");
        $req->setDelayFee("0");
        $req->setFineFee("50");
        $req->setFineNo("3211837900553986");
        $req->setItemId("94011");
        $req->setCarType("2");
        $res = $data['client']->execute($req, $data['accessToken']);
        dump($res);
    }

    public function sign(){
        $loader  = new \QmLoader;
        $loader  -> autoload_path  = array(CURRENT_FILE_DIR.DS."client");
        $loader  -> init();
        $loader  -> autoload();
        $client  = new \OpenClient;
        // 你的账号信息
        $client  -> appKey =  "10002101";
        $client  -> appSecret =  "9zFCFKycNGq8wmSIia6Xz4xmOos4yMcp";
        $accessToken  = "0d16f87fd744478aa079216e794c10a5";

        $data = [
            'client' => $client,
            'accessToken' => $accessToken
        ];
        return $data;
    }

    /**
     * 运营商三要素
     */
    public function verifyIdcardNameMobile(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/verifyIdcardNameMobile';

        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);
//        dump($res);
        return $this->return_msg(1,'success',$res);
    }
    /**
     * 信用探针
     */
    public function creditProbe(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/creditProbe';

//        $postdata = array(
//            'idcard'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);
//        dump($res);
        return $this->return_msg(1,'success',$res);
    }
    /**
     * 运营商特征在网时长
     */
    public function getMobileOnlineTime(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/getMobileOnlineTime';

//        $postdata = array(
//            'idcard'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);

        return $this->return_msg(1,'success',$res);
    }
    /**
     * 运营商特征当前状态
     */
    public function getMobileState(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/getMobileState';

//        $postdata = array(
//            'idcard'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);
        return $this->return_msg(1,'success',$res);
    }
    /**
     * 无间探针黑名单
     */
    public function getBlackList(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/getBlackList';

//        $postdata = array(
//            'idcard'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);
        return $this->return_msg(1,'success',$res);
    }
    /**
     * 雨点分查询接口
     */
    public function searchYudianScore(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/zzc/searchYudianScore';

//        $postdata = array(
//            'pid'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');
        $res = $this->creditSign($postdata,$testurl);
        dump($res);
    }
    /**
     * 银行卡交易特征和评分查询
     */
    public function unionpayTransactionVariable(){
        //接口url
        $testurl = 'https://api.wangdunbao.net/api/rycTC/unionpayTransactionVariable';

//        $postdata = array(
//            'pid'=>'421127199605143019',//身份证号码
//            'name'=>'石黄群',//姓名
//            'mobile'=>'17688866068',//
//        );//业务参数
        $postdata = input('');

        $res = $this->creditSign($postdata,$testurl);
//        dump($res);
        return $this->return_msg(1,'success',$res);
    }
    /**
     * 信用签名
     */
    public function creditSign($postdata,$testurl){
        $params = array();
        $params['mercId'] = 'DYZX000083';

        //获取毫秒时间戳
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);


        $params['timestamp'] = strval($msectime);
        $params['nonce'] = strval(rand(100000,999999));
        $params['version'] = '1.0';
        $params['extContent'] ='';
        $params['notifyUrl'] ='';

        $params['bizContent'] =$postdata;
        $prikey = "-----BEGIN PRIVATE KEY-----
MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBANWYsoozKakPWcoh
JN/oheL0inkSLTfhtWJcD7sQ6atAH4B656ZZPQG7LY/BVYsj5xc+gBZeOkFrnQ3Q
+SjWf0BpkwaiN5GJ2KTXd8UMEhkxlKJBHtiUNaYA7axbpKlzWxfOBD+WBMACRokZ
eBAuNSPm5i7iJjbIWUdpFvi3s0Y/AgMBAAECgYEAqRbVmBQCkeQmF55/W4Xun4kp
Kcka5NcYSUJJ7bPo13nOtl4Vjhms8vbjjZ7mglysrzj6GzsC8REo5mwdIpgTaU6Z
LHflfxsSiaZ4LT5rK4PhJoa1GZLNfUyWgn4mqE5WRFFOQ9sqGihSsyxuTygMP3af
hjCnUdUsjNmAB73YXbECQQD3WP+kkEOoNPhUxQ5Njg+gltDZNUCXA4DO2/EBGm8h
lQ4tepZVjsiZCt+wwrPjRAWILtQP/jVpY5YEibwHPs8HAkEA3RF044VzEukpOMRt
/SpYfcnfM/6xEigiCYZFeSgvQG0r2+KtJdhTmkvTBRiQJ/hR/nYoalv7ShNacu5Z
ZKNJCQJAY05QFPn2r+nUafRTsb9/drIWV56RuA+n/2U+dXrvc0Qs1QWKpf8Vepxr
AsSpBG2i6vIiIeml+BILgPbrjt0gsQJBAM/lWsec+FsQanO4RreO2ylwbze2jU9F
7ryGSU9nOwibomNnCO5OQlEYfZqNPwRXwsRK2jcryWYgTS9Id0jtRgECQQC1iRap
n/Z+SimNHtW++34/4d3wvt7LR9MSzF4Z/8IfAgkLhgaDMMNJ3hn/D67+rYV7XYrw
G87QC0Rt3lD1raS6
-----END PRIVATE KEY-----";


        //加签算法
        if (!$prikey || !$postdata || !is_array($postdata)){
            $err = array('res'=>'false','msg'=>'参数为空或缺少');
            echo  json_encode($err);
            exit;
        }
        ksort($postdata);
        $raw = '';
        foreach($postdata as $k=>$v){
            if ($k != 'extContent'){
                if ($v){
                    $raw .= $raw?'&':'';
                    $raw .= ($v !== null && $v !== '')?($k.'='.$v):'';
                }
            }
        }
        $keyid = openssl_pkey_get_private($prikey);
        if (!$keyid){
            $err = array('res'=>'false','msg'=>'加签私钥错误');
            echo  json_encode($err);
            exit;
        }
        if (!openssl_sign($raw, $out, $keyid)){
            $err = array('res'=>'false','msg'=>'加签失败');
            echo  json_encode($err);
            exit;
        }
        openssl_free_key($keyid);
        $params['sign'] =  base64_encode($out);


        // $params['sign'] = $this->service->addSign($postdata,$prikey);
        $data =json_encode($params, JSON_UNESCAPED_UNICODE);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//您可以根据需要，决定是否打开SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        $output = json_decode($output,TRUE);
        if ($output['reqCode'] != '200'){
            header('HTTP/1.1 500 Internal Server Error');
        }
        curl_close($ch);
        return $output;

    }

    public function credit(){
        $param = input('');
        $checkMobile = cache($param['mobile']);
        $creditStatus = Config::get('site')['credit_status'];
        $data = [
            'status' => Config::get('site')['credit_status'],
            'price' => Config::get('site')['credit_price']
        ];
        $sms = new Sms();
//        dump($data);
        $checkSms = $sms->check($param['mobile'],'register',$param['code']);
//        if($checkMobile !== $param['code']) return $this->return_msg(0,'验证码错误，请重新输入！');
        return $this->return_msg(1,'success',$data);
    }
    public function yhk(){
        $param = input('');
        $checkMobile = cache($param['mobile']);
        $creditStatus = Config::get('site')['yhk_status'];
        $data = [
            'status' => Config::get('site')['yhk_status'],
            'price' => Config::get('site')['yhk_price']
        ];

        $sms = new Sms();
        $checkSms = $sms->check($param['mobile'],'register',$param['code']);
//        if($checkMobile !== $param['code']) return $this->return_msg(0,'验证码错误，请重新输入！');
        return $this->return_msg(1,'success',$data);
    }


    public function send(){
        $phone = input('mobile');
        if(Cache::get('str_send') == input('str')) return $this->return_msg(0,'请勿重复点击！');
        Cache::set('str_send',input('str'));
//        unset($res['str']);
        $randStr = str_shuffle('1234567890');
        $rand = substr($randStr,0,6);
        $url='http://120.79.19.104:9008';//系统接口地址
        $content="【艾勒迪达】您的验证码是:".$rand.",5分钟后过期，请您及时验证!";
        $con =iconv("UTF-8", "GBK", $content);
        $cons =urlencode($con);
        $username="13079955373";//用户名
        $password="13079955373";
        $psd = base64_encode($password);//密码百度BASE64加密后密文
        $phone = $phone;
        cache($phone,$rand);

        $url=$url."/servlet/UserServiceAPI?method=sendSMS&extenno=&isLongSms=0&username=".$username."&password=".$psd."&smstype=0&mobile=".$phone."&content=".$cons;
//        echo $url;
        $html = file_get_contents($url);
//        var_dump($html);
        if(!strpos($html,"success")){
            return $this->return_msg(1,'success');
        }else{
            return $this->return_msg(0,'error');
        }
    }

    //获取会员余额
    public function getInfo(){
        $member_id = input('member_id');

        $info = Db::name('member')->where('id',$member_id)->find();
        $info['shareNum'] = Db::name('member')->where('topid',$info['id'])->count();
        return $this->return_msg(1,'success',$info);
    }
    public function getMemberInfo(){
        $member_id = input('member_id');
        $info = Db::name('member')->where('id',$member_id)->find();
        return $this->return_msg(1,'success',$info['is_zx']);
    }
    public function shares(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.kuaihaibao.com/services/screenshot');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer 4116c322-1e9b-4ffe-a0c7-1cb729fca31c',
            'Content-Type: application/json; charset=utf-8',
        ]);
        $json_array = [
            'template' => '3rp98k8v',
            'data' => [
                'cover' => 'https://khb-sample.oss-cn-shanghai.aliyuncs.com/sample/bread.jpg',
                'qrcode' => 'https://khb-sample.oss-cn-shanghai.aliyuncs.com/sample/sample_qr_0.png',
                'title' => '快海报',
                'price' => '¥29.99',
                'tip' => '给你推荐了一个好东西',
                'user' => [
                    'avatar' => 'https://khb-sample.oss-cn-shanghai.aliyuncs.com/sample/girl_1.jpg',
                    'nickname' => '晓阳'
                ]
            ]
        ];
        $body = json_encode($json_array);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($ch);
        if (!$response) {
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
        echo 'HTTP Status Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
        echo 'Response Body: ' . $response . PHP_EOL;
        curl_close($ch);
    }
    


}