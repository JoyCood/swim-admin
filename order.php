<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');
require_once LIB_DIR . 'PHPExcel/Classes/PHPExcel.php';

$app->get('/natatorium.html', function() use($app) {
    $region = $app->request->get('region');
    $Natatorium = SwimAdmin::model('natatorium.main');
    $filter = array('region.id' => $region);
    $projection = array('title'=>1);
    $cursor = $Natatorium->find($filter, $projection);
    $data = array();
    foreach($cursor as $item) {
        $data[] = array(
            'id' => (string)$item['_id'],
            'title' => $item['title']
        );
    }
    $app->view()->renderJSON($data);
});

$app->get('/form.html', function() use($app) {
    $Course = SwimAdmin::model('course.main');
    $Region = SwimAdmin::model('course.region');
    $Order  = SwimAdmin::model('course.order');
    $app->render('order/form.html', array(
        'property' => $Course->property,
        'region'   => $Region->find(),
        'course'   => $Course->find()
    ));
});

//保存订单
$app->post('/form.html', function() use($app) {
    $Order = SwimAdmin::model('course.order');
    $orderId     = trim($app->request->post('orderId'));
    $channel     = trim($app->request->post('channel'));
    $course      = explode('::', $app->request->post('course'));
    $guige       = explode('::', $app->request->post('guige'));
    $natatorium  = explode('::', $app->request->post('natatorium'));
    $xiaoqu      = explode('::', $app->request->post('xiaoqu'));
    $zhonglei    = explode('::', $app->request->post('zhonglei'));
    $price       = trim($app->request->post('price', 0));
    $quantity    = trim($app->request->post('quantity'))*1;
    $payTime     = strtotime($app->request->post('payTime'));
    $payMethod   = trim($app->request->post('payMethod'));
    $demanded = array(
        'guige'         => trim($guige[0]),
        'natatorium'    => trim($natatorium[1]),
        'natatorium_id' => trim($natatorium[0]), 
        'xiaoqu'        => trim($xiaoqu[0]), 
        'zhonglei'      => trim($zhonglei[0]),
        'phone'         => trim($app->request->post('phone')),
        'name'          => trim($app->request->post('name')),
        'xiaoquTitle'   => trim($xiaoqu[1]),
        'guigeTitle'    => trim($guige[1]),
        'zhongleiTitle' => trim($zhonglei[1])
    );

    $doc = array(
        'user_id'               => '0', 
        'course_id'             => $course[0],
        'order_id'              => "$orderId",
        'channel'               => $channel,
        'price'                 => "$price",
        'total_fee'             => $price * $quantity,
        'quantity'              => $quantity*1, 
        'discount'              => 0,
        'demanded'              => $demanded,
        'order_status'          => $Order::PAY_STATUS_SUCESS, //important
        'train_status'          => $Order::TRAIN_STATUS_PROCESSING, //important
        'buyer_process_status'  => $Order::BUYER_PROCESS_STATUS_PAY_SUCESS, //important
        'seller_process_status' => $Order::SELLER_PROCESS_STATUS_NORMAL, //important
        'order_expired_time'    => time() + (30*60),
        'order_available_time'  => time() + (90 * 24 * 60 * 60), //课程过期时间
        'pay_time'              => $payTime,
        'comment_status'        => 0,
        'platform'              => $payMethod,
        'ask_refund_time'       => 0, //important
        'refund_no'             => 0, //important
        'refund_time'           => 0, //important
        'refund_fee'            => 0, //important
        'code'                  => Helper::mkrand(),
        'teacher_id'            => 0,
        'teacher_name'          => 0,
        'teacher_phone'         => 0,
        'type'                  => 'course',
        'teacher_type'          => 0,
        'active_time'           => 0,
        'create_time'           => time(),
        'modified_time'         => 0,
        'allocation_time'       => 0,
        'finish_time'           => 0,
        'operator'              => $_SESSION[S_USER],
        'pay_result'            => array(),
        'refund_result'         => 0,
        'course'                => array('title'=>$course[1]),
        'import'                => 1
    );
    $Order->insert($doc);
    $data = $Order->pagination();

    $app->view()->addGlobal('_MOD', 'local');
	$app->render('order/index.html', array(
        'data' => $data,
    ));
});

