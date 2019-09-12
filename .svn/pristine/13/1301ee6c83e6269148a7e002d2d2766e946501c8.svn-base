<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/index.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$tweet  = SwimAdmin::model('tweet.main');
	$tGroup = SwimAdmin::model('tweet.group');
	$Topic  = SwimAdmin::model('tweet.topic');
	$member = SwimAdmin::model('member.main');
	$tweetTag = SwimAdmin::model('tweet.tag');
	$data     = $tweet->pagination();
	//$authors  = $member->getMembers();
	$cursor   = $tweetTag->find();
    $tags     = array();
	foreach($cursor as $tag) {
		$tagId = (string)($tag['_id']);
		$tag['selected'] = isset($tagSeleted[$tagId]);
		$tags[] = $tag;
	}
	$app->view()->addGlobal('_MOD', 'lbs');
	$app->view()->addGlobal('_Action', 'index');
	$app->render('tweet/index.html', array(
		'data'       => $data,
		'tags'       => $tags,
		'authors'    => $data['members'],
		//'groups'     => $tGroup->findAll(),
		//'topics'     => $Topic->find(),
		//'Sections'   => $tGroup->getGroups(),
		//'group_id'   => $app->request->get('group-id'),
		'tag_id'   => $app->request->get('tag-id'),
		//'topic_id'   => $app->request->get('topic-id'),
		'tweet_type' => $app->request->get('tweet-type'),
		'title'      => $app->request->get('title'),
		'author'     => $app->request->get('author'),
		'start'      => $app->request->get('start'),
		'end'        => $app->request->get('end'),
	));
});

$app->get('/form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$id = $app->request->get('id', '');
	$data = array(
		'create_time' => strtotime(date('Y-m-d H:i:s'))
	);
	if($id) {
		$tweet = SwimAdmin::model('tweet.main');
		$data = $tweet->collection()->findOne(array('_id' => new MongoId($id)));
	}
	if(!isset($data['content']) || !$data['content']) {
		$data['content'] =array(
				'text' 		=> '', 
				'location' 	=> array(
					'address' 		=> '',
					'coordinates' 	=> array(),
					'type'			=> 'Point'
				),
				'photos' 	=> array(),
				'tweet_tag' => array()
		);
	}
	//标签
    if(!isset($data['content']['tweet_tag'])) {
	    $data['content']['tweet_tag'] = array();
	}
	//所属话题精选
	if(!isset($data['topic'])) { 
	    $data['topic'] = array();
	}

	$member = SwimAdmin::model('member.main');
	$filter['type'] = 2;
	$users  = $member->find($filter);

	$tweetGroup = SwimAdmin::model('tweet.group');
	$groups     = $tweetGroup->findAll();

	$tweetComment = SwimAdmin::model('tweet.comment');
	$comments     = $id? $tweetComment->getComments($id):array();

	$tags = array_values($data['content']['tweet_tag']);
	$tagSeleted = array();
	foreach($tags as $item) {
	    $tagSeleted[$item['id']] = $item['name'];
	}

	$tweetTag = SwimAdmin::model('tweet.tag');
	$cursor   = $tweetTag->find();
    $tags     = array();
	foreach($cursor as $tag) {
		$tagId = (string)($tag['_id']);
		$tag['selected'] = isset($tagSeleted[$tagId]);
		$tags[] = $tag;
	}
    $topicSeleted = array();
	foreach($data['topic'] as $item) {
	    $topicSeleted[$item['id']] = $item['name'];
	}

	$Topic     = SwimAdmin::model('tweet.topic');
	$cursor    = $Topic->find(); 
	$topics = array();
	foreach($cursor as $item) {
		$topicId = (string)$item['_id'];
	    $item['selected'] = isset($topicSeleted[$topicId]);	
		$topics[] = $item;
	}

	$author = array();
	if(isset($data['user_id']) && $data['user_id']) {
		$author = $member->findOneById($data['user_id']);
	}

	$app->render('tweet/form.html', array(
		'id' 	   => $id,
		'data' 	   => $data,
		'users'    => $users,
		'comments' => $comments,
		'author'   => $author,
		'groups'   => $groups,
		'tags'     => $tags,
		'topics'   => $topics
	));
});

