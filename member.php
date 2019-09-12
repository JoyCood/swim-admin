<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');
require_once LIB_DIR . 'PHPExcel/Classes/PHPExcel.php';

$app->get('/index.html', function() use ($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $member = SwimAdmin::model('member.main');
    $member_tags = SwimAdmin::model('member.tags');
    $tags   = $member_tags->findAll();
	$data   = $member->pagination();
    $app->view()->addGlobal('_MOD', 'member');
    $app->view()->addGlobal('_Action', 'index');
    $app->render('member/index.html', array(
        'type'  => Helper::getValue($data['params'], 'type', ''),
        'statu' => Helper::getValue($data['params'], 'statu', ''),
        'query' => Helper::getValue($data['params'], 'query', ''),
        'title' => Helper::getValue($data['params'], 'title', ''),
        'phone' => Helper::getValue($data['params'], 'phone', ''),
        'tags'  => $tags,
        'data'  => $data,
    ));
});

$app->get('/tags.html', function() use ($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $tags = SwimAdmin::model('member.tags');
    $app->view()->addGlobal('_MOD', 'member');
    $app->view()->addGlobal('_Action', 'tags');
    $data   = $tags->pagination();
    $app->render('member/tags.html', array(
        'data'  => $data,
    ));
});
$app->get('/tags-form.html', function() use ($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $id   = trim($app->request->get('id'));
    $data = array();
    $tags = SwimAdmin::model('member.tags');
    if($id) {
        $data   = $tags->findOneById($id);
    }
    $app->render('member/tags-form.html', array(
        'data' => $data,
    ));
});
$app->post('/tags-form.html', function() use($app) {
    $id = trim($app->request->post('id'));
    SwimAdmin::checkPermission('members', $id? PERM_EDIT: PERM_ADD);

    $metags = SwimAdmin::model('member.tags');
    $doc    = array(
        'title'       => trim($app->request->post('title')),
        'statu'       => intval($app->request->post('statu'))
    );
    if(!$doc['title']) {
        SwimAdmin::error('请输入马甲名称。');
    }
    if(empty($id)) {
        $metags->insert($doc);
    } else {
        $metags->update($doc, $id);
    }
    $data = $metags->pagination($app->request->post('__url'));
  
    $app->render('member/tags-list.html', array(
        'data' => $data
    ));
});
// 修改界面
$app->get('/form.html', function() use ($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $id   = $app->request->get('id', '');
    $data = array();
    $type = $app->request->get('type');
    $member = SwimAdmin::model('member.main');

	$member_tags = SwimAdmin::model('member.tags');
    $tags   = $member_tags->findAll();

	$ident  = SwimAdmin::model('member.ident');
    $idents = $ident->find();	

    if($id) {
        $data   = $member->findOneById($id);
	}
    
	$dataIdents = array();
	if(isset($data['idents'])) {
	    foreach($data['idents'] as $idt) {
		    $identId = (string)$idt['ident_id'];
			$dataIdents[$identId] = $idt['ident_name'];
		} 
	}

	$idents2 = array();
	foreach($idents as $ident) {
	    $identId = (string)$ident['_id'];
		$ident['_id'] = $identId;
		$ident['selected'] = isset($dataIdents[$identId]);
		$idents2[] = $ident;
    }
    $app->render('member/form.html', array(
        'id'     => $id,
        'data'   => $data,
		'tags'   => $tags,
		'idents' => $idents2,
        'type'   => $type,
        'realMember' => $data && $data['type'] == ModelMemberMain::MEMBER_REAL,
        'punish' => Config::$punish
    ));
});

