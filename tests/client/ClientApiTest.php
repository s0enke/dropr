<?php
class ClientApiTest extends PHPUnit_Framework_TestCase
{
	
	private $client;
	
	public function setUp()
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../../client/classes'));
		
		/*
		 * Setup PDO
		 */
		$pdo = new PDO('sqlite::memory:');
		
		$createSql = file_get_contents('sql/schema.sql', true);
		
		$pdo->query($createSql);
		
		require_once 'Client.php';
		require_once 'storage/PDO.php';
		$this->client = new pmq_Client(new pmq_Client_Storage_PDO($pdo));
	}
	
	public function testBlah()
	{
		
	}
	
}