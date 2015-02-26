<?php
class DatabaseModelManager extends Module {

	public function onLoad(){
		$this->events->addListener(array($this, 'eventRegisterBuild'), 'eventRegisterBuildEvent', EventPriority::NORMAL);
	}

	public function eventRegisterBuild($event) {
		$event->addEvent('databasemodel', 'loadModelsEvent');
		return $event;
	}
}
class DatabaseModel extends Bus{

    public $fields  = array();
	public $primary = 'id';
    public $table   = '';

    public function __construct(&$core){
        parent::__construct($core);
        $this->core->loadMod('database');
    }

    public function select(){

        $select = '';
        $where  = '';
        $order  = '';
        $limit  = '';
        $other  = '';

        $args   = func_get_args();
        $binds  = array();

        // Only one argument given
        if(func_num_args() >= 1){
            // Null: select all fields
            if($args[0] === null){

                $fields = $this->fields;
            }else{

                $fields = $args[0];
            }

            // Convert array to string
            if(is_array($fields)){

                // Slightly faster as implode()
                foreach($fields as $index => $field){

                    $select .= ' `'.$field.($index == (count($fields)-1) ? '`' : '`,');
                }
            }elseif(is_numeric($fields)){

                // Build where based on primary key
                $select= '*';
                $where = 'WHERE `'.$this->primary.'` = '.$fields;
            }else{

                // Directly use it as $select
                $select = $fields;
            }

            if(func_num_args() >= 2){

                // Convert array to string
                if($args[1] !== null)
                if(is_array($fields)){

                    $where = 'WHERE';

                    // Implode the fields into a string
                    foreach($args[1] as $field => $value){

                        $token1 = 'AND';
                        $token2 = '=';

                        // Operator feautures
                        if(strpos($field, '!') !== false){

                            $token2 = '<>';
                            $field = str_replace('!', '', $field);
                        }

                        if(strpos($field, '/') !== false){

                            $token1 = 'OR';
                            $field = str_replace('/', '', $field);
                        }

                        if (strpos($field, '>') !== false) {

                            $token2 = '>';
                            $field = str_replace('>', '', $field);                        	
                        }

                        if (strpos($field, '<') !== false) {

                            $token2 = '<';
                            $field = str_replace('<', '', $field);
                        }

                        if (strpos($field, '=>') !== false) {

                            $token2 = '=>';
                            $field = str_replace('=>', '', $field);                        	
                        }

                        if (strpos($field, '<=') !== false) {

                            $token2 = '<=';
                            $field = str_replace('<=', '', $field);
                        }

                        $where .= ($where === 'WHERE' ? '' : $token1).' `'.$field.'` '.$token2.' :'.$field.' ';
                        $binds[$field] = $value;
                    }
                }else{
                    // Directly use it as $select
                    $where = $args[1];
                }

                if(func_num_args() >= 3){

                    // Order
                    if($args[2] !== null)
                    if(is_array($args[2])){

                        $order = 'ORDER BY';
                        foreach($args[2] as $index => $field){

                            $mode = 'ASC';
                            if(substr($field, 0, 1) === '-'){

                                $field = substr($field, 1);
                                $mode = 'DESC';
                            }

                            $order .= ' `'.$field.'` '.$mode.($index == (count($args[2])-1) ? '' : ',');
                        }
                    }else{

                        $order = 'ORDER BY '.$args[2];
                    }

                    if(func_num_args() >= 4){

                        // Limit
                        if($args[3] !== null)
                        if(is_array($args[3])){

                            $limit = 'LIMIT '.$args[3][0].','.$args[3][1];
                        }elseif($args[3]){

                            $limit = 'LIMIT '.$args[3];
                        }

                        if(func_num_args() >= 5){

                            // Other
                            $other = $args[4];
                        }
                    }
                }
            }
        }else{
		
			$select = '*';
		}

        $query = 'SELECT '.$select.' FROM `'.$this->table.'` '.$where.' '.$order.' '.$limit.' '.$other;

        try{
            $sth = $this->mods->database->prepare($query);
            $sth->execute($binds);
        }catch (\PDOException $e){

            throw new \Exception('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }

        // Fetch results
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

	public function insert($arr){
		$query = '';
		$fields= '';
		$values= '';
		
		$i = 0;
		foreach($arr as $index => $value){
		
			$i++;
			$fields .= '`'.$index.'`'.($i == count($arr) ? '' : ', ');
			$values .= ':'.$index.($i == count($arr) ? '' : ', ');
		}
		
		$query = 'INSERT INTO `'.$this->table.'` ('.$fields.')VALUES('.$values.')';
		
		try{

            $sth = $this->mods->database->prepare($query);
            $sth->execute($arr);
        }catch (\PDOException $e){

            throw new \Exception('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }
	}
	
	public function delete(){
		$query = '';
		$where = '';
		$args  = func_get_args();
		
		// Convert array to string
		if(func_num_args() >= 1){
		
			// Where
			if($args[0] !== null)
			if(is_array($args[0])){

				$where = 'WHERE';

				// Implode the fields into a string
				foreach($args[0] as $field => $value){

					$token1 = 'AND';
					$token2 = '=';

					if(strpos($field, '!') !== false){

						$token2 = '<>';
						$field = str_replace('!', '', $field);
					}

					if(strpos($field, '/') !== false){

						$token1 = 'OR';
						$field = str_replace('/', '', $field);
					}

					$where .= ($where === 'WHERE' ? '' : $token1).' `'.$field.'` '.$token2.' :'.$field.' ';
					$binds[$field] = $value;
				}
			}elseif(is_numeric($args[0])){
			
				// Build where based on primary key
				$where = 'WHERE `'.$this->primary.'` = '.$args[0];
			}else{

				// Directly use it as $select
				$where = $args[1];
			}
			
			if(func_num_args() >= 2){

				// Limit
				if($args[1] !== null)
				if(is_array($args[1])){

					$limit = 'LIMIT '.$args[1][0].','.$args[1][1];
				}elseif($args[1]){

					$limit = 'LIMIT '.$args[1];
				}
			}
		}
		
		$query = 'DELETE FROM `'.$this->table.'` '.$where.' '.$limit;
		
		try{

            $sth = $this->mods->database->prepare($query);
            $sth->execute($binds);
        }catch (\PDOException $e){

            throw new \Exception('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }
	}
	
	public function update(){
		$query = '';
		$fields= '';
		$where = '';
		$args  = func_get_args();
		
		// Convert array to string
		if(func_num_args() >= 1){
		
			// Update
			if($args[0] !== null)
			if(is_array($args[0])){
			
				// Slightly faster as implode()
				$i = 0;
                foreach($args[0] as $field => $value){

					$i ++;
                    $fields .= ' `'.$field.'` = :insert_'.$field.($i == (count($args[0])) ? '' : ',');
					$binds['insert_'.$field] = $value;
                }
			}else{
				// Directly use it as $select
				$fields = $args[0];
			}
				
			if(func_num_args() >= 2){
				// Where
				if($args[1] !== null)
				if(is_array($args[1])){
					$where = 'WHERE';

					// Implode the fields into a string
					foreach($args[1] as $field => $value){

						$token1 = 'AND';
						$token2 = '=';

						if(strpos($field, '!') !== false){

							$token2 = '<>';
							$field = str_replace('!', '', $field);
						}

						if(strpos($field, '/') !== false){

							$token1 = 'OR';
							$field = str_replace('/', '', $field);
						}

						$where .= ($where === 'WHERE' ? '' : $token1).' `'.$field.'` '.$token2.' :'.$field.' ';
						$binds[$field] = $value;
					}
				}elseif(is_numeric($args[1])){
				
					// Build where based on primary key
					$where = 'WHERE `'.$this->primary.'` = '.$args[1];
				}else{

					// Directly use it as $select
					$where = $args[1];
				}
				
				if(func_num_args() >= 3){

					// Limit
					if($args[2] !== null)
					if(is_array($args[2])){

						$limit = 'LIMIT '.$args[2][0].','.$args[2][1];
					}elseif($args[2]){

						$limit = 'LIMIT '.$args[2];
					}
				} else {
					$limit = "";
				}
			}
		}
		
		$query = 'UPDATE `'.$this->table.'` SET '.$fields.' '.$where.' '.$limit;
		
		try{
            $sth = $this->mods->database->prepare($query);
            $sth->execute($binds);
        }catch (\PDOException $e){
            throw new \Exception('Could not execute SQL-query due PDO-exception '.$e->getMessage());
        }
	}

	public function __call($name, $params) {
		return call_user_func_array(array($this->mods->database, $name), $params);
	}
}