$app->post('/form.html', function() use ($app) {
    $id = trim($app->request->post('id', ''));
    SwimAdmin::checkPermission('members', $id? PERM_EDIT: PERM_ADD);

    $member = SwimAdmin::model('member.main');
	$avatar = trim($app->request->post('avatar_small'));
	$idents = $app->request->post('idents', array());
    $dumb   = trim($app->request->post('dumb'));
    $doc    = array(
        'nick'      => trim($app->request->post('nick')),
        'sex'       => trim($app->request->post('sex')),
        'phone'     => trim($app->request->post('phone')),
        'mail'      => trim($app->request->post('mail')),
        'sig'       => trim($app->request->post('sig')),
        'title'     => trim($app->request->post('title')),
        'hometown'  => trim($app->request->post('hometown')),
        'location'  => trim($app->request->post('location')),
        // 'type'           => 2,
        'vip'       => intval($app->request->post('vip')),
        'sns'       => array(),
        'birthday'  => trim($app->request->post('birthday', null)),
        'age'       => trim($app->request->post('age', null)),
        'height'    => trim($app->request->post('height')),
        'weight'    => trim($app->request->post('weight')),
        'bmr'       => intval($app->request->post('bmr')),
        'recommand' => intval($app->request->post('recommand')),
		'idents'    => $idents,
		'inviteable'=> intval($app->request->post('inviteable', 0)),
    );
    $sns = $app->request->post('sns', array());
    foreach($sns as $key => $val) {
        $sns[$key] = trim($val);
    }
    $doc['sns'] = $sns;
    
	$identList = array();
    $identIds  = array();
	foreach($idents as $ident) {
        $tmp = explode(':', $ident, 3);
        $identIds[] = $tmp[0];        
	    $identList[] = array(
		    'ident_id' => $tmp[0],
			'ident_name' => $tmp[1],
			'ident_icon' => $tmp[2]
        );
        //获得资质认证积分、经验奖励
        $Task = SwimAdmin::model('task.main');
        $reward = Config::$taskReward;
        $taskId = '6'; 
        $filter = array('taskId' => $taskId, 'itemId' => $tmp[0], 'userId'=>$id);
        $taskData = $Task->findOne($filter);
        if(!$taskData && $id) {
            $taskData = array(
                'userId'   => $id,
                'taskId'   => $taskId,
                'itemId'   => $tmp[0],
                'quantity' => 1,
                'status'   => 2, //置为已领取状态
                'type'     => 2,
                'times'    => 1,
                'time'     => strtotime('today midnight')
            );
            $Task->insert($taskData);
            $filter = array('_id' => new MongoId($id));
            $update = array('$inc'=> array(
                    'exp'=>$reward[$taskId]['exp'], 
                    'points'=>$reward[$taskId]['points']
            ));
            $member->findAndModify($filter, $update);
            $data = array(
                'user_id' => $id,
                'action'  => $reward[$taskId]['action'],
                'points'  => $reward[$taskId]['points'],
                'desc'    => $reward[$taskId]['desc']
            );
            SwimAdmin::model('points.main')->insert($data);
        }
	}
	$doc['idents'] = $identList;

    if(empty($doc['height'])) $doc['height'] = 0;
    if(empty($doc['weight'])) $doc['weight'] = 0;
    if(empty($doc['bmr'])) $doc['bmr'] = 0;
    if(!empty($doc['birthday'])) {
        $doc['birthday'] = strtotime($doc['birthday']);
    }
	$doc['birthday'] = intval($doc['birthday']);

    if($doc['birthday'] && empty($doc['age'])) {
        $doc['age'] = max(0, date('Y') - date('Y', $doc['birthday']));
    }

    $pwd = $app->request->post('pwd');
    if($id) {
        // 修改会员
        $info = $member->findOneById($id);
        if($info && $info['avatar_small'] != $avatar) {
            $doc['avatar_small'] = $avatar;
            $doc['avatar_mid']   = str_replace('/small-', '/mid-', $avatar);
            $doc['avatar_big']   = str_replace('/small-', '/big-', $avatar);
        }
        if($pwd) {
            $doc['pwd'] = md5($pwd);
        }
        if($info && $info['type'] == ModelMemberMain::MEMBER_REAL) {
            unset($doc['sns']); // 会员不修改 sns 信息
            unset($doc['pwd']); // 会员不修改 密码 信息
        }
        
        if($dumb) {
            $punish = Config::$punish[$dumb];
            if(empty($info['dumb']['id']) || $info['dumb']['id']!=$dumb) { //禁言时间跟上次不一致
                if(empty($info['dumb']['expire']) || $info['dumb']['expire']>time()) { //上次的禁言时间未过期，经验积分回滚
                    $prePoints = empty($info['dumb']['points'])? 0 : abs($info['dumb']['points']);
                    $preExp = empty($info['dumb']['exp'])? 0 : abs($info['dumb']['exp']);
                    $points = $prePoints + $punish['points'];
                    $exp    = $preExp + $punish['exp'];
                    $filter = array('_id' => new MongoId($id));
                    $update = array(
                        '$set' => array(
                            'dumb'=>array(
                                'id'     => $dumb, 
                                'expire' => time() + $punish['time'], 
                                'points' => ($dumb=='3'? 0: $punish['points']),
                                'exp'    => ($dumb=='3'? 0: $punish['exp'])
                        )
                    ));
                    if($dumb!='3') {
                        $update2 = array('$inc' => array('points' => $points));
                        $member->update2($filter, $update2);
                        $pointsData = array(
                            'user_id' => $id,
                            'action'  => $punish['action'],
                            'points'  => $points,
                            'desc'    => $punish['desc'], 
                        );
                        SwimAdmin::model('points.main')->insert($pointsData);
                    }else{ //永久禁言，特殊处理
                        $update['$set']['points'] = 0;
                        $pointsData = array(
                            'user_id' => $id,
                            'action'  => $punish['action'],
                            'points'  => $info['points'] * -1,
                            'desc'    => $punish['desc'],
                        );
                        SwimAdmin::model('points.main')->insert($pointsData);
                    }
                    $member->update2($filter, $update);
                }else{ //禁言已过期，设置新的禁言时间
                    $filter = array('_id' => new MongoId($id)); 
                    $update = array(
                        '$set' => array(
                            'dumb' => array(
                                'id'     => $dumb,
                                'expire' => time() + $punish['time'],
                                'points' => ($dumb=='3'? 0: $punish['points']),
                                'exp'    => ($dumb=='3'? 0: $punish['exp'])
                            )
                        )
                    ); 
                    if($dumb!='3') {
                        $update2 = array('$inc' => array('points' => $punish['points']));
                        $member->update2($filter, $update2);
                        $pointsData = array(
                            'user_id' => $id,
                            'action'  => $punish['action'],
                            'points'  => $punish['points'],
                            'desc'    => $punish['desc']
                        );
                        SwimAdmin::model('points.main')->insert($pointsData);
                    }else{
                        $update['$set']['points'] = 0;
                        $pointsData = array(
                            'user_id' => $id,
                            'action'  => $punish['action'],
                            'points'  => $info['points'] * -1,
                            'desc'    => $punish['desc'],
                        );
                        SwimAdmin::model('points.main')->insert($pointsData);
                    }
                    $member->update2($filter, $update);
                }
            }else{
                if(!empty($info['dumb']['expire']) && $info['dumb']['expire']<time()){ //跟上次禁言时间一致,但已过期，使用新的禁言时间
                    $filter = array('_id' => new MongoId($id)); 
                    $update = array(
                        '$set' => array(
                            'dumb' => array(
                                'id' => $dumb,
                                'expire' => time() + $punish['time'],
                                'points' => ($dumb=='3'? 0: $punish['points']),
                            )
                        )
                    );
                    if($dumb!='3') {
                        $update2 = array('$inc' => array('points' => $punish['points']));
                        $member->update2($filter, $update2);
                    }else{
                        $update['$set']['points'] = 0;
                    }
                    $member->update2($filter, $update);
                }
            } 
        }else{ //解除禁言
            if(!empty($info['dumb']['expire'])) {
                $points = abs($info['dumb']['points']);
                $exp    = abs($info['dumb']['exp']);
                $filter = array('_id' => new MongoId($id)); 
                $update = array(
                    '$set' => array(
                        'dumb.id'     => '0',
                        'dumb.expire' => time()-1,
                        'dumb.points' => 0,
                        'dumb.exp'    => 0,
                    )
                );
                $update2 = array('$inc' => array('points' => $points));
                $member->update2($filter, $update);
                $member->update2($filter, $update2);
                $pointsData = array(
                    'user_id' => $id,
                    'action'  => 0,
                    'points'  => $points,
                    'desc'    => '解队禁言'
                );
                SwimAdmin::model('points.main')->insert($pointsData);
            } 
        }
        //积分经验小于0的，全置为0
        $member->update2(array('_id'=>new MongoId($id), 'points'=>array('$lt'=>0)), array('$set'=>array('points'=>0)));
        $member->update($doc, $id);
    } else {
        // 新建会员
        $doc['block']        = 0;
        $doc['statu']        = 4;
        $doc['pwd']          = md5($pwd? $pwd: 'swim123456');
        $doc['secret']       = md5($doc['phone'].Config::$app['app_key'].time());
        $doc['reg_time']     = time();
        $doc['type']         = ModelMemberMain::MEMBER_FAKE;
        $doc['avatar_small'] = $avatar;
        $doc['avatar_mid']   = str_replace('/small-', '/mid-', $avatar);
        $doc['avatar_big']   = str_replace('/small-', '/big-', $avatar);
        $member->insert($doc);
    }

    //发送获得游泳达人通知
    if($identIds && in_array(Config::$ident2push['游泳达人'], $identIds) && $id) {
        SwimAdmin::model('msg.main')->send(Config::$systemAccount['趣游泳小秘书'], $id, ModelMsgMain::SYS_MSG_TYPE_STAR, ModelMsgMain::ACTION_SYSTEM_MSG, ModelMsgMain::BELONG_SWIMER);
    }

    // $data = $member->find(array('type' => 2));
    $data = $member->pagination($app->request->post('__url'));
    $app->render('member/list.html', array(
        'id'         => $id,
        'data'       => $data
    ));
});

