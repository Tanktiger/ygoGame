<?php
echo 'start' . PHP_EOL;

$wikiUrl = 'http://yugioh.wikia.com';
$germanCardListUrl = array('http://yugioh.wikia.com/wiki/Category:German_Set_Card_Galleries',
'http://yugioh.wikia.com/wiki/Category:German_Set_Card_Galleries?pagefrom=Structure+Deck%3A+The+Dark+Emperor+%28TCG-DE-1E%29%0AStructure+Deck%3A+The+Dark+Emperor+%28TCG-DE-1E%29#mw-pages');
$englishCardListUrl = array(
		'http://yugioh.wikia.com/wiki/Category:English_Set_Card_Galleries',
		'http://yugioh.wikia.com/wiki/Category:English_Set_Card_Galleries?pagefrom=Phantom+Darkness+%28TCG-EN-1E%29#mw-pages',
);

$db = new mysqli('127.0.0.1', 'root', '1337', 'ygo');
$db->set_charset('utf8');

if (isset($_SERVER['argv'][1])) {
    getPicLinks($englishCardListUrl, $db);
} else {
//     getPicLinks($englishCardListUrl, $db);
    getCards($db);
}
//Sollten circa 11600 werden
function getPicLinks ($englishCardListUrl, $db) {
    echo 'Starte mit dem holen der Links fuer die Karten!' . PHP_EOL;
    $wikiUrl = 'http://yugioh.wikia.com';
    $cardlinks = array();
    foreach ($englishCardListUrl as $url) {
    	echo '.';
    	$curlResult = setCurl($url);
    // 	file_put_contents('wiki'.'.html', $curlResult);
    	$dom = new DOMDocument('1.0', 'UTF-8');
    	@$dom->loadHTML($curlResult);
    	$cardlinkArray = getCategoryLinks($dom);
    	$cardlinks = array_merge($cardlinks, $cardlinkArray);
    }
    echo 'getSingleCardLinks' . PHP_EOL;
    foreach ($cardlinks as $link) {
        echo 'new Category: ' . $link . PHP_EOL;
        $curlResult = setCurl($wikiUrl . $link);
        @$dom->loadHTML($curlResult);
        $div = $dom->getElementById('gallery-0');
        if (isset($div)) {
            $xpath = new DOMXPath($dom);
            $spans = $xpath->query('//div[@id="gallery-0"]/span[@class="wikia-gallery-item"]');
            $singleCardLinks = array();
            for ($i = $spans->length - 1; $i > -1; $i--) {
                echo '.';
                $singleCardLinks = $wikiUrl . $spans->item($i)->lastChild->firstChild->getAttribute('href');
                $query = 'INSERT IGNORE INTO cards_wikia (url) VALUES ("' . $singleCardLinks . '")';
                $result = $db->query($query);
            }
        } else {
    		$query = 'INSERT IGNORE INTO cards_wikia (url, type) VALUES ("'.$wikiUrl . $link.'", "fail")';
            $result = $db->query($query);
    	}
    }
    echo 'Fertig!' .PHP_EOL;
}

function getCards ($db) {
    $query = 'SELECT id, url FROM cards_wikia';//LIMIT 5647, 10000';
    $result = $db->query($query);
    echo 'Begin with getting the Pictures' . PHP_EOL;
    $stopCount = 0;
    while ($link = $result->fetch_assoc()) {
//     	$link['url'] = 'http://yugioh.wikia.com/wiki/CT08-EN004';
            echo $link['url'] . PHP_EOL;
            echo $link['id'] . PHP_EOL;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $curlResult = setCurl($link['url']);
//             file_put_contents('wiki'.'.html', $curlResult);
            @$dom->loadHTML($curlResult);

            $xpathCard = new DOMXPath($dom);
            $trs = $xpathCard->query('//table[@class="cardtable"]/tr');
            $cardValues = array();
            foreach ($trs as $tr) {
                $category = str_replace('&nbsp;', '', htmlentities(utf8_decode($tr->firstChild->nodeValue)));
                $values = getCategoryValue($category, $tr);
                if ($values) {
                    $cardValues[$values['category']] = $values['value'];
                }
            }
            saveCard($cardValues, $link['id'], $db);
            $stopCount++;
            if ($stopCount == 1) {
            	exit('stopCount reached');
            }
    }
    echo 'finish';
}

