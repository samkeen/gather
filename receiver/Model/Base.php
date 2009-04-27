<?php
/**
 * This is the base DataObject Class.
 *
 * @package default
 * @author Sam
 **/
abstract class Model_Base {
	
	private $db_handle;
	protected $model_name;
	protected $model_id_name;
	protected $id = null;
	private $base_attribute_definitions = array(
		'created' => null,
		'modified' => null,
		'active' => null
	);
	protected $relations = array();
    /*
     * ex: array( 'group' => array('id' => '1'))
     */
    protected $submitted_habtm_data;
	/**
	 * defined in the implementing class thusly
	 * 	protected $attribute_definitions = array(
	 *	  'username' => null,
	 *	  'password' => null,
	 *	  'xmpp_jid' => null,
	 *	  'sms_number' => null,
	 *	  'active' => null
	 *  );
	 */
	protected $attribute_definitions = array();
	private $field_values = array();
	private $field_value_comparitors = array();
	
	/**
	 * Allow an injected db_handle, else create on the fly
	 */
	public function __construct(Model_DBHandle $db_handle) {
		$this->db_handle = $db_handle;
	}


	/**
	 * 
	 * @param array $submitted_data {optional, we could have set the 
	 * various field values on the model prior to calling this method
	 */
	public function save(array $submitted_data = null) {
		$rows_affected = null;
		$this->set_field_values($submitted_data);
		if ($this->have_data_to_save()) {
			$save_statement = $this->is_new_model() 
				? $this->build_insert_statement()
				: $this->build_update_statement();
			ENV::$log->debug(__METHOD__.' built save QUERY: '.$save_statement);
			$statement = null;
			try {
				if( ! $statement = $this->db_handle->prepare($save_statement)) {
					ENV::$log->error(__METHOD__.' - $statement::prepare failed for query: '
						.$save_statement."\n".print_r($this->db_handle->errorInfo(),1));
				}
				foreach ($this->field_values as $field_name => $field_value) {
					 $statement->bindValue(':'.$field_name, $field_value);
				}
				if ( ! $this->is_new_model()) {
					$statement->bindValue(':'.$this->model_id_name, $this->id);
				}
				$rows_affected = $statement->execute();
                if ($rows_affected===false) {
					ENV::$log->error(__METHOD__.' - $statement->execute() failed for query: '
						.$save_statement."\n".print_r($statement->errorInfo(),1));
				} else if($this->is_new_model()) {
                    $this->id = $this->db_handle->last_insert_id();
                }
			} catch (Exception $e) {
				ENV::$log->error(__METHOD__.'-'.$e->getMessage());
			}
            $this->save_habtm_relations();
		} else {
			ENV::$log->error(__METHOD__. ' Valid model id not supplied as param and not currently set on $this');
		}
		return $rows_affected;
	}

	public function delete() {
		if ($this->id !== null) {
			$result = null;
            $delete_sql = $this->build_delete_statement($this->model_name, $this->model_id_name);
			try {
				$statement = $this->db_handle->prepare($delete_sql);
				$statement->bindValue(':'.$this->model_id_name, $this->id);
				$result = $statement->execute();
                $this->remove_habtm_relations();
			} catch (Exception $e) {
				ENV::$log->error(__METHOD__.'-'.$e->getMessage());
			}
		}
		return $result;
	}
	
	public function findOne(array $field_values = null) {
		$one = $this->_find($field_values);
		return isset($one[0]) ? $one[0] : null;
	}
    /**
	 *
	 * @param array $field_values {optional, we could have set the
	 * various field values on the model prior to calling this method
     * @param string $table_to_query if given we query that table not
     * the one belonging to this model (for internal use only)
	 */
    public function find(array $field_values = null, $table_to_query = null, $attach_habtm = true) {
        $result = null;
		$this->set_field_values($field_values);
        $table = $table_to_query!==null ? "`$table_to_query`" : "`{$this->model_name}`";
        // SELECT b, d FROM foo WHERE `b` = :b AND `d` = :d
		$find_statement = 
			"SELECT {$table}.* FROM ".$table.$this->build_where_clause($table_to_query);
        $bind_params = $this->field_values;
        if ($this->id !== null) {
            $bind_params += array($this->model_id_name => $this->id);
        }
        $result = $this->execute_find_statment($find_statement, $bind_params);
        if($attach_habtm) {
            $result = $this->attach_habtm_data($result);
        }
		return $result;
	}

