<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

abstract class Cognac_Exception extends Kohana_Exception
{
	/**
	 * error message
	 * 
	 * @var		string
	 */
	 protected $_message = NULL;

	 /**
	 * translation variables
	 * 
	 * @var		array
	 */
	 protected $_variables = array();

	 /**
	 * the exception code
	 * 
	 * @var		Int
	 */
	 protected $_code = 0;

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param	string		error message
	 * @param	array 		translation variables
	 * @param  	mixed		the exception code
	 * @return 	void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		// save variables for future reference
		$this->_message	=	$message;
		$this->_variables	=	$variables;
		$this->_code		=	$code;

		parent::__construct($message, $variables, $code);
	}

	/**
	 * Returns property from self::_variables
	 *
	 * @param	string		$key
	 * @return 	mixed
	 * @return	void		key does not exist
	 */
	public function __get ($key)
	{
		if ( ! array_key_exists(':'.$key, $this->_variables) ) {
			return;
		}

		return $this->_variables[':'.$key];
	}
}
