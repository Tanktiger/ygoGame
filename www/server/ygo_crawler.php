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
    $query = 'SELECT id, url FROM cards_wikia';
    $result = $db->query($query);
    echo 'Begin with getting the Pictures' . PHP_EOL;
    while ($link = $result->fetch_assoc()) {
            echo $link['url'] . PHP_EOL;
            $dom = new DOMDocument('1.0', 'UTF-8');
            $curlResult = setCurl($link['url']);
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
			case 'Rank' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Level' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'ATK/DEF' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Card Number' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Fusion Material' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Materials' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Card Number' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
				break;
			case 'Propertys' :
				$values['category'] = $category;
				$values['value'] = $tr->lastChild->nodeValue;
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
	// " " bei fusions material - entfernen oder wie?
	$query = 'UPDATE cards_wikia
			 SET ' .
			 (isset($values['pic'])? "pic_url='" . $values['pic'] . "'," : 'pic_url=null,').
			 (isset($values['English'])? "name_en='" . $values['English'] . "'," : 'name_en=null,').
			 (isset($values['German'])? "name_de='" . $values['German'] . "'," : 'name_de=null,').
			 (isset($values['Alternate'])? "name_en_alternate='" . $values['Alternate'] . "'," : 'name_en_alternate=null,').
			 (isset($values['Attribute'])? 'attribute="' . $values['Attribute'] . '",' : 'attribute=null,').
			 (isset($values['Types'])? 'type="' . $values['Types'] . '",' : 'type=null,').
			 (isset($values['Rank'])? 'rank="' . $values['Rank'] . '",' : 'rank=null,').
			 (isset($values['Level'])? 'level=' . $values['Level'] . ',' : 'level=null,').
			 (isset($atk)? 'atk=' . $atk . ',' : 'atk=null,').
			 (isset($def)? 'def=' . $def . ',' : 'def=null,').
			 (isset($values['Card Number'])? 'code=' . $values['Card Number'] . ',' : 'code=null,').
			 (isset($values['Fusion Material'])? 'fusion_material="' . $values['Fusion Material'] . '",' : 'fusion_material=null,').
			 (isset($values['Materials'])? "material='" . $values['Materials'] . "'," : 'material=null,').
			 (isset($values['Property'])? "propertys='" . $values['Property'] . "'," : 'propertys=null')
			 . ' WHERE id = ' . $id . '';
	$result = $db->query($query);
	if ($result) {
	    echo '...saved...' . PHP_EOL;
	    file_put_contents('pics_wikia/' . name_replace($values['English']) . '.jpg', file_get_contents($values['pic']));
	} else {
	    echo '...failed...' . PHP_EOL;
	}
}
function setCurl ($url) {
    $curlUserAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:8.0.1) Gecko/20100101 Firefox/8.0.1 FirePHP/0.7.0';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERAGENT, $curlUserAgent);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

//     curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
//http://www.proxy-listen.de/Proxy/Proxyliste.html
    curl_setopt($curl, CURLOPT_PROXY, '124.119.50.254:80');
//     curl_setopt($curl, CURLOPT_PROXYPORT, 80);
//     curl_setopt($curl, CURLOPT_PROXYUSERPWD, 'DOMÄNE\benutzer:password');

    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);

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
    //� � � m�ssen nicht entfernt werden
    $name = str_replace(array('"', ' ', '!', '?', '/', 'ö', 'ä', 'ü'), array('', '_', '', '', '_', 'oe', 'ae', 'ue'), $name);
    return $name;
}