<?php
require_once 'Storage.php';

class dropr_Client_Storage_PDO implements dropr_Client_Storage {
	
	/**
	 * @var PDO
	 */
	private $pdo;
	
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
}