//批量导入订单
$app->post('/import.html', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_ADD);
	$Course = SwimAdmin::model('course.main');
	$Order = SwimAdmin::model('course.order');
	$Natatorium = SwimAdmin::model('natatorium.main');
	$Member = SwimAdmin::model('member.main');
	$Teacher    = SwimAdmin::model('teacher.main');
    $PHPExcel = new PHPExcel();
    $upfile = isset($_FILES['upfile'])? $_FILES['upfile']: null;  
    if(!$upfile || $upfile['error']) {
        $app->view()->renderJSON(array(
            'result' => false,
            'error'  => '你上传了一个无法识别的文件。' 
        ));
    }
    $fileType = PHPExcel_IOFactory::identify($upfile['tmp_name']);
    $reader   = PHPExcel_IOFactory::createReader($fileType);
    $PHPExcel = $reader->load($upfile['tmp_name']); 
    $sheet = $PHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $orders   = array();
    for($row=2; $row<=$highestRow; $row++) {
        $cell = "A{$row}:{$highestColumn}{$row}"; 
        $item = $sheet->rangeToArray($cell, NULL, TRUE, FALSE);
        $item = $item[0];
		$filters = array(
			'region.name' => $item[14], 
			'title' => trim($item[15])
		);
        $natatorium = $Natatorium->findOne($filters);
		if(!$natatorium) {
		    $app->view()->renderJSON(array(
			    'result' => false,
				'error'  => "O:{$row}的校区或P:{$row}的泳馆不存在"
			));
			return;
		}
	    $filters = array('phone' => "{$item[17]}", 'type'=>1); 
		$member = $Member->findOne($filters);
		if(!$member) {
		    $app->view()->renderJSON(array(
			    'result' => false,
				'error' => "R:{$row}教练不存在"
			));
			return;
		}
		$filters = array('member_id'=> (string)$member['_id']);
		$teacher = $Teacher->findOne($filters);
		if(!$teacher) {
		    $app->view()->renderJSON(array(
			    'result' => false,
				'error' => "R:{$row}教练不存在"
			));
			return;
		}
		$guige = false;
        foreach($Course->property as $p){
	        if($p['name'] == '规格'){
		        $guige = array_search($item['6'], $p['values']);	
				break;
			}	
		}
		if(!$guige) {
		    $app->view()->renderJSON(array(
			    'result' => false,
				'error' => "G:{$row}规格不存在"
			));
			return;
		}
        $demanded = array(
            'guige' => $guige, //规格
            'natatorium' => $natatorium['title'], //场馆-
            'natatorium_id' => (string)$natatorium['_id'], //场馆id-
            'xiaoqu' => $natatorium['region']['id'], //校区-
            'zhonglei' => '1', //种类
            'phone' => "$item[5]", //课户电话-
            'name'  => "$item[4]", //客户姓名-
            'xiaoquTitle' => $natatorium['region']['name'], //校区名称-
            'guigeTitle'  => $item['6'], //规格名称-
            'zhongleiTitle' => ''
        );
        $payMethod = array(
            '微信'   => 'weixin', 
            '支付宝' => 'alipay',
            '其它'   => 'other'
        );
        $platform = trim($item[11]);
        $platform = isset($payMethod[$platform]) ? $payMethod[$platform] : 'other';
		$courseTypes = array('私教'=>1, '普通'=>2);
		if(!isset($courseTypes[$item['3']])){
		    $app->view()->renderJSON(array(
			    'result' => false,
				'error'  => "D:{$row}课程类型不存在"
			));
			break;
		}
		$courseType = $courseTypes[$item['3']];
        $orders[] = array(
            'user_id'               => '0',
            'course_id'             => '0',
            'order_id'              => "$item[0]", //订单号-
            'price'                 => ($item[8]*1), //单价-
            'channel'               => $item[1], //来源-
            'total_fee'             => ($item[10]*1), //实付金额-
            'quantity'              => ($item[7]*1), //数量-
            'discount'              => ($item[9]*1), //优惠金额-
            'demanded'              => $demanded,
            'order_status'          => $Order::PAY_STATUS_SUCESS,
            'train_status'          => $Order::TRAIN_STATUS_PROCESSING,
            'buyer_process_status'  => $Order::BUYER_PROCESS_STATUS_PAY_SUCESS,
            'seller_process_status' => $Order::SELLER_PROCESS_STATUS_NORMAL,
            'order_expired_time'    => time() + (30 * 60), //30分钟有效期
            'order_available_time'  => time() + (90 * 24 * 60 * 60), //课程过期时间
            'pay_time'              => strtotime($item[11]), //下单时间-
            'comment_status'        => $Order::COMMENT_STATUS_NORMAL, 
            'platform'              => $platform, //支付方式
            'ask_refund_time'       => 0, //请求退款时间
            'refund_no'             => 0, //退款单号
            'refund_time'           => 0, //退款时间
            'refund_fee'            => 0, //退款金额
            'code'                  => Helper::mkrand(), //验证码
            'teacher_id'            => (string)$teacher['_id'],
            'teacher_name'          => (string)$teacher['nick'],
            'teacher_phone'         => (string)$teacher['phone'], 
			'teacher_type'          => 1,
            'type'                  => 'course', //订单类型  
            'active_time'           => 0, //订单激活时间       
            'create_time'           => time(),
            'modified_time'         => time(),  //订单修改时间
            'allocation_time'       => time(),  //分配教练的时间
            'finish_time'           => 0,  //毕业时间
            'operator'              => $_SESSION[S_USER],
            'pay_result'            => array(),
            'refund_result'         => 0,
            'course'                => array('title'=>$item[2], 'type'=>$courseType, 'training_step' => $item[13]*1),
            'import'                => 1,
            'customer_notes'        => "$item[12]", //客户留言备注-
        );
    }
    
    SwimAdmin::model('course.order')->batchInsert($orders);; 

    $app->view()->renderJSON(array(
        'result' => true,
        'num'    => count($orders)
    ));
});

