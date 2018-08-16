<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * $smtpserver = "smtp.126.com";//SMTP服务器
*$smtpserverport = 25;//SMTP服务器端口
*$smtpusermail = "txkj667@126.com";//SMTP服务器的用户邮箱
*$smtpemailto = $smtpemailto;//发送给谁
*$smtpuser = "txkj667";//SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
*$smtppass = "txkj667ss1";//SMTP服务器的用户密码
 * 发送邮箱
 * @param type $data 邮箱队列数据 包含邮箱地址 内容
 */
function sendEmail($smtpemailto="824495596@qq.com",$mailcontent="") {
    header("Content-Type: text/html; charset=utf-8");
    //$smtpemailto = "767102673@qq.com,824495596@qq.com";

    $smtpserver = "smtp.163.com";//SMTP服务器
    $smtpserverport = 25;//SMTP服务器端口
    $smtpusermail = "zhb11456@163.com";//SMTP服务器的用户邮箱
    $smtpemailto = $smtpemailto;//发送给谁
    $smtpuser = "zhb11456";//SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
    $smtppass = "texunkeji20180";//SMTP服务器的用户密码

    $mailtitle = '黑龙江特讯科技';//邮件主题
    $params = getRandStr(6, '', 2);
    if($mailcontent==""){
        $mailcontent = "【黑龙江特讯科技】尊敬的工建通用户，您的验证码为：<h1 style='color: red;display: inline-block;'>$params</h1>";//邮件内容
    }


    $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件

    $smtp = new  \smtp\smtp1($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
    $smtp->debug = false;//是否显示发送的调试信息
    $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);

    if($state){
        return ['sl_mobile' => $smtpemailto, 'sl_add_time' => time(), 'sl_code' => $params, 'sl_status' => 1];
    }else{
        return false;
    }
}



/**
 * 模块值 加密方式
 * @author   户连超
 * @Email    zrkjhlc@gmail.com
 * @DateTime 2017-08-14
 * @param    string            $controllerName  = 控制器@方法名
 * @return string
 */
function encryptController($controllerName)
{
    $array = explode("@", $controllerName);
    if (count($array) > 0) {
        $str = strtolower($array[0]) .  strtolower($array[1]);
        return md5($str);
    } else {
        return false;
    }
}
/**
 * 返回16位md5
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-18
 * @param  [type]            $str [description]
 * @return [type]                 [description]
 */
function md5_16($str)
{
    return substr(md5($str), 8, 16);
}
/**
 * rc4公钥加密
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-08
 * @param  [json]             $info     [要加密的字符串]
 * @return [string]           $encrypt  [加密后字符串]
 */
function rc4Encypt($info)
{
    $rc4 = new \rc4\rc4();
    $key = config("plugin")['rc4Key'];
    if (is_array($info)) {
        $str = json_encode($info);
    } else {
        $str = $info;
    }
    return $rc4->Encrypted($key, urlencode($str));
}

/**
 * rc4私钥解密
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-08
 * @param  [string]            $info [要解密的字符串]
 * @return [array]                   [解密后的数组]
 */
function rc4Decrypt($info)
{
    $re4 = new \rc4\rc4();
    $key = config("plugin")['rc4Key'];
    $json = $re4->Decrypted($key, $info);
    if ($json) {
        return json_decode(urldecode($json), true);
    } else {
        return false;
    }
}
/**
 * [ApiReturn description]     短信验证码
 * @author 袁中旭
 * @e-mail iron_boy@yeah.net
 * @date   2017-09-25
 */

function sendSMS($mobile)
{
    $AppKey = config("plugin")['sms']['AppKey'];
    $AppSecret = config("plugin")['sms']['AppSecret'];
    $TemplateID = config("plugin")['sms']['TemplateID'];
    $sms = new \sms\ServerAPI($AppKey, $AppSecret, 'curl');
    $params = getRandStr(6, '', 2);
    $auth_value = $sms->sendSMSTemplate($TemplateID, [$mobile], [$params]);

    if ($auth_value['code'] == 200) {
        return ['sl_mobile' => $mobile, 'sl_add_time' => time(), 'sl_code' => $params, 'sl_status' => 1];
    } else {
        false;
    }
}
/**
 * [ApiReturn description]     生成数据的返回值
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-08
 * @param  string            $data [给客户端返回数据]
 * @param  integer           $code [状态码]
 * @param  string            $msg  [信息]
 * @return [array]           [$array]
 */
function format($data = array(), $code = 200, $msg = "success")
{
    if (isset($data) && is_array($data) && !empty($data)) {
        $info = json_encode($data, JSON_UNESCAPED_UNICODE);
    } else if (isset($data) && is_string($data) && !empty($data)) {
        $info = $data;
    } else {
        $info = "";
    }
    $array = array(
        "code" => $code,
        "msg" => $msg,
        "data" => $info,
    );
    return $array;
}
/**
 * [processData description]    数组中的大写字母转换成小写
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  array             $data [description]
 * @return [type]                  [description]
 */
function processData($data = array())
{
    if (!empty($data)) {

        foreach ($data as $key => $value) {
            $data[$key] = strtolower($value);
        }
        return $data;
    } else {
        return false;
    }
}

/**
 * [encryptPasswd description] 密码加密
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  string            $password [description]
 * @return [type]                      [description]
 */
function encryptPasswd($password = '')
{
    if ('' != $password) {
        return md5($password);
    }
    return '';
}
/**
 * [deepAddslashes description]  数组中的字符串深度转义
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  array             $data [要转义的数组]
 * @return [array]           $data [转义后的数组]
 */
function deepAddslashes($data = array())
{
    if (get_magic_quotes_gpc()) {
        return $data;
    }

    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key] = deepAddslashes($val);
        }
    } else {
        $data = addslashes($data);
    }

    return $data;
}
/**
 * [deepStripslashes description] 反转意数组
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  array             $data [description]
 * @return [type]                  [description]
 */
function deepStripslashes($data = array())
{
    if (get_magic_quotes_gpc()) {
        return $data;
    }

    if (is_array($data)) {
        foreach ($data as $key => $val) {
            $data[$key] = deepStripslashes($val);
        }
    } else {
        $data = stripslashes($data);
    }

    return $data;
}

/**
 * [getIp description] 获取客户端ip
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  integer           $type [返回值类型 ip2long 或者正常ip]
 * @return [type]                  [description]
 */
