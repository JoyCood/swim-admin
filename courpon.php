<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');
require_once LIB_DIR . 'PHPExcel/Classes/PHPExcel.php';

$app->post('/courses',function() use($app){
    $id = $app->request->post('id');
    $course = SwimAdmin::model('course.main');
    $matcher = array(
        'belong.cate_id' => $id,
        'statu'=>1,
//        'is_tuangou'=>0,
        'source'=>array('$exists'=>false)
    );
    $result = $course->find($matcher);
    $body = Config_Statu_Code::$code['ok_get_data_success'];
    $body['data'] = iterator_to_array($result,false);
    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($body));
});

$app->post('/category',function() use($app){
    $id = $app->request->post('id');
    $result = array();
    if($id==1){
        $category = SwimAdmin::model('course.category');
        $result = $category->find(array('status' => 1));
    }elseif($id==2){
        $category = SwimAdmin::model('mall.category');
        $result = $category->find(array('status' => 1));
    }
    $body = Config_Statu_Code::$code['ok_get_data_success'];
    if($id==0){
        $body['data'] = $result;
    }else{
        $body['data'] = iterator_to_array($result,false);
    }
    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($body));
});


$app->post('/get_chose',function() use($app){
    $id = $app->request->post('id');
    $result_ids = array();
    if($id){
        $news = SwimAdmin::model('courpon.main');
        $data = $news->collection()->findOne(array('_id' => new MongoId($id)));

        if(isset($data['goods_ids'])) {
            foreach($data['goods_ids'] as $belong) {
                if(isset($belong['goods_id'])) {
                    $result_ids[$belong['goods_id']] = $belong['goods_name'];
                }
            }
        }
    }


    $body = Config_Statu_Code::$code['ok_get_data_success'];
    $body['data'] = $result_ids;

    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($body));
});



//修改界面
$app->get('/form.html', function() use($app) {
    $id = $app->request->get('id', '');
    if(!$id) {
        $data = array(
            'date' => date('Y-m-d'),
            'expire_date'=>date("Y-m-d",strtotime("+7 day")),
            'status'=>1,
            'is_all'=>1,
        );
    }else{
        $news = SwimAdmin::model('courpon.main');
        $data = $news->collection()->findOne(array('_id' => new MongoId($id)));

        $data['date'] = date('Y-m-d',$data['effect_time']);
        $data['expire_date'] = date('Y-m-d',$data['expire_time']);
    }

    //指定商品类型  目前就培训和商城
    $coltypes = Config::$goods_coltypes['goods'];
    $app->render('courpon/form.html', array(
        'id' => $id,
        'data' => $data,
        'coltypes'=>$coltypes,
    ));
});


//新增或修改
$app->post('/form.html', function() use($app) {
    $courpons = SwimAdmin::model('courpon.main');
    $id = $app->request->post('id', '');
    $doc  = array(
        'title' 	       => trim($app->request->post('title')),
        'coupon_val' 	   => doubleval($app->request->post('coupon_val')),
        'coupon_type' 	   => intval($app->request->post('coupon_type')),
        'contents' 	       => trim($app->request->post('contents')),
        'coupon_total' 	   => intval($app->request->post('coupon_total')),
        'failure_remind'   => $app->request->post('failure_remind'),
        'effect_time' 	   => strtotime($app->request->post('effect_time')),
        'expire_time' 	   => strtotime($app->request->post('expire_time')),
        'is_condition' 	   => intval($app->request->post('is_condition')),
        'condition_value'  => doubleval($app->request->post('condition_value')),
        'is_all' 	       => intval($app->request->post('is_all')),
        'target_type' 	   => intval($app->request->post('target_type')),
        'target_category'  => $app->request->post('target_category'),
        'restriction'      => intval($app->request->post('restriction')),
        'restrict_value'      => intval($app->request->post('restrict_value')),
        'restrict_nums'      => intval($app->request->post('restrict_nums')),
        'get_expire_time'  => intval($app->request->post('get_expire_time')),
        'status'           => intval($app->request->post('status')),
        'cover'         => trim($app->request->post('cover')),
    );

    $goods_info = $app->request->post('goods_ids');
    $doc['goods_ids'] = array();
    if($goods_info && isset($goods_info)){
        foreach($goods_info as $val) {
            if($val) {
                $exp = explode(',', $val);
                if($exp){
                    $doc['goods_ids'][] = array(
                        'goods_id'   => $exp[0],
                        'goods_name' => $exp[1]
                    );
                }
            }
        }
    }

    if(!$doc['title']) {
        SwimAdmin::error('请输入名称。');
    }

    if(!$doc['coupon_val']) {
        SwimAdmin::error('请输入优惠金额。');
    }

    if(!$doc['coupon_total']) {
        SwimAdmin::error('请输入总数量。');
    }

    if($doc['is_condition']==1 && !$doc['condition_value']) {
        SwimAdmin::error('请输入使用限制条件。');
    }

    if(!$doc['contents']) {
        SwimAdmin::error('请输入使用说明。');
    }

    if($doc['restriction']==1 && !$doc['restrict_value']) {
        SwimAdmin::error('请输入领取限制张数。');
    }

    if($doc['restriction']==2 && !$doc['restrict_nums']) {
        SwimAdmin::error('请输入领取限制次数。');
    }

    if(!$id) {
        $doc['get_coupon_nums'] = 0;
        $doc['coupon_stock'] = $doc['coupon_total'];
        $res = $courpons->insert($doc);
    } else {
        $doc['coupon_total'] =  intval($app->request->post('coupon_total'))+intval($app->request->post('add_stock'));
        $doc['coupon_stock'] =  intval($app->request->post('coupon_stock'))+intval($app->request->post('add_stock'));
        $courpons->update($doc, $id);
    }

    $app->render('courpon/list.html', array(
        'id' => $id,
        'data' => $courpons->pagination($app->request->post('__url'))
    ));
});

//列表
$app->get('/list.html', function() use($app) {
    SwimAdmin::checkPermission('courpon', PERM_READ);
    $courpons = SwimAdmin::model('courpon.main');
    $status     = $app->request()->get('status');
    $coupon_type    = $app->request()->get('coupon_type');
    $data          = $courpons->pagination();

    $app->view()->addGlobal('_MOD', 'courpon');

	$app->render('courpon/index.html', array(
        'data'           => $data,
        'status'=>$status,
        'coupon_type'=>$coupon_type
	));
});


//详情
$app->get('/detail.html', function() use($app){
   SwimAdmin::checkPermission('courpon', PERM_READ);
   $id     = $app->request()->get('id');
   $Courpons = SwimAdmin::model('courpon.main');
   try{
       $courpon_info = $Courpons->findOneById($id);
       $courpon_info['effect_time'] = date('Y-m-d',$courpon_info['effect_time']);
       $courpon_info['expire_time'] = date('Y-m-d',$courpon_info['expire_time']);
    } catch(Exception $e) {
       $courpon_info = array();
    }
    if($app->request->isAjax()) {
        $app->render('courpon/detail-form.html', array(
            'courpon' => $courpon_info
        ));
    } else {
       $app->view()->addGlobal('_MOD', 'courpon');
       $app->render('courpon/detail.html', array(
            'courpon'   => $courpon_info,
       ));
    }
});


// 删除
$app->post('/delete.html', function() use($app) {
    $news = SwimAdmin::model('courpon.main');
    $items 	= $app->request->post('items', array());
    $news->deleteById($items);
    $app->view()->renderJSON(array('result' => true));
});