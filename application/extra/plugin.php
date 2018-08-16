<?php
/**
 * 第三方配置文件
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-29
 */
return [
    /*微信app支付*/
    "wechatPay" => [
        "pay" => [
            "appid" => "wx99a6905fbfdf09c5",
            "mch_id" => "1489110852",
            "notify_url" => "http://".$_SERVER["HTTP_HOST"]."/api/Payment/callback/type/wechatPay/order_type/",
            "key" => "c36d00c2dd3534b99d8efd3dbabad680",
        ],
        "body" => "工建通-微信支付",
        "trade_type" => 'APP',
        "desc" => "微信支付"
    ],
    /*支付宝app支付*/
    "alipay" => [
        "seller_id" => "258363258@qq.com",
        "partner" => "2088721448198806",
        "appid" => "2017072807928980",
        "notify_url" => "http://".$_SERVER["HTTP_HOST"]."/api/Payment/callback/type/alipay/order_type/",
        "rsaPrivateKey" => "MIIEpQIBAAKCAQEA39XIStGAIVuhkcxWVRh4uXwHoGimz+ua/gqGClOHH0H3G7UZwQMRhxm/xHIZib2Bp0MnD97kIV2vuc1dWg7SbgKGZREK2GCighQigI0I7TIKmbLdEzRmbFgZBZwxNVBAdVPsY+2u5ZKOzZOgPNDMlR3ESPVEBrB54b0eA1Sq8mB7lI01Y+b/dnrrWQAiw9R5PrqOcXa/RcPpBFfs/LkTDFGo04BmDj0PwJ5UfdOa9oe08KoszJSD2ScQUuR+Ivp0u5/LGCHjV5YkHCWkEl5W72KdDL94GCwHPqXIR5TFEJra2w1uYiTg2mJ+fAjPZtGDNMhIaeceJjxGT9z2GSEgRQIDAQABAoIBAQCFEwFwUdt+eY62MqjFELZ9eBrEqFM8XBOao0ELlJtJ3xr9kw7LrHpYOtvC/B7owAz8FzV6/wXcPGnD6i9s4lEC7GdgYOB0wgb7lOLqUG8VLeIVfLxotYeLNFkz96ddzed0mb95rY6EmDxkRjdG0NBAeyD+Syr1WygrKBdW3ZiAXfWCbxDJxK7B0lL6DR4IkpwHjuDthvmUS0DbbEyW0JmYaVty0u+GOS3AHW1fss7Tg3LBFvUdBsPId6vQi9aVqexgoS3F8lM+tis3cIF7uQaPECp308Sa1E+Ws3x1bEmzB14YevzAYKK34PF3jm7v9LO4LJRSvBmWiMOkNNoPa40hAoGBAPVbHolIfeODTUX6gBx3vm9B7GaZrN/s0aAYWGB0DkNyoJOddzOYsWM2nXXUUPHTtYCKSYg6iWdKPjsxd7n+XWB3jXnKt+fR5kUkB0Bks/2FwClyh5xHN6ytME9WIioyk6lZFZ7SSf4W9V57Avp5wKKQOd8+kf2cfYu2dccrZrR9AoGBAOmLqAKZnfh8izV5TnNrXPz1Gv/EjR9XO5AUquc64ufgrzzNf9XbFeTxh/CE/cQAAMsN3tMnfjmftqPP7BDKDSLPGk+gSqQICH38r9m9PzYmKLaAajlY/L94AUFaJcwC5n+i9QYAV9wJ3nsSd7mrc0pGYAi8AIFwlbxErpF5rc1pAoGBAN3rfdI0NFFteJ479lZJZIDjQryGcRvU6sIAYydSsXgGHQGHYsLTcFbJlmB2GoSwtbE+40WJlLBNMb5+fslHLhRL7jXjyrWuX7XX4Ys3yqkbqfSTN999dvkDaPfJc3txZae+ANU3ZV+iSmsbnlUJfNhM1Vt8D7YxLkkJTrxIIExNAoGAXhMab0mv32tTrAucNVP3FhIpeQOUkR5TaNtaaGBJDHxuOkDRELT6oclFJ/Z6Phx+NUz2B/ptlwqlyGC1x6GMHwxrnc2Eo4R030MNFtlrgAVo+vLJVyZoUTwmzUth1EcK0Dk1F+DFg2YaT1sGDS1p+G5Wus34KivOtRK5evIXt5kCgYEA8uFunPgiyIrX0aXa7Azw5vB0pUe77VMVzGZkChZ+x5NlCOTHX44yQQtXbXGcaS68LfELYMyVJTnCsDcWzA1yjVUjtLXHz8GNspIIBz0vZztZulu5rNBQwOPap3S5p0aDcIKPkY3nv1C4DyyRJco3lmO0ts2maRdyo62Il1xozEY=",
        "alipayrsaPublicKey" => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA39XIStGAIVuhkcxWVRh4uXwHoGimz+ua/gqGClOHH0H3G7UZwQMRhxm/xHIZib2Bp0MnD97kIV2vuc1dWg7SbgKGZREK2GCighQigI0I7TIKmbLdEzRmbFgZBZwxNVBAdVPsY+2u5ZKOzZOgPNDMlR3ESPVEBrB54b0eA1Sq8mB7lI01Y+b/dnrrWQAiw9R5PrqOcXa/RcPpBFfs/LkTDFGo04BmDj0PwJ5UfdOa9oe08KoszJSD2ScQUuR+Ivp0u5/LGCHjV5YkHCWkEl5W72KdDL94GCwHPqXIR5TFEJra2w1uYiTg2mJ+fAjPZtGDNMhIaeceJjxGT9z2GSEgRQIDAQAB",
        "body" => '工建通-支付宝支付',
        "subject" => '支付宝支付',
        "timeout_express" => '30m',
        "product_code" => 'QUICK_MSECURITY_PAY',
        "desc" => "支付宝支付"
    ],
    /*高德地图*/
    "amap" => [
        "key" => "1ed0e146f50b905596ba0bf75fd17cef",
    ],
    /*短信配置*/
    "sms" => [
        "AppKey" => 'ec3a2fe86af322be00dbf12270321348',
        "AppSecret" => '519e21f65a23',
        "TemplateID" => "3050734",
        "sendCount" => 10, /*验证码单日最大发送次数*/
        "Timeout" => 60, /*验证码发送间隔*/
        // "overtimeYime" => 300,
        "overtimeYime" => 3000,
    ],
    "AppSignKey" => md5("texunkeji"),/*校验签名key*/
    "rc4Key" => md5("texunkeji"),/*rc4key*/
    "userResumeAddCount" => 5,/*用户简历添加长度*/
    "umeng" => [
        "IOSAppKey" => '59a8afc81061d243c6000397',
        "IOSAppSecret" => 'tgwka0goitrpxi8sub9ncpqa3dxtrnxu',
        "AndroidAppKey" => '59a8aef2ae1bf82be70007ca',
        "AndroidAppSecret" => 'jaxddqgwctcbl91jhy5nlfyvz8t34x3o',
        "production_mode" => "true",
        "alias_type" => "GJT"
    ],
];
