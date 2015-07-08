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
        $ajaxContext->addActionContext('delete', 'json');        
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

        $content = $excel->writeExcelCsv($filename, $segment, $rs, $user);
        $response->setBody($content);
    }   
    
    public function reportAction()
    {

        $session = new Zend_Session_Namespace();
        $wheres = $session->__get("wheres");

        $mapper = new Application_Model_TableMapper();

        $this->_helper->layout->disableLayout();
        $this->getResponse()->clearAllHeaders();

        $filename = 'bmw_aftersales_rnd_'.date('YmdHis').".csv";
        $user = JEB_Lib_Lib::getUser();

        $response = $this->getResponse();
        $name = "Content-Disposition";
        $value = "attachment; filename=\"".$filename."\"";
        $response->setHeader($name, $value);
        $name = "Content-Type";
        //$value = "application/vnd.ms-excel";
        $value = "text/csv";
        $response->setHeader($name, $value);
        
        $excel = new JEB_Lib_Excel();

        $segments = array('Participants Together', 'Participants With Guest', 'Participants Solo');
        $rss = array();
        $rss[] = $mapper->getAll('view_pwp', $wheres);
        $rss[] = $mapper->getAll('view_pwg', $wheres);
        $rss[] = $mapper->getAll('view_pwn', $wheres);
        
        
        //$content = $excel->writeExcelXml2($filename, $segments, $rss, $user);
        $content = $excel->writeExcelCsv1($filename, $segments, $rss, $user);
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
    
    
        
}

?>
