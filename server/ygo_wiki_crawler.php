<?php
$wikiUrl = 'http://yugioh-wiki.de';
$urlArray = array(
'http://yugioh-wiki.de/wiki/Kategorie:Yugioh_Karte',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Zerst%C3%B6rersaurier#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Wolkian+-+Toxische+Wolke#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Vulcan+the+Divine#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Unity#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Tiefsee-Kiemenschlitzaal#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Sto%C3%9Ftruppen+der+Eisbarriere#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Silberfl%C3%BCgel#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Schwarz+Gefl%C3%BCgelte+Sturzkampfbombe#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Salamandra#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Rauchgranate+des+Diebs#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Pfau#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Ninjitsu-Kunst+der+Schattenversiegelung#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Morphtronisches+Kabel#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Maschinenwesen+Soldat#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Leuchtsterndrache#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Krallenstrecker#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Kamionwizard#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Hinabst%C3%BCrzen#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Grizzly%2C+the+Red+Star+Beast#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Ghostrick+Jiangshi#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Garde+des+Pharao#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Finsterlicht#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Evo-Vielfalt#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Eisenketten-Schlange#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Drachenfluch#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Deltakr%C3%A4he+-+Antiumkehrung#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=B%C3%B6ser+HELD+Lightning+Golem#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Belial+-+Marquis+der+Finsternis#mw-pages',
'http://yugioh-wiki.de/w/index.php?title=Kategorie:Yugioh_Karte&pagefrom=Archfiend+Palace+-+The+Labyrinth+of+Fiends#mw-pages'
);
$db = new mysqli('127.0.0.1', 'root', '1337', 'ygo');
$db->set_charset('utf8');

echo 'Starte mit dem holen der Links f�r die Karten!' . PHP_EOL;
$cardlinks = array();
foreach ($urlArray as $url) {
    echo '.';
    $curlResult = setCurl($url);
    file_put_contents('wiki'.'.html', $curlResult);
    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($curlResult);
    $cardlinkArray = getCategoryLinks($dom);
    $cardlinks = array_merge($cardlinks, $cardlinkArray);
    break;
}
echo 'Gefunden Links: ' . count($cardlinks) . PHP_EOL;

echo 'starte mit holen der Karten:' . PHP_EOL;
foreach ($cardlinks as $name => $link) {
	echo '.';
    $curlResult = setCurl($wikiUrl . $link);
//     file_put_contents('wiki2'.'.html', $curlResult);
    @$dom->loadHTML($curlResult);
    $xpathDom = new DOMXPath($dom);
    //auf der Seite gucken
    //anhand der Tabellen struktur bei den Karten orientieren
    // mit xpath navigieren
    //muss anhand des th herausgesucht werden!
    $pcitureLink = $xpathDom->evaluate("string(//div[@id='mw-content-text']/table[1]/tr[2]/td[1]/a/img/@src)");
    $effect = $xpathDom->evaluate("string(//div[@id='mw-content-text']/table[1]/tr[6]/td[1])");
    $cardCode = $xpathDom->evaluate("string(//div[@id='mw-content-text']/table[1]/tr[4]/td[1])");
	$cardCode = str_replace(' ', '', $cardCode);
// 	echo $name;
    $query = 'INSERT IGNORE INTO cards_wiki (name, url, code, effect) VALUES ("'.$name.'","' . $wikiUrl . $pcitureLink . '", "' . $cardCode . '","' . $effect . '")';
    $result = $db->query($query);
    file_put_contents('pics_wiki/' . $name . '.jpg', file_get_contents($wikiUrl . $pcitureLink));#
}
echo 'finish';

function name_replace ($name) {
	//� � � m�ssen nicht entfernt werden
	$name = str_replace(array('"', ' ', '!', '?', '/', 'ö', 'ä', 'ü'), array('', '_', '', '', '_', 'oe', 'ae', 'ue'), $name);
	return $name;
}

function setCurl ($url) {
    $curlUserAgent = 'Mozilla/5.0 (X11; Linux x86_64; rv:8.0.1) Gecko/20100101 Firefox/8.0.1 FirePHP/0.7.0';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERAGENT, $curlUserAgent);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $curlResult = curl_exec($curl);
//         file_put_contents('wiki'.'.html', $curlResult);
    curl_close($curl);
    return $curlResult;
}

function getCategoryLinks($dom) {
    $linkArray = array();
    $div = $dom->getElementById('mw-pages');
    foreach ($div->getElementsByTagName('li') as $li) {
        foreach($li->getElementsByTagName('a') as $links){
            $linkArray[name_replace(utf8_decode($links->getAttribute('title')))] = $links->getAttribute('href');
        }
    }
    return $linkArray;
}