//批量导出订单
$app->get('/export.html', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_READ);
    $Order = SwimAdmin::model('course.order');
    $data  = $Order->pagination();
	$courseTypes = array(1=>'私教', 2=>'普通');

    $app->view()->addGlobal('_MOD', 'local');
    $PHPExcel = new PHPExcel();
    $PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '订单号')
        ->setCellValue('B1', '来源')
        ->setCellValue('C1', '商品名称')
        ->setCellValue('D1', '课程类型')
        ->setCellValue('E1', '用户姓名')
        ->setCellValue('F1', '联系方式')
        ->setCellValue('G1', '规格')
        ->setCellValue('H1', '件数')
        ->setCellValue('I1', '单价（元）')
		->setCellValue('J1', '优惠金额')
		->setCellValue('K1', '实付金额（元）')
        ->setCellValue('L1', '用户留言');
    $row = 2;
    foreach($data['items'] as $item) {
        $demanded = $item['demanded'];
        $guigeTitle = empty($demanded['guigeTitle'])? '-': $demanded['guigeTitle'];
		if(empty($item['course']['type'])) {
		    $courseType = '-';
		}else{
		    $courseType = isset($courseTypes[$item['course']['type']])? $courseTypes[$item['course']['type']]: '-';
		}
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit("A{$row}", $item['order_id'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit("B{$row}", isset($item['channel'])? $item['channel']: 'app')
            ->setCellValueExplicit("C{$row}", $item['course']['title'])
            ->setCellValueExplicit("D{$row}", $courseType)
            ->setCellValueExplicit("E{$row}", $demanded['name'])
            ->setCellValueExplicit("F{$row}", $demanded['phone'])
            ->setCellValueExplicit("G{$row}", $guigeTitle)
			->setCellValueExplicit("H{$row}", $item['quantity'])
			->setCellValueExplicit("I{$row}", $item['price'])
			->setCellValueExplicit("J{$row}", $item['discount'])
			->setCellValueExplicit("K{$row}", $item['total_fee'])
            ->setCellValueExplicit("L{$row}", isset($item['customer_notes'])? $item['customer_notes'] : '-');
        $row++;
    }
    $filename = date('YmdHis', time());
    header("Content-Type: application/vdn.ms-excel");
    header("Content-Disposition: attachment;filename=\"7swimOrder_{$filename}.xls\"");
    header('Cache-Control: max-age=0');
    $writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
    $writer->save('php://output');
});
/*
$app->get('/delete.html', function() use($app) {
    $id    = trim($app->request->get('id'));
    $Order = SwimAdmin::model('course.order');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/delete-form.html', array(
        'order' => $order
    ));
});
$app->post('/delete', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_DELETE);
    $orderId = trim($app->request->post('orderId'));
    $Order = SwimAdmin::model('course.order');
    $filter = array('order_id' => $orderId); 
    $Order->delete($filter);
    
    $data = $Order->pagination($app->request()->post('__url'));
    $app->render('order/list.html', array(
        'data' => $data
    ));
});
 */

//订单列表
$app->get('/list.html', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_READ);
    $order         = SwimAdmin::model('course.order');
    $query         = $app->request->get('query');
    $orderStatus   = $app->request->get('order_status');
    $trainStatus   = $app->request->get('train_status');
    $processStatus = $app->request->get('process_status');
    $phone         = $app->request->get('phone');
    $name          = $app->request->get('name');
    $data          = $order->pagination();
    $app->view()->addGlobal('_MOD', 'local');

	$app->render('order/index.html', array(
        'data'           => $data,
        'order_status'   => $orderStatus,
        'train_status'   => $trainStatus,
        'process_status' => $processStatus,
        'query'          => $query,
        'phone'          => $phone,
        'name'           => $name
	));
});

$app->get('/finance.html', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_FINANCE_READ);
	$region = trim($app->request->get('region'));
	$natatorium = trim($app->request->get('natatorium', '-'));
	$teacher = $app->request->get('teacher');
	$teacher_phone = $app->request->get('teacher_phone');
	$start = $app->request->get('start');
	$end   = $app->request->get('end');
	$balance = $app->request->get('balance', 0)*1;
	$natatoriumData = array();

	if($region && $region != '-') {
		$Natatorium = SwimAdmin::model('natatorium.main');
		$filter = array('region.id' => $region);
		$projection = array('title'=>1);
		$natatoriumData = $Natatorium->find($filter, $projection);
	}
    $regions = SwimAdmin::model('course.region')->find();
    $teachers = SwimAdmin::model('teacher.main')->find();
    $data = SwimAdmin::model('finance.main')->pagination();
    $app->render('order/finance.html', array(
        'data' => $data,
        'regions' => $regions,
        'teachers' => $teachers, 
		'regionId' => $region,
		'natatoriumId' => $natatorium,
		'natatoriumData' => $natatoriumData,
		'teacherId' => $teacher,
		'teacher_phone' => $teacher_phone,
		'start' => $start,
		'end' => $end,
		'balance' => $balance,
    ));
});

$app->get('/finance-finish.html', function() use($app) {
	SwimAdmin::checkPermission('orders', PERM_FINANCE_READ);
	$id = $app->request->get('id');
    $data = SwimAdmin::model('finance.main')->findOneById($id);
	if(!$data){
	    SwimAdmin::error('订单不存在或已经被删除');
	}
	$app->render('order/finance-finish.html', array(
	    'data' => $data
	));
});

//结算
$app->post('/finance-finish', function() use($app){
	SwimAdmin::checkPermission('orders', PERM_FINANCE_EDIT);
    $id    = trim($app->request->post('id'));
    $money = $app->request->post('money')*1;
    $notes = trim($app->request->post('notes', ''));
    $photo = trim($app->request->post('photo'));

    if($money<=0){
        SwimAdmin::error('打款金额必须大于0'); 
    }
    $Finance = SwimAdmin::model('finance.main');
    $filters = array('_id' => new MongoId($id));
    $finance = $Finance->findOne($filters);
    if(!$finance) {
        SwimAdmin::error('找不到订单。'); 
    }
    $sub = $finance['settle']-$money;
    if($sub<0){
        SwimAdmin::error('金额不足。'); 
	}

    $Remit = SwimAdmin::model('finance.remit');
    $data = array(
        'region_id'       => $finance['region_id'],
        'region_name'     => $finance['region_name'],
        'natatorium_id'   => $finance['natatorium_id'],
        'natatorium_name' => $finance['natatorium_name'],
        'teacher_id'      => $finance['teacher_id'],
		'teacher_name'    => $finance['teacher_name'],
		'teacher_phone'   => $finance['teacher_phone'],
        'total_fee'       => $money,
        'photo'           => $photo,
		'notes'           => $notes,
        'created_by'      => $_SESSION[S_USER],
		'create_time'     => time(),
		'last_updated'    => time(),
		'updated_by'      => $_SESSION[S_USER]
    );
    $res = $Remit->insert($data);
    if($res){
        $Finance->update($filters, array(
            '$inc' => array('settle'=> -$money, 'balance'=> -$money)
        )); 
    }
    $data = $Finance->pagination($app->request->post('__url'));
	$app->render('order/finance-list.html', array(
	    'data' => $data
	));
});

