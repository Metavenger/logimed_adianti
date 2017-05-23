<?php

class TipoDescarte extends TRecord
{
    const TABLENAME = 'tipo_descarte';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('grupo');
        parent::addAttribute('descricao');
    }
}

?>