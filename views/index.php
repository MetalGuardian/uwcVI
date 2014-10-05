<?php
/**
 * Author: metal
 * Email: metal
 */
/**
 * @var $clans \metalguardian\models\Clan[]
 * @var $members
 */
?>

<!DOCTYPE html>
<html>
<head>
	<title>Metalguardian Balancer</title>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
	<div class="table-responsive col-md-6">
		<table class="table table-striped">
			<thead>
			<tr>
				<td colspan="3" align="center">First clan</td>
			</tr>
			<tr>
				<td>clan id</td>
				<td>clan name</td>
				<td>provinces count</td>
			</tr>
			<tr>
				<td><?= $clans[0]->clan_id; ?></td>
				<td><?= $clans[0]->name; ?></td>
				<td><?= $clans[0]->provinces_count; ?></td>
			</tr>
			<tr>
				<td>member id</td>
				<td>member tank id</td>
				<td>score</td>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($members[0] as $member) : ?>
			<tr>
				<td><?= $member->account_id; ?></td>
				<td><?= $member->tank_id; ?></td>
				<td><?= $member->score; ?></td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td></td>
				<td>Sum</td>
				<td><?= \metalguardian\helpers\Helper::countSumScore($members[0]); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="table-responsive col-md-6">
		<table class="table table-striped">
			<thead>
			<tr>
				<td colspan="3" align="center">Second clan</td>
			</tr>
			<tr>
				<td>clan id</td>
				<td>clan name</td>
				<td>provinces count</td>
			</tr>
			<tr>
				<td><?= $clans[1]->clan_id; ?></td>
				<td><?= $clans[1]->name; ?></td>
				<td><?= $clans[1]->provinces_count; ?></td>
			</tr>
			<tr>
				<td>member id</td>
				<td>member tank id</td>
				<td>score</td>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($members[1] as $member) : ?>
				<tr>
					<td><?= $member->account_id; ?></td>
					<td><?= $member->tank_id; ?></td>
					<td><?= $member->score; ?></td>
				</tr>
				<?php endforeach; ?>
			<tr>
				<td></td>
				<td>Sum</td>
				<td><?= \metalguardian\helpers\Helper::countSumScore($members[1]); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">What can you do</h3>
		</div>
		<div class="panel-body">
			<a href="/update-tanks-stat" class="btn btn-primary btn-xs">Update tank stats (base tanks from encyclopedia)</a>
			<a href="/update-tanks-scores" class="btn btn-primary btn-xs">Update only tank scores</a>
			<a href="/update" class="btn btn-primary btn-xs">Update all tables</a>
			<a href="/update-clans" class="btn btn-primary btn-xs">Update clans</a>
			<a href="/update-members" class="btn btn-primary btn-xs">Update clan members</a>
			<a href="/update-member-tanks" class="btn btn-primary btn-xs">Update member tanks (take about 10 min)</a>
		</div>
	</div>
</div>
</body>
</html>
