<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

define('AdminTemplatePath', DOC_ROOT . '/view');

class Config
{
    public static $admin = array(
		// 网站标题
		'title' => '趣游泳',
		'listRowsNum' => 30,

		// Slim 配置
		'slim' => array(
			// 是否打开调试
			'debug' 			=> false,
			// 模式设置：
			// 开发模式： “development”
			// 测试模式： “test”
			// 生产环境： “production”
			'mode' 				=> 'production',
			// 模板路径
			'templates.path'	=> AdminTemplatePath
		),

		// SESSION
		'sessionSecret' => '^_^ SwIm',

		// 静态资源版本号，CSS、JS等
		'resVersion' => '2',
		// 第三方静态资源版本号，jQuery, awesome等
		'res3rdVersion' => '3'
	);
	// -- 后台配置结束

    //系统用户帐号
    public static $systemAccount = array(
        '趣游泳小秘书' => '556ebdf6026f97d719fcafa6'
    );

    //获得以下标识的时候发送消息通知用户
    public static $ident2push = array(
        '游泳达人' => '57df906e1cb9fdc77c8b4584'
    );
    
    //任务奖励
    public static $taskReward = array(
        '6' => array(
            'title'        => '资质认证',
            'exp'          => 100, 
            'points'       => 100, 
            'require'      => 6, 
            'type'         => 2,
            'normalExp'    => 0,
            'normalPoints' => 0,
            'times'        => 0,
            'callable'     => false,
            'goto'         => 5, //跳转到个人中心资料祥情
            'action'       => 6,
            'desc'         => '资质认证'
        ), //资质认证

        '7' => array(
            'title'=>'帖子选入精华',
            'exp' => 100,  
            'points' => 100,  
            'require' => 1, 
            'type'=>2, 
            'normalExp' => 0,
            'normalPoints' => 0,
            'times' => 0,
            'callable' => false,
            'goto' => 1,
            'action' => 7,
            'desc'   => '帖子选入精华' 
        ), //帖子被选为精华帖
        
        '8' => array(
            'title'=>'帖子官方置顶',
            'exp' => 500,  
            'points' => 500,  
            'require' => 8, 
            'type'=>2,
            'normalExp' => 0,
            'normalPoints' => 0,
            'times' => 0,
            'callable' => false,
            'goto' => 1,
            'action' => 8,
            'desc' => '帖子置顶'
        )  //帖子被置顶
    );

    //禁言惩罚
    public static $punish = array(
        '1' => array('points' => -30, 'exp'=> -30, 'title'=>'15分钟', 'time'=>900, 'desc'=>'禁言15分钟', 'action'=>100),
        '2' => array('points' => -50, 'exp'=> -50, 'title'=>'一天',   'time'=>86400, 'desc'=>'禁言一天', 'action'=>101),
        '3' => array('points' => 0,   'exp'=> 0,   'title'=>'永久',   'time'=>86400*10000, 'desc'=>'永久禁言', 'action' => 102), 
    );

	public static $imageSize = array(
		'default' 	=> null,			// null 表示不处理
		'banner'	=> null,
		'icon'		=> '152x152',
		'goods-icon'=> null,			// 牛装备封面图标
		'tweet' 	=> array('mid' => '160x160', 'big' => null),
		'avatar'	=> array('small' => '40x40', 'mid' => '160x160', 'big' => '400x400')
	);

	public static $app = array(
        //通信密钥
	    'app_key' => '8ac5a142126a2d413fc57f540091bd3d'
	);
    
    public static $system = array(
		'copyright' => ' © 1999-2014'
	);

    //推送服务器配置
	public static $pusher = array(
        'host' => '127.0.0.1',
        'port' => 7777,
        'secret' => 'e820c512c1a2f9aefbc98d76757dd9e2',
        
        'umeng_android_app_key' => '54a9ea19fd98c5c9c6000a1b',
        'umeng_android_app_master_secret' => 'euc4f3jc7xv5n8yiiudykzqofscpuup1',
        'umeng_ios_app_key' => '55c9c596e0f55a5f1d005327',
        'umeng_ios_app_master_secret' => 'rxjvjkh3xlulafh9rwvthwlnviyg0vif'
	);

