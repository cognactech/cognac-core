<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'mongo' => array
	(
		'type'		=>	'mongo',
		'server'	=>	'mongodb://'.MONGO_HOST.':'.MONGO_PORT,
		
		'database'	=>	MONGO_NAME,
		'connect'	=>	TRUE,
		'timeout'	=>	MONGO_TIMEOUT,
		'username'	=>	MONGO_USER,
		'password'	=>	MONGO_PASS,
		'replicaSet'	=>	NULL,

		'debug'		=>	40,
		'profiling' =>	TRUE,
	),
	'dynamo' => array
	(
		'type'		=>	'aws_dynamo',
		'debug'		=>	0,
		'profiling'    	=>	TRUE,
	)
);
