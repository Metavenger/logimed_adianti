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
        $flag_esgotado = new TCombo('flag_esgotado');
        $flag_esgotado->setValue(TSession::getValue('flag_esgotado'));
        $flag_esgotado->addItems(array('S'=>'Sim','N'=>'Não'));


        // add the fields
        $this->form->addQuickField('Id', $id,  75 );
        $this->form->addQuickField('Numero', $numero,  150, new TRequiredValidator);
        $this->form->addQuickField('Peso', $peso,  85, new TRequiredValidator);
        $this->form->addQuickField('Valor', $valor,  85, new TRequiredValidator);
        $this->form->addQuickField('Temperatura', $temperatura,  85 , new TRequiredValidator);
        $this->form->addQuickField('Tipo de Descarte', $tipodescarte_id,  230 , new TRequiredValidator);
        $this->form->addQuickField('Esgotado?', $flag_esgotado,  85 , new TRequiredValidator);



        
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
}