$app->post('/form.html', function() use($app) {
	$id = $app->request->post('id', '');
	SwimAdmin::checkPermission('main-contents', $id? PERM_EDIT: PERM_ADD);

	$tweet       = SwimAdmin::model('tweet.main');
    $tGroup      = SwimAdmin::model('tweet.group');
    $userId      = trim($app->request->post('user_id')); 
	$time        = strtotime($app->request->post('create_time'));
	$photos      = $app->request->post('photos', array());
	$coordinates = explode(',', trim($app->request->post('coordinates', '')));
	$hot         = $app->request->post('hot', 0); //是否热帖(0否，1是)
	$top         = $app->request->post('top', 0); //是否置顶(0否，1置顶)
	$recommand   = $app->request->post('recommand', 0); //是否精华帖(0否, 是)
	$group       = explode(':', trim($app->request->post('group', ''))); //所属版块（对应tweet_group表中的_id值）
	$type        = $app->request->post('type'); //帖子所属类型（0泳圈 1公告）
	
	$content     = array(
		'title'     => trim($app->request->post('title')),
		'text' 		=> trim($app->request->post('text')), 
		'location' 	=> array(
			'address' 		=> trim($app->request->post('address')),
			'coordinates' 	=> array((float)$coordinates[0], isset($coordinates[1])? (float)$coordinates[1]: 0),
			'type'			=> 'Point'
		),
        'photos' 	=> array(),
		'video'        => array(
		    'w'        => $app->request->post('video-width', 0) * 1,
			'h'        => $app->request->post('video-height', 0) * 1,
			'time'     => $app->request->post('video-time', 0) * 1,
			'url'      => $app->request->post('video-url', ''),
			'cover'    => $app->request->post('video-cover', ''),
			'vertical' => $app->request->post('video-vertical', 0) * 1,
			'reverse'  => $app->request->post('video-reverse', 0) * 1
		),
	);
	$groupId = '';
	$groupTitle = '';
	if(isset($group[0]) and isset($group[1])){
		$groupId    = $group[0];
		$groupTitle = $group[1];
	}
	$sizeOfPhotos = $app->request->post('size_of_photos', array());
	$countPhoto   = count($photos);
	if($countPhoto > $tweet::MAX_PHOTO_NUM) {
		$photos = array_slice($photos, 0, $tweet::MAX_PHOTO_NUM);
		$app->response->headers->set('Photonum', join(':', array($countPhoto, $tweet::MAX_PHOTO_NUM)));
	}
	foreach($photos as $idx => $photo) {
		$size = explode(',', isset($sizeOfPhotos[$idx]) && $sizeOfPhotos[$idx]? $sizeOfPhotos[$idx]: '');
		$content['photos'][] = array(
			'big' => str_replace('/mid-', '/big-', $photo),
			'min' => $photo,
			'w'   => isset($size[0])? intval($size[0]): 0,
			'h'   => isset($size[1])? intval($size[1]): 0
		);
	}
	$share = $app->request->post('shared_swimgroup', array());
	foreach($share as $key=>$value) {
	    $content['shared_swimgroup'][$key] = $value;
	}
	$tweet_tag = $app->request->post('tweet_tag', array());
	$sltedTags = array();
	foreach($tweet_tag as $tag) {
	    $tmp = explode(':', $tag);
		$sltedTags[] = array(
		    'id'   => $tmp[0],
			'name' => $tmp[1],
			'invite' => intval($tmp[2])
		);
	}
	$content['tweet_tag'] = $sltedTags;

	$topic = $app->request->post('topic', array());
	$belongTopic = array();
	foreach($topic as $item) {
	    $tmp = explode(':', $item);
		$belongTopic[] = array(
		    'id'   => $tmp[0],
			'name' => $tmp[1]
		);
	}
    $member = SwimAdmin::model('member.main')->findOneById($userId);
	$doc = array(
		'hot'               => (int)$hot,
		'top'               => (int)$top,
		'recommand'         => (int)$recommand,
		'group_id'          => $groupId,
		'group_title'       => $groupTitle,
		'topic'             => $belongTopic,
		'type'              => (int)$type,
		'content' 			=> $content,
		'create_time'		=> $time? $time: time(),
		'views_counter'		=> intval($app->request->post('views_counter')),
		'likes_counter' 	=> intval($app->request->post('likes_counter')),
		'comments_counter' 	=> intval($app->request->post('comments_counter')),
		'statu'				=> 0,
		'user_id'			=> $userId,
		'author'            => $member['nick'],
	);
	// if(!$doc['content']['text']) {
	// 	SwimAdmin::error('请输入内容。');
	// }
	if(!$doc['content']['title']) {
		SwimAdmin::error('请输入标题。');
	} else if(mb_strlen($doc['content']['title'], 'UTF8') > 35) {
		SwimAdmin::error('标题不能多于35个字。');
	}
	if(!$groupId)
	{
		//SwimAdmin::error('请选择对应的版块。');
	}
	if(!$type)
	{
		// SwimAdmin::error('请选择帖子的类型（公告还是普通帖子）');
	}
/*	
	$filter = array(
		'_id' => new MongoId($groupId), 
		'region' => 'local'
	);
	$group = $tGroup->findOne($filter);
 */
    $group = false;

	if(!$id) {
		$res = $tweet->insert($doc);
		if($group) { //如果是同城
			$doc = array(
				'group_id' => $groupId,
				'coords' => $content['location']['coordinates'] 
			);
			$tweet->insertElasticSearch($doc, (string)$res['_id']);
		}
	} else {
		$tweet->update($doc, $id);
        if($group) { //如果是同城
			$doc = array(
				'group_id' => $groupId,
				'coords' => $content['location']['coordinates'] 
			);
			$tweet->updateElasticSearch($doc, $id);
		}
	}
    //被选为热帖
    if($hot) {
        $tweetComment = SwimAdmin::model('tweet.comment');
        $objId = $id ? $id : (string)$res['_id'];
        $comment = $tweetComment->findOne(array(
            'user_id' => Config::$systemAccount['趣游泳小秘书'],
            'tweet_id' => $objId
        ));
        if(!$comment) {
            $content = array('text' => '恭喜，您的帖子被小秘书选为热帖了');
            $res = $tweetComment->post(Config::$systemAccount['趣游泳小秘书'], $objId, $content);
            SwimAdmin::model('msg.main')->send(Config::$systemAccount['趣游泳小秘书'], $doc['user_id'], $res['id'],  ModelMsgMain::ACTION_TWEET_COMMENT, ModelMsgMain::BELONG_COMMENT);     
            $tweetComment->freshMemberCommentsCounter(Config::$systemAccount['趣游泳小秘书']);
        }
    }
    //被选为精华帖
    if($recommand) {
        $tweetComment = SwimAdmin::model('tweet.comment');
        $objId = $id ? $id : (string)$res['_id'];
        $comment = $tweetComment->findOne(array(
            'user_id' => Config::$systemAccount['趣游泳小秘书'],
            'tweet_id' => $objId
        ));
        if(!$comment) { //避免重复发送消息提醒
            $content = array('text' => '恭喜，您的帖子被小秘书选为精华帖了');
            $res = $tweetComment->post(Config::$systemAccount['趣游泳小秘书'], $objId, $content);
            SwimAdmin::model('msg.main')->send(Config::$systemAccount['趣游泳小秘书'], $doc['user_id'], $res['id'],  ModelMsgMain::ACTION_TWEET_COMMENT, ModelMsgMain::BELONG_COMMENT);     
            $tweetComment->freshMemberCommentsCounter(Config::$systemAccount['趣游泳小秘书']);
        }

        $Task = SwimAdmin::model('task.main');
        $reward = Config::$taskReward;
        $taskId = '7';
        $filter = array('taskId' => $taskId, 'itemId'=>$objId);
        $taskData = $Task->findOne($filter);
        if(!$taskData) {
            $taskData = array(
                'userId' => $userId,
                'taskId' => $taskId,
                'itemId' => $objId,
                'quantity' => 1,
                'status'   => 2,
                'type'     => 2,
                'times'    => 1,
                'time'     => strtotime('today midnight')
            );
            $Task->insert($taskData);
            $filter = array('_id' => new MongoId($userId));
            $update = array('$inc'=> array(
                'exp' => $reward[$taskId]['exp'],
                'points' => $reward[$taskId]['points']
            ));
            SwimAdmin::model('member.main')->findAndModify($filter, $update);
            $pointsData = array(
                'user_id' => $userId,
                'action'  => $reward[$taskId]['action'],
                'points'  => $reward[$taskId]['points'],
                'desc'    => $reward[$taskId]['desc']
            );
            SwimAdmin::model('points.main')->insert($pointsData);
        }
    }
	$params = Helper::parseQueryString($app->request->post('__url'));
	$app->render('tweet/list.html', array(
		'id'         => $id,
		'data'       => $tweet->pagination($app->request->post('__url')),
		'groups'     => $tGroup->getGroups(),
		'group_id'   => Helper::popValue($params, 'group-id'),
		'tweet_type' => Helper::popValue($params, 'tweet-type'),
		'title'      => Helper::popValue($params, 'title')
	));
});

