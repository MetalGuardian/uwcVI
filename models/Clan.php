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
 * @property $clan_id
 * @property $name;
 * @property $provinces_count
 */
class Clan extends Model
{
	static $table_name = 'clan';
	static $primary_key = 'clan_id';
} 
