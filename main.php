<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

SwimAdmin::init($app);

define('S_USER', 'sa_username');
define('S_GROUP', 'sa_group');
session_start();
// Cookie 设置 for session
// $app->add(new \Slim\Middleware\SessionCookie(array(
// 	'expires' => '20 minutes',
// 	'path' => '/admin/',
// 	'domain' => null,
// 	'secure' => false,
// 	'httponly' => false,
// 	'name' => 'swim-session',
// 	'secret' => Config::$admin['sessionSecret'],
// 	'cipher' => MCRYPT_RIJNDAEL_256,
// 	'cipher_mode' => MCRYPT_MODE_CBC
// )));

/*$app->hook('slim.before.router', function () use ($app) {
	
	// 判断是否需要登录
	$path =	$app->request()->getPathInfo();
	var_dump($path);
	if(substr($path, 0, 6) == '/admin' && (!isset($_SESSION[S_USER]) || !$_SESSION[S_USER])) {
		if(!in_array($path, array(
			'test.html',
			//'/admin/upload.html',
			'login.html',
			'logout.html'
		))) {
			$app->redirect(BASE_URL. 'login.html?from='. urlencode($_SERVER['REQUEST_URI']));
		}
	}
});*/

$app->get('/say', function() use($app){
    echo 'hi';
});
// 首页
$app->get('/index.html', function() use($app) {
	$app->redirect(BASE_URL. 'school/teaching.html');
});

// 登录
$app->get('/login.html', function() use($app) {
	echo 'hello';
	/*$app->render('login.html', array(
		'from' => $app->request->get('from')
	));*/
});
// 验证登录
$app->post('/login.html', function() use($app) {
	$admin 		= SwimAdmin::db('admin');
	$rs 		= false;
	$username 	= $app->request->post('username', null);
	$password	= $app->request->post('password', '');
	$data 		= $admin->findOne(array('username' => $username));
	$user 		= SwimAdmin::model('user.main');
	// 管理员首次登录
	if(!$data) {
		// 管理员首次登录默认帐号密码为 admin / admin，并生成管理员记录
		if($username == 'admin' && $password == 'admin') {
			$data = $user->addUser(array(
				'username' 	=> 'admin',
				'password' 	=> $user->password('admin'),
				'name'		=> '管理员',
				'group' 	=> -1
			));
			$rs = true;
		} else {
			$rs = false;
		}
	} else {
		// 密码验证
		$password = $user->password($password);
		if($password == $data['password']) {
			$data['id'] = (string)$data['_id'];
			unset($data['_id']);

			$rs = true;
		} else {
			$rs = false;
		}
	}
	if($rs) {
		$_SESSION[S_USER]  = $data['username'];
		$_SESSION[S_GROUP] = $data['group'];

		$admin->update(
			array('_id' => new MongoId($data['id'])), 
			array('$set' => array(
				'last_login' 	=> time(),
				'last_ip' 		=> SwimAdmin::getRemoteIP()
			))
		);
	}
	$app->view()->renderJSON(array(
		'result' => $rs
	));
});
// 注销
$app->get('/logout.html', function() use($app) {
	session_destroy();
	$app->redirect(BASE_URL. 'login.html');
});

$app->get('/blank.html', function() use($app) {
	
});

