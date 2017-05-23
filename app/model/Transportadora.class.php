<?php

class Transportadora extends TRecord
{
    const TABLENAME = 'transportadora';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('preco');
        parent::addAttribute('avaliacao');
        parent::addAttribute('zonaatendimento_id');
        parent::addAttribute('habilitado');
    }
}

?>