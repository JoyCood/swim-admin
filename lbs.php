<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

// 附近游泳馆
$app->get('/natatorium.html', function() use($app) {
	$region = $app->request->get('region');
	$natatorium = SwimAdmin::model('natatorium.main');
	$data = $natatorium->pagination();
	$filter = array('status'=>1);
    $regions = SwimAdmin::model('course.region')->find($filter);
	$app->view()->addGlobal('_MOD', 'City');
	$app->view()->addGlobal('_Action', 'index');
    
	$app->render('lbs/natatorium-index.html', array(
		'data' => $data,
		'regions' => $regions,
		'region' => $region
	));
});

// 导入游泳馆信息界面
$app->get('/natatorium/import.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$app->view()->addGlobal('_MOD', 'lbs');
	$app->view()->addGlobal('_Action', 'import');
	$app->render('lbs/natatorium-import.html');
});

// 导入游泳馆信息处理
$app->post('/natatorium/import.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
	$upfile = isset($_FILES['upfile'])? $_FILES['upfile']: null;
	// 判断文件是否有效
	if($upfile && !$upfile['error'] && strpos($upfile['type'], 'text') === 0) {
		// 读取文件内容
		$contents = preg_split('/[\r|\n|\r\n]/', file_get_contents($upfile['tmp_name']));
		$data = array();
		foreach($contents as $row) {
			$item = explode(',', $row);
			if($item) {
				$title = trim($item[0]);
				if($title) {
					// 游泳馆信息
					$data[] = array(
						// 标题
						'title' 	=> $title,
						// 坐标
						'coords' 	=> array(
							'type' 			=> 'Point',
							'coordinates' 	=> array(
								isset($item[1])? $item[1] * 1: null,
								isset($item[2])? $item[2] * 1: null
							)
						),
						// 星级默认为5
						'rates' 	=> 5,
						// 地址
						'tel' 		=> isset($item[3])? trim($item[3]): '',
						'district' 	=> isset($item[4])? trim($item[4]): '',
						'address' 	=> isset($item[5])? trim($item[5]): '',
						// 营业时间
						'open_time' => isset($item[6])? trim($item[6]): null,
						// 价格
						'price' 	=> isset($item[7])? trim($item[7]): null
					);
				}
			}
		}
		if($data) {
			try {
				// 写入DB
				$natatorium = SwimAdmin::model('natatorium.main');
				// $natatorium = SwimAdmin::db('natatorium');
				foreach($data as $item) {
					$natatorium->insert($item);
				}
			} catch(Exception $e) {
				$app->log->error($e);
				$app->view()->renderJSON(array(
					'result' => false,
					'error' => '导入失败，请检查坐标或文件格式是否正确。'
				));
				return;
			}
		}
		$app->view()->renderJSON(array(
			'result' 	=> true,
			'num'		=> count($data) // 返回导入总数
		));
	} else {
		$app->view()->renderJSON(array(
			'result' => false,
			'error' => '您上传了一个无法识别的文件。'
		));
	}
});

// 修改游泳馆信息界面
$app->get('/natatorium/form.html', function() use($app) {
	$id = $app->request->get('id', '');
	$data = array();
	$slted = array();
	$filter = array('status'=>1);
	$regions = SwimAdmin::model('course.region')->find($filter);

	if($id) {
		$natatorium = SwimAdmin::model('natatorium.main');
		$data = $natatorium->collection()->findOne(array('_id' => new MongoId($id)));
	}
	if(isset($data['region'])) {
		$slted[$data['region']['id']] = $data['region']['name'];
	}

	$app->render('lbs/natatorium-form.html', array(
		'id' => $id,
		'slted' => $slted,
		'regions' => $regions,
		'data' => $data
	));
});
// 修改游泳馆信息
$app->post('/natatorium/form.html', function() use($app) {
	$id = $app->request->post('id', '');
	SwimAdmin::checkPermission('main-contents', $id? PERM_EDIT: PERM_ADD);

	$natatorium     = SwimAdmin::model('natatorium.main');
	$data 		    = $app->request->post();
	$region         = $app->request->post('region');
	$photos         = $app->request->post('photos', array());
	$sizeOfPhotos   = $app->request->post('size_of_photos', array());
	if(!$region) {
		SwimAdmin::error("请选择校区");
	}
	$data['photos'] = array();
	foreach($photos as $idx => $photo) {
		$size = explode(',', isset($sizeOfPhotos[$idx]) && $sizeOfPhotos[$idx]? $sizeOfPhotos[$idx]: '');
		$data['photos'][] = array(
			'url' => $photo,
			'w'   => intval($size[0]),
			'h'   => intval($size[1])
		);
	}
	unset($data['size_of_photos']);
	$data['create_time'] = isset($data['create_time'])? strtotime($data['create_time']): '';
    $region = explode('::', $region);
	$data['region'] = array('id' => $region[0], 'name'=>$region[1]);
	if(!$id) {
		$natatorium->insert($data);
	} else {
		$id = $app->request->post('id');
		$natatorium->update($data, $id);
	}
	$data = $natatorium->pagination($app->request->post('__url', ''));
	$app->render('lbs/natatorium-list.html', array(
		'data' => $data
	));
});

