<?php
/**
 * TransportadoraSeek Listing
 * @author  <your name here>
 */
class TransportadoraSeek extends TWindow
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $filterFields;
    protected $formFilters;
    protected $filterTransformers;
    protected $loaded;
    protected $limit;
    protected $operators;
    protected $order;
    protected $direction;
    protected $criteria;
    protected $transformCallback;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTitle( AdiantiCoreTranslator::translate('Search record') );
        parent::setSize(0.7, 640);
        
        $this->setDatabase('logimed');            // defines the database
        $this->setActiveRecord('Transportadora');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('zonaatendimento_id', '=', 'zonatendimento_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_Lote');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Lote');
        

        // create the form fields
        $nome = new TEntry('nome');
        $zonaatendimento_id = new TDBCombo('zonaatendimento_id', 'logimed', 'ZonaAtendimento', 'id', 'descricao', 'descricao');


        // add the fields
        $this->form->addQuickField('Nome', $nome,  200 );
        $this->form->addQuickField('Zona de Atendimento', $zonaatendimento_id,  200 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Transportadora_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'right');
        $column_zonaatendimento_nome = new TDataGridColumn('zonaatendimento_nome', 'Zona de Atendimento', 'right');
        $column_avaliacao = new TDataGridColumn('avaliacao', 'Avaliacao', 'right');
        $column_preco = new TDataGridColumn('preco', 'Preco', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_zonaatendimento_nome);
        $this->datagrid->addColumn($column_avaliacao);
        $this->datagrid->addColumn($column_preco);

        
        // create EDIT action
        $action_select = new TDataGridAction(array($this, 'onSelect'));
        $action_select->setButtonClass('btn btn-default');
        $action_select->setLabel(AdiantiCoreTranslator::translate('Select'));
        $action_select->setImage('ico_apply.png');
        $action_select->setField('id');
        $this->datagrid->addAction($action_select);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
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
                        
            if( ! isset($param['order']) )
                $param['order'] = 'id';
            
            // creates a repository for System_group
            $repository = new TRepository('Transportadora');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('order', 'avaliacao desc, preco asc');
            
            if (TSession::getValue('nome_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('nome_filter'));
            }
            if (TSession::getValue('zonaatendimento_id_filter'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('zonaatendimento_id_filter'));
            }
            
            $criteria->add(new TFilter('habilitado', '!=', 'N'));
            
            // load the objects according to criteria
            $objects = $repository->load($criteria,false);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $zona = new ZonaAtendimento($object->zonaatendimento_id);
                    $object->zonaatendimento_nome = $zona->descricao;
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
        
        TSession::setValue('nome_filter',   NULL);
        TSession::setValue('zonaatendimento_id_filter', NULL);
        
        TSession::setValue('nome', '');
        TSession::setValue('zonaatendimento_id', '');
        
        // check if the user has filled the form
        if ( $data->nome )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            
            // stores the filter in the session
            TSession::setValue('nome_filter',   $filter);
            TSession::setValue('nome', $data->nome);
        }
        if ( $data->zonaatendimento_id )
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('zonaatendimento_id', '=', "$data->zonaatendimento_id");
            
            TSession::setValue('zonaatendimento_id_filter', $filter);
            TSession::setValue('zonaatendimento_id', $data->zonaatendimento_id);            
        }
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Executed when the user chooses the record
     */
    public function onSelect($param)
    {
        $key = $param['key'];
        $database      = isset($param['database'])      ? $param['database'] : TSession::getValue('transportadoraseek_database');
        $receive_key   = isset($param['receive_key'])   ? $param['receive_key']   : TSession::getValue('transportadoraseek_receive_key');
        $receive_field = isset($param['receive_field']) ? $param['receive_field'] : TSession::getValue('transportadoraseek_receive_field');
        $display_field = isset($param['display_field']) ? $param['display_field'] : TSession::getValue('transportadoraseek_display_field');
        $parent        = isset($param['parent'])        ? $param['parent']        : TSession::getValue('transportadoraseek_parent');
        
        try
        {
            TTransaction::open($database);
            // load the active record

            // onblur
            if(isset($param['static']) && $param['static'] )
                $transportadora = new Transportadora($key);
            else
                $transportadora = new Transportadora($key);

            if( $transportadora )
            {
                $object = new StdClass;
                $object->$receive_key   = $transportadora->id;
            
                if( $display_field == 'transportadora_nome' )
                {
                    $produto = $transportadora->nome; 
                    $object->$receive_field = $produto;
                }
                else
                    $object->$receive_field = $transportadora->$display_field;
                
                TTransaction::close();
            
                TForm::sendData($parent, $object);
                parent::closeWindow(); // closes the window
            }
            else
                throw new Exception();
        }
        catch (Exception $e) // in case of exception
        {
            // clear fields
            $object = new StdClass;
            $object->$receive_key   = '';
            $object->$receive_field = '';
            TForm::sendData($parent, $object);
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    public function onSetup($param=NULL)
    {
        // store the parameters in the section
        TSession::setValue('transportadoraseek_filter_numero', NULL);
        TSession::setValue('transportadoraseek_filter_nome', NULL);
        TSession::setValue('transportadoraseek_numero', NULL);
        TSession::setValue('transportadoraseek_nome', NULL);
        TSession::setValue('transportadoraseek_display_value', NULL);
        TSession::setValue('transportadoraseek_receive_key',   $param['receive_key']);
        TSession::setValue('transportadoraseek_receive_field', $param['receive_field']);
        TSession::setValue('transportadoraseek_display_field', $param['display_field']);
        TSession::setValue('transportadoraseek_model',         $param['model']);
        TSession::setValue('transportadoraseek_database',      $param['database']);
        TSession::setValue('transportadoraseek_parent',        $param['parent']);
        
        $this->form->clear();
                
        $this->onReload();
    }
}