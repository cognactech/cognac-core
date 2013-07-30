<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MySQL "driver" for models.
 * 
 * @author Cesar Gonzalez <cesar.gonzalez@cognactechnologies.com>
 */

class Model_MySQL extends Model
{
	public $type = 'sql';
	public $subType = 'mysql';
	public $_primaryKey = 'id';

	/**
	 * If $_primaryKey is not in columns, it gets added.
	 * Sets the DB resource.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		//merge primaryKey with columns
		if( ! in_array($this->_primaryKey, $this->_columns))
			$this->_columns[] = $this->_primaryKey;

		$this->_db = Database::instance($this->_dbName);
	}

	/**
	 * Not currently used.
	 * @return void
	 */
	public function __destruct()
	{
		
	}

	/**
	 * returns the columns to keep when serializing the object.
	 * @return array
	 */
	public function __sleep()
	{
		return array('_columns','_data','_tableName','_primaryKey','type','subType','_loaded', '_dbName', '_dbConfig');
	}

	/**
	 * Wakup method to reinitialize resources required by the object.
	 * @return void
	 */
	public function __wakeup()
	{
		$this->_db = Database::instance($this->_dbName);
	}

	/**
	 * If $value is null, returns the value of $column in the object.
	 * If $value is not null, it will set the value of $column to $value.
	 * @param string $column 
	 * @param mixed $value 
	 * @return mixed
	 */
	public function param($column, $value=NULL)
	{
		//check to see if its even a column
		if(in_array($column, $this->_columns))
		{
			//are we setting the value?
			if( ! is_null($value))
			{
				//do not allow the changing of the primary key
				//if the value was not changed, do not change it
				if($column == $this->_primaryKey OR $value === $this->_data[$column])
					return;
				
				//if it's not loaded, add it to data instead
				if($this->loaded())
				{
					//check to see if the data's been changed
					if($value !== $this->_data[$column])
						$this->_data_changed[$column] = $value;
				}
				else
					$this->_data[$column] = $value;

				//no longer saved
				$this->_saved = FALSE;

				return;
			}
			
			//check to see if there's pending data
			return (array_key_exists($column, $this->_data_changed)) ? $this->_data_changed[$column] : $this->_data[$column];
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * This will store the object using MySQL.
	 * Any cached version is removed so next load re-caches the updated version.
	 * @return boolean
	 */
	public function save()
	{
		//if it's an update, only update the updated columns
		if($this->loaded() AND ! $this->loaded_by_array())
		{
			//check to see if there's even changes
			if(empty($this->_data_changed))
				return $this;

			$this->_data_changed_crypt = $this->_encrypt($this->_data_changed);

			$changed = DB::update($this->_tableName)
				->set($this->_data_changed_crypt)
				->where($this->_primaryKey, '=', $this->_data[$this->_primaryKey])
				->execute($this->_db);

			if($changed == 1)
			{
				//this means data was changed, update data
				$this->_data = array_merge($this->_data, $this->_data_changed);
			}

			//empty changed data
			$this->_data_changed = array();
		}
		else
		{
			$this->_data_crypt = $this->_encrypt($this->_data);

			//create a new record
			$result = DB::insert($this->_tableName)
				->columns(array_keys($this->_data_crypt))
				->values(array_values($this->_data_crypt))
				->execute($this->_db);

			if ( ! array_key_exists($this->_primaryKey, $this->_data))
			{
				// Load the insert id as the primary key if it was left out
				$this->_data[$this->_primaryKey] = $result[0];
			}
		}

		$this->_saved = TRUE;
		$this->_loaded = TRUE;

		//remove instances of this model from cache
		try
		{
			Cache::instance('memcachetag')->delete(strtolower(get_class($this)) . '-' . $this->_data[$this->_primaryKey]);
		}
		catch(Exception $e)
		{
			//couldn't delete the cached version
			Logging::factory()->log('Could not delete cache: ' . (string) $this, 'Model', 'Warning');
		}

		return $this;
	}

	/**
	 * Returns whether the object has been saved.
	 * When changing the value of a column this is set to false.
	 * @return boolean
	 */
	public function saved()
	{
		return $this->_saved;
	}

	/**
	 * Attempts to load object with primary key $id.
	 * @param mixed $id 
	 * @return Model
	 */
	public function load($id)
	{
		if($this->loaded() AND ! $this->loaded_by_array())
			return $this;

		$modelName = get_class($this);

		//check the cache before hand
		try
		{
			$object_data = Cache::instance('memcachetag')->get(strtolower($modelName) . '-' . $id);
		}
		catch(Exception $e)
		{
			//Issue with the cache, so we will not load anything from cache
			$object_data = '';
			Logging::factory()->log('Could not get cache: ' . (string) $this, 'Model', 'Warning');
		}

		if( ! empty($object_data))
		{
			try
			{
				$object = unserialize($object_data);
				if(is_object($object))
				{
					$this->load_array($object->as_array());
					$this->_cache = TRUE;
					$this->_loaded_by_array = FALSE;

					return $this;
				}
			}
			catch(Exception $e)
			{
				//object didn't exist or is not valid
				unset($object_data);
			}
		}

		$result = DB::select_array($this->_columns)
					->from($this->_tableName)
					->where($this->_primaryKey,'=',$id)
					->execute($this->_db);
		
		//set the data
		if(count($result) == 1)
		{
			$this->_data = $this->_decrypt($result[0]);

			$this->_saved = TRUE;
			$this->_loaded = TRUE;
			$this->_loaded_by_array = FALSE;

			//cache the resulting object
			try
			{
				Cache::instance('memcachetag')->set_with_tags(strtolower($modelName) . '-' . $id, serialize($this), 3600, array(strtolower($modelName), strtolower($modelName) . '-' . $id));
			}
			catch(Exception $e)
			{
				//could not save to the cache.
				Logging::factory()->log('Could not set with tags: ' . (string) $this, 'Model', 'Warning');
			}
		}

		return $this;
	}

	/**
	 * Returns whether the object is loaded or not.
	 * @return boolean
	 */
	public function loaded()
	{
		return $this->_loaded;
	}

	/**
	 * Deletes the object from the DB and removes the cached object.
	 * Returns false is the object was not removed.
	 * @return boolean
	 */
	public function delete()
	{
		if( ! $this->loaded())
		{
			return FALSE;
		}

		try
		{
			DB::delete($this->_tableName)
				->where($this->_primaryKey, '=', $this->_data[$this->_primaryKey])
				->execute($this->_db);
		}
		catch(Exception $e)
		{
			//couldn't delete the cached version
			Logging::factory()->log('Could not delete: ' . (string) $this, 'Model', 'Warning');
			return FALSE;
		}

		//delete the cache
		$modelName = get_class($this);
		try
		{
			Cache::instance('memcachetag')->delete(strtolower($modelName) . '-' . $this->_data[$this->_primaryKey]);
		}
		catch(Exception $e)
		{
			//couldn't delete the cached version
			Logging::factory()->log('Could not delete cache: ' . (string) $this, 'Model', 'Warning');
		}
		//clear the object
		$this->_data = array();
		$this->_data_changed = array();
		$this->_saved = FALSE;
		$this->_loaded = FALSE;

		return TRUE;
	}
}