<?php

//
// Author: Maxim Harder (dev@devcraft.club) (c) 2021-2022
// Project: PostrgeSQL ORM Class
// File: PostgreSQL.php
// Desc: PDO PostreSQL Class
// Created:  2021-10-14T09:31:58.676Z
// Modified: 2022-05-13T06:11:18.881Z
//

namespace MaHarder\classes;

/**
 * PostgreSQL Class.
 */
class PostgreSQL {
	/**
	 * DB Host of PostgreSQL, default: localhost
	 * @var string
	 */
	private string $host; 
	/**
	 * DB name
	 * @var string
	 */
	private string $db;
	/**
	 * DB username (user login)
	 * @var string
	 */
	private string $user;
	/**
	 * DB user password
	 * @var string
	 */
	private string $password;
	/**
	 * DB port, default: 5432
	 * @var int
	 */
	private int $port;
	/**
	 * DB Connection
	 * When not all creditional are given returns a NULL
	 * 
	 * @var PDO
	 */
	private ?\PDO $pdo = null;	

	/**
	 * Class init / constructor.
	 *
	 * @param string $db		Database name
	 * @param string $user		Database user
	 * @param string $password	Database password
	 * @param string $host		Database host, default: localhost
	 * @param int    $port		Database port, default: 5432
	 *
	 * @return \PDO|null
	 */
	public function __construct($db, $user, $password, $host = 'localhost', $port = 5432) {
		$this->host = $host;
		$this->db = $db;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;

		return $this->_connect();
	}

	/**
	 * Automatically disconnects PDO connection.
	 */
	public function __destruct() {
		$this->pdo = null;
	}

	/**
	 * Public function to return setted PDO connection.
	 *
	 * @return \PDO|null
	 */
	public function getConnection() {
		return $this->pdo;
	}

	/**
	 * For inserting multiple values into the same table.
	 *
	 * @param string $table Table name
	 * @param array  $names List of keys
	 * @param array  $data  Array of data arrays for keys
	 *
	 * @return array []				Array of inserted values in DB
	 */
	public function insertList($table, $names = [], $data = [])	{
		$list = [];

		if (count($data) < 0) {
			foreach ($data as $id => $d) {
				$new_data = array_combine($names, $d);
				$list[] = $this->insert($table, $new_data);
			}
		}

		return $list;
	}

	/**
	 * Inserts single values into a table
	 * After success execution returns complete inserted data with row id as array.
	 *
	 * @param string $table    Table name
	 * @param array  $data     Array of keys and their values
	 * @param string $id_field Used for update data if dataset conflicts with existing entry, default: id
	 *
	 * @return array []		returns either array of data of inserted values or array with error and its description
	 */
	public function insert($table, $data = [], $id_field = 'id') {
		if (count($data) > 0) {
			$pre_data = $this->_prepareData($data);
			$names = $pre_data[0];
			$values = $pre_data[1];
			
			$sql_values = $names;
			foreach ($names as $id => $n) {
				$sql_values[$id] = ":{$n}";
			}

			// Prepare SQL string
			$sql = "INSERT INTO {$table} ( " .implode(', ', $names). ') VALUES (' . implode(', ', $sql_values) . ')';

			// $this->pdo->beginTransaction();

			try {
				$prepare = $this->pdo->prepare($sql);

				for ($i = 0, $max = count($names); $i < $max; ++$i) {
					$n = $names[$i];
					$v = $values[$i];
					$prepare->bindValue(':'.$n, $v);
				}

				if ($prepare->execute()) {
					// $this->pdo->commit();

					$arr = [];
					$arr[$id_field] = (int) $this->pdo->lastInsertId();

					return $this->fetch($table, ['*'], [
						'where' => [
							'arr' => $arr,
						],
					]);
				}
			} catch (\Exception $e) {
				// $this->pdo->rollBack();

				return [
					'status' => 'error',
					'message' => $e->getMessage(),
				];
			}
		}

		return [
			'status' => 'error',
			'message' => 'Data has not been transfred to request.',
		];
	}

	/**
	 * @param string  $table Table name
	 * @param array  $data  Data that will be insert or updated in table
	 * @param string $field ID field of the table for update entries, default: id
	 *
	 * @return array [] 	Returns answer of the request in form of an array
	 */
	public function insertOrUpdate($table, $data = [], $field = 'id') {
		try {
			try {
				return $this->insert($table, $data, $field);
			} catch (\Exception $th) {
				$arr = [];
				$arr[$field] = (int) $data[$field] ? (int) $data[$field] : $data[$field];

				return $this->update($table, $data, [
					'where' => [
						'arr' => $arr,
					],
				]);
			}
		} catch (\Exception $th) {
			return [
				'status' => 'error',
				'message' => $th->getMessage(),
			];
		}
	}

