<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AgendaController
 *
 * @author jb197342
 */
class AgendaController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {            
            
                        
        } else {
            $this->_helper->redirector('index', 'index', 'default');            
        }
        
        
    }
    
}

?>
