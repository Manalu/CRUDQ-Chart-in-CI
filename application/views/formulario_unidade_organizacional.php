
<div class="container">
	<?php echo validation_errors(); ?>
	<?php echo (isset($message))?$message:''; ?>
</div>

<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading"><h4>Unidade Organizacional</h4></div>
		<?php echo form_open('', '', array('id'=>set_value('',$unidades_organizacionais->id), 
										   'confirmado'=>(( isset($por_confirmar) && $por_confirmar )?$por_confirmar:0))); ?>
			
			<div class="panel-body">
				<div class="row">
					<?php echo form_label('Nome:','name',array('class' => 'col-sm-2')); ?>
					<div class="col-sm-10">
						<?php echo form_input('name',set_value('',$unidades_organizacionais->name), 'maxlength="250" style="width:200px !important;" class="form-control"'); ?>
					</div>
				</div>

				<div class="row">&nbsp;</div>

				<div class="row">
					<?php echo form_label('CNPJ:','cnpj', array('class'=>'col-sm-2')); ?>
					<div class="col-sm-10">
						<?php echo form_input('cnpj', set_value('',$unidades_organizacionais->cnpj), 'maxlength="14" style="width:200px;" onkeyup="this.value=this.value.replace(/[^0-9]/g,\'\')" class="form-control" '.(($unidades_organizacionais->cnpj)?'readonly':'')); ?>
					</div>
				</div>

				<div class="row">&nbsp;</div>

				<div class="row">
					<?php echo form_label('Descendentes:','descendants[]', array('class'=>'col-sm-2')); ?>
					<div class="col-sm-10">
						<?php 
						$options 		= array();
						$comAcenstral 	= array();
						foreach ($unidades_organizacionais_tree_paths as $value) {
							if( count($value->ancestral) > 0 ){
								array_push($comAcenstral,$value->id);
							}
							$options[$value->id] = $value->name;
						}
						echo form_multiselect('descendants[]', $options, $descendants, 'style="width:200px; class="form-control" id="descendants"');
						?>
						<input type="hidden" name="comAncestral" id="comAncestral" value="<?php echo implode(',', $comAcenstral); ?>"/>
					</div>
				</div>

				<div class="row">&nbsp;</div>

				<div class="row">
					<div class="col-sm-2">
						<?php 
							if( isset($por_confirmar) && $por_confirmar ){
								echo form_submit('confirmar', 'Confirmar', 'class="btn btn-danger"'); 
							}else{
								echo form_submit('salvar', 'Salvar', 'class="btn btn-primary" onclick="if(!verificaMudanca()){return false;}"'); 
							}
							echo "&nbsp;&nbsp;";
							echo anchor('/','Voltar', 'class="btn btn-default"');
						?>
					</div>
				</div>
			</div>

		<?php echo form_close(); ?>
	</div>
</div>

<script>
	/**
	 *
	 */
	 function verificaMudanca(){
	 	var retorno = $('#descendants').val().find(function(element){
	 		return $('#comAncestral').val().split(',').indexOf(element) != -1
	 	})
	 	if( typeof retorno == 'string' ){
	 		var nome = $('[value="'+retorno+'"]').html();
	 		if(confirm('A Unidade Organizacional '+nome+' já possui ancestral, deseja mudá-la?')){
	 			return true;
	 		}else{
	 			return false;
	 		}
	 	}else{
	 		return true;
	 	}
	 }
</script>