<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {
                
        $email = $this->_getParam('email', 'Missing Email Address');
        $password = $this->_getParam('password', '');
        //$q_number = $this->_getParam('q_number', '');
        
        $table_name = 'managers';

        $message = "";

        $values = array(
            'email' => $email,
            'password' => $password,
            
            //'q_number' => $q_number,
        );


        if ($this->getRequest()->isPost()) {

            if ($this->_process($table_name, $values)) {
                $auth = Zend_Auth::getInstance();
                $role_id = $auth->getIdentity()->role_id;
                $id = $auth->getIdentity()->id;
                $this->_helper->redirector('index', 'agenda', 'default');
            } else {
                $message = "Sorry!  We cannot authenticate: " . $values['email'];
            }
        } else {
            
        }

        $this->view->message = $message;
    }
            
    protected function _process($table_name, $values) {

        $adapter = $this->_getAuthAdapter($table_name);
        
        $adapter->setIdentity($values['email']);
        $adapter->setCredential($values['password']);
        //$adapter->setCredential($values['q_number']);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
            $auth->getStorage()->write($user);
            $session = new Zend_Session_Namespace('Zend_Auth');
            $session->setExpirationSeconds(30 * 60);
            return true;
        }
        return false;
    }

    protected function _getAuthAdapter($table_name) {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName($table_name);
        
        $authAdapter->setIdentityColumn('email');
        $authAdapter->setCredentialColumn('password');
        //$authAdapter->setCredentialColumn('q_number');
        
        return $authAdapter;
    }
    
    public function _decrypt($password, $user_id, $key) {
        $mapper = new Application_Model_TableMapper();
        $column = new Zend_Db_Expr("AES_DECRYPT(password,'$key')");
        $where = "user_id = '$user_id'";
        $decrypt = $mapper->getSingleColumnValue('managers', $column, $where);

        return $decrypt;
    }

}

