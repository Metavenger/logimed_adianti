<?php

class Inicial extends TPage
{
    private $html;
    
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        TPage::include_css('app/resources/styles.css');
        $this->html = new THtmlRenderer('app/resources/Inicial.html');

        // define replacements for the main section
        $replace = array();
        
        // replace the main section variables
        $this->html->enableSection('main', $replace);
        
        // add the template to the page
        parent::add($this->html);
    }
}
