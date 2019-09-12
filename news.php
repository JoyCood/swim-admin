<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/list.html', function() use($app) {
	$news = SwimAdmin::model('news.main');
	$data = $news->pagination();
	$app->view()->addGlobal('_MOD', 'discover');
	$app->render('news/index.html', array(
		'data'  => $data,
		'promotion' => $app->request->get('promotion'),
		'type'  => $app->request->get('type')
	));
});

// 修改界面
$app->get('/form.html', function() use($app) {
	$id = $app->request->get('id', '');
	$data = array(
		'date' => date('Y-m-d')
	);
	if($id) {
		$news = SwimAdmin::model('news.main');
		$data = $news->collection()->findOne(array('_id' => new MongoId($id)));
	}
	if(!isset($data['contents']) || !$data['contents']) {
		$data['contents'] =array(
			array('text' => '', 'media' => array())
		);
	}
	$category = $news = SwimAdmin::model('news.category');
	$dataCats = array();
	if(isset($data['category'])) {
		foreach($data['category'] as $belong) {
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
	$columnist 	= SwimAdmin::model('news.columnist');
	$columnist = $columnist->find();
	$app->render('news/form.html', array(
		'id' => $id,
		'data' => $data,
		'cats' => $cats,
		'columnist' =>$columnist
	));
});
$app->post('/form.html', function() use($app) {
	$news = SwimAdmin::model('news.main');

	$id = $app->request->post('id', '');
	$cats = $app->request->post('category', array());
	$category = array();
	foreach($cats as $cat) {
		$tmp = explode(':', $cat);
		$cid = array_shift($tmp);
		$cat = join(':', $tmp);
		$category[] = array(
			'cateId' => $cid,
			'cate' => $cat
		);
	}
    $columnist = $app->request->post('columnist', array());
    if($columnist) {
        $tmp = explode(':', $columnist);
        $columnist = array(
            'id'   => $tmp[0],
            'name' => $tmp[1]
        );    
    }
	$date        = strtotime($app->request->post('date'));
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

	if($date)
	{
        $time = date('H:i:s', $date);

        if($time=='00:00:00')
        {
        	$date = mktime(date('H'), date('i'), date('s'), date('n', $date), date('j', $date), date('Y', $date));
        }
    }
    
    $type = trim($app->request->post('type'));
    $prom = intval($app->request->post('promotion', '0'));
	$doc  = array(
		'title' 	   => trim($app->request->post('title')),
		'author' 	   => trim($app->request->post('author')),
		'summary' 	   => trim($app->request->post('summary')),
		'columnist'    => $columnist,
		'type'		   => in_array($type, array('native', 'link'))? $type: 'native',
		'promotion'    => $prom? 1: 0,
		'link' 		   => trim($app->request->post('link')),
		'photos' 	   => array(),
		'date' 		   => $date? $date: time(),
		'icon' 		   => $app->request->post('icon'),
		'res_type' 	   => intval($app->request->post('res_type')),
		'category' 	   => $category,
		'contents' 	   => $contents,
		'video'        => trim($app->request->post('video')),
		'video_time'   => trim($app->request->post('video_time'))
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
	$image        = $app->request->post('cover', '');
	$doc['cover'] = $image;
	// $sizeOfImage  = $app->request->post('size_of_cover', array());
	// $size         = explode(',', $sizeOfImage? $sizeOfImage: '');
	// $doc['cover'] = array(
	// 	'url' => $image,
	// 	'w'   => intval(@$size[0]),
	// 	'h'   => intval(@$size[1])
	// );
	if(!$doc['title']) {
		SwimAdmin::error('请输入标题。');
	}

	if(!$id) {
		$news->insert($doc);
	} else {
		$news->update($doc, $id);
	}
	$app->render('news/list.html', array(
		'id' => $id,
		'data' => $news->pagination($app->request->post('__url'))
	));
});

// 删除
$app->post('/delete.html', function() use($app) {
	$news = SwimAdmin::model('news.main');
	$items 	= $app->request->post('items', array());
	$news->deleteById($items);
	$app->view()->renderJSON(array('result' => true));
});

$app->get('/columnist.html', function() use($app) {
	$columnist 	= SwimAdmin::model('news.columnist');
	$list 	= $columnist->find();
	$app->view()->addGlobal('_MOD', 'discover');
	$app->render('news/columnist.html', array(
		'columnist' => $list
	));
});
$app->get('/columnist/form.html', function() use($app) {
	$id 	= $app->request->get('id');
	$columnist 	= SwimAdmin::model('news.columnist');
	$data 	= array();
	if($id) {
		$data = $columnist->collection()->findOne(array('_id' => new MongoId($id)));
	}
	$app->render('news/columnist-form.html', array(
		'columnist' => $data
	));
});
$app->post('/columnist/form.html', function() use($app) {
    $id = trim($app->request->post('id', ''));
	$columnist = SwimAdmin::model('news.columnist');
    $data  = array(
	    'name'   => trim($app->request->post('name', '')),
	    'cintro'   => trim($app->request->post('cintro', '')),
	    'cover'   => trim($app->request->post('columnist-cover', '')),
	    'auname'   => trim($app->request->post('auname', '')),
		'aintro'   => trim($app->request->post('aintro', '')),
		'icon'   => trim($app->request->post('columnist-icon')),
		'priority'   => trim($app->request->post('priority', '')),
		'status' => intval($app->request->post('status')),
		'create_time' => time()
	);
    
    if(!$data['name']) {
	    SwimAdmin::error('请输入专栏名。');
	}
    
    if(!$id) {
	    $columnist->insert($data);
	} else {
	    $columnist->update($data, $id);
	}

    $app->render('news/columnist-list.html', array(
	    'columnist' => $columnist->find()
	));	
});
$app->post('/columnist/delete.html', function() use($app) {
	$columnist 	= SwimAdmin::model('news.columnist');
	$items 	= $app->request->post('items', array());
	$columnist->deleteById($items);
	$app->render('news/columnist-list.html', array(
		'columnist' => $columnist->find()
	));
});
$app->get('/category.html', function() use($app) {
	$cat 	= SwimAdmin::model('news.category');
	$list 	= $cat->find();
	$app->view()->addGlobal('_MOD', 'discover');
	$app->render('news/category.html', array(
		'categoryList' => $list
	));
});
$app->get('/category/form.html', function() use($app) {
	$id 	= $app->request->get('id');
	$cat	= SwimAdmin::model('news.category');
	$columnist 	= SwimAdmin::model('news.columnist');
	$columnist = $columnist->find();
	$data 	= array();
	if($id) {
		$data = $cat->collection()->findOne(array('_id' => new MongoId($id)));
    }
    $belongCol = array();
    if(isset($data['columnist']) && is_array($data['columnist'])) {
        foreach($data['columnist'] as $item) {
            $colId = (string)$item['id'];
            $belongCol[$colId] = $item['name'];
        }
    }
    
    $col = array();
    foreach($columnist as $item) {
        $colId = (string)$item['_id'];
        $item['_id'] = $colId;        
        $item['selected'] = isset($belongCol[$colId]);
        $col[] = $item;
    }

	$app->render('news/category-form.html', array(
		'cat' => $data,
		'columnist' => $col
	));
});
// 处理分类表单
$app->post('/category/form.html', function() use($app) {
    $id 	= $app->request->post('id', '');
    $columnists = $app->request->post('columnist', array());
    $columnist = array();
    foreach($columnists as $col) {
        $tmp = explode(':', $col);
        $columnist[] = array(
            'id' => $tmp[0],
            'name' => $tmp[1]
        ); 
    }
	$cat 	= SwimAdmin::model('news.category');
	$data 	= array(
		'name' 		=> trim($app->request->post('name', '')),
		'columnist' => $columnist,
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
	$app->render('news/category-list.html', array(
		'categoryList' => $cat->find()
	));
});
// 删除分类
$app->post('/category/delete.html', function() use($app) {
	$cat 	= SwimAdmin::model('news.category');
	$items 	= $app->request->post('items', array());
	$cat->deleteById($items);
	$app->render('news/category-list.html', array(
		'categoryList' => $cat->find()
	));
});
