<?php
/**
 * TransportadoraList Listing
 * @author  <your name here>
 */
class TransportadoraList extends TStandardList
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
        parent::setActiveRecord('Transportadora');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        
        // creates the form
        $this->form = new TQuickForm('form_search_Transportadora');
        $this->form->class = 'tform'; // change CSS class
        
        $this->form->setFormTitle('Transportadora');
        

        // create the form fields
        $nome = new TEntry('nome');
        $preco = new TEntry('preco');
        $avaliacao = new TEntry('avaliacao');
        $zonaatendimento_id = new TDBCombo('zonaatendimento_id', 'logimed', 'ZonaAtendimento', 'id', 'descricao', 'descricao');
        $zonaatendimento_id->setValue(TSession::getValue('Transportadora_zonaatendimento_id'));
        $habilitado = new TCombo('habilitado');
        $habilitado->addItems(array('S'=>'Sim','N'=>'Não'));


        // add the fields
        $this->form->addQuickField('Nome da Transportadora', $nome,  300 );
        $this->form->addQuickField('Preco', $preco,  85 );
        $this->form->addQuickField('Avaliacao', $avaliacao,  85 );
        $this->form->addQuickField('Zona de Atendimento', $zonaatendimento_id,  150 );
        $this->form->addQuickField('Esgotado?', $habilitado,  85 );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Transportadora_filter_data') );
        
        // add the search form actions
        $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'ico_find.png');
        $this->form->addQuickAction(_t('New'),  new TAction(array('TransportadoraForm', 'onEdit')), 'ico_new.png');
        
        // creates a DataGrid
        $this->datagrid = new TDataGrid;
        
        //$this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome da Transportadora', 'center');
        $column_preco = new TDataGridColumn('preco', 'Preco', 'right');
        $column_avaliacao = new TDataGridColumn('avaliacao', 'Avaliacao', 'center');
        $column_zonaatendimento_id = new TDataGridColumn('zonaatendimento_id', 'Zona de Atendimento', 'left');
        $column_habilitado = new TDataGridColumn('habilitado', 'Habilitado?', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_preco);
        $this->datagrid->addColumn($column_avaliacao);
        $this->datagrid->addColumn($column_zonaatendimento_id);
        $this->datagrid->addColumn($column_habilitado);


        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_nome = new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $column_nome->setAction($order_nome);
        
        $order_preco = new TAction(array($this, 'onReload'));
        $order_preco->setParameter('order', 'preco');
        $column_preco->setAction($order_preco);
        
        $order_avaliacao = new TAction(array($this, 'onReload'));
        $order_avaliacao->setParameter('order', 'avaliacao');
        $column_avaliacao->setAction($order_avaliacao);
        
        $order_zonaatendimento_id = new TAction(array($this, 'onReload'));
        $order_zonaatendimento_id->setParameter('order', 'zonaatendimento_id');
        $column_zonaatendimento_id->setAction($order_zonaatendimento_id);
        
        $order_habilitado = new TAction(array($this, 'onReload'));
        $order_habilitado->setParameter('order', 'habilitado');
        $column_habilitado->setAction($order_habilitado);
        

        // define the transformer method over image
        $column_preco->setTransformer( function($value, $object, $row) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });


        
        // create EDIT action
        $action_edit = new TDataGridAction(array('TransportadoraForm', 'onEdit'));
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
            $repository = new TRepository('Transportadora');
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            if (TSession::getValue('Transportadora_filter_nome'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Transportadora_filter_nome'));
            }
            if (TSession::getValue('Transportadora_filter_preco'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Transportadora_filter_preco'));
            }
            if (TSession::getValue('Transportadora_filter_avaliacao'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Transportadora_filter_avaliacao'));
            }
            if (TSession::getValue('Transportadora_filter_zonaatendimento_id'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Transportadora_filter_zonaatendimento_id'));
            }
            if (TSession::getValue('Transportadora_filter_habilitado'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('Transportadora_filter_habilitado'));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $tipo_descarte = new ZonaAtendimento($object->zonaatendimento_id);
                    $object->zonaatendimento_id = $tipo_descarte->descricao;
                    if($object->habilitado == 'S')
                    {
                        $object->habilitado = 'Sim';
                    }
                    else if($object->habilitado == 'N')
                    {
                        $object->habilitado = 'Não';
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
        
        TSession::setValue('Transportadora_filter_nome',   NULL);
        TSession::setValue('Transportadora_nome', '');
        TSession::setValue('Transportadora_filter_preco',   NULL);
        TSession::setValue('Transportadora_preco', '');
        TSession::setValue('Transportadora_filter_avaliacao',   NULL);
        TSession::setValue('Transportadora_avaliacao', '');
        TSession::setValue('Transportadora_filter_zonaatendimento_id',   NULL);
        TSession::setValue('Transportadora_zonaatendimento_id', '');
        TSession::setValue('Transportadora_filter_habilitado', NULL);
        TSession::setValue('Transportadora_habilitado', '');
        
        // check if the user has filled the form
        if ($data->nome)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', '=', "%{$data->nome}%");
            
            // stores the filter in the session
            TSession::setValue('Transportadora_filter_nome',   $filter);
            TSession::setValue('Transportadora_nome', $data->nome);
        }
        if ($data->preco)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('preco', '=', $data->preco);
            
            // stores the filter in the session
            TSession::setValue('Transportadora_filter_preco',   $filter);
            TSession::setValue('Transportadora_preco', $data->preco);
        }
        if ($data->avaliacao)
        {
            $filter = new TFilter('avaliacao', '=', $data->avaliacao);
            
            TSession::setValue('Transportadora_filter_avaliacao',   $filter);
            TSession::setValue('Transportadora_avaliacao', $data->avaliacao);
        }
        if( $data->zonaatendimento_id )
        {
            $filter = new TFilter('zonaatendimento_id','=',$data->zonaatendimento_id);
            
            TSession::setValue('Transportadora_filter_zonaatendimento_id',$filter);
            TSession::setValue('Transportadora_zonaatendimento_id',$data->zonaatendimento_id);
        }
        if($data->habilitado)
        {
            $filter = new TFilter('habilitado', '=', $data->habilitado);
            
            TSession::setValue('Transportadora_filter_habilitado',   $filter);
            TSession::setValue('Transportadora_habilitado', $data->habilitado);
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
}