//导出结算列表
$app->get('/finance-export', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_FINANCE_EXPORT);
    $Finance = SwimAdmin::model('finance.main');
    $data = $Finance->pagination();
    $PHPExcel = new PHPExcel();
    $PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '校区')
        ->setCellValue('B1', '场馆名称')
        ->setCellValue('C1', '教练')
        ->setCellValue('D1', '教练联系方式')
        ->setCellValue('E1', '已完成订单')
        ->setCellValue('F1', '已完成总金额')
        ->setCellValue('G1', '账户总金额')
        ->setCellValue('H1', '待结算')
        ->setCellValue('I1', '上次操作时间');
    $row = 2;
    foreach($data['items'] as $item) {
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit("A{$row}", $item['region_name'])
            ->setCellValueExplicit("B{$row}", $item['natatorium_name'])
            ->setCellValueExplicit("C{$row}", $item['teacher_name'])
            ->setCellValueExplicit("D{$row}", $item['teacher_phone'])
            ->setCellValueExplicit("E{$row}", $item['finish_num']) 
            ->setCellValueExplicit("F{$row}", $item['finish_total'])
            ->setCellValueExplicit("G{$row}", $item['balance'])
            ->setCellValueExplicit("H{$row}", $item['settle'])
            ->setCellValueExplicit("I{$row}", date('Y-m-d H:i:s', $item['last_updated']));
        $row++;
    }
    $filename = date("YmdHis", time());
    header("Content-Type: application/vdn.ms-excel");
    header("Content-Disposition: attachment;filename=\"finance_{$filename}.xls\"");
    header("Cache-Control: max-age=0");
    $writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
    $writer->save('php://output');
});

//汇款列表
$app->get('/remit.html', function() use($app){
	SwimAdmin::checkPermission('orders', PERM_FINANCE_READ);
	$region = trim($app->request->get('region'));
	$natatorium = trim($app->request->get('natatorium', '-'));
	$teacher = $app->request->get('teacher');
	$teacher_phone = $app->request->get('teacher_phone');
	$start = $app->request->get('start');
	$end   = $app->request->get('end');
	$natatoriumData = array();
	if($region && $region != '-') {
		$Natatorium = SwimAdmin::model('natatorium.main');
		$filter = array('region.id' => $region);
		$projection = array('title'=>1);
		$natatoriumData = $Natatorium->find($filter, $projection);
	}
	$regions = SwimAdmin::model('course.region')->find();
	$teachers = SwimAdmin::model('teacher.main')->find();
    $Remit = SwimAdmin::model('finance.remit');
    $data = $Remit->pagination();

    $app->render('order/remit.html', array(
		'data' => $data,
	    'regions' => $regions,
	    'teachers' => $teachers,	
		'regionId' => $region,
		'natatoriumId' => $natatorium,
		'natatoriumData' => $natatoriumData,
		'teacherId' => $teacher,
		'teacher_phone' => $teacher_phone,
		'start' => $start,
		'end' => $end,
    ));
});

//汇款记录表单
$app->get('/remit-form.html', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_FINANCE_READ);
    $id = trim($app->request->get('id'));
    $filters = array('_id' => new MongoId($id));
    $data = SwimAdmin::model('finance.remit')->findOne($filters);
    $app->render('order/remit-form.html', array(
        'data' => $data
    ));
});

//修改汇款记录
$app->post('/remit-form', function() use($app){
	SwimAdmin::checkPermission('orders', PERM_FINANCE_EDIT);
	$id    = trim($app->request->post('id'));
	$notes = trim($app->request->post('notes'));
	$photo = trim($app->request->post('photo'));
    $Remit = SwimAdmin::model('finance.remit');
	$filters = array('_id' => new MongoId($id));
	$update = array('$set' => array(
		'photo' => $photo,
		'notes'  => $notes,
		'last_updated' => time()
	));
	$res = $Remit->update($filters, $update);
	$data = $Remit->pagination($app->request->post('__url'));
	$app->render('order/remit-list.html', array(
	    'data' => $data
	));
});

//导出汇款记录
$app->get('/remit-export', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_FINANCE_EXPORT);
    $Finance = SwimAdmin::model('finance.remit');
    $data    = $Finance->pagination();
    $PHPExcel = new PHPExcel();
    $PHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '#ID')
        ->setCellValue('B1', '校区')
        ->setCellValue('C1', '场馆名称')
        ->setCellValue('D1', '教练')
        ->setCellValue('E1', '联系方式')
		->setCellValue('F1', '操作金额(元)')
		->setCellValue('G1', '操作时间')
		->setCellValue('H1', '操作员备注');
	$row = 2;
	foreach($data['items'] as $item) {
		$PHPExcel->setActiveSheetIndex(0)
		    ->setCellValueExplicit("A{$row}", (string)$item['_id'])	
			->setCellValueExplicit("B{$row}", $item['region_name'])
			->setCellValueExplicit("C{$row}", $item['natatorium_name'])
			->setCellValueExplicit("D{$row}", $item['teacher_name'])
			->setCellValueExplicit("E{$row}", $item['teacher_phone'])
			->setCellValueExplicit("F{$row}", $item['total_fee'])
			->setCellValueExplicit("G{$row}", date('Y-m-d H:i:s', $item['create_time']))
			->setCellValueExplicit("H{$row}", $item['notes']);
		$row++;
	}
    $filename = date("YmdHis", time());
    header("Content-Type: application/vdn.ms-excel");
    header("Content-Disposition: attachment;filename=\"remit_{$filename}.xls\"");
    header("Cache-Control: max-age=0");
    $writer = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
    $writer->save('php://output');
});

