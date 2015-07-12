<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller para Unidades Organizacionais
 *
 * CRUD+organograma de Unidades Organizacionais
 */
class UnidadesOrganizacionais extends My_Controller {

	/**
	 * Lista unidades organizacionais
	 */
	public function lista() {
		
		$this->load->helper('url');
		$this->load->library('table');
		$this->aplicaTemplateDefaultTable();

		$where = '';
		$this->data = array();
		$unidades_organizacionais = array();

		$this->load->model('Unidades_organizacionais');

		// GET name
		$where .= ($this->input->get('name'))?' name LIKE "%'.$this->input->get('name').'%" ':'';
		// GET cnpj
		if( $this->input->get('cnpj') ){
			$where .= (($this->input->get('name'))?' AND ':'').' cnpj LIKE "%'.$this->input->get('cnpj').'%" ';
		}

		$unidades_organizacionais_lista = $this->Unidades_organizacionais->get( $where );

		if( $this->input->get('formato') == 'pdf' || $this->input->get('formato') == 'csv' ){
			$this->download( $unidades_organizacionais_lista );
			exit;
		}

		$this->data['numero_resultados'] = count($unidades_organizacionais_lista);
		foreach ($unidades_organizacionais_lista as $unidades_organizacionais_item) {

			$unidades_organizacionais[] = array(
				anchor('/unidadesorganizacionais/insere/'.$unidades_organizacionais_item->id, $unidades_organizacionais_item->name),
				$unidades_organizacionais_item->cnpj,
				$this->preparaOpcaoEditarGrid( $unidades_organizacionais_item->id ),
			);

		}

		$this->data['unidades_organizacionais'] = $unidades_organizacionais;

		$this->middle = 'grid_unidades_organizacionais';
		$this->layout();
	}

	/**
	 * Insere unidades organizacionais
	 *
	 * @param integer id da unidade organizacional
	 */
	public function insere( $id = null ) {

		$this->load->helper('url');
		
		$this->load->library('table');
		$this->load->library('form_validation');

		$this->load->model(array('Unidades_organizacionais','Unidades_organizacionais_tree_paths'));

		$this->preparaFormulario( $id );

		$this->Unidades_organizacionais->id = (is_null($id) && $this->input->post('id'))?$this->input->post('id'):$id;

		$this->data['unidades_organizacionais'] = $this->Unidades_organizacionais;

		$this->aplicaRegrasDeValidacao();

		if( $this->form_validation->run() ){
			$retorno = $this->mantemPersistencia();
		}

		$this->data['descendants'] = array();
		if( !is_null($this->Unidades_organizacionais->id) ){
			$this->Unidades_organizacionais->load( $this->Unidades_organizacionais->id );
			$this->data['unidades_organizacionais'] = $this->Unidades_organizacionais;

			$this->data['descendants'] = array_map(function($row){
				return $row->descendant;
			},$this->Unidades_organizacionais_tree_paths->get( 'ancestor = '.$this->Unidades_organizacionais->id ));
		}

		$this->middle = 'formulario_unidade_organizacional';

		$this->layout();
	}

	/**
	 * Método para encapsular preparação de campos de formulário
	 */
	private function preparaFormulario( $id ){
		if( $id ){
			$this->data['unidades_organizacionais_tree_paths'] = $this->Unidades_organizacionais->get( 'id != '.$id.' ' );
		}else{
			$this->data['unidades_organizacionais_tree_paths'] = $this->Unidades_organizacionais->get();
		}
	}

	/**
	 * Método para encapsular regras de validacao de formulário
	 */
	private function aplicaRegrasDeValidacao(){
		$cnpj_rule = 'required|callback_validacao_cnpj';
		if( is_null($this->Unidades_organizacionais->id) ){
			$cnpj_rule = 'required|is_unique[unidades_organizacionais.cnpj]|callback_validacao_cnpj';
		}

		$this->form_validation->set_rules(array(
			array(
				'field' => 'name',
				'label' => 'Nome da Unidade Organizacional',
				'rules' => 'required',
			),
			array(
				'field' => 'cnpj',
				'label' => 'CNPJ da Unidade Organizacional',
				'rules' => $cnpj_rule,
			)
		));

		$this->form_validation->set_error_delimiters('<div class="alert alert-danger">', '</div>');
	}

	/**
	 * Metodo para encapsular processo de persistencia
	 */
	private function mantemPersistencia(){
		$this->Unidades_organizacionais->name = $this->input->post('name');
		$this->Unidades_organizacionais->cnpj = $this->input->post('cnpj');
		$retorno = $this->Unidades_organizacionais->save();

		$this->Unidades_organizacionais_tree_paths->insertUnique( $this->Unidades_organizacionais->id, $this->input->post('descendants[]') );

		$this->data['unidades_organizacionais'] = $this->Unidades_organizacionais;

		$this->data['message'] = '<div class="alert alert-danger">Houve algum erro.</div>';
		if( is_null($this->Unidades_organizacionais->id) && $retorno ){ // criado

			$this->data['message'] = '<div class="alert alert-success">Unidade Organizacional criada com sucesso!</div>';

		}else if( !is_null($this->Unidades_organizacionais->id) && $retorno ){ // atualizado

			$this->data['message'] = '<div class="alert alert-success">Unidade Organizacional atualizada com sucesso!</div>';

		}
	}

