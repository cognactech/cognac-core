<?php defined('SYSPATH') or die('No direct access allowed.');

return array(

	'driver'			=>	'nosql_mongo',
	'salt'				=>	'$2a$10$' . 'This!Is!A!Salt',
	'lifetime'			=>	0,
	'session_type'		=>	'native',
	'session_key' 		=>	'cognac-auth',

	'hash_method' 		=>	'sha256',
	'hash_key'			=>	'Cognac!Hash!Key',

	// database config group to use for Auth NoSQL driver
	'database'			=>	'mongo', // nosql db instance name
	'table_name'		=>	'users' // data store name
);
