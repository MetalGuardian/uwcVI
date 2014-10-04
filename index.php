<?php
require 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use metalguardian\helpers\Helper;

ActiveRecord\Config::initialize(
	function(\ActiveRecord\Config $cfg) {
		$cfg->set_model_directory(__DIR__ . '/models');
		$cfg->set_connections(['development' => 'mysql://root:@10.0.2.2/uwc']);
	}
);

$app = new \Slim\Slim();
$app->get(
	'/',
	function () use ($app) {

	}
);
$app->get(
	'/update-clans',
	function () use ($app) {
		Helper::updateClan();

		$app->redirect('/update-members');
	}
);
$app->get(
	'/update-members',
	function () use ($app) {
		Helper::updateMember();

		$app->redirect('/update-tanks');
	}
);
$app->get(
	'/update',
	function () use ($app) {
		$app->redirect('/update-clans');
	}
);
$app->get(
	'/update-tanks',
	function () use ($app) {
		Helper::updateMemberTanks();

		//$app->redirect('/');
	}
);

$app->get(
	'/update-tanks-stat',
	function () use ($app) {
		Helper::updateTankStat();
	}
);

$app->run();