// 删除游泳馆信息
$app->post('/natatorium/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$natatorium = SwimAdmin::model('natatorium.main');
	$items = $app->request->post('items');
	$natatorium->deleteById($items);
	$app->view()->renderJSON(array('result' => true));
});



// 附近的教练
$app->get('/teacher.html', function() use($app) {
	$teacher = SwimAdmin::model('teacher.main');
	$data = $teacher->pagination();
	// $data = $teacher->find(Helper::parseQueryString($_SERVER['QUERY_STRING']));

	$app->view()->addGlobal('_MOD', 'City');
	$app->view()->addGlobal('_Action', 'index');

	$app->render('lbs/teacher-index.html', array(
		'data' => $data
	));
});
// 修改教练信息界面
$app->get('/teacher/form.html', function() use($app) {
	$id = $app->request->get('id', '');
	$data = array();
	if($id) {
		$teacher = SwimAdmin::model('teacher.main');
		$data = $teacher->findOneById($id);
	}
	$app->render('lbs/teacher-form.html', array(
		'id' => $id,
		'data' => $data
	));
});

// 修改教练信息
$app->post('/teacher/form.html', function() use($app) {
	$id = $app->request->post('id', '');
	$teacher = SwimAdmin::model('teacher.main');
	$data 	 = $app->request->post();
    $data['photos'] = isset($data['photos']) ? $data['photos'] : array();
	$data['cert'] 		 = isset($data['cert']) && $data['cert']? 1: 0;
	$data['create_time'] = isset($data['create_time'])? strtotime($data['create_time']): '';
    $photos = array();
    $sizeOfPhotos = isset($data['size_of_photos'])? $data['size_of_photos'] : array();
    foreach($data['photos'] as $idx => $photo) {
        $size = explode(',', empty($sizeOfPhotos[$idx])? '': $sizeOfPhotos[$idx]);
        $photos[] = array(
            'url' => $photo,
            'w'   => empty($size[0])? 0: intval($size[0]),
            'h'   => empty($size[1])? 0: intval($size[1])
        );
    }
    $data['photos'] = $photos;
	if(!$id) {
		SwimAdmin::checkPermission('main-contents', PERM_ADD);
		$teacher->insert($data);
	} else {
		SwimAdmin::checkPermission('main-contents', PERM_EDIT);
		$id = $app->request->post('id');
		$teacher->update($data, $id);
	}
	$data = $teacher->pagination($app->request->post('_url', ''));
	$app->render('lbs/teacher-list.html', array(
		'data' => $data
	));
});

// 删除教练信息
$app->post('/teacher/delete.html', function() use($app) {
	SwimAdmin::checkPermission('main-contents', PERM_DELETE);
	$teacher = SwimAdmin::model('teacher.main');
	$items = $app->request->post('items');
	$teacher->deleteById($items);
	$data = $teacher->find(Helper::parseQueryString($app->request->post('_url', '')));
	$app->view()->renderJSON(array('result' => true));
});

// 协会信息
$app->get('/club.html', function() use($app) {
	$club = SwimAdmin::model('club.main');
	$app->view()->addGlobal('_MOD', 'City');
	$app->view()->addGlobal('_Action', 'index');
	$data  = $club->pagination();
	$app->render('lbs/club.html', array(
		'data' => $data,
	));
});

// 修改协会信息
$app->get('/club-form.html', function() use($app) {
	$id = $app->request->get('id', '');
	$app->view()->addGlobal('_MOD', 'City');
	$data = array();
	$area = SwimAdmin::model('course.area');
	$club_service = SwimAdmin::model('club.service');
	$slted = array(
        'city'       => array(),
    );

	if($id) {
		$club = SwimAdmin::model('club.main');
		$data = $club->findOne(array('_id' => new MongoId($id)));
		$club_service = $club_service->findOneById($id);
	}
	if(!isset($data['contents']) || !$data['contents']) {
        $data['contents'] = array(
            array(
					'title' => '',
					'text'  => array('')
				)
        );
    }
	if(isset($data['service_area'])) {
            foreach($data['service_area'] as $city) {
                $slted['city'][$city['city_id']] = $city['city_name'];
            }
    }
	$app->render('lbs/club-form.html', array(
		'club' => $data,
		'club_service' => $club_service,
		'slted'       => $slted,
		'cities'      => $area->find(array('statu' => 1)),
	));
});

