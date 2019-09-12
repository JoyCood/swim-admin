<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/list.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$goods = SwimAdmin::model('goods.main');
	$data = $goods->pagination();
	$app->view()->addGlobal('_MOD', 'adv');
	$app->render('goods/index.html', array(
		'data' => $data
	));
});
$app->get('/mall-form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$goods = SwimAdmin::model('goods.mall');
	$data = $goods->findOneById();
	$app->render('goods/mall-form.html', array(
		'data' => $data
	));
});
$app->post('/mall-form.html', function() use($app) {
    $id = trim($app->request->post('id'));
    SwimAdmin::checkPermission('main-contents', $id? PERM_EDIT: PERM_ADD);

    $mall = SwimAdmin::model('goods.mall');
    $doc  = array(
        'mall'       => trim($app->request->post('mall')),
        'class'       => trim($app->request->post('class')),
        'member'       => trim($app->request->post('member')),
        'order'       => trim($app->request->post('order')),
        'statu'       => trim($app->request->post('statu')),
    );
    if(!$doc['mall']) {
        SwimAdmin::error('请输入商城主页信息。');
    }
    if(!$doc['class']) {
        SwimAdmin::error('请输入分类检索信息。');
    }
    if(!$doc['member']) {
        SwimAdmin::error('请输入会员主页信息。');
    }
    if(!$doc['order']) {
        SwimAdmin::error('请输入我的订单信息。');
    }
    if(empty($id)) {
        $mall->insert($doc);
    } else {
        $mall->update($doc, $id);
    }
    $app->view()->renderJSON(array(
		'result' => true
	));
});
// 修改界面
$app->get('/form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$id = $app->request->get('id', '');
	$data = array();
	if($id) {
		$goods = SwimAdmin::model('goods.main');
		$data = $goods->collection()->findOne(array('_id' => new MongoId($id)));
	} else {
		$data['up_time'] = strtotime(date('Y-m-d 00:00:00'));
	}
	$category = $goods = SwimAdmin::model('goods.category');
	$dataCats = array();
	if(isset($data['belong'])) {
		foreach($data['belong'] as $belong) {
			$cid = (string)$belong['cateId'];
			$dataCats[$cid] = $belong['cate'];
		}
	}
	$catsList = $category->find(array('status' => 1));
	$cats = array();
	foreach($catsList as $cat) {
		$cid = (string)$cat['_id'];
		$cat['_id'] = $cid;
		$cat['selected'] = isset($dataCats[$cid]);
		$cats[] = $cat;
	}
	if(!isset($data['contents']) || !$data['contents']) {
		$data['contents'] =array(
			array('text' => '', 'media' => array())
		);
	}
	$app->render('goods/form.html', array(
		'id' => $id,
		'data' => $data,
		'cats' => $cats
	));
});
$app->post('/form.html', function() use($app) {
	$id = $app->request->post('id', '');
	SwimAdmin::checkPermission('main-contents', $id? PERM_EDIT: PERM_ADD);

	$goods  = SwimAdmin::model('goods.main');
	$cats   = $app->request->post('category', array());
	$belong = array();
	foreach($cats as $cat) {
		$tmp = explode(':', $cat);
		$cid = array_shift($tmp);
		$cat = join(':', $tmp);
		$belong[] = array(
			'cateId' => $cid,
			'cate' => $cat
		);
	}
	$upTime      = strtotime(trim($app->request->post('up_time')));
	$downTime    = strtotime(trim($app->request->post('down_time')));
	$statu       = intval($app->request->post('statu'));
	$contents    = array();
	$texts       = $app->request->post('content', array());
	$photos      = $app->request->post('contentThumbs', array());
	$sizeOfSteps = $app->request->post('size_of_contentThumbs', array());
	foreach($texts as $idx => $txt) {
		$content = array(
			'text'  => $txt,
			'media' => array()
		);
		if(isset($photos[$idx]) && $photos[$idx]) {
			$sizeOfPhotos = isset($sizeOfSteps[$idx])? $sizeOfSteps[$idx]: array();
			foreach($photos[$idx] as $n => $url) {
				$size = explode(',', isset($sizeOfPhotos[$n]) && $sizeOfPhotos[$n]? $sizeOfPhotos[$n]: '');
				$content['media'][] = array(
					'type' => 'photo',
					'url'  => $url,
					'w'    => intval($size[0]),
					'h'    => intval($size[1])
				);
			}
		}
		$contents[] = $content;
	}
	$doc = array(
		'title' 		  => trim($app->request->post('title')),
		'description' 	  => trim($app->request->post('description')),
		'photos' 		  => array(),
		'conver' 		  => $app->request->post('conver'),
		'up_time' 		  => $upTime? $upTime: null,
		'down_time' 	  => $downTime? $downTime: null,
		'price' 		  => (float)$app->request->post('price'),
		'original_price'  => (float)$app->request->post('original_price'),
		'discount' 		  => trim($app->request->post('discount', '')),
		'express' 		  => trim($app->request->post('express', '')),
		'shop' 			  => trim($app->request->post('shop', '')),
		'shop_url' 		  => trim($app->request->post('shop_url', '')),
		'statu' 		  => $statu? true: false,
		'contents' 		  => $contents,
		'belong' 		  => $belong,
		'likes_counter'   => intval($app->request->post('likes_counter')),
		'dislike_counter' => intval($app->request->post('dislike_counter'))
	);
	$photos       = $app->request->post('photos', array());
	$sizeOfPhotos = $app->request->post('size_of_photos', array());
	foreach($photos as $idx => $photo) {
		$size = explode(',', isset($sizeOfPhotos[$idx]) && $sizeOfPhotos[$idx]? $sizeOfPhotos[$idx]: '');
		$doc['photos'][] = array(
			'url' => $photo,
			'w'   => intval($size[0]),
			'h'   => intval($size[1])
		);
	}
	if(!$doc['title']) {
		SwimAdmin::error('请输入名称。');
	}

	if(!$id) {
		$goods->insert($doc);
	} else {
		$goods->update($doc, $id);
	}
	$app->render('goods/list.html', array(
		'id' => $id,
		'data' => $goods->pagination($app->request->post('__url'))
	));
});

