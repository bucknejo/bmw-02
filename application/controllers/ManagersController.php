<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ManagersController
 *
 * @author jb197342
 */
class ManagersController extends Zend_Controller_Action {
    

    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('edit', 'html');        
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {            
            
            $manager_id = $auth->getIdentity()->id;
            $this->view->manager_id = $manager_id;
                        
            $table_name = 'managers';
            $mapper = new Application_Model_TableMapper();
            $manager = $mapper->getItemById($table_name, $manager_id);
            
            $this->view->manager = $manager;                                   
                        
        } else {
            $this->_helper->redirector('index', 'index', 'default');            
        }
        
    }
    
    public function completeAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            
            $id = $auth->getIdentity()->id;
            $email = $auth->getIdentity()->email;
            
            $table_name = 'users';
            $mapper = new Application_Model_TableMapper();

            if ($this->getRequest()->isPost()) {
            }
            
            $users = $mapper->getItemById($table_name, $id);            
            $this->view->users = $users;
            
            $config = Zend_Registry::get('config');
            $columns = explode("|", $config->tablename->users->columns);
            $wheres = array(
                "registration_filer = '$email'"
            );
            $guest = $mapper->getItemsByColumns($table_name, $columns, $wheres);
            $this->view->guest = $guest;
            
            
        } else {
            
        }
        
    }
    
    public function readAction() {
        
        $page = $this->_getParam('page', 1);
        $rows = $this->_getParam('rows', 5);
        $table_name = $this->_getParam('table', '');
                
        $grid = new JEB_Lib_Grid();
        $wheres = $grid->parseFilters();
        
        $session = new Zend_Session_Namespace();
        $session->__set("wheres", $wheres);

        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name->columns);

        $service = new Application_Service_TableService();
        $entries = $service->fetchOutstanding($page, $wheres, $table_name, $columns, $rows);

        $this->view->response = $grid->writeXml($page, $rows, $entries);
        
        
    }
    
    public function editAction() {
        
        $oper = $this->_getParam("oper");
        
        $table_name = $this->_getParam("table", "");        
        $id = $this->_getParam("id", 0);
        
        $date_created = date('Y-m-d');
        $last_updated = date('Y-m-d');
        $user = "system";
        $active = "1";
        
        $first_name = $this->_getParam("first_name", "");
        $last_name = $this->_getParam("last_name", "");
        $user_name = $this->_getParam("user_name", "");
        $email = $this->_getParam("email", "");

        $password = $this->_getParam("password", "");
        $registered = $this->_getParam("registered", "");
        $title = $this->_getParam("title", "");
        $center = $this->_getParam("center", "");
        $region = $this->_getParam("region", "");
        $gender = $this->_getParam("gender", "");
        $role_id = $this->_getParam("role_id", "");

        $data = array(
            'date_created' => $date_created,
            'last_updated' => $last_updated,
            'user' => $user,
            'active' => $active,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_name' => $user_name,
            'email' => $email,
            'password' => $password,
            'registered' => $registered,
            'title' => $title,
            'center' => $center,
            'region' => $region,
            'gender' => $gender,
            'role_id' => $role_id
        );
        
        $i = 0;
                
        if($this->getRequest()->isPost()) {
            
            $mapper = new Application_Model_TableMapper();
                                                
            if ($oper == "add") {                
                $i = $mapper->insertItem($table_name, $data);                    
            } else if ($oper == "edit") {                
                $i = $mapper->updateItem($table_name, $data, $id);
            } else {                
                $i = $mapper->deleteItem($table_name, $id);                
            }
            
        }
        
        $this->view->i = $i;
        
                        
    }
    
    public function _writeSelectOptions($data) {
        
        $select = "";
        foreach($data as $item) {
            $select .= "$item:$item";
        }
        
        return $select;
        
    }
    
    
}

?>
