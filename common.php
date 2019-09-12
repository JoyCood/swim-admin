<?php define('YOUNG_TEAM', TRUE);

define('DEVELOPMENT', TRUE);

define('DOC_ROOT', __DIR__);
define('DOC_DIR', substr(dirname($_SERVER['SCRIPT_NAME']), 0));
define('BASE_URL', DOC_DIR == DIRECTORY_SEPARATOR? '': DOC_DIR);
define('LIB_DIR',  dirname(__FILE__).'/lib/');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

if(!DEVELOPMENT)
{
    ini_set('display_errors', 'Off');
    error_reporting(0);	
}

require('conf/config.php');
require('conf/cache.key.php');
require('conf/statu.code.php');
require('lib/Slim/Slim.php');
require('helper/helper.php');
require('lib/redis/lib.redis.php');
require('src/admin.php');
require('src/redis.php');



