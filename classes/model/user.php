<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package		model
 * @category	User
 * @author		Nicholas Curtis		<nich.curtis@gmail.com>
 */

class Model_User extends Model_Mongo
{
	/**
	 * holds name of mongo collection
	 * 
	 * @var		string
	 */
	protected $_tableName = 'users';

	/**
	 * @var Holds the columns of the model
	 */
	protected  $_columns = array(
		'email', 'lastlogin', 'logins', 'password', 'first_name', 'last_name', 'roles'
	);

	/**
	 * Checks to see if loaded user has one of the roles passed in
	 *
	 * if $all_roles === TRUE , then user must have all roles passed in
	 *
	 * @return 		bool
	 */
	public function has_role ($roles, $all_roles=FALSE)
	{
		$has_role = FALSE;

		foreach ($roles AS $role) {
			if ( in_array($role, $this->roles) ) {
				// role was found
				$has_role = TRUE;
				
				// if only one is required we can end now
				if ( $all_roles ) {
					break;
				}
			}
			elseif ($all_roles === TRUE) {
				// missing a role and all roles are required
				return FALSE;
			}
		}

		return $has_role;
	}
}