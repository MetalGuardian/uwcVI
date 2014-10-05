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
	/**
	 * Update tank list and tank stats
	 *
	 * @throws \ActiveRecord\RecordNotFound
	 * @throws \ErrorException
	 */
	public static function updateTankStat()
	{
		$curl = new Curl();
		Tank::delete_all(false);
		$curl->get(
			'https://api.worldoftanks.ru/wot/encyclopedia/tanks/',
			[
				'application_id' => WARGAMING_API_KEY,
				'language' => 'en',
				'fields' => 'level,tank_id,is_premium',
			]
		);
		$tanks = isset($curl->response->data) ? $curl->response->data : [];
		foreach ($tanks as $model) {
			if ($model->level >= 4 && $model->level <= 6) {
				$tank = new Tank();
				$tank->tank_id = $model->tank_id;
				$tank->level = $model->level;
				$tank->is_premium = $model->is_premium;
				$tank->save();
			}
		}
		/** @var Tank[] $models */
		$models = Tank::all(['select' => 'tank_id']);
		$tanks = [];
		foreach ($models as $model) {
			$tanks[] = $model->tank_id;
		}
		$ids = join(',', $tanks);
		$curl->get(
			'https://api.worldoftanks.ru/wot/encyclopedia/tankinfo/',
			[
				'application_id' => WARGAMING_API_KEY,
				'language' => 'en',
				'fields' => 'tank_id,gun_damage_min,max_health,gun_damage_max',
				'tank_id' => $ids,
			]
		);
		$tanks = isset($curl->response->data) ? $curl->response->data : [];
		foreach ($tanks as $model) {
			/** @var Tank $tank */
			$tank = Tank::find($model->tank_id);
			$tank->gun_damage_min = $model->gun_damage_min;
			$tank->max_health = $model->max_health;
			$tank->gun_damage_max = $model->gun_damage_max;
			$tank->score = Helper::tankScore($tank);
			$tank->save();
		}
		$curl->close();
	}

	/**
	 * Update clan list, top 10 by provinces_count
	 *
	 * @throws \ErrorException
	 */
	public static function updateClan()
	{
		$curl = new Curl();
		$curl->get(
			'https://api.worldoftanks.ru/wot/globalwar/top/',
			[
				'application_id' => WARGAMING_API_KEY,
				'language' => 'en',
				'fields' => 'name,members_count,clan_id,provinces_count',
				'map_id' => 'globalmap',
				'order_by' => 'provinces_count',
			]
		);
		$curl->close();
		Clan::delete_all(false);
		for ($i = 0; $i < 10; $i++) {
			/** @var Clan $clan */
			$clan = new Clan();
			$clan->clan_id = $curl->response->data[$i]->clan_id;
			$clan->name = $curl->response->data[$i]->name;
			$clan->provinces_count = $curl->response->data[$i]->provinces_count;
			$clan->save();
		}
	}

	/**
	 * Update clan members
	 *
	 * @throws \ErrorException
	 */
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
					'application_id' => WARGAMING_API_KEY,
					'language' => 'en',
					'fields' => 'members',
					'clan_id' => $clan->clan_id,
				]
			);
			$members = isset($curl->response->data->{$clan->clan_id}->members) ? $curl->response->data->{$clan->clan_id}->members : [];
			foreach ($members as $model) {
				/** @var ClanMember $member */
				$member = new ClanMember();
				$member->clan_id = $clan->clan_id;
				$member->member_id = $model->account_id;
				$member->save();
			}
		}
		$curl->close();
	}

	/**
	 * Update clan member tanks
	 *
	 * @throws \ErrorException
	 */
	public static function updateMemberTanks()
	{
		$curl = new Curl();

		$models = Tank::all(['select' => 'tank_id']);
		$tankIds = [];
		foreach ($models as $model) {
			$tankIds[] = $model->tank_id;
		}

		MemberTank::delete_all(false);
		/** @var ClanMember[] $members */
		$members = ClanMember::all();
		foreach ($members as $member) {
			for ($i = 0; $i < count($tankIds); $i += 70) {
				$ids = join(',', array_slice($tankIds, $i, 70));

				$curl->get(
					'https://api.worldoftanks.ru/wot/tanks/stats/',
					[
						'application_id' => WARGAMING_API_KEY,
						'language' => 'en',
						'fields' => 'tank_id,mark_of_mastery,max_xp,max_frags,account_id',
						'account_id' => $member->member_id,
						'tank_id' => $ids,
					]
				);

				$tanks = isset($curl->response->data->{$member->member_id}) ? $curl->response->data->{$member->member_id} : [];
				foreach ($tanks as $model) {
					/** @var MemberTank $tank */
					$tank = new MemberTank();
					$tank->account_id = $model->account_id;
					$tank->tank_id = $model->tank_id;
					$tank->mark_of_mastery = $model->mark_of_mastery;
					$tank->max_xp = $model->max_xp;
					$tank->max_frags = $model->max_frags;
					$tank->score = Helper::memberTankScore($tank);
					$tank->save();
				}
			}
		}
		$curl->close();
	}

	/**
	 * Return 2 random clan ids
	 *
	 * @throws \Exception
	 * @return array
	 */
	public static function getClans()
	{
		/** @var Clan[] $models */
		$models = Clan::find_by_sql('SELECT DISTINCT (clan_id), name, provinces_count FROM clan ORDER BY RAND() LIMIT 2');
		if (count($models) != 2) {
			throw new \Exception('Can not get 2 clans. Run updater');
		}
		return $models;
	}

	/**
	 * Count tank score
	 *
	 * @param Tank $tank
	 *
	 * @return int
	 */
	public static function tankScore(Tank $tank)
	{
		return PREMIUM_TANK_COEFFICIENT * $tank->is_premium + $tank->level * LEVEL_TANK_COEFFICIENT + $tank->max_health * HEALTH_TANK_COEFFICIENT + $tank->gun_damage_max * MAX_DAMAGE_TANK_COEFFICIENT + $tank->gun_damage_min * MIN_DAMAGE_TANK_COEFFICIENT;
	}

	/**
	 * @param MemberTank $tank
	 *
	 * @return int
	 */
	public static function memberTankScore(MemberTank $tank)
	{
		return ($tank->mark_of_mastery + 1) * $tank->tank->score * TANK_SCORE_AND_MASTERY_COEFFICIENT + $tank->max_xp * MAX_XP_MEMBER_TANK_COEFFICIENT + $tank->max_frags * MAX_FRAGS_MEMBER_TANK_COEFFICIENT;
	}

	/**
	 * Update only scores of tanks
	 */
	public static function updateTanksScores()
	{
		/** @var Tank[] $tanks */
		$tanks = Tank::all();
		foreach ($tanks as $tank) {
			$tank->score = Helper::tankScore($tank);
			$tank->save();
		}

		/** @var MemberTank[] $memberTanks */
		$memberTanks = MemberTank::all();
		foreach ($memberTanks as $tank) {
			$tank->score = Helper::memberTankScore($tank);
			$tank->save();
		}
	}

	/**
	 * Get 15 random tank members
	 *
	 * @param Clan $clan
	 *
	 * @throws \Exception
	 * @return array
	 */
	public static function getFirstClanMembers(Clan $clan)
	{
		$models = MemberTank::find_by_sql('SELECT DISTINCT (account_id), id, tank_id, score FROM member_tank WHERE account_id IN (SELECT member_id FROM clan_member WHERE clan_id = ?) ORDER BY RAND() LIMIT 15', [$clan->clan_id]);
		if (count($models) != 15) {
			throw new \Exception('Can not get 15 members with tanks for first clan. Run updater');
		}
		return $models;
	}

	/**
	 * Get 15 tank members according to first clan members selection
	 *
	 * @param Clan $clan
	 *
	 * @throws \Exception
	 * @return array
	 */
	public static function getSecondClanMembers(Clan $clan, $members)
	{
		$models = [];
		$ids = [];
		foreach ($members as $one) {
			$getted = false;
			$diff = BALANCE_DELTA_SCORE;
			while ($getted === false) {
				$model = MemberTank::find_by_sql(
					'SELECT account_id, id, tank_id, score FROM member_tank WHERE account_id IN (SELECT member_id FROM clan_member WHERE clan_id = ?) AND score > ? AND score < ? AND account_id NOT IN (?) ORDER BY RAND() LIMIT 1',
					[$clan->clan_id, $one->score - $diff, $one->score + $diff, join(',', $ids)]
				);
				if (count($model)) {
					$models[] = $model[0];
					$ids[] = $model[0]->account_id;
					$getted = true;
				} else {
					$diff += BALANCE_DELTA_SCORE;
				}
			}
		}

		if (count($models) != 15) {
			throw new \Exception('Can not get 15 members with tanks for second clan. Run updater');
		}
		return $models;
	}

	/**
	 * @param $members
	 *
	 * @return int
	 */
	public static function countSumScore($members)
	{
		$sum = 0;
		array_map(
			function ($item) use (&$sum) {
				$sum += $item->score;
			},
			$members
		);
		return $sum;
	}
} 
