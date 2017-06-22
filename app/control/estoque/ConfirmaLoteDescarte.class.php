<?php
class ConfirmaLoteDescarte extends TPage
{
    protected $form;
    
    function __construct()
    {
        parent::__construct();
         
        $this->form = new TQuickForm('form_ConfirmaLoteDescarte');
        $this->form->class = 'tform'; // change CSS class
        
        // define the form title
        $this->form->setFormTitle('Confirmar Descarte de Lotes');
        
        $total_lotes  = new TEntry('total_lotes');
        $dt_descarte = new TEntry('dt_descarte');
        
        $total_lotes->setEditable(false);
        $dt_descarte->setEditable(false);
        
        TTransaction::open('logimed');
        $sql = "select count(*) as total from lote where fl_descarte = 'S' and dt_descarte is null order by tipodescarte_id asc, numero asc";
        $conn = TTransaction::get();
        $result = $conn->query($sql);
        $count = $result->fetchObject();
        TTransaction::close();
        
        $total_lotes->setValue($count->total);

        $dt_descarte->setValue(date('d/m/Y'));
        
        $this->form->addQuickField('Total de Lotes', $total_lotes, 100, new TRequiredValidator);
        $this->form->addQuickField('Data de Descarte', $dt_descarte, 100, new TRequiredValidator);
        
        $this->form->addQuickAction('Listar', new TAction(array($this, 'onDescartar')), 'ico_apply.png');
        
        $container = new TVBox;
        
        //$container->style = 'width: 50%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    function onDescartar()
    {
        $actionYes = new TAction(array($this, 'descartar'));
        new TQuestion('Deseja confirmar o descarte para todos os lotes?', $actionYes);
    }
    
    function descartar()
    {
        try
        {
            // open a transaction with database 'tab'
            TTransaction::open('logimed');
                      
            $sql = "select * from lote where dt_descarte = '" . date('Y-m-d') . "'";
            $conn = TTransaction::get();
            $result = $conn->query($sql);
            $testa_exec = $result->fetchAll(PDO::FETCH_OBJ);
            
            if($testa_exec)
            {
                throw new Exception('Já foi realizado o descarte para a data especificada. Aguarde a próxima data.');
            }
            
            $sql = "select * from lote where fl_descarte = 'S' and dt_descarte is null order by tipodescarte_id asc, numero asc";
            $conn = TTransaction::get();
            $result = $conn->query($sql);
            $lotes = $result->fetchAll(PDO::FETCH_OBJ);
                        
            // cria
            $pdf= new FPDF("P","pt","A4");
            $pdf->SetAutoPageBreak(TRUE);
            $pdf->SetMargins(20,35,20);
            
            $tit = "* * * LOGIMED * * *";
            $nom = "RELAÇÃO DE LOTES PARA DESCARTE EM " . date('d/m/Y');
            $numero = 'NUMERO';
            $peso = 'PESO';
            $corta_linha = '- - - - - - - - - - - - - - - - -';
            $contador = 0;
            $total = 0;
            $conta_lotes = 1;
            
            $old_tipo = null;
            $new_tipo = null;
            
            //AddPage
            $pdf->AddPage();       
            
            $pdf->SetFont('Courier','B',12);
            
            $pdf->Cell(550,30,utf8_decode($tit),0,1,'C');
            $pdf->Cell(35,20,'',0,0,'L');
            $pdf->MultiCell(480,20,utf8_decode($nom),1,'C');
            $pdf->Cell(35,10,'',0,1,'L');
                       
            foreach($lotes as $lote)
            {
                $old_tipo = $new_tipo;
                $new_tipo = $lote->tipodescarte_id;
                
                if($old_tipo != $new_tipo and $conta_lotes < 2)
                {
                    $tipodescarte = new TipoDescarte($new_tipo);
                    $pdf->Cell(550,30,utf8_decode($tipodescarte->grupo . ' - ' . $tipodescarte->descricao),0,1,'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,1,'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,1,'C');
                }
                else if($old_tipo != $new_tipo and $conta_lotes == 2)
                {
                    $pdf->Cell(10,15, '',0,1,'C');
                    $conta_lotes = 1;
                    $tipodescarte = new TipoDescarte($new_tipo);
                    $pdf->Cell(550,30,utf8_decode($tipodescarte->grupo . ' - ' . $tipodescarte->descricao),0,1,'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,1,'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,1,'C');
                }
                
                if($contador == 88)
                {
                    $total += $contador;
                    $contador = 0;
                    $pdf->AddPage();       
            
                    $pdf->SetFont('Courier','B',12);
                    
                    $pdf->Cell(550,30,utf8_decode($tit),0,1,'C');
                    $pdf->Cell(35,20,'',0,0,'L');
                    $pdf->MultiCell(480,20,utf8_decode($nom),1,'C');
                    $pdf->Cell(35,10,'',0,1,'L');
                    
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(130,15,$numero, 0, 0, 'C');
                    $pdf->Cell(130,15,$peso, 0, 0, 'C');
                    $pdf->Cell(10,15, '|',0,1,'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,0,'C');
                    $pdf->Cell(260,15,$corta_linha, 0, 0, 'C');
                    $pdf->Cell(10,15, '+',0,1,'C');
                }
                $contador++;
                
                if($conta_lotes < 2)
                {
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $pdf->Cell(135,15,$lote->numero,0,0,'C');
                    $pdf->Cell(125,15,$lote->peso . 'g',0,0,'C');
                    $pdf->Cell(10,15, '|',0,0,'C');
                    $conta_lotes++;
                    $object = new Lote($lote->id);
                    $object->dt_descarte = date('Y-m-d');
                    $object->store();
                }
                else
                {
                    $pdf->Cell(135,15,$lote->numero,0,0,'C');
                    $pdf->Cell(125,15,$lote->peso . 'g',0,0,'C');
                    $pdf->Cell(10,15, '|',0,1,'C');
                    $conta_lotes = 1;
                    $object = new Lote($lote->id);
                    $object->dt_descarte = date('Y-m-d');
                    $object->store();
                }
                
                
            }
            if($contador != 0)
            {
                $total += $contador;
            }
            $pdf->Cell(10,15,'',0,1,'C');

            $pdf->Cell(550,30,'TOTAL DE LOTES PARA DESCARTE: ' . $total,0,1,'C'); 
            
            $file = "app/output/ListaDescarteLotes" . uniqid() .".pdf";
            
            // stores the file
            if($total > 0)
            {
                if (!file_exists($file) OR is_writable($file))
                {
                    $pdf->Output($file);
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . $file);
                }
            }
            else
            {
                throw new Exception('Pelo menos um lote deve estar marcado para descarte!');
            }
            
            parent::openFile($file);
            
            // shows the success message
            new TMessage('info', 'Relatório gerado. Por favor, habilite os popups no browser.', new TAction(array($this, 'recarregaPagina')));

            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    function recarregaPagina()
    {
        AdiantiCoreApplication::loadPage('ConfirmaLoteDescarte');
    }
}
?>