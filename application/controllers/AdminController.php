<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminController
 *
 * @author jb197342
 */
class AdminController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('detail', 'html');
        $ajaxContext->addActionContext('list', 'html');
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();    
        if ($auth->hasIdentity()) {
            
            $manager_id = 0;
            $this->view->manager_id = $manager_id;
            
            $role_id = $auth->getIdentity()->role_id;
            
            if ($role_id != 1) {
                $this->_helper->redirector('index', 'index', 'default');                                
            }
            
            // for participants drop down
            $table_name = 'participants';
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $participants = $mapper->getAll($table_name, $wheres);
            
            $participant_options = "0:-- Select --";
            
            $last_names = array();
            foreach($participants as $key => $row) {
                $last_names[$key] = $row["last_name"];
            }
            
            array_multisort($last_names, SORT_ASC, $participants);
            
            foreach ($participants as $participant) {
                $participant = JEB_Lib_Lib::escapeArray($participant);
                $participant_options .= ";". $participant["id"] . ":" . $participant["first_name"] . " " . $participant["last_name"];
            }
            
            // for managers drop down
            $table_name = 'managers';
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $managers = $mapper->getAll($table_name, $wheres);
            
            $manager_options = "<option value=\"0\">-- Select --</option>";
            
            foreach ($managers as $manager) {
                $manager = JEB_Lib_Lib::escapeArray($manager);
                $manager_options .= "<option value=\"". $manager["id"] . "\">" . $manager["first_name"] . " " . $manager["last_name"] . "</option>";
            }
            
            
            $this->view->participant_options = $participant_options;
            $this->view->manager_options = $manager_options;
            
            
            
        } else {
            $this->_helper->redirector('index', 'index', 'default');                
        }
        
        //$this->view->excel = "/admin/excel/table/";        
        $this->view->excel = "/admin/report";        
    }
    
    public function readAction()
    {

        $page = $this->_getParam('page', 1);
        $rows = $this->_getParam('rows', 5);
        $table_name = 'users';

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
    
    public function excelAction()
    {

        $session = new Zend_Session_Namespace();
        $wheres = $session->__get("wheres");

        $table_name = $this->_getParam("table");
        $mapper = new Application_Model_TableMapper();

        $this->_helper->layout->disableLayout();
        $this->getResponse()->clearAllHeaders();

        $filename = 'bmw_aftersales_rnd_'.date('YmdHis').".xls";
        $segment = $table_name;
        $rs = $mapper->getAll($table_name, $wheres);
        $user = JEB_Lib_Lib::getUser();

        $response = $this->getResponse();
        $name = "Content-Disposition";
        $value = "attachment; filename=\"".$filename."\"";
        $response->setHeader($name, $value);
        $name = "Content-Type";
        $value = "application/vnd.ms-excel";
        $response->setHeader($name, $value);
        
        $excel = new JEB_Lib_Excel();

        $content = $excel->writeExcelXml1($filename, $segment, $rs, $user);
        $response->setBody($content);
    }   
    
    public function reportAction()
    {

        $session = new Zend_Session_Namespace();
        $wheres = $session->__get("wheres");

        $mapper = new Application_Model_TableMapper();

        $this->_helper->layout->disableLayout();
        $this->getResponse()->clearAllHeaders();

        $filename = 'bmw_aftersales_rnd_'.date('YmdHis').".xls";
        $user = JEB_Lib_Lib::getUser();

        $response = $this->getResponse();
        $name = "Content-Disposition";
        $value = "attachment; filename=\"".$filename."\"";
        $response->setHeader($name, $value);
        $name = "Content-Type";
        $value = "application/vnd.ms-excel";
        $response->setHeader($name, $value);
        
        $excel = new JEB_Lib_Excel();

        $segments = array('Participants Together', 'Participants With Guest', 'Participants Solo');
        $rss = array();
        $rss[] = $mapper->getAll('view_pwp', $wheres);
        $rss[] = $mapper->getAll('view_pwg', $wheres);
        $rss[] = $mapper->getAll('view_pwn', $wheres);
        
        
        $content = $excel->writeExcelXml2($filename, $segments, $rss, $user);
        $response->setBody($content);
    }   
    
    
    public function detailAction() {
        
        $id = $this->_getParam("id");
        
        $table_name = "users";
        $mapper = new Application_Model_TableMapper();
        $config = Zend_Registry::get('config');
        $columns = explode("|", $config->tablename->$table_name->columns);
        
        $users = $mapper->getItemById($table_name, $id);
        
        $this->view->users = $users[0];
        
    }
    
    public function listAction() {
        
        
        $participant_options = "";
                    
        $manager_id = $this->_getParam("manager_id", 0);

        $table_name = 'participants';
        $mapper = new Application_Model_TableMapper();
        $wheres = array();
        $wheres[] = "manager_id = $manager_id";
        $participants = $mapper->getAll($table_name, $wheres);
        
        $last_names = array();
        foreach($participants as $key => $row) {
            $last_names[$key] = $row["last_name"];
        }

        array_multisort($last_names, SORT_ASC, $participants);
        
        //$participant_options = "0:-- Select --";
        $participant_options = "<option value='0'>-- Select --</option>";

        foreach ($participants as $participant) {
            //$participant_options .= ";". $participant["id"] . ":" . $participant["first_name"] . " " . $participant["last_name"];
            $participant_options .= "<option value='".$participant["id"]."'>".$participant["first_name"]." ".$participant["last_name"]."</option>";
        }
            
                        
        
        $this->view->response = $participant_options;
        
    }
    
        
}

?>