// 删除
$app->post('/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$goods = SwimAdmin::model('goods.main');
	$items = $app->request->post('items', array());
	$goods->deleteById($items);
	$app->view()->renderJSON(array('result' => true));
	// $app->render('goods/list.html', array(
	// 	'data' => $goods->pagination($app->request->post('__url'), 1)
	// ));
});

$app->get('/category.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$cat 	= SwimAdmin::model('goods.category');
	$list 	= $cat->find();
	$app->view()->addGlobal('_MOD', 'adv');
	$app->render('goods/category.html', array(
		'categoryList' => $list
	));
});
// 分类表单
$app->get('/category/form.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_READ);
	$id 	= $app->request->get('id');
	$cat 	= SwimAdmin::model('goods.category');
	$data 	= array();
	if($id) {
		$data = $cat->collection()->findOne(array('_id' => new MongoId($id)));
	}
	if(!isset($data['list_type'])) {
		$data['list_type'] = 1;
	}
	if(!isset($data['sort'])) {
		$data['sort'] = 'create_time';
	}
	if(!isset($data['order'])) {
		$data['order'] = -1;
	}
	$app->render('goods/category-form.html', array(
		'cat' 			=> $data,
		'sortColumns' 	=> $cat->getSortColumns()
	));
});
// 处理分类表单
$app->post('/category/form.html', function() use($app) {
	$id = $app->request->post('id', '');
	SwimAdmin::checkPermission('main-contents', $id? PERM_EDIT: PERM_ADD);

	$cat  = SwimAdmin::model('goods.category');
	$data = array(
		'cover' 	=> trim($app->request->post('cover', '')),
		'name' 		=> trim($app->request->post('name', '')),
		'priority' 	=> intval($app->request->post('priority')),
		'list_type' => intval($app->request->post('list_type')) == 2? 2: 1,
		'status' 	=> intval($app->request->post('status')),
		'sort' 		=> trim($app->request->post('sort')),
		'order' 	=> intval($app->request->post('order'))
	);
	if(!$data['sort']) $data['sort'] = 'create_time';
	if($data['order'] != -1) $data['order'] = 1;
	if(!$data['name']) {
		SwimAdmin::error('请输入分类名称。');
	}
	if(!$id) {
		$cat->insert($data);
	} else {
		$cat->update($data, $id);
	}
	$app->render('goods/category-list.html', array(
		'categoryList' => $cat->find()
	));
});
// 删除分类
$app->post('/category/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$cat 	= SwimAdmin::model('goods.category');
	$items 	= $app->request->post('items', array());
	$cat->deleteById($items);
	$app->render('goods/category-list.html', array(
		'categoryList' => $cat->find()
	));
});
$app->get('/merchant.html', function() use($app) {
	$merchant 	= SwimAdmin::model('goods.merchant');
	$list 	= $merchant->find();
	$app->view()->addGlobal('_MOD', 'adv');
	$app->render('goods/merchant.html', array(
		'merchant' => $list
	));
});
$app->get('/merchant/form.html', function() use($app) {
	$id 	= $app->request->get('id');
	$merchant 	= SwimAdmin::model('goods.merchant');
	$data 	= array();
	if($id) {
		$data = $merchant->collection()->findOne(array('_id' => new MongoId($id)));
	}
	$course 	= SwimAdmin::model('course.main');
	$courses     = $course->find();
	$dataIdents = array();
	if(isset($data['course'])) {
	    foreach($data['course'] as $idt) {
		    $identId = (string)$idt['course_id'];
			$dataIdents[$identId] = $idt['course_name'];
		} 
	}

	$idents2 = array();
	foreach($courses as $ident) {
	    $identId = (string)$ident['_id'];
		$ident['_id'] = $identId;
		$ident['selected'] = isset($dataIdents[$identId]);
		$idents2[] = $ident;
	}
	$app->render('goods/merchant-form.html', array(
		'merchant' => $data,
		'courses'  => $idents2
	));
});
$app->post('/merchant/form.html', function() use($app) {
    $id = trim($app->request->post('id', ''));
	$merchant = SwimAdmin::model('goods.merchant');
	$courses = $app->request->post('courses', array());
	$courselist = array();
	foreach($courses as $course) {
        $tmp = explode(':', $course, 2);        	
	    $courselist[] = array(
		    'course_id' => $tmp[0],
			'course_name' => $tmp[1],
		);
	}
    $data  = array(
	    'name'   => trim($app->request->post('name', '')),
	    'intro'   => trim($app->request->post('intro', '')),
	    'cover'   => trim($app->request->post('merchant-cover', '')),
	    'course'    => $courselist,
		'priority'   => trim($app->request->post('priority', '')),
		'status' => intval($app->request->post('status')),
		'create_time' => time()
	);
    
    if(!$data['name']) {
	    SwimAdmin::error('请输入专栏名。');
	}
    
    if(!$id) {
	    $merchant->insert($data);
	} else {
	    $merchant->update($data, $id);
	}

    $app->render('goods/merchant-list.html', array(
	    'merchant' => $merchant->find()
	));	
});
$app->post('/merchant/delete.html', function() use($app) {
	$merchant 	= SwimAdmin::model('goods.merchant');
	$items 	= $app->request->post('items', array());
	$merchant->deleteById($items);
	$app->render('goods/merchant-list.html', array(
		'merchant' => $merchant->find()
	));
});