// 上传
$app->post('/upload.html', function() use($app) {
	try{
		// require_once(DOC_ROOT. '/src/SimpleImage.php');
		require_once(DOC_ROOT. '/helper/upload.php');

		$name 	= $app->request->post('name', '');
		$type 	= $app->request->post('type', '');
		$type 	= empty($type)? 'default': $type;
		// $image 	= new SimpleImage();
		// $size 	= isset(Config::$imageSize[$type])?
		// 			Config::$imageSize[$type]:
		// 			array(0, 0);

		if(isset($_FILES['upload'])) {
			$len = sizeof($_FILES['upload']['name']);
			$html = array();
			for($i=0; $i<$len; $i++) {
				$pathinfo 	= pathinfo($_FILES['upload']['name'][$i]);
				$ext 		= $pathinfo['extension']? '.'. $pathinfo['extension']: '';
				$base64name = str_replace('/', '', base64_encode($_FILES['upload']['name'][$i]. '-'. rand(10001, 99999)));
                $base64name = str_replace('+', '', $base64name);
                $base64name = str_replace('=', '', $base64name);
				$src 		= date('Ymd'). '/'. $base64name. '-'. date('His'). $ext;
				$file 		= $_FILES['upload']['tmp_name'][$i];
				$fileType 	= strtolower($_FILES['upload']['type'][$i]);

				if($type == 'file') {
					$fileType = 'file';
				} else if(strpos($fileType, 'video') === 0) {
					$fileType = 'video';
				} else {
					$fileType = 'image';
				}
				$src = Upload::put($file, $fileType. '/' .$src, $fileType, $type);
				if($src) {
					if($fileType == 'image') {
						$info   = getimagesize($file);
						$size   = join(',', array(intval($info[0]), intval($info[1])));
						$html[] = join('', array(
							'<div class="thumb">',
								'<img src="image/nothing.gif" style="background-image:url(\''. $src. '\');" />',
								'<a>&times;</a>',
								'<input type="hidden" name="'. $name. '" value="'. $src. '" />',
								'<input type="hidden" name="size[]" value="'. $size. '" />',
							'</div>'
						));
					} else if($fileType == 'file') {
						$html[] = join('', array(
							'<div class="thumb">',
								'<i class="fa fa-file"></i>',
								'<a>&times;</a>',
								'<input type="hidden" name="'. $name. '" value="'. $src. '" />',
							'</div>'
						));
					} else {
						$html[] = join('', array(
							'<div class="thumb">',
								'<video controls="controls" src="'. $src. '"></video>',
								'<a>&times;</a>',
								'<input type="hidden" name="'. $name. '" value="'. $src. '" />',
							'</div>'
						));
					}
				}
			}
			if($html) {
				echo '<div id="upload">';
				echo join('', $html);
				echo '</div>';
			}
		}
	} catch(Exception $e) {
		$app->log->error($e);
	}
});

// 测试
$app->get('/test.html', function() use($app) {
	$app->render('test.html');
});
$app->post('/test.html', function() use($app) {
	
});

// 鲜资讯
$app->group('/home', function () use ($app) {
	require 'home.php';
});

// 教与学
$app->group('/school', function () use ($app) {
	require 'school.php';
});

// 牛装备
$app->group('/goods', function () use ($app) {
	require 'goods.php';
});

// 鲜资讯
$app->group('/news', function () use ($app) {
	require 'news.php';
});

// 附近
$app->group('/lbs', function () use ($app) {
	require 'lbs.php';
});

// 广告
$app->group('/adv', function () use ($app) {
	require 'adv.php';
});

// 泳圈
$app->group('/tweet', function () use ($app) {
	require 'tweet.php';
});

// 举报
$app->group('/report', function () use ($app) {
	require 'report.php';
});

// member
$app->group('/member', function () use ($app) {
	require 'member.php';
});

// member
$app->group('/user', function () use ($app) {
	require 'user.php';
});

// upgrade
$app->group('/upgrade', function () use ($app) {
	require 'upgrade.php';
});

// feedback
$app->group('/feedback', function () use ($app) {
	require 'feedback.php';
});


$app->get('/test.html', function() use($app) {
	$app->render('test.html');
});

// 创建索引
$app->get('/ensureindex.html', function() use($app) {
	$ary = array(
		'admin' 			=> array(array('username' => 1), array('unique' => true)),
		'natatorium' 		=> array(array('coords.coordinates' => '2dsphere')),
		'location_teacher' 	=> array(array('coords.coordinates' => '2dsphere'))
	);
	foreach($ary as $name => $index) {
		SwimAdmin::db($name)->ensureIndex(
			$index[0],
			isset($index[1])? $index[1]: array()
		);
	}
	echo '<pre>';
	print_r($ary);
	echo '</pre>';
	exit;
});

$app->get('/genmongo.html', function() use($app) {
	$collection = 'location_teacher';
	$fields = array(
		'score' 		=> 0
	);
	$action = "db.{$collection}.find().forEach(function(doc) { ";
	foreach($fields as $key => $val) {
		$val = is_numeric($val)? $val: "'{$val}'";
		$action .= "doc.{$key}=doc.{$key}?doc.{$key}:{$val};";
	}
	$action .= "db.{$collection}.save(doc);";
	$action .= "})";
	echo $action;
});

$app->get('/data.html',  function() use($app) {
	$doc = array(
		'express' => '包邮',
		'discount' => 200,
		'original_price' => 1688
	);

    Loader_Mongo::dbmaster()->goods()->update(
    	array(), 
    	array('$set' => $doc),
    	array("multiple" => true)
    );
    var_dump($doc);
});
