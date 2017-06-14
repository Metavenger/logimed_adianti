<?php

class Lote extends TRecord
{
    const TABLENAME = 'lote';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    
    private $tipo_descarte;
    
    function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('numero');
        parent::addAttribute('peso');
        parent::addAttribute('valor');
        parent::addAttribute('temperatura');
        parent::addAttribute('tipodescarte_id');
        parent::addAttribute('fl_descarte');
        parent::addAttribute('estoque_atual');
        parent::addAttribute('total_estoque');
        parent::addAttribute('dt_descarte');
        parent::addAttribute('nome');
    }
    
    /**
     * Method set_tipo_descarte
     * Sample of usage: $lote->tipo_descarte = $object;
     * @param $object Instance of TipoDescarte
     */
    public function set_tipo_descarte(TipoDescarte $object)
    {
        $this->tipo_descarte = $object;
        $this->tipo_descarte_id = $object->id;
    }
    
    /**
     * Method get_tipo_descarte
     * Sample of usage: $lote->tipo_descarte->attribute;
     * @returns TipoDescarte instance
     */
    public function get_tipo_descarte()
    {
        // loads the associated object
        if (empty($this->tipo_descarte))
            $this->tipo_descarte = new TipoDescarte($this->tipo_descarte_id);
    
        // returns the associated object
        return $this->tipo_descarte;
    }
    
    static public function newFromNumero($numero)
    {
        $repos = new TRepository(__CLASS__);
        $criteria = new TCriteria;
        $criteria->add(new TFilter('numero', '=', (int)$numero));
        $objects = $repos->load($criteria);
        if (isset($objects[0]))
        {
            return $objects[0];
        }
    }
}

?>
