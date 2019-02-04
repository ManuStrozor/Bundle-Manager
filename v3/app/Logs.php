<?php
namespace App;

class Logs {
	private $db;
	private $table;

	/**
	 * Constructor of Logs class
	 * @param object (database instance) & string (logs table int the database)
	 */
	function __construct($db, $table) {
		$this->db = $db;
		$this->table = $table;
	}

	/**
	 * Register an event into the logs table
	 * @param string (title of the event) & string (description of the event)
	 */
	public function new($title, $description) {
		$this->db->exec("INSERT INTO {$this->table} (title, description, user_agent, ip_address, date_upd) VALUES (\"$title\", \"$description\", \"{$_SERVER['HTTP_USER_AGENT']}\", \"{$_SERVER['REMOTE_ADDR']}\", \"{$this->db->getDatenow()}\")");
	}
}
