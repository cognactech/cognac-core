<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * 
 *
 * @package    Cognac-Core
 * @category   Cookie
 * @author     Nicholas Curtis 		<nich.curtis@gmail.com>
 * @license    https://github.com/cognactech/cognac-core/blob/master/LICENSE
 */

class Cookie extends Kohana_Cookie
{
	public static $salt = 'My!Cookie!Salt';
}