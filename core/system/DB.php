<?php

class DB{

	protected  $username;
	protected  $password;
	protected  $hostname;
	protected  $database;
	protected  $dbprefix		= '';
	protected  $char_set		= 'utf8';
	protected  $dbcollat		= 'utf8_general_ci';
	protected  $pconnect		= TRUE; 
	protected  $autoinit		= TRUE; 
	protected  $port			= '';
	protected  $conn_id			= FALSE;
	protected  $result_id		= FALSE;
	protected  $data_cache		= array();
	var $query_count			= 0;
	var $queries				= array();
	var $last					= '';
	var $num_rows				= 0;
	
	function __construct($_config = array())
	{
		foreach ($_config as $key => $value)
		{
			$this->$key = $value;
		}
		
		if ($this->autoinit)
		{
			$this->init();
		}
	}
	
	function __desctruct()
	{
		$this->close_connection();
	}
	
	function display_error($error = '', $swap = '', $native = FALSE)
	{
		$this->_reset_select();
		$this->_reset_write();
		$message = '';
		$query = '';
		$file = '';
		$line = '';
		$_debug =& load('Debug', 'units', TRUE);
		if (is_array($error) )
		{
			if (isset($error[2]))
			{
				$query = $error[2];
				$message = $error[1];
				$error = $error[0];
			}
			else {
				$error = $error[0];
			}
		}
		
		$trace = debug_backtrace();
		foreach ($trace as $call)
		{
			if ($call['file'] !== $trace[0]['file'])
			{
				$file = $call['file'];
				$line = $call['line'];
				break;
			}
		}
		
		$_debug->db($error, $message, $query, $file, $line);
		exit;
	}
	
	public function init($config = array())
	{
		if (is_array($config) && count($config) != 0)
		{
			if ((isset($_config['username']) && $this->username != $_config['username']) ||
				(isset($_config['password']) && $this->password != $_config['password']) || 
				(isset($_config['hostname']) && $this->password != $_config['hostname']) ||
				(isset($_config['database']) && $this->password != $_config['database']))
			{
				$this->close_connection();
			}
			
			foreach ($_config as $key => $value)
			{
				$this->$key = $value;
			}
		}
			
		if (is_resource($this->conn_id))
		{
			return;
		}
		
		if ($this->port != '')
		{
			$this->hostname .= ':'.$this->port;
		}
		
		if ($this->pconnect)
		{
			$this->conn_id = @mysql_pconnect($this->hostname, $this->username, $this->password);
		}
		else {
			$this->conn_id = @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
		}
		
		if ( ! $this->conn_id)
		{
			$this->display_error('db_unable_to_connect');
			return FALSE;
		}
		
		if ($this->database == '')
		{
			$this->display_error('db_wrong_database');
			return FALSE;
		}
		
		if ( ! @mysql_select_db($this->database, $this->conn_id))
		{
			$this->display_error('db_unable_connect_to_database', $this->database);
			return FALSE;
		}
		else
		{
			if (! @mysql_set_charset($this->char_set, $this->conn_id))
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	public function close_connection()
	{
		if (is_resource($this->conn_id))
		{
			@mysql_close($this->conn_id);
			$this->conn_id = FALSE;
			return TRUE;
		}
		return FALSE;
	}
	
	public function q($sql, $return = TRUE, $last = TRUE)
	{
		return $this->_query($sql, $return = TRUE, $last = TRUE);
	}
	public function query($sql, $return = TRUE, $last = TRUE)
	{
		return $this->_query($sql, $return = TRUE, $last = TRUE);
	}
	
	protected function _query($sql, $return = TRUE, $last = TRUE)
	{
		$sql = trim($sql);
		if ($sql == '')
		{
			$this->display_error('db_invalid_query');
		}
		
		if (! $this->conn_id)
		{
			$this->initialize();
		}
		
		if (FALSE === ($this->result_id = @mysql_query($sql, $this->conn_id)))
		{
			$error_no = mysql_errno($this->conn_id);
			$error_msg = mysql_error($this->conn_id);
			$this->display_error(array( $error_no, $error_msg, $sql));
		}
		
		if ($last)
		{
			$this->queries[] = $sql;
			$this->last = $sql;
			$this->query_count++;
		}
		
		if ((stripos('SELECT', $sql) === 0) && $return)
		{
			$return =  _get_array();
			return $return;
		}
		else {
			return $this->result_id;
		}
	}
	
	public function last()
	{
		return $this->last;
	}
	
	protected function _get_array()
	{
		$return = array();
		while ($row = mysql_fetch_assoc($this->result_id)) {
			$return[] = $row;
		}
		mysql_free_result($this->result_id);
		$this->result_id = FALSE;
		
		return $return;
	}
	
	function escape($str, $escape)
	{
		if (is_string($str) || is_numeric($str))
		{
			$str = $this->escape_str($str, $escape);
		}
		elseif (is_array($str))
		{
			$str = $this->escape_str($str, $escape);
		}
		elseif (is_bool($str))
		{
			$str = ($str === FALSE) ? '0' : '1';
		}
		elseif (is_null($str))
		{
			$str = 'NULL';
		}
		
		return $str;
	}
	
	function escape_str($str, $escape = TRUE, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $escape,$like);
			}
			return $str;
		}
		if ($escape)
		{
			$str = mysql_real_escape_string($str, $this->conn_id);
			if ($like === TRUE)
			{
				$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
			}
			$str = "'".$str."'";
		}
		