$app->post('/club-form.html', function() use($app) {
    $id = trim($app->request->post('id', ''));
   	SwimAdmin::checkPermission('main-contents', PERM_EDIT);

	$club = SwimAdmin::model('club.main');
	$idents = $app->request->post('service_area', array());
	$identList = array();
	foreach($idents as $ident) {
        $exp = explode('::', $ident, 2);        	
	    $identList[] = array(
		    'city_id'   => $exp[0],
            'city_name' => $exp[1]
		);
	}
	$contents = (array)$app->request->post('contents', array());
		$rows     = array();
		if(isset($contents['text']) && is_array($contents['text'])) {
			foreach($contents['text'] as $idx => $text) {
				$title = isset($contents['title']) && $contents['title'][$idx]? $contents['title'][$idx]: '';
				if($title || $text) {
					$row   = array(
						'title' => $title,
						'text'  => array()
					);
					if(is_array($text)) {
						foreach($text as $val) {
							$row['text'][] = $val;
						}
					}
					array_push($rows, $row);
				}
			}
		}
	$idents = $identList;
    $data  = array(
	    'name'   => trim($app->request->post('name', '')),
	    'icon'   => trim($app->request->post('club-icon')),
		'summary'   => trim($app->request->post('summary', '')),
		'status' => intval($app->request->post('status')),
		'contents' =>  $rows,
		'service_area'    => $idents
	);
   
    if(!$id) {
	    $club->insert($data);
	} else {
	    $club->update($data, $id);
	}$data = $club->pagination($app->request->post('__url'));
	
    $app->render('lbs/club-list.html', array(
	    'data' => $data,
	));	
});

$app->post('/club-delete.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_EDIT);
    $club = SwimAdmin::model('club.main');
	$items = $app->request->post('items', array());
	$club->deleteById($items);
	$data = $club->pagination($app->request->post('__url'));
	$app->render('lbs/club-list.html', array(
	    'data' => $data,
	));
});

// 修改协会服务信息
$app->get('/club-service-form.html', function() use($app) {
	$id = $app->request->get('vid', '');
	$data = array();
	$club_service = SwimAdmin::model('club.service');
	$region = SwimAdmin::model('course.region');
	$natatorium = SwimAdmin::model('natatorium.main');
	$club_id   = $app->request->get('club_id');
	$slted = array(
        'natatorium' => array(),
    );
	if($id) {
		$data = $club_service->findOne(array('_id' => new MongoId($id)));
	}
	if(!isset($data['contents']) || !$data['contents']) {
        $data['contents'] = array(
            array('text' => '', 'media' => array())
        );
    }
    if(isset($data['space'])) {
	    if($data['space']['natatorium']) {
	            foreach($data['space']['natatorium'] as $nata) {
	                $slted['natatorium'][$nata['id']] = $nata['name'];
	            }
	        }
	}
	$app->render('lbs/club-service-form.html', array(
		'data' => $data,
		'club_id'   => $club_id,
		'slted'       => $slted,
		'regions'     => $region->find(array('status' => 1)),
		'natatoriums' => $natatorium->find(),
	));
});

