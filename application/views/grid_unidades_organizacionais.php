<div class="container menu-superior-grid">
	<?php echo anchor('/unidadesorganizacionais/insere/', 'Nova Unidade Organizacional', 'class="btn btn-default"'); ?>
	<a href="#" onclick="$('#formato').val('pdf');$('#form_busca').submit();" class="btn btn-default">Exportar para PDF</a>
	<a href="#" onclick="$('#formato').val('csv');$('#form_busca').submit();" class="btn btn-default">Exportar para CSV</a>
</div>
<div class="container">

	<div class="panel panel-default">
	  
	  	<div class="panel-heading">

			<div class="row">
				<div class="col-sm-3">
					<h4>Unidades Organizacionais</h4>
				</div>
				<form id="form_busca" role="search" method="get" action="">
					<input type="hidden" name="formato" id="formato" value="default"/>
					<div class="col-sm-2">
						<h5 class="numero_resultados"><?php echo $numero_resultados.' resultado'.(($numero_resultados != 1)?'s':''); ?></h5>
					</div>
					<div class="col-sm-3 coluna-busca-panel-header">
						<input type="text" name="name" class="form-control" value="<?php echo ($this->input->get('name'))?$this->input->get('name'):''; ?>" placeholder="Unidade Organizacional" maxlength="255"/>
					</div>
					<div class="col-sm-2 coluna-busca-panel-header">
						<input type="text" name="cnpj" class="form-control" value="<?php echo ($this->input->get('cnpj'))?$this->input->get('cnpj'):''; ?>" placeholder="CNPJ" maxlength="14" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"/>
					</div>
					<div class="col-sm-2 coluna-busca-panel-header">
						<button class="btn btn-primary" onclick="$('#formato').val('default');$('#form_busca').submit();">
							<i class="glyphicon glyphicon-search"></i>
						</button>&nbsp;&nbsp;&nbsp;&nbsp;
						<?php 
						if( ($this->input->get('name')) || ($this->input->get('cnpj')) ){
							echo anchor('/', 'Todas', 'class="btn btn-primary"'); 
						}
						?>
					</div>
				</form>
			</div>
      	 </div>

	  	<div class="panel panel-body">
			<?php 
			$this->table->set_heading('Nome', 'CNPJ', 'Opções');
			echo $this->table->generate( $unidades_organizacionais ); ?>
	  	</div>

	</div>
</div>