	public static $payment = array(
	    'weixin_app_id'          => 'wxd32d25b2a43c0f93',
	    'weixin_app_secret'      => '01c6d59a3f9024db6336662ac95c8e74',
	    'weixin_app_key'         => '343d8b263c2aea31250a6907785bd128',
	    'weixin_mch_id'          => '1312998901',
	    'weixin_sslcert_path'    => '/usr/local/paykey/wxpay/apiclient_cert.pem',
	    'weixin_sslkey_path'     => '/usr/local/paykey/wxpay/apiclient_key.pem',
	    'weixin_curl_proxy_host' => '0.0.0.0',
	    'weixin_curl_proxy_port' => 0,
	    'weixin_report_level'    => 1,
	    'weixin_notify_url'      => 'https://swim.7swim.com/swim/pay/weixin',

	    'alipay_partner'           => '2088021002933404',
	    'alipay_seller_id'         => 'tbkpay@7swim.com',
	    'alipay_notify_url'        => 'https://swim.7swim.com/swim/pay/alipay',
	    'alipay_input_charset'     => 'utf-8',
	    'alipay_payment_type'      => "1",
	    'alipay_sign_type'         => 'RSA',
	    'alipay_private_key_path'  => '/usr/local/paykey/alipay/rsa_private_key.pem',
	    'alipay_public_key_path'   => '/usr/local/paykey/alipay/rsa_public_key.pem',
	    'alipay_cacert_path'       => '/usr/local/paykey/alipay/cacert.pem',
	    'alipay_transport'         => 'https',
	    'alipay_refund_notify_url' => 'https://swim.7swim.com/swim/pay/refund/alipay',

	    'pay_platform' => array('weixin', 'alipay')
	);
    
	public static $storage = array(
		'APIKey' => 'aKeWScZ5JsqDW3ziM9ZCFn1zktI=',
		'image'  => array(
			'host' 		=> 'http://funswim.b0.upaiyun.com',
			'bucket' 	=> 'funswim',
			'user' 		=> 'helloswim',
			'pass' 		=> 'helloswim'
		),
		'media'  => array( // http://funswim-media.b0.upaiyun.com/video-20150326-95538-PJovUR0WQnyGO0fXJKP9hC.mp4
			'host' 		=> 'http://funswim-media.b0.upaiyun.com',
			'bucket' 	=> 'funswim-media',
			'user' 		=> 'helloswim',
			'pass' 		=> 'helloswim'
		),
		'file'   => array(
			'host' 		=> 'http://funswim-media.b0.upaiyun.com',
			'bucket' 	=> 'funswim-media',
			'user' 		=> 'helloswim',
			'pass' 		=> 'helloswim'
		),
        'redis'  => array(
            'common' => array(array('localhost', '6379')),
            'member' => array(array('localhost', '6379'))
        ),

        'mongo'  => array(
            'master' => array('localhost', '27017'),
            'slave'  => array('localhost', '27018')
        )
	); 

    public static $elastic = array(
        'host'  => 'localhost:9200',
        'index' => 'swim'
    );

    public static $goods_coltypes = array(
        'goods'=> array(
            array(
                'id'=>1,
                'title'=>'培训'
            ),
            array(
                'id'=>2,
                'title'=>'商城'
            ),
        )
    );

	public static $db = array(
		'allowed' => array(
			'admin',
			'member',
			'member_tags',
			'member_recommand',
			'location_teacher',
			'member_ident',
			'club',
			'club_service',
			'club_notice',

            'courpon',
            'mall_category',

			'natatorium',
			'natatorium_score',

			'technical',
			'technical_video',
			'technical_category',
            'technical_comment',

			'news',
			'news_category',
            'news_comment',
			'columnist',

			'goods',
			'goods_category',
			'goods_mall',
			'goods_merchant',
			'goods_moov_order',

			'topic',

			'report',

			'group',
			'user',

			'setting',

			'feedback',

			'message',
            'msg_counter',

			'tweet',
			'tweet_group',
			'tweet_comment',
			'tweet_recommand',
            'tweet_top',
			'tweet_tag',
			'tweet_topic',

			'course_order',
			'service_area',
			'course',
			'course_order',
			'course_comment',
			'course_category',
			'course_region',

			'device_tokens',

			'interview',
			'interview_category',
            'interview_tags',

			'match',
			'match_category',
			'match_list',
			'match_member',
            'friend',
            'favorites',
            'task',
            'finance',
            'remit',
            'points_log',
		    'newcoach'
		),
        'redis' => array(
            'host'  => 'localhost',
            'port'  => '6379'
        ),
	);
}
