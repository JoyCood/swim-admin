<?php
class ControlFeedback {
	public function __construct() {
		$app  = SwimAdmin::app();
		$view = $app->view();

		$view->addGlobal('_MOD', 'system');
		$view->addGlobal('title', '系统');
	}

	public function indexAction() {
		SwimAdmin::checkPermission('main-contents', PERM_READ);
		$app 	  = SwimAdmin::app();
		$feedback = SwimAdmin::model('feedback.main');
		$data     = $feedback->pagination();
		$app->render('feedback/index.html', array(
			'data' => $data
		));
	}
}