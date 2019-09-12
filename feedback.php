<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

$app->get('/index.html', function() {
	return SwimAdmin::control('feedback')->indexAction();
});

