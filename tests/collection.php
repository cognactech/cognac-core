<?php defined('SYSPATH') or die('No direct script access.');

class CollectionTest extends Kohana_Unittest_TestCase
{
	public function testCollectionMySQLFindAll ()
	{
		$Collection = Collection::instance('example');

		// make sure the instance method works
		$this->assertInstanceOf('Collection', $Collection);

		// we will select all records and all fields for each
		$Models = $Collection->find_all();

		$this->assertTrue( $Models->count() > 0 );

		$FirstModel = $Models->current();

		$this->assertInstanceOf('Model_Example', $FirstModel);

		$this->assertTrue( $FirstModel->loaded() );

		// loop results and check to see if each is a valid model
		foreach ($Models AS $Model)
		{
			// also make sure they are loaded objects
			$this->assertTrue( $Model->loaded() );

			$this->assertInstanceOf('Model_Example', $Model);
		}

		// lets try to get another current model
		// this should fail since we already moved the pointer to the end
		$LastModel = $Models->current();

		$this->assertFalse($LastModel);

		// rewind data
		$Models->rewind();

		// lets get the first element again
		// and make sure it matches the
		// first element we got the first time
		$NewFirstModel = $Models->current();

		$this->assertEquals($FirstModel, $NewFirstModel);

		// we will select all records and all fields for each
		$Models = $Collection->find_all(array(), array(), 0, 1);

		$this->assertEquals(1, $Models->count());

		// loop results and check to see if each is a valid model
		// with the keys specified and within our where ranges
		foreach ($Models AS $Model)
		{
			// also make sure they are loaded objects
			$this->assertTrue( $Model->loaded() );

			$this->assertInstanceOf('Model_Example', $Model);
		}
	}

	public function testCollectionMySQLCountAll()
	{
		$Collection = Collection::instance('example');

		$totalModels = $Collection->count_all();
		$this->assertTrue( $totalModels > 0 );

		$where = array('username' => 'nicholas');
		$singleModel = $Collection->count_all($where);
		$this->assertTrue( $singleModel > 0 );

		$where = array('username' => 'this-aint-nicholas');
		$noModels = $Collection->count_all($where);
		$this->assertTrue( $noModels === 0 );
	}

	public function testCollectionMongoFindAll ()
	{
		$Collection = Collection::instance('examplemongo');

		// make sure the instance method works
		$this->assertInstanceOf('Collection', $Collection);

		// we will select all records and all fields for each
		$Models = $Collection->find_all();

		$this->assertInstanceOf('Collection_Result', $Models);

		$this->assertTrue( $Models->count() > 0 );

		$FirstModel = $Models->current();

		$this->assertInstanceOf('Model_ExampleMongo', $FirstModel);

		$this->assertTrue( $FirstModel->loaded() );

		// loop results and check to see if each is a valid model
		foreach ($Models AS $Model)
		{
			$this->assertInstanceOf('Model_ExampleMongo', $Model);

			// also make sure they are loaded objects
			$this->assertTrue( $Model->loaded() );
		}

		// lets try to get another current model
		// this should fail since we already moved the pointer to the end
		$LastModel = $Models->current();

		$this->assertFalse($LastModel);

		// rewind data
		$Models->rewind();

		// lets get the first element again
		// and make sure it matches the
		// first element we got the first time
		$NewFirstModel = $Models->current();

		$this->assertEquals($FirstModel, $NewFirstModel);

		// we will select all records and all fields for each
		$Models = $Collection->find_all(array(), array(), 0, 1);

		$this->assertEquals(1, $Models->count() );

		// loop results and check to see if each is a valid model
		// with the keys specified and within our where ranges
		foreach ($Models AS $Model)
		{
			// also make sure they are loaded objects
			$this->assertTrue( $Model->loaded() );

			$this->assertInstanceOf('Model_ExampleMongo', $Model);
		}
	}

	public function testCollectionMongoCountAll()
	{
		$Collection = Collection::instance('examplemongo');

		$totalModels = $Collection->count_all();
		$this->assertTrue( $totalModels > 0 );

		$where = array('username' => 'nicholas');
		$singleModel = $Collection->count_all($where);
		$this->assertTrue( $singleModel > 0 );

		$where = array('username' => 'this-aint-nicholas');
		$noModels = $Collection->count_all($where);
		$this->assertTrue( $noModels === 0 );
	}

	public function testCollectionMongoResultCount()
	{
		$Collection = Collection::instance('examplemongo');

		// test that count behaves for all results
		$totalModels = $Collection->find_all();
		$this->assertEquals($totalModels->count(), $totalModels->count_all());

		// test that count behaves for single result
		$where = array('username' => 'nicholas');
		$singleModel = $Collection->find_all($where);
		$this->assertEquals($singleModel->count(), 1);
		$this->assertEquals($singleModel->count_all(), 1);

		// test that counts behave with or query
		$where = array('$or' => array(
			array('username' => 'nicholas'),
			array('username' => 'nicholas-test')
		));
		$noModels = $Collection->find_all($where);
		$this->assertEquals($noModels->count(), $noModels->count_all());

		// test that start limit work with or query
		$where = array('$or' => array(
			array('username' => 'nicholas'),
			array('username' => 'nicholas-unit-tester')
		));
		$noModels = $Collection->find_all($where, array(), 0 , 1 );
		$this->assertTrue( $noModels->count() === 1 );
		$this->assertTrue( $noModels->count_all() === 2 );
	}
}
