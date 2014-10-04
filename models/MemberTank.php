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
 * @property $max_frags
 * @property $max_xp
 */
class MemberTank extends Model
{
	static $table_name = 'member_tank';
	static $primary_key = 'account_id';
} 