	/**
	 * Updates dataset in a table.
	 *
	 * @param string $table 		Table name
	 * @param array  $data  		Array of keys and their values
	 * @param array  $where			Array of parameters with predefined values
	 * 	@var string|array 'query' 	Custom where query without 'WHERE'
	 * 	@var array 'arr'			Array of keys and values for where clause
	 * 	@var string 'arr_param'		Binder between multiple search keys, default: AND
	 *
	 * @return array []			returns array of updated dataset
	 */
	public function update($table, $data = [], $where = [
		'query' => '',
		'arr' => [],
		'arr_param' => 'AND',
	]) {
		$where = array_merge_recursive(['query' => '', 'arr' => [], 'arr_param' => 'AND'], $where);

		if (count($data) > 0) {
			$pre_data = $this->_prepareData($data);
			$names = $pre_data[0];
			$values = $pre_data[1];

			$check_vals = array_combine($names, $values);

			$sql = "UPDATE {$table} SET ";
			$count = 1;
			foreach ($names as $id => $n) {
				$sql .= "{$n} = :{$n}";
				if ($count < count($names)) {
					$sql .= ', ';
				}
				++$count;
			}
			unset($count);

			if (is_array($where['query']) || !empty($where['query']) || count($where['arr']) > 0) {
				$sql .= ' WHERE ';

				if (is_array($where['query']) || !empty($where['query'])) {
					if (is_array($where['query'])) {
						foreach ($where['query'] as $key => $value) {
							if (empty($value)) {
								unset($where['query'][$key]);
							}
						}

						$query = implode(" {$where['arr_param']} ", $where['query']);
					} else {
						$query = $where['query'];
					}
					$sql .= $query;
				} else {
					$wh = [];
					foreach ($where['arr'] as $n => $v) {
						$v_a = $this->getComparer($v);
						$wh[] = "{$n} {$v_a['sign']} {$v_a['value']}";
					}

					$sql .= ' '.implode(" {$where['arr_param']} ", $wh);
				}
			}

			$sql .= ' RETURNING *';

			//  $this->pdo->beginTransaction();

			try {
				$prepare = $this->pdo->prepare($sql);

				$check_vals = array_merge($check_vals, $where['arr']);

				foreach ($check_vals as $n => $v) {
					$prepare->bindValue(":{$n}", $v);
				}

				return $prepare->execute();
				//    $this->pdo->commit();
			} catch (\Exception $e) {
				//    $this->pdo->rollBack();

				return [
					'status' => 'error',
					'message' => $e->getMessage(),
				];
			}
		}

		return [
			'status' => 'error',
			'message' => 'Data has not been transfred to request.',
		];
	}

	/**
	 * Fetches all rows in a single table.
	 *
	 * @param string $table Table name
	 * @param array $order Sort arguements of the output in format column => sort direction, eg. 'date' => 'ASC'
	 *
	 * @return array []			List of all entries in table
	 */
	public function fetchAll($table, $order = []) {
		$sql = "SELECT * FROM {$table}";

		if (count($order) > 0) {
			$sql .= ' ORDER by ';
			foreach ($order as $col => $sort) {
				$sql .= " {$col} {$sort}";
			}
		}

		$lines = [];

		$prepare = $this->pdo->prepare($sql);
		$prepare->execute();

		while ($row = $prepare->fetch(\PDO::FETCH_ASSOC)) {
			$lines[] = $row;
		}

		return $lines;
	}

