<?php

class Transportadora extends TRecord
{
    const TABLENAME = 'lote';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    function __construct($id = NULL)
    {
        parent::__construct();
        parent::addAttribute('nome');
        parent::addAttribute('preco');
        parent::addAttribute('avaliacao');
        parent::addAttribute('zonaatendimento_id');
        parent::addAttribute('habilitado');
    }
}

?>