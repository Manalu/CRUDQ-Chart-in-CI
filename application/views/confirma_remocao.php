
<div class="container">

	<?php if( isset($id) && !is_null($id) ){ ?>
		<div class="panel panel-default">
			<div class="panel-heading">Confirma remoção de Unidade Organizacional "<?php echo $name ?>"? Se tiver, essa remoção apagará todos seus descendentes.</div>
			<div class="panel-body">
				<?php 
				echo form_open('', '', array('id'=>set_value('',$id),
											 'confirmado'=>1));
				echo form_submit('confirmar', 'Confirmar', 'class="btn btn-danger"'); 
				echo "&nbsp;&nbsp;";
				echo anchor('/','Voltar', 'class="btn btn-default"');
				echo form_close(); 
				?>
			</div>
		</div>
	<?php }else if( !$confirmado ){ ?>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="alert alert-danger">Unidade Organizacional não encontrada!</div>
				<?php echo anchor('/','Voltar', 'class="btn btn-default"'); ?>
			</div>
		</div>
	<?php }else{ ?>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="alert alert-success">Unidade Organizacional removida com sucesso!</div>
				<?php echo anchor('/','Voltar', 'class="btn btn-default"'); ?>
			</div>
		</div>
	<?php } ?>

</div>