<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/(user.html|index.html)', function() {
	return SwimAdmin::control('user')->indexAction();
});

$app->get('/form.html', function() {
	return SwimAdmin::control('user')->formAction();
});

$app->get('/permission.html', function() {
	return SwimAdmin::control('user')->permissionAction();
});

$app->post('/save.html', function() {
	return SwimAdmin::control('user')->saveAction();
});

$app->post('/delete.html', function() {
	return SwimAdmin::control('user')->deleteAction();
});

$app->map('/password.html', function() {
	return SwimAdmin::control('user')->passwordAction();
})->via('GET', 'POST');


//--- GROUP ---//
$app->get('/group.html', function() {
	return SwimAdmin::control('user.group')->indexAction();
});

$app->get('/group/form.html', function() {
	return SwimAdmin::control('user.group')->formAction();
});

$app->post('/group/save.html', function() {
	return SwimAdmin::control('user.group')->saveAction();
});

$app->post('/group/delete.html', function() {
	return SwimAdmin::control('user.group')->deleteAction();
});