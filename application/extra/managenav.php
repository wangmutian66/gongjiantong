<?php
/**
 * 后台菜单列表(菜单根据实际模板设置)
 * @param  string  name 中文名称(每级菜单都有中文名称)
 * @param  string  icon 图标(只有二级菜单有图标)
 * @param  string  link 链接地址(二级菜单,三级菜单才会有, 二级菜单可有可没有)
 * @param  array   sub_menu 下级菜单数组
 * @return array $menu 菜单数组
 * @author 户连超
 * @e-mail zrkjhlc@gmail.com
 * @date   2017-09-11
 */
return [
    /**
     * 系统设置
     */
    [
        'name' => '系统设置', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '首页', 'icon' => 'fa fa-home', 'link' => 'manage/index/index',
            ],
            [
                "name" => '系统管理', 'icon' => 'fa fa-cogs', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '基本设置', 'icon' => '', 'link' => 'manage/System/system',
                    ],
//                    [
//                        'name' => '特讯新闻', 'icon' => '', 'link' => 'manage/System/systemMessageList',
//                    ],
                    [
                        'name' => '消息推送', 'icon' => '', 'link' => 'manage/System/broadcastList',
                    ],
                ],
            ],
            [
                "name" => '广告设置', 'icon' => 'fa fa-file-image-o', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '广告列表', 'icon' => '', 'link' => 'manage/Ad/adList',
                    ],
                    [
                        'name' => '广告定位列表', 'icon' => '', 'link' => 'manage/Ad/adPositionList',
                    ],
                ],
            ],
        ],
    ],

    /*审核*/
    [
        'name' => '审核管理', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '未入驻审核', 'icon' => 'fa fa-info', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '申请入驻', 'icon' => '', 'link' => 'manage/Audit/apply',
                    ],
//                    [
//                        'name' => '入驻审核', 'icon' => '', 'link' => 'manage/Audit/inaudit',
//                    ],
                ],
            ],
            [
                "name" => '已入驻审核', 'icon' => 'fa fa-info', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '商家列表', 'icon' => '', 'link' => 'manage/Audit/merchants',
                    ],
                ],
            ],
        ],
    ],

    /*商品管理*/
    [
        'name' => '商品管理', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '商品管理', 'icon' => 'fa fa-cubes', 'link' => '', 'sub_menu' => [
                   [
                       'name' => '商品列表', 'icon' => '', 'link' => 'manage/Goods/goodsList',
                   ],
                    [
                        'name' => '商品分类', 'icon' => '', 'link' => 'manage/Goods/goodsCategoryList',
                    ],
                    [
                        'name' => '品牌管理', 'icon' => '', 'link' => 'manage/Goods/goodsBrandList',
                    ],
                    // [
                    //     'name' => '用户评论', 'icon' => '', 'link' => 'manage/Goods/goodsEvaluationlist',
                    // ],
                ],
            ],
