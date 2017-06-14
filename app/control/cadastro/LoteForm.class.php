<?php
/**
 * LoteForm Registration
 * @author  <your name here>
 */
class LoteForm extends TPage
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
        $this->setActiveRecord('Lote');     // defines the active record
        
        // creates the form
        $this->form = new TQuickForm('form_Lote');
        $this->form->class = 'tform'; // change CSS class
        
        //$this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Cadastro de Lote');
        


        // create the form fields
        $id = new TEntry('id');
        $numero = new TEntry('numero');
        $peso = new TEntry('peso');
        $valor = new TEntry('valor');
        $temperatura = new TEntry('temperatura');
        $tipodescarte_id = new TDBCombo('tipodescarte_id', 'logimed', 'TipoDescarte', 'id', 'descricao', 'grupo');
        $fl_descarte = new TCombo('fl_descarte');
        $fl_descarte->setValue(TSession::getValue('fl_descarte'));
        $fl_descarte->addItems(array('S'=>'Sim','N'=>'Não'));
        $estoque_atual = new TEntry('estoque_atual');
        $total_estoque = new TEntry('total_estoque');
        $dt_descarte = new TDate('dt_descarte');
        $dt_descarte->setMask('dd/mm/yyyy');


        // add the fields
        $this->form->addQuickField('Id', $id,  75 );
        $this->form->addQuickField('Numero', $numero,  150, new TRequiredValidator);
        $this->form->addQuickField('Peso', $peso,  85, new TRequiredValidator);
        $this->form->addQuickField('Valor', $valor,  85, new TRequiredValidator);
        $this->form->addQuickField('Temperatura', $temperatura,  85 , new TRequiredValidator);
        $this->form->addQuickField('Tipo de Descarte', $tipodescarte_id,  230 , new TRequiredValidator);
        $this->form->addQuickField('Descarte?', $fl_descarte,  85 , new TRequiredValidator);
        $this->form->addQuickField('Estoque Atual', $estoque_atual, 85);
        $this->form->addQuickField('Total Estoque', $total_estoque, 85);
        $this->form->addQuickField('Data de Descarte', $dt_descarte, 100);
        
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
        $this->form->addQuickAction('Voltar para Listagem', new TAction(array('LoteList', 'onReload')), 'ico_datagrid.png');
        
        // vertical box container
        $container = new TVBox;
        //$container->style = 'width: 90%';
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
            $object = $this->form->getData('Lote');
                      
            // fill the form with the active record data
            $this->form->setData($object);
            
            // form validation
            $this->form->validate();
            
            if($object->estoque_atual > $object->total_estoque)
            {
                throw new Exception('Estoque atual não pode ser superior ao Total em estoque');
            }
            
            if(isset($object->dt_descarte) and $object->dt_descarte != '' and $object->fl_descarte != 'S')
            {
                throw new Exception('Lote não foi marcado para descarte.');
            }
            else
            {
                $object->dt_descarte = TDate::date2us($object->dt_descarte);
            }
            
            // stores the object
            $object->store();
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
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
