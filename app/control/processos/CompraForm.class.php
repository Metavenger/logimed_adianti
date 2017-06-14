<?php
/**
 * CompraForm Registration
 * @author  <your name here>
 */
class CompraForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('logimed');              // defines the database
        $this->setActiveRecord('Compra');     // defines the active record
        
        // creates the form
        $this->form = new TQuickForm('form_Compra');
        $this->form->class = 'tform'; // change CSS class
        
        //$this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Cadastro de Compra');
        
        // create the form fields
        $id = new TEntry('id');
        //$lote_id = new TEntry('lote_id');
        //$lote_id = new TDBCombo('lote_id', 'logimed', 'Lote', 'id', 'numero', 'numero');
        $nome_cliente = new TEntry('nome_cliente');
        //$lote_id = new TDBSeekButton('lote_id', 'logimed', $this->form->getName(), 'Lote', 'numero', 'lote_id', 'lote_numero');
        $lote_numero = new TSeekButton('numero');
        $lote_nome  = new TEntry('nome_produto');
        $unidades = new TEntry('unidades');
        
        $action1 = new TAction(array(new LoteSeek, 'onSetup'));
        $action1->setParameter('database',      'logimed');
        TSession::setValue('loteseek_database','logimed');
        $action1->setParameter('parent',        $this->form->getName());
        TSession::setValue('loteseek_parent',$this->form->getName());
        $action1->setParameter('model',         'Lote');
        TSession::setValue('loteseek_model','Lote');
        $action1->setParameter('display_field', 'nome_produto');
        TSession::setValue('loteseek_display_field','nome_produto');
        $action1->setParameter('receive_key',   'numero');
        TSession::setValue('loteseek_receive_key','numero');
        $action1->setParameter('receive_field', 'nome_produto');
        TSession::setValue('loteseek_receive_field','nome_produto');
        
        $lote_numero->setAction($action1);
        
        $lote_numero->setSize(80);
        $lote_nome->setSize(100);
        $lote_nome->setEditable(false);

        // add the fields
        $this->form->addQuickField('Id', $id,  100 );
        $this->form->addQuickField('Cliente', $nome_cliente,  250, new TRequiredValidator );
        $this->form->addQuickFields('Lote', array($lote_numero, $lote_nome), new TRequiredValidator);
        $this->form->addQuickField('Unidades', $unidades,  100 );

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar', new TAction(array('CompraList', 'onReload')), 'ico_back.png');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 30%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    function onSave()
    {
        try
        {
            TTransaction::open('logimed');
            
            // get the form data into an active record Magistrado
            $object = $this->form->getData('Compra');
            $msg = false;
            // form validation
            $this->form->validate();
            
            if(isset($object->id))
            {
                $compra = new Compra($object->id);
            }
            else
            {
                $compra = new Compra();
            }
            $compra->fl_ativo = 'S';
            $compra->nome_cliente = utf8_encode($object->nome_cliente);
            
            $lote = Lote::newFromNumero($object->numero);
            
            $compra->lote_id = $lote->id;

            if($lote->estoque_atual < $object->unidades)
            {
                throw new Exception('Estoque insuficiente para o lote solicitado.');
            }
            else
            {
                if(isset($object->id))
                {
                    $old_unidades = $compra->unidades;
                    if($old_unidades != $object->unidades)
                    {
                        $lote->estoque_atual += ($old_unidades - $object->unidades);
                    }
                }
                $compra->unidades = $object->unidades;
                $lote->estoque_atual -= $object->unidades;
                if(($lote->total_estoque * 0.2) > $lote->estoque_atual)
                {
                    $msg = true;
                    
                }
                $compra->valor = $lote->valor * $object->unidades;
                $lote->store();
                $compra->store();
            } 
            
            $action = new TAction(array('CompraList', 'onReload'));
            new TMessage('info', 'Compra registrada com sucesso.', $action);
            if($msg)
            {
                new TMessage('info', 'Estoque baixo. Por favor reabastecer.');
            }      
            
            $this->form->setData($compra);
            
            TTransaction::close();
        }
        catch(Exception $e)
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'tab'
                TTransaction::open('logimed');
                
                // instantiates object AtosPorAcao
                $object = new Compra($key);
                
                $lote = new Lote($object->lote_id);
                
                $object->numero = $lote->numero;
                $object->nome_produto = $lote->nome;
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