$app->get('/intop-form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$Tweet   = SwimAdmin::model('tweet.main');
	$tweetId = trim($app->request->get('id'));
	$data    = $Tweet->findOneById($tweetId);
    $inTop   = $Tweet->getOneTop(array('tweet_id' => $tweetId));
	$app->render('tweet/intop-form.html', array(
        'data'  => $data,
        'inTop' => $inTop,
	));
});

$app->post('/intop-form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$Tweet = SwimAdmin::model('tweet.main');
	$id    = trim($app->request->post('id'));
	$date  = strtotime(trim($app->request->post('date')));
    $photo = trim($app->request->post('photo'));
    $sizeOfPhoto = trim($app->request->post('size_of_photo'));
    $size = explode(',', $sizeOfPhoto);
    if(!$photo) {
        SwimAdmin::error('请上传一张置顶图片');
    }
	$doc   = array(
		'title'  => trim($app->request->post('title')),
		'status' => intval($app->request->post('status')),
		'date'   => $date? $date: time(),
        'photo'  => $photo,
        'photo_w'      => (int)$size[0],
        'photo_h'      => (int)$size[1],      
	);
	if($Tweet->addTop($id, $doc)) {
        $Tweet->update(
            array('top'=>$doc['status']? 1: 0), 
            $id
        );
		$doc['tweet_id'] = $id;
		$app->view->renderJSON($doc);
	} else {
		SwimAdmin::error('添加置顶失败。');
	}
});

