<?php
include 'cards.php';
// header('Content-type: application/json');
$card = new Cards();
$data = null;

$card->_init();
$limitOld = 0;
$limitNew = 100;
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
    case 'all' :
        $data = $card->getAllCards($limitOld, $limitNew);
    case 'main' :
        $data = $card->mainSearch($_GET['cardSearch']);
        break;
    case 'monster' :
        $data = $card->getMonsterCards($limitOld, $limitNew);
        break;
    case 'spell' :
        $data = $card->getCardsByType('Spell Card', $limitOld, $limitNew);
        break;
    case 'trap' :
        $data = $card->getCardsByType('Trap Card', $limitOld, $limitNew);
        break;
}
echo $_GET['jsoncallback'] . '(' . json_encode($data) . ');';