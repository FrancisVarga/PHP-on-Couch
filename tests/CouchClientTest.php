<?php

namespace PHPOnCouch;

use InvalidArgumentException,
	PHPOnCouch\Exceptions,
	PHPUnit_Framework_TestCase,
	stdClass;

require_once join(DIRECTORY_SEPARATOR, [__DIR__, '_config', 'config.php']);

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-11-01 at 01:49:47.
 */
class CouchClientTest extends PHPUnit_Framework_TestCase
{

	private $host = 'localhost';
	private $port = '5984';
	private $updateFn = <<<EOT
function(doc,req) {
	var resp = {query:null,form:null};
	if ( "query" in req ) {
		resp.query = req.query;
	}
	if ( "form" in req ) {
		resp.form = req.form;
	}
	return [doc,{
			headers: {"Content-Type": "application/json"},
			body: JSON.stringify(resp)
		}];
}
EOT

	;

	/**
	 *
	 * @var PHPOnCouch\CouchClient
	 */
	private $client;

	/**
	 *
	 * @var PHPOnCouch\CouchClient
	 */
	private $aclient;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$config = \config::getInstance();
		$this->url = $config->getUrl($this->host, $this->port, null);
		$this->aUrl = $config->getUrl($this->host, $this->port, $config->getFirstAdmin());
		$this->couch_server = 'http://' . $this->host . ':' . $this->port . '/';