$app->post('/comment.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$tweetComment = SwimAdmin::model('tweet.comment');
	$tweetId      = trim($app->request->post('tweetId'));
	$userId       = trim($app->request->post('userId'));
	$comment      = trim($app->request->post('comment'));
	$commentId    = trim($app->request->post('commentId'));
	$time    	  = strtotime(trim($app->request->post('time')));
	if($tweetId && $userId && $comment) {
		$replyUser = null;
		$content = array(
			'text' => $comment
		);
		if($commentId) { //回复评论
			$detail = SwimAdmin::model('tweet.comment')->detail($commentId);
			if($detail) {
				$text = isset($detail['content']) && isset($detail['content']['text'])? $detail['content']['text']: '';
				$user = isset($detail['user'])? $detail['user']: array();
				$content['Replay_Comment_Text'] = array(
					'replyComment' => array(
						'content' => $text,
						'id'      => isset($detail['id'])? $detail['id']: ''
					),
					'replyUser' => array(
						'nick'  => isset($user['nick'])? $user['nick']: '',
						'id'    => isset($detail['user_id'])? $detail['user_id']: ''
					)
				);
				$replyUser = isset($detail['user_id']) ? $detail['user_id'] : null;
			}
		}
		$res = $tweetComment->post($userId, $tweetId, $content, $time? $time: time());
		$tweet = SwimAdmin::model('tweet.main')->findOneById($tweetId);
		$msgModel = SwimAdmin::model('msg.main');
		
		if($tweet && $tweet['user_id'] != $userId && $replyUser==null) {
		    $msgModel->send($userId, $tweet['user_id'], $res['id'], ModelMsgMain::ACTION_TWEET_COMMENT, ModelMsgMain::BELONG_COMMENT);
			$tweetComment->freshMemberCommentsCounter($userId);
		}
		if($tweet && $replyUser) { //通知作者评论被回复
		    $msgModel->send($userId, $replyUser, $res['id'], ModelMsgMain::ACTION_COMMENT_REPLY, ModelMsgMain::BELONG_COMMENT);
		}
	}
	$comments     = $tweetComment->getComments($tweetId);
	$count        = $tweetComment->getCommentCount($tweetId);
	$app->response->headers->set('comment-count', $count);
	$app->render('tweet/comment-list.html', array(
		'comments' => $comments
	));
});

