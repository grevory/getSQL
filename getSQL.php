<?php

class getSQL
{
	
	public $db_type = "mysql";

	public $db_host = "localhost";
	public $db_user = "root";
	public $db_password = "";
	public $db_database = "";

	public $table = "";
	public $column = "id";
	public $value = "%";
	public $limit = false;

	public $table_columns = array();
	public $statements = array();

	public $skip_primary_keys= false;

	public $error = false;
	public $die_on_error = true;

	private $connection;

	public function __construct() {

		if ( ! ($connection = mysql_connect($this->db_host, $this->db_user, $this->db_password)) ) {
			$this->_set_error('Could not connect to your database.');
			return false;
		}

		if ( ! (mysql_select_db($this->db_database, $connection)) ) {
			$this->_set_error('Could not use database '.$this->db_database.'.');
			return false;
		}

		$this->connection = $connection;

	}

	public function insert_statement($search=false) {

		if ($search) {
			$this->value = $search;
		}

		if( ! ($columns_query = mysql_query('SHOW COLUMNS FROM '.$this->table)) ) {
			$this->_set_error('Could not get columns for table '.$this->table.'.');
			return false;
		}

		if (mysql_num_rows($columns_query) < 1) {
			$this->_set_error('No columns were found for table '.$this->table.'.');
			return false;
		}

		while ($column = mysql_fetch_assoc($columns_query)) {
			
			if ($column['Key']==='PRI' && $this->skip_primary_keys)
				continue;

			$this->table_columns[] = $column['Field'];
		}

		$select_statement = 'SELECT * FROM '.$this->table.' WHERE ' . $this->column . ' LIKE "' . $this->value . '"';

		if ($this->limit) {
			$select_statement .= 'LIMIT = ' . $this->limit;
		}

		if ( ! ($rows_query = mysql_query($select_statement)) ) {
			$this->_set_error('Could not run SQL query.');
		}

		if (mysql_num_rows($rows_query) < 1) {
			$this->_set_error('No rows were found in table '.$this->table.' that match your criteria.');
			return false;
		}

		while ($row = mysql_fetch_assoc($rows_query)) {

			$column_values = '';
			foreach ($this->table_columns as $column) {
				
				if ($column_values != '') {
					$column_values .= ',';
				}

				$column_values .= '"' . $row[$column] . '"';
			}

			$column_names = implode(',',$this->table_columns);

			$this->statements[] = 'INSERT INTO '.$this->table.' ('.$column_names.') VALUES ('.$column_values.')';
		}

		foreach ($this->statements as $statement) {
			echo '<p>'.$statement.';</p>';
		}

	}

	private function _set_error($msg) {

		// Reset the error if this function is called with no message
		if (!$msg) {
			$this->error = false;
			return false;
		}

		$this->error = $msg . ' ' . mysql_error($this->connection);

		if ($this->die_on_error) {
			die($this->error);
		}

	}

}