//订单详情
$app->get('/detail.html', function() use($app){
   SwimAdmin::checkPermission('orders', PERM_READ);
   $id     = $app->request()->get('id');
   $Order  = SwimAdmin::model('course.order');
   $Course = SwimAdmin::model('course.main');
   $order  = $Order->findOneByOrderId($id);
   $buyers = array();
   $orders = array();
   if(isset($order['course']['is_tuangou']) && $order['course']['is_tuangou']==1) {
	   $filters = array(
		   'course.id' => $order['course']['id'], 
		   'course.is_tuangou'=>1, 
		   'course.tuangou_people'=>array('$gt'=>0),
		   'order_status'=>1
	   );
       $orders = SwimAdmin::model('course.order')->find($filters);     
	   $uids = array();
	   foreach($orders as $item) {
	       $uids[] = new MongoId($item['user_id']); 
	   }
	   if($uids) {
		   $filters = array('_id' => array('$in' => $uids));
	       $cursor = SwimAdmin::model('member.main')->find($filters);
		   foreach($cursor as $item) {
		      $buyers[(string)$item['_id']] = $item; 
		   }
	   }
   }
   try{
       $buyer = SwimAdmin::model('member.main')->findOneById($order['user_id']);
   } catch(Exception $e) {
       $buyer = array();
   }
   try{
       $course = $Course->findOneById($order['course_id']);
    } catch(Exception $e) {
        $course = array();
    }
    if($app->request->isAjax()) {
        $app->render('order/detail-form.html', array(
			'order' => $order,
			'orders'=> $orders,
			'buyer' => $buyer,
			'buyers'=> $buyers
        ));
    } else {
       $app->view()->addGlobal('_MOD', 'cousre');
       $app->render('order/detail.html', array(
            'order'   => $order,
			'orders'  => $orders,
			'course'  => $course,
			'buyer'   => $buyer,
			'buyers'  => $buyers
       ));
    }
});

$app->get('/code.html', function() use($app) {
    $id = $app->request->get('id');
    $app->render('order/code-form.html', array(
        'orderId' => $id
    ));
});

$app->get('/teacher.html', function() use($app) {
    $Teacher = SwimAdmin::model('teacher.main');
    $filters = $_GET;
    $data    = $Teacher->pagination('order/teacher.html?'. http_build_query($filters), null, 6);
    
    $id   = $app->request->get('id');
    $from = $app->request->get('from');
    $regions = SwimAdmin::model('course.region')->find();
    $app->render('order/teacher-'. ($from == 'ajax'? 'list': 'form'). '.html', array(
        'orderId' => $id,
        'data'    => $data,
        'regions' => $regions
    ));
});


/*
//核销
$app->get('/active.html', function() use($app) {
    $Order = SwimAdmin::model('course.order');
    $id    = $app->request->get('id');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/active-form.html', array(
        'order'    => $order,
        'finished' => $order['train_status'] == ModelCourseOrder::TRAIN_STATUS_FINISH
    ));
});

$app->get('/clock.html', function() use($app) {
    $id   = $app->request->get('id');
    $Order  = SwimAdmin::model('course.order');
    $order  = $Order->findOneByOrderId($id);
    $app->render('order/clock-form.html', array(
        'order' => $order
    ));
});

$app->post('/active', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_CONFIRM);
    $Order = SwimAdmin::model('course.order');
    $orderId = $app->request->post('orderId');
    if(!$orderId) {
        SwimAdmin::error('订单号不能为空。');
    }
    $res = $Order->beginTrain($orderId);
    if(!$res) {
        SwimAdmin::error('订单不能核消。<br />订单号：'. $orderId);
    }
    $data = $Order->pagination($app->request()->post('__url'));
    $app->render('order/list.html', array(
        'data'     => $data,
    ));
});

*/

//分配教练
$app->post('/allocation', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_ALLOCATE_TEACHER);
    $order     = SwimAdmin::model('course.order');
    $teacher   = SwimAdmin::model('teacher.main');
    $orderId   = $app->request->post('orderId');
    $teacherId = $app->request->post('teacherId');

    $orderData   = $order->findOneByOrderId($orderId);
    $teacherData = $teacher->findOneById($teacherId);
    if(!$orderData) {
        SwimAdmin::error('订单不存在。');
    } else {
        if(
            $orderData['order_status'] != ModelCourseOrder::PAY_STATUS_SUCESS ||
            $orderData['train_status'] != ModelCourseOrder::TRAIN_STATUS_PROCESSING ||
            $orderData['seller_process_status'] != ModelCourseOrder::SELLER_PROCESS_STATUS_NORMAL
        ) {
            SwimAdmin::error('无法关联教练，订单状态：未付款、退款或已开始授课。');
        }
    }
    if(!$teacherData) {
        SwimAdmin::error('教练不存在。');
    } else {
        if(
            $teacherData['type'] != 1 ||
            $teacherData['phone']  == '' ||
            $teacherData['nick'] == ''
        ) {
            SwimAdmin::error('无法关联教练，教练信息不全：联系电话或昵称为空。');
        }
    }

    $rs = $order->teacherAllocation($orderId, $teacherId);
    if($rs) {
        $data = $order->pagination($app->request()->post('__url'));
        $app->render('order/list.html', array(
            'data' => $data
        ));
    } else {
        SwimAdmin::error('分配教练失败。');
    }
});

