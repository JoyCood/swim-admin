<?php
class ControlUserGroup {
	public function __construct() {
		$app  = SwimAdmin::app();
		$view = $app->view();

		$view->addGlobal('_MOD', 'admin');
		$view->addGlobal('_Action', 'group');
		$view->addGlobal('title', '系统');
	}

	public function indexAction() {
		SwimAdmin::checkPermission('groups', PERM_READ);
		$app  = SwimAdmin::app();
		$perm = SwimAdmin::model('permission.main');
		$data = $perm->pagination();
		$app->render('user/group.html', array(
			'data' => $data
		));
	}

	public function formAction() {
		$app  = SwimAdmin::app();
		$perm = SwimAdmin::model('permission.main');
		$id   = $app->request->get('id');
		$data = array(
			'statu' => true
		);
		if($id) {
			$data = $perm->getOneById($id);
		}
		$app->render('user/group-form.html', array(
			'data'		=> $data,
			'settings'  => $perm->getPermissionSettings(),
			'dict' 		=> $perm->getPermissionDict()
		));
	}

	public function saveAction() {
		$app  = SwimAdmin::app();
		$perm = SwimAdmin::model('permission.main');
		$id   = $app->request->post('id');
		$doc  = array(
			'name' 		  => trim($app->request->post('name', '')),
			'description' => trim($app->request->post('description', '')),
			'privilege'	  => array(),
			'statu'	  	  => intval($app->request->post('statu'))
		);
		$privilege = $app->request->post('privilege', array());
		foreach($privilege as $key => $vals) {
			$doc['privilege'][$key] = 0;
			foreach($vals as $val) {
				$doc['privilege'][$key] += $val;
			}
		}
		if(empty($id)) {
			SwimAdmin::checkPermission('groups', PERM_ADD);
			$perm->insert($doc);
		} else {
			SwimAdmin::checkPermission('groups', PERM_EDIT);
			$perm->update($doc, $id);
		}
		$app->view()->renderJSON(array(
			'result' => true
		));
	}

	public function deleteAction() {
		SwimAdmin::checkPermission('groups', PERM_DELETE);
		$app   = SwimAdmin::app();
		$perm  = SwimAdmin::model('permission.main');
		$items = $app->request->post('items', array());
		$perm->deleteById($items);
		$app->view()->renderJSON(array(
			'result' => true
		));
	}
}