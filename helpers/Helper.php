<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\helpers;

use Curl\Curl;
use metalguardian\models\Clan;
use metalguardian\models\ClanMember;
use metalguardian\models\MemberTank;
use metalguardian\models\Tank;

/**
 * Class Helper
 * @package metalguardian\helpers
 */
class Helper
{
	public static function updateTankStat()
	{
		$curl = new Curl();
		Tank::delete_all(false);
		$curl->get(
			'https://api.worldoftanks.ru/wot/encyclopedia/tanks/',
			[
				'application_id' => '02278d8bd1d2dcc41de30a28f76b5ad2',
				'language' => 'en',
				'fields' => 'level,tank_id,is_premium',
			]
		);
		$tanks = $curl->response->data;
		foreach ($tanks as $model) {
			if ($model->level >= 5 && $model->level <= 6) {
				$tank = new Tank();
				$tank->tank_id = $model->tank_id;
				$tank->level = $model->level;
				$tank->is_premium = $model->is_premium;
				$tank->save();
			}
		}
		sleep(1);
		$models = Tank::all(['select' => 'tank_id']);
		$tanks = [];
		foreach ($models as $model) {
			$tanks[] = $model->tank_id;
		}
		$ids = join(',', $tanks);
		$curl->get(
			'https://api.worldoftanks.ru/wot/encyclopedia/tankinfo/',
			[
				'application_id' => '02278d8bd1d2dcc41de30a28f76b5ad2',
				'language' => 'en',
				'fields' => 'tank_id,gun_damage_min,max_health,gun_damage_max',
				'tank_id' => $ids,
			]
		);
		$tanks = $curl->response->data;
		foreach ($tanks as $model) {
			$tank = Tank::find($model->tank_id);
			$tank->gun_damage_min = $model->gun_damage_min;
			$tank->max_health = $model->max_health;
			$tank->gun_damage_max = $model->gun_damage_max;
			$tank->save();
		}
		$curl->close();
	}

	public static function updateClan()
	{
		$curl = new Curl();
		$curl->get(
			'https://api.worldoftanks.ru/wot/globalwar/top/',
			[
				'application_id' => '02278d8bd1d2dcc41de30a28f76b5ad2',
				'language' => 'en',
				'fields' => 'name,members_count,clan_id,provinces_count',
				'map_id' => 'globalmap',
				'order_by' => 'provinces_count',
			]
		);
		$curl->close();
		Clan::delete_all(false);
		for ($i = 0; $i < 10; $i++) {
			$clan = new Clan();
			$clan->clan_id = $curl->response->data[$i]->clan_id;
			$clan->name = $curl->response->data[$i]->name;
			$clan->provinces_count = $curl->response->data[$i]->provinces_count;
			$clan->save();
		}
	}

	public static function updateMember()
	{
		$curl = new Curl();
		ClanMember::delete_all(false);
		/** @var Clan[] $clans */
		$clans = Clan::all();
		foreach ($clans as $clan) {
			$curl->get(
				'https://api.worldoftanks.ru/wot/clan/info/',
				[
					'application_id' => '02278d8bd1d2dcc41de30a28f76b5ad2',
					'language' => 'en',
					'fields' => 'members',
					'clan_id' => $clan->clan_id,
				]
			);
			$members = $curl->response->data->{$clan->clan_id}->members;
			foreach ($members as $model) {
				$member = new ClanMember();
				$member->clan_id = $clan->clan_id;
				$member->member_id = $model->account_id;
				$member->save();
			}
			sleep(1);
		}
		$curl->close();
	}

	public static function updateMemberTanks()
	{
		$curl = new Curl();

		$models = Tank::all(['select' => 'tank_id']);
		$tanks = [];
		foreach ($models as $model) {
			$tanks[] = $model->tank_id;
		}
		$ids = join(',', $tanks);

		MemberTank::delete_all(false);
		/** @var ClanMember[] $members */
		$members = ClanMember::all();
		foreach ($members as $member) {
			$curl->get(
				'https://api.worldoftanks.ru/wot/tanks/stats/',
				[
					'application_id' => '02278d8bd1d2dcc41de30a28f76b5ad2',
					'language' => 'en',
					'fields' => 'tank_id,mark_of_mastery,max_frags,max_xp,account_id',
					'account_id' => $member->member_id,
					'tank_id' => $ids,
				]
			);

			$tanks = $curl->response->data->{$member->member_id};
			foreach ($tanks as $model) {
				$tank = new MemberTank();
				$tank->account_id = $model->account_id;
				$tank->tank_id = $model->tank_id;
				$tank->mark_of_mastery = $model->mark_of_mastery;
				$tank->max_frags = $model->max_frags;
				$tank->max_xp = $model->max_xp;
				$tank->save();
			}

			sleep(1);
		}
		$curl->close();
	}
} 