$app->post('/delete.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_DELETE);
    $Member = SwimAdmin::model('member.main');
    $items = $app->request->post('items', array());
    $Member->deleteById($items);
    if($items) {
        $Tweet = SwimAdmin::model('tweet.main');
        $TweetComment = SwimAdmin::model('tweet.comment');
        $TechnicalComment = SwimAdmin::model('technical.comment');
        $NewsComment = SwimAdmin::model('news.comment');
        $Friend = SwimAdmin::model('friend.main');
        $Favorites = SwimAdmin::model('favorites.main');

        $filter = array('user_id' => array('$in' => $items));    
        $Tweet->delete($filter);
        $TweetComment->delete($filter);
        $TechnicalComment->delete($filter);
        $NewsComment->delete($filter);
        $Friend->delete($filter);
        $Favorites->delete($filter);
    }
    $app->view()->renderJSON(array(
        'result' => true
    ));
});

// 删除会员
$app->post('/status.html', function() use ($app) {
    SwimAdmin::checkPermission('members', PERM_EDIT);
    $member = SwimAdmin::model('member.main');
    $status = intval($app->request->post('status'));
    $items  = $app->request->post('items', array());
    $member->updateStatus($items, $status);
    $app->view()->renderJSON(array(
        'result' => true
    ));
});