$app->post('/comment-delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$tweetComment = SwimAdmin::model('tweet.comment');
	$commentId    = trim($app->request->post('commentId'));
    $comment      = $tweetComment->findOne(array(
        '_id' => new MongoId($commentId)
    ));
	$comments     = array();
	$count        = 0;
	if($comment) {
		$result = $tweetComment->deleteById($commentId);
		if($result) {
            //更新动态被评论数量
            SwimAdmin::model('tweet.main')->freshCommentCounter($comment['tweet_id'], -1);
			$tweetComment->freshMemberCommentsCounter($comment['user_id'], -1);
        }
		$comments = $tweetComment->getComments($comment['tweet_id']);
		$count    = $tweetComment->getCommentCount($comment['tweet_id']);
	}
	$app->response->headers->set('comment-count', $count);
	$app->render('tweet/comment-list.html', array(
		'comments'     => $comments,
	));
});

$app->get('/user-chosen.html', function() use($app) {
	$app->view->renderJSON(array(
		array('text' => 'hello', 'value' => 'world')
	));
});

$app->post('/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$tweet  = SwimAdmin::model('tweet.main');
	$tGroup = SwimAdmin::model('tweet.group');
	$items 	= $app->request->post('items', array());
	$params = Helper::parseQueryString($app->request->post('__url'));
	$tweet->deleteById($items);
	$tweet->deleteElasticSearch($items);
	$app->render('tweet/list.html', array(
		'data'       => $tweet->pagination($app->request->post('__url')),
		'groups'     => $tGroup->getGroups(),
		'group_id'   => Helper::popValue($params, 'group-id'),
		'tweet_type' => Helper::popValue($params, 'tweet-type'),
		'title'      => Helper::popValue($params, 'title')
	));
});

$app->get('/category.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$cat 	= SwimAdmin::model('tweet.category');
	$list 	= $cat->find();
	$app->view()->addGlobal('_MOD', 'discover');
	$app->render('tweet/category.html', array(
		'categoryList' => $list
	));
});

$app->get('/category/form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$id 	= $app->request->get('id');
	$cat 	= SwimAdmin::model('tweet.category');
	$data 	= array();
	if($id) {
		$data = $cat->collection()->findOne(array('_id' => new MongoId($id)));
	}
	$app->render('tweet/category-form.html', array(
		'cat' => $data
	));
});

$app->post('/category/form.html', function() use($app) {
	$id 	= $app->request->post('id', '');
	$cat 	= SwimAdmin::model('tweet.category');
	$data 	= array(
		'name' 		=> trim($app->request->post('name', '')),
		'priority' 	=> intval($app->request->post('priority')),
		'status' 	=> intval($app->request->post('status'))
	);
	if(!$data['name']) {
		SwimAdmin::error('请输入分类名称。');
	}
	if(!$id) {
		$cat->insert($data);
	} else {
		$cat->update($data, $id);
	}
	$app->render('tweet/category-list.html', array(
		'categoryList' => $cat->find()
	));
});