		return $str;
	}
	
	protected function _protect_identifiers($item, $escape = TRUE, $table = FALSE)
	{
		if (trim($item) == '' || is_bool($item) || is_null($item))
		{
			return $item;
		}
		if (is_array($item))
		{
			$escaped_array = array();
			foreach ($item as $k => $v)
			{
				$escaped_array[$this->_protect_identifiers(trim($k), $escape, $field)] 
								= $this->_protect_identifiers(trim($v), $escape, $field);
			}
			return $escaped_array;
		}
		
		$item = preg_replace('/[\t ]+/', ' ', $item);

		$alias = '';
		if (strpos($item, ' ') !== FALSE)
		{
			$alias = strstr($item, " ");
			$item = substr($item, 0, - strlen($alias));
		}

		if (strpos($item, '(') !== FALSE)
		{
			return $item.$alias;
		}

		if (strpos($item, '.') !== FALSE)
		{

			$parts	= explode('.', $item);
			
			if (in_array($parts[0], $this->lz_aliased_tables))
			{
				if ($escape === TRUE)
				{
					foreach ($parts as $key => $val)
					{
						if ($val != '*')
						{
							$parts[$key] = $this->_escape_identifiers($val);
						}
					}

					$item = implode('.', $parts);
				}
				return $item.$alias;
			}
			elseif ($this->dbprefix != '')
			{
				if (isset($parts[3]))
				{
					$i = 2;
				}
				elseif (isset($parts[2]))
				{
					$i = 1;
				}
				else
				{
					$i = 0;
				}
				if ($table === TRUE)
				{
					$i++;
				}
				if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix)
				{
					$parts[$i] = $this->dbprefix.$parts[$i];
				}
				
				$item = implode('.', $parts);
			}
			
			if ($escape === TRUE)
			{
				$item = $this->_escape_identifiers($item);
			}
			
			return $item.$alias;
		}
			
		if ($table && substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix)
		{
			$item = $this->dbprefix.$item;
		}
		
		if ($escape === TRUE)
		{
			$item = $this->_escape_identifiers($item);
		}
		
		return $item.$alias;
	}
	
	protected function _escape_identifiers($item)
	{
		if (is_array($item))
		{
			foreach ($item as $key => $value)
			{
				$item[$key] = $this->_escape_identifiers($value);
			}
			return $item;
		}
		$str = '`'.str_replace('.', '`.`', $item).'`';
		
		return preg_replace('/[`]+/', '`', $str);
	}
	
	protected function _has_operator($str)
	{
		$str = trim($str);
		preg_match("/([<>!=]+|is null|is not null)/i", $str, $match);
		if (isset($match[1]))
		{
			return trim($match[1]);
		}
		
		return '=';
	}
	
	protected function _track_aliases($table)
	{
		if (strpos($table, " ") !== FALSE)
		{
			$table = preg_replace('/ AS /i', ' ', $table);
			$table = trim(strrchr($table, " "));
			if ( ! in_array($table, $this->lz_aliased_tables))
			{
				$this->lz_aliased_tables[] = $table;
			}
		}
	}

	protected function _compile_select($select_override = FALSE)
	{
		$sql = '';
		$this->lz_countall = '';
		$select = '';
		
		if ($select_override !== FALSE)
		{
			$select = $select_override;
		}
		else
		{
			$select = (( ! $this->lz_distinct) ? 'SELECT ' : 'SELECT DISTINCT ');
			$this->lz_countall = $select.' SQL_NO_CACHE COUNT(*) as \'numrows\' ';

			if (count($this->lz_select) == 0)
			{
				$select .= '*';
			}
			else
			{
				$select .= implode(', ', $this->lz_select);
			}
		}
		
		if (count($this->lz_from) > 0)
		{
			$sql .= "\nFROM ";
			$sql .= '('.implode(', ', $this->lz_from).')';
		}
		
		if (count($this->lz_join) > 0)
		{
			$sql .= "\n".implode("\n\t", $this->lz_join);
		}
		
		if (count($this->lz_where) > 0)
		{
			$sql .= "\nWHERE ";
		}
		
		$sql .= implode("\n\t", $this->lz_where);
		
		if (count($this->lz_groupby) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $this->lz_groupby);
		}
		
		if (count($this->lz_having) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $this->lz_having);
		}
		$this->lz_countall .= $sql;
		
		if (count($this->lz_orderby) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $this->lz_orderby);

			if ($this->lz_order !== FALSE)
			{
				$sql .= ($this->lz_order == 'desc') ? ' DESC' : ' ASC';
			}
		}
		
		if (is_numeric($this->lz_limit))
		{
			$sql .= "\n";
			$sql .= " LIMIT ".(($this->lz_offset)? $this->lz_offset : '0').', '.$this->lz_limit;
		}
		
		$sql = $select.$sql;
		return $sql;
	}
	
	public function reset_select()
	{
		$this->_reset_select();
	}

	protected function _reset_select()
	{
		$this->lz_select 		= array();
		$this->lz_from 			= array();
		$this->lz_join 			= array();
		$this->lz_where 		= array();
		$this->lz_groupby 		= array();
		$this->lz_limit 		= array();
		$this->lz_having 		= array();
		$this->lz_having 		= array();
		$this->lz_orderby 		= array();
		$this->lz_aliased_tables= array();
		$this->lz_distinct		= FALSE;
		$this->lz_limit			= FALSE;
		$this->lz_offset		= FALSE;
	}

	protected function _reset_write()
	{
		$this->lz_set 		= array();
		$this->lz_from 		= array();
		$this->lz_where 	= array();
		$this->lz_where 	= array();
		$this->lz_orderby 	= array();
		$this->lz_limit 	= array();
		$this->lz_order 	= array();
	}
	
	var $lz_select				= array();
	var $lz_countall			= '';
	var $lz_distinct			= FALSE;
	var $lz_from				= array();
	var $lz_join				= array();
	var $lz_where				= array();
	var $lz_where_begin			= array();
	var $lz_where_end			= array();
	var $lz_groupby				= array();
	var $lz_having				= array();
	var $lz_limit				= FALSE;
	var $lz_offset				= FALSE;
	var $lz_order				= FALSE;
	var $lz_orderby				= array();
	var $lz_set					= array();
	var $lz_wherein				= array();
	var $lz_aliased_tables		= array();
	var $lz_count 				= 0;
	

	public function select($select = '*', $escape = TRUE)
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}
		
		foreach ($select as $val)
		{
			$val = trim(str_replace(array('`', '*'), '', $val));
			
			if ($val != '')
			{
				$alias = FALSE;
				if (preg_match('/(.*) AS (.*)/i', $val, $match))
				{
					$val = trim($match[1]); 
					$alias = trim($match[2]);
					$this->lz_aliased_fields[] = $alias; 
				}
				$this->lz_select[] = array(
									'field' => $val, 
									'function' => '',
									'alias' => mysql_escape_string($alias),
									'escape' => $escape);
			}
		}
		return $this;
	}
	
	public function select_if($if, $true, $false, $alias = '', $escape = TRUE)
	{
		$this->lz_select[] = array(
							'field' => $if, 
							'true' => $true,
							'false' => $false,
							'function' => 'IF',
							'alias' => mysql_escape_string($alias),
							'escape' => $escape);
		
		return $this;
	}

	public function select_max($select = '', $alias = FALSE)
	{
		return $this->_max_min_avg_sum($select, $alias, 'MAX');
	}

	public function select_min($select = '', $alias = FALSE)
	{
		return $this->_max_min_avg_sum($select, $alias, 'MIN');
	}

	public function select_avg($select = '', $alias = FALSE)
	{
		return $this->_max_min_avg_sum($select, $alias, 'AVG');
	}

	public function select_sum($select = '', $alias = FALSE)
	{
		return $this->_max_min_avg_sum($select, $alias, 'SUM');
	}

	protected function _max_min_avg_sum($select = '', $alias = FALSE, $type = 'MAX')
	{
		if ( ! is_string($select) OR $select == '')
		{
			$this->display_error('db_invalid_query');
		}
		
		if ($alias === FALSE)
		{
			$this->display_error('db_invalid_alias');
		}
		
		$this->lz_select[] = array(
								'field' => $select, 
								'function' => $type,
								'alias' => $alias,
								'escape' => NULL);
		
		return $this;
	}

	public function distinct($val = TRUE)
	{
		$this->lz_distinct = (is_bool($val)) ? $val : TRUE;
		return $this;
	}
	
	public function set($key, $value = '', $escape = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		foreach ($key as $k => $v)
		{
			$this->lz_set[] = array('field' => $k,
									'from' => NULL,
									'to' => $v,
									'function' => FALSE,
									'escape' => $escape);
		}
		return $this;
	}
	
	public function set_inc($key, $value = '', $escape = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		foreach ($key as $k => $v)
		{
			$this->lz_set[] = array('field' => $k,
									'from' => NULL,
									'to' => (int)$v,
									'function' => 'INCREMENT',
									'escape' => $escape);
		}
		return $this;
	}
	
	public function replace($field, $from, $to = '', $escape = TRUE)
	{
		if (is_array($field))
		{
			foreach ($field as $f)
			{
				$this->replace($f, $from, $to, $escape);
			}
			return $this;
		}
		
		if (strpos(',', $field) !== FALSE)
		{
			foreach (explode(',', $field) as $f)
			{
				$this->replace(trim($f), $from, $to, $escape);
			}
			return $this;
		}
		
		$this->lz_set[] = array('field' => $field,
								'from' => $from,
								'to' => $to,
								'function' => 'REPLACE',
								'escape' => $escape);
		return $this;
	}
	
	public function from($from, $escape = TRUE, $unshift = FALSE)
	{
		if (is_array($from))
		{
			foreach ($from as $f)
			{
				$this->from($f);
			}
		}
		else {
			if (strpos($from, ',') !== FALSE)
			{
				foreach (explode(',', $from) as $f)
				{
					$this->from($f);
				}
			}
			else
			{
				$from = trim($from);
				if ($from != '')
				{
					$from = str_ireplace(' AS ', ' ', $from);
					$from = explode(' ', $from);
					$from[1] = ((isset($from[1])) ? trim($from[1]) : FALSE);
					if (! $unshift)
					{
						$this->lz_from[] = array('table' => $from[0],
												'alias' => mysql_escape_string($from[1]),
												'escape' => $escape);
					}
					else {
						array_unshift($this->lz_from, array('table' => $from[0],
															'alias' => mysql_escape_string($from[1]),
															'escape' => $escape));
					}
					if ($from[1])
					{
						$this->lz_aliased_tables[] = $from[1];
					}
				}
			}
		}
		
		return $this;
	}
	
	public function join($table, $cond, $type = '', $escape = TRUE)
	{
		$type = strtoupper(trim($type));
		$type =  ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) ? '':  $type;
		
		$alias = $using = $where1 = $where2 = $operator = FALSE;
		
		if (preg_match('/(.*) AS (.*)/i', $table, $match))
		{
			$table = trim($match[1]); 
			$alias = trim($match[2]);
			$this->lz_aliased_tables[] = $alias; 
		}
		$table = str_replace('`', '', $table);
		$cond = str_replace('`', '', $cond);
		
		if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match))
		{
			$where1 = $match[1];
			$where2 = $match[3];
			$operator = trim($match[2]);
		}
		else
		{
			$using = $cond;
		}
		
		$this->lz_join[] = array(	'type' => $type,
									'table' => $table,
									'alias' => $alias,
									'using' => $using,
									'where1' => $where1,
									'operator' => $operator,
									'where2' => $where2,
									'escape' => $escape);
		
		return $this;
	}
	
	public function where($key, $value = NULL, $escape = NULL)
	{
		return $this->_where($key, $value, 'AND', $escape);
	}
	
	public function or_where($key, $value = NULL, $escape = NULL)
	{
		return $this->_where($key, $value, 'OR', $escape);
	}
	
	public function begin($type = 'AND')
	{
		$this->lz_where_begin[] = $type;
	}
	
	public function end()
	{
		end($this->lz_where);
		$this->lz_where[key($this->lz_where)]['end'] .= ')';
	}
	
	protected function _where($key, $value = NULL, $type = 'AND', $escape = NULL)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}
		
		if ( ! is_bool($escape))
		{
			$escape = TRUE;
		}
		
		$begin = '';
		for ($j = count($this->lz_where_begin) - 1; $j >= 0; $j--)
		{
			$type = $this->lz_where_begin[$j];
			$begin .= '(';
		}
		$this->lz_where_begin = array();
		
		foreach ($key as $k => $v)
		{
			$operator = $this->_has_operator($k);
			$k = trim(str_replace($operator, '', $k));
			
			$this->lz_where[] = array(	'field' => $k,
										'value' => $v,
										'operator' => $operator,
										'type' => $type,
										'not' => FALSE,
										'begin' => $begin,
										'end' => '',
										'escape' => $escape);
		}
		
		return $this;
	}
	
	public function where_in($key = NULL, $values = NULL, $escape = NULL)
	{
		return $this->_where_in($key, $values, FALSE,'AND', $escape);
	}
	
	public function or_where_in($key = NULL, $values = NULL, $escape = NULL)
	{
		return $this->_where_in($key, $values, FALSE, 'OR', $escape);
	}
	
	public function where_not_in($key = NULL, $values = NULL, $escape = NULL)
	{
		return $this->_where_in($key, $values, TRUE, 'AND', $escape);
	}
	
	public function or_where_not_in($key = NULL, $values = NULL, $escape = NULL)
	{
		return $this->_where_in($key, $values, TRUE, 'OR', $escape);
	}
	
	protected function _where_in($key = NULL, $values = NULL, $not = FALSE, $type = 'AND', $escape = NULL)
	{
		if ($key === NULL OR $values === NULL)
		{
			return;
		}
		if ( ! is_array($values))
		{
			$values = array($values);
		}
		if ( ! is_bool($escape))
		{
			$escape = TRUE;
		}
		
		$begin = '';
		for ($j = count($this->lz_where_begin) - 1; $j >= 0; $j--)
		{
			$type = $this->lz_where_begin[$j];
			$begin .= '(';
		}
		$this->lz_where_begin = array();
		
		$this->lz_where[] = array(	'field' => $key,
									'value' => $values,
									'operator' => 'IN',
									'type' => $type,
									'not' => $not,
									'begin' => $begin,
									'end' => '',
									'escape' => $escape);
		return $this;
	}
	
	public function like($field, $match = '', $side = 'both', $escape = NULL)
	{
		return $this->_like($field, $match, 'AND', $side, $escape);
	}
	
	public function not_like($field, $match = '', $side = 'both', $escape = NULL)
	{
		return $this->_like($field, $match, 'AND', $side, TRUE, $escape);
	}
	
	public function or_like($field, $match = '', $side = 'both', $escape = NULL)
	{
		return $this->_like($field, $match, 'OR', $side, $escape);
	}
	
	public function or_not_like($field, $match = '', $side = 'both', $escape = NULL)
	{
		return $this->_like($field, $match, 'OR', $side, TRUE, $escape);
	}
	
	protected function _like($field, $match = '', $type = 'AND', $side = 'both', $not = FALSE, $escape = NULL)
	{
		if ( ! is_array($field))
		{
			$field = array($field => $match);
		}
		
		if ( ! is_bool($escape))
		{
			$escape = TRUE;
		}

		foreach ($field as $k => $v)
		{
			$begin = '';
			for ($j = count($this->lz_where_begin) - 1; $j >= 0; $j--)
			{
				$type = $this->lz_where_begin[$j];
				$begin .= '(';
			}
			$this->lz_where_begin = array();
			
			$this->lz_where[] = array(	'field' => $k,
										'value' => $v,
										'operator' => 'LIKE',
										'side' => $side,
										'not' => $not,
										'type' => $type,
										'begin' => $begin,
										'end' => '',
										'escape' => $escape);
			
		}
		return $this;
	}

	public function between($field, $from, $to, $escape = NULL)
	{
		return $this->_between($field, $from, $to, $escape, 'AND', FALSE);
	}
	
	public function or_between($field, $from, $to, $escape = NULL)
	{
		return $this->_between($field, $from, $to, $escape, 'OR', FALSE);
	}
	
	public function not_between($field, $from, $to, $escape = NULL)
	{
		return $this->_between($field, $from, $to, $escape, 'AND', TRUE);
	}
	
	public function or_not_between($field, $from, $to, $escape = NULL)
	{
		return $this->_between($field, $from, $to, $escape, 'OR', TRUE);
	}

	protected function _between($field, $from, $to = NULL, $escape = NULL,$type = 'AND', $not = '')
	{
		if ( ! is_bool($escape))
		{
			$escape = TRUE;
		}
		
		if (! is_array($from))
		{
			$from = array($from, $to);
		}
		
		$begin = '';
		for ($j = count($this->lz_where_begin) - 1; $j >= 0; $j--)
		{
			$type = $this->lz_where_begin[$j];
			$begin .= '(';
		}
		$this->lz_where_begin = array();
		
		$this->lz_where[] = array(	'field' => $field,
									'value' => $from,
									'operator' => 'BETWEEN',
									'not' => $not,
									'type' => $type,
									'begin' => $begin,
									'end' => '',
									'escape' => $escape);
	}
	
	public function group_by($by)
	{
		if (is_string($by))
		{
			$by = explode(',', $by);
		}
		foreach ($by as $val)
		{
			$val = trim($val);
			if ($val != '')
			{
				$this->lz_groupby[] = $val;
			}
		}
		return $this;
	}

	public function having($key, $value = '', $escape = TRUE)
	{
		return $this->_having($key, $value, 'AND', $escape);
	}

	public function or_having($key, $value = '', $escape = TRUE)
	{
		return $this->_having($key, $value, 'OR', $escape);
	}

	protected function _having($key, $value = '', $type = 'AND ', $escape = TRUE)
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$operator =  $this->_has_operator($k);
			
			$this->lz_having[] = array(	'field' => $k,
										'value' => $v,
										'operator' => $operator,
										'not' => FALSE,
										'type' => $type,
										'begin' => '',
										'end' => '',
										'escape' => $escape);
		}
		
		return $this;
	}

	public function order_by($orderby, $direction = '', $escape = TRUE)
	{
		if (strtolower($direction) == 'random')
		{
			$direction = ' RAND()';
		}
		else
		{
			$direction = strtoupper(trim($direction));
			$direction = (in_array($direction, array('ASC', 'DESC'), TRUE)) ? ' '.$direction : 'ASC';
		}
		
		if (strpos($orderby, ',') !== FALSE)
		{
			foreach (explode(',', $orderby) as $part)
			{
				$part = trim($part);
				$part = explode(' ', $part);
				if (isset($part[1]))
				{
					$this->lz_orderby[] = array('field' => $part[0],
												'direction' => $part[1],
												'function' => FALSE,
												'values' => array(),
												'escape' => $escape);
				}
				else
				{
					$this->lz_orderby[] = array('field' => $part[0],
												'direction' => $direction,
												'function' => FALSE,
												'values' => array(),
												'escape' => $escape);
				}
			}
		}
		else {
			$orderby = explode(' ', $orderby);
			if (isset($orderby[1]))
			{
				$orderby[1] = strtoupper(trim($orderby[1]));
				$direction = (in_array($orderby[1], array('ASC', 'DESC'), TRUE)) ? ' '.$orderby[1] : $direction;
			}
			$orderby = $orderby[0];
			$this->lz_orderby[] = array('field' => $orderby,
										'direction' => $direction,
										'function' => FALSE,
										'values' => array(),
										'escape' => $escape);
		}
		
		return $this;
	}
	
	public function order_by_field($orderby, $values, $direction = '', $escape = TRUE)
	{
		$direction = strtoupper(trim($direction));
		$direction = (in_array($direction, array('ASC', 'DESC'), TRUE)) ? ' '.$direction : ' ASC';
		
		$orderby = explode(' ', $orderby);
		if (isset($orderby[1]))
		{
			$orderby[1] = strtoupper(trim($orderby[1]));
			$direction = (in_array($orderby[1], array('ASC', 'DESC'), TRUE)) ? ' '.$orderby[1] : $direction;
		}
		$orderby = $orderby[0];
		
		$this->lz_orderby[] = array('field' => $orderby,
									'direction' => $direction,
									'function' => 'FIELD',
									'values' => array_unique($values),
									'escape' => $escape);
		return $this;
	}
	
	public function order_by_length($field, $direction = '', $escape = TRUE)
	{
		$direction = strtoupper(trim($direction));
		$direction = (in_array($direction, array('ASC', 'DESC'), TRUE)) ? ' '.$direction : ' ASC';
		if (strpos($field, ',') !== FALSE)
		{
			foreach (explode(',', $field) as $part)
			{
				$this->order_by_length(trim($part), $direction, $escape);
			}
			return $this;
		}
		$field = explode(' ', $field);
		
		if (isset($field[1]))
		{
			$field[1] = strtoupper(trim($field[1]));
			$direction = (in_array($field[1], array('ASC', 'DESC'), TRUE)) ? ' '.$field[1] : $direction;
		}
		$field = $field[0];
		
		$this->lz_orderby[] = array('field' => $field,
									'direction' => $direction,
									'function' => 'LENGTH',
									'values' => array(),
									'escape' => $escape);
		return $this;
	}

	public function limit($value, $offset = '')
	{
		$this->lz_limit = (int)$value;
		
		if ($offset != '')
		{
			$this->lz_offset = $offset;
		}
		return $this;
	}
	
	public function get($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table, TRUE, TRUE);
		}
		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}
		if ( ! isset($this->lz_from[0]))
		{
			$this->display_error('db_must_set_table');
		}
		
		$sql = '';
		$this->lz_countall = 'SELECT COUNT(*) as numrows '."\r\n";
		if (count($this->lz_select) == 0)
		{
			$sql .= 'SELECT * ';
		}
		else {
			$sql .= $this->_get_select();
		}
		
		$sql .= $this->_get_from();
		$sql .= $this->_get_join();
		$sql .= $this->_get_where();
		$sql .= $this->_get_groupby();
		$sql .= $this->_get_having();
		$sql .= $this->_get_orderby();
		$sql .= $this->_get_limit();
		
		$result = $this->_query($sql);
		$result = $this->_get_array($result);
		$this->lz_count = count($result);
		if ($this->lz_limit == 1 && isset($result[0]))
		{
			$result = $result[0];
		}
		$this->_reset_select();
		return $result;
	}
	
	public function get_lang($table = '', $this_lang = LANG, $limit = NULL)
	{
		if ($table != '')
		{
			$this->from($table, TRUE, TRUE);
		}
		if ( ! is_null($limit))
		{
			$this->limit($limit);
		}
		if ( ! isset($this->lz_from[0]))
		{
			$this->display_error('db_must_set_table');
		}
		$table = array();
		$table_texts = array();
		
		$sql = '';
		$this->lz_countall = 'SELECT COUNT(*) as numrows '."\r\n";
		
		foreach ($this->lz_from as $from)
		{
			if ($this->table_exists($from['table'].'_texts'))
			{
				$table = $this->fields_list($from['table']);
				$table_texts = $this->fields_list($from['table'].'_texts');
				$id = explode('_', $from['table']);
				$select = $this->_set_lang_select($from['table'], $table, $table_texts);
				$join = '(';
				$join .= 'SELECT '.$select;
				$join .= ' FROM '.$this->dbprefix.$from['table'].'_texts';
				$join .= ' WHERE `langs_code` = \''.$this_lang.'\'';
				$join .= ') AS `'.$this->dbprefix.$from['table'].'_texts`';
				$this->join($join, next($id).'_id', 'left');
			}
		}
		
		if (count($this->lz_select) == 0)
		{
			$sql .= 'SELECT * ';
		}
		else {
			$sql .= $this->_get_select();
		}
		
		$sql .= $this->_get_from();
		$sql .= $this->_get_join();
		$sql .= $this->_get_where();
		$sql .= $this->_get_groupby();
		$sql .= $this->_get_having();
		$sql .= $this->_get_orderby();
		$sql .= $this->_get_limit();
		
		$result = $this->_query($sql);
		$result = $this->_get_array($result);
		$this->lz_count = count($result);
		if ($this->lz_limit == 1 && isset($result[0]))
		{
			$result = $result[0];
		}
		$this->_reset_select();
		return $result;
	}
	
	public function num_rows($table = '')
	{
		if ($table != '')
		{
			$this->from($table);
			$sql = $this->lz_countall = 'SELECT COUNT(*) AS `numrows` ';
			$this->lz_countall;
			$sql .= $this->_get_from();
			$sql .= $this->_get_join();
			$sql .= $this->_get_where();
			$sql .= $this->_get_groupby();
			$sql .= $this->_get_having();
			$result = $this->_query($sql);
			$result = $this->_get_array($result);
			$count = ((int) $result['0']['numrows'] > $this->lz_limit) ? $this->lz_limit : (int) $result['0']['numrows'];
			$this->_reset_select();
			return $count;
		}
		else {
			return $this->lz_count;
		}
	}
	
	public function count_all($table = '')
	{
		if ($table != '')
		{
			$this->from($table);
			$sql = $this->lz_countall = 'SELECT COUNT(*) AS `numrows` ';
			$sql .= $this->_get_from();
			$sql .= $this->_get_join();
			$sql .= $this->_get_where();
			$sql .= $this->_get_groupby();
			$sql .= $this->_get_having();
			$result = $this->_query($sql);
			$result = $this->_get_array($result);
			$count = ((int) $result['0']['numrows'] > $this->lz_limit) ? $this->lz_limit : (int) $result['0']['numrows'];
			$this->_reset_select();
			return $count;
		}
		else {
			if ($this->lz_countall != '')
			{
				$result = $this->_query($this->lz_countall, TRUE, FALSE);
				$result = $this->_get_array($result);
				return (int) $result['0']['numrows'];
			}
			else {
				$this->display_error('db_invalid_count');
			}
		}
	}
	
	public function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}
		if($table != '')
		{
			$this->from($table);
		}
		
		if (count($this->lz_set) == 0)
		{
			$this->display_error('db_must_use_set');
		}
		
		if ( ! isset($this->lz_from[0]))
		{
			$this->display_error('db_must_set_table');
		}
		
		$table = $this->lz_from[0];
		$table['table'] = $this->_protect_identifiers($table['table'], $table['escape'], TRUE);
		
		$sql = "INSERT INTO ";
		$sql .= $table['table'];
		$sql .= $this->_get_set();
		
		$this->_reset_write();
		return $this->_query($sql);
	}
	
	public function id()
	{
		return @mysql_insert_id($this->conn_id);
	}

	public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}
		if (count($this->lz_set) == 0)
		{
			$this->display_error('db_must_use_set');
		}
		if($table != '')
		{
			$this->from($table);
		}
		if ($where != NULL)
		{
			$this->where($where);
		}
		if ($limit != NULL)
		{
			$this->limit($limit);
		}
		
		if ( ! isset($this->lz_from[0]))
		{
			$this->display_error('db_must_set_table');
		}
		$table = $this->lz_from[0];
		$table['table'] = $this->_protect_identifiers($table['table'], $table['escape'], TRUE);
		
		
		$sql = 'UPDATE ';
		$sql .= $table['table'];
		$sql .= $this->_get_set();
		$sql .= $this->_get_where(FALSE);
		$sql .= $this->_get_orderby();
		$sql .= $this->_get_limit(FALSE);
		
		$this->_reset_write();
		return $this->_query($sql);
	}
	
	public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE)
	{
		if ($where != '')
		{
			$this->where($where);
		}

		if ($limit != NULL)
		{
			$this->limit($limit);
		}
		
		if (is_array($table))
		{
			foreach ($table as $single_table)
			{
				$this->from($single_table);
			}
		}
		elseif (strpos(',', $table) !== FALSE)
		{
			foreach (explode(',', $table) as $value) 
			{
				$this->from(trim($value));
			}
		}
		else
		{
			$table = $this->from($table);
		}
		if (count($this->lz_from) == 0)
		{
			$this->display_error('db_must_set_table');
		}
		if (count($this->lz_where) == 0)
		{
			$this->display_error('db_del_must_use_where');
		}
		
		$check = TRUE;
		$sql_begin = 'DELETE FROM ';
		$sql_end = ' ';
		$sql_end .= $this->_get_where(FALSE);
		$sql_end .= $this->_get_orderby();
		$sql_end .= $this->_get_limit(FALSE);
		
		foreach ($this->lz_from as $table)
		{
			$check *= $this->_query($sql_begin.$this->_protect_identifiers($table['table'], $table['escape'], TRUE).$sql_end);
		}
		$this->_reset_write();

		return $check;
	}
	
	public function empty_table($table = '')
	{
		return $this->truncate($table);
	}
	
	public function truncate($table = '')
	{
		if ($table == '')
		{
			$this->display_error('db_must_set_table');
			return FALSE;
		}
		else
		{
			$table = $this->_protect_identifiers($table, TRUE, TRUE);
		}
		
		return $this->_query("TRUNCATE ".$table);
	}
	
	protected function _get_select()
	{
		$temp = array();
		foreach ($this->lz_select as $select)
		{
			$sql_temp = '';
			
			$select['field'] = $this->_protect_identifiers($select['field'], $select['escape']);
			if ($select['alias'])
			$select['alias'] = $this->_protect_identifiers($select['alias'], $select['escape']);
			
			
			switch ($select['function']) {
				case 'MAX':
				case 'MIN':
				case 'AVG':
				case 'SUM':
					$sql_temp .= $select['function'].'('.$select['field'].')';
					break;
				case 'IF':
					$true = $select['true'];
					$false = $select['false'];
					if (! is_numeric($true))
					{
						$true = $this->_protect_identifiers($true, $select['escape']);
					}
					if (! is_numeric($false))
					{
						$false = $this->_protect_identifiers($false, $select['escape']);
					}
					$sql_temp .= $select['function'].'('.$select['field'].', '.$true.', '.$false.')'; 
					break;
				default:
					$sql_temp .= $select['field'];
					break;
			}
			
			if ($select['alias'])
			{
				$sql_temp .= ' AS '.$select['alias'];
			}
			$temp[] = $sql_temp;
		}
		if (count($temp))
		{
			return 'SELECT '.implode(', ', $temp)."\r\n";
		}
		else
		{
			return 'SELECT *';
		}
	}

	protected function _set_lang_select($table_name, $table, $table_texts)
	{
		if (count($this->lz_select) > 0)
		{
			foreach ($this->lz_select as $i => $select)
			{
				$field = explode('.', $select['field']);
				$field = end($field);
				$id = explode('_', $field);
				if (in_array($field, $table_texts))
				{
					if (end($id) != 'id')
					{
						$alias = (($select['alias'] != '') ? $select['alias'] : $field);
						$field = $this->_protect_identifiers($table_name.'.'.$select['field'], FALSE);
						$field_text = $this->_protect_identifiers($table_name.'_texts.'.$select['field'], FALSE);
						$this->lz_select[$i] = array(
												'field' => $field_text." <> ''", 
												'true' => $field_text,
												'false' => $field,
												'function' => 'IF',
												'alias' => $alias,
												'escape' => TRUE);
					}
				}
			}
		}
		else
		{
			foreach ($table as $field)
			{
				$id = explode('_', $field);
				if (in_array($field, $table_texts))
				{
					if (end($id) != 'id')
					{
						$alias = (($select['alias'] != '') ? $select['alias'] : $field);
						$field = $this->_protect_identifiers($table_name.'.'.$field, FALSE);
						$field_text = $this->_protect_identifiers($table_name.'_texts.'.$field, FALSE);
						$this->lz_select[$i] = array(
												'field' => $field_text." <> NULL", 
												'true' => $field_text,
												'false' => $field,
												'function' => 'IF',
												'alias' => $alias,
												'escape' => TRUE);
					}
				}
			}
		}
		
		$return = array();
		foreach ($table as $field)
		{
			if (in_array($field, $table_texts))
			{
				$return[] = $this->_protect_identifiers($field, FALSE);
			}
		}
		return implode(', ', $return);
	}

	protected function _get_set()
	{
		$temp = array();
		foreach ($this->lz_set as $set)
		{
			$sql_temp = '';
			$set['field'] = $this->_protect_identifiers($set['field'], $set['escape']);
			$set['from'] = $this->escape($set['from'], $set['escape']);
			$set['to'] = $this->escape($set['to'], $set['escape']);
			
			switch ($set['function']) {
				case 'REPLACE':
					$temp[] = $set['field'].' = '.'REPLACE ('.$set['field'].', '.$set['from'].', '.$set['to'].')';
					break;
				case 'INCREMENT':
					$temp[] = $set['field'].' = '.'(SELECT @i := @i + 1 from (select @i := '.((int)str_replace("'", '', $set['to']) - 1).') as s)';
					break;
				default:
					$temp[] = $set['field'].' = '.$set['to'];
					break;
			}
		}
		return "\r\n SET ".implode(",\r\n\t ", $temp);
	}
	
	protected function _get_from()
	{
		$temp = array();
		foreach ($this->lz_from as $from)
		{
			$sql_temp = '';
			$from['table'] = $this->_protect_identifiers($from['table'], $from['escape'], TRUE);
			$sql_temp .= $from['table'];
			if ($from['alias'] != '')
			{
				$sql_temp .= ' AS '.$from['alias'];
			}
			$temp[] = $sql_temp;
		}
		if (count($temp))
		{
			$sql_temp = "\r\nFROM ".implode(', ', $temp)."\r\n";
			$this->lz_countall .= $sql_temp;
			return  $sql_temp;
		}
		else
		{
			return '';
		}
		
		
	}
	
	protected function _get_join()
	{
		$temp = array();
		foreach ($this->lz_join as $join)
		{
			$sql_temp = $join['type'].' JOIN ';
			
			$join['table'] = $this->_protect_identifiers($join['table'], $join['escape'], TRUE);
			$join['using'] = $this->_protect_identifiers($join['using'], $join['escape']);
			$join['where1'] = $this->_protect_identifiers($join['where1'], $join['escape']);
			$join['where2'] = $this->_protect_identifiers($join['where2'], $join['escape']);
			
			$sql_temp .= $join['table'];
			if ($join['alias'])
			{
				$sql_temp .= ' AS '.$join['alias'];
			}
			if ($join['using'])
			{
				$sql_temp .= "\r\n\t\t".' USING ('.$join['using'].') ';
			}
			else {
				$sql_temp .= "\r\n\t\t".' ON '.$join['where1'].' '.$join['operator'].' '.$join['where2'];
			}
			
			$temp[] = $sql_temp;
		}
		
		if (count($temp))
		{
			$sql_temp = implode("\r\n", $temp);
			$this->lz_countall .= $sql_temp;
			return  $sql_temp;
		}
		else
		{
			return '';
		}
	}
	
	protected function _get_where($select = TRUE)
	{
		$temp = array();
		foreach ($this->lz_where as $w => $where)
		{
			$sql_temp = '';
		
			$where['field'] = $this->_protect_identifiers($where['field'], $where['escape']);
			$where['value'] = $this->escape($where['value'], $where['escape']);
			
			
			if ($w != 0)
			{
				$sql_temp .= ' '.$where['type'];
			}
			$sql_temp .= ' '.$where['begin'];
			$sql_temp .= ' '.$where['field'];
			if ($where['not']) 
				$sql_temp .= ' NOT';
			
			switch ($where['operator']) {
				case 'LIKE':
					$sql_temp .= ' LIKE ';
					switch ($where['side']) {
						case 'before':
							$sql_temp .= "'%".ltrim($where['value'], "'");
							break;
						case 'after':
							$sql_temp .= rtrim($where['value'], "'")."%'";
							break;
						default:
							$sql_temp .= "'%".trim($where['value'], "'")."%'";
							break;
					}
					break;
				case 'IN':
					$sql_temp .= ' IN ';
					$sql_temp .= "(".implode(", ", $where['value']).")";
					break;
				case 'BETWEEN':
					$sql_temp .= ' BETWEEN ';
					$sql_temp .= $where['value'][0]." AND ".$where['value'][1];
					break;
				default:
					$sql_temp .= ' '.$where['operator'].' ';
					$sql_temp .= $where['value'];
					break;
			}
			$sql_temp .= $where['end'];
			$temp[] = $sql_temp;
		}
		
		if (count($temp))
		{
			$sql_temp = "\r\nWHERE ".implode("\r\n\t\t", $temp);
			if($select)
			{
				$this->lz_countall .= $sql_temp;
			}
			return  $sql_temp;
		}
		else
		{
			return '';
		}
	}
	
	protected function _get_groupby()
	{
		$temp = array();
		foreach ($this->lz_groupby as $groupby)
		{
			$temp[] = $this->_protect_identifiers($groupby, TRUE);
		}
		if (count($temp))
		{
			$sql_temp = "\r\nGROUP BY ".implode(", ", $temp);
			$this->lz_countall .= $sql_temp;
			return  $sql_temp;
		}
		else
		{
			return '';
		}
	}
	
	protected function _get_having()
	{
		$temp = array();
		foreach ($this->lz_having as $w => $having)
		{
			$sql_temp = '';
		
			$having['field'] = $this->_protect_identifiers($having['field'], $having['escape']);
			$having['value'] = $this->escape($having['value'], $having['escape']);
			
			
			if ($w != 0)
			{
				$sql_temp .= ' '.$having['type'];
			}
			$sql_temp .= ' '.$having['field'];
			$sql_temp .= ' '.$having['operator'].' ';
			$sql_temp .= "'".$having['value']."'";
			
			$sql_temp .= $where['end'];
			$temp[] = $sql_temp;
		}
		if (count($temp))
		{
			$sql_temp = "\r\nHAVING ".implode("\r\n\t\t", $temp);
			$this->lz_countall .= $sql_temp;
			return  $sql_temp;
		}
		else
		{
			return '';
		}
	}
	
	protected function _get_orderby()
	{
		$temp = array();
		foreach ($this->lz_orderby as $orderby)
		{
			$sql_temp = '';
			
			$orderby['field'] = $this->_protect_identifiers($orderby['field'], $orderby['escape']);
			if ($orderby['function'] !== FALSE)
			{
				$orderby['values'] = $this->escape($orderby['values'], TRUE);
			}
			else {
				$orderby['values'] = $this->escape($orderby['values'], $orderby['escape']);
			}
			
			switch ($orderby['function']) {
				case 'FIELD':
					$sql_temp .=' FIELD';
					$sql_temp .='('.$orderby['field'].', '.implode(", ", $orderby['values']).')';
					break;
				case 'LENGTH':
					$sql_temp .=' LENGTH';
					$sql_temp .='('.$orderby['field'].') '.$orderby['direction'];
					break;
				
				default:
					$sql_temp .= ' '.$orderby['field'].' '.$orderby['direction'];
					break;
			}
			
			$temp[] = $sql_temp;
		}
		
		if (count($temp))
		{
			$sql_temp = "\r\nORDER BY ".implode(",\r\n\t\t", $temp);
			return  $sql_temp;
		}
		else
		{
			return '';
		}
	}
	
	protected function _get_limit($full = TRUE)
	{
		$sql_temp = '';
		if (is_numeric($this->lz_limit))
		{
			$sql_temp .= (($full)
					? "\r\nLIMIT ".(($this->lz_offset)? $this->lz_offset : '0').', '.$this->lz_limit
					:"\r\nLIMIT ".$this->lz_limit);
		}
		return $sql_temp;
	}

	public function tables_list($table = '')
	{
		if (isset($this->data_cache['tables_names']))
		{
			return $this->data_cache['tables_names'];
		}
		
		$sql = "SELECT TABLE_NAME as 'Table', COLUMN_NAME as 'Field', COLUMN_TYPE as 'Type', IS_NULLABLE as 'Null', COLUMN_DEFAULT as 'Null', COLUMN_KEY as 'Key', EXTRA as 'Extra' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME LIKE '".$this->dbprefix."%' AND TABLE_SCHEMA = '".$this->database."' ORDER BY TABLE_NAME ASC, ORDINAL_POSITION ASC";
		
		if ($this->result_id = @mysql_query($sql))
		{
			$result = $this->_get_array();
		}
		else
		{
			$result = array();
			$sql = "SHOW TABLES FROM `".$this->database.'`';
			if ($this->result_id = @mysql_query($sql))
			{
				$tables_result = $this->_get_array();
				
				foreach ($tables_result as $table)
				{
					$sql = "SHOW COLUMNS FROM ".$this->_escape_identifiers($table['Tables_in_'.$this->database]);
					if ($this->result_id = @mysql_query($sql))
					{
						$columns_result = $this->_get_array();
						foreach ($columns_result as $colum)
						{
							$result[] = array_merge(array('Table' => $table['Tables_in_'.$this->database]), $colum);
						}
					}
				}
			}
		}
		
		if (count($result) > 0)
		{
			foreach ($result as $row)
			{
				$table_name = substr($row['Table'], strlen($this->dbprefix));
				unset($row['Table']);
				$this->data_cache['tables_names'][] = $table_name;
				$this->data_cache['fields_names'][$table_name][] = $row['Field'];
				$this->data_cache['fields_data'][$table_name][] = $row;
			}
			 $this->data_cache['tables_names'] = array_unique($this->data_cache['tables_names']);
			 sort($this->data_cache['tables_names']);
		}
		
		return $this->data_cache['tables_names'];
	}
	
	public function table_exists($table_name)
	{
		if (! isset($this->data_cache['tables_names']))
		{
			$this->tables_list();
		}
		
		return ( ! in_array($table_name, $this->tables_list())) ? FALSE : TRUE;
	}
	
	public function fields_list($table = '')
	{
		if (! isset($this->data_cache['fields_names']))
		{
			$this->tables_list();
		}
		
		return (! isset($this->data_cache['fields_names'][$table])) ? FALSE : $this->data_cache['fields_names'][$table];
	}
	
	public function field_exists($table_name, $field_name)
	{
		if (! isset($this->data_cache['fields_names']))
		{
			$this->tables_list();
		}
		
		return ( isset($this->data_cache['fields_names'][$table_name]) && in_array($field_name, $this->data_cache['fields_names'][$table_name])) ? TRUE : FALSE;
	}
	
	public function fields_data($table = '')
	{
		if (! isset($this->data_cache['fields_data']))
		{
			$this->tables_list();
		}
		
		return (isset($this->data_cache['fields_data'][$table])) ? $this->data_cache['fields_data'][$table]: FALSE;
	}
}
