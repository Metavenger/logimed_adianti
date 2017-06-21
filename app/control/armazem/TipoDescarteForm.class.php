<?php
/**
 * TipoDescarteForm Registration
 * @author  <your name here>
 */
class TipoDescarteForm extends TPage
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
        $this->setActiveRecord('TipoDescarte');     // defines the active record
        
        // creates the form
        $this->form = new TQuickForm('form_TipoDescarte');
        $this->form->class = 'tform'; // change CSS class
        
        // define the form title
        $this->form->setFormTitle('Cadastro de Tipo de Descarte');
        


        // create the form fields
        $id = new TEntry('id');
        $grupo = new TEntry('grupo');
        $descricao = new TEntry('descricao');


        // add the fields
        $this->form->addQuickField('Id', $id,  75 );
        $this->form->addQuickField('Grupo', $grupo,  50 , new TRequiredValidator);
        $this->form->addQuickField('Descricao', $descricao,  200 , new TRequiredValidator);



        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
                 
        // create the form actions
        $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'ico_save.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onEdit')), 'ico_new.png');
        $this->form->addQuickAction('Voltar para Listagem', new TAction(array('TipoDescarteList', 'onReload')), 'ico_datagrid.png');
        
        // vertical box container
        $container = new TVBox;
        //$container->style = 'width: 40%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
}