//分配泳馆
$app->post('/allocate-natatorium', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_ALLOCATE_YONGGUAN);
    $orderId = $app->request->post('orderId');
    $natatoriumId = $app->request->post('natatoriumId');

    $natatorium = SwimAdmin::model('natatorium.main');
    $order   = SwimAdmin::model('course.order');

    $orderData = $order->findOneByOrderId($orderId);
    if(!$orderData) {
        SwimAdmin::error('订单不存在。');    
    } else {
        if(
            $orderData['order_status'] != ModelCourseOrder::PAY_STATUS_SUCESS ||
            $orderData['train_status'] != ModelCourseOrder::TRAIN_STATUS_PROCESSING ||
            $orderData['seller_process_status'] != ModelCourseOrder::SELLER_PROCESS_STATUS_NORMAL
        ) {
            SwimAdmin::error('无法指派泳馆，订单未完成付款、退款或已经开始授课。');
        }
    }

    $natatoriumData = $natatorium->findOneById($natatoriumId);
    if(!$natatoriumData) {
        SwimAdmin::error('泳馆不存在。');        
    } else {
        if(
            trim($natatoriumData['title']) == '' ||
            trim($natatoriumData['tel']) == '')        {
            SwimAdmin::error('无法指派泳馆，泳馆信息不全：泳馆名称或电话为空'); 
        }
    }

    $rs = $order->allocateNatatorium($orderId, $natatoriumId);
    if($rs) {
        $data = $order->pagination($app->request->post('__url'));

        $app->render('order/list.html', array(
            'data' => $data
        ));
    } else {
        SwimAdmin::error('分配游馆失败。'); 
    }
});


//打卡
$app->get('/clock.html', function() use($app){
    $id   = $app->request->get('id');
    $Order  = SwimAdmin::model('course.order');
    $order  = $Order->findOneByOrderId($id);
    $app->render('order/clock-form.html', array(
        'order' => $order
    ));
});


//打卡
$app->post('/clock', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_CLOCK);
    $orderId = trim($app->request->post('orderId'));
    $add     = intval($app->request->post('add'));
    $Order   = SwimAdmin::model('course.order');
    $order   = $Order->findOneByOrderId($orderId); 
    if(!$order) {
        SwimAdmin::error('订单不存在。');
    }
	if(!$order['teacher_id']){
	    SwimAdmin::error('此订单尚未分配教练，你不能对此订单打卡。');
	}
	
	if($order['teacher_id']!=$_SESSION[S_TEACHER_ID]){
	    SwimAdmin::error('此订单属于其它教练，你无法对此订单打卡。');
	}
    $step = empty($order['current_step'])? 0: $order['current_step'];
    $maxStep = empty($order['course']['training_step'])? 0: intval($order['course']['training_step']);

    $add ? ($step++) : ($step--);
    if($step<=0) { //未开始上课
        $step = 0;
        $res = $Order->beginTrain($orderId, 0);
    }else if($step>=$maxStep) { //毕业了
        $step = $maxStep;
        $res = $Order->finishTrain($orderId, $maxStep);
        if($res) {
            $filters = array(
                'region_id'     => $order['demanded']['xiaoqu'],
				'natatorium_id' => $order['demanded']['natatorium_id'],
                'teacher_id'    => $order['teacher_id'] 
            );     
            $Finance = SwimAdmin::model('finance.main');
            $finance = $Finance->findOne($filters);
            if(!$finance) {
				$data = array(
					'region_id'       => $order['demanded']['xiaoqu'], //校区id
					'region_name'     => $order['demanded']['xiaoquTitle'], //校区
					'natatorium_id'   => $order['demanded']['natatorium_id'], //场馆id
					'natatorium_name' => $order['demanded']['natatorium'], //场馆名称
					'teacher_id'      => $order['teacher_id'], //教练id
					'teacher_name'    => $order['teacher_name'], //教练名字
					'teacher_phone'   => $order['teacher_phone'], //教练电话
					'teacher_type'    => empty($order['teacher_type'])? 1: $order['teacher_type'], //1教练 2泳馆
					'finish_num'      => 1, //已完成订单数量
					'finish_total'    => $order['total_fee'], //已完成总金额
					'balance'         => $order['total_fee'], //帐户总金额
					'settle'          => $order['total_fee'], //待结算金额
					'last_updated'    => time(), //最后更新时间
					'create_time'     => time()  //创建时间
				);
                $Finance->insert($data);
            }else{
				$update = array('$inc'=>array(
					'finish_num'=>1,
					'finish_total' => $order['total_fee'],
					'balance' => $order['total_fee'],
					'settle' => $order['total_fee'],
				),
				'$set' => array(
				    'last_updated' => time()
				),
			);
                $Finance->update($filters, $update);
            }
        }
    }else{ //打卡，增加或减少当前课时
        $res = $Order->beginTrain($orderId, $step);
    }
    $data = array('step'=>$step);
    $app->view()->renderJSON($data);
});

//订单备注
$app->get('/note.html', function() use($app) {
    $Order = SwimAdmin::model('course.order');
    $id    = $app->request->get('id');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/note-form.html', array(
        'order' => $order
    ));
});

