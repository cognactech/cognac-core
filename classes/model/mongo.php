<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MongoDB "driver" for models.
 * 
 * @author Cesar Gonzalez <cesar.gonzalez@cognactechnologies.com>
 */

class Model_Mongo extends Model
{
	public $type = 'nosql';
	public $subType = 'mongo';
	public $_primaryKey = '_id';
	public $_shardKey = NULL;

	/**
	 * Constructor for the mongo model.
	 * Sets the dbconfig used.
	 * If $_primaryKey is not in the columns already, we'll add it.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		if( ! in_array($this->_primaryKey, $this->_columns))
			$this->_columns[] = $this->_primaryKey;

		if(empty($this->_dbConfig))
			$this->_dbConfig = $this->subType;

		$config = Kohana::$config->load('nosql.' . $this->_dbConfig);

		$this->_db = NoSQL::instance('mongo', $config);

		if(empty($this->_dbName))
		{
			//it's empty, grab from the config
			$this->_dbName = $config['database'];
		}
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
		return array('_columns','_data','_tableName','_primaryKey','type','subType', '_loaded', '_dbName', '_dbConfig','_shardKey');
	}

	/**
	 * Wakup method to reinitialize MongoDB resource required by the object.
	 * @return void
	 */
	public function __wakeup()
	{
		if(empty($this->_dbConfig))
			$this->_dbConfig = $this->subType;

		$config = Kohana::$config->load('nosql.' . $this->_dbConfig);

		$this->_db = NoSQL::instance('mongo', $config);

		if(empty($this->_dbName))
		{
			//it's empty, grab from the config
			$this->_dbName = $config['database'];
		}
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
				//if the value was not changed, do not change it
				if($value === $this->_data[$column])
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
	 * This will store the object into the MongoDB.
	 * If a MongoID does not exist, it will generate one.
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
			
			$query	= array('_id' => new MongoID($this->pk()));
			$update	= array('$set' => $this->_data_changed);

			$result = $this->_db->update($this->_tableName, $query, $update);

			$this->_data = array_merge($this->_data, $this->_data_changed);
			
			//empty changed data
			$this->_data_changed = array();

			if($query['_id'] != $this->pk())
				$this->_data['_id'] = $query['_id'];
		}
		else
		{
			$this->_data['_id'] = new MongoID();
			$result = $this->_db->put($this->_tableName, array('item' => $this->_data));
		}

		$this->_saved = TRUE;
		$this->_loaded = TRUE;
		$this->_loaded_by_array = FALSE;

		//remove instances of this model from cache
		$modelName = get_class($this);
		try
		{
			Cache::instance('memcachetag')->delete(strtolower($modelName) . '-' . (string) $this->_data[$this->_primaryKey]);
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
			$object_data = Cache::instance('memcachetag')->get(strtolower($modelName) . '-' . (string) $id);
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

		$record	= $this->_db->get($this->_tableName, $id, $this->_columns);

		if (count($record) > 0)
		{
			$this->_data = array_merge($this->_data, $record);
			$this->_saved = TRUE;
			$this->_loaded = TRUE;
			$this->_loaded_by_array = FALSE;

			//cache the resulting object
			try
			{
				Cache::instance('memcachetag')->set_with_tags(strtolower($modelName) . '-' . (string) $id, serialize($this), 3600, array(strtolower($modelName), strtolower($modelName) . '-' . $id));
			}
			catch(Exception $e)
			{
				//couldn't save to the cache.
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
	 * Deletes the object and removes the cached object.
	 * Returns false is the object was not removed or was never loaded.
	 * @return boolean
	 */
	public function delete()
	{
		if( ! $this->loaded())
		{
			return FALSE;
		}

		$query = array('_id' => $this->_data[$this->_primaryKey]);
		$options = array('justOne' => TRUE, 'safe' => TRUE);
		
		$result = $this->_db->delete($this->_tableName, $query, $options);

		if(array_key_exists('ok', $result) AND $result['ok'] == 1 AND array_key_exists('n', $result) AND $result['n'] > 0)
		{
			//remove the cache and all data
			$modelName = get_class($this);
			try
			{
				Cache::instance('memcachetag')->delete(strtolower($modelName) . '-' . (string) $this->_data[$this->_primaryKey]);
			}
			catch(Exception $e)
			{
				//couldn't delete the cached version
				Logging::factory()->log('Could not delete cache: ' . (string) $this, 'Model', 'Warning');
			}

			//delete successfully
			$this->_data = array();
			$this->_data_changed = array();
			$this->_saved = FALSE;
			$this->_loaded = FALSE;

			return TRUE;
		}

		return FALSE;
	}

}