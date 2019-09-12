<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class Config_Statu_Code
{
	public static $code = array(
		'ok_auth_code_send'        => array('code' => 0,    'msg' => '验证码已发送'),
        'ok_register_success'      => array('code' => 0,    'msg' => '注册成功'),
        'ok_login_success'         => array('code' => 0,    'msg' => '登录成功'),
        'ok_get_data_success'      => array('code' => 0,    'msg' => '加载成功'),
        'ok_delete_success'        => array('code' => 0,    'msg' => '已删除'),
        'ok_not_more_data'         => array('code' => 0,    'msg' => '没有更多了'),
        'ok_operation_success'     => array('code' => 0,    'msg' => '操作成功'),
        'ok_post_success'          => array('code' => 0,    'msg' => '发送成功'),
        'ok_follow_success'        => array('code' => 0,    'msg' => '已关注'),
        'ok_not_follow_friend'     => array('code' => 0,    'msg' => '你还没有关注好友'),
        'ok_add_favorite_success'  => array('code' => 0,    'msg' => '已收藏'), 
        'ok_not_follower'          => array('code' => 0,    'msg' => '还没有好友关注你哦'),
        'ok_save_success'          => array('code' => 0,    'msg' => '已保存'),
        'ok_pwd_send'              => array('code' => 0,    'msg' => '密码已发送到您手机'),
        'ok_auth_code_success'     => array('code' => 0,    'msg' => '通过验证'), 

	    'error_member_not_login'     => array('code' => 1000,   'msg' => '请登录应用'),
	    'error_auth_code_expired'    => array('code' => 1001,   'msg' => '验证码已过期'),
	    'error_auth_code_invalid'    => array('code' => 1002,   'msg' => '验证码无效'),
	    'error_phone_invalid'        => array('code' => 1003,   'msg' => '请输入正确的手机号码'),
	    'error_member_exist'         => array('code' => 1004,   'msg' => '用户已经存在'),
	    'error_pwd_invalid'          => array('code' => 1005,   'msg' => '密码错误'),
        'error_pwd_not_init'         => array('code' => 1006,   'msg' => '请输入密码'),
        'error_member_not_exist'     => array('code' => 1007,   'msg' => '用户不存在'),
        'error_tweet_not_exist'      => array('code' => 1008,   'msg' => '内容不存在'),
        'error_delete_failure'       => array('code' => 1009,   'msg' => '删除失败'),
        'error_operation_failure'    => array('code' => 1010,   'msg' => '操作失败'),
        'error_phone_exist'          => array('code' => 1011,   'msg' => '手机号码已被占用'),
        'error_follow_failure'       => array('code' => 1012,   'msg' => '未关注成功，请稍后重试'),
        'error_post_failure'         => array('code' => 1013,   'msg' => '发送失败，请稍后重试'),
        'error_update_location_fast' => array('code' => 1014,   'msg' => '更新太频繁了'),
        'error_invalid_location'     => array('code' => 1015,   'msg' => '请打开地理位置设置项'),
        'error_natatorium_not_exist' => array('code' => 1016,   'msg' => '游泳馆不存在'),
        'error_item_not_exist'       => array('code' => 1017,   'msg' => '内容已删除啦'),
        'error_nothing_to_update'    => array('code' => 1018,   'msg' => '没有内容可更新'),
        'error_update_failure'       => array('code' => 1019,   'msg' => '更新出错啦，请稍后再试'),
        'error_new_pwd_not_match'    => array('code' => 1020,   'msg' => '新密码不一致,请重新输入'),
        'error_pwd_length_wrong'     => array('code' => 1021,   'msg' => '密码长度不对，请输入6-12字母或数字作为密码'),
        'error_basic_not_set'        => array('code' => 1022,   'msg' => '请填写身高、体重、年龄信息'),
        'error_favorites_type_wrong' => array('code' => 1023,   'msg' => '请选择正确的收藏分类'),
        'error_alread_comment'       => array('code' => 1024,   'msg' => '您已经评价过啦'),
        'error_account_is_blocked'   => array('code' => 1025,   'msg' => '您的账号已经被封了，请联系客服'),
	    'error_secret_key_invalid'   => array('code' => 2000,   'msg' => '非法请求')
    );

    public static $admin = array(
        
    );
}