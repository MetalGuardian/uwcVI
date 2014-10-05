<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\models;

use ActiveRecord\Model;

/**
 * Class Clan
 * @package metalguardian\models
 *
 * @property $account_id
 * @property $tank_id
 * @property $mark_of_mastery
 * @property $max_xp
 * @property $max_frags
 * @property $score
 */
class MemberTank extends Model
{
	static $table_name = 'member_tank';
	static $primary_key = 'account_id';

	static $belongs_to = [
		['tank', 'readonly' => true]
	];
} 
