<?php

namespace App;

use \PDO;
use \DateTime;

class Database
{
	private $pdo;

	private $statement;

	private $total;
	private $order;
	private $ignore;
	private $page;
	private $maxrows;

	function __construct() {}

	private function getPDO()
	{
		if (is_null($this->pdo))
		{
			$this->pdo = new PDO('mysql:host='.DB_SERV.';dbname='.DB_NAME, DB_USER, DB_PASS, array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	        	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	        	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	        ));
		}
		return $this->pdo;
	}

	public function lastInsertId()
	{
		return $this->getPDO()->lastInsertId();
	}

	public function exec($statement)
	{
		return $this->getPDO()->exec($statement);
	}

	public function query($statement)
	{
		return $this->getPDO()->query($statement);
	}

	public function fetch($statement)
	{
		return $this->getPDO()->query($statement)->fetch();
	}

	public function fetchAll($statement)
	{
		$this->statement = $statement;

		return $this->getPDO()->query($statement)->fetchAll();
	}

	public function fetchColumn($statement)
	{
		return $this->getPDO()->query($statement)->fetchColumn();
	}

	public function selectAll($params)
	{
		// SELECT
		$select = '';
		foreach ($params['select'] as $key => $field)
		{
			$select .= $field;
			if ($key < count($params['select']) - 1) $select .= ', ';
		}

		// FROM
		$from = $params['from']['table'];
		if (!empty($alias = $params['from']['alias']))
			$from .= ' '.$alias;

		// WHERE
		if (!empty($params['where']['conditions']))
		{
			$where = 'WHERE ';
			foreach ($params['where']['conditions'] as $key => $condition)
			{
				$where .= $condition;
				if ($key < count($params['where']['conditions']) - 1) $where .= ' AND ';
			}
		}
		
		// LIKE
		if (!empty($search = trim($params['where']['like']['search'])))
		{
			$where = (empty($params['where']['conditions'])) ? 'WHERE ' : $where.' AND ';

			$fields = $params['where']['like']['fields'];

			if (strpos($search, ' '))
			{
				$sections = array();

				foreach (explode(' ', $search) as $kt => $term)
				{
					foreach ($fields as $field)
					{
						array_push($sections, "$field LIKE '%$term%'");
					}

					if ($kt < count(explode(' ', $search)) - 1)
					{
						foreach ($sections as $section)
						{
							$section .= ' OR ';
						}
					}
				}

				$where .= '(';
				foreach ($sections as $ks => $section)
				{
					$where .= $section;

					if ($ks < count($sections) - 1)
					{
						$where .= ' OR ';
					}
				}
				$where .= ')';
			}
			else
			{
				$where .= '(';
				foreach ($fields as $kf => $field)
				{
					$where .= "$field LIKE '%".$search."%'";

					if ($kf < count($fields) - 1)
					{
						$where .= ' OR ';
					}
				}
				$where .= ')';
			}
		}

		$this->total = $this->getPDO()->query("SELECT COUNT({$params['select'][0]}) FROM $from $where")->fetchColumn();

		// ORDER BY
		if (!empty($order = trim($params['orderby']['order'])))
		{
			$orderby = 'ORDER BY ';
			$can = array();
			
			foreach ($params['select'] as $elem)
			{
				array_push($can, (!strpos($elem, 'AS')) ? $elem : explode(' AS ', $elem)[1]);
			}

			$this->order = (in_array(explode(' ', $order)[0], $can)) ? $order : $params['orderby']['default'];
			$orderby .= $this->order;
		}
		else if (!empty($order = trim($params['orderby']['default'])))
		{
			$orderby = 'ORDER BY '.$params['orderby']['default'];
		}

		// LIMIT
		if (!empty($params['limit']))
		{
			$this->page = (empty($params['limit']['page'])) ? 1 : $params['limit']['page'];
			
			$lastMaxrows = $this->getPDO()->query("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_MAX_ROWS'")->fetch();

			if (!empty($params['limit']['maxrows']) && $params['limit']['maxrows'] != $lastMaxrows['value'])
			{
				$this->getPDO()->exec("UPDATE ".PREFIX_."configuration SET value = {$params['limit']['maxrows']} WHERE name = 'BUNDLEMANAGER_MAX_ROWS'");

				$this->maxrows = $params['limit']['maxrows'];
			}
			else
			{
				$this->maxrows = $lastMaxrows['value'];
			}

			$this->ignore = ($this->page-1)*$this->maxrows;

			$limit = 'LIMIT '.$this->ignore.','.$this->maxrows;
		}

		$this->statement = "SELECT $select FROM $from $where $orderby $limit";

		return $this->getPDO()->query($this->statement)->fetchAll();
	}

	public function getStatement()
	{
		return str_replace("  ", " ", trim($this->statement));
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getIgnore()
	{
		return $this->ignore;
	}

	public function getPage()
	{
		return $this->page;	
	}

	public function getMaxrows()
	{
		return $this->maxrows;
	}

	public function getDatenow($format = 'Y-m-d H:i:s')
	{
		$now = new DateTime('NOW');
		return $now->format($format);
	}
}
