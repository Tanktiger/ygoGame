<?php
$db = new mysqli('127.0.0.1', 'root', '1337', 'ygo');
$db->set_charset('utf8');

$query = 'SELECT * FROM cards_wikia';
$result = $db->query($query);

echo 'start importing' . PHP_EOL;


while ($card = $result->fetch_assoc()) {
	echo $card['id'] . '...';
	if (isset($card['name_en']) || (isset($card['name_en']) && isset($card['code']))) {
	
		$insertQuery = 'INSERT INTO cards (name_de, name_en, name_en_alternate, pic_url, type, propertys, attribute, atk, def, level, rank, effect_en, effect_de, code, fusion_material, material)
					VALUES ("'. getNameDe($card) .'", "' 
							. getNameEn($card) . '", "' 
							. getNameAlt($card) . '", "' 
							. getPic($card) . '", "' 
							. getCardType($card) . '", "' 
							. getPropertys($card) . '", "' 
							. getAttribute($card) . '",	"' 
							. getATK($card) . '", "' 
							. getDEF($card) . '", ' 
							. getLevel($card) . ', ' 
							. getRank($card) . ', "' 
							. getEffectEn($card) . '", "' 
							. getEffectDe($card) . '", ' 
							. getCode($card) . ', "' 
							. getFusion($card) . '", "' 
							. getMaterial($card) . '")';
		
		$resultInsert = $db->query($insertQuery);
		if ($resultInsert) {
			echo 'saved' . PHP_EOL;
		} else {
			echo 'failed' . PHP_EOL;
		}
	} else {
		echo 'Missing Name for Card' . PHP_EOL;
	}
}

function getEffectEn ($card) {
	$matches = null;
	if (isset($card['effect'])) {
		preg_match('/English\s(.*)/m', $card['effect'], $matches);
		if (isset ($matches[1])) {
			return $matches[1];
		}
	}
	return null;
}

function getEffectDe ($card) {
	$matches = null;
	if (isset($card['effect'])) {
		preg_match('/German\s(.*)/m', $card['effect'], $matches);
		if (isset ($matches[1])) {
			return $matches[1];
		}
	}
	return null;
}

function getNameDe ($card) {
	if (isset($card['name_de'])) {
		return $card['name_de'];
	}
	return null;
}

function getNameEn ($card) {
	if (isset($card['name_en'])) {
		return $card['name_en'];
	}
	return null;
}

function getNameAlt ($card) {
	if (isset($card['name_en_alternate'])) {
		return $card['name_en_alternate'];
	}
	return null;
}

function getPic ($card) {
	if (isset($card['pic_url'])) {
		return $card['pic_url'];
	}
	return null;
}

function getCardType ($card) {
	if (isset($card['type'])) {
		return $card['type'];
	}
	return null;
}

function getPropertys ($card) {
	if (isset($card['propertys'])) {
		return $card['propertys'];
	}
	return null;
}

function getAttribute ($card) {
	if (isset($card['attribute'])) {
		return $card['attribute'];
	}
	return null;
}

function getATK ($card) {
	if (isset($card['atk'])) {
		return $card['atk'];
	}
	return null;
}

function getDEF ($card) {
	if (isset($card['def'])) {
		return $card['def'];
	}
	return null;
}

function getLevel ($card) {
	if (isset($card['level'])) {
		return $card['level'];
	}
	return "null";
}

function getRank ($card) {
	if (isset($card['rank'])) {
		return $card['rank'];
	}
	return "null";
}

function getCode ($card) {
	if (isset($card['code'])) {
		return $card['code'];
	}
	return "null";
}

function getFusion ($card) {
	if (isset($card['fusion_material'])) {
		return $card['fusion_material'];
	}
	return null;
}

function getMaterial ($card) {
	if (isset($card['material'])) {
		return $card['material'];
	}
	return null;
}