	/**
	 * Remove unidades organizacionais
	 */
	public function remove() {

		$this->load->model('Unidades_organizacionais');
		$this->load->model('Unidades_organizacionais_tree_paths');

		$this->load->helper('url');
		$this->load->library('form_validation');

		if( $this->input->post('confirmado') ){
			$id = $this->input->post('id');

			// deleta relacionamentos de elemento e seus descendentes
			// deleta eus descendentes
			$descendentes = $this->Unidades_organizacionais_tree_paths->buscaDescendentesDeUnidadeOrganizacional( $id );
			$this->Unidades_organizacionais_tree_paths->delete_relacionamentos( $id );
			foreach ($descendentes as $descendente) {
				$this->Unidades_organizacionais_tree_paths->delete_relacionamentos( $descendente );
			}
			$retorno_uo = $this->Unidades_organizacionais->delete_descendentes( $descendentes );

			$this->Unidades_organizacionais->id = $id;
			$retorno_uo = $this->Unidades_organizacionais->delete();

			$this->data['confirmado'] = 1;
		}else{
			$this->Unidades_organizacionais->load( $this->input->get('id') );

			$this->data = array(
				'id' => $this->Unidades_organizacionais->id,
				'name' => $this->Unidades_organizacionais->name
			);
		}

		$this->middle = 'confirma_remocao';

		$this->layout();
	}

	/**
	 * Download unidades organizacionais
	 */
	public function download( $unidades_organizacionais_lista ){

		$html = "<h3>Unidades Organizacionais</h3>";
		$html .= "<table>";
		$html .= "<thead><tr><th>Nome</th><th>CNPJ</th></tr></thead>";
		$html .= "<tbody>";
		foreach ($unidades_organizacionais_lista as $unidade) {
			$html .= "<tr>";
			$html .= "<td>".$unidade->name."</td>";
			$html .= "<td>".$unidade->cnpj."</td>";
			$html .= "</tr>";
		}
		$html .= "</tbody>";
		$html .= "</table>";

		if( $this->input->get('formato') == 'pdf' ){

			$this->load->library('mpdf');
			$mpdf = new mPDF('c');
			$mpdf->WriteHTML($html);
			$mpdf->Output(); 
			exit;

		}else if( $this->input->get('formato') == 'csv' ){

			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			header('Content-Disposition: attachment; filename=pedidos.csv');
			echo "<html><body>",$html."</body></html>";
			exit;

		}

	}

	/**
	 * Prepara Opção de edição para grid de unidades organizacionais
	 *
	 * @param integer $id
	 */
	private function preparaOpcaoEditarGrid( $id ){

		$opcoes = '';
		// editar
		$opcoes .= anchor('/unidadesorganizacionais/insere/'.$id, '<i alt="Editar" class="glyphicon glyphicon-edit" title="Editar"></i>');
		// remover
		$opcoes .= '&nbsp;&nbsp;&nbsp;';
		$opcoes .= anchor('/unidadesorganizacionais/remove/?id='.$id, '<i alt="Remover" class="glyphicon glyphicon-trash" title="Remover"></i>');

		return $opcoes;
	}

	/**
	 * Valida CNPJ
	 *
	 * @param string $cnpj
	 * @return bool
	 */
	public function validacao_cnpj($cnpj){
		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

		// Valida tamanho
		if (strlen($cnpj) != 14){
			$this->form_validation->set_message('validacao_cnpj', '%s não é válido.');
			return false;
		}

		// Valida primeiro dígito verificador
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++){
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto)){
			$this->form_validation->set_message('validacao_cnpj', '%s não é válido.');
			return false;
		}

		// Valida segundo dígito verificador
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++){
			$soma += $cnpj{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		if(!($cnpj{13} == ($resto < 2 ? 0 : 11 - $resto))){
			$this->form_validation->set_message('validacao_cnpj', '%s não é válido.');
			return false;
		}

		return true;
	}

	/**
	 * Template personalizado para grid de unidades organizacionais
	 */
	private function aplicaTemplateDefaultTable(){
		$template = array(
		        'table_open'            => '<table class="table">',

		        'thead_open'            => '<thead>',
		        'thead_close'           => '</thead>',

		        'heading_row_start'     => '<tr>',
		        'heading_row_end'       => '</tr>',
		        'heading_cell_start'    => '<th>',
		        'heading_cell_end'      => '</th>',

		        'tbody_open'            => '<tbody>',
		        'tbody_close'           => '</tbody>',

		        'row_start'             => '<tr>',
		        'row_end'               => '</tr>',
		        'cell_start'            => '<td>',
		        'cell_end'              => '</td>',

		        'row_alt_start'         => '<tr>',
		        'row_alt_end'           => '</tr>',
		        'cell_alt_start'        => '<td>',
		        'cell_alt_end'          => '</td>',

		        'table_close'           => '</table>'
		);

		$this->table->set_template($template);
	}

	/**
	 *
	 */
	public function visualizarorganograma(){

		$this->load->helper('url');

		$this->load->model('Unidades_organizacionais');

		$this->data['active'] = false;
		if( !is_null($this->input->get('id')) ){
			$this->Unidades_organizacionais->load( $this->input->get('id') );
			$this->data['active'] = $this->Unidades_organizacionais;
		}

		$this->data['uo_options'] = $this->Unidades_organizacionais->get();

		$this->data['arvore'] = $this->Unidades_organizacionais->buscaUnidadesOrganizacionaisComDescendentes( $this->input->get('id') );

		$this->middle = 'organograma';
		$this->layout();
	}

}