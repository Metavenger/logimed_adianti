<?php

class CancelarLoteDescarte extends TPage
{
    protected $form;
    
    function __construct()
    {
        parent::__construct();
        
        $this->form = new TQuickForm('form_CancelaLoteDescarte');
        $this->form->class = 'tform'; // change CSS class
        
        // define the form title
        $this->form->setFormTitle('Cancelar Descarte de Lote');
        
        $lote_numero = new TSeekButton('numero');
        $lote_nome  = new TEntry('nome_produto');
        
        $action1 = new TAction(array(new LoteSeek, 'onSetup'));
        $action1->setParameter('database',      'logimed');
        TSession::setValue('loteseek_database','logimed');
        $action1->setParameter('parent',        $this->form->getName());
        TSession::setValue('loteseek_parent',$this->form->getName());
        $action1->setParameter('model',         'Lote');
        TSession::setValue('loteseek_model','Lote');
        $action1->setParameter('display_field', 'nome_produto');
        TSession::setValue('loteseek_display_field','nome_produto');
        $action1->setParameter('receive_key',   'numero');
        TSession::setValue('loteseek_receive_key','numero');
        $action1->setParameter('receive_field', 'nome_produto');
        TSession::setValue('loteseek_receive_field','nome_produto');
        
        $lote_numero->setAction($action1);
        
        $lote_numero->setSize(80);
        $lote_nome->setSize(300);
        $lote_nome->setEditable(false);
        
        $this->form->addQuickFields('Lote', array($lote_numero, $lote_nome), new TRequiredValidator);
        
        $this->form->addQuickAction('Cancelar', new TAction(array($this, 'onCancelar')), 'ico_apply.png');
        
        $container = new TVBox;
        $container->style = 'width: 50%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    function onCancelar()
    {
        try
        {
            TTransaction::open('logimed');
            
            $data = $this->form->getData();
            
            if(!$data->numero)
            {
                throw new Exception('Informe um número de lote');
            }
            
            $lote = Lote::newFromNumero($data->numero);
            
            if($lote->fl_descarte == 'N')
            {
                throw new Exception('O lote ' . $lote->numero . ' não está marcado para descarte!');
            }
            else if($lote->fl_descarte == 'S' and $lote->dt_descarte != null)
            {
                throw new Exception('O lote ' . $lote->numero . ' teve descarte confirmado em ' . TDate::date2br($lote->dt_descarte));
            }
            else
            {
                $lote->fl_descarte = 'N';
                $lote->store();
            }
            
            new TMessage('info', 'Descarte do lote foi cancelado');
            
            TTransaction::close();
        }
        catch(Exception $e)
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}

?>