function getIp($type = 1)
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($ip != '') {
        if ($type == 1) {
            return ip2long($ip);
        }
        return $ip;
    }
}
/**
 * [delFile description] 递归删除文件夹及文件
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  [type]            $dir       [文件路径]
 * @param  string            $file_type [description]
 * @return [bool]
 */
function delFile($dir, $file_type = '')
{
    if (is_dir($dir)) {
        $files = scandir($dir);
        //打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            //正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            //指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    delFile($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
        return true;
    } else {
        if (file_exists($dir)) {
            unlink($dir);
        } else {
            return false;
        }
    }
}

/**
 * [get_rand_str description]  获取随机字符串
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  integer           $randLength    [长度]
 * @param  integer           $addtime       [是否加入当前时间戳]
 * @param  integer           $includenumber [是否包含数字] 0:字母加数字 1:字母 2:数字
 * @return [string]          $tokenvalue    [获取后的字符串]
 */
function getRandStr($randLength = 6, $addtime = 1, $includenumber = 0)
{
    if ($includenumber === 0) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    } elseif ($includenumber === 1) {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    } elseif ($includenumber === 2) {
        $chars = '0123456789';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randLength; $i++) {
        $randStr .= $chars[rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr . time();
    }
    return $tokenvalue;
}
/**
 * [logs description] 记录日志
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-08-16
 * @param  [string]            $path 路径
 * @param   $type 类型（1写普通日志，2写聊天记录）
 * @param  [array || string]            $msg  要记录的信息
 */
function logs($msg, $pathUrl = '')
{
    $path = $_SERVER['DOCUMENT_ROOT'];

    if (!is_dir($path."/log/")) {
        mkdir($path."/log/",0777,true);
    }
    if (is_array($msg)) {
        $info = json_encode($msg, JSON_UNESCAPED_UNICODE);
    } else {
        $info = $msg;
    }
    if ($pathUrl == '') {
        $filename = $path . '/log/' . date('YmdH') . '.txt';
        $content = "------------------" . date("Y-m-d H:i:s") . "------------------" . "\r\n" . $info . "\r\n \r\n";
        file_put_contents($filename, $content, FILE_APPEND);
    } else {
        $filename = $path .'/log/'. $pathUrl;
        if (file_exists($filename)) {
            $tmp_str = file_get_contents($filename);
            $tmp_str_rtrim = rtrim($tmp_str,']');
            $content = $tmp_str_rtrim . "," . $info . ']';
        } else {
            $content = '[' . $info . ']';
        }

        file_put_contents($filename, $content);
    }
}

/**
 * [getArrayDeep description] 获取数组维度 或 验证数组是否是一维数组
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  array             $data [多为数组]
 * @param  integer           $type [0代表的是一维数组]
 * @return [type]                  [description]
 */
function getArrayDeep($data = array(), $type = 0)
{
    if (!is_array($data)) {
        return 0;
    } else {
        if ($type == 0) {
            if (count($array) == count($array, 1)) {
                return true; //一维数组
            } else {
                return false; //非一维数组
            }
        } else {
            //获取数组深度
            $max1 = 0;
            foreach ($data as $item1) {
                $t1 = getArrayDeep($item1);
                if ($t1 > $max1) {
                    $max1 = $t1;
                }
            }
            return $max1 + 1;
        }
    }
}
/**
 * [time2date description]  时间戳转换成时间格式
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  [int]            $time [时间戳]
 * @return [string]         $data [转换格式的时间]
 */
function time2date($time)
{
    if ($time != '') {
        $date = date('Y-m-d H:i:s', $time);
    } else {
        $date = "";
    }
    return $date;
}
/**
 * [time2date description]  时间格式转换成时间戳
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-09
 * @param  [string]         $data [转换格式的时间]
 * @return [int]            $time [时间戳]
 */
function date2time($data)
{
    $time = strtotime($data);
    return $time;
}
/**
 * [uploadImage description]图片上传函数返回上传的基本信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 * @param  [type]            $path [上传路径]
 * @return [type]                  [传入上传路径]
 */
function uploadImage($path, $fileKey = '')
{

    if (empty($fileKey)) {
        $fileKey = key($_FILES);
    }
    $file = request()->file($fileKey);
    if ($file === null) {


//        return array(
//            'l'=>1,
//            'msg' => '上传文件不存在或超过服务器限制',
//            'code' => '201',
//            'data' => '',
//        );
    }else{

        $validate = new \think\Validate([
            [
                'fileExt',
                'fileExt:jpg,jpeg,gif,png,bmp',
                '只允许上传后缀为jpg,gif,png,bmp的文件',
            ],
            [
                'fileSize',
                'fileSize:10485760',
                '文件大小超出限制',
            ],
        ]); // 最大2M
        $data = [
            // 'fileMime' => $file,
            'fileSize' => $file,
            'fileExt' => $file,
        ];
        if (!$validate->check($data)) {
            return array(
                'l'=>2,
                'msg' => $validate->getError(),
                'code' => "201",
                'data' => '',
            );
        }
        $save_path = '.' . getUploadPath() . '/' . $path;
        if(!is_dir($save_path)){
            mkdir($save_path,0777,true);
        }

        $info = $file->rule('uniqid')->move($save_path);

        $imagesize = getimagesize(IMG_URL.$path . '/' . $info->getSaveName());


        if ($info) {
            // 获取基本信息
            $result['ext'] = $info->getExtension();
            $result['pic_cover'] = $path . '/' . $info->getSaveName();
            $result['pic_name'] = $info->getFilename();
            $result['pic_size'] = $info->getSize();
            $result['code'] = 200;
            $result['width'] = $imagesize[0];
            $result['height'] = $imagesize[1];
            $img = \think\Image::open('.' . getUploadPath() . '/' . $result['pic_cover']);
            return $result;
        }
    }
}

/**
 * [uploadImages description]多图片上传函数返回上传的基本信息
 * @author 李鑫
 * @date   2017-09-11
 * @param  [type]            $path [上传路径]
 * @return [type]                  [传入上传路径]
 */
