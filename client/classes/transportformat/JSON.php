<?php
class pmq_Client_TransportFormat_JSON implements pmq_Client_TransportFormat
{
	public function formatData(array $data)
	{
		return json_encode($data);
	}
}