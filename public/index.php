<?php
require '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use metalguardian\helpers\Helper;

require '..' . DIRECTORY_SEPARATOR . 'config.php';

ActiveRecord\Config::initialize(
	function(\ActiveRecord\Config $cfg) {
		$cfg->set_model_directory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'models');
		$cfg->set_connections(['development' => MYSQL_CONNECTION_STRING]);
	}
);

$app = new \Slim\Slim(
	[
		'templates.path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views'
	]
);
$app->get(
	'/',
	function () use ($app) {
		$clans = Helper::getClans();
		$members[0] = Helper::getFirstClanMembers($clans[0]);
		$members[1] = Helper::getSecondClanMembers($clans[1], $members[0]);
		$app->render('index.php', ['clans' => $clans, 'members' => $members]);
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
	'/update-member-tanks',
	function () use ($app) {
		Helper::updateMemberTanks();

		$app->redirect('/');
	}
);

$app->get(
	'/update-tanks-stat',
	function () use ($app) {
		Helper::updateTankStat();

		$app->redirect('/');
	}
);

$app->get(
	'/update-tanks-scores',
	function () use ($app) {
		Helper::updateTanksScores();

		$app->redirect('/');
	}
);

$app->run();
