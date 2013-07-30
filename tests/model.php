<?php defined('SYSPATH') or die('No direct script access.');

class ModelTest extends Kohana_Unittest_TestCase
{
	public function testNewModelMySQL ()
	{
		$model = Model::factory('example');

		//make sure model is instantiated
		$this->assertInstanceOf('Model_Example', $model);

		//make sure the column is null
		$this->assertNull($model->username);

		//set the column
		$username = 'cesar' . rand();
		$model->username = $username;
		$this->assertSame($username, $model->username);
	}

	/**
	 * @depends testNewModelMySQL
	 */
	public function testSaveModelMySQL()
	{
		$model = new Model_Example;

		//set data
		$username = 'cesar' . rand();
		$email = $username . '@dudeitscesar.com';
		$model->username = $username;
		$model->email = $email;
		$model->password = 'test';

		//save the record
		$model->save();
		$this->assertTrue($model->saved());

		//change a column, no longer saved
		$model->username = rand();
		$this->assertFalse($model->saved());

		//save the model id
		$model_id = $model->id;

		//make sure id is set
		$this->assertInternalType('integer', $model->id);
		//make sure it's greater than 0
		$this->assertGreaterThan(0, $model->id);

		//make sure it's now loaded
		$this->assertTrue($model->loaded());

		//now LOAD the model and make sure the data is the same
		$model = Model::factory('example', $model_id);

		//make sure it's now loaded
		$this->assertTrue($model->loaded());

		//make sure username is the same
		$this->assertEquals($username, $model->username);
		$this->assertEquals($email, $model->email);

		//should be saved, nothing changed
		$this->assertTrue($model->saved());

		//and ids should be the same
		$this->assertEquals($model_id, $model->id);

		return $model->id;
	}

	/**
	 * @depends testSaveModelMySQL
	 */
	public function testLoadModelMySQL($id)
	{
		//do not use the factory
		$model = new Model_Example;

		//make sure it's a Model_Example
		$this->assertInstanceOf('Model_Example', $model);

		//load data
		$model->load($id);

		$previousUsername = $model->username;

		//make sure that it's loaded, id == 5, and username is not null
		$this->assertTrue($model->loaded());
		$this->assertEquals($id, $model->id);
		$this->assertNotEquals(null, $model->username);

		//make sure fields still behave
		$username = 'cesar' . rand();
		$model->username = $username;
		$this->assertSame($username, $model->username);

		//test array_loading
		$data = $model->as_array();
		unset($data['username']);

		$model = new Model_Example;
		$model->load_array($data);

		$this->assertTrue($model->loaded());
		$this->assertTrue($model->loaded_by_array());
		$this->assertSame($previousUsername, $model->username);
		$this->assertFalse($model->loaded_by_array());

		return $model->id;
	}

	/**
	 * @depends testLoadModelMySQL
	 */
	public function testUpdateModelMySQL($id)
	{
		//load a new model
		$model = Model::factory('example', $id);

		//make sure it's loaded
		$this->assertTrue($model->loaded());
		$username = 'cesar' . rand();
		$model->username = $username;

		//save it
		$model->save();
		$this->assertTrue($model->saved());

		//reload it
		$model = Model::factory('example', $id);

		//make sure it's loaded
		$this->assertTrue($model->loaded());

		//same username
		$this->assertSame($username, $model->username);

		return $model->id;
	}

	/**
	 * @depends testUpdateModelMySQL
	 */
	public function testDeleteModelMySQL($id)
	{
		//load by factory
		$model = Model::factory('example', $id);

		//assert is a model
		$this->assertInstanceOf('Model_Example', $model);

		//make sure it's loaded
		$this->assertTrue($model->loaded());
		$this->assertEquals($id, $model->id);

		//delete it
		$this->assertTrue($model->delete());

		$model = Model::factory('example', $id);
		//make sure it's not loaded
		$this->assertFalse($model->loaded());
	}