//            [
//                "name" => '申请管理', 'icon' => 'fa fa-info', 'link' => '', 'sub_menu' => [
//                    [
//                        'name' => '品牌申请', 'icon' => '', 'link' => 'manage/Goods/shopBrandApplicationList',
//                    ],
//                    [
//                        'name' => '商品上架申请', 'icon' => '', 'link' => 'manage/Goods/goodsShowApplyList',
//                    ],
//                ],
//            ],
        ],
    ],
    /**
     * 管理设置
     */
    [
        'name' => '管理设置', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '管理员', 'icon' => 'fa fa-user-plus', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '管理员列表', 'icon' => '', 'link' => 'manage/Manager/managersList',
                    ],
                    [
                        'name' => '管理员添加', 'icon' => '', 'link' => 'manage/Manager/managersAdd',
                    ],
                    [
                        'name' => '管理员日志', 'icon' => '', 'link' => 'manage/Manager/managerLogList',
                    ],
                ],
            ],
            [
                "name" => '权限分组', 'icon' => 'fa fa-group', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '分组列表', 'icon' => '', 'link' => 'manage/Manager/managersPrivilegesGroupList',
                    ],
                    [
                        'name' => '分组添加', 'icon' => '', 'link' => 'manage/Manager/managersPrivilegesGroupAdd',
                    ],
                ],
            ],
            // [
            //     "name" => '权限模块', 'icon' => 'fa fa-server', 'link' => '', 'sub_menu' => [
            //         [
            //             'name' => '模块列表', 'icon' => '', 'link' => 'manage/Manager/managersPrivilegesModulesList',
            //         ],
            //         [
            //             'name' => '模块添加', 'icon' => '', 'link' => 'manage/Manager/ManagersPrivilegesModulesAdd',
            //         ],
            //     ],
            // ],
        ],
    ],
    /*文章管理*/
    [
        'name' => '文章管理', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '文章分类管理', 'icon' => 'fa fa-list-ol', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '文章分类列表', 'icon' => '', 'link' => 'manage/Articles/articlesCategoryList',
                    ],
                    [
                        'name' => '文章分类添加', 'icon' => '', 'link' => 'manage/Articles/articlesCategoryAdd',
                    ],
                ],
            ],
            [
                "name" => '文章管理', 'icon' => 'fa fa-file-text', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '文章列表', 'icon' => '', 'link' => 'manage/Articles/articlesList',
                    ],
                    [
                        'name' => '文章添加', 'icon' => '', 'link' => 'manage/Articles/articlesAdd',
                    ],
                ],
            ],
            [
                "name" => '招标管理', 'icon' => 'fa fa-tags', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '发布信息', 'icon' => '', 'link' => 'manage/Articles/biddingInformationAdd',
                    ],
                    [
                        'name' => '招标列表', 'icon' => '', 'link' => 'manage/Articles/biddingInformationList?bi_type=0',
                    ],
//                    [
//                        'name' => '中标列表', 'icon' => '', 'link' => 'manage/Articles/biddingInformationList?bi_type=1',
//                    ],
//                    [
//                        'name' => '公告列表', 'icon' => '', 'link' => 'manage/Articles/biddingInformationList?bi_type=2',
//                    ],
                ],
            ],
            [
                "name" => '规范管理', 'icon' => 'fa fa-tags', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '规范列表', 'icon' => '', 'link' => 'manage/Articles/engineeringSpecificationsList',
                    ],