	/**
	 * Fetches entries in DB with custom definitions.
	 *
	 * @param string $table  		Table name
	 * @param array  $select 		List of DB columns of the table, default: *
	 * @param array  $params 		Predefined array of parameters for custom selects
	 * 	@var array where 			Array of parameteres for where clause
	 *  	@var array|string query	Custom raw sql select (where clause) array of strings or a single string, use without 'WHERE'
	 *  	@var array arr			Array of where keys and these values
	 *  	@var string arr_param	Binder of multiple where array, default: AND
	 *  @var array order			Array of ORDER by statement, use this way: 'name' => 'SORT value', eg. 'date' => 'ASC'
	 *  @var int|string limit		Either ALL or an Integer to limit output, no use if ORDER is empty
	 *  @var int offset				Skips first X entries, no use if ORDER is empty
	 *
	 * @return array []				List of filtered entries
	 */
	public function fetch(string $table, array $select, array $params = ['where' => ['query' => null, 'arr' => [], 'arr_param' => 'AND'], 'order' => [], 'limit' => 'ALL', 'offset' => 0]) {
		$params = array_merge_recursive(['where' => ['query' => '', 'arr' => [], 'arr_param' => 'AND'], 'order' => [], 'limit' => 'ALL', 'offset' => 0], $params);
		$sel_val = '*';
		if (count($select) > 0) {
			$sel_val = implode(', ', $select);
		}

		$sql = "SELECT {$sel_val} FROM {$table}";

		if (is_array($params['where']['query']) || !empty($params['where']['query']) || count($params['where']['arr']) > 0) {
			$sql .= ' WHERE ';

			if (is_array($params['where']['query']) || !empty($params['where']['query'])) {
				if (is_array($params['where']['query'])) {
					foreach ($params['where']['query'] as $key => $value) {
						if (empty($value)) {
							unset($params['where']['query'][$key]);
						}
					}

					$query = implode(" {$params['where']['arr_param']} ", $params['where']['query']);
				} else $query = $params['where']['query'];
				
				$sql .= $query;
			} else {
				$wh_par = $this->_prepareData($params['where']['arr']);
				$w = [];

				for ($i = 0, $max = count($wh_par[0]); $i < $max; ++$i) {
					$n = $wh_par[0][$i];
					$v = $wh_par[1][$i];
					$v_a = $this->getComparer($v);
					$w[] = "{$n} {$v_a['sign']} {$v_a['value']}";
				}

				$sql .= implode(" {$params['where']['arr_param']} ", $w);
				unset($w);
			}
		}

		if (count($params['order']) > 0) {
			$sql .= ' ORDER by ';
			$o = [];
			foreach ($params['order'] as $key => $sort) {
				$sort = !empty($sort) ? $sort : 'ASC';
				$o[] = "{$key} {$sort}";
			}
			$sql .= implode(', ', $o);
			unset($o);

			$limit = ((int) $params['limit'] || 'ALL' === $params['limit']) ? $params['limit'] : 'ALL';
			$offset = (int) $params['offset'] ?: 0;
			$sql .= " LIMIT {$limit} OFFSET {$offset}";
		}

		$lines = [];

		$prepare = $this->pdo->prepare($sql);

		foreach ($params['where']['arr'] as $n => $v) {
			$prepare->bindValue(":{$n}", $v);
		}

		$this->pdo->beginTransaction();

		try {
			$prepare->execute();
			$this->pdo->commit();

			while ($row = $prepare->fetch(\PDO::FETCH_ASSOC)) {
				$lines[] = $row;
			}

			return $lines;
		} catch (\PDOException $e) {
			$this->pdo->rollBack();

			return [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * Deletes an entry in table by ID.
	 *
	 * @param string $table Table name
	 * @param string $field Columns for filter
	 * @param string  $id    Value of columns
	 *
	 * @return int|array rowCount()		Returns affected rows or error in form of an array
	 */
	public function delete($table, $field, $id)	{
		$v_a = $this->getComparer($id);
		$sql = "DELETE FROM {$table} WHERE {$field} {$v_a['sign']} {$v_a['value']}";

		$this->pdo->beginTransaction();

		try {
			$pre = $this->pdo->prepare($sql);
			$pre->execute();
			$this->pdo->commit();

			return $pre->rowCount();
		} catch (\Exception $e) {
			$this->pdo->rollBack();

			return [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * Deletes all entries in a table.
	 *
	 * @param string $table   Table name
	 * @param bool   $restart Restart sequences of table, default: true
	 * @param bool   $cascade Automatically truncate all tables that have foreign-key references to any of the named table, default: true
	 *
	 * @return int|array rowCount()		Returns affected rows or error in form of an array
	 */
	public function deleteAll($table, $restart = true, $cascade = true)	{
		$sql = "TRUNCATE {$table}";
		$sql .= ($restart) ? ' RESTART IDENTITY' : ' CONTINUE IDENTITY';
		$sql .= ($cascade) ? ' CASCADE' : ' RESTRICT';

		$this->pdo->beginTransaction();

		try {
			$pre = $this->pdo->prepare($sql);
			$pre->execute();
			$this->pdo->commit();

			return $pre->rowCount();
		} catch (\Exception $e) {
			$this->pdo->rollBack();

			return [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * Custom SQL query with secure parameters.
	 *
	 * @param string $query String with SQL query (for values use :column name)
	 * @param array  $vals  Array of column names and their values
	 *
	 * @return string|array $out			Returns custom query request or error in form of an array
	 */
	public function query($query, $vals = []) {
		$this->pdo->beginTransaction();

		try {
			$pre = $this->pdo->prepare($query);
			foreach ($vals as $n => $v) {
				$pre->bindValue(":{$n}", $v);
			}
			$out = $pre->execute();
			$this->pdo->commit();

			return $out;
		} catch (\Exception $e) {
			$this->pdo->rollBack();

			return [
				'status' => 'error',
				'message' => $e->getMessage(),
			];
		}
	}

	/**
	 * Checks value and converts it into given type.
	 *
	 * @param string|int|float|bool $value	The value for the column
	 * @param string $type					Type of the used value
	 *
	 * @return float|int|string|bool
	 */
	protected function defType($value, $type) {
		$output = null;

		if ('double' === $type) {
			$output = (float) $value;
		} elseif ('boolean' === $type) {
			$output = (bool) $value;
		} elseif ('integer' === $type) {
			$output = (int) $value;
		} else {
			$output = "'{$value}'";
		}

		return $output;
	}

	/**
	 * Checks value for sign for transmission into DB
	 * If %sign% has been given on the first place before the value,
	 * so function will transform it in right definition.
	 *
	 * ||	First sign	|	Description																								||
	 * ||		!		|	Negative statement. Transforms = into <> 																||
	 * ||	   <(=)		|	More (equal) than... statement. Transforms = into < or <=												||
	 * ||	   >(=)		|	Less (equal) than... statement. Transforms = into > or >=												||
	 * ||	   (!)%		|	(NOT) LIKE expression. Transforms = into (NOT) LIKE and value becomes % wrapped around (eg. %value%)	||
	 *
	 * For more statements please inform author
	 *
	 * @param string $value
	 *
	 * @return array 
	 * 	@var string sign 		statement sign (=, <>, <, >, etc)
	 * 	@var string value 		output value with predefined type
	 */
	protected function getComparer($value) {
		$firstSign = ['!', '<', '>', '%'];
		$secondSign = ['=', '%'];
		$type = gettype($value);
		$outSign = '=';
		$checkSign = null;

		if (!in_array($type, ['integer', 'double', 'boolean']) && in_array($value[0], $firstSign, true)) {
			$checkSign = $value[0];
			if (in_array($value[1], $secondSign, true)) {
				$checkSign .= $value[1];
				$value = substr($value, 2);
			} else {
				$value = substr($value, 1);
			}
		}

		if ('!' === $checkSign) {
			$outSign = '<>';
			if ('!%' === $checkSign) {
				$outSign = 'NOT LIKE';
				$value = '%'.$value.'%';
			}
		} elseif (in_array($checkSign, ['<', '>', '<=', '>='])) {
			$outSign = $checkSign;
		} elseif ('%' === $checkSign) {
			$outSign = 'LIKE';
			$value = '%'.$value.'%';
		}

		$value = $this->defType($value, $type);

		return [
			'sign' => $outSign,
			'value' => $value,
		];
	}

	/**
	 * Prepares array with splitted data
	 * Splits in keys and values.
	 *
	 * @param array $data Array with keys and values
	 *
	 * @return array []
	 * 	@var array '0'	Array of name keys
	 * 	@var array '1'	Array of values
	 */
	private function _prepareData($data = []) {
		$names = [];
		$values = [];

		if (count($data) > 0) {
			foreach ($data as $n => $v) {
				$names[] = $n;
				$values[] = $v;
			}
		}

		return [
			$names,
			$values,
		];
	}

	/**
	 * Uses defined private variables to connect to pqsql db.
	 *
	 * @return null|PDO
	 */
	private function _connect()	{
		if (!empty($this->user) && !empty($this->password) && !empty($this->db)) {
			try {
				$pdo = new \PDO("pgsql:host={$this->host};port={$this->port};dbname={$this->db};", $this->user, $this->password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

				if ($pdo) {
					$this->pdo = $pdo;
				}
			} catch (\PDOException $e) {
				exit($e->getMessage());
			}
		}
		
		return $this->pdo;
	}
}
