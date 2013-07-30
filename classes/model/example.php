<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MySQL "driver" for models.
 * 
 * @author Cesar Gonzalez <cesar.gonzalez@cognactechnologies.com>
 */

class Model_Example extends Model_MySQL
{
	public $type = 'SQL';
	public $subType = 'MySQL';

	protected $_columns = array(
		'username','email', 'password'
	);

	protected $_tableName = 'users';
}
