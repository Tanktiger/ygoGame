<?php
header('Content-Type: image/jpeg');
if(isset($_REQUEST['file'])){
    $file = $_REQUEST['file'];
    echo file_get_contents('cards/' . $file);
}
