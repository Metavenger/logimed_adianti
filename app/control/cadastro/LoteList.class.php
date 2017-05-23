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
        // parent::setCriteria($criteria) // define a standard filter

        //parent::addFilterField('numero', 'like', 'numero'); // filterField, operator, formField
        //parent::addFilterField('peso', 'like', 'peso'); // filterField, operator, formField
        //parent::addFilterField('valor', 'like', 'valor'); // filterField, operator, formField
        //parent::addFilterField('temperatura', 'like', 'temperatura'); // filterField, operator, formField
        //parent::addFilterField('tipodescarte_id', '=', 'tipodescarte_id'); // filterField, operator, formField
        //parent::addFilterField('flag_esgotado', '=', 'flag_esgotado'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_Lote');
        $this->form->class = 'tform'; // change CSS class
        
        //$this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Lote');
        

        // create the form fields
        $numero = new TEntry('numero');
        $peso = new TEntry('peso');
        $valor = new TEntry('valor');
        $temperatura = new TEntry('temperatura');
        //$tipodescarte_id = new TEntry('tipodescarte_id');
        $tipodescarte_id = new TDBCombo('tipodescarte_id', 'logimed', 'TipoDescarte', 'id', 'descricao', 'grupo');
        $tipodescarte_id->setValue(TSession::getValue('Lote_tipodescarte_id'));
        //$flag_esgotado = new TEntry('flag_esgotado');
        $flag_esgotado = new TCombo('flag_esgotado');
        //$flag_esgotado->setValue(TSession::getValue('flag_esgotado'));
        $flag_esgotado->addItems(array('S'=>'Sim','N'=>'Não'));


        // add the fields
        $this->form->addQuickField('Numero', $numero,  150 );
        $this->form->addQuickField('Peso', $peso,  85 );
        $this->form->addQuickField('Valor', $valor,  85 );
        $this->form->addQuickField('Temperatura', $temperatura,  85 );
        $this->form->addQuickField('Tipo de Descarte', $tipodescarte_id,  230 );
        $this->form->addQuickField('Esgotado?', $flag_esgotado,  85 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Lote_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('LoteForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        //$this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_numero = new TDataGridColumn('numero', 'Numero', 'center');
        $column_peso = new TDataGridColumn('peso', 'Peso', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_temperatura = new TDataGridColumn('temperatura', 'Temperatura', 'right');
        $column_tipodescarte_id = new TDataGridColumn('tipodescarte_id', 'Tipo de Descarte', 'left');
        $column_flag_esgotado = new TDataGridColumn('flag_esgotado', 'Esgotado?', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_numero);
        $this->datagrid->addColumn($column_peso);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_temperatura);
        $this->datagrid->addColumn($column_tipodescarte_id);
        $this->datagrid->addColumn($column_flag_esgotado);


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
        
        $order_flag_esgotado = new TAction(array($this, 'onReload'));
        $order_flag_esgotado->setParameter('order', 'flag_esgotado');
        $column_flag_esgotado->setAction($order_flag_esgotado);
        

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
        //$action_edit->setUseButton(TRUE);
        $action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('ico_edit.png');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        $action_del->setButtonClass('btn btn-default');
        //$action_del->setLabel(_t('Delete'));
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
            if (TSession::getValue('Lote_filter_flag_esgotado'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Lote_filter_flag_esgotado'));
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
                    if($object->flag_esgotado == 'S')
                    {
                        $object->flag_esgotado = 'Sim';
                    }
                    else if($object->flag_esgotado == 'N')
                    {
                        $object->flag_esgotado = 'Não';
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
        TSession::setValue('Lote_filter_flag_esgotado', NULL);
        TSession::setValue('Lote_flag_esgotado', '');
        
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
        if($data->flag_esgotado)
        {
            $filter = new TFilter('flag_esgotado', '=', $data->flag_esgotado);
            
            TSession::setValue('Lote_filter_flag_esgotado',   $filter);
            TSession::setValue('Lote_flag_esgotado', $data->flag_esgotado);
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
}
