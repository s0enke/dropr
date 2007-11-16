<?php
require_once 'Storage.php';

class pmq_Client_Storage_PDO implements pmq_Client_Storage {
	
	/**
	 * @var PDO
	 */
	private $pdo;
	
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
}