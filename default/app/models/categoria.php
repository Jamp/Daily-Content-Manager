<?php

/**
*
*/
class categoria extends ActiveRecord
{
    protected $logger = True;

    function nueva($nombre, $descripcion)
    {
        if (Auth::get('grupo_id') == Grupo::COLABORADOR) {
            $rs = $this->find('nombre='.$nombre);
            if ( $rs ) {
                Flash::error('Ya existe una categoría con ese nombre');
                return False;
            } else {
                $this->nombre = Filter::get($nombre, 'string');
                $this->slug = Utils::dash(strtolower($this->nombre));
                $this->descripcion = Filter::get($descripcion, 'string');
                $this->prioridad = '0';

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

    function eliminar($id)
    {
        if (Auth::get('grupo_id') == Grupo::COLABORADOR) {
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

    protected function after_delete() {
        Load::model('categoria_enlaces');
        $categoria_enlaces = new CategoriaEnlaces();
        $categoria_enlaces->delete('categoria_id='.$this->id);
    }
}

?>