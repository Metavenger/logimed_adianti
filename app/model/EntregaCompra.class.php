<?php
/**
 * EntregaCompra Active Record
 * @author  <your-name-here>
 */
class EntregaCompra extends TRecord
{
    const TABLENAME = 'entrega_compra';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $compra;
    private $entrega;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('entrega_id');
        parent::addAttribute('compra_id');
    }

    
    /**
     * Method set_compra
     * Sample of usage: $entrega_compra->compra = $object;
     * @param $object Instance of Compra
     */
    public function set_compra(Compra $object)
    {
        $this->compra = $object;
        $this->compra_id = $object->id;
    }
    
    /**
     * Method get_compra
     * Sample of usage: $entrega_compra->compra->attribute;
     * @returns Compra instance
     */
    public function get_compra()
    {
        // loads the associated object
        if (empty($this->compra))
            $this->compra = new Compra($this->compra_id);
    
        // returns the associated object
        return $this->compra;
    }
    
    
    /**
     * Method set_entrega
     * Sample of usage: $entrega_compra->entrega = $object;
     * @param $object Instance of Entrega
     */
    public function set_entrega(Entrega $object)
    {
        $this->entrega = $object;
        $this->entrega_id = $object->id;
    }
    
    /**
     * Method get_entrega
     * Sample of usage: $entrega_compra->entrega->attribute;
     * @returns Entrega instance
     */
    public function get_entrega()
    {
        // loads the associated object
        if (empty($this->entrega))
            $this->entrega = new Entrega($this->entrega_id);
    
        // returns the associated object
        return $this->entrega;
    }
    


}
