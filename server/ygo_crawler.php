<?php
echo 'start' . PHP_EOL;

$wikiUrl = 'http://yugioh.wikia.com';
$germanCardListUrl = array('http://yugioh.wikia.com/wiki/Category:German_Set_Card_Galleries',
'http://yugioh.wikia.com/wiki/Category:German_Set_Card_Galleries?pagefrom=Structure+Deck%3A+The+Dark+Emperor+%28TCG-DE-1E%29%0AStructure+Deck%3A+The+Dark+Emperor+%28TCG-DE-1E%29#mw-pages');
$englishCardListUrl = array(
		'http://yugioh.wikia.com/wiki/Category:English_Set_Card_Galleries',
		'http://yugioh.wikia.com/wiki/Category:English_Set_Card_Galleries?pagefrom=Phantom+Darkness+%28TCG-EN-1E%29#mw-pages',
);

$db = new mysqli('127.0.0.1', 'root', '', 'ygo');
$db->set_charset('utf8');

if (isset($_SERVER['argv'][1])) {
    getCards($db);
} else {
    getPicLinks($englishCardListUrl, $db);
    getCards($db);
}

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
    	break;
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
                $query = 'INSERT IGNORE INTO cards (url) VALUES ("' . $singleCardLinks . '")';
                $result = $db->query($query);
            }
        } else {
    		$query = 'INSERT IGNORE INTO cards (name, url, language) VALUES ("'.$wikiUrl . $link.'","' . $wikiUrl . $link . '", "fail")';
            $result = $db->query($query);
    	}
    }
    echo 'Gespeicherte Links: ' . count($cardlinks) . PHP_EOL;
}

function getCards ($db) {
    $query = 'SELECT url FROM cards';
    $result = $db->query($query);
    echo 'Begin with getting the Pictures' . PHP_EOL;
    while ($link = $result->fetch_assoc()) {
            echo '.';
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
            var_dump($cardValues);
            exit();
            // 	    foreach ($div->getElementsByTagName('a') as $picThumb) {
            //
            // 	        $cardLink = $picThumb->getAttribute('href');
            // 	        preg_match('/\/wiki\/File\:(.*)/', $cardLink, $cardName);
            // 	        $curlResult = setCurl($wikiUrl . $cardLink);
            // 	        @$dom->loadHTML($curlResult);
            // 	        $xpath = new DOMXPath($dom);
            // 	        $imgUrl = $xpath->evaluate("string(//div[@class='fullMedia']/a/@href)");
            // 	        if (isset($cardName[1])) {
            // 	            $query = 'INSERT IGNORE INTO cards (name, url, language) VALUES ("'.$cardName[1].'","' . $imgUrl . '", "de")';
            // 	            file_put_contents('pics_wikia/'.$cardName[1], file_get_contents($imgUrl));
// 	        }
// 	    }
    }
    echo 'finish';
}

function getCategoryValue($category, $tr) {
	$values = array();
	//sind dann die beschreibungen
	if (strlen($category) > 255) {

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
		}
	}
	return $values;
}
function setCurl ($url) {
    $curlUserAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:8.0.1) Gecko/20100101 Firefox/8.0.1 FirePHP/0.7.0';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERAGENT, $curlUserAgent);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
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