<?php

class EntregaForm extends TPage
{
    protected $form; // form
    protected $formFields;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    function __construct()
    {
        parent::__construct();
        
        $this->form = new TQuickForm('form_Entrega');
        $this->form->class = 'tform'; // change CSS class
        $this->form->setFormTitle('Entrega');
        
        $table_master = new TPanelGroup();
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
        $this->form->add($table_master);
        $table_master->add($vbox);
        
        $frame_geral = new TFrame;
        $frame_geral->setLegend('Dados da Entrega');
        $table_geral = new TTable;
        
        
        $frame_compra = new TFrame;
        $frame_compra->setLegend('Compras');
        $table_compra = new TTable;
        
        $frame_geral->add($table_geral);
        $frame_compra->add($table_compra);
        
        $vbox->add( $frame_geral );
        $vbox->add( $frame_compra );
        
        //fields da entrega (dados)
        $id = new TEntry('id');
        $transportadora_id = new TSeekButton('transportadora_id');
        $transportadora_nome  = new TEntry('transportadora_nome');
        
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
        
        $dt_confirmacao = new TDate('dt_confirmacao');
        $dt_confirmacao->setMask('dd/mm/yyyy');
        $fl_ativo = new TCombo('fl_ativo');
        $fl_ativo->setValue(TSession::getValue('fl_ativo'));
        $fl_ativo->addItems(array('S'=>'Sim','N'=>'Não'));
        $cliente = new TEntry('cliente');
        //fim dos fields da entrega (dados)
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        $table_geral->addRowSet(new TLabel('Id'), $id);
        $table_geral->addRowSet(new TLabel('Transportadora'), $transportadora_id, $transportadora_nome);
        $table_geral->addRowSet(new TLabel('Data de Confirmacao'), $dt_confirmacao);
        $table_geral->addRowSet(new TLabel('Ativa?'), $fl_ativo);
        
        //fields de compras
        $compra_id = new TSeekButton('compra_id');
        $compra_nome  = new TEntry('compra_nome');
        $compra_cliente = new TEntry('compra_cliente');
        $compra_endereco = new TEntry('compra_endereco');
        $compra_valor  = new TEntry('compra_valor');
        $compra_id->setExitAction(new TAction(array($this,'onCompraChange')));
        
        $actionAR = new TAction(array(new CompraSeek, 'onSetup'));
        $actionAR->setParameter('database',      'logimed');
        TSession::setValue('compraseek_database','logimed');
        $actionAR->setParameter('parent',        $this->form->getName());
        TSession::setValue('compraseek_parent', $this->form->getName());
        $actionAR->setParameter('model',         'Compra');
        TSession::setValue('compraseek_model','Compra');
        $actionAR->setParameter('display_field', 'compra_nome');
        TSession::setValue('compraseek_display_field', 'compra_nome');
        $actionAR->setParameter('receive_key',   'compras_id');
        TSession::setValue('compraseek_receive_key','compras_id');
        $actionAR->setParameter('receive_field', 'compras_nome');
        TSession::setValue('compraseek_receive_field','compras_nome');
        
        $add_compra = new TButton('add_compra');
        $action_compra = new TAction(array($this, 'onCompraAdd'));
        $add_compra->setAction($action_compra, 'Registrar');
        $add_compra->setImage('ico_save.png');
        
        $table_compra->addRowSet(new TLabel('Compra'), $compra_id, $compra_nome);
        $table_compra->addRowSet(new TLabel('Cliente'), $compra_cliente);
        $table_compra->addRowSet(new TLabel('Endereço'), $compra_endereco);
        $table_compra->addRowSet(new TLabel('Valor'), $compra_valor);
        $table_compra->addRowSet($add_compra);
        
        $transportadora_id->setAction($action1);
        $transportadora_nome->setEditable(false);
        $compra_id->setAction($actionAR);
        $compra_nome->setEditable(false);
        $compra_cliente->setEditable(false);
        $compra_endereco->setEditable(false);
        $compra_valor->setEditable(false);
        
        //fim fields de compras
        
        //inicio datagrid de compras        
        $this->compras_list = new TQuickGrid;
        $this->compras_list->style = "margin-bottom: 10px";
        $this->compras_list->disableDefaultClick();
        $this->compras_list->addQuickColumn('', 'delete', 'left', 40);
        $this->compras_list->addQuickColumn('ID', 'compra_id', 'center', 40);
        $this->compras_list->addQuickColumn('Produto', 'compra_nome', 'left', 200);
        $this->compras_list->addQuickColumn('Cliente', 'compra_cliente', 'left', 200);
        $this->compras_list->addQuickColumn('Endereço', 'compra_endereco', 'left', 200);
        $vl = $this->compras_list->addQuickColumn('Valor', 'compra_valor', 'right', 95);
        $this->compras_list->createModel();
        
        $vbox->add( $this->compras_list );
        
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $vl->setTransformer($format_value);
        
        $vl->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });
        
        //fim do datagrid de compras
        
        $save_button = TButton::create('save', array($this, 'onSave'),  _t('Save'),  'ico_save.png');
        $new_button  = TButton::create('new',  array($this, 'onEdit'), _t('New'), 'ico_new.png');
        $back_button = TButton::create('back', array('EntregaList', 'onReload'), _t('Back'), 'ico_back.png');
        
        $this->formFields = array($id, $transportadora_id, $transportadora_nome, $cliente, $dt_confirmacao, $fl_ativo, $compra_id, $compra_nome, $compra_cliente, $compra_valor, $add_compra, $save_button, $new_button, $back_button);
        $this->form->setFields($this->formFields);
        
        $container = new TVBox;
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(THBox::pack($save_button, $new_button, $back_button));
        
        parent::add($container);
    }
    
    function onEdit($param)
    {
        try
        {           
            if(isset($param['key']))
            {
                TTransaction::open('logimed');
                
                $key = $param['key'];
                TSession::setValue('entrega_items', array());
                
                $object = new Entrega($key);
                $entrega_items = $object->getItensEntrega();
                $object->transportadora_nome = $object->transportadora->nome;
                               
                $list_items = array();
                
                if(count($entrega_items) > 0)
                {
                    foreach($entrega_items as $ei)
                    {
                        $compra = new Compra($ei->compra_id);
                        
                        $key = $ei->compra_id;
                        $list_items[ $key ] = array('compra_id'       => $compra->id,
                                                    'compra_nome'     => $compra->lote->nome,
                                                    'compra_endereco' => $compra->endereco,
                                                    'compra_cliente'   => $compra->nome_cliente,
                                                    'compra_valor'    => $compra->valor);
                    }
                }
                
                
                                  
                $this->form->setData($object); 
                TSession::setValue('entrega_items', $list_items);         
                
                $this->onReload( $param );
                
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
                TSession::setValue('entrega_items', array());
                $this->onReload( $param );
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    static function onCompraChange($params)
    {
        if( isset($params['compra_id']) && $params['compra_id'] )
        {
            try
            {
                TTransaction::open('logimed');
                
                $compra = new Compra($params['compra_id']);
                $fill_data = new StdClass;
                $fill_data->compra_nome = $compra->lote->nome;
                $fill_data->compra_cliente = $compra->nome_cliente;
                $fill_data->compra_endereco = $compra->endereco;
                $fill_data->compra_valor = $compra->valor;
                TForm::sendData('form_Entrega', $fill_data);
                TTransaction::close();
            }
            catch (Exception $e) // in case of exception
            {
                new TMessage('error', '<b>Error</b> ' . $e->getMessage());
                TTransaction::rollback();
            }
        }
        else
        {
            $object = new StdClass;
            $object->compra_id = '';
            $object->compra_nome = '';
            $object->compra_endereco = '';
            $object->compra_cliente = '';
            $object->compra_valor = '';
            TForm::sendData('form_Entrega', $object);
        }
    }
    
    public function onCompraAdd($param)
    {
        try
        {
            TTransaction::open('logimed');
            $data = $this->form->getData();
            if( (!$param['compra_id']) || (!$param['compra_cliente']) || (!$param['compra_valor']) || (!$param['compra_endereco']))
                throw new Exception('Os campos Compra, Cliente, Endereço e Valor são obrigatórios');
            
            $compra = new Compra($param['compra_id']);
            
            $entrega_items = TSession::getValue('entrega_items');
            $key = (int) $param['compra_id'];
            $entrega_items[ $key ] = array('compra_id'       => $compra->id,
                                        'compra_nome'     => $compra->lote->nome,
                                        'compra_endereco' => $compra->endereco,
                                        'compra_cliente'   => $compra->nome_cliente,
                                        'compra_valor'    => $compra->valor);
            
            TSession::setValue('entrega_items', $entrega_items);
            
            TTransaction::close();
            $data->compra_id = '';
            $data->compra_nome = '';
            $data->compra_endereco = '';
            $data->compra_cliente = '';
            $data->compra_valor = '';
            $this->form->setData($data);
            $this->onReload( $param ); // reload the sale items
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    function onNew($param)
    {
        $this->form->clear();
        TSession::setValue('entrega_items', array());
        $this->onReload( $param );
    }
    
    function onSave($param)
    {
        try
        {            
            $data = $this->form->getData('Entrega');
            $entrega_itens = TSession::getValue('entrega_items');
            
            TTransaction::open('logimed');
            
            if(isset($data->id) and $data->id)
            {
                $entrega = new Entrega($data->id);
                $entrega->transportadora_id = $data->transportadora_id;
                $old_entregas = $entrega->getItensEntrega();
                if(!$data->fl_ativo)
                {
                    throw new Exception('Situação de Entrega inválida');
                }
                $entrega->fl_ativo = $data->fl_ativo;
                $entrega->dt_confirmacao = $data->dt_confirmacao;   
                
                foreach($entrega_itens as $ei)
                {
                    if(!$entrega->getItensEntrega($ei['compra_id']))
                    {
                        $entrega_compra = new EntregaCompra();
                        $entrega_compra->entrega_id = $entrega->id;
                        $entrega_compra->compra_id = $ei['compra_id'];
                        $entrega_compra->store();
                    }
                }
                
                foreach($old_entregas as $oe)
                {
                    if(!isset($entrega_itens[$oe->compra_id]))
                    {
                        $entrega_compra = new EntregaCompra($oe->id);
                        $entrega_compra->delete();
                    }
                }
                
                $entrega->store();
                
                $this->form->setData($entrega);
            }
            else
            {
                $entrega = new Entrega();
                $entrega->transportadora_id = $data->transportadora_id;
                $entrega->fl_ativo = 'S';
                $entrega->store();
                
                foreach($entrega_itens as $ei)
                {
                    $entrega_compra = new EntregaCompra();
                    $entrega_compra->entrega_id = $entrega->id;
                    $entrega_compra->compra_id = $ei['compra_id'];
                    $entrega_compra->store();
                }
                
                $this->form->setData($entrega);
            }
            new TMessage('info', 'Entrega salva com sucesso');
            
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function onReload($param)
    {
        // read session items
        
        if(!isset($param['method']))
        {
            TSession::setValue('entrega_items', array());
            $entrega_items = TSession::getValue('entrega_items');
        }
        else
        {
            $entrega_items = TSession::getValue('entrega_items');
        }
        $this->compras_list->clear(); // clear product list
        
        $data = $this->form->getData();

        if ($entrega_items)
        {
            ksort($entrega_items);
            $cont = 1;
            foreach ($entrega_items as $list_compra_id => $list_compra)
            {
                $item_name = 'comp_' . $cont++;
                $item = new StdClass;
                
                // create action buttons
                $action_del = new TAction(array($this, 'onDeleteItem'));
                $action_del->setParameter('list_compra_id', $list_compra_id);
                
                $button_del = new TButton('delete_compra'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('ico_delete.png');

                $item->delete = $button_del;
                
                $this->formFields[ $item_name.'_delete' ] = $item->delete;
                
                $item->compra_id = $list_compra['compra_id'];
                $item->compra_nome = $list_compra['compra_nome'];
                $item->compra_endereco = $list_compra['compra_endereco'];
                $item->compra_cliente = $list_compra['compra_cliente'];
                $item->compra_valor = $list_compra['compra_valor'];
                
                $row = $this->compras_list->addItem( $item );
                $row->onmouseover = '';
                $row->onmouseout  = '';
            }
            
            $this->form->setFields( $this->formFields );
        }
        
        $this->loaded = TRUE;
    }
    
    public function onDeleteItem( $param )
    {
        $data = $this->form->getData();
        
        $data->compra_id = '';
        $data->compra_nome = '';
        $data->compra_endereco = '';
        $data->compra_cliente = '';
        $data->compra_valor = '';
        
        // clear form data
        $this->form->setData( $data );
        
        // read session items
        $entrega_items = TSession::getValue('entrega_items');
        
        // delete the item from session
        unset($entrega_items[ (int) $param['list_compra_id'] ] );
        TSession::setValue('entrega_items', $entrega_items);
        
        // reload sale items
        $this->onReload( $param );
    }

    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}

?>
