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
 * @property $tank_id
 * @property $level
 * @property $is_premium
 * @property $gun_damage_min
 * @property $max_health
 * @property $gun_damage_max
 * @property $score
 */
class Tank extends Model
{
	static $table_name = 'tank';
	static $primary_key = 'tank_id';
} 