$app->post('/note', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_COMPLETE);
    $Order = SwimAdmin::model('course.order');
    $orderId = trim($app->request->post('orderId')); 
    $note    = trim($app->request->post('note'));
    if(!$note) {
        SwimAdmin::error('请填写备注信息');
    }
    $filter = array('order_id'=>$orderId);
    $update = array('note'=>$note);
    $Order->update($filter, $update); 
    
    $data = $Order->pagination($app->request->post('__url'));
    $app->render('order/list.html', array(
        'data' => $data
    ));
});

//培训结束，毕业了
$app->get('/finish.html', function() use($app) {
    $Order = SwimAdmin::model('course.order');
    $id    = $app->request->get('id');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/finish-form.html', array(
        'order'    => $order,
        'finished' => $order['train_status'] == ModelCourseOrder::TRAIN_STATUS_FINISH
    ));
});

$app->post('/finish', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_NOTES);
    $Order   = SwimAdmin::model('course.order');
    $orderId = $app->request()->post('orderId');
    $order   = $Order->findOneByOrderId($orderId);
    if(empty($order)) {
        SwimAdmin::error('订单不存在或已经被删除。');
    } else {
        if($order['order_status'] != ModelCourseOrder::PAY_STATUS_SUCESS) {
            SwimAdmin::error('订单未付款，不能确认完成。');
        } else if($order['train_status'] != ModelCourseOrder::TRAIN_STATUS_START) {
            SwimAdmin::error('订单未开始授课，不能确认完成。');
        } else if($order['seller_process_status'] != ModelCourseOrder::SELLER_PROCESS_STATUS_NORMAL) {
            SwimAdmin::error('退款状态中，不能确认完成。');
        } else if($order['buyer_process_status'] != ModelCourseOrder::BUYER_PROCESS_STATUS_PAY_SUCESS) {
            SwimAdmin::error('退款状态中，不能确认完成。');
        } else if(empty($order['teacher_id'])) {
            SwimAdmin::error('未绑定教练，不能确认完成。');
        } else if(empty($order['code']) || empty($order['active_time'])) {
            SwimAdmin::error('未核消，不能确认完成。');
        } else if(isset($order['finish_time']) && $order['finish_time']) {
            SwimAdmin::error('订单已经完成，不能确认完成。');
        }
    }

    $res = $Order->finishTrain($orderId);
    if(!$res) {
        SwimAdmin::error('确认完成失败。');
    } else {
        $data = $Order->pagination($app->request()->post('__url'));
        $app->render('order/list.html', array(
            'data' => $data
        ));
    }
});

//取消退款
$app->get('/cancel-refund.html', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_COMPLETE);
    $Order = SwimAdmin::model('course.order');
    $id    = $app->request->get('id');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/cancel-form.html', array(
        'order' => $order 
    ));
});

$app->post('/cancel-refund', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_COMPLETE);
    $Order = SwimAdmin::model('course.order');
    $orderId = $app->request->post('orderId');
    $order = $Order->findOneByOrderId($orderId);
    if(empty($order)) {
        SwimAdmin::error('订单不存在或已经被删除');
    }
    if($order['order_status'] != $Order::PAY_STATUS_SUCESS) {
        SwimAdmin::error('订单未付款，不能取消退款');
    } else if(empty($order['import']) && $order['buyer_process_status'] != $Order::BUYER_PROCESS_STATUS_ASK_REFUND) {
        SwimAdmin::error('订单不处于申请退款状态，不能取消退款');
    } 
    $result = $Order->cancelRefund($orderId);
    if(!$result) {
        SwimAdmin::error('取消退款失败。');
    }
    $data = $Order->pagination($app->request->post('__url'));
    $app->render('order/list.html', array(
        'data' => $data
    ));
});

//退款表单
$app->get('/refund.html', function() use($app) {
    $Order = SwimAdmin::model('course.order');
    $id    = $app->request->get('id');
    $order = $Order->findOneByOrderId($id);
    $app->render('order/refund-form.html', array(
        'order' => $order
    ));
});

$app->post('/other/refund', function() use($app) {
    SwimAdmin::checkPermission('orders', PERM_REFUND);
    $Order     = SwimAdmin::model('course.order');
    $orderId   = trim($app->request->post('orderId'));
    $refundFee = trim($app->request->post('refundFee'))*1;

    $order     = $Order->findOneByOrderId($orderId);
    if(empty($order)) {
        SwimAdmin::error('订单不存在或已经被删除。');
    }
    if(empty($order['import'])) {
        SwimAdmin::error('无法完成退款确认。');
    }
    if($order['seller_process_status'] == $Order::SELLER_PROCESS_STATUS_REFUND_SUCESS) {
        SwimAdmin::error('该订单已经完成退款，不能重复退款。');
    }
    $filter = array(
        'order_id' => $orderId,    
        'order_status' => $Order::PAY_STATUS_SUCESS,
        'seller_process_status' => $Order::SELLER_PROCESS_STATUS_NORMAL 
    );
    $update = array(
        'ask_refund_time' => time(),
        'buyer_process_status' => $Order::BUYER_PROCESS_STATUS_ASK_REFUND,
        'seller_process_status' => $Order::SELLER_PROCESS_STATUS_REFUND_SUCESS,
        'refundNo' => '0',
        'refund_fee' => $refundFee,
        'refund_time' => time(),
        'refund_result' => array()
    );
    $res = $Order->update($filter, $update);
    if($res['nModified']>0) {
        $data = $Order->pagination($app->request()->post('__url'));
        $app->render('order/list.html', array(
            'data' => $data
        ));
    } else {
        SwimAdmin::error('退款确认失败。');
    }
});

