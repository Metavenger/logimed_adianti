<?php

class Lote extends TRecord
{
    const TABLENAME = 'lote';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    function __construct($id = NULL)
    {
        parent::__construct();
        parent::addAttribute('numero');
        parent::addAttribute('peso');
        parent::addAttribute('valor');
        parent::addAttribute('temperatura');
        parent::addAttribute('tipodescarte_id');
        parent::addAttribute('flag_esgotado');
    }
}

?>
