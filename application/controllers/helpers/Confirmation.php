<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Confirmation
 *
 * @author jb197342
 */
class Zend_Controller_Action_Helper_Confirmation extends Zend_Controller_Action_Helper_Abstract {
    //put your code here
    
    public function direct() {
        
    }
    
    public function mailConfirmation($manager_id, $participant_id) {
        
        $config = Zend_Registry::get('config');
        $from = $config->mail->from->address;
        $name = $config->mail->from->name;
        $year = $config->project->year;
        
        $table_name = 'managers';
        $mapper = new Application_Model_TableMapper();
        $managers = $mapper->getItemById($table_name, $manager_id);
        $manager = $managers[0];
        
        $table_name = 'participants';
        $mapper = new Application_Model_TableMapper();
        $participants = $mapper->getItemById($table_name, $participant_id);
        $participant = $participants[0];
        
        // guest infomation
        $guest_name = $participant['guest_first_name'] . " " . $participant['guest_last_name'];
        
        if ($participant['guest_participant_id'] != 0) {
            $guest_participants = $mapper->getItemById($table_name, $participant['guest_participant_id']);
            $guest_participant = $guest_participants[0];
            $guest_name = $guest_participant['first_name'] . " " . $guest_participant['last_name'];
        }
        
        $data = array(
            'manager_name' => $manager['first_name'] . " " . $manager['last_name'],
            'participant_name' => $participant['first_name'] . " " . $participant['last_name'],
            'hotel' => $participant['hotel'],
            'arrival_date' => $participant['arrival_date'],
            'departure_date' => $participant['departure_date'],
            'occupancy' => $participant['occupancy'],
            'guest_name' => $guest_name
        );
        
        $html = $this->_buildHTML($data);
        
        $mail = new Zend_Mail();
        $mail->setFrom($from, $name);
        $mail->addTo($manager['email']);
        $mail->addTo($participant['email']);
        //$mail->addCc($config->site->admin->email);
        $mail->setSubject("After Sales Ride and Drive $year - Registration");
        $mail->setBodyHtml($html);
        return $mail->send();
            
    }
                        
    
    public function _buildHTML($data) {
        
        $config = Zend_Registry::get('config');
        $image_path = $config->mail->images->path;    
        $room_rate = $config->hotel->room->rate;
        $room_tax = $config->hotel->room->tax;
        $room_price = "$" . (number_format($room_rate + $room_tax, 2));
        $year = $config->project->year;
        
        $html = "";
                
        $html .= "<table style='color:#666666;width:600px;font-family:Arial'>";
        
        $html .= "<tr>";
        $html .= "<td colspan='2'>";
        //$html .= "<div style='background-color:#999999;height:85px;position:relative;'>";
        //$html .= "<div style='color:#fff;font-weight:bold;position:absolute;top:10px;left:10px;'>BMW Aftersales Recognition Ride-and-Drive Event<br><span style='font-weight:normal;'>Greer, South Carolina</span></div>";
        //$html .= "<img src='".$image_path."/images/id_box_90x90-new.png' alt='' style='float:right;'/>";
        $html .= "<img src='".$image_path."/images/mail-confirmation-2014.png' alt='' />";
        $html .= "</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // header
        $html .= "<tr>";
        $html .= "<td colspan='2'>";
        $html .= "<div style=''>Congratulations!  <b>". $data['participant_name'] ."</b> is officially registered to attend the $year BMW Aftersales Recognition Ride-and-Drive Event in Greer, South Carolina.</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // spacing
        $html .= "<tr>";
        $html .= "<td colspan='2'>";
        $html .= "<br></br>";
        $html .= "</td>";
        $html .= "</tr>";
        
        //hotel
        $html .= "<tr>";
        $html .= "<td valign='top'>";
        $html .= "<div style=''>Hotel:</div>";
        $html .= "</td>";
        $html .= "<td valign='top'>";
        $html .= "<div style='color:#0088ce;'>".$data['hotel']."<br>One Parkway East<br>Greenville, SC  29615<br>Tel. (864) 297-0300</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // arrival date
        $html .= "<tr>";
        $html .= "<td>";
        $html .= "<div style=''>Arrival Date:</div>";
        $html .= "</td>";
        $html .= "<td>";
        $html .= "<div style='color:#0088ce;'>".$data['arrival_date']."</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // departure date
        $html .= "<tr>";
        $html .= "<td>";
        $html .= "<div style=''>Departure Date:</div>";
        $html .= "</td>";
        $html .= "<td>";
        $html .= "<div style='color:#0088ce;'>".$data['departure_date']."</div>";        
        $html .= "</td>";
        $html .= "</tr>";
        
        // occupancy
        $html .= "<tr>";
        $html .= "<td>";
        $html .= "<div style=''>Occupancy:</div>";
        $html .= "</td>";
        $html .= "<td>";
        $html .= "<div style='color:#0088ce;'>".$data['occupancy']."</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // price
        $html .= "<tr>";
        $html .= "<td>";
        $html .= "<div style=''>Room Price (rate + tax):</div>";
        $html .= "</td>";
        $html .= "<td>";
        $html .= "<div style='color:#0088ce;'>".$room_price."</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // guest
        if ($data['occupancy'] == 'Double') {
            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<div style=''>Guest:</div>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= "<div style='color:#0088ce;'>".$data['guest_name']."</div>";
            $html .= "</td>";
            $html .= "</tr>";
        }
        
        // spacing
        $html .= "<tr>";
        $html .= "<td colspan='2'>";
        $html .= "<br></br>";
        $html .= "</td>";
        $html .= "</tr>";        
        
        // contact
        $html .= "<tr>";
        $html .= "<td colspan='2'>";
        $html .= "<div style=''>if you need to make any changes to your reservation, please email <a href='mailto:".$config->site->admin->email."' style='color:#0088ce;'>".$config->site->admin->email."</a>.</div>";
        $html .= "</td>";
        $html .= "</tr>";
        
        $html .= "<tr>";
        $html .= "</tr>";
        
        $html .= "</table>";
        
        return $html;
        
    }
}

?>
