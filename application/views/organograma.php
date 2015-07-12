
<div class="container">

  <div class="panel panel-default">
    
    <div class="panel-heading">

      <div class="row">
        <div class="col-sm-6">
          <h4>Organograma das Unidades Organizacionais</h4>
        </div>
        <div class="col-sm-6 coluna-busca-panel-header">  
          <div class="input-group" style="float:right">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo ($active)?$active->name:'Unidade Organizacional'; ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <?php 
              echo "<li ".((is_null($this->input->get('id')))?'active':'').">";
              echo anchor('/unidadesorganizacionais/visualizarorganograma','Todas');
              foreach ($uo_options as $row) {
                echo "<li ".(($this->input->get('id')==$row->id)?'active':'').">";
                echo anchor('/unidadesorganizacionais/visualizarorganograma/?id='.$row->id,$row->name);
                echo "</li>";
              } ?>
            </ul>
          </div>
        </div>
      </div>

    </div>

    <div class="panel-body">

      <div class="tree">
        <ul>
            <?php 

            function mostraDescendente( $unidade_organizacional ){
              foreach ($unidade_organizacional->descendentes as $descendente) {
                echo '<li><a href="#">'.$descendente->name.'</a>';
                if( count($descendente->descendentes) > 0 ){
                  echo " <ul> ";
                  mostraDescendente( $descendente );
                  echo " </ul> ";
                }
                echo '</li>';
              }
            }

            foreach ($arvore as $key => $unidade) {
              echo '<li><a href="#">'.$unidade->name.'</a>';
              if( count($unidade->descendentes) > 0 ){
                echo " <ul> ";
                mostraDescendente( $unidade );
                echo " </ul> ";
              }
              echo '</li>';
            } 

            ?>
        </ul>
      </div>
      </div>
    </div>
  </div>

</div>