<?php

class TipoDescarte extends TRecord
{
    const TABLENAME = 'tipo_descarte';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    function __construct($id = NULL)
    {
        parent::__construct();
        parent::addAttribute('grupo');
        parent::addAttribute('descricao');
    }
}

?>