//删除马甲
$app->post('/tags-delete.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_EDIT);
    $tags = SwimAdmin::model('member.tags');
    $items  = $app->request->post('items', array());
    $tags->deleteById($items);
    $app->render('member/tags-list.html', array(
        'data' => $tags->pagination($app->request->post('__url'))
    ));
});

// 查询动态
$app->get('/comment.html', function() use($app){
    SwimAdmin::checkPermission('members', PERM_READ);
    $id   = $app->request->get('id');
	$tweetComment = SwimAdmin::model('tweet.comment');
    $comments     = $tweetComment->getRepliesComment($id);
    $comments_count = $tweetComment->getRepliesCount($id);
    $tweet = SwimAdmin::model('tweet.main');
    $postlist = $tweet->getUserTweet($id);
    $post_count = $tweet->getUserCount($id);
    $app->view()->addGlobal('_MOD', 'member');
	$app->render('member/comment.html', array(
	    'comments' => $comments,
	    'comments_count' => $comments_count,
	    'postlist' => $postlist,
	    'post_count' => $post_count,
	));
});

// 身份认证
$app->get('/ident.html', function() use($app){
    SwimAdmin::checkPermission('members', PERM_READ);
	$ident = SwimAdmin::model('member.ident');
	$data  = $ident->find();

    $app->view()->addGlobal('_MOD', 'member');
	$app->render('member/ident.html', array(
	    'identList' => $data
	));
});

