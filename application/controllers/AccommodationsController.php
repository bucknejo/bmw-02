<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccommodationController
 *
 * @author jb197342
 */
class AccommodationsController extends Zend_Controller_Action {
    //put your code here
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('read', 'html');
        $ajaxContext->addActionContext('edit', 'html');
        $ajaxContext->addActionContext('list', 'html');
        $ajaxContext->initContext();        
    }
    
    public function indexAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {            
            
            $manager_id = $auth->getIdentity()->id;
            $this->view->manager_id = $manager_id;
            $this->view->manager_name = $auth->getIdentity()->first_name . " " . $auth->getIdentity()->last_name;
            $this->view->manager_title = $auth->getIdentity()->title;
                        
            $table_name = 'managers';
            $mapper = new Application_Model_TableMapper();
            $managers = $mapper->getItemById($table_name, $manager_id);
            $manager = $managers[0];
            
            $table_name = 'participants';
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $wheres[] = "manager_id = $manager_id";
            $participants = $mapper->getAll($table_name, $wheres);
            
            $participant_options = "0:-- Select --";
            
            foreach ($participants as $participant) {
                $participant_options .= ";". $participant["id"] . ":" . $participant["first_name"] . " " . $participant["last_name"];
            }
            
            $this->view->manager = $manager;
            $this->view->participant_options = $participant_options;
                        
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
        
        $manager_id = $this->_getParam('manager_id', 0);
        
        $grid = new JEB_Lib_Grid();
        $wheres = $grid->parseFilters();
        
        if ($table_name == 'guests') {
            $wheres[] = "manager_id = $manager_id";
            $wheres[] = "participant_id = $participant_id";
        }

        if ($table_name == 'participants' && $manager_id != 0) {
            $wheres[] = "manager_id = $manager_id";
        }

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
        
        $manager_id = $this->_getParam("manager_id", 0);
        $registered = 1;

        $first_name = $this->_getParam("first_name", "");
        $last_name = $this->_getParam("last_name", "");
        $email = $this->_getParam("email", "");
        $nickname = $this->_getParam("nickname", "");

        $title = $this->_getParam("title", "");
        $center = $this->_getParam("center", "");
        $region = $this->_getParam("region", "");
        $gender = $this->_getParam("gender", "");

        $check_in_date = $this->_getParam("check_in_date", "");
        $check_out_date = $this->_getParam("check_out_date", "");

        $early_check_in = $this->_getParam("early_check_in", "");
        $late_check_out = $this->_getParam("late_check_out", "");
        $transportation = $this->_getParam("transportation", "");


        $hotel = $this->_getParam("hotel", "");
        $room_type = $this->_getParam("room_type", "");
        $occupancy = $this->_getParam("occupancy", "");
        $telephone = $this->_getParam("telephone", "");
        $emergency_contact = $this->_getParam("emergency_contact", "");
        $emergency_telephone = $this->_getParam("emergency_telephone", "");
        $dietary_restrictions = $this->_getParam("dietary_restrictions", "");

        // new items for 2013

        $arrival_date = $this->_getParam("arrival_date", "");
        $arrival_time = $this->_getParam("arrival_time", "");
        $arrival_flight_info = $this->_getParam("arrival_flight_info", "");
        $departure_date = $this->_getParam("departure_date", "");
        $departure_time = $this->_getParam("departure_time", "");
        $departure_flight_info = $this->_getParam("departure_flight_info", "");

        // New conditional logic to default an unused check in/out date(s)
        /*
        if ($check_in_date == null || $check_in_date == "") {
            
            $check_in_date = date('Y-m-d');
        } else {
            $ci = explode("-", $check_in_date);
            $check_in_date = $ci[2] ."-". $ci[0] ."-".$ci[1];
        }
        
        if ($check_out_date == null || $check_out_date == "") {
            $check_out_date = date('Y-m-d');
        } else {
            $co = explode("-", $check_out_date);
            $check_out_date = $co[2] ."-". $co[0] ."-".$co[1];    
        }
        */
        
        $guest_first_name = $this->_getParam("guest_first_name", "");
        $guest_last_name = $this->_getParam("guest_last_name", "");
        
        $guest_type = $this->_getParam("guest_type", "");
        $guest_participant_id = $this->_getParam("guest_participant_id", "");
        
        if ($guest_type == 'Participant') {
            
            $guest_gender = "";
            $guest_dietary_restrictions = "";
            $guest_emergency_contact = "";
            $guest_emergency_telephone = "";
            $guest_arrival_date = date('Y-m-d');
            $guest_arrival_time = "";
            $guest_arrival_flight_info = "";
            $guest_departure_date = date('Y-m-d');
            $guest_departure_time = "";
            $guest_departure_flight_info = "";
            
        } else {
            
            $guest_gender = $this->_getParam("guest_gender", "");
            $guest_dietary_restrictions = $this->_getParam("guest_dietary_restrictions", "");
            $guest_emergency_contact = $this->_getParam("guest_emergency_contact", "");
            $guest_emergency_telephone = $this->_getParam("guest_emergency_telephone", "");
            $guest_arrival_date = $this->_getParam("guest_arrival_date", date('Y-m-d'));
            $guest_arrival_time = $this->_getParam("guest_arrival_time", "");
            $guest_arrival_flight_info = $this->_getParam("guest_arrival_flight_info", "");
            $guest_departure_date = $this->_getParam("guest_departure_date", date('Y-m-d'));
            $guest_departure_time = $this->_getParam("guest_departure_time", "");
            $guest_departure_flight_info = $this->_getParam("guest_departure_flight_info", "");            
            
        }
        

        $data = array(

            'date_created' => $date_created,
            'last_updated' => $last_updated,
            'user' => $user,
            'active' => $active,
            'manager_id' => $manager_id,
            'registered' => $registered,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'nickname' => $nickname,
            'title' => $title,
            'center' => $center,
            'region' => $region,
            'gender' => $gender,                    
            /*
            'check_in_date' => $check_in_date,
            'check_out_date' => $check_out_date,
            'early_check_in' => $early_check_in,
            'late_check_out' => $late_check_out,   
            */
            'transportation' => $transportation,
            'hotel' => $hotel,
            'room_type' => $room_type,
            'occupancy' => $occupancy,
            'telephone' => $telephone,
            'emergency_contact' => $emergency_contact,
            'emergency_telephone' => $emergency_telephone,
            'dietary_restrictions' => $dietary_restrictions,
            'arrival_date' => $arrival_date,
            'arrival_time' => $arrival_time,
            'arrival_flight_info' => $arrival_flight_info,
            'departure_date' => $departure_date,
            'departure_time' => $departure_time,
            'departure_flight_info' => $departure_flight_info,
            'guest_first_name' => $guest_first_name,
            'guest_last_name' => $guest_last_name,
            'guest_type' => $guest_type,
            'guest_participant_id' => $guest_participant_id,
            'guest_gender' => $guest_gender,
            'guest_dietary_restrictions' => $guest_dietary_restrictions,
            'guest_emergency_contact' => $guest_emergency_contact,
            'guest_emergency_telephone' => $guest_emergency_telephone,
            'guest_arrival_date' => $guest_arrival_date,
            'guest_arrival_time' => $guest_arrival_time,
            'guest_arrival_flight_info' => $guest_arrival_flight_info,
            'guest_departure_date' => $guest_departure_date,
            'guest_departure_time' => $guest_departure_time,
            'guest_departure_flight_info' => $guest_departure_flight_info,
        );
        
        $i = 0;
                
        if($this->getRequest()->isPost()) {
            
            $mapper = new Application_Model_TableMapper();
                                                
            if ($oper == "add") {                
                $i = $mapper->insertItem($table_name, $data);
                $id = $mapper->getLastInsertId($table_name);
            } else if ($oper == "edit") {                
                $i = $mapper->updateItem($table_name, $data, $id);
            } else {                
                $i = $mapper->deleteItem($table_name, $id);                
            }
                                    
            
            $links = array($id, $guest_participant_id);
            sort($links);
            
            if ($guest_participant_id != 0) {
                
                $link_id = "P".str_pad(implode($links), 5, "0", STR_PAD_LEFT);
                                
                
                // link the participant back to the original
                $data = array(
                    'guest_type' => 'Participant',
                    'room_type' => $room_type,
                    'occupancy' => 'Double',
                    'guest_participant_id' => $id,                    
                    'link_id' => $link_id
                );                       
                
                $j = $mapper->updateItem($table_name, $data, $guest_participant_id);
                
                // set participant link id
                $data = array(
                    'link_id' => $link_id
                );                       
                
                $j = $mapper->updateItem($table_name, $data, $id);
                
            }
            
            // link id for guests
            if ($guest_participant_id == 0 && $occupancy == 'Double') {
                
                $link_id = "G".str_pad(implode($links), 5, "0", STR_PAD_LEFT);
                
                // set participant link id
                $data = array(
                    'link_id' => $link_id
                );                       
                
                $j = $mapper->updateItem($table_name, $data, $id);
                
            }
            
            // unlink action for de-linking participant guests
            if ($guest_participant_id == 0 && $occupancy == 'Single') {
                
                // unlink items
                $data = array(
                    'guest_type' => '',
                    'room_type' => $room_type,
                    'occupancy' => 'Single',
                    'guest_participant_id' => 0,                    
                    'link_id' => 0
                );
                
                $wheres = array();
                $wheres[] = "guest_participant_id = $id";
                
                $j = $mapper->updateSpecific($table_name, $data, $wheres);
                
                $wheres = array();
                $wheres[] = "id = $id";
                
                $j = $mapper->updateSpecific($table_name, $data, $wheres);
            }
            
        }
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {            
            $manager_id = $auth->getIdentity()->id;
        }
        
        if ($i > 0) {
            if ($oper == "add") {
                $b = $this->_helper->confirmation->mailConfirmation($manager_id, $id);
            } 
        }
        
        $this->view->i = $i;
        
                        
    }
    
    public function confirmationAction() {
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {            
            
            $manager_id = $auth->getIdentity()->id;
                        
            $table_name = 'managers';
            $mapper = new Application_Model_TableMapper();
            $managers = $mapper->getItemById($table_name, $manager_id);
            $manager = $managers[0];
            
            $this->view->manager = $manager;
            
            $table_name = 'participants';
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $wheres[] = "manager_id = $manager_id";
            $participants = $mapper->getAll($table_name, $wheres);
            
            $this->view->participants = $participants;
                        
        } else {
            $this->_helper->redirector('index', 'index', 'default');            
        }
        
        
    }
    
    public function listAction() {
        
        $auth = Zend_Auth::getInstance();
        
        $participant_options = "";
        
        if ($auth->hasIdentity()) {            
            
            $manager_id = $auth->getIdentity()->id;
            
            $table_name = 'participants';
            $mapper = new Application_Model_TableMapper();
            $wheres = array();
            $wheres[] = "manager_id = $manager_id";
            $participants = $mapper->getAll($table_name, $wheres);
            
            //$participant_options = "0:-- Select --";
            $participant_options = "<option value='0'>-- Select --</option>";
            
            foreach ($participants as $participant) {
                //$participant_options .= ";". $participant["id"] . ":" . $participant["first_name"] . " " . $participant["last_name"];
                $participant_options .= "<option value='".$participant["id"]."'>".$participant["first_name"]." ".$participant["last_name"]."</option>";
            }
            
                        
        } else {
            
        }
        
        $this->view->response = $participant_options;
        
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
