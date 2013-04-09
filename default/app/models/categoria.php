<?php

/**
*
*/
Load::model('grupo');
Load::model('categoria_enlaces');
class categoria extends ActiveRecord
{
    protected $logger = True;

    public function nueva()
    {
        if (Auth::get('grupo_id') == Grupo::ADMINISTRADOR) {
            $rs = $this->find("nombre='$this->nombre'");
            if ( $rs ) {
                Flash::error('Ya existe una categoría con ese nombre');
                return False;
            } else {
                $this->nombre = Filter::get($this->nombre, 'string');
                $this->slug = Utils::slug($this->nombre);
                $this->descripcion = Filter::get($this->descripcion, 'string');
                //$this->prioridad = "'$this->prioridad'";

                if ( $this->create() ) {
                    return True;
                } else {
                    Flash::error('Error al crear categoría');
                    return False;
                }
            }
        } else {
            Flash::error('Acceso erroneo al sistema');
            return False;
        }
    }

    public function modificar()
    {
        if (Auth::get('grupo_id') == Grupo::ADMINISTRADOR) {
            $this->nombre = Filter::get($this->nombre, 'string');
            $this->slug = Utils::slug($this->nombre);
            $this->descripcion = Filter::get($this->descripcion, 'string');
            //$this->prioridad = "'$this->prioridad'";

            if ( $this->update() ) {
                return True;
            } else {
                Flash::error('Error al crear categoría');
                return False;
            }
        } else {
            Flash::error('Acceso erroneo al sistema');
            return False;
        }
    }

    function eliminar($id)
    {
        if (Auth::get('grupo_id') == Grupo::ADMINISTRADOR) {
            Load::model('categoria_enlaces');
            $categoria_enlaces = new CategoriaEnlaces();
            $numero = $categoria_enlaces->count('categoria_id='.$id);
            if ($numero != 0) {
                Flash::error('No se puede eliminar una categoría que no está vacía');
                return False;
            } else {
                $this->id = $id;
                if ($this->delete()) {
                    return True;
                } else {
                    Flash::error('Ha ocurrido un error durante la eliminación');
                    return False;
                }
            }
        } else {
            Flash::error('Acceso erroneo al sistema');
            return False;
        }
    }

    public function getCategoria() {
        return $this->find();
    }

    protected function after_delete() {
        Load::model('categoria_enlaces');
        $categoria_enlaces = new CategoriaEnlaces();
        $categoria_enlaces->delete('categoria_id='.$this->id);
    }

    public function listarCategorias($page, $per_page) {
        $page = Filter::get($page, 'int');
        $per_page = Filter::get($per_page, 'int');

        $sql = "SELECT `categoria`.`id`, `categoria`.`nombre`, `categoria`.`slug`, `categoria`.`prioridad`,
        count(`categoria_enlaces`.`id`) AS cantidad
        FROM `categoria`
        LEFT JOIN `categoria_enlaces` ON `categoria`.`id` = `categoria_enlaces`.`categoria_id`
        GROUP BY `categoria`.`id`";
        return $this->paginate_by_sql($sql, "page: $page", "per_page: $per_page");
    }
}

?>