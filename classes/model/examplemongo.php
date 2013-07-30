<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MySQL "driver" for models.
 * 
 * @author Cesar Gonzalez <cesar.gonzalez@cognactechnologies.com>
 */

class Model_ExampleMongo extends Model_Mongo
{
	public $type = 'NOSQL';
	public $subType = 'Mongo';

	protected $_columns = array(
		'username','email'
	);

	protected $_tableName = 'users';
}