function uploadImages($path, $fileKey = '')
{

    if (empty($fileKey)) {
        $fileKey = key($_FILES);
    }
    $files = request()->file($fileKey);
     if($files != ''){
        $i = 0;
        foreach ($files as $key => $file) {
            if ($file === null) {
                return array(
                    'l'=>1,
                    'msg' => '上传文件不存在或超过服务器限制',
                    'code' => '201',
                    'data' => '',
                );
            }
            $validate = new \think\Validate([
                [
                    'fileExt',
                    'fileExt:jpg,jpeg,gif,png,bmp',
                    '只允许上传后缀为jpg,gif,png,bmp的文件',
                ],
                [
                    'fileSize',
                    'fileSize:90485760',
                    '文件大小超出限制',
                ],
            ]); // 最大2M

            $data = [
                // 'fileMime' => $file,
                'fileSize' => $file,
                'fileExt' => $file,
            ];
            if (!$validate->check($data)) {
                return array(
                    'msg' => $validate->getError(),
                    'code' => "201",
                    'data' => '',
                );
            }
            $save_path = '.' . getUploadPath() . '/' . $path;
            if(!is_dir($save_path)){
                mkdir($save_path,0777,true);
            }
            $info = $file->rule('uniqid')->move($save_path);
            if ($info) {
                // 获取基本信息
                $result['ext'] = $info->getExtension();
                $result['pic_cover'][$i] = $path . '/' . $info->getSaveName();
                $result['pic_name'] = $info->getFilename();
                $result['pic_size'] = $info->getSize();
                $result['code'] = 200;
                $img = \think\Image::open('.' . getUploadPath() . '/' . $result['pic_cover'][$i]);
                $i++;
            }
            
        }
    }else{
        $result = '';
    }

        return $result;
}
/**
 * [uploadImage description]图片上传函数返回上传的基本信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 * @param  [type]            $path [上传路径]
 * @return [type]                  [传入上传路径]
 */
function uploadPdf($path, $fileKey = '')
{
    if (empty($fileKey)) {
        $fileKey = key($_FILES);
    }    
    $file = request()->file($fileKey);
    if ($file === null) {
        return array(
            'msg' => '上传文件不存在或超过服务器限制',
            'code' => '201',
            'data' => '',
        );
    }
    $validate = new \think\Validate([
        [
            'fileExt',
            'fileExt:pdf,PDF',
            '只允许上传后缀为pdf,PDF的文件',
        ],
        [
            'fileSize',
            'fileSize:409657600',
            '文件大小超出限制',
        ],
    ]); // 最大2M
    $data = [
        // 'fileMime' => $file,
        'fileSize' => $file,
        'fileExt' => $file,
    ];
    if (!$validate->check($data)) {
        return array(
            'msg' => $validate->getError(),
            'code' => "201",
            'data' => '',
        );
    }
    $save_path = '.' . getUploadPath() . '/' . $path;
    if(!is_dir($save_path)){
        mkdir($save_path,0777,true);
    }
    $info = $file->rule('uniqid')->move($save_path);
    if ($info) {
        // 获取基本信息
        $result['ext'] = $info->getExtension();
        $result['pdf_cover'] = $path . '/' . $info->getSaveName();
        $result['pdf_name'] = $info->getFilename();
        $result['pdf_size'] = $info->getSize();
        $result['code'] = 200;
        return $result;
    }
}
/**
 * 作者：袁中旭
 * 时间：2017-10-18
 * 功能：多文件上传
 */

function manyUploadImage($paths, $fileKeys = '')
{
    $results = '';
    if (empty($fileKey)) {
        $fileKey = key($_FILES);
    }
    $files = request()->file($fileKeys);
    if($files != ''){
        $i = 0;
        foreach ($files as $key => $file) {
            if ($file === null) {
                return array(
                    'msg' => '上传文件不存在或超过服务器限制',
                    'code' => '201',
                    'data' => '',
                );
            }
            $validate = new \think\Validate([
                [
                    'fileExt',
                    'fileExt:jpg,jpeg,gif,png,bmp',
                    '只允许上传后缀为jpg,jpeg,gif,png,bmp的文件',
                ],
                [
                    'fileSize',
                    'fileSize:10485760',
                    '文件大小超出限制',
                ],
            ]); // 最大2M

            $data = [
                // 'fileMime' => $file,
                'fileSize' => $file,
                'fileExt' => $file,
            ];
            if (!$validate->check($data)) {
                return array(
                    'msg' => $validate->getError(),
                    'code' => "201",
                    'data' => '',
                );
            }
            $save_path = '.' . getUploadPath() . '/' . $paths;
            if(!is_dir($save_path)){
                mkdir($save_path,0777,true);
            }
            $info = $file->rule('uniqid')->move($save_path);
            if ($info) {
                // 获取基本信息
                $result['pic_cover'][$i] = $paths . '/' . $info->getSaveName();
                $result['code'] = 200;
                $i++;
            }
        }
    }else{
        $result = '';
    }
    return $result;

}
/**
 * [getUploadPath description]获取上传根目录
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 * @return [type]            [上传根目录]
 */
function getUploadPath()
{
    $list = \think\config::get("view_replace_str.__UPLOAD__");
    return $list;
}
/**
 * [verify description] 常用的校验及信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-12
 * @param  array             $data [description]
 * @return [type]                  [description]
 */
function verify($data = array(), $rule, $msg)
{
    $validate = new \think\Validate($rule, $msg);
    if (!$validate->check($data)) {
        return array(
            "code" => 0,
            "msg" => $validate->getError(),
        );
    } else {
        return array(
            "code" => 1,
            "msg" => '',
        );
    }
}
/**
 * 多维数组转化为一维数组
 * @param 多维数组
 * @return array 一维数组
 */
function array_multi2single($array)
{
    static $result_array = array();
    foreach ($array as $value) {
        if (is_array($value)) {
            array_multi2single($value);
        } else {
            $result_array[] = $value;
        }

    }
    return $result_array;
}

/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length)
{
    if (mb_strlen($string, 'utf-8') > $length) {
        $str = mb_substr($string, $start, $length, 'utf-8');
        return $str . '...';
    } else {
        return $string;
    }
}
/**
 * 作者：袁中旭
 * 时间：2017-09-18
 * 功能：城市三级联动
 */
function getRegion($where = [])
{
    if (isset($where) && empty($where)) {
        $where = ['r_parent_id' => 1];
    }
    $region = new \model\Region();
    $regions = $region->getList($where);
    return $regions;
}
/**
 * 支付宝支付
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-24
 * @param  [type]            $order_sn     [订单号]
 * @param  [type]            $total_amount [支付金额]
 * @param  [type]            $order_type[0:招投标,1:规范,2:正常商品订单]
 */
