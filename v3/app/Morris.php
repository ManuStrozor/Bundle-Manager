<?php

namespace App;

class Morris
{
	private $db;
	private $interval;

	function __construct($database, $interval)
	{
		$this->db = $database;
		$this->interval = $interval;
	}

	private function sumify($from, $to)
	{
	    $result = $this->db->fetch("SELECT SUM(total_paid_tax_excl / (SELECT conversion_rate FROM ".PREFIX_."currency cy WHERE cy.id_currency = o.id_currency)) as total FROM ".PREFIX_."orders o WHERE date_add > '$from' AND date_add < '$to' AND valid = 1");

	    return round($result['total'], 2);
	}

	private function isWE($date)
	{
	    return (date('N', strtotime($date)) > 5);
	}

	private function ago($a, $format = 'Y-m-d')
	{
	    return date($format, strtotime(date()."$a day"));
	}

	public function getData()
	{
		$data = "[";
	    for ($i = 0; $i < $this->interval; $i++)
	    {
	    	$a = $i - $this->interval;

	        $data .= "{day:'".$this->ago($a)."',";
	        $data .= "value:".$this->sumify($this->ago($a), $this->ago($a+1)).",";
	        $data .= "format:'".$this->ago($a, 'd M')."'}";
	        if ($i < $this->interval-1) $data .= ",";
	    }
	    $data .= "]";

	    return $data;
	}

	public function getEvents()
	{
		$data = "[";
	    for ($i = 0; $i < $this->interval; $i++)
	    {
	    	$a = $i - $this->interval;

	        if ($this->isWE($this->ago($a)))
	        {
	            $data .= "'".$this->ago($a)."'";
	            if ($i < $this->interval-1) $data .= ",";
	        }
	    }
	    $data .= "]";

	    return $data;
	}
}