// 身份认证表单
$app->get('/ident-form.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $id   = $app->request->get('id');
	$data = array();

	if($id) {
		$ident = SwimAdmin::model('member.ident');
		
		$data = $ident->findOne(array('_id' => new MongoId($id)));
	}

	$app->render('member/ident-form.html', array(
	    'ident' => $data
	));
});

$app->post('/ident-form.html', function() use($app) {
    $id = trim($app->request->post('id', ''));
    SwimAdmin::checkPermission('members', $id? PERM_EDIT: PERM_ADD);

	$ident = SwimAdmin::model('member.ident');
    $data  = array(
	    'name'   => trim($app->request->post('name', '')),
		'desc'   => trim($app->request->post('desc', '')),
		'icon'   => trim($app->request->post('ident-icon')),
		'status' => intval($app->request->post('status')),
		'create_time' => time()
	);
    
    if(!$data['name']) {
	    SwimAdmin::error('请输入分类名称。');
	}
    
    if(!$id) {
	    $ident->insert($data);
	} else {
	    $ident->update($data, $id);
	}

    $app->render('member/ident-list.html', array(
	    'identList' => $ident->find()
	));	
});


$app->post('/ident-delete.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_DELETE);
    $ident = SwimAdmin::model('member.ident');
	$items = $app->request->post('items', array());
	$ident->deleteById($items);
	$app->render('member/ident-list.html', array(
	    'identList' => $ident->find()
	));
});