$app->post('/category/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$cat 	= SwimAdmin::model('tweet.category');
	$items 	= $app->request->post('items', array());
	$cat->deleteById($items);
	$app->render('tweet/category-list.html', array(
		'categoryList' => $cat->find()
	));
});

//话题列表
$app->get('/topic.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_READ);
    $app->view()->addGlobal('_MOD', 'lbs');
	$app->view()->addGlobal('_Action', 'topic');

	$Topic = SwimAdmin::model('tweet.topic');
	$data  = $Topic->pagination();
    $app->render('tweet/topic-index.html', array(
        'data' => $data	
	));	
});

$app->get('/topic-form.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_READ);
	$Topic = SwimAdmin::model('tweet.topic');
	$id    = trim($app->request->get('id'));
	$data  = $Topic->findOneById($id);
	$app->render('tweet/topic-form.html', array(
	    'data' => $data
	));
});

$app->post('/topic-form.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_READ);
	$Topic = SwimAdmin::model('tweet.topic');
	$id    = trim($app->request->post('id'));
	$title = trim($app->request->post('title'));
	$date  = strtotime($app->request->post('date'));

	if(!$title) {
	    SwimAdmin::error('请输入话题名称');
	}

	if($date) {
		$time = date('H:i:s', $date);
        if($time=='00:00:00') {
		    $date = mktime(date('H'), date('i'), date('s'), date('n', $date), date('j', $date), date('Y', $date));
		}
	}

    $doc = array(
	    'title' => $title,
   	    'type'        => trim($app->request->post('type')),
		'description' => trim($app->request->post('description')),
		'cover'       => trim($app->request->post('cover')),
		'photo'       => trim($app->request->post('photo')),
		'priority'    => intval($app->request->post('priority')),
		'statu'       => intval($app->request->post('statu')),
		'recommand'   => intval($app->request->post('recommand')),
		'date'        => $date ? $date : time()
	);

	if(empty($id)) {
	    $Topic->insert($doc);
	} else {
	    $Topic->update($doc, $id);
	}
    
	$data = $Topic->pagination($app->request->post('__url'));
	$app->render('tweet/topic-list.html', array(
	    'data' => $data
	));
});

$app->post('/topic-delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$Topic = SwimAdmin::model('tweet.topic');
	$items 	= $app->request->post('items', array());
	$Topic->deleteById($items);
	$app->render('tweet/topic-list.html', array(
		'data' => $Topic->pagination($app->request->post('__url'))
	));
});

$app->get('/tag.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_READ);
	$app->view()->addGlobal('_MOD', 'lbs');
	$app->view()->addGlobal('_Action', 'tag');
	$Tag   = SwimAdmin::model('tweet.tag');
	$data  = $Tag->pagination();
	$app->render('tweet/tag-index.html', array(
	    'data' => $data
	));
});

$app->get('/tag-form.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_READ);
	$Tag  = SwimAdmin::model('tweet.tag'); 
	$id   = trim($app->request->get('id'));
	$data = $Tag->findOneById($id);
	$app->render('tweet/tag-form.html', array(
	    'data' => $data
	));
});

$app->post('/tag-form.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$Tag  = SwimAdmin::model('tweet.tag');
	$id   = trim($app->request->post('id'));
	$name = trim($app->request->post('name'));
    $date = strtotime($app->request->post('date'));
    $category = trim($app->request->post('category'));

	if(!$name) {
	    SwimAdmin::error('请输入标签名称');
	}

    if(!$category) {
        SwimAdmin::error('请选择标签所属分类');
    }

	if($date) {
	    $time = date('H:i:s', $date);

		if($time=='00:00:00') {
		    $date = mktime(date('H'), date('i'), date('s'), date('n', $date), date('j', $date), date('Y', $date));
		}
	}
    $category = explode(':', $category);
	$doc = array(
	    'name'        => $name,
		'description' => trim($app->request->post('description')),
		'priority'    => intval($app->request->post('priority')),
		'invite'      => intval($app->request->post('invite')),
        'cat_id'      => intval($category[0]),
        'cat_name'    => $category[1],
		'statu'       => trim($app->request->post('statu')),
        'date'        => $date ? $date : time()
    );

	if(empty($id)) {
	    $Tag->insert($doc); 
	} else {
	    $Tag->update($doc, $id);   
	}

	$data = $Tag->pagination($app->request->post('__url'));
	$app->render('tweet/tag-list.html', array(
	    'data' => $data
	));
});

