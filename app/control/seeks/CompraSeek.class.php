<?php
/**
 * CompraSeek Listing
 * @author  <your name here>
 */
class CompraSeek extends TWindow
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
        $this->setActiveRecord('Compra');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order

        $this->addFilterField('id', 'like', 'id'); // filterField, operator, formField
        $this->addFilterField('nome_cliente', 'like', 'nome_cliente'); // filterField, operator, formField
        $this->addFilterField('endereco', 'like', 'endereco');
        
        // creates the form
        $this->form = new TQuickForm('form_search_Compra');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Compra');
        

        // create the form fields
        $id = new TEntry('id');
        $nome_cliente = new TEntry('nome_cliente');
        $endereco = new TEntry('endereco');


        // add the fields
        $this->form->addQuickField('Id', $id,  200 );
        $this->form->addQuickField('Nome do Cliente', $nome_cliente,  200 );
        $this->form->addQuickField('Endereço', $endereco, 200);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Compra_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome_cliente = new TDataGridColumn('nome_cliente', 'Cliente', 'left');
        $column_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');
        $column_lote_id = new TDataGridColumn('lote->nome', 'Produto', 'right');
        $column_unidades = new TDataGridColumn('unidades', 'Unidades', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_fl_ativo = new TDataGridColumn('fl_ativo', 'Ativa?', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome_cliente);
        $this->datagrid->addColumn($column_endereco);
        $this->datagrid->addColumn($column_lote_id);
        $this->datagrid->addColumn($column_unidades);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_fl_ativo);
        
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
    
    /**
     * Executed when the user chooses the record
     */
    public function onSelect($param)
    {
        $key = $param['key'];
        $database      = isset($param['database'])      ? $param['database'] : TSession::getValue('compraseek_database');
        $receive_key   = isset($param['receive_key'])   ? $param['receive_key']   : TSession::getValue('compraseek_receive_key');
        $receive_field = isset($param['receive_field']) ? $param['receive_field'] : TSession::getValue('compraseek_receive_field');
        $display_field = isset($param['display_field']) ? $param['display_field'] : TSession::getValue('compraseek_display_field');
        $parent        = isset($param['parent'])        ? $param['parent']        : TSession::getValue('compraseek_parent');
        
        if(isset($param['key']) && $param['key'] )
        {
            try
            {
                TTransaction::open($database);
                // load the active record
                $compra = new Compra($key);
                if( $compra )
                {
                    $object = new StdClass;
                    
                    $object->compra_id = $compra->id;
                    $object->compra_nome = $compra->lote->nome;
                    $object->compra_cliente = $compra->nome_cliente;
                    $object->compra_endereco = $compra->endereco;
                    $object->compra_valor = $compra->valor;
                    
                    TTransaction::close();
                
                    TForm::sendData('form_Entrega', $object);
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
        
    }
    
    public function onSetup($param=NULL)
    {
        // store the parameters in the section
        TSession::setValue('compraseek_filter_id', NULL);
        TSession::setValue('compraseek_filter_nome_cliente', NULL);
        TSession::setValue('compraseek_filter_endereco', NULL);
        TSession::setValue('compraseek_id', NULL);
        TSession::setValue('compraseek_nome_cliente', NULL);
        TSession::setValue('compraseek_endereco', NULL);
        TSession::setValue('compraseek_display_value', NULL);
        TSession::setValue('compraseek_receive_key',   $param['receive_key']);
        TSession::setValue('compraseek_receive_field', $param['receive_field']);
        TSession::setValue('compraseek_display_field', $param['display_field']);
        TSession::setValue('compraseek_model',         $param['model']);
        TSession::setValue('compraseek_database',      $param['database']);
        TSession::setValue('compraseek_parent',        $param['parent']);
        
        $this->form->clear();
                
        $this->onReload();
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            $model    = TSession::getValue('compraseek_model');
            $database = TSession::getValue('compraseek_database');
            
            // begins the transaction with database
            TTransaction::open($database);
            
            $repos = new TRepository('Compra');
            
            // creates a repository for the model
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('compraseek_filter_id'))          
                $criteria->add(TSession::getValue('compraseek_filter_id'));
            
            if (TSession::getValue('compraseek_filter_nome_cliente'))          
                $criteria->add(TSession::getValue('compraseek_filter_nome_cliente'));
                
            if (TSession::getValue('compraseek_filter_endereco'))          
                $criteria->add(TSession::getValue('compraseek_filter_endereco'));
            
            $criteria->add(new TFilter('id','not in',"(SELECT compra_id FROM entrega_compra)"));
            // load all objects according with the criteria
            $objects = $repos->load($criteria);
            
            $this->datagrid->clear();
            
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }
            
            // clear the crieteria to count the records
            $criteria->resetProperties();
            
            $count = $repos->count($criteria);
                      
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // closes the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception genearated message
            new TMessage('error', '<b>Erro</b> ' . $e->getMessage());
            // rollback all the database operations 
            TTransaction::rollback();
        }
    }
}