function getCategoryValue($category, $tr) {
	$values = array();
	//sind dann die beschreibungen
	if (preg_match('/Card descriptions/', $category)) {
		$values['category'] = 'desc';
		$values['value'] = $category;
	//Bild holen
	} else if(strlen($category) == 0) {
		$values['category'] = 'pic';
		$values['value'] = $tr->getElementsByTagName('a')->item(0)->getAttribute('href');
	} else {
		switch ($category) {
			case 'English' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'German' :
				$values['category'] = $category;
				$values['value'] = str_replace('Check translation', '', $tr->lastChild->nodeValue);
				break;
			case 'Alternate' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Attribute' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Types' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Type' :
				$values['category'] = $category . 's';
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Rank' :
				$values['category'] = $category;
				preg_match('/\d+/', $tr->lastChild->nodeValue, $matches);
				$values['value'] = $matches[0];
				break;
			case 'Level' :
				$values['category'] = $category;
				preg_match('/\d+/', $tr->lastChild->nodeValue, $matches);
				$values['value'] = $matches[0];
				break;
			case 'ATK/DEF' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Card Number' :
				$values['category'] = $category;
				preg_match('/\d+/', $tr->lastChild->nodeValue, $matches);
				$values['value'] = $matches[0];
				break;
			case 'Fusion Material' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Materials' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Propertys' :
				$values['category'] = $category;
				$values['value'] = str_replace(' ', '', $tr->lastChild->nodeValue);
				break;
			case 'Property' :
				$values['category'] = $category . 's';
				$values['value'] = str_replace(' ', '', $tr->lastChild->nodeValue);
				break;
		}
	}
	return $values;
}

function saveCard ($values, $id, $db) {
	$atk = $def = null;
	if (isset($values['ATK/DEF'])) {
		$vals = preg_split('/\//', $values['ATK/DEF']);
		$atk = $vals[0];
		$def = $vals[1];
	}
	//Erst das Bild speichern und dann link für das Bild bauen
// 	    file_put_contents('/pics_wikia/' . name_replace($values['English']) . '.jpg', file_get_contents($values['pic']));
	// " " bei fusions material - entfernen oder wie?
	//Problem mit " , ' im Namen und in der Beschreibung - wie lösen?
	$query = 'UPDATE cards_wikia
			 SET ' .
			 (isset($values['pic'])? "pic_url='" . $values['pic'] . "'," : 'pic_url=null,').
			 (isset($values['English'])? "name_en='" . name_replace_db($values['English']) . "'," : 'name_en=null,').
			 (isset($values['German'])? "name_de='" . name_replace_db($values['German']) . "'," : 'name_de=null,').
			 (isset($values['Alternate'])? "name_en_alternate='" . name_replace_db($values['Alternate']) . "'," : 'name_en_alternate=null,').
			 (isset($values['Attribute'])? 'attribute="' . $values['Attribute'] . '",' : 'attribute=null,').
			 (isset($values['Types'])? 'type="' . $values['Types'] . '",' : 'type=null,').
			 (isset($values['Rank'])? 'rank=' . $values['Rank'] . ',' : 'rank=null,').
			 (isset($values['Level'])? 'level=' . $values['Level'] . ',' : 'level=null,').
			 (isset($atk)? 'atk="' . $atk . '",' : 'atk=null,').
			 (isset($def)? 'def="' . $def . '",' : 'def=null,').
			 (isset($values['Card Number'])? 'code=' . $values['Card Number'] . ',' : 'code=null,').
			 (isset($values['desc'])? "effect='" . name_replace_db(html_entity_decode($values['desc'])) . "'," : 'effect=null,').
			 (isset($values['Fusion Material'])? 'fusion_material="' .name_replace_db($values['Fusion Material']) . '",' : 'fusion_material=null,').
			 (isset($values['Materials'])? "material='" . name_replace_db($values['Materials']) . "'," : 'material=null,').
			 (isset($values['Propertys'])? "propertys='" . $values['Propertys'] . "'" : 'propertys=null')
			 . ' WHERE id = ' . $id . '';
	$result = $db->query($query);
	if ($result) {
	    echo '...saved...' . PHP_EOL;
	} else {
	    echo '...save card failed...' . PHP_EOL;
	    var_dump('fail');
	    exit($query);
	}
}
function setCurl ($url) {
    $curlUserAgent = 'Mozilla/5.0 (X11; U; Linux i686; en-US) 
            AppleWebKit/532.4 (KHTML, like Gecko) 
            Chrome/4.0.233.0 Safari/532.4';
    $curl = curl_init();
//     curl_setopt($curl, CURLOPT_USERAGENT, $curlUserAgent);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
//http://www.proxy-listen.de/Proxy/Proxyliste.html
    curl_setopt($curl, CURLOPT_PROXY, '64.209.159.223:3128');

    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

    $curlResult = curl_exec($curl);
//     file_put_contents('wiki'.'.html', $curlResult);
    curl_close($curl);
    return $curlResult;
}

function getCategoryLinks($dom) {
    $linkArray = array();
    $div = $dom->getElementById('mw-pages');
    foreach ($div->getElementsByTagName('li') as $li) {
        foreach($li->getElementsByTagName('a') as $links){
            $linkArray[] = $links->getAttribute('href');
        }
    }
    return $linkArray;
}
function name_replace ($name) {
    //ï¿½ ï¿½ ï¿½ mï¿½ssen nicht entfernt werden
    $name = str_replace(array('"', ' ', '!', '?', '/', 'ö', 'ä', 'ü', ':', '.'), array('', '_', '', '', '_', 'oe', 'ae', 'ue', '', ''), $name);
    return $name;
}
function name_replace_db ($name) {
	//ï¿½ ï¿½ ï¿½ mï¿½ssen nicht entfernt werden
	$name = str_replace(array('"', "'", 'ö', 'ä', 'ü'), array('', '', 'oe', 'ae', 'ue'), $name);
	return $name;
}