function alipay($order_sn, $total_amount, $order_type = 2)
{
    file_put_contents("./public/alipay.txt","到这列！！");
    if ($order_sn != '' && $total_amount > 0) {
        Vendor('alipay.aop.AopClient');
        $content = array(
            'body' => config("plugin")['alipay']['body'],
            'subject' => config("plugin")['alipay']['subject'], /*商品的标题/交易标题/订单标题/订单关键字等*/
            'out_trade_no' => $order_sn, /*商户网站唯一订单号*/
            'timeout_express' => config("plugin")['alipay']['timeout_express'], //该笔订单允许的最晚付款时间
            'total_amount' => floatval($total_amount), //订单总金额(必须定义成浮点型)
            'product_code' => config("plugin")['alipay']['product_code'], //销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        );
        /*$content是biz_content的值,将之转化成字符串*/
        $con = json_encode($content);
        //公共参数
        $param = [];
        $Client = new \AopClient(); //实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
        $param['app_id'] = config("plugin")["alipay"]['appid']; //支付宝分配给开发者的应用ID
        $param['method'] = 'alipay.trade.app.pay'; //接口名称
        $param['charset'] = 'utf-8'; //请求使用的编码格式
        $param['sign_type'] = 'RSA2'; //商户生成签名字符串所使用的签名算法类型
        $param['timestamp'] = date("Y-m-d H:i:s"); //发送请求的时间
        $param['version'] = "1.0"; //调用的接口版本，固定为：1.0
        $param['notify_url'] = config("plugin")["alipay"]['notify_url'] . $order_type; //支付宝服务器主动通知地址
        $param['biz_content'] = $con; //业务请求参数的集合,长度不限,json格式
        /*生成签名*/
        $paramStr = $Client->getSignContent($param);
        $sign = $Client->alonersaSign($paramStr, config("plugin")["alipay"]['rsaPrivateKey'], 'RSA2');
        /*生成最终的请求字符串*/
        $param['sign'] = $sign;

        return rc4Encypt(json_encode($param,JSON_UNESCAPED_UNICODE));
    } else {
        return false;
    }
}


/**
 * [支付宝转账]
 * @author 王牧田
 * @date 2015-05-16
 * @param $payee_account 支付宝账号
 * @param $amount 金额
 * @param $payee_real_name 支付宝真实姓名
 */
function alipaygateway($payee_account,$payee_real_name,$amount){
    Vendor('alipay.aop.SignData');
    Vendor('alipay.aop.AopClient');
    Vendor('alipay.aop.request.AlipayFundTransToaccountTransferRequest');
    $aop = new \AopClient();
    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    $aop->appId =  config("plugin")["alipay"]['appid'];
    $aop->rsaPrivateKey = config("plugin")["alipay"]['rsaPrivateKey'];
    $aop->alipayrsaPublicKey= config("plugin")["alipay"]['alipayrsaPublicKey'];
    $aop->apiVersion = '1.0';
    $aop->signType = 'RSA2';
    $aop->postCharset='UTF-8';
    $aop->format='json';
    $request = new \AlipayFundTransToaccountTransferRequest();
    $nowtime=time();
    $request->setBizContent("{" .
        "\"out_biz_no\":\"".$nowtime."\"," .
        "\"payee_type\":\"ALIPAY_LOGONID\"," .
        "\"payee_account\":\"{$payee_account}\"," .
        "\"amount\":\"{$amount}\"," .
        "\"payer_show_name\":\"工建通转账\"," .
        "\"payee_real_name\":\"{$payee_real_name}\"," .
        "\"remark\":\"转账\"" .
        "}");

    $result = $aop->execute($request);


    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
   
    return $resultCode;
}


/**
 * 微信支付
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-24
 * @param  [type]            $order_sn  [订单号]
 * @param  [type]            $total_fee [支付金额]
 * @param  [type]            $order_type[0:招投标,1:规范,2:正常商品订单]
 * @return [type]
 */
function wechatPay($order_sn, $total_fee, $order_type = 2)
{
    //统一下单方法
    Vendor('wechat.WechatAppPay');
    $config = config("plugin")['wechatPay']['pay'];
    $config['notify_url'] = $config['notify_url'].$order_type;
    $wechatAppPay = new \WechatAppPay($config);

    $params = [
        "body" => config("plugin")["wechatPay"]['body'],
        "out_trade_no" => $order_sn,
        "total_fee" => $total_fee * 100,
        "trade_type" => config("plugin")["wechatPay"]['trade_type'],
    ];
    $result = $wechatAppPay->unifiedOrder($params);
    if ($result['return_code'] == 'FAIL') {
        $result = $wechatAppPay->unifiedOrder($params);
        if ($result['return_code'] == 'FAIL') {
            return false;
        }
    }

    //创建APP端预支付参数
    $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
    if (!$data['partnerid']) {
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        if (!$data['partnerid']) {
            return false;
        }
    }
    $data['order_sns'] = $order_sn;

    return rc4Encypt(json_encode($data,JSON_UNESCAPED_UNICODE));

}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}
/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url, $param, $post_file = false)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
        $is_curlFile = true;
    } else {
        $is_curlFile = false;
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
        }
    }
    if (is_string($param)) {
        $strPOST = $param;
    } elseif ($post_file) {
        if ($is_curlFile) {
            foreach ($param as $key => $val) {
                if (substr($val, 0, 1) == '@') {
                    $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                }
            }
        }
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach ($param as $key => $val) {
            $aPOST[] = $key . "=" . urlencode($val);
        }
        $strPOST = join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}
/**
 * 高德经纬度转换成地址
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-28
 * @param  [type]            $lat_lgt [description]
 * @return [type]                     [description]
 */
function latLgtToLocation($lat_lgt)
{
    $key = config("plugin")['amap']['key'];

    if (empty($key)) {
        return false;
    }
    $url = "http://restapi.amap.com/v3/geocode/regeo?output=json&location=" . $lat_lgt . "&key=" . $key;
    $object = json_decode(http_get($url), true);
    if ($object['status'] != '1' && $object['info'] != "OK") {
        return false;
    }

    $data = [];
    $data['province'] = $object['regeocode']['addressComponent']['province'];
    if ($data['province'] == "北京市") {
        $data['province'] = rtrim($data['province'], "市");
    } else {
        $data['province'] = rtrim($data['province'], "省");
    }
    if (empty($object['regeocode']['addressComponent']['city'])) {
        $data['city'] = $object['regeocode']['addressComponent']['province'];
    } else {
        $data['city'] = $object['regeocode']['addressComponent']['city'];
    }
    $data['city'] = rtrim($data['city'], "市");

    $data['district'] = $object['regeocode']['addressComponent']['district'];
    if ($data['province'] && $data['city'] && $data['district']) {
        return $data;
    } else {
        return '1';
    }
}
/**
 * 获取数据库配置信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-28
 * @param  integer           $type    [类型1总后台配置,其他为商户后台配置]
 * @param  [type]            $prefix  [表字段前缀]
 * @param  integer           $shop_id [店铺id]
 * @return array|bool                 [拼装好的数据]
 */
