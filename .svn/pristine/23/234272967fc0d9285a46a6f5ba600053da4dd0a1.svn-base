<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/index.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$topic = SwimAdmin::model('topic.main');
	$data = $topic->find(Helper::parseQueryString($_SERVER['QUERY_STRING']));
	$app->view()->addGlobal('_MOD', 'discover');
	$app->render('adv/index.html', array(
		'data' 			=> $data,
		'mods' 			=> $topic->getBelong(),
		'currentMod' 	=> $app->request->get('mod'),
		'cats' 			=> $topic->getCats(),
		'belongs' 		=> $topic->getBelong()
	));
});

$app->get('/form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$id = $app->request->get('id');
	$topic = SwimAdmin::model('topic.main');
	$area  = SwimAdmin::model('course.area');
	if(!$id) {
		$data = array(
			'start_at' 	=> time(),
			'expire_at' => strtotime('+3 month'),
			'statu' 	=> true
		);
	} else {
		$data = $topic->collection()->findOne(array('_id' => new MongoId($id)));
	}
    
	$slted = array(
	    'city' => array()
	);

	if(isset($data['city'])) {
        foreach($data['city'] as $city) {
	        $slted['city'][$city['city_id']] = $city['city_name'];	
		}	
	}

	$app->render('adv/form.html', array(
		'adv' 		=> $data,
		'belong' 	=> $topic->getBelong(),
		'cats' 		=> $topic->getCats(),
		'cities'    => $area->find(array('statu' => 1)),
		'slted'     => $slted
	));
});
$app->post('/form.html', function() use($app) {
	$id = $app->request->post('id');
	$start_at = $app->request->post('start_at');
	$expire_at = $app->request->post('expire_at');
	if(!$start_at or !($start_at = strtotime($start_at))) {
		$start_at = null;
	}
	if(!$expire_at or !($expire_at = strtotime($expire_at))) {
		$expire_at = null;
	}
	$doc = array(
		'title' 		=> trim($app->request->post('title')),
		'title_type' 	=> trim($app->request->post('title_type')),
		'category' 		=> trim($app->request->post('category')),
		'description' 	=> trim($app->request->post('desc')),
		'cover' 		=> trim($app->request->post('cover')),
		'link' 			=> trim($app->request->post('link')),
		'order' 		=> intval($app->request->post('order')),
		'start_at' 		=> $start_at,
		'expire_at' 	=> $expire_at,
		'statu' 		=> (boolean)$app->request->post('statu'),
		'belong' 		=> $app->request->post('belong', array()),
		'city'          => array()
	);
	$cities = $app->request->post('service_area', array("0::global"));
	if($cities) {
	    foreach((array)$cities as $city) {
		    $city = trim($city);
			if($city) {
			    $exp = explode('::', $city);
				$doc['city'][] = array(
				    'city_id'   => $exp[0],
					'city_name' => $exp[1]
				);
			}
		}
	}
	if(empty($doc['cover'])) {
		SwimAdmin::error('请上传图片。');
	}

	$topic = SwimAdmin::model('topic.main');
	if(!$id) {
		SwimAdmin::checkPermission('main-contents', PERM_ADD);
		$topic->insert($doc);
	} else {
		SwimAdmin::checkPermission('main-contents', PERM_EDIT);
		$topic->update($doc, $id);
	}

	$data = $topic->find(Helper::parseQueryString($app->request->post('_url')));
	$app->render('adv/list.html', array(
		'data' 		=> $data,
		'cats' 		=> $topic->getCats(),
		'belongs' 	=> $topic->getBelong()
	));
});
$app->post('/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$id    = $app->request->post('id');
	$topic = SwimAdmin::model('topic.main');
	if($topic->deleteById($id)) {
		$app->view->renderJSON(array(
			'result' => true
		));
	} else {
		SwimAdmin::error('删除失败。');
	}
});
