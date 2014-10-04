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
 * @property $member_id
 * @property $clan_id
 */
class ClanMember extends Model
{
	static $table_name = 'clan_member';
	static $primary_key = 'member_id';
} 