function getTableConfig($prefix, $type = 1, $shop_id = 0)
{
    if ($type = 1) {
        $conf = new \model\ManageBackgroundConfig();
    } else {
        $conf = new \model\ShopBackgroundConfig();
    }
    $where = [];
    if ($shop_id != 0) {
        $where["shop_id"] = $shop_id;
    }
    $conf_list = $conf->getList($where);
    if (!empty($conf_list)) {
        foreach ($conf_list as $key => $value) {
            $data[$value[$prefix . "inc_type"]][$value[$prefix . "name"]] = $value[$prefix . "value"];
        }
        return $data;
    } else {
        return false;
    }
}
/**
 * 设置数据库配置信息
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-10-28
 * @param  integer           $type    [类型1总后台配置,其他为商户后台配置]
 * @param  [type]            $prefix  [表字段前缀]
 * @param  integer           $shop_id [店铺id]
 */
function setTableConfig($data= [],$prefix, $type = 1, $shop_id = 0)
{
    if ($type = 1) {
        $conf = new \model\ManageBackgroundConfig();
    } else {
        $conf = new \model\ShopBackgroundConfig();
    }
    $save = [];
    if ($shop_id != 0) {
        $save["shop_id"] = $shop_id;
    }
    $conf->truncate();
    foreach ($data as $key => $value) {
        foreach ($value as $k => $val) {
            $save[$prefix . 'inc_type'] = $key;
            $save[$prefix . 'name'] = $k;
            $save[$prefix . 'value'] = $val;
            $conf->save($save);
        }
    }
    return true;
}
/**
 * 生成订单号
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-11-01
 * @param  [type]            $goods_id [商品ID]
 * @param  [type]            $u_id     [用户ID]
 */
function getOrderSn($goods_id,$u_id)
{
    return md5_16($goods_id.$u_id.time().getRandStr(6, 0, 2));
}

/**
 * 检测敏感字
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-03-29
 * @param  [type]            $keyword [检测字段]
 */
