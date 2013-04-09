<?php

/**
*
*/
Load::model('enlaces');
Load::model('categoria');
class EnlaceController extends AppController
{
    const NUM_CATEGORIAS = 10;

    function index($pag='pag',$page=1)
    {
        $enlace = new Enlaces();
        $this->enlaces = $enlace->listarEnlaces($page, self::NUM_CATEGORIAS);
    }

    function categorias($pag='pag',$page=1)
    {

        if (Input::hasPost('categoria')) {
            $categorias = new Categoria(Input::post('categoria'));
            $categorias->nueva();
            Input::delete();
        }

        if (!isset($categorias)) $categorias = new Categoria();
        $cantidad = $categorias->count();
        $this->prioridad = array();
        for ($i=0;$i<=$cantidad;$i++) { // Después se me ocurrirá algo mejora para hacer
            $this->prioridad[]=$i;
        }
        $this->categorias = $categorias->listarCategorias($page, self::NUM_CATEGORIAS);

    }

    function modificar_categoria($id=null,$key='key',$valueKey='') {

        if (Input::hasPost('categoria')) {
            $categorias = new Categoria(Input::post('categoria'));
            if( $categorias->modificar() ) Flash::valid('Categoria actualizada correctamente');
            Input::delete();
        }

        if (!isset($categorias)) $categorias = new Categoria();
        $this->categoria = $categorias->find($id);
        $cantidad = $categorias->count();
        $this->prioridad = array();
        for ($i=0;$i<=$cantidad;$i++) { // Después se me ocurrirá algo mejora para hacer
            $this->prioridad[]=$i;
        }
        $this->disabled = 'disabled';
        $this->categorias = $categorias->listarCategorias(1, self::NUM_CATEGORIAS);
        View::select('categorias');
    }

    function agregar($id=null,$key='key',$valueKey='') {

        if (Input::hasPost('enlaces')) {
            $enlaces = new Enlaces(Input::post('enlaces'));
            $enlaces->nuevo();
            Input::delete();
        }

        if (!isset($enlaces)) $enlaces = new Enlaces();
        if (isset($id) && $valueKey === md5($id.$this->ipKey.$this->expKey.'enlace')) {
            $this->disabled = 'disabled';
            $this->enlaces = $enlaces->buscar($id);
        }

        $this->target = array('_blank'=>'_blank En una nueva pestaña', '_top' => '_top Superior sin frame', '' => '_none Sin cambios, Dentro de la misma página');
        $this->visible = array('Privado', 'Visible');
        $cantidad = $enlaces->count();
        $this->prioridad = array();
        for ($i=0;$i<=$cantidad;$i++) { // Después se me ocurrirá algo mejora para hacer
            $this->prioridad[]=$i;
        }
    }

    function eliminar_enlace($id=null,$key='key',$valueKey='') {
        if($valueKey === md5($id.$this->ipKey.$this->expKey.'enlace')) {
            $enlaces = new Enlaces();
            if ( $enlaces->eliminar($id) ) Flash::valid('Enlace Eliminado éxitosamente');
            Router::redirect('dc-admin/enlace/');
        } else {
            Flash::error('Acceso incorrecto al sistema.');
            Router::redirect('dc-admin/enlace/');
        }
    }

    public function eliminar_categoria($id=null,$key='key',$valueKey='')
    {
        if($valueKey === md5($id.$this->ipKey.$this->expKey.'categoria')) {
            $categorias = new Categoria();
            if ( $categorias->eliminar($id) ) Flash::valid('Categoría Eliminada exitosamente');
            Router::redirect('dc-admin/enlace/categorias/');
        } else {
            Flash::error('Acceso incorrecto al sistema.');
            Router::redirect('dc-admin/enlace/categorias/');
        }
    }

}

?>