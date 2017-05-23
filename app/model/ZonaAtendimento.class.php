<?php

class ZonaAtendimento extends TRecord
{
    const TABLENAME = 'zona_atendimento';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('descricao');
    }
}

?>