function mingan($keyword)
{
$blacklist = '/阿扁推翻|阿宾|阿賓|挨了一炮|爱液横流|安街逆|安局办公楼|安局豪华|安门事|安眠藥|案的准确|八九民|八九学|八九政治|
把病人整|把邓小平|把学生整|罢工门|白黄牙签|败培训|办本科|办理本科|办理各种|办理票据|办理文凭|办理真实|办理证书|
办理资格|办文凭|办怔|办证|半刺刀|辦毕业|辦證|谤罪获刑|磅解码器|磅遥控器|宝在甘肃修|保过答案|报复执法|爆发骚|
北省委门|被打死|被指抄袭|被中共|本公司担|本无码|毕业證|变牌绝|辩词与梦|冰毒|冰火毒|冰火佳|冰火九重|冰火漫|
冰淫传|冰在火上|波推龙|博彩娱|博会暂停|博园区伪|不查都|不查全|不思四化|布卖淫女|部忙组阁|部是这样|才知道只生|
财众科技|采花堂|踩踏事|苍山兰|苍蝇水|藏春阁|藏獨|操了嫂|操嫂子|策没有不|插屁屁|察象蚂|拆迁灭|车牌隐|成人电|
成人卡通|成人聊|成人片|成人视|成人图|成人文|成人小|城管灭|惩公安|惩贪难|充气娃|冲凉死|抽着大中|抽着芙蓉|
出成绩付|出售发票|出售军|穿透仪器|春水横溢|纯度白|纯度黄|次通过考|催眠水|催情粉|催情药|催情藥|挫仑|达毕业证|
答案包|答案提供|打标语|打错门|打飞机专|打死经过|打死人|打砸办公|大鸡巴|大雞巴|大纪元|大揭露|大奶子|大批贪官|
大肉棒|大嘴歌|代办发票|代办各|代办文|代办学|代办制|代辦|代表烦|代開|代考|代理发票|代理票据|代您考|代您考|
代写毕|代写论|代孕|贷办|贷借款|贷开|戴海静|当代七整|当官要精|当官在于|党的官|党后萎|党前干劲|刀架保安|
导的情人|导叫失|导人的最|导人最|导小商|到花心|得财兼|的同修|灯草和|等级證|等屁民|等人老百|等人是老|等人手术|
邓爷爷转|邓玉娇|地产之歌|地下先烈|地震哥|帝国之梦|递纸死|点数优惠|电狗|电话监|电鸡|甸果敢|蝶舞按|丁香社|
丁子霖|顶花心|东北独立|东复活|东京热|東京熱|洞小口紧|都当警|都当小姐|都进中央|毒蛇钻|独立台湾|赌球网|短信截|
对日强硬|多美康|躲猫猫|俄羅斯|恶势力操|恶势力插|恩氟烷|儿园惨|儿园砍|儿园杀|儿园凶|二奶大|发牌绝|发票出|
发票代|发票销|發票|法车仑|法伦功|法轮|法轮佛|法维权|法一轮|法院给废|法正乾|反测速雷|反雷达测|反屏蔽|范燕琼|
方迷香|防电子眼|防身药水|房贷给废|仿真枪|仿真证|诽谤罪|费私服|封锁消|佛同修|夫妻交换|福尔马林|福娃的預|
福娃頭上|福香巴|府包庇|府集中领|妇销魂|附送枪|复印件生|复印件制|富民穷|富婆给废|改号软件|感扑克|冈本真|肛交|
肛门是邻|岡本真|钢针狗|钢珠枪|港澳博球|港馬會|港鑫華|高就在政|高考黑|高莺莺|搞媛交|告长期|告洋状|格证考试|
各类考试|各类文凭|跟踪器|工程吞得|工力人|公安错打|公安网监|公开小姐|攻官小姐|共狗|共王储|狗粮|狗屁专家|
鼓动一些|乖乖粉|官商勾|官也不容|官因发帖|光学真题|跪真相|滚圆大乳|国际投注|国家妓|国家软弱|国家吞得|国库折|
国一九五七|國內美|哈药直销|海访民|豪圈钱|号屏蔽器|和狗交|和狗性|和狗做|黑火药的|红色恐怖|红外透视|紅色恐|
胡江内斗|胡紧套|胡錦濤|胡适眼|胡耀邦|湖淫娘|虎头猎|华国锋|华门开|化学扫盲|划老公|还会吹萧|还看锦涛|环球证件|
换妻|皇冠投注|黄冰|浑圆豪乳|活不起|火车也疯|机定位器|机号定|机号卫|机卡密|机屏蔽器|基本靠吼|绩过后付|激情电|
激情短|激情妹|激情炮|级办理|级答案|急需嫖|集体打砸|集体腐|挤乳汁|擠乳汁|佳静安定|家一样饱|家属被打|甲虫跳|
甲流了|奸成瘾|兼职上门|监听器|监听王|简易炸|江胡内斗|江太上|江系人|江贼民|疆獨|蒋彦永|叫自慰|揭贪难|姐包夜|
姐服务|姐兼职|姐上门|金扎金|金钟气|津大地震|津地震|进来的罪|京地震|京要地震|经典谎言|精子射在|警察被|警察的幌|
警察殴打|警察说保|警车雷达|警方包庇|警用品|径步枪|敬请忍|究生答案|九龙论坛|九评共|酒象喝汤|酒像喝汤|就爱插|
就要色|举国体|巨乳|据说全民|绝食声|军长发威|军刺|军品特|军用手|开邓选|开锁工具|開碼|開票|砍杀幼|砍伤儿|
康没有不|康跳楼|考答案|考后付款|考机构|考考邓|考联盟|考前答|考前答案|考前付|考设备|考试包过|考试保|考试答案|
考试机构|考试联盟|考试枪|考研考中|考中答案|磕彰|克分析|克千术|克透视|空和雅典|孔摄像|控诉世博|控制媒|口手枪|
骷髅死|快速办|矿难不公|拉登说|拉开水晶|来福猎|拦截器|狼全部跪|浪穴|老虎机|雷人女官|类准确答|黎阳平|李洪志|
李咏曰|理各种证|理是影帝|理证件|理做帐报|力骗中央|力月西|丽媛离|利他林|连发手|聯繫電|炼大法|两岸才子|两会代|
两会又三|聊视频|聊斋艳|了件渔袍|猎好帮手|猎枪销|猎槍|獵槍|领土拿|流血事|六合彩|六死|六四事|六月联盟|龙湾事件|
隆手指|陆封锁|陆同修|氯胺酮|乱奸|乱伦类|乱伦小|亂倫|伦理大|伦理电影|伦理毛|伦理片|轮功|轮手枪|论文代|罗斯小姐|
裸聊网|裸舞视|落霞缀|麻古|麻果配|麻果丸|麻将透|麻醉狗|麻醉枪|麻醉槍|麻醉藥|蟆叫专家|卖地财政|卖发票|卖银行卡|
卖自考|漫步丝|忙爱国|猫眼工具|毛一鲜|媒体封锁|每周一死|美艳少妇|妹按摩|妹上门|门按摩|门保健|門服務|氓培训|
蒙汗药|迷幻型|迷幻药|迷幻藥|迷昏口|迷昏药|迷昏藥|迷魂香|迷魂药|迷魂藥|迷奸药|迷情水|迷情药|迷藥|谜奸药|蜜穴|
灭绝罪|民储害|民九亿商|民抗议|明慧网|铭记印尼|摩小姐|母乳家|木齐针|幕没有不|幕前戲|内射|南充针|嫩穴|嫩阴|
泥马之歌|你的西域|拟涛哥|娘两腿之间|妞上门|浓精|怒的志愿|女被人家搞|女激情|女技师|女人和狗|女任职名|女上门|
女優|鸥之歌|拍肩神药|拍肩型|牌分析|牌技网|炮的小蜜|陪考枪|配有消|喷尿|嫖俄罗|嫖鸡|平惨案|平叫到床|仆不怕饮|
普通嘌|期货配|奇迹的黄|奇淫散|骑单车出|气狗|气枪|汽狗|汽枪|氣槍|铅弹|钱三字经|枪出售|枪的参|枪的分|枪的结|
枪的制|枪货到|枪决女犯|枪决现场|枪模|枪手队|枪手网|枪销售|枪械制|枪子弹|强权政府|强硬发言|抢其火炬|切听器|
窃听器|禽流感了|勤捞致|氢弹手|清除负面|清純壆|情聊天室|情妹妹|情视频|情自拍|氰化钾|氰化钠|请集会|请示威|请愿|
琼花问|区的雷人|娶韩国|全真证|群奸暴|群起抗暴|群体性事|绕过封锁|惹的国|人权律|人体艺|人游行|人在云上|人真钱|
认牌绝|任于斯国|柔胸粉|肉洞|肉棍|如厕死|乳交|软弱的国|赛后骚|三挫|三级片|三秒倒|三网友|三唑|骚妇|骚浪|骚穴|
骚嘴|扫了爷爷|色电影|色妹妹|色视频|色小说|杀指南|山涉黑|煽动不明|煽动群众|上门激|烧公安局|烧瓶的|韶关斗|
韶关玩|韶关旭|射网枪|涉嫌抄袭|深喉冰|神七假|神韵艺术|生被砍|生踩踏|生肖中特|圣战不息|盛行在舞|尸博|失身水|
失意药|狮子旗|十八等|十大谎|十大禁|十个预言|十类人不|十七大幕|实毕业证|实体娃|实学历文|士康事件|式粉推|视解密|
是躲猫|手变牌|手答案|手狗|手机跟|手机监|手机窃|手机追|手拉鸡|手木仓|手槍|守所死法|兽交|售步枪|售纯度|售单管|
售弹簧刀|售防身|售狗子|售虎头|售火药|售假币|售健卫|售军用|售猎枪|售氯胺|售麻醉|售冒名|售枪支|售热武|售三棱|
售手枪|售五四|售信用|售一元硬|售子弹|售左轮|书办理|熟妇|术牌具|双管立|双管平|水阎王|丝护士|丝情侣|丝袜保|
丝袜恋|丝袜美|丝袜妹|丝袜网|丝足按|司长期有|司法黑|私房写真|死法分布|死要见毛|四博会|四大扯 四小码|苏家屯集|
诉讼集团|素女心|速代办|速取证|酸羟亚胺|蹋纳税|太王四神|泰兴幼|泰兴镇中|泰州幼|贪官也辛|探测狗|涛共产|涛一样胡|
特工资|特码|特上门|体透视镜|替考|替人体|天朝特|天鹅之旅|天推广歌|田罢工|田田桑|田停工|庭保养|庭审直播|
通钢总经|偷電器|偷肃贪|偷听器|偷偷贪|头双管|透视功能|透视镜|透视扑|透视器|透视眼镜|透视药|透视仪|秃鹰汽|
突破封锁|突破网路|推油按|脱衣艳|瓦斯手|袜按摩|外透视镜|外围赌球|湾版假|万能钥匙|万人骚动|王立军|王益案|网民案|
网民获刑|网民诬|微型摄像|围攻警|围攻上海|维汉员|维权基|维权人|维权谈|委坐船|谓的和谐|温家堡|温切斯特|温影帝|
溫家寶|瘟加饱|瘟假饱|文凭证|文强|纹了毛|闻被控制|闻封锁|瓮安|我的西域|我搞台独|乌蝇水|无耻语录|无码专|五套功|
五月天|午夜电|午夜极|武警暴|武警殴|武警已增|务员答案|务员考试|雾型迷|西藏限|西服进去|希脏|习进平|习晋平|
席复活|席临终前|席指着护|洗澡死|喜贪赃|先烈纷纷|现大地震|现金投注|线透视镜|限制言|陷害案|陷害罪|相自首|
香港论坛|香港马会|香港一类|香港总彩|硝化甘|小穴|校骚乱|协晃悠|写两会|泄漏的内|新建户|新疆叛|新疆限|新金瓶|
新唐人|信访专班|信接收器|兴中心幼|星上门|行长王益|形透视镜|型手枪|姓忽悠|幸运码|性爱日|性福情|性感少|性推广歌|
胸主席|徐玉元|学骚乱|学位證|學生妹|丫与王益|烟感器|严晓玲|言被劳教|言论罪|盐酸曲|颜射|恙虫病|姚明进去|要人权|
要射精了|要射了|要泄了|夜激情|液体炸|一小撮别|遗情书|蚁力神|益关注组|益受贿|阴间来电|陰唇|陰道|陰戶|淫魔舞|
淫情女|淫肉|淫騷妹|淫兽|淫兽学|淫水|淫穴|隐形耳|隐形喷剂|应子弹|婴儿命|咏妓|用手枪|幽谷三|游精佑|有奶不一|
右转是政|幼齿类|娱乐透视|愚民同|愚民政|与狗性|玉蒲团|育部女官|冤民大|鸳鸯洗|园惨案|园发生砍|园砍杀|园凶杀|
园血案|原一九五七|原装弹|袁腾飞|晕倒型|韵徐娘|遭便衣|遭到警|遭警察|遭武警|择油录|曾道人|炸弹教|炸弹遥控|炸广州|
炸立交|炸药的制|炸药配|炸药制|张春桥|找枪手|找援交|找政法委副|赵紫阳|针刺案|针刺伤|针刺事|针刺死|侦探设备|
真钱斗地|真钱投注 真善忍|真实文凭|真实资格|震惊一个民|震其国土|证到付款|证件办|证件集团|证生成器|证书办|
证一次性|政府操|政论区|證件|植物冰|殖器护|指纹考勤|指纹膜|指纹套|至国家高|志不愿跟|制服诱|制手枪|制证定金|
制作证件|中的班禅|中共黑|中国不强|种公务员|种学历证|众像羔|州惨案|州大批贪|州三箭|宙最高法|昼将近|主席忏|
住英国房|助考|助考网|专业办理|专业代|专业代写|专业助|转是政府|赚钱资料|装弹甲|装枪套|装消音|着护士的胸|着涛哥|
姿不对死|资格證|资料泄|梓健特药|字牌汽|自己找枪|自慰用|自由圣|自由亚|总会美女|足球玩法|最牛公安|醉钢枪|醉迷药|
醉乙醚|尊爵粉|左转是政|作弊器|作各种证|作硝化甘|唑仑|做爱小|做原子弹|做证件|习近平/i';
    if(preg_match($blacklist, $keyword)){
       return true;
    }
}


