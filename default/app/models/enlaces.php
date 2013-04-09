<?php

/**
*
*/
Load::model('grupo');
class enlaces extends ActiveRecord
{
    protected $logger = True;

    public function listarEnlaces($page, $per_page, $estado='ACTIVO') {
        $page = Filter::get($page, 'int');
        $per_page = Filter::get($per_page, 'int');
        //$conditions = ($estado=='todos')?'':"WHERE `album`.`estado` = ". constant('self::'.$estado);

        $sql = "SELECT `enlaces`.*, `categoria`.`nombre` AS categoria_nombre
        FROM `enlaces`
        INNER JOIN `categoria_enlaces` ON `categoria_enlaces`.`enlaces_id` = `enlaces`.`id`
        INNER JOIN `categoria` ON `categoria`.`id` = `categoria_enlaces`.`categoria_id`";
        return $this->paginate_by_sql($sql, "page: $page", "per_page: $per_page");
    }

    public function buscar($id) {
        $columns = "columns: enlaces.id, nombre, descripcion, url, prioridad, categoria_id, visible, target";
        $joins = "join: INNER JOIN `categoria_enlaces` ON `categoria_enlaces`.`enlaces_id` = `enlaces`.`id`";
        $conditions= "enlaces.id=$id";
        return $this->find_first($conditions, $columns, $joins);
    }

    public function nuevo() {
        $this->begin();
        $this->nombre = Filter::get($this->nombre, 'string');
        $this->visible = Filter::get($this->visible, 'int');
        $this->prioridad = Filter::get($this->prioridad, 'int');
        $this->create();
    }

    public function eliminar($id)
    {
        if (Auth::get('grupo_id') == Grupo::ADMINISTRADOR) {
            if ($this->delete($id)) {
                return True;
            } else {
                Flash::error('Ha ocurrido un error durante la eliminación');
                return False;
            }
        } else {
            Flash::error('Acceso erroneo al sistema');
            return False;
        }
    }

    protected function after_create() {
        Load::model('categoria_enlaces');
        $indice = new CategoriaEnlaces();
        $indice->enlaces_id = $this->id;
        $aux = Input::post('enlaces');
        $indice->categoria_id = $aux['categoria_id'];
        if ( $indice->create() ) {
            $this->commit();
            Flash::valid('Enlace creado correctamente');
        } else {
            $this->rollback();
            Flash::error('Error al intentar crear nuevo enlace');
        }
    }


}

?>