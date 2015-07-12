<?php

class Unidades_organizacionais extends My_Model {
	
	const DB_TABLE = 'unidades_organizacionais';

	const DB_TABLE_PK = 'id';

	/**
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Nome da Unidade Organizacional
	 * @var string
	 */
	public $name;

	/**
	 * CNPJ da Unidade Organizacional
	 * @var string
	 */
	public $cnpj;

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

		$this->db->order_by('name ASC');

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
			$model->buscaAncestral();
			$result_array[$row->{$this::DB_TABLE_PK}] = $model;
		}
		return $result_array;
	}

	/** 
	 * Método para buscar ancestral
	 */
	private function buscaAncestral(){
		$this->load->model('Unidades_organizacionais_tree_paths');
		$this->ancestral = $this->Unidades_organizacionais_tree_paths->buscaAncestral( $this->id );
	}

	/**
	 * 
	 * @param integer $id
	 * @return array
	 */
	public function buscaUnidadesOrganizacionaisComDescendentes( $id ){

		$arvore = array();
		
		if( is_null($id) ){
			$arvore = $this->montaArvore( $this->buscaUnidadeOrganizacionalSemAncestral( $id ) );
		}else{
			$arvore = $this->montaArvore( $this->get( 'id = '.$id ) );
		}

		return $arvore;
	}

	/**
	 * Método para montar arvore em array para organograma
	 *
	 * @param array $arvore
	 * @param array $unidades_organizacionais
	 */
	private function montaArvore( $arvore ){

		foreach ($arvore as $chave => $unidade_organizacional) {
			$descendentes = $this->buscaDescendentesDeUnidadeOrganizacional( $unidade_organizacional->id );
			
			if( count($descendentes) > 0 ){
				$descendentes = $this->montaArvore( $descendentes );
			}

			$arvore[$chave]->descendentes = $descendentes;
		}

		return $arvore;
	}

	/**
	 * 
	 * @return array
	 */
	private function buscaUnidadeOrganizacionalSemAncestral(){

		$this->load->model('Unidades_organizacionais_tree_paths');

		$nao_orfas = $this->Unidades_organizacionais_tree_paths->buscaDescendentesDeUnidadeOrganizacional();
		
		if( count($nao_orfas) > 0 ){
			$this->db->where(' id NOT IN ('.implode(',', $nao_orfas).') ', NULL, false);
			$query = $this->db->get($this::DB_TABLE);
			return $query->result();
		}else{
			return array();
		}
	}

	/**
	 * 
	 * @param integer $ancestral
	 * @return array
	 */
	private function buscaDescendentesDeUnidadeOrganizacional( $ancestral ){

		$this->load->model('Unidades_organizacionais_tree_paths');

		$descendentes = $this->Unidades_organizacionais_tree_paths->buscaDescendentesDeUnidadeOrganizacional( $ancestral );
		
		if( count($descendentes) > 0 ){
			$this->db->where(' id IN ('.implode(',', $descendentes).') ', NULL, false);
			$query = $this->db->get($this::DB_TABLE);
			return $query->result();
		}else{
			return array();
		}
	}	

	/**
	 * Método para remover todos os descendentes de uma dada UO
	 */
	public function delete_descendentes( $descendentes ){

		foreach ($descendentes as $value) {
			$this->load($value);
			$this->delete();
			
		}
	}

}