/**
 * 地址显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function dizhi($where)
{
    $where = ['r_id' => $where];
    $region = new \model\Region();
    $regions = $region->getOne($where,'r_name');
    return $regions;
}
/**
 * 地址显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function sgtname($where)
{
    $where = ['sgt_id' => $where];
    $region = new \model\Shopgoodstype();
    $regions = $region->getOne($where,'sgt_name');
    return $regions;
}
/**
 * 用户名称
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function yonghu($where)
{
    $where = ['u_id' => $where];
    $region = new \model\Users();
    $regions = $region->getOne($where,'u_name');
    return $regions;
}

/**
 * 地址id
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function dizhis($where)
{
    $where = ['r_name' => $where];
    $region = new \model\Region();
    $regions = $region->getOne($where,'r_id');
    return $regions;
}
/**
 * 资质显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function zizhi($where)
{
    $where = ['ss_id' => $where];
    $region = new \model\SellerShop();
    $regions = $region->getOne($where,'nature');
    return $regions;
}

/**
 * 资质是否显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function zizhis($where)
{
    $where = ['ss_id' => $where];
    $region = new \model\SellerShop();
    $regions = $region->getOne($where,'admin_nature');
    return $regions;
}

/**
 * 卖家保障
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function baozhang($where)
{
    $where = ['ss_id' => $where];
    $region = new \model\SellerShop();
    $regions = $region->getOne($where,'guarantes');
    return $regions;
}
/**
 * 地址显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function hangye($where)
{
    $where = ['ui_id' => $where];
    $region = new \model\UserIndustry();
    $regions = $region->getOne($where,'ui_name');
    return $regions;
}
/**
 * 分类显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function fenlei($where)
{
    $where = ['mgc_id' => $where];
    $region = new \model\ManageGoodsCategory();
    $regions = $region->getOne($where,'mgc_name');
    return $regions;
}
/**
 * 规格显示
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-24
 * @param  [type]            $keyword [检测字段]
 */
function guige($where)
{
    $where = ['id' => $where];
    $region = new \model\Specifications();
    $regions = $region->getOne($where,'sp_name');
    return $regions;
}

/**
 * 筛选
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-04-27
 * @param  [type]            $keyword [检测字段]
 */
function shaixuan($where,$field)
{
    $region = new \model\SellerShop();
    $regions = $region->setfind($where,$field);
    // return $region->getlastsql();
    return $regions;
}


/**
 * 商品筛选
 * @author 李鑫
 * @e-mail 83795552@qq.com
 * @date   2018-05-25
 * @param  [type]            $keyword [检测字段]
 */
