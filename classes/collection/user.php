<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * 
 * @package	Cognac-Core
 * @author	Nicholas Curtis	<nich.curtis@gmail.com>	<http://www.echo1exit.com> 
 */

class Collection_User extends Collection_Mongo
{
	/**
	 * Returns a Collection_Result that match filters passed
	 *
	 * @param		array			$filters
	 * @param		int			$start
	 * @param		int			$limit
	 * @return		Collection_Result
	 */
	public function search (Array $filters=array(), $start=0, $limit=null)
	{
		// if $filters['type'] isset this will be used to filter specific users
		// base on favorite or recent status
		$users = array();

		// recent users search, add recent user ids to $users array
		if (array_key_exists('type', $filters) AND $filters['type'] == 'favorite')
		{
			// use db Collection instance to query mongo
			$results = $this->_db->get_items('physician_favorites', array('user_id' => $User->pk()));

			if ($results->count(TRUE) > 0)
			{
				foreach ($results AS $item)
				{
					$users[] = new MongoId($item['favorite_user_id']);
				}
			}
		}
		
		// recent users search, add recent user ids to $users array
		if (array_key_exists('type', $filters) AND $filters['type'] == 'recent')
		{
			// use db Collection instance to query mongo
			$results = $this->_db->get_items('physician_recent_history', array('user_id' => $User->pk()));

			if ($results->count(TRUE) > 0)
			{
				foreach ($results AS $item)
				{
					$users[] = new MongoId($item['recent_user_id']);
				}
			}
		}

		if (array_key_exists('type', $filters))
		{
			if (count($users) <= 0) return FALSE;
			$where['_id'] = array('$in' => $users);
		}
		unset($results, $users, $filters['type']);

		// remove process filters, and merge rest with $where
		$where = $filters + $where;

		// use find_all to get user models  that match the rest of our filters
		return $this->find_all($where, $fields, $start, $limit);
	}

	/**
	  * Returns a Collection_Result for a users favorite physicians
	  *
	  * @param	Model_User	$User		// Loaded user to get recent history of
	  * @param	int 		$start
	  * @param	int 		$limit
	  * @return	bool
	  * @return	Collection_Result
	  */
	public function favorites ($User, array $fields=array(), $start=0, $limit=null)
	{
		if ( ! $User->loaded() ) {
			return FALSE;
		}

		// use db Collection instance to query mongo
		$results = $this->_db->get_items('physician_favorites', array('user_id' => $User->pk()));

		// no results return false
		if ($results->count(TRUE) <= 0) return FALSE;

		$users = array();
		foreach ($results AS $item)
		{
			$users[] = new MongoId($item['favorite_user_id']);
		}
		unset($results);

		return $this->find_all(array('_id' => array('$in' => $users)), $fields, $start, $limit);
	}

	/**
	  * Returns a Collection_Result for a users recent physician history 
	  *
	  * @param	Model_User	$User		// Loaded user to get recent history of
	  * @param	int 		$start
	  * @param	int 		$limit
	  * @return	bool
	  * @return	Collection_Result
	  */
	public function recent ($User, array $fields=array(), $start=0, $limit=null)
	{
		if ( ! $User->loaded() ) {
			return FALSE;
		}

		// use db Collection instance to query mongo
		$results = $this->_db->get_items('physician_recent_history', array('user_id'	=>	$User->pk()));

		// no results return false
		if ($results->count(TRUE) <= 0) return FALSE;

		$users = array();
		foreach ($results AS $item)
		{
			$users[] = new MongoId($item['recent_user_id']);
		}
		unset($results);

		return $this->find_all(array('_id' => array('$in' => $users)), $fields, $start, $limit);
	}

	/**
	  * Returns a Collection_Result for a users sync calendar
	  *
	  * @param	Model_User	$User		// Loaded user to get sync calendar for
	  * @param	int 		$start
	  * @param	int 		$limit
	  * @return	bool
	  * @return	Collection_Result
	  */
	public function sync ($User, array $fields=array(), $start=0, $limit=null)
	{
		if ( ! $User->loaded() ) {
			return FALSE;
		}

		// use db Collection instance to query mongo
		$old_results = $this->_db->get_items('sync', array('user_id' => $User->pk()));

		// no results return false
		if ($old_results->count(TRUE) <= 0) return FALSE;

		$new_results = array();
		foreach($old_results AS $item)
		{
			$id = $item['synced_user_id'];

			$User = Model::factory('user', $id);

			$new_results[$id] = array('name' => $User->first_name . ' ' . $User->last_name . ' ' . $User->title, 'calendar_synced' => $item['synced']);
		}
		unset($old_results);

		return $new_results;
	}
}