    private function execute_find_statment($statement_text, $bind_params, $result_structure=null) {
        $result = null;
        ENV::$log->debug(__METHOD__.' executing QUERY: '.$statement_text);
        try {
			$statement = $this->db_handle->prepare($statement_text);
			foreach ($bind_params as $field_name => $field_value) {
				$statement->bindValue(':'.$field_name, $field_value);
			}
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			ENV::$log->error(__METHOD__.'-'.$e->getMessage());
		}
        return $result_structure===null ? $result : $this->apply_return_structure($result_structure, $result);
    }

	

	/**
	 * ex: 
	 * return structure is: array('user_id'=>array('username','age'));
	 * row of results: array('user_id'=> 2, 'age'=>30, 'username' => 'sam');
	 * transformed row = array(2=>array(age=>30, username=>sam))
     *
     * ex: $result_structure = array('user_id');
     * row of results: array('user_id'=> 2, 'age'=>30, 'username' => 'sam');
	 * transformed row = array(0 => '2')
	 * 
	 * @param $return_structure
	 * array({key} => {field1})
	 * OR
	 * array({key} => array({field1}, {field2},...))
	 */
	private function apply_return_structure(array $return_structure, $results) {
		$structure_formatted_results = null;
		if (isset($results[0])) {
			foreach ($results as $result) {
				foreach ($return_structure as $structure_key => $field) {
					if( ! is_array($field)) {
                        if($structure_key===0) {
                            $structure_formatted_results[] = $result[$field];
                        } else {
                            $structure_formatted_results[$result[$structure_key]] = $result[$field];
                        }
						
					} else {
						foreach ($field as $value) {
							$structure_formatted_results[$result[$structure_key]][$value] = $result[$value];
						}
					}
				}
			}
		}
		return $structure_formatted_results;
	}
    
	/**
	 * if $fields_is_return_struct is true, we blend the keys into the values and create the
	 * select from that
	 */
	private function build_select_clause(array $fields, $fields_is_return_struct=true) {
		$select_fields[] = key($fields);
		foreach ($fields as $field) {
			if (is_array($field)) {
				$select_fields = array_merge($select_fields, $field);
			} else {
				$select_fields[] = $field;
			}
		}
		return isset($select_fields[0]) ? 'SELECT '.implode(', ',$select_fields):null;
	}
	private function build_where_clause($table_to_query=null) {
        $table = $table_to_query===null?$this->model_name:$table_to_query;
		$where_clause = '';
		// if $this->id is set, just do
		if ($this->id !==null) {
			$where_clause = " WHERE `{$table}`.`{$this->model_id_name}` = :{$this->model_id_name} ";
		} else if(count($this->field_values)) {
			$where_clause = ' WHERE ';
			$and = '';
			foreach (array_keys($this->field_values) as $field_name) {		
				$where_clause .= $and." `{$table}`.`{$field_name}` {$this->field_value_comparitors[$field_name]} :{$field_name}";
				$and = ' AND ';
			}
		}
		return $where_clause;	
	}
	private function build_insert_statement() {
		$insert_statement = 
			'INSERT INTO '.$this->model_name.'( `'.implode('`,`',array_keys($this->field_values)).'`, `modified`, `created` )'
			.' VALUES ( :'.implode(',:',array_keys($this->field_values)).', now(), now() )';
		return $insert_statement;
	}
    private function build_delete_statement($table, $fieldnames_for_condition) {
        $fieldnames_for_condition = is_array($fieldnames_for_condition)?$fieldnames_for_condition:array($fieldnames_for_condition);
        $statement = "DELETE FROM `$table` WHERE ";
        $and = '';
        foreach ($fieldnames_for_condition as $fieldname) {
            $statement .= " $and `{$fieldname}`= :{$fieldname} ";
            $and = 'AND';
        }
        return $statement;
    }
	private function build_update_statement() {
		$update_statement = 'UPDATE `'.$this->model_name.'` SET modified = now(), ';
		$comma = '';
		foreach (array_keys($this->attribute_definitions) as $field_name) {		
			if (isset($this->field_values[$field_name])) {
				$update_statement .= $comma.'`'.$field_name.'` = :'.$field_name;
				$comma = ', ';
			}
		}
		return $update_statement . ' WHERE `'.$this->model_name.'_id` =  :'.$this->model_id_name;
	}

	protected function query($sql) {
		return $this->db_handle->query($sql);
	}
	protected function execute($sql) {
		return $this->db_handle->execute($sql);
	}

}
