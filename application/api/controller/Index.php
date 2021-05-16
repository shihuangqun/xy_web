<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use think\Db;
/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
//        $notice = Db::name('notice')->order('createtime desc')->select();
//        $site = Config::get('site');
//        $data = [
//            'notice' => $notice,
//            'bl_title' => $site['bl_title'],
//            'bl_image' => $site['bl_image']
//        ];
//        return $this->return_msg(1,'success',$data);
        $aa = array(
            array(
                'name' => '张三',
                'money' => 1000
            ),
            array(
                'name' => '李四',
                'money' => 600
            )
        );
        foreach ($aa as $k => $v){
            if($v['name'] == '张三'){
                $v['money'] += 500;
            }
            $newArr[] = $v;
        }
        dump($newArr);
    }
    public function jieguo(){
        $notice = Db::name('notice')->order('createtime desc')->select();
        $site = Config::get('site');
        $data = [
            'site' => $site,
            'notice' => $notice,
        ];
        return $this->return_msg(1,'success',$data);
    }

    /**
     * 小程序授权登录
     */
    public function login(){
        if(request()->isPost()){
            $params = input('');

            $openid = $this->getOpenid($params['code']);
//            dump($openid);
            $params['openid'] = $openid['openid'];

            $info = Db::name('member')->where('openid',$params['openid'])->find();

//            ozOem5Hxwl-G5uL05UkPNaf6FCBE

            if(!$info){//未注册
                unset($params['code']);
                $params['createtime'] = time();
                if(!empty($params['ids'])){
                    // Db::name('member')->where('id',$params['ids'])->setInc('money',1);//给上级加1
                    $params['topid'] = $params['ids'];

                }
                unset($params['ids']);
                $id = Db::name('member')->insertGetId($params);

                $info = $params;
//                dump($info);
                $info['id'] = $id;
            }else{
                $info['shareNum'] = Db::name('member')->where('topid',$info['id'])->count();
            }

            if($info) return $this->return_msg(1,'登录成功！',$info);
        }
    }
    // 获取openid
    function getOpenid($code){ // $code为小程序提供
        $appid = 'wxa3704686d395d189'; // 小程序APPID
        $secret = '58fd8d3f9ecbe8522822323d206292e5'; // 小程序secret
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);

        return json_decode($res, true); // 这里是获取到的信息
    }

    //发送post请求
    public function curlPost($url,$data)
    {
        $ch = curl_init();
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = FALSE; //是否返回响应头信息
        $params[CURLOPT_SSL_VERIFYPEER] = false;
        $params[CURLOPT_SSL_VERIFYHOST] = false;
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $data;
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
        curl_close($ch); //关闭连接
        return $content;
    }
    public function hf(){
        $res = Db::name('hf')->order('price asc')->select();
        return $this->return_msg(1,'success',$res);
    }

    public function hf_list(){
        $member_id = input('member_id');
        $res = Db::name('hf_list')->where('member_id',$member_id)->order('createtime desc')->select();
        $data = [];
        foreach ($res as $k => $v){
            $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $data[] = $v;
        }
        return $this->return_msg(1,'success',$data);
    }
    public function xy_list(){
        $member_id = input('member_id');
        $res = Db::name('order')->where('member_id',$member_id)->order('createtime desc')->select();

        $data = [];
        foreach ($res as $k => $v){
            $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $data[] = $v;
        }
        return $this->return_msg(1,'success',$data);
    }
    public function agreement_xy(){

        return $this->return_msg(1,'success',Config::get('site')['agreement_xy']);
    }
    public function agreement_yhk(){

        return $this->return_msg(1,'success',Config::get('site')['agreement_yhk']);
    }
    public function aa(){
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
