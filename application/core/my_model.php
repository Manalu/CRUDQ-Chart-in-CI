<?php

class My_Model extends CI_Model{

	const DB_TABLE 		= 'abstract';

	const DB_TABLE_PK 	= 'abstract';

	/**
	 * Cria registro
	 */
	protected function insert(){
		$this->db->insert($this::DB_TABLE, $this);
		$this->{$this::DB_TABLE_PK} = $this->db->insert_id();
		return $this->{$this::DB_TABLE_PK};
	}

	/**
	 * Atualiza registro
	 */
	private function update(){
		$this->db->where($this::DB_TABLE_PK, $this->{$this::DB_TABLE_PK});
		return $this->db->update($this::DB_TABLE, $this);
	}

	/**
	 * Popula a partir de um array ou de uma classe standart
	 * @param mixed $row
	 */
	public function populate($row){
		foreach ($row as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Carrega registro do banco de dados
	 * @param integer $id
	 */
	public function load($id){
		$query = $this->db->get_where($this::DB_TABLE, array(
			$this::DB_TABLE_PK => $id,
		));
		$row = $query->row();
		if( !is_null($row) ){
			$this->populate($row);
		}else{
			return null;
		}
	}

	/**
	 * Remove registro do banco de dados
	 */
	public function delete(){
		$this->db->delete($this::DB_TABLE, array(
			$this::DB_TABLE_PK => $this->{$this::DB_TABLE_PK},
		));
		unset($this->{$this::DB_TABLE_PK});
	}

	/**
	 * Salva registro
	 */
	public function save(){
		if( isset($this->{$this::DB_TABLE_PK}) ){
			return $this->update();
		}else{
			return $this->insert();
		}
	}

	/**
	 * Resgata Array de Models como where, limit, offset
	 *
	 * @param string $where
	 * @param int $limit
	 * @param int $offset
	 * @param array $order ex.:('name','ASC')
	 * @return array Models populado com banco de dados, chaves em id
	 */
	public function get($where = '', $limit = 0, $offset = 0){
		if(!empty($where)){
			$this->db->where( $where );
		}

		if($limit){
			$query = $this->db->get($this::DB_TABLE, $limit, $offset);
		}else{
			$query = $this->db->get($this::DB_TABLE);
		}

		$result_array = array();
		$classe = get_class($this);
		foreach ($query->result() as $row) {
			$model = new $classe;
			$model->populate($row);
			$result_array[$row->{$this::DB_TABLE_PK}] = $model;
		}
		return $result_array;
	}
}