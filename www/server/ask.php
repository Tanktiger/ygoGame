<?php
include 'cards.php';
// header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");
$card = new Cards();
$data = null;
$card->_init();

$limitOld = (isset($_GET['limitOld'])) ? $_GET['limitOld'] : 0;
$limitNew = (isset($_GET['limitNew'])) ? $_GET['limitNew'] : 500;
switch ($_GET['ask']) {
    case 'singleName' :
        $data = $card->getSingleCardByName($_GET['cardSearch']);
        break;
    case 'singleCode' :
        $data = $card->getSingleCardByCode($_GET['cardSearch']);
        break;
    case 'singleId' :
        $data = $card->getSingleCardById($_GET['id']);
        break;
    case 'main' :
        $data = $card->mainSearch($_GET['cardSearch']);
        break;
        //kommen später hinzu
//     case 'all' :
//         $data = $card->getAllCards($limitOld, $limitNew);
//         break;
//     case 'monster' :
//         $data = $card->getMonsterCards($limitOld, $limitNew);
//         break;
//     case 'spell' :
//         $data = $card->getCardsByType('Spell Card', $limitOld, $limitNew);
//         break;
//     case 'trap' :
//         $data = $card->getCardsByType('Trap Card', $limitOld, $limitNew);
//         break;
}
echo $_GET['callback'] . '(' . json_encode($data) . ');';