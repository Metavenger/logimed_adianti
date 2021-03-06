<?php
/**
 * TipoDescarteList Listing
 * @author  <your name here>
 */
class TipoDescarteList extends TStandardList
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
        parent::setActiveRecord('TipoDescarte');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order

        parent::addFilterField('grupo', 'like', 'grupo'); // filterField, operator, formField
        parent::addFilterField('descricao', 'like', 'descricao'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_TipoDescarte');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->setFormTitle('Tipo de Descarte');
        

        // create the form fields
        $grupo = new TEntry('grupo');
        $descricao = new TEntry('descricao');


        // add the fields
        $this->form->addQuickField('Grupo', $grupo,  50 );
        $this->form->addQuickField('Descricao', $descricao,  200 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('TipoDescarte_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('TipoDescarteForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        //$this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_grupo = new TDataGridColumn('grupo', 'Grupo', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_grupo);
        $this->datagrid->addColumn($column_descricao);


        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_grupo = new TAction(array($this, 'onReload'));
        $order_grupo->setParameter('order', 'grupo');
        $column_grupo->setAction($order_grupo);
        
        $order_descricao = new TAction(array($this, 'onReload'));
        $order_descricao->setParameter('order', 'descricao');
        $column_descricao->setAction($order_descricao);
        

        
        // create EDIT action
        $action_edit = new TDataGridAction(array('TipoDescarteForm', 'onEdit'));
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
        //$container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
    

}