function shaixuangoods($where,$field)
{
    $regione = new \model\Goods();
    $regions = $regione->setfinds($where,$field);
    // return $regione->getlastsql();
    return $regions;
}


/**
 * [活动折扣价格]
 * @param $g_id 商品id
 * @param $sp_id 规格id
 * @return mixed
 * @author 王牧田
 * @date 2018-04-25
 */
function getActivityPrice($g_id,$sp_id){
    $act = new \model\Activity();
    $gsp = new \model\GoodsSpecifications();
    $actfind = $act->getRow("find_in_set('".$g_id."_".$sp_id."',g_ids) and UNIX_TIMESTAMP(now()) > act_start_time and UNIX_TIMESTAMP(now()) < act_end_time and act_isstop = 0");
    $gsplist1 =  $gsp->getRow(["g_id"=>$g_id,"sp_id"=>$sp_id],"gsp_price");

    $gsplist=array();
    /*活动类型*/
    $gsplist["act_type"] = 0;
    /*满*/
    $gsplist["act_meet"] = 0;
    /*减*/
    $gsplist["act_reduction"] = 0;
    /*活动id*/
    $gsplist["act_id"] = 0;
    /*折扣价格*/
    $gsplist["g_discount_price"] = $gsplist["gsp_price"] = empty($gsplist["gsp_price"])?0:$gsplist["gsp_price"];
    if(!empty($actfind)) {

        $gsplist["act_id"] = $actfind["act_id"];
      
        if($actfind["act_type"] == 1){

            $gsplist["act_type"] = 1;
            /*折扣价格*/

            $act_discount = unserialize($actfind["act_discount"]);
            if($act_discount){
                $act_key=find_array_key($g_id,$sp_id,$act_discount);
                $gsplist["g_discount_price"]=$act_discount[$act_key]["g_discount_price"];
            }

        }else{
            /*满减*/

            if($gsplist1["gsp_price"]>=$actfind["act_meet"]){
                $gsplist["g_discount_price"] = number_format(($gsplist1["gsp_price"]-$actfind["act_reduction"]),2);
            }

            $gsplist["act_type"] = 2;
            $gsplist["act_meet"] = $actfind["act_meet"];
            $gsplist["act_reduction"] = $actfind["act_reduction"];

        }

    }

    return $gsplist;
}


/**
 * [计算物流费用]
 * @param $log_id 物流公司id
 * @param $usa_id 用户id
 * @param $weight 重量
 * @date 2018-04-24
 * @author wangmutian
 */
function getWeightMoney($log_id,$usa_id,$weight){

    $logc = new model\Pluginconfig();
    $usa = new \model\UserShippingAddress();

    /*获取当前物流公司下所有可以管辖的城城市*/
    $logclist = $logc->getOnes(["log_id"=>$log_id],"logc_area");
    $logcRowa = $logc->getRow(["log_id"=>$log_id,"logc_national"=>1]);
    /*当前用户地址的省市区*/
    $address =  $usa->getRow(["usa_id"=>$usa_id],"usa_province,usa_city,usa_district"); //12

    if(empty($logclist)){
        return 0;
    }

    foreach ($logclist as $a){

        if(empty($address)){
            continue;
        }

        $unionarray=array_intersect(explode(",",$a),$address);

        if(!empty($unionarray)){

            $logcRow = $logc->getRow(['log_id'=>$log_id,"logc_area"=>$a],"logc_id,logc_heavy,logc_cmoney,logc_contheavys,logc_contcmoneys");

            if($weight <= $logcRow["logc_heavy"]){

                return $logcRow["logc_cmoney"];
            }

            return ceil(($weight - $logcRow["logc_heavy"])/$logcRow["logc_contheavys"])*$logcRow["logc_contcmoneys"]+$logcRow["logc_cmoney"];
        }
    }

    //全国费用
    if(!empty($logcRowa)){
        if($weight <= $logcRowa["logc_heavy"]){
            return $logcRowa["logc_cmoney"];
        }
        return ceil(($weight - $logcRowa["logc_heavy"])/$logcRowa["logc_contheavys"])*$logcRowa["logc_contcmoneys"]+$logcRowa["logc_cmoney"];
    }


}

function find_array_key($value1,$value2,$array) {

    foreach($array as $k=>$item) {
        if ($item["g_id"] == $value1 && $item["sp_id"] == $value2) {
            return $k;
        } else {
            continue;
        }

    }
    return -1;
}


//二维数组 排序
function arr_sort($array,$key,$order="asc"){//asc是升序 desc是降序
    $arr_nums=$arr=array();
    foreach($array as $k=>$v){
        $arr_nums[$k]=$v[$key];
    }

    if($order=='asc'){
        asort($arr_nums);
    }else{
        arsort($arr_nums);
    }

    $i=0;
    foreach($arr_nums as $k=>$v){
        $arr[$i]=$array[$k];
        $i++;
    }

    return $arr;
}
/**
 * 求两个已知经纬度之间的距离,单位为米
 * 
 * @param lng1 $ ,lng2 经度
 * @param lat1 $ ,lat2 纬度
 * @return float 距离，单位米
 */
function getdistance($lng1, $lat1, $lng2, $lat2) {
    // 将角度转为狐度
    $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
    return $s;
} 
/**
 *
 * @param $key
 * @param $value
 * @param $returnkey 返回key
 * @param $array 数组
 * @return bool
 */
function in_array_findkey($key,$value,$returnkey, $array) {
    foreach($array as $k=>$item) {
        if($item[$key]==$value){
            return $item[$returnkey];
        }
    }
    return false;
}


function separatePushAndroid($url,$uid,$title,$text,$json=""){
    $production_mode = config('plugin')['umeng']['production_mode'];
    $IOSAppKey = config('plugin')['umeng']['IOSAppKey'];
    $IOSAppSecret = config('plugin')['umeng']['IOSAppSecret'];
    $AndroidAppKey = config('plugin')['umeng']['AndroidAppKey'];
    $AndroidAppSecret = config('plugin')['umeng']['AndroidAppSecret'];
    $alias_type = config('plugin')['umeng']['alias_type'];
    Vendor('umeng.Umeng');
    $um = new \Umeng($AndroidAppKey,$AndroidAppSecret,$production_mode);
    $ticker = "工建通-信息";
//    $title = "您的订单已发货";
//    $text = "";
//    $url = "order_".$id;
    return $um->sendAndroidCustomizedcast($ticker, $title, $text, $url,$uid,$alias_type,$json);

}