<?php

$app->get('/teacher.html', function() use($app) {   
        $coach = SwimAdmin::model('newcoach.main');
        $datapage=$coach->pagination("create_time");
		$app->render('newcoach/teacher.html',array(
        'datapage'=>$datapage
        ));
});

 
$app->post('/teacher.html', function() use($app) {
    $coach = SwimAdmin::model('newcoach.main');
    $data=$app->request->post();
    $array=array();
    if(isset($data['phoneEmail'])&& preg_match('/^1[34578]{1}\d{9}$/',$data['phoneEmail'])){
        $array['phone']=$data['phoneEmail'];
    }elseif(isset($data['phoneEmail'])&&preg_match('/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i',$data['phoneEmail'])){
        $array['email']=$data['phoneEmail'];
    }
    $datapage=$coach->pagination("create_time");
    if(isset($data['status'])&& !$data['status']){
        $array['status']=0;
        $datapage=$coach->pagination("create_time",$array);
    }elseif(isset($data['status'])&& $data['status']==1){
        $array['status']="1";
        $datapage=$coach->pagination("check_time",$array);
    }elseif(isset($data['status'])&& $data['status']==2){
        $array['status']="2";
        $datapage=$coach->pagination("check_time",$array);
    }elseif($data['status']==10 && $array){
        $datapage=$coach->pagination("create_time",$array);
    }
    $app->render('newcoach/teacher.html',array(
        'datapage'=>$datapage
    ));
    

});

$app->get('/teacher_info.html/:id',function($id)use($app){
        $coach =SwimAdmin::model('newcoach.main');
        $data=$coach->collection()->findOne(array('_id' => new MongoId($id)));
        $app->render('newcoach/teacher_info.html',
            array(
                'data'=>$data
            ));
});


$app->post('/teacher_info.html',function()use($app){
        $data= $app->request->post();
        $data['check_time']=time();
        $id=$app->request->post('id');
        if(!$app->request->post('status')){
            $data['status']=0;
        }
        $coach =SwimAdmin::model('newcoach.main');
        $result= $coach->update($data,$id);
//         if($result){
//             if($data['status']==1){
//                 $msg    = str_replace(array('#title#','#order_id#'), array("aa","bb"), Config::$app['sms_yunpian_msg_order_kfu']);
//                 SwimAdmin::sms()->send($data['phone'], $msg);
//             }elseif($data['status']==2){
//                 $msg    = str_replace(array('#title#','#order_id#'), array("aa","bb"), Config::$app['sms_yunpian_msg_order_kfu']);
//                 SwimAdmin::sms()->send($data['phone'], $msg);
//             }

//         }
        $datapage=$coach->pagination("check_time");
        $app->render('newcoach/teacher.html',array(
            'datapage'=>$datapage
        ));
    
});


$app->post('/form.html',function()use($app){
   
    
});

