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
        $ajaxContext->addActionContext('delete', 'json');
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

        $arrival_date = $this->_getParam("arrival_date", NULL);
        $arrival_time = $this->_getParam("arrival_time", "");
        $arrival_flight_info = $this->_getParam("arrival_flight_info", "");
        $departure_date = $this->_getParam("departure_date", NULL);
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
        
        $document_id = $this->_getParam("document", "-1");
        $document_name = $this->_getParam("document_name", "");
        

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
            'document_id' => $document_id,
            'document_name' => $document_name
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
    
    public function uploadAction() {
        
        try {
        
            // HTTP headers for no cache etc
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            
            $itinerary_type = $_REQUEST["itinerary_type"];
            $document_name = $_REQUEST["document_name"];
            
            // Settings
            //$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";        
            $config = Zend_Registry::get('config');
            //$targetDir = realpath($config->itinerary->upload->path) . DIRECTORY_SEPARATOR;
            $targetDir = realpath($config->itinerary->upload->path);

            $cleanupTargetDir = true; // Remove old files
            //$maxFileAge = 5 * 3600; // Temp file age in seconds
            $maxFileAge = 60 * 60 * 24 * 365; // Temp file age in seconds

            // 5 minutes execution time
            @set_time_limit(5 * 60);
            
            // Get parameters
            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
            $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

            // Clean the fileName for security reasons
            $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
            
            // Make sure the fileName is unique but only if chunking is disabled
            if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
                $ext = strrpos($fileName, '.');
                $fileName_a = substr($fileName, 0, $ext);
                $fileName_b = substr($fileName, $ext);

                $count = 1;
                while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                    $count++;                    
                }

                $fileName = $fileName_a . '_' . $count . $fileName_b;
            }
            
            $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

            // Create target dir
            if (!file_exists($targetDir)) {
                @mkdir($targetDir);
            }
                
            // Remove old temp files	
            if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                    
                    $max = time() - $maxFileAge;
                    $filetime = filemtime($tmpfilePath);
                    $match = preg_match('/\.part$/', $file);

                    // Remove temp file if it is older than the max age and is not the current file
                    //if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                    if (($filetime < $max) && ($tmpfilePath != "{$filePath}.part")) {
                        @unlink($tmpfilePath);
                    }
                }

                closedir($dir);
            } else {
                $response = '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}';
                die($response);           
            }
            
            // Look for the content type header
            if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
                    $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

            if (isset($_SERVER["CONTENT_TYPE"]))
                    $contentType = $_SERVER["CONTENT_TYPE"];

            // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
            if (strpos($contentType, "multipart") !== false) {
                if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    // Open temp file
                    $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                    if ($out) {
                        // Read binary input stream and append it to temp file
                        $in = fopen($_FILES['file']['tmp_name'], "rb");
                                                
                        if ($in) {
                            while ($buff = fread($in, 4096)) {
                                fwrite($out, $buff);                            
                            }
                        } else {
                            $response = '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
                            die($response);                        
                        }
                        fclose($in);
                        fclose($out);
                        @unlink($_FILES['file']['tmp_name']);
                    } else {
                        $response = '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
                        die($response);
                    }
                } else {
                    $response = '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}';
                    die($response);               
                }
            } else {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen("php://input", "rb");
                                        
                    if ($in) {
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);                        
                        }
                    } else {
                        $response = '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
                        die($response);                    
                    }

                    fclose($in);
                    fclose($out);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');                
                }
            }
            
            // Check if file has been uploaded
            if (!$chunks || $chunk == $chunks - 1) {
                // Strip the temp .part suffix off 
                rename("{$filePath}.part", $filePath);  
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $filePath);
                finfo_close($finfo);

                $mapper = new Application_Model_TableMapper();   
                $table_name = "documents";
                $data = array(
                    'date_created' => date('Y-m-d'),
                    'type' => $itinerary_type,
                    'name' => $fileName,
                    //'itinerary' => file_get_contents($filePath),
                    'file_name' => $document_name,
                    'mime' => $mime
                );

                $document = $mapper->insertItem($table_name, $data);
                $id = -1;
                if ($document > 0) {
                    $id = $mapper->getLastInsertId($table_name);
                }
                
            }                
                        
            // Return JSON-RPC response
            $response = '{"jsonrpc" : "2.0", "result" : null, "id" : "id", "document": ' . $id . '}';                                    
            
            $this->view->response = $response;
            die($response);            
            
        } catch (Exception $ex) {

        }
        
    }
    
    public function deleteAction() {
        
        $document = $this->_getParam("document", "-1");
        
        $mapper = new Application_Model_TableMapper();   
        $table_name = "documents";
        $id = $mapper->deleteItem($table_name, $document);
        
        $code = -1;
        $message = "fail";
        if ($id > 0) {
            $code = 0;
            $message = "success";
        }
                
        $data = array(
            'code' => $code,
            'message' => $message,
            'id' => $id,
            'document' => $document
        );
        $response = json_encode($data);
        $this->view->response = $response;
    }
    
    function downloadAction() {
        
        $id = $this->_getParam("document", "-1");
        
        $mapper = new Application_Model_TableMapper();
        $table_name = "documents";
        $documents = $mapper->getItemById($table_name, $id);
        
        $config = Zend_Registry::get('config');
        //$targetDir = realpath($config->itinerary->upload->path) . DIRECTORY_SEPARATOR;
        $targetDir = realpath($config->itinerary->upload->path);
        
                
        if (count($documents) > 0) {
            
            $document = $documents[0];
            
            $filePath = $targetDir . DIRECTORY_SEPARATOR . $document["name"];
            $mime = $document["mime"];
            $filename = $document["file_name"];
            header("Content-type: $mime");
            header("Content-Disposition: attachment; filename=\"".$filename."\"");            
            //header("Content-length: $fsize");
            //header("Cache-control: private");  
            set_time_limit(0);
            $file = @fopen($filePath,"rb");
            while(!feof($file))
            {
                print(@fread($file, 1024*8));
                ob_flush();
                flush();
            }
        
        }
        
        
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
