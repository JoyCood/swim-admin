<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class Config_Cache_Key
{
	public static function swimmer($userId)
	{
        return "swimmer_{$userId}";
	}

	public static function techer($userId)
	{
		return "techer_{$userId}";
	}

	public static function memberPhotosWall($userId)
	{
		return "wall_{$userId}";
	}

	public static function memberCounter($userId)
	{
		return "memberCounter_{$userId}";
	}

	public static function newMsgCounter($userId)
	{
		return "newMsgCounter_{$userId}";
	}
}