$app->post('/club-service-form.html', function() use($app) {
    $id = trim($app->request->post('id'));
   	SwimAdmin::checkPermission('main-contents', PERM_EDIT);

	$club_service = SwimAdmin::model('club.service');
   	$doc = array(
        'title'         => trim($app->request->post('title')),
        'type'         => trim($app->request->post('type')),
        'icon'   => trim($app->request->post('club-icon')),
        'club_id'        => trim($app->request->post('club-id')),
        'price'         => trim($app->request->post('price')) * 1,
        'price_subject' => trim($app->request->post('price_subject')),
        'training_time' => trim($app->request->post('training_time')),
        'schedule'      => trim($app->request->post('schedule')),
        'teacher'       => trim($app->request->post('teacher')),
        'teacher_extra' => trim($app->request->post('teacher_extra')),
        'teacher_phone' => trim($app->request->post('teacher_phone')),
        'score'         => trim($app->request->post('score')) * 1,
        'cover'         => trim($app->request->post('cover')),
        'cover_big'     => trim($app->request->post('cover_big')),
        'cover_small'   => trim($app->request->post('cover_small')),
        'statu'         => trim($app->request->post('status')) * 1,
        'priority'      => trim($app->request->post('priority')) * 1,
        'extra'         => array(),
        'contents'      => array(),
        'photos'        => array(),
        'space'         => array(
            'region'       => array(),
            'natatorium'   => array()
        )
    );
    $photos = $app->request->post('photos', array());
    $sizeOfPhotos = $app->request->post('size_of_photos', array());
    foreach($photos as $idx => $photo) {
        $size = explode(',', isset($sizeOfPhotos[$idx]) && $sizeOfPhotos[$idx]? $sizeOfPhotos[$idx]: '');
        $doc['photos'][] = array(
            'url' => $photo,
            'w'   => intval($size[0]),
            'h'   => intval($size[1])
        );
    }

    $extras = explode(';', str_replace('；', ';', trim($app->request->post('extra'))));
    foreach($extras as $extra) {
        $extra = trim($extra);
        if($extra) {
            $doc['extra'][] = $extra;
        }
    }
    $region = trim($app->request->post('region'));
    if($region) {
        $exp = explode('::', $region);
        $doc['space']['region'] = array(
            'id'   => $exp[0],
            'name' => $exp[1]
        );
    }
    $natatoriums = $app->request->post('natatorium');
    if($natatoriums) {
        foreach((array)$natatoriums as $natatorium) {
            $natatorium = trim($natatorium);
            if($natatorium) {
                $exp = explode('::', $natatorium);
                $doc['space']['natatorium'][] = array(
                    'id'   => $exp[0],
                    'name' => $exp[1]
                );
            }
        }
    }

    $contents = array();
    $texts = $app->request->post('content', array());
    $photos = $app->request->post('contentThumbs', array());
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
    $doc['contents'] = $contents;

    if(!$doc['title']) {
        SwimAdmin::error('请输入标题。');
    }
    if(!$id) {
        SwimAdmin::checkPermission('main-contents', PERM_ADD);
        $club_service->insert($doc);
    } else {
        SwimAdmin::checkPermission('main-contents', PERM_EDIT);
        $club_service->update($doc, $id);
    }
    $app->render('lbs/club-service-list.html', array(
	    'clubServiceList' => $club_service->find()
	));	
});
//删除协会服务信息
$app->post('/club-service-delete.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_EDIT);
    $club_service = SwimAdmin::model('club.service');
	$items = $app->request->post('vid', '');
	$club_service->deleteById($items);
	$app->render('lbs/club-service-list.html', array(
	    'club_service' => $club_service->find()
	));
});

// 协会公告信息
$app->get('/club-notice.html', function() use($app) {
	$club_service = SwimAdmin::model('club.notice');
	$app->view()->addGlobal('_MOD', 'City');
	$app->view()->addGlobal('_Action', 'notice');
	$club_id = $app->request->get('id', '');
	$data  = $club_service->pagination();
	$app->render('lbs/club-notice.html', array(
		'data' => $data,
		'club_id' =>$club_id,
	));
});

// 修改协会公告信息
$app->get('/club-notice-form.html', function() use($app) {
	$id = $app->request->get('vid', '');
	$club_id   = $app->request->get('club_id');
	$data = array();
	if($id) {
		$club_notice = SwimAdmin::model('club.notice');
		$data = $club_notice->findOne(array('_id' => new MongoId($id)));
	}
	if(!isset($data['contents']) || !$data['contents']) {
        $data['contents'] = array(
            array('text' => '', 'media' => array())
        );
    }
	$app->render('lbs/club-notice-form.html', array(
		'club' => $data,
		'club_id' => $club_id,
	));
});

$app->post('/club-notice-form.html', function() use($app) {
    $id = trim($app->request->post('id', ''));
   	SwimAdmin::checkPermission('main-contents', PERM_EDIT);
   	$time        = strtotime($app->request->post('create_time'));
	$club_notice = SwimAdmin::model('club.notice');
	$contents = array();
    $texts = $app->request->post('content', array());
    $photos = $app->request->post('contentThumbs', array());
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
    $doc['contents'] = $contents;
    $data  = array(
    	'title'   => trim($app->request->post('title', '')),
    	'club_id'        => trim($app->request->post('club-id')),
    	'content_type'   => trim($app->request->post('content_type', '')),
    	'link'   => trim($app->request->post('link', '')),
	    'icon'   => trim($app->request->post('club-icon')),
	    'create_time'		=> $time? $time: time(),
	    'contents'      => $contents,
		'status' => intval($app->request->post('status')),
	);
    if(!$data['title']) {
	    SwimAdmin::error('请输入协会名称。');
	}
	if(!$data['link']) {
	    SwimAdmin::error('请输入链接。');
	}
    if(!$id) {
	    $club_notice->insert($data);
	} else {
	    $club_notice->update($data, $id);
	}
	$data = $club_notice->pagination($app->request->post('__url'));
    $app->render('lbs/club-notice-list.html', array(
	    'data' => $data,
	));	
});

//删除协会公告列表
$app->post('/club-notice-delete.html', function() use($app) {
    SwimAdmin::checkPermission('main-contents', PERM_EDIT);
    $club_notice = SwimAdmin::model('club.notice');
	$items = $app->request->post('items', array());
	$club_notice->deleteById($items);
	$data = $club_notice->pagination($app->request->post('__url'));
	$app->render('lbs/club-notice-list.html', array(
	    'data' => $data,
	));
});

