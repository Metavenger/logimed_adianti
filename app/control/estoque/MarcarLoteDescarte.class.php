<?php

class MarcarLoteDescarte extends TPage
{
    protected $form;
    
    function __construct()
    {
        parent::__construct();
        
        $this->form = new TQuickForm('form_MarcarLoteDescarte');
        $this->form->class = 'tform'; // change CSS class
        
        // define the form title
        $this->form->setFormTitle('Selecionar Lotes para Descarte');
        
        $lote_numero = new TSeekButton('numero');
        $lote_nome  = new TEntry('nome_produto');
        $mfLotes = new TMultiField('lotes');
        
        $action1 = new TAction(array(new LoteSeek, 'onSetup'));
        $action1->setParameter('database',      'logimed');
        TSession::setValue('loteseek_database','logimed');
        $action1->setParameter('parent',        $this->form->getName());
        TSession::setValue('loteseek_parent',$this->form->getName());
        $action1->setParameter('model',         'Lote');
        TSession::setValue('loteseek_model','Lote');
        $action1->setParameter('display_field', 'nome_produto');
        TSession::setValue('loteseek_display_field','nome_produto');
        $action1->setParameter('receive_key',   'lotes_numero');
        TSession::setValue('loteseek_receive_key','lotes_numero');
        $action1->setParameter('receive_field', 'lotes_nome_produto');
        TSession::setValue('loteseek_receive_field','lotes_nome_produto');
        
        $lote_numero->setAction($action1);
        
        $lote_numero->setSize(80);
        $lote_nome->setSize(300);
        $lote_nome->setEditable(false);
        
        $mfLotes->setHeight(400);
        $mfLotes->addField('numero', 'Nº Lote', $lote_numero, 200, true);
        $mfLotes->addField('nome_produto', 'Produto', $lote_nome, 300, true);
        
        $row=$this->form->addQuickField('', $mfLotes);
        $row->del($row->get(0));
        $row->get(1)->colspan = '2';
        
        $generate_button=new TButton('marcar');
        $generate_button->setAction(new TAction(array($this, 'onMarcar')), 'Marcar');
        $generate_button->setImage('ico_apply.png');
        
        $this->form->setFields(array($lote_numero, $lote_nome, $mfLotes, $generate_button));
        
        $container = new TTable;
        $container->addRow()->addCell(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->addRow()->addCell($this->form);
        $container->addRow()->addCell($generate_button);
        
        parent::add($container);
    }
    
    function onMarcar()
    {
        try
        {
            TTransaction::open('logimed');
            
            $formdata = $this->form->getData();
            
            $this->form->validate();
            
            if(!$formdata->lotes)
            {
                throw new Exception('Informe pelo menos um número de lote');
            }
            
            foreach($formdata->lotes as $l)
            {
                $lote = Lote::newFromNumero($l->numero);
                if($lote->dt_descarte)
                {
                    throw new Exception('O lote ' . $lote->numero . ' já teve seu descarte confirmado!');
                }
                else if($lote->fl_descarte == 'S')
                {
                    throw new Exception('O lote ' . $lote->numero . ' já está marcado para descarte!');
                }
                else
                {
                    $lote->fl_descarte = 'S';
                    $lote->store();
                }
            }
            
            new TMessage('info', 'Os lotes selecionados foram marcados para descarte.');
            
            TTransaction::close();
        }
        catch(Exception $e)
        {
            // shows the exception error message
            $this->form->setData($this->formdata);
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}

?>