//导出用户
$app->get('/export.html', function() use($app) {
    ini_set("max_execution_time", 60);
	$Member = SwimAdmin::model('member.main');
	$filters = array('phone'=>array('$ne'=>''));
	$projection = array('phone'=>true, 'sex'=>true, 'weight'=>true, 'height'=>true, 'bmr'=>true, 'age'=>true);
	$data = $Member->collection()->find($filters, $projection)->limit(500);
	$app->view()->addGlobal('_MOD', 'local');
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
    $cacheSettings = array('memoryCacheSize' => '200MB');
    PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	$PHPExcel = new PHPExcel();
	$PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '手机号码')
        ->setCellValue('B1', '性别')
        ->setCellValue('C1', '体重')
        ->setCellValue('D1', '身高')
        ->setCellValue('E1', '年龄')
        ->setCellValue('F1', '基础代谢率');
     
	$row = 2;
	foreach($data as $item) {
        $sex = (isset($item['sex']) and in_array($item['sex'], array('男', '女', '保密')))? $item['sex'] : '保密';
        $weight = isset($item['weight'])? $item['weight']: '';
        $height = isset($item['height'])? $item['height']: '';
        $bmr    = isset($item['bmr'])? $item['bmr']: '';
        $age    = isset($item['age'])? $item['age']: '';
		$PHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit("A{$row}", substr($item['phone'], -11), PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit("B{$row}", $sex,    PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit("C{$row}", $weight, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit("D{$row}", $height, PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit("E{$row}", $age)
            ->setCellValueExplicit("F{$row}", $bmr,    PHPExcel_Cell_DataType::TYPE_STRING);
		$row++;
	}
	$filename = date('YmdHis', time());
	header("Content-Type: application/vdn.ms-excel");
	header("Content-Disposition: attachment;filename=\"7swimMember_{$filename}.xls\"");
	header('Cache-Control: max-age=0');
	$writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
	$writer->save('php://output');
});

//积分日志
$app->get('/points-log.html', function() use($app) {
    $data = SwimAdmin::model('points.main')->pg();
    $app->render('member/points-log.html', array(
        'data' => $data,
        'mid'   => $app->request->get('mid'),
        'phone' => $app->request->get('phone'),
        'nick'  => $app->request->get('nick'),
        'start' => $app->request->get('start'),
        'end'   => $app->request->get('end'),
        'min'   => $app->request->get('min'),
        'max'   => $app->request->get('max'),
    ));

});

//导出积分日志
$app->get('/points-log-export.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $Points = SwimAdmin::model('points.main');
    $data = $Points->pg();
    $PHPExcel = new PHPExcel();
    $PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '类型')
        ->setCellValue('B1', '名称')
        ->setCellValue('C1', '性别')
        ->setCellValue('D1', '地区')
        ->setCellValue('E1', '电话')
        ->setCellValue('F1', '积分')
        ->setCellValue('G1', '描述')
        ->setCellValue('H1', '日期');
    $row = 2;
    foreach($data['items'] as $item) {
        $member = $data['members'][$item['user_id']];
        if($member['type'] == 2) {
            $type = '马甲';
        }else if($member['type'] == 1) {
            $type = '教练';
        }else {
            $type = '会员';
        }
        $PHPExcel->setActiveSheetIndex(0) 
            ->setCellValueExplicit("A{$row}", $type)
            ->setCellValueExplicit("B{$row}", $member['nick'])
            ->setCellValueExplicit("C{$row}", $member['sex'])
            ->setCellValueExplicit("D{$row}", $member['location'])
            ->setCellValueExplicit("E{$row}", $member['phone'])
            ->setCellValueExplicit("F{$row}", $item['points'])
            ->setCellValueExplicit("G{$row}", $item['desc'])
            ->setCellValueExplicit("H{$row}", date('Y-m-d H:i:s', $item['create_time']));
        $row++;
    }
    $filename = date("YmdHis", time());
    header("Content-Type: application/vdn.ms-excel");
    header("Content-Disposition: attachment;filename=\"points-log-{$filename}.xls\"");
    header("Cache-Control: max-age=0");
    $writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
    $writer->save('php://output');
});

//用户积分
$app->get('/points.html', function() use($app) {
    $data = SwimAdmin::model('points.main')->pagination();
    $app->render('member/points.html', array(
        'data' => $data,
        'mid'   => $app->request->get('mid'),
        'phone' => $app->request->get('phone'),
        'nick'  => $app->request->get('nick'),
        'start' => $app->request->get('start'),
        'end'   => $app->request->get('end'),
        'min'   => $app->request->get('min'),
        'max'   => $app->request->get('max'),
    ));

});

//导出积分
$app->get('/points-export.html', function() use($app) {
    SwimAdmin::checkPermission('members', PERM_READ);
    $Points = SwimAdmin::model('points.main');
    $data = $Points->pagination();
    $PHPExcel = new PHPExcel();
    $PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '类型')
        ->setCellValue('B1', '名称')
        ->setCellValue('C1', '性别')
        ->setCellValue('D1', '地区')
        ->setCellValue('E1', '电话')
        ->setCellValue('F1', '积分');
    $row = 2;
    foreach($data['items'] as $item) {
		if(isset($data['members'][$item['_id']['user_id']]))
		{
			$member = $data['members'][$item['_id']['user_id']];
			if($member['type'] == 2) {
				$type = '马甲';
			}else if($member['type'] == 1) {
				$type = '教练';
			}else {
				$type = '会员';
			}
			$PHPExcel->setActiveSheetIndex(0) 
				->setCellValueExplicit("A{$row}", $type)
				->setCellValueExplicit("B{$row}", $member['nick'])
				->setCellValueExplicit("C{$row}", $member['sex'])
				->setCellValueExplicit("D{$row}", $member['location'])
				->setCellValueExplicit("E{$row}", $member['phone'])
				->setCellValueExplicit("F{$row}", $item['sum']);
			$row++;
		}
    }
    $filename = date("YmdHis", time());
    header("Content-Type: application/vdn.ms-excel");
    header("Content-Disposition: attachment;filename=\"points_{$filename}.xls\"");
    header("Cache-Control: max-age=0");
    $writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
    $writer->save('php://output');
});