$app->get('/moov/list.html', function() use($app){
    $Moov = SwimAdmin::model('goods.moov');
	$data = $Moov->pagination();
	$app->view()->addGlobal('_MOD', 'adv');
	$app->render('goods/moov-order-index.html', array(
	    'data' => $data
	));
});

$app->get('/moov/form.html', function() use($app){
	$app->render('goods/moov-order-form.html');
});

$app->post('/moov/form.html', function() use($app){
    $orderId = trim($app->request->post('orderId'));

	$Moov = SwimAdmin::model('goods.moov');
    $filters = array(
	    'order_id' => $orderId
	);
	$data = $Moov->findOne($filters);
	if($data) {
	    SwimAdmin::error('订单已存在，不能重复添加订单！');
	}
	$data = array(
	    'order_id' => $orderId,
		'create_time' => time()
	);
	$Moov->insert($data);
	$app->render('goods/moov-order-list.html', array(
        'data' => $Moov->pagination($app->request->post('__url'))	
	));
});

$app->post('/moov/delete.html', function() use($app){
	$Moov 	= SwimAdmin::model('goods.moov');
	$items 	= $app->request->post('items', array());
	$Moov->deleteById($items);
	$app->render('goods/moov-order-list.html', array(
	    'data' => $Moov->pagination()
	));
});
