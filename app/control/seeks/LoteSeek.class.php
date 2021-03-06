<?php
/**
 * LoteSeek Listing
 * @author  <your name here>
 */
class LoteSeek extends TWindow
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
        $this->setActiveRecord('Lote');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order

        $this->addFilterField('numero', '=', 'numero'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        
        // creates the form
        $this->form = new TQuickForm('form_search_Lote');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Lote');
        

        // create the form fields
        $numero = new TEntry('numero');
        $nome = new TEntry('nome');


        // add the fields
        $this->form->addQuickField('Numero', $numero,  200 );
        $this->form->addQuickField('Nome', $nome,  200 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Lote_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_numero = new TDataGridColumn('numero', 'Numero', 'right');
        $column_estoque_atual = new TDataGridColumn('estoque_atual', 'Estoque Atual', 'right');
        $column_total_estoque = new TDataGridColumn('total_estoque', 'Total Estoque', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_fl_descarte = new TDataGridColumn('fl_descarte', 'Descarte?', 'center');
        $column_dt_descarte = new TDataGridColumn('dt_descarte', 'Data de Descarte', 'center');
        
        $column_fl_descarte->setTransformer(function($value, $object, $row) {
            if($value == 'S')
            {
                return 'Sim';
            }
            else if($value == 'N')
            {
                return 'Não';
            }
        });
        
        $column_dt_descarte->setTransformer(function($value, $object, $row) {
            return TDate::date2br($value);
        });


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_numero);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_estoque_atual);
        $this->datagrid->addColumn($column_total_estoque);
        $this->datagrid->addColumn($column_fl_descarte);
        $this->datagrid->addColumn($column_dt_descarte);
                
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
        $database      = isset($param['database'])      ? $param['database'] : TSession::getValue('loteseek_database');
        $receive_key   = isset($param['receive_key'])   ? $param['receive_key']   : TSession::getValue('loteseek_receive_key');
        $receive_field = isset($param['receive_field']) ? $param['receive_field'] : TSession::getValue('loteseek_receive_field');
        $display_field = isset($param['display_field']) ? $param['display_field'] : TSession::getValue('loteseek_display_field');
        $parent        = isset($param['parent'])        ? $param['parent']        : TSession::getValue('loteseek_parent');
        
        try
        {
            TTransaction::open($database);
            // load the active record

            // onblur
            if(isset($param['static']) && $param['static'] )
                $lote = Lote::newFromNumero($key);
            else
                $lote = new Lote($key);

            if( $lote )
            {
                $object = new StdClass;
                $object->$receive_key   = $lote->numero;
            
                if( $display_field == 'nome_produto' )
                {
                    $produto = $lote->nome; 
                    $object->$receive_field = $produto;
                }
                else
                    $object->$receive_field = $lote->$display_field;
                
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
        TSession::setValue('loteseek_filter_numero', NULL);
        TSession::setValue('loteseek_filter_nome', NULL);
        TSession::setValue('loteseek_numero', NULL);
        TSession::setValue('loteseek_nome', NULL);
        TSession::setValue('loteseek_display_value', NULL);
        TSession::setValue('loteseek_receive_key',   $param['receive_key']);
        TSession::setValue('loteseek_receive_field', $param['receive_field']);
        TSession::setValue('loteseek_display_field', $param['display_field']);
        TSession::setValue('loteseek_model',         $param['model']);
        TSession::setValue('loteseek_database',      $param['database']);
        TSession::setValue('loteseek_parent',        $param['parent']);
        
        $this->form->clear();
                
        $this->onReload();
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            $model    = TSession::getValue('loteseek_model');
            $database = TSession::getValue('loteseek_database');
            
            // begins the transaction with database
            TTransaction::open($database);
            
            $repos = new TRepository('Lote');
            
            // creates a repository for the model
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('loteseek_filter_numero'))          
                $criteria->add(TSession::getValue('loteseek_filter_numero'));
            
            if (TSession::getValue('loteseek_filter_nome'))          
                $criteria->add(TSession::getValue('loteseek_filter_nome'));
            
            $criteria->add(new TFilter('estoque_atual','>',0));
            
            //$criteria->add(new TFilter('fl_descarte', '!=', 'S'));
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
