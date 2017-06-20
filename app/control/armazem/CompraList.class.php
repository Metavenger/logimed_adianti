<?php
/**
 * CompraList Listing
 * @author  <your name here>
 */
class CompraList extends TStandardList
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
        parent::setActiveRecord('Compra');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        
        // creates the form
        $this->form = new TQuickForm('form_search_Compra');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Compra');
        

        // create the form fields
        $lote_numero = new TEntry('lote_numero');
        $fl_ativo = new TCombo('fl_ativo');
        $fl_ativo->addItems(array('S'=>'Sim','N'=>'Não'));
        $nome_cliente = new TEntry('nome_cliente');
        $endereco = new TEntry('endereco');


        // add the fields
        $this->form->addQuickField('Nº Lote', $lote_numero,  200 );
        $this->form->addQuickField('Ativa?', $fl_ativo,  200 );
        $this->form->addQuickField('Nome do Cliente', $nome_cliente,  200 );
        $this->form->addQuickField('Endereço', $endereco, 200);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Compra_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('CompraForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        //$this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome_cliente = new TDataGridColumn('nome_cliente', 'Nome do Cliente', 'left');
        $column_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');
        $column_lote_numero = new TDataGridColumn('lote_numero', 'Nº Lote', 'right');
        $column_lote_nome = new TDataGridColumn('lote_nome', 'Nome do Produto', 'center');
        $column_unidades = new TDataGridColumn('unidades', 'Unidades', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_fl_ativo = new TDataGridColumn('fl_ativo', 'Ativa?', 'center');
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome_cliente);
        $this->datagrid->addColumn($column_endereco);
        $this->datagrid->addColumn($column_lote_numero);
        $this->datagrid->addColumn($column_lote_nome);
        $this->datagrid->addColumn($column_unidades);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_fl_ativo);
       

        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_lote_numero = new TAction(array($this, 'onReload'));
        $order_lote_numero->setParameter('order', 'lote_numero');
        $column_lote_numero->setAction($order_lote_numero);
        
        $order_fl_ativo = new TAction(array($this, 'onReload'));
        $order_fl_ativo->setParameter('order', 'fl_ativo');
        $column_fl_ativo->setAction($order_fl_ativo);
        
        $order_nome_cliente = new TAction(array($this, 'onReload'));
        $order_nome_cliente->setParameter('order', 'nome_cliente');
        $column_nome_cliente->setAction($order_nome_cliente);
        
        // define the transformer method over image
        $column_valor->setTransformer( function($value, $object, $row) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('CompraForm', 'onEdit'));
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setImage('ico_edit.png');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setLabel(_t('Delete'));
        $action_del->setButtonClass('btn btn-default');
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
    
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'tab'
            TTransaction::open('logimed');
            
            // creates a repository for Busca
            $repository = new TRepository('Compra');
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Compra_filter_lote_numero'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Compra_filter_lote_numero'));
            }
            if (TSession::getValue('Compra_filter_fl_ativo'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Compra_filter_fl_ativo'));
            }
            if (TSession::getValue('Compra_filter_nome_cliente'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Compra_filter_nome_cliente'));
            }
                        
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $lote = Lote::newFromNumero($object->lote_numero);
                    $lote = new Lote($object->lote_id);
                    $object->lote_numero = $lote->numero;
                    $object->lote_nome = $lote->nome;
                    if($object->fl_ativo == 'S')
                    {
                        $object->fl_ativo = 'Sim';
                    }
                    else if($object->fl_ativo == 'N')
                    {
                        $object->fl_ativo = 'Não';
                    }
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        TSession::setValue('Compra_filter_lote_numero',   NULL);
        TSession::setValue('Compra_lote_numero', '');
        TSession::setValue('Compra_filter_fl_ativo',   NULL);
        TSession::setValue('Compra_fl_ativo', '');
        TSession::setValue('Compra_filter_nome_cliente',   NULL);
        TSession::setValue('Compra_nome_cliente', '');
        TSession::setValue('Compra_filter_endereco',   NULL);
        TSession::setValue('Compra_endereco', '');
        
        // check if the user has filled the form
        if ($data->lote_numero)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('lote_numero', '=', $data->lote_numero);
            
            // stores the filter in the session
            TSession::setValue('Compra_filter_lote_numero',   $filter);
            TSession::setValue('Compra_lote_numero', $data->lote_numero);
        }
        if($data->fl_ativo)
        {
            $filter = new TFilter('fl_ativo', '=', $data->fl_ativo);
            
            TSession::setValue('Compra_filter_fl_ativo',   $filter);
            TSession::setValue('Compra_fl_ativo', $data->fl_ativo);
        }
        if ($data->nome_cliente)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome_cliente', '=', "%{$data->nome_cliente}%");
            
            // stores the filter in the session
            TSession::setValue('Compra_filter_nome_cliente',   $filter);
            TSession::setValue('Compra_nome_cliente', $data->nome_cliente);
        }
        if ($data->endereco)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('endereco', '=', "%{$data->endereco}%");
            
            // stores the filter in the session
            TSession::setValue('Compra_filter_endereco',   $filter);
            TSession::setValue('Compra_endereco', $data->endereco);
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }

}
