<?php
/**
 * EntregaList Listing
 * @author  <your name here>
 */
class EntregaList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('logimed');            // defines the database
        parent::setActiveRecord('Entrega');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order

        parent::addFilterField('transportadora_id', '=', 'transportadora_id'); // filterField, operator, formField
        parent::addFilterField('dt_confirmacao', '=', 'dt_confirmacao'); // filterField, operator, formField
        parent::addFilterField('fl_ativo', '=', 'fl_ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_Entrega');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Entrega');
        

        // create the form fields
        $transportadora_id = new TSeekButton('transportadora_id');
        $transportadora_nome  = new TEntry('transportadora_nome');
        $dt_confirmacao = new TDate('dt_confirmacao');
        $dt_confirmacao->setMask('dd/mm/yyyy');
        $fl_ativo = new TCombo('fl_ativo');
        $fl_ativo->setValue(TSession::getValue('fl_ativo'));
        $fl_ativo->addItems(array('S'=>'Sim','N'=>'Não'));
                

        $action1 = new TAction(array(new TransportadoraSeek, 'onSetup'));
        $action1->setParameter('database',      'logimed');
        TSession::setValue('transportadoraseek_database','logimed');
        $action1->setParameter('parent',        $this->form->getName());
        TSession::setValue('transportadoraseek_parent',$this->form->getName());
        $action1->setParameter('model',         'Transportadora');
        TSession::setValue('transportadoraseek_model','Transportadora');
        $action1->setParameter('display_field', 'transportadora_nome');
        TSession::setValue('transportadoraseek_display_field','transportadora_nome');
        $action1->setParameter('receive_key',   'transportadora_id');
        TSession::setValue('transportadoraseek_receive_key','transportadora_id');
        $action1->setParameter('receive_field', 'transportadora_nome');
        TSession::setValue('transportadoraseek_receive_field','transportadora_nome');
        
        $transportadora_id->setAction($action1);
        
        $transportadora_id->setSize(80);
        $transportadora_nome->setSize(100);
        $transportadora_nome->setEditable(false);

        // add the fields
        $this->form->addQuickFields('Transportadora', array($transportadora_id, $transportadora_nome),  200 );
        $this->form->addQuickField('Data de Confirmacao', $dt_confirmacao,  200 );
        $this->form->addQuickField('Ativa?', $fl_ativo,  200 );       

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Entrega_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('EntregaForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_transportadora_id = new TDataGridColumn('transportadora->nome', 'Transportadora', 'right');
        $column_dt_confirmacao = new TDataGridColumn('dt_confirmacao', 'Data de Confirmacao', 'center');
        $column_fl_ativo = new TDataGridColumn('fl_ativo', 'Ativa?', 'center');

        $formata_flag = function($value) 
        {
            if ($value == 'S') {
                return 'Sim';
            }
            else if($value == 'N')
            {
                return 'Não';
            }
            return $value;
        };
        
        $column_fl_ativo->setTransformer( $formata_flag );

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_transportadora_id);
        $this->datagrid->addColumn($column_dt_confirmacao);
        $this->datagrid->addColumn($column_fl_ativo);        


        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        

        
        // create EDIT action
        $action_edit = new TDataGridAction(array('EntregaForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('ico_edit.png');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('ico_delete.png');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 40%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
    

}
