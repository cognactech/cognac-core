<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'credentials' => array(
		'@default' => array(
			'key' => 'AWSKEY',
			'secret' => 'AWSSECRET',
			'default_cache_config' => APPPATH . 'cache/aws',
			'certificate_authority' => true
		)
	)
);
