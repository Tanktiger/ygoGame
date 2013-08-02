<?php
include 'cards.php';
header('Content-type: application/json');
$card = new Cards();
$data = $card->getSingleCardByName( $_GET['cardSearch']);

echo $_GET['jsoncallback'] . '(' . json_encode($data) . ');';