<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class Loader_Redis
{
	private static $instance;

	public static function common()
	{
		if(!is_object(self::$instance['common']))
		{
			self::$instance['common'] = new Lib_Redis(Config::$storage['redis']['common']);
		}

		return self::$instance['common'];
	}
}