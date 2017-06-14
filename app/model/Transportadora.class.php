<?php

class Transportadora extends TRecord
{
    const TABLENAME = 'transportadora';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    private $zona_atendimento;
    
    function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('preco');
        parent::addAttribute('avaliacao');
        parent::addAttribute('zonaatendimento_id');
        parent::addAttribute('habilitado');
    }
    
    /**
     * Method set_zona_atendimento
     * Sample of usage: $transportadora->zona_atendimento = $object;
     * @param $object Instance of ZonaAtendimento
     */
    public function set_zona_atendimento(ZonaAtendimento $object)
    {
        $this->zona_atendimento = $object;
        $this->zona_atendimento_id = $object->id;
    }
    
    /**
     * Method get_zona_atendimento
     * Sample of usage: $transportadora->zona_atendimento->attribute;
     * @returns ZonaAtendimento instance
     */
    public function get_zona_atendimento()
    {
        // loads the associated object
        if (empty($this->zona_atendimento))
            $this->zona_atendimento = new ZonaAtendimento($this->zona_atendimento_id);
    
        // returns the associated object
        return $this->zona_atendimento;
    }
}

?>