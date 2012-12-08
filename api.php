<?php

require 'common.php';

/*
File for api requests
*/

require 'messages.php';

if (isset($_GET['action'])) {
  if ($_GET['action']=='summary') {
    $return = array();
    foreach ($history->getLastHistory() as $hist) {
        $hostarr = array('hostname' => $hist->addr,'date' => date('H:i:s d.M.Y',$hist->time));
        $status_code = $hist->msg_id;
        if ($status_code==-6||$status_code==-9) {
            $hostarr['message'] = strip_tags(sprintf($messages[$status_code],
                date('H:i:s d.M.Y',$hist->ri_max),date('H:i:s d.M.Y',$hist->ri_min)));
        } else if ($status_code==-3) {
            if (strlen($hist->msg)>0) {
                $hostarr['message'] = strip_tags(sprintf($messages[$status_code],'Debug: '.$hist->msg));
            } else {
                $hostarr['message'] = strip_tags(sprintf($messages[$status_code],'No debug message :('));
            }
        } else {
            $hostarr['message'] = strip_tags($messages[$status_code]);
        }
        $return[] = $hostarr;
    }
    echo json_encode($return);
  }
}

