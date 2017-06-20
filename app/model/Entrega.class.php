<?php
/**
 * Entrega Active Record
 * @author  <your-name-here>
 */
class Entrega extends TRecord
{
    const TABLENAME = 'entrega';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $transportadora;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('transportadora_id');
        parent::addAttribute('dt_confirmacao');
        parent::addAttribute('fl_ativo');
    }

    
    /**
     * Method set_transportadora
     * Sample of usage: $entrega->transportadora = $object;
     * @param $object Instance of Transportadora
     */
    public function set_transportadora(Transportadora $object)
    {
        $this->transportadora = $object;
        $this->transportadora_id = $object->id;
    }
    
    /**
     * Method get_transportadora
     * Sample of usage: $entrega->transportadora->attribute;
     * @returns Transportadora instance
     */
    public function get_transportadora()
    {
        // loads the associated object
        if (empty($this->transportadora))
            $this->transportadora = new Transportadora($this->transportadora_id);
    
        // returns the associated object
        return $this->transportadora;
    }
    
    public function getItensEntrega($param = null)
    {
        if($param)
        {
            $sql = "select * from entrega_compra where entrega_id = $this->id and compra_id = $param";
            $conn = TTransaction::get();
            $result = $conn->query($sql);
            return $result->fetchObject();
        }
        else
        {
            $sql = "select * from entrega_compra where entrega_id = $this->id";
            $conn = TTransaction::get();
            $result = $conn->query($sql);
            return $result->fetchAll(PDO::FETCH_OBJ);
        }
    }
}
