<?php

class Unidades_organizacionais_tree_paths extends My_Model {
	
	const DB_TABLE = 'unidades_organizacionais_tree_paths';

	// const DB_TABLE_PK = array('ancestor','descendant');
	const DB_TABLE_PK = 'ancestor';

	/**
	 * FK para Unidade Organizacional Pai
	 *
	 * @var int
	 */
	public $ancestor;

	/**
	 * FK para Unidade Organizacional Filha
	 *
	 * @var int
	 */
	public $descendant;

	/**
	 * Cria registro associativo
	 *
	 * @param integer $ancestor
	 * @param array $descendants
	 */
	public function insertUnique( $ancestor, $descendants ){

		$this->removeNotSelected( $descendants );

		if( isset($descendants) && !empty($ancestor) ){
			foreach ($descendants as $descendant) {
				// para remocao de relacionamentos de ancestralidade com outros elementos
				$this->db->delete('unidades_organizacionais_tree_paths', array('descendant' => $descendant));

				// para insercao unica entre relacionamentos atuais
				$busca = $this->get( ' ancestor = '.$ancestor.' AND descendant = '.$descendant );
				if( count($busca) == 0 && !empty($ancestor) ){
					$this->ancestor 	= $ancestor;
					$this->descendant 	= $descendant;
					$this->insert();
				}
			}
		}
	}

	/**
	 * Remove associativos nao selecionados
	 *
	 * @param array $descendants
	 */
	private function removeNotSelected( $descendants ){
		
		$busca = array_map(function($row){
			return $row->descendant;
		},$this->get( ' ancestor = '.$this->ancestor ));

		// echo "<pre>";var_dump($busca); exit();
		foreach ($busca as $descendant) {
			if( !in_array($descendant, $descendants) ){
				$this->descendant = $descendant;
				$this->delete();
			}
		}
	}

	/**
	 * Remove registro do banco de dados
	 */
	public function delete_relacionamentos( $id ){
		if( $id ){
			$ancestor_return = $this->db->delete($this::DB_TABLE, array('ancestor' => $id));
			$descendant_return = $this->db->delete($this::DB_TABLE, array('descendant' => $id));
			return $ancestor_return && $descendant_return;
		}
		return null;
	}

	/**
	 * Resgata Array de Models como where, limit, offset
	 *
	 * @internal a difecenta com my_model é a chave do array resultante
	 * @param string $where
	 * @param int $limit
	 * @param int $offset
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
			$result_array[] = $model;
		}
		return $result_array;
	}

	/**
	 * 
	 * @return array
	 */
	public function buscaUnidadeOrganizacionalSemAncestral(){
		
		$this->db->select('uo.ancestor');
		$this->db->where('uo.ancestor NOT IN (SELECT uotp.descendant
								   			  FROM unidades_organizacionais_tree_paths uotp)', NULL, false);
		$query = $this->db->get('unidades_organizacionais_tree_paths uo');
		// echo "<pre>";var_dump($query->result());exit;
		return array_unique(array_map(function( $row ){
			return $row->ancestor;
		},$query->result()));
	}

	/**
	 * 
	 * @param integer $ancestral
	 * @return array
	 */
	public function buscaDescendentesDeUnidadeOrganizacional( $ancestral = null ){
		$this->db->select('descendant');
		if( !is_null($ancestral) ){
			$this->db->where('ancestor  = '.$ancestral);
		}
		$query = $this->db->get('unidades_organizacionais_tree_paths');
		return array_unique(array_map(function( $row ){
			return $row->descendant;
		},$query->result()));
	}

	/**
	 * Método para buscar ancestral
	 * 
	 * @param integer $id 
	 */
	public function buscaAncestral( $id ){

		if( $id ){
			return $this->get(' descendant = '.$id);
		}
	}

}