$app->post('/tag-delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$Tag = SwimAdmin::model('tweet.tag');
	$items 	= $app->request->post('items', array());
	$Tag->deleteById($items);
	$app->render('tweet/tag-list.html', array(
		'data' => $Tag->pagination($app->request->post('__url'))
	));
});

$app->get('/group.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$app->view()->addGlobal('_MOD', 'fun');
	$app->view()->addGlobal('_Action', 'group');

	$TGroup = SwimAdmin::model('tweet.group');
	$data   = $TGroup->pagination();

	$app->render('tweet/group-index.html', array(
		'data' => $data
	));
});

$app->get('/group-form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$Member = SwimAdmin::model('member.main');
	$filter['type'] = 2;
	$users  = $Member->find($filter);

	$TGroup = SwimAdmin::model('tweet.group');
	$id     = trim($app->request->get('id'));
	$data   = $TGroup->findOneById($id);
  
	$app->render('tweet/group-form.html', array(
		'users' => $users,
		'data'  => $data
	));
});

$app->post('/group-form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$TGroup = SwimAdmin::model('tweet.group');
	$id     = trim($app->request->post('id'));
	$doc    = array(
		'region'      => trim($app->request->post('local', 'global')),
		'leader'      => trim($app->request->post('user_id')),
		'title'       => trim($app->request->post('title')),
		'description' => trim($app->request->post('description')),
		'cover'       => trim($app->request->post('cover')),
		'priority'    => trim($app->request->post('priority')),
		'hot'         => trim($app->request->post('hot')),
		'member_num'  => intval($app->request->post('member_num')),
		'tweet_num'   => intval($app->request->post('tweet_num')),
		'statu'       => intval($app->request->post('statu'))
	);
	if($doc['priority'] === '') {
		$doc['priority'] = 100;
	} else {
		$doc['priority'] = intval($doc['priority']);
	}
	if(!$doc['title'] AND $doc['region']!=='local') {
		SwimAdmin::error('请输入圈子名称。');
	}
	if(!$doc['leader'] AND !$doc['region']) {
		SwimAdmin::error('请选择圈主或同城');
	}
	
	$doc['region'] = ($doc['region'] == 'local') ? 'local' : 'global';
	
	if(empty($id)) {
		if($doc['region'] == 'local') {
			$filter = array('region'=>'local');
			$group  = $TGroup->findOne($filter);
			if($group) {
				SwimAdmin::error('同城约泳版块已存在');
			}
		}
		$TGroup->insert($doc);
	} else {
		$TGroup->update($doc, $id);
	}
	$data = $TGroup->pagination($app->request->post('__url'));
	$app->render('tweet/group-list.html', array(
		'data' => $data
	));
});

$app->post('/group-delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$TGroup = SwimAdmin::model('tweet.group');
	$items 	= $app->request->post('items', array());
	$TGroup->deleteById($items);
	$app->render('tweet/group-list.html', array(
		'data' => $TGroup->pagination($app->request->post('__url'))
	));
});

$app->post('/group-recommand.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$TGroup = SwimAdmin::model('tweet.group');
	$items 	= $app->request->post('items', array());
	$action = intval($app->request->post('recommand'));
	if($action) {
		$TGroup->insertRecommand($items);
	} else {
		$TGroup->deleteRecommandById($items);
	}
	$app->render('tweet/group-list.html', array(
		'data' => $TGroup->pagination($app->request->post('__url'))
	));
});