$app->post('/weixin/refund', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_REFUND);
    include_once('lib/wxpay/WxPay.Api.php');
    $Order     = SwimAdmin::model('course.order');
    $orderId   = trim($app->request->post('orderId'));   //订单号
    //$orderId = '20160314150212936261731';
    $refundFee = trim($app->request->post('refundFee')) * 100; //退款金额

    $order = $Order->findOneByOrderId($orderId);
    if(empty($order)) {
        SwimAdmin::error('订单不存在或已经被删除。');
    }
    if($order['buyer_process_status'] != ModelCourseOrder::BUYER_PROCESS_STATUS_ASK_REFUND) {
        SwimAdmin::error('不是申请退款状态，无法完成退款确认。');
    } else if($order['seller_process_status'] != ModelCourseOrder::SELLER_PROCESS_STATUS_PROCESSING) {
        SwimAdmin::error('不是退款处理中状态，无法完成退款确认。');
    }

    $num  = Helper::mkrand();
    $time = date('YmdHis');
    $outRefundNo = $time . $num;

    $input = new WxPayRefund();
    $input->SetOut_trade_no($orderId);
    $input->SetTotal_fee(intval($order['total_fee'] * 100)); //订单总金额  单位为分
    $input->SetRefund_fee($refundFee); //退款金额   单位为分
    $input->SetOut_refund_no($outRefundNo);
    $input->SetOp_user_id(Config::$payment['weixin_mch_id']);
    $result = WxPayApi::refund($input); 
    
    if($result['return_code']=='SUCCESS')
    {
        $wxPayDataBase = new WxPayResults();
        $wxPayDataBase->FromArray($result);
        $sign = $wxPayDataBase->SetSign(); 

        if($sign==$result['sign'])
        {
            // 退款金额=退款金额/100 (元) 单位转换
            $res = $Order->updateRefundStatus($result['out_trade_no'], $result['out_refund_no'], $result['refund_fee']/100, 'weixin');
            if($res) {
                $data = $Order->pagination($app->request()->post('__url'));
                $app->render('order/list.html', array(
                    'data' => $data
                ));
            } else {
                SwimAdmin::error('退款确认失败。');
            }
        }
        else
        {
        	//签名错误
            SwimAdmin::error('退款确认失败, 签名错误。');
        }
    } else {
        $error = @$result['return_msg'];
        SwimAdmin::error('退款确认错误: '. ($error? $error: '未知错误'));
    }
});

$app->post('/alipay/refund', function() use($app){
    SwimAdmin::checkPermission('orders', PERM_REFUND);
    include_once('lib/alipay/alipay_submit.class.php');
    $Order     = SwimAdmin::model('course.order');
    $orderId   = trim($app->request()->post('orderId'));
    $refundFee = trim($app->request()->post('refundFee')) * 1;
    $reason    = trim($app->request()->post('reason'));

    $order = $Order->findOneByOrderId($orderId);
    if(empty($order)) {
        echo '订单不存在或已经被删除。'; exit;
    }
    if($order['buyer_process_status'] != ModelCourseOrder::BUYER_PROCESS_STATUS_ASK_REFUND) {
        echo '不是申请退款状态，无法完成退款确认。'; exit;
    } else if($order['seller_process_status'] != ModelCourseOrder::SELLER_PROCESS_STATUS_PROCESSING) {
        echo '不是退款处理中状态，无法完成退款确认。'; exit;
    } else if(!isset($order['pay_result']) || !isset($order['pay_result']['trade_no']) || empty($order['pay_result']['trade_no'])) {
        echo 'trade no 为空，无法完成退款确认。'; exit;
    }
    $tradeNo = $order['pay_result']['trade_no'];

    //模拟数据
    /*$tradeNo   = '2016030521001004460264738879';
    $refundFee = 0.01;
    $reason    = '正常退款'*/

    $batch_no    = date('YmdHis', time()) . Helper::mkrand();
    $batch_num   = '1';
    $detail_data = "{$tradeNo}^{$refundFee}^{$reason}";

    $parameter = array(
        "service"        => 'refund_fastpay_by_platform_pwd',
        "partner"        => Config::$payment['alipay_partner'],
        "notify_url"     => Config::$payment['alipay_refund_notify_url'],
        "seller_user_id" => Config::$payment['alipay_partner'],
        "refund_date"    => date("Y-m-d H:i:s",time()),
        "batch_no"       => $batch_no,
        "batch_num"      => $batch_num,
        "detail_data"    => $detail_data,
        "_input_charset" => strtolower(Config::$payment['alipay_input_charset']),
        "sign_type"      => strtoupper(Config::$payment['alipay_sign_type'])
    );

    $alipayConfig = array(
        'partner'             => Config::$payment['alipay_partner'],
        'seller_id'           => Config::$payment['alipay_seller_id'],
        'private_key_path'    => Config::$payment['alipay_private_key_path'],
        'ali_public_key_path' => Config::$payment['alipay_public_key_path'],
        'sign_type'           => strtoupper(Config::$payment['alipay_sign_type']),
        'input_charset'       => strtolower(Config::$payment['alipay_input_charset']),
        'cacert'              => Config::$payment['alipay_cacert_path'],
        'transport'           => Config::$payment['alipay_transport']
    );

    //建立请求
    $alipaySubmit = new AlipaySubmit($alipayConfig);
    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    echo $html_text;

    /**
    $data = $Order->pagination($app->request()->post('__url'));
    $app->render('order/list.html', array(
        'data' => $data
    ));
    **/
});
