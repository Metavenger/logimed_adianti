<?php
/**
 * Compra Active Record
 * @author  <your-name-here>
 */
class Compra extends TRecord
{
    const TABLENAME = 'compra';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $lote;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('lote_id');
        parent::addAttribute('unidades');
        parent::addAttribute('valor');
        parent::addAttribute('fl_ativo');
        parent::addAttribute('nome_cliente');
        parent::addAttribute('endereco');
    }

    
    /**
     * Method set_lote
     * Sample of usage: $compra->lote = $object;
     * @param $object Instance of Lote
     */
    public function set_lote(Lote $object)
    {
        $this->lote = $object;
        $this->lote_id = $object->id;
    }
    
    /**
     * Method get_lote
     * Sample of usage: $compra->lote->attribute;
     * @returns Lote instance
     */
    public function get_lote()
    {
        // loads the associated object
        if (empty($this->lote))
            $this->lote = new Lote($this->lote_id);
    
        // returns the associated object
        return $this->lote;
    }
    


}