//                    [
//                        'name' => '规范添加', 'icon' => '', 'link' => 'manage/Articles/engineeringSpecificationsAdd',
//                    ],
                ],
            ],
        ],
    ],
    /*店铺管理*/
    [
        'name' => '店铺管理', 'icon' => '', 'link' => '', 'sub_menu' => [
//            [
//                "name" => '入住管理', 'icon' => 'fa fa-exchange', 'link' => '', 'sub_menu' => [
//                    [
//                        'name' => '添加入住流程', 'icon' => '', 'link' => 'manage/Shop/checkInProcessAdd',
//                    ],
//                    [
//                        'name' => '入住流程', 'icon' => '', 'link' => 'manage/Shop/checkInProcessList',
//                    ],
//                ],
//            ],
            [
                "name" => '店铺管理', 'icon' => 'fa fa-tasks', 'link' => '', 'sub_menu' => [
                    // [
                    //     'name' => '店铺列表', 'icon' => '', 'link' => 'manage/Shop/shopList',
                    // ],
                    [
                        'name' => '店铺保障', 'icon' => '', 'link' => 'manage/Shop/guaranteeList',
                    ],
                    [
                        'name' => '店铺开户行', 'icon' => '', 'link' => 'manage/Shop/shopBank',
                    ],
                    [
                        'name' => '招聘行业', 'icon' => '', 'link' => 'manage/Users/userIndustryList',
                    ],
                    // [
                        // 'name' => '店铺类目审核', 'icon' => '', 'link' => 'manage/Shop/shopCategoryAudit',
                    // ],
                ],
            ],
        ],
    ],
    /*用户管理*/
    [
        'name' => '用户管理', 'icon' => '', 'link' => '', 'sub_menu' => [
//            [
//                "name" => '行业/技能管理', 'icon' => 'fa fa-mortar-board', 'link' => '', 'sub_menu' => [
//                    [
//                        'name' => '行业列表', 'icon' => '', 'link' => 'manage/Users/userIndustryList',
//                    ],
//                    [
//                        'name' => '行业添加', 'icon' => '', 'link' => 'manage/Users/userIndustryAdd',
//                    ],
//                    [
//                        'name' => '技能列表', 'icon' => '', 'link' => 'manage/Users/userSkillsList',
//                    ],
//                    [
//                        'name' => '技能添加', 'icon' => '', 'link' => 'manage/Users/userSkillsAdd',
//                    ],
//                ],
//            ],
            [
                "name" => '用户管理', 'icon' => 'fa fa-users', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '用户列表', 'icon' => '', 'link' => 'manage/Users/usersList',
                    ],
                    [
                        'name' => '用户添加', 'icon' => '', 'link' => 'manage/Users/usersAdd',
                    ],
                    [
                        'name' => '等级列表', 'icon' => '', 'link' => 'manage/Users/userLevelList',
                    ],
                    [
                        'name' => '等级添加', 'icon' => '', 'link' => 'manage/Users/userLevelAdd',
                    ],
//                    [
//                        'name' => '举报列表', 'icon' => '', 'link' => 'manage/Users/userReportIndfoList',
//                    ],
                ],
            ],
        ],
    ],
    /*在线客服*/
    [
        'name' => '店铺客服', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '店铺客服', 'icon' => 'fa fa-user-secret', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '在线客服', 'icon' => '', 'link' => 'manage/Users/customerService',
                    ],
                    [
                        'name' => '投诉', 'icon' => '', 'link' => 'manage/Users/complain',
                    ],
                    [
                        'name' => '用户反馈', 'icon' => '', 'link' => 'manage/Users/feedback',
                    ],
                ],
            ],
        ],
    ],
    /*版本号*/
    [
        'name' => '版本号', 'icon' => '', 'link' => '', 'sub_menu' => [
            [
                "name" => '版本号', 'icon' => 'fa fa-server', 'link' => '', 'sub_menu' => [
                    [
                        'name' => '版本号', 'icon' => '', 'link' => 'manage/Edition/editionList',
                    ],
                ],
            ],
        ],
    ],
    /*求职/招聘*/
    //  => [
        // 'name' => '求职/招聘', 'icon' => '', 'link' => '', 'sub_menu' => [
            // [
                // "name" => '招聘管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    // [
                        // 'name' => '招聘列表', 'icon' => '', 'link' => 'manage/Shop/checkInProcessAdd',
                    // ],
                    // [
                        // 'name' => '添加招聘', 'icon' => '', 'link' => 'manage/Shop/checkInProcessList',
                    // ],
                // ],
            // ],
            // [
                // "name" => '求职管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    // [
                        // 'name' => '求职列表', 'icon' => '', 'link' => 'manage/Shop/shopList',
                    // ],
                    // [
                        // 'name' => '添加求职', 'icon' => '', 'link' => 'manage/Shop/shopBank',
                    // ],
                // ],
            // ],
        // ],
    // ],

    /*第三方插件 暂时不用*/
    //  => [
        // 'name' => '第三方插件', 'icon' => '', 'link' => '', 'sub_menu' => [
            // [
                // "name" => '支付插件', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    // [
                        // 'name' => '支付插件', 'icon' => '', 'link' => 'manage/Plugin/paymentList',
                    // ],
                // ],
            // ],
            // [
                // "name" => '授权/分享插件', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    // [
                        // 'name' => '授权/分享插件', 'icon' => '', 'link' => 'manage/Plugin/oauthList',
                    // ],
                    // [
                        // 'name' => '授权/分享插件添加', 'icon' => '', 'link' => 'manage/Plugin/oauthAdd',
                    // ],
                // ],
            // ],
            // [
                // "name" => '其他插件', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    // [
                        // 'name' => '其他插件', 'icon' => '', 'link' => 'manage/Plugin/pluginList',
                    // ],
                    // [
                        // 'name' => '其他插件添加', 'icon' => '', 'link' => 'manage/Plugin/pluginAdd',
                    // ],
                // ],
            // ],
            // 
        // ],
    // ],
];