		$this->client = new CouchClient($this->url, 'couchclienttest');
		$this->aclient = new CouchClient($this->aUrl, 'couchclienttest');
		try {
			$this->aclient->deleteDatabase();
		} catch (\Exception $e) {
			
		}
		$this->aclient->createDatabase();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->client = null;
		$this->aclient = null;
	}

	/**
	 * @covers PHPOnCouch\CouchClient::revs()
	 */
	public function testRevs()
	{
		$cd = new CouchDocument($this->client);
		$cd->set(array(
			'_id' => 'somedoc'
		));
		$cd->property1 = "one";
		$cd->property2 = "two";
		$doc = $this->client->revs()->revs_info()->getDoc("somedoc");
		$this->assertObjectHasAttribute("_revisions", $doc);
		$this->assertObjectHasAttribute("ids", $doc->_revisions);
		$this->assertEquals(count($doc->_revisions->ids), 3);
		$this->assertObjectHasAttribute("_revs_info", $doc);
		$this->assertEquals(count($doc->_revs_info), 3);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::setQueryParameters
	 * @todo   Implement testSetQueryParameters().
	 */
	public function testSetQueryParameters()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::useDatabase
	 * @todo   Implement testUseDatabase().
	 */
	public function testUseDatabase()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::isValidDatabaseName
	 */
	public function testIsValidDatabaseName()
	{
		$matches = array(
			"Azerty" => false,
			"a-zer_ty" => true,
			"a(zert)y" => true,
			"4azerty" => false
		);
		foreach ($matches as $key => $val) {
			$this->assertEquals($val, CouchClient::isValidDatabaseName($key));
		}
	}

	/**
	 * @covers PHPOnCouch\CouchClient::listDatabases
	 * @todo   Implement testListDatabases().
	 */
	public function testListDatabases()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::createDatabase
	 * @todo   Implement testCreateDatabase().
	 */
	public function testCreateDatabase()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteDatabase
	 */
	public function testDeleteDatabase()
	{
		$back = $this->aclient->deleteDatabase();
		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals(true, $back->ok);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getDatabaseInfos
	 */
	public function testGetDatabaseInfos()
	{
		$infos = $this->client->getDatabaseInfos();
		$this->assertInternalType("object", $infos);
		$tsts = array(
			'db_name' => "couchclienttest",
			"doc_count" => 0,
			"doc_del_count" => 0,
			"purge_seq" => 0,
			"compact_running" => false,
			"disk_size" => false,
			"instance_start_time" => false,
			"disk_format_version" => false
		);
		foreach ($tsts as $attr => $value) {
			$this->assertObjectHasAttribute($attr, $infos);
			if ($value !== false) {
				$this->assertEquals($value, $infos->$attr);
			}
		}
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getDatabaseUri
	 */
	public function testGetDatabaseUri()
	{
		$this->assertEquals($this->couch_server . "couchclienttest", $this->client->getDatabaseUri());
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getDatabaseName
	 * @todo   Implement testGetDatabaseName().
	 */
	public function testGetDatabaseName()
	{
		$this->assertEquals("couchclienttest", $this->client->getDatabaseName());
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getServerUri
	 */
	public function testGetServerUri()
	{
		$this->assertEquals($this->couch_server . "couchclienttest", $this->client->getDatabaseUri());
	}

	/**
	 * @covers PHPOnCouch\CouchClient::databaseExists
	 */
	public function testDatabaseExists()
	{
		$exist = $this->client->databaseExists();
		$this->assertTrue($exist, "testing against an existing database");

		$client = new CouchClient($this->couch_server, "foofoofooidontexist");
		$this->assertFalse($client->databaseExists(), "testing against a non-existing database");
	}

	/**
	 * @covers PHPOnCouch\CouchClient::compactDatabase
	 * @todo   Implement testCompactDatabase().
	 */
	public function testCompactDatabase()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getMemberShip
	 */
	public function testGetMemberShip()
	{
		$memberShip = $this->aclient->getMemberShip();
		$this->assertInternalType('object', $memberShip);
		$this->assertObjectHasAttribute('all_nodes', $memberShip);
		$this->assertObjectHasAttribute('cluster_nodes', $memberShip);

		//Must know itself
		$this->assertInternalType('array', $memberShip->all_nodes);
		$this->assertGreaterThanOrEqual(1, sizeof($memberShip->all_nodes));

		//Must have itself
		$this->assertInternalType('array', $memberShip->cluster_nodes);
		$this->assertGreaterThanOrEqual(1, sizeof($memberShip->cluster_nodes));
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getConfig
	 * @dataProvider testGetconfigExceptionsProvider
	 * @depends testGetMemberShip
	 */
	public function testGetConfigExceptions($args, $exception)
	{
		$this->expectException($exception);
		call_user_func_array([$this->aclient, "getConfig"], $args);
	}

	public function testGetConfigExceptionsProvider()
	{
		return [
			[
				[null], InvalidArgumentException::class
			],
			[
				["", null, ""], InvalidArgumentException::class
			]
		];
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getConfig
	 * @depends testGetConfigExceptions
	 */
	public function testGetConfig()
	{
		$memberShip = $this->aclient->getMemberShip();
		$firstNode = $memberShip->all_nodes[0];
		$config1 = $this->aclient->getConfig($firstNode);

		//Returns a JSON object
		$this->assertInternalType("object", $config1);

		//Tests based on basic configuration
		$this->assertObjectHasAttribute('log', $config1);
		$this->assertObjectHasAttribute('level', $config1->log);
		$this->assertEquals('info', $config1->log->level);

		$log = $this->aclient->getConfig($firstNode, 'log');
		$this->assertInternalType('object', $log);
		$this->assertObjectHasAttribute('level', $log);
		$this->assertEquals($log->level, 'info');

		$level = $this->aclient->getConfig($firstNode, 'log', 'level');
		$this->assertEquals('info', $level);

		$this->expectException("PHPOnCouch\Exceptions\CouchNotFoundException");
		$this->aclient->getconfig($firstNode, 'not', 'existing');
	}

	/**
	 * @covers PHPOnCouch\CouchClient::setConfig
	 * @depends testGetConfig
	 * @dataProvider testSetConfigExceptionsProvider
	 */
	public function testSetConfigExceptions($args, $exception)
	{
		$this->expectException($exception);
		call_user_func_array([$this->aclient, "setConfig"], $args);
	}

	public function testSetConfigExceptionsProvider()
	{
		return [
			[
				[null, "", "", ""], InvalidArgumentException::class
			],
			[
				["", null, "", ""], InvalidArgumentException::class
			],
			[
				["", "", null, ""], InvalidArgumentException::class
			]
		];
	}

	/**
	 * @covers PHPOnCouch\CouchClient::setConfig
	 * @depends testSetConfigExceptions
	 */
	public function testSetConfig()
	{
		$node = $this->aclient->getMemberShip()->all_nodes[0];
		$oldValue = $this->aclient->setConfig($node, 'log', 'level', "debug");
		$this->assertEquals('info', $oldValue);

		$currValue = $this->aclient->getConfig($node, 'log', 'level');
		$this->assertEquals('debug', $currValue);

		//Undo changes
		$oldValue2 = $this->aclient->setConfig($node, 'log', 'level', 'info');
		$this->assertEquals('debug', $oldValue2);

		$currValue2 = $this->aclient->getConfig($node, 'log', 'level');
		$this->assertEquals('info', $currValue2);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteConfig
	 * @depends testSetConfig
	 * @dataProvider testDeleteConfigExceptionsProvider
	 * @param array $args The arguments to be passed to the deleteConfig()
	 * @param Exception $ex The exception exepected
	 */
	public function testDeleteConfigExceptions($args, $ex)
	{
		$this->expectException($ex);
		call_user_func_array([$this->aclient, "deleteConfig"], $args);
	}

	public function testDeleteConfigExceptionsProvider()
	{
		return [
			[
				[null, "", "", ""], InvalidArgumentException::class
			],
			[
				["", null, "", ""], InvalidArgumentException::class
			],
			[
				["", "", null, ""], InvalidArgumentException::class
			],
		];
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteConfig
	 * @depends testDeleteConfigExceptions
	 */
	public function testDeleteConfig()
	{
		//We add a random config entry
		$node = $this->aclient->getMemberShip()->all_nodes[0];

		$this->aclient->setConfig($node, 'testing', 'stuff', 'here');
		$oldValue = $this->aclient->deleteConfig($node, 'testing', 'stuff');
		$this->assertEquals($oldValue, 'here');


		$this->expectException("PHPOnCouch\Exceptions\CouchNotFoundException");
		$this->aclient->getConfig($node, 'testing', 'stuff');
	}

	/**
	 * @covers PHPOnCouch\CouchClient::cleanupDatabaseViews
	 * @todo   Implement testCleanupDatabaseViews().
	 */
	public function testCleanupDatabaseViews()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::feed
	 * @todo   Implement testFeed().
	 */
	public function testFeed()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::filter
	 * @todo   Implement testFilter().
	 */
	public function testFilter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getChanges
	 * @todo   Implement testGetChanges().
	 */
	public function testGetChanges()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::open_revs
	 * @todo   Implement testOpen_revs().
	 */
	public function testOpen_revs()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getDoc
	 * @todo   Implement testGetDoc().
	 */
	public function testGetDoc()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::storeDoc
	 */
	public function testStoreDoc()
	{

		//Test 1
		$test1 = array("_id" => "great", "type" => "array");
		$this->expectException(InvalidArgumentException::class);
		$this->client->storeDoc($test1);

		//Test 2
		$test2 = new \stdclass();
		$test2->_id = "great";
		$test2->_type = "object";
		$this->expectException(InvalidArgumentException::class);
		$this->client->storeDoc($test2);

		//Test 3
		$infos = $this->client->getDatabaseInfos();
		$test3 = new \stdclass();
		$test3->_id = "great";
		$test3->type = "object";
		$this->client->storeDoc($test3);
		$infos2 = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count + 1, $infos2->doc_count);
		$doc = $this->client->getDoc("great");
		$this->assertInternalType("object", $doc);
		$this->assertObjectHasAttribute("type", $doc);
		$this->assertEquals("object", $doc->type);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::storeDocs
	 */
	public function testStoreDocs()
	{
		$data = array(
			new \stdclass(),
			new \stdclass(),
			new \stdclass()
		);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 0);

		$stored = $this->client->storeDocs($data);
// 		print_r($stored);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 3);

		$data[0]->_id = "test";
		$data[0]->type = "male";
		$data[1]->_id = "test";
		$data[1]->type = "female";
		$data[2]->_id = "test";
		$data[2]->type = "both";
		$stored = $this->client->storeDocs($data);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 4);

		$doc = $this->client->conflicts()->getDoc("test");
		$this->assertInternalType("object", $doc);
		$this->assertObjectNotHasAttribute("_conflicts", $doc);
		$data[0]->_id = "test2";
		$data[1]->_id = "test2";
		$data[2]->_id = "test2";
		$stored = $this->client->storeDocs($data);
		$this->assertInternalType("array", $stored);
		$this->assertEquals(count($stored), 3);
		foreach ($stored as $s) {
			if ($s == reset($stored))
				continue; //Skip first document because he's legit.
			$this->assertInternalType("object", $s);
			$this->assertObjectHasAttribute("error", $s);
			$this->assertEquals($s->error, "conflict");
		}
		$doc = $this->client->conflicts()->getDoc("test2");
		$this->assertObjectNotHasAttribute("_conflicts", $doc);

		//Part 2

		$data = array(
			new \stdclass(),
			new \stdclass(),
			new \stdclass()
		);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 5);

		$data[0]->_id = "test";
		$data[0]->type = "male";
		$data[1]->_id = "test";
		$data[1]->type = "female";
		$data[2]->_id = "test";
		$data[2]->type = "both";
		$stored = $this->client->storeDocs($data);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 5);
		$doc = $this->client->conflicts()->getDoc("test");
		$this->assertObjectNotHasAttribute("_conflicts", $doc); //No conflicts with the new bulk semantic

		$data[0]->_id = "test2";
		$data[0]->type = "male";
		$data[1]->_id = "test2";
		$data[1]->type = "female";
		$data[2]->_id = "test2";
		$data[2]->type = "both";

		$stored = $this->client->storeDocs($data);
		$infos = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count, 5);
		$doc = $this->client->conflicts()->getDoc("test2");
		$this->assertObjectNotHasAttribute("_conflicts", $doc);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteDocs
	 * @todo   Implement testDeleteDocs().
	 */
	public function testDeleteDocs()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::updateDoc
	 */
	public function testUpdateDoc()
	{

		$ddoc = new stdClass();
		$ddoc->_id = "_design/test";
		$ddoc->updates = array("test" => $this->updateFn);
		$this->aclient->storeDoc($ddoc);
		$doc = new stdClass();
		$doc->_id = "foo";
		$this->client->storeDoc($doc);


		$update = $this->aclient->updateDoc("test", "test", array());
		$this->assertInternalType("object", $update);
		$this->assertObjectHasAttribute("query", $update);
		$this->assertInternalType("object", $update->query);
		$this->assertEquals(0, count((array) $update->query));
		$this->assertObjectHasAttribute("form", $update);
		$this->assertInternalType("object", $update->form);
		$this->assertEquals(0, count((array) $update->form));

		$update = $this->aclient->updateDoc("test", "test", array("var1" => "val1/?\"", "var2" => "val2"));
		$this->assertInternalType("object", $update);
		$this->assertObjectHasAttribute("query", $update);
		$this->assertInternalType("object", $update->query);
		$this->assertEquals(2, count((array) $update->query));
		$this->assertObjectHasAttribute("var1", $update->query);
		$this->assertInternalType("string", $update->query->var1);
		$this->assertEquals("val1/?\"", $update->query->var1);



		$this->assertObjectHasAttribute("form", $update);
		$this->assertInternalType("object", $update->form);
		$this->assertEquals(0, count((array) $update->form));
	}

	/**
	 * @covers PHPOnCouch\CouchClient::updateDocFullAPI
	 */
	public function testUpdateDocFullAPI()
	{
		$ddoc = new stdClass();
		$ddoc->_id = "_design/test";
		$ddoc->updates = array("test" => $this->updateFn);
		$this->aclient->storeDoc($ddoc);
		$doc = new stdClass();
		$doc->_id = "foo";
		$this->client->storeDoc($doc);

		$update = $this->aclient->updateDocFullAPI("test", "test", array(
			"data" => array("var1" => "val1/?\"", "var2" => "val2")
		));
		$this->assertInternalType("object", $update);
		$this->assertObjectHasAttribute("query", $update);
		$this->assertInternalType("object", $update->query);
		$this->assertEquals(0, count((array) $update->query));
		$this->assertObjectHasAttribute("form", $update);
		$this->assertInternalType("object", $update->form);
		$this->assertEquals(2, count((array) $update->form));
		$this->assertObjectHasAttribute("var1", $update->form);
		$this->assertInternalType("string", $update->form->var1);
		$this->assertEquals("val1/?\"", $update->form->var1);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::copyDoc
	 * @todo   Implement testCopyDoc().
	 */
	public function testCopyDoc()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::storeAsAttachment
	 */
	public function testStoreAsAttachment()
	{
		$cd = new CouchDocument($this->aclient);
		$cd->set(array(
			'_id' => 'somedoc'
		));
		$back = $cd->storeAsAttachment("This is the content", "file.txt", "text/plain");
		$fields = $cd->getFields();

		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$this->assertObjectHasAttribute("_attachments", $fields);
		$this->assertObjectHasAttribute("file.txt", $fields->_attachments);

		$cd = new CouchDocument($this->client);
		$cd->set(array(
			'_id' => 'somedoc2'
		));
		$back = $cd->storeAttachment(join(DIRECTORY_SEPARATOR, [__DIR__, '_config', 'test.txt']), "text/plain", "file.txt");
		$fields = $cd->getFields();

		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$this->assertObjectHasAttribute("_attachments", $fields);
		$this->assertObjectHasAttribute("file.txt", $fields->_attachments);

		$back = $cd->deleteAttachment("file.txt");
		$fields = $cd->getFields();
		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$test = property_exists($fields, '_attachments');
		$this->assertEquals($test, false);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::storeAttachment
	 */
	public function testStoreAttachment()
	{
		$cd = new CouchDocument($this->aclient);
		$cd->set(array(
			'_id' => 'somedoc'
		));
		$back = $cd->storeAsAttachment("This is the content", "file.txt", "text/plain");
		$fields = $cd->getFields();

		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$this->assertObjectHasAttribute("_attachments", $fields);
		$this->assertObjectHasAttribute("file.txt", $fields->_attachments);

		$cd = new CouchDocument($this->client);
		$cd->set(array(
			'_id' => 'somedoc2'
		));
		$back = $cd->storeAttachment(join(DIRECTORY_SEPARATOR, [__DIR__, '_config', 'test.txt']), "text/plain", "file.txt");
		$fields = $cd->getFields();

		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$this->assertObjectHasAttribute("_attachments", $fields);
		$this->assertObjectHasAttribute("file.txt", $fields->_attachments);

		$back = $cd->deleteAttachment("file.txt");
		$fields = $cd->getFields();
		$this->assertInternalType("object", $back);
		$this->assertObjectHasAttribute("ok", $back);
		$this->assertEquals($back->ok, true);
		$test = property_exists($fields, '_attachments');
		$this->assertEquals($test, false);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteAttachment
	 * @todo   Implement testDeleteAttachment().
	 */
	public function testDeleteAttachment()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::deleteDoc
	 * @dataProvider testDeleteDocProvider
	 */
	public function testDeleteDoc($doc, $ex)
	{
		if ($ex != null) {
			$this->expectException($ex);
			$this->aclient->deleteDoc($doc);
		} else {
			$validObject = (object) ['_id' => 'test2'];
			$doc = $this->client->storeDoc($validObject);
			$validObject->_rev = $doc->rev;
			$doc = $validObject;
			$result = $this->aclient->deleteDoc($doc);

			$this->assertInternalType('object', $result);
			$this->assertObjectHasAttribute('ok', $result);
			$this->assertObjectHasAttribute('rev', $result);
			$this->assertObjectHasAttribute('id', $result);
			$this->assertEquals($doc->_id, $result->id);
			$this->assertEquals(true, $result->ok);
		}
	}

	public function testDeleteDocProvider()
	{
		$invalidObject = (object) ['_id' => 'test'];
		$validObject = (object) ['_id' => 'test2', '_rev' => 'undefined'];

		return [
			["", InvalidArgumentException::class],
			[$invalidObject, \Exception::class],
			[null, null]
		];
	}

	/**
	 * @covers PHPOnCouch\CouchClient::asCouchDocuments
	 * @todo   Implement testAsCouchDocuments().
	 */
	public function testAsCouchDocuments()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::asArray
	 */
	public function testAsArray()
	{
		$infos = $this->client->getDatabaseInfos();
		$test = new \stdclass();
		$test->_id = "great";
		$test->type = "object";
		$this->client->storeDoc($test);
		$infos2 = $this->client->getDatabaseInfos();
		$this->assertEquals($infos->doc_count + 1, $infos2->doc_count);
		$doc = $this->client->asArray()->getDoc("great");
		$this->assertInternalType("array", $doc);
		$this->assertArrayHasKey("type", $doc);
		$this->assertEquals("object", $doc['type']);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getList
	 */
	public function testGetList()
	{
		$doc = new CouchDocument($this->aclient);
		$doc->_id = "_design/test";
		$views = array(
			"simple" => array(
				"map" => "function (doc) {
					if ( doc.type ) {
						emit( [ doc.type, doc._id ] , doc);
					}
				}"
			)
		);
		$lists = array(
			"list1" => "function (head, req) {
				var back = [];
				var row;
				while ( row = getRow() ) {
					back.push(row);
				}
				send(JSON.stringify(back));
			}"
		);
		$doc->views = $views;
		$doc->lists = $lists;

		$doc = new CouchDocument($this->aclient);
		$doc->_id = '_design/test2';
		$lists = array(
			"list2" => "function (head, req) {
				var back = [];
				var row;
				while ( row = getRow() ) {
					row.value='test2';
					back.push(row);
				}
				send(JSON.stringify(back));
			}"
		);
		$doc->lists = $lists;

		$docs = array(
			array('_id' => 'first', 'type' => 'test', 'param' => 'hello'),
			array('_id' => 'second', 'type' => 'test2', 'param' => 'hello2'),
			array('_id' => 'third', 'type' => 'test', 'param' => 'hello3')
		);
		$this->client->storeDocs($docs);
		$test = $this->client->getList('test', 'list1', 'simple');
		$this->assertInternalType("array", $test);
		$this->assertEquals(count($test), 3);
		foreach ($test as $row) {
			$this->assertInternalType("object", $row);
			$this->assertObjectHasAttribute('id', $row);
			$this->assertObjectHasAttribute('key', $row);
			$this->assertObjectHasAttribute('value', $row);
		}

		$test = $this->client->startkey(array('test'))->endkey(array('test', array()))->getList('test', 'list1', 'simple');
		$this->assertInternalType("array", $test);
		$this->assertEquals(count($test), 2);
		foreach ($test as $row) {
			$this->assertInternalType("object", $row);
			$this->assertObjectHasAttribute('id', $row);
			$this->assertObjectHasAttribute('key', $row);
			$this->assertObjectHasAttribute('value', $row);
		}
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getForeignList
	 */
	public function testGetForeignList()
	{
		$doc = new CouchDocument($this->aclient);
		$doc->_id = "_design/test";
		$views = array(
			"simple" => array(
				"map" => "function (doc) {
					if ( doc.type ) {
						emit( [ doc.type, doc._id ] , doc);
					}
				}"
			)
		);
		$lists = array(
			"list1" => "function (head, req) {
				var back = [];
				var row;
				while ( row = getRow() ) {
					back.push(row);
				}
				send(JSON.stringify(back));
			}"
		);
		$doc->views = $views;
		$doc->lists = $lists;
		$docs = array(
			array('_id' => 'first', 'type' => 'test', 'param' => 'hello'),
			array('_id' => 'second', 'type' => 'test2', 'param' => 'hello2'),
			array('_id' => 'third', 'type' => 'test', 'param' => 'hello3')
		);
		$this->client->storeDocs($docs);

		$doc = new CouchDocument($this->aclient);
		$doc->_id = '_design/test2';
		$lists = array(
			"list2" => "function (head, req) {
				var back = [];
				var row;
				while ( row = getRow() ) {
					row.value='test2';
					back.push(row);
				}
				send(JSON.stringify(back));
			}"
		);
		$doc->lists = $lists;

		$test = $this->client->startkey(array('test2'))->endkey(array('test2', array()))->getForeignList('test2', 'list2', 'test', 'simple');
		$this->assertInternalType("array", $test);
		$this->assertEquals(1, count($test));
		foreach ($test as $row) {
			$this->assertInternalType("object", $row);
			$this->assertObjectHasAttribute('id', $row);
			$this->assertObjectHasAttribute('key', $row);
			$this->assertObjectHasAttribute('value', $row);
			$this->assertEquals($row->value, 'test2');
		}

		$test = $this->client
				->startkey(array('test2'))
				->endkey(array('test2', array()))
				->include_docs(TRUE)
				->getForeignList('test2', 'list2', 'test', 'simple');
		$this->assertInternalType("array", $test);
		$this->assertEquals(count($test), 1);
		foreach ($test as $row) {
			$this->assertInternalType("object", $row);
			$this->assertObjectHasAttribute('id', $row);
			$this->assertObjectHasAttribute('key', $row);
			$this->assertObjectHasAttribute('value', $row);
			$this->assertObjectHasAttribute('doc', $row);
			$this->assertInternalType("object", $row->doc);
			$this->assertObjectHasAttribute('_id', $row->doc);
			$this->assertObjectHasAttribute('_rev', $row->doc);
			$this->assertEquals($row->value, 'test2');
		}
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getShow
	 */
	public function testGetShow()
	{
		$doc = new CouchDocument($this->aclient);
		$doc->_id = "_design/test";
		$show = array(
			"simple" => "function (doc, ctx) {
				ro = {body: ''};
				if ( ! doc ) {
					ro.body = 'no document';
				} else {
					ro.body = 'document: '+doc._id;
				}
				ro.body += ' ';
				var len = 0;
				for ( var k in ctx.query ) {
					len++;
				}
				ro.body += len;
				return ro;
			}",
			"json" => "function (doc, ctx) {
				ro = {body: ''};
				back = {doc: null};
				if ( doc ) {
					back.doc = doc._id;
				}
				var len = 0;
				for ( var k in ctx.query ) {
					len++;
				}
				back.query_length = len;
				ro.body = JSON.stringify(back);
				ro.headers = { \"content-type\": 'application/json' };
				return ro;
			}"
		);
		$doc->shows = $show;
		$test = $this->client->getShow("test", "simple", "_design/test");
		$this->assertEquals($test, "document: _design/test 0");
		$test = $this->client->getShow("test", "simple", "_design/test", array("param1" => "value1"));
		$this->assertEquals($test, "document: _design/test 1");
		$test = $this->client->getShow("test", "simple", null);
		$this->assertEquals($test, "no document 0");
		$test = $this->client->getShow("test", "simple", null, array("param1" => "value1"));
		$this->assertEquals($test, "no document 1");
		$test = $this->client->getShow("test", "json", null);
		$this->assertInternalType("object", $test);
		$this->assertObjectHasAttribute("doc", $test);
		$this->assertObjectHasAttribute("query_length", $test);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getViewInfos
	 */
	public function testGetViewInfos()
	{
		$dd = (object) ['_id' => '_design/testView', 'language' => 'javascript', 'views' => (object) []];
		$this->aclient->storeDoc($dd);

		$result = $this->aclient->getViewInfos('testView');
		$this->assertInternalType('object', $result);
		$this->assertObjectHasAttribute('name', $result);
		$this->assertObjectHasAttribute('view_index', $result);
		$this->assertInternalType('object', $result->view_index);
		$this->assertEquals('testView', $result->name);
		$this->assertEquals('javascript', $result->view_index->language);

		$this->expectException(InvalidArgumentException::class);
		$this->aclient->getViewInfos(null);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::compactViews
	 */
	public function testCompactViews()
	{
		$dd = (object) ['_id' => '_design/testView', 'language' => 'javascript', 'views' => (object) []];
		$this->aclient->storeDoc($dd);
		$result = $this->aclient->compactViews('testView');
		$this->assertInternalType('object', $result);
		$this->assertObjectHasAttribute('ok', $result);
		$this->assertEquals(true, $result->ok);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::compactAllViews
	 */
	public function testCompactAllViews()
	{
		$cd = new CouchDocument($this->aclient);
		$cd->set(array(
			'_id' => '_design/test',
			'language' => 'javascript'
		));
		$this->aclient->compactAllViews();
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getAllDocs
	 * @depends testStoreDoc
	 */
	public function testGetAllDocs()
	{
		$doc = (object) ['_id' => 'test1'];
		$this->aclient->storeDoc($doc);

		$result = $this->aclient->getAllDocs();

		$this->assertInternalType('object', $result);
		$this->assertObjectHasAttribute('total_rows', $result);
		$this->assertObjectHasAttribute('rows', $result);
		$this->assertInternalType('array', $result->rows);
		$this->assertInternalType('int', $result->total_rows);

		$this->assertCount(1, $result->rows);
	}

	/**
	 * @covers PHPOnCouch\CouchClient::getUuids
	 */
	public function testGetUuids()
	{
		$result = $this->aclient->getUuids();
		$this->assertInternalType('array', $result);
		$this->assertCount(1, $result);

		$this->expectException(InvalidArgumentException::class);
		$this->aclient->getUuids(-1);
	}

	/**
	 * @link http://docs.couchdb.org/en/2.0.0/api/database/compact.html#post--db-_ensure_full_commit
	 * @covers PHPOnCouch\CouchClient::ensureFullCommit
	 */
	public function testEnsureFullCommit()
	{
		$result = $this->aclient->ensureFullCommit();
		$this->assertInternalType('object', $result);
		$this->assertObjectHasAttribute('ok', $result);
		$this->assertObjectHasAttribute('instance_start_time', $result);
		$this->assertEquals(true, $result->ok);
	}

}
