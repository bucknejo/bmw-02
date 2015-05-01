<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParticipantsController
 *
 * @author jb197342
 */
class ParticipantsController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('modify', 'html');
        $ajaxContext->initContext();                        
    }
    
    public function modifyAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            
            // get mapper for DB operations
            $mapper = new Application_Model_TableMapper();

            // get config item 
            $config = Zend_Registry::get('config');            
            
            $manager_id = $this->_getParam("manager_id", "");
            $participant_id = $this->_getParam("participant_id", "");
            
            
        
        } else {
            $this->_helper->redirector('index', 'index', 'default');            
        }
        
    }
}

?>
