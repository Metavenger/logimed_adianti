<?php
/**
 * LoteList Listing
 * @author  <your name here>
 */
class LoteList extends TStandardList
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
        parent::setActiveRecord('Lote');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        
        // creates the form
        $this->form = new TQuickForm('form_search_Lote');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->setFormTitle('Lote');
        

        // create the form fields
        $numero = new TEntry('numero');
        $peso = new TEntry('peso');
        $valor = new TEntry('valor');
        $temperatura = new TEntry('temperatura');
        $tipodescarte_id = new TDBCombo('tipodescarte_id', 'logimed', 'TipoDescarte', 'id', 'descricao', 'grupo');
        $tipodescarte_id->setValue(TSession::getValue('Lote_tipodescarte_id'));
        $fl_descarte = new TCombo('fl_descarte');
        $fl_descarte->addItems(array('S'=>'Sim','N'=>'Não'));
        $dt_descarte = new TDate('dt_descarte');
        $dt_descarte->setMask('dd/mm/yyyy');


        // add the fields
        $this->form->addQuickField('Numero', $numero,  150 );
        $this->form->addQuickField('Peso', $peso,  85 );
        $this->form->addQuickField('Valor', $valor,  85 );
        $this->form->addQuickField('Temperatura', $temperatura,  85 );
        $this->form->addQuickField('Tipo de Descarte', $tipodescarte_id,  230 );
        $this->form->addQuickField('Descarte?', $fl_descarte,  85 );
        $this->form->addQuickField('Data de Descarte', $dt_descarte, 100);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Lote_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('LoteForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        //$this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_numero = new TDataGridColumn('numero', 'Numero', 'center');
        $column_peso = new TDataGridColumn('peso', 'Peso', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_temperatura = new TDataGridColumn('temperatura', 'Temperatura', 'right');
        $column_tipodescarte_id = new TDataGridColumn('tipodescarte_id', 'Tipo de Descarte', 'left');
        $column_fl_descarte = new TDataGridColumn('fl_descarte', 'Descarte?', 'center');
        $column_estoque_atual = new TDataGridColumn('estoque_atual', 'Estoque Atual', 'center');
        $column_total_estoque = new TDataGridColumn('total_estoque', 'Total em Estoque', 'center');
        $column_dt_descarte = new TDataGridColumn('dt_descarte', 'Data de Descarte', 'center');
        $column_dt_descarte->setTransformer(function($value, $object, $row) {
            if($value)
            {
                return TDate::date2br($value);
            }
        });


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_numero);
        $this->datagrid->addColumn($column_peso);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_temperatura);
        $this->datagrid->addColumn($column_tipodescarte_id);
        $this->datagrid->addColumn($column_fl_descarte);
        $this->datagrid->addColumn($column_estoque_atual);
        $this->datagrid->addColumn($column_total_estoque);
        $this->datagrid->addColumn($column_dt_descarte);
        


        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_numero = new TAction(array($this, 'onReload'));
        $order_numero->setParameter('order', 'numero');
        $column_numero->setAction($order_numero);
        
        $order_peso = new TAction(array($this, 'onReload'));
        $order_peso->setParameter('order', 'peso');
        $column_peso->setAction($order_peso);
        
        $order_valor = new TAction(array($this, 'onReload'));
        $order_valor->setParameter('order', 'valor');
        $column_valor->setAction($order_valor);
        
        $order_temperatura = new TAction(array($this, 'onReload'));
        $order_temperatura->setParameter('order', 'temperatura');
        $column_temperatura->setAction($order_temperatura);
        
        $order_tipodescarte_id = new TAction(array($this, 'onReload'));
        $order_tipodescarte_id->setParameter('order', 'tipodescarte_id');
        $column_tipodescarte_id->setAction($order_tipodescarte_id);
        
        $order_estoque_atual = new TAction(array($this, 'onReload'));
        $order_estoque_atual->setParameter('order', 'estoque_atual');
        $column_estoque_atual->setAction($order_estoque_atual);
        
        $order_total_estoque = new TAction(array($this, 'onReload'));
        $order_total_estoque->setParameter('order', 'total_estoque');
        $column_total_estoque->setAction($order_total_estoque);
        
        $order_dt_descarte = new TAction(array($this, 'onReload'));
        $order_dt_descarte->setParameter('order', 'dt_descarte');
        $column_dt_descarte->setAction($order_dt_descarte);
        

        // define the transformer method over image
        $column_valor->setTransformer( function($value, $object, $row) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });
        $column_peso->setTransformer( function($value, $object, $row)
        {
            return number_format($value, 1, ',', '.') . 'kg';
        });
        $column_temperatura->setTransformer( function($value, $object, $row)
        {
            return number_format($value, 1, ',', '.') . 'ºC';
        });


        
        // create EDIT action
        $action_edit = new TDataGridAction(array('LoteForm', 'onEdit'));
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
    
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'tab'
            TTransaction::open('logimed');
            
            // creates a repository for Busca
            $repository = new TRepository('Lote');
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Lote_filter_numero'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_numero'));
            }
            if (TSession::getValue('Lote_filter_peso'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_peso'));
            }
            if (TSession::getValue('Lote_filter_valor'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_valor'));
            }
            if (TSession::getValue('Lote_filter_temperatura'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_temperatura'));
            }
            if (TSession::getValue('Lote_filter_tipodescarte_id'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_tipodescarte_id'));
            }
            if (TSession::getValue('Lote_filter_fl_descarte'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_fl_descarte'));
            }
            if (TSession::getValue('Lote_filter_dt_descarte'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_dt_descarte'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $tipo_descarte = new TipoDescarte($object->tipodescarte_id);
                    $object->tipodescarte_id = $tipo_descarte-> grupo . ' - ' . $tipo_descarte->descricao;
                    if($object->fl_descarte == 'S')
                    {
                        $object->fl_descarte = 'Sim';
                    }
                    else if($object->fl_descarte == 'N')
                    {
                        $object->fl_descarte = 'Não';
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
        
        TSession::setValue('Lote_filter_numero',   NULL);
        TSession::setValue('Lote_numero', '');
        TSession::setValue('Lote_filter_peso',   NULL);
        TSession::setValue('Lote_peso', '');
        TSession::setValue('Lote_filter_valor',   NULL);
        TSession::setValue('Lote_valor', '');
        TSession::setValue('Lote_filter_temperatura',   NULL);
        TSession::setValue('Lote_temperatura', '');
        TSession::setValue('Lote_filter_tipodescarte_id',   NULL);
        TSession::setValue('Lote_tipodescarte_id', '');
        TSession::setValue('Lote_filter_fl_descarte', NULL);
        TSession::setValue('Lote_fl_descarte', '');
        TSession::setValue('Lote_filter_dt_descarte', NULL);
        TSession::setValue('Lote_dt_descarte', '');
        
        // check if the user has filled the form
        if ($data->numero)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('numero', '=', "%{$data->numero}%");
            
            // stores the filter in the session
            TSession::setValue('Lote_filter_numero',   $filter);
            TSession::setValue('Lote_numero', $data->numero);
        }
        if ($data->peso)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('peso', '=', "%{$data->peso}%");
            
            // stores the filter in the session
            TSession::setValue('Lote_filter_peso',   $filter);
            TSession::setValue('Lote_peso', $data->peso);
        }
        if ($data->valor)
        {
            $filter = new TFilter('valor', '=', $data->valor);
            
            TSession::setValue('Lote_filter_valor',   $filter);
            TSession::setValue('Lote_valor', $data->valor);
        }
        if ($data->temperatura)
        {
            $filter = new TFilter('temperatura', '=', $data->temperatura);
            
            TSession::setValue('Lote_filter_temperatura',   $filter);
            TSession::setValue('Lote_temperatura', $data->temperatura);
        }
        if( $data->tipodescarte_id )
        {
            $filter = new TFilter('tipodescarte_id','=',$data->tipodescarte_id);
            
            TSession::setValue('Lote_filter_tipodescarte_id',$filter);
            TSession::setValue('Lote_tipodescarte_id',$data->tipodescarte_id);
        }
        if($data->fl_descarte)
        {
            $filter = new TFilter('fl_descarte', '=', $data->fl_descarte);
            
            TSession::setValue('Lote_filter_fl_descarte',   $filter);
            TSession::setValue('Lote_fl_descarte', $data->fl_descarte);
        }
        if($data->dt_descarte)
        {
            $filter = new TFilter('dt_descarte', '=', TDate::date2us($data->dt_descarte));
            
            TSession::setValue('Lote_filter_dt_descarte',   $filter);
            TSession::setValue('Lote_dt_descarte', $data->dt_descarte);
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
}
