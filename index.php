<?php

require 'common.php';

if (isset($_GET['view'])) {
    $view = (ctype_alpha($_GET['view'])) ? $_GET['view'] : 'main';
} else { $view = 'main'; }

//TODO: fix a better way to make all $view except defined one display main 
switch ($view) {
    case 'history':
        $view_page='history';
        if (isset($_GET['id'])&&ctype_digit($_GET['id'])) {
            $view_id = intval($_GET['id']);
        } else { $view_page='main'; }
    break;
    case 'main':
    default:
        $view_page='main';
}


$messages = array(
     0 => '<p class="text-success">OK - host works!</p>',
    -1 => '<p class="text-error">Error - Too little content returned. Something is wrong.</p>',
    -2 => '<p class="text-error">Error - Could not find any href pointing to routerInfo files.</p>',
    -3 => '<p class="text-error" title="%s">Error - Problems with dat files from reseed hosts.</p>',
    -4 => '<p class="text-warning">Warning - Host contains routerInfo with published date older than a day.</p>',
    -5 => '<p class="text-error">Error - Network problems or host is down.</p>',
    -6 => '<p class="text-error">Error - Host contains routerInfo with published date older than three days. (Newest: %s, Oldest: %s)</p>',
    -7 => '<p class="text-warning">Warning - Host contains routerInfo with published date older than two days.</p>',
);

//TODO: Template system.. smarty?
?><DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Reseed monitoring system</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
</head>
<body>
<div class="container">
<h1>Reseed monitoring (BETA)</h1><br>
Short information:<br>
<pre>
This site fetches information from reseed hosts once each hour. Then it checks for href="routerInfo* in the returned content. If routerInfo files found, it will download routerInfo files and check "Published on" date via a python script reading RIs.
</pre>
<br>
<b>What can make a reseed host fail this test?</b><br>
<ol>
    <li>If the content from host is less than 100 characters.</li>
    <li>If no href="routerInfo* is found in content from host.</li>
    <li>If none of the routerInfo test samples was published less than a day ago.</li>
    <li>Network problems.</li>
</ol>
<br>
<p class="text-info">Source for this monitoring system is hosted at <a href="http://git.repo.i2p/w/rsstatus.git">http://git.repo.i2p/w/rsstatus.git</a>.</p>
<br>
<?php

if ($view_page=='main') {
    
?>
<br>
<table class="table table-hover">
<thead>
    <tr>
        <th>Host</th><th>Last check</th><th>Status</th>
    </tr>
</thead>
<tbody>
<?php


foreach ($history->getLastHistory() as $hist) {
    echo '<tr><td><a href="?view=history&amp;id='.$hist->host_id
            .'">'.$hist->addr.'</a></td><td><p class="text-info">'.
            date('H:i:s d.M.Y',$hist->time).'</p></td><td>';
    $status_code = $hist->msg_id;
    if ($status_code==-6) {
        echo '<b>'.sprintf($messages[$status_code],
                date('H:i:s d.M.Y',$hist->ri_max),date('H:i:s d.M.Y',$hist->ri_min)).'</b>'."\n";
    } else if ($status_code==-3) {
        if (strlen($hist->msg)>0) {
            echo '<b>'.sprintf($messages[$status_code],'Debug: '.$hist->msg).'</b>';
        } else {
            echo '<b>'.sprintf($messages[$status_code],'No debug message :(').'</b>';
        }
    } else {
        echo '<b>'.$messages[$status_code].'</b>'."\n";
    }
    echo '</td></tr>';
}

?>
</tbody>
</table>
<?php
} else if ($view_page=='history') {
    $hist_array = $history->getHistory($view_id);
    if (is_array($hist_array)) {
        ?>
<br>
<table class="table table-hover">
<thead>
    <tr>
        <th>Check time</th><th>Status</th>
    </tr>
</thead>
<tbody>
<?php
foreach ($hist_array as $hist) {
    echo '<tr><td><p class="text-info">'.date('H:i:s d.M.Y',$hist->time).'</p></td><td>';
    $status_code = $hist->msg_id;
    if ($status_code==-6) {
        echo '<b>'.sprintf($messages[$status_code],
                date('H:i:s d.M.Y',$hist->ri_max),date('H:i:s d.M.Y',$hist->ri_min)).'</b>'."\n";
    } else if ($status_code==-3) {
        if (strlen($hist->msg)>0) {
            echo '<b>'.sprintf($messages[$status_code],'Debug: '.$hist->msg);
        } else {
            echo '<b>'.sprintf($messages[$status_code],'No debug message :(');
        }
    } else {
        echo '<b>'.$messages[$status_code].'</b>'."\n";
    }
    echo '</td></tr>';
}
?>
</tbody>
</table>
        <?php
    } else {
        // TODO: fix something better
        die("Server error. Please check back later.");
    }
} else {
    // This should never happen with current code
    die("...");
}
?>
</div>

</body>
</html>
