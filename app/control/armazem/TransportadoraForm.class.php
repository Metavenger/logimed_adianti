<?php
/**
 * TransportadoraForm Registration
 * @author  <your name here>
 */
class TransportadoraForm extends TPage
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
        $this->setActiveRecord('Transportadora');     // defines the active record
        
        // creates the form
        $this->form = new TQuickForm('form_Transportadora');
        $this->form->class = 'tform'; // change CSS class
               
        // define the form title
        $this->form->setFormTitle('Cadastro de Transportadora');
        


        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $preco = new TEntry('preco');
        $avaliacao = new TEntry('avaliacao');
        $zonaatendimento_id = new TDBCombo('zonaatendimento_id', 'logimed', 'ZonaAtendimento', 'id', 'descricao', 'descricao');
        $habilitado = new TCombo('habilitado');
        $habilitado->setValue(TSession::getValue('habilitado'));
        $habilitado->addItems(array('S'=>'Sim','N'=>'Não'));
        
        $preco->setNumericMask(2, ',', '.');

        // add the fields
        $this->form->addQuickField('Id', $id,  75 );
        $this->form->addQuickField('Nome', $nome,  300, new TRequiredValidator);
        $this->form->addQuickField('Preco', $preco,  85, new TRequiredValidator);
        $this->form->addQuickField('Avaliacao', $avaliacao,  85, new TRequiredValidator);
        $this->form->addQuickField('Zona de Atendimento', $zonaatendimento_id,  230 , new TRequiredValidator);
        $this->form->addQuickField('Habilitado?', $habilitado,  85 , new TRequiredValidator);



        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
         
        // create the form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar para Listagem', new TAction(array('TransportadoraList', 'onReload')), 'ico_datagrid.png');
        
        // vertical box container
        $container = new TVBox;
        //$container->style = 'width: 90%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
}
