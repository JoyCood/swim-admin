<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/package.html', function() {
	return SwimAdmin::control('upgrade')->packageAction();
});

$app->get('/package/form.html', function() {
	return SwimAdmin::control('upgrade')->formAction();
});

$app->post('/package/save.html', function() {
	return SwimAdmin::control('upgrade')->saveAction();
});

