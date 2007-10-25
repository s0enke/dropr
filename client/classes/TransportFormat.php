<?php
interface pmq_Client_TransportFormat 
{
	public function formatData(array $data);
}