	public function testNewModelMongo ()
	{
		$model = Model::factory('examplemongo');

		//make sure model is instantiated
		$this->assertInstanceOf('Model_ExampleMongo', $model);

		//make sure the column is null
		$this->assertNull($model->username);

		//set the column
		$username = 'cesar' . rand();
		$model->username = $username;
		$this->assertSame($username, $model->username);
	}

	/**
	 * @depends testNewModelMongo
	 */
	public function testSaveModelMongo()
	{
		$model = new Model_ExampleMongo;

		//set data
		$username = 'cesar' . rand();
		$email = $username . '@dudeitscesar.com';
		$model->username = $username;
		$model->email = $email;

		//save the record
		$model->save();
		$this->assertTrue($model->saved());

		//change a column, no longer saved
		$model->username = rand();
		$this->assertFalse($model->saved());

		//save the model id
		$model_id = $model->pk();

		//make sure id is set
		$this->assertInternalType('object', $model->_id);
		//mongo id?
		$this->assertInstanceOf('MongoID', $model->_id);

		//make sure it's now loaded
		$this->assertTrue($model->loaded());

		//now LOAD the model and make sure the data is the same
		$model = Model::factory('examplemongo', $model_id);

		//make sure it's now loaded
		$this->assertTrue($model->loaded());

		//make sure username is the same
		$this->assertEquals($username, $model->username);
		$this->assertEquals($email, $model->email);

		//should be saved nothing changed.
		$this->assertTrue($model->saved());

		//and ids should be the same
		$this->assertEquals($model_id, $model->pk());

		return $model->pk();
	}

	/**
	 * @depends testSaveModelMongo
	 */
	public function testLoadModelMongo($id)
	{
		//do not use the factory
		$model = new Model_ExampleMongo;

		//make sure it's a Model_Example
		$this->assertInstanceOf('Model_ExampleMongo', $model);

		//load data
		$model->load((string) $id);

		$previousUsername = $model->username;

		//make sure that it's loaded, id == 5, and username is not null
		$this->assertTrue($model->loaded());
		$this->assertEquals((string) $id, (string) $model->pk());
		$this->assertNotEquals(null, $model->username);

		//make sure fields still behave
		$username = 'cesar' . rand();
		$model->username = $username;
		$this->assertSame($username, $model->username);

		//test array_loading
		$data = $model->as_array();
		unset($data['username']);

		$model = new Model_ExampleMongo;
		$model->load_array($data);

		$this->assertTrue($model->loaded());
		$this->assertTrue($model->loaded_by_array());
		$this->assertSame($previousUsername, $model->username);
		$this->assertFalse($model->loaded_by_array());

		return $model->pk();
	}

	/**
	 * @depends testLoadModelMongo
	 */
	public function testUpdateModelMongo($id)
	{
		//load a new model
		$model = Model::factory('examplemongo', $id);

		//make sure it's loaded
		$this->assertTrue($model->loaded());
		$username = 'cesar' . rand();
		$model->username = $username;

		//save it
		$model->save();
		$this->assertTrue($model->saved());

		//reload it
		$model = Model::factory('examplemongo', $id);

		//make sure it's loaded
		$this->assertTrue($model->loaded());

		//same username
		$this->assertSame($username, $model->username);

		return $model->pk();
	}

	/**
	 * @depends testUpdateModelMongo
	 */
	public function testDeleteModelMongo($id)
	{
		//load by factory
		$model = Model::factory('examplemongo', $id);

		//assert is a model
		$this->assertInstanceOf('Model_ExampleMongo', $model);

		//make sure it's loaded
		$this->assertTrue($model->loaded());
		$this->assertEquals($id, $model->pk());

		//delete it
		$this->assertTrue($model->delete());

		$model = Model::factory('examplemongo', (string) $id);
		//make sure it's not loaded
		$this->assertFalse($model->loaded());
	}
}
