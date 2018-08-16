<?php
/**
 * 商户后台菜单列表(菜单根据实际模板设置)
 * @param  string  name 中文名称(每级菜单都有中文名称)
 * @param  string  icon 图标(只有二级菜单有图标)
 * @param  string  link 链接地址(二级菜单,三级菜单才会有, 二级菜单可有可没有)
 * @param  array   sub_menu 下级菜单数组
 * @return array $menu 菜单数组
 * @author 袁中旭
 * @e-mail iron_boy@yeah.net
 * @date   2017-08-15
 */
return [
    /**
     * 系统设置
     */
    0 => [
        'name' => '', 'icon' => '', 'link' => '', 'sub_menu' => [
            0 => [
                "name" => '首页', 'icon' => 'fa fa fa-bar-chart-o', 'link' => 'seller/index/index',
            ],
        ],
    ],
    /**
     * 店铺设置
     */
    1 => [
        'name' => '店铺设置', 'icon' => '', 'link' => '', 'sub_menu' => [
            0 => [
                "name" => '店铺设置', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '店铺设置', 'icon' => '', 'link' => 'seller/ShopSettings/shopSettings',
                    ],
                ],
            ],
            1 => [
                "name" => '物流管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '物流列表', 'icon' => '', 'link' => 'seller/Plugin/logList',
                    ],
                    1 => [
                        'name' => '物流添加', 'icon' => '', 'link' => 'seller/Plugin/logAdd',
                    ],

                ],
            ],
            2 => [
                "name" => '店铺客服', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '店铺客服', 'icon' => '', 'link' => 'seller/ShopSettings/customerService',
                    ],
//                    1 => [
//                        'name' => '店铺投诉', 'icon' => '', 'link' => 'seller/ShopSettings/complain',
//                    ],
                ],
            ],

        ],
    ],
    /*商品管理*/
    2 => [
        'name' => '商品管理', 'icon' => '', 'link' => '', 'sub_menu' => [
            0 => [
                "name" => '商品管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '商品列表', 'icon' => '', 'link' => 'seller/Goods/goodsList',
                    ],
                    // 1 => [
                    //     'name' => '所有品牌列表', 'icon' => '', 'link' => 'seller/Goods/goodsBrandList',
                    // ],
                    2 => [
                        'name' => '店铺品牌列表', 'icon' => '', 'link' => 'seller/Goods/sellerGoodsBrandList',
                    ],
                    3 => [
                        'name' => '商品系列列表', 'icon' => '', 'link' => 'seller/Goods/goodsTypeList',
                    ],                    
                ],
            ],


            // 1 => [
            //     "name" => '商品分类管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
            //         0 => [
            //             'name' => '商品分类列表', 'icon' => '', 'link' => 'seller/Goods/goodsCategoryList',
            //         ],
            //         1 => [
            //             'name' => '商品分类添加', 'icon' => '', 'link' => 'seller/Goods/goodsCategoryAdd',
            //         ],
                    
            //     ],
            // ],

        ],
    ],
    /*订单*/
    3 => [
         'name' => '订单', 'icon' => '', 'link' => '', 'sub_menu' => [
             0 => [
                 "name" => '订单管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                     0 => [
                         'name' => '订单列表', 'icon' => '', 'link' => 'seller/Ordermanage/index',
                     ],
                     1 => [
                         'name' => '发货单', 'icon' => '', 'link' => 'seller/Ordermanage/deliverylist',
                     ],
                     2 => [
                         'name' => '退换货单', 'icon' => '', 'link' => 'seller/Ordermanage/returnlist',
                     ],
                     3 => [
                         'name' => '订单日志', 'icon' => '', 'link' => 'seller/Ordermanage/orderlog',
                     ],
                     4 => [
                        'name' => '用户评论', 'icon' => '', 'link' => 'seller/Ordermanage/goodsEvaluationlist',
                    ],
                 ],
             ]
         ],
     ],
    /*招聘管理*/
     4 => [
         'name' => '招聘管理', 'icon' => '', 'link' => '', 'sub_menu' => [
             0 => [
                 "name" => '招聘管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                     0 => [
                         'name' => '招聘列表', 'icon' => '', 'link' => 'seller/Recruitment/recruitmentList',
                     ],
//                     1 => [
//                         'name' => '招聘添加', 'icon' => '', 'link' => 'seller/Recruitment/recruitmentAdd',
//                     ],
                 ],
             ],
//             1 => [
//                 "name" => '投递管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
//                     0 => [
//                         'name' => '投递简历列表', 'icon' => '', 'link' => 'seller/Recruitment/deliveryResume',
//                     ],
//                 ],
//             ],
         ],
     ],
    /*文章管理*/
//    3 => [
//        'name' => '文章管理', 'icon' => '', 'link' => '', 'sub_menu' => [
//            0 => [
//                "name" => '文章管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
//                    0 => [
//                        'name' => '文章列表', 'icon' => '', 'link' => 'seller/Articles/articlesList',
//                    ],
//                    1 => [
//                        'name' => '文章添加', 'icon' => '', 'link' => 'seller/Articles/articlesAdd',
//                    ],
//                ],
//            ],
//            1 => [
//                "name" => '文章分类管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
//                    0 => [
//                        'name' => '文章分类列表', 'icon' => '', 'link' => 'seller/Articles/articlesCategoryList',
//                    ],
//                    1 => [
//                        'name' => '文章分类添加', 'icon' => '', 'link' => 'seller/Articles/articlesCategoryAdd',
//                    ],
//                ],
//            ],
//            2 => [
//                "name" => '广告管理', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
//                    0 => [
//                        'name' => '广告列表', 'icon' => '', 'link' => 'seller/Advertising/advertisingList',
//                    ],
//                    1 => [
//                        'name' => '广告添加', 'icon' => '', 'link' => 'seller/Advertising/advertisingAdd',
//                    ],
//                ],
//            ],
//
//        ],
//    ],
    /**
     * 管理设置
     */
    5 => [
        'name' => '管理设置', 'icon' => '', 'link' => '', 'sub_menu' => [
            0 => [
                "name" => '管理员', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '管理员列表', 'icon' => '', 'link' => 'seller/Seller/sellerManagerList',
                    ],
                    1 => [
                        'name' => '管理员添加', 'icon' => '', 'link' => 'seller/Seller/sellerManagerAdd',
                    ],
                    // 2 => [
                    //     'name' => '管理员日志', 'icon' => '', 'link' => 'seller/Seller/sellerManagerLogList',
                    // ],
                ],
            ],
            1 => [
                "name" => '权限分组', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
                    0 => [
                        'name' => '分组列表', 'icon' => '', 'link' => 'seller/Seller/sellerPrivilegesGroupList',
                    ],
                    1 => [
                        'name' => '分组添加', 'icon' => '', 'link' => 'seller/Seller/sellerPrivilegesGroupAdd',
                    ],
                ],
            ],
            // 2 => [
            //     "name" => '权限模块', 'icon' => 'fa fa fa-bar-chart-o', 'link' => '', 'sub_menu' => [
            //         0 => [
            //             'name' => '模块列表', 'icon' => '', 'link' => 'seller/Seller/sellerPrivilegesModulesList',
            //         ],
            //         1 => [
            //             'name' => '模块添加', 'icon' => '', 'link' => 'seller/Seller/sellerPrivilegesModulesAdd',
            //         ],
            //     ],
            // ],
        ],
    ],

];
