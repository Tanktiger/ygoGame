<?php
class Cards
{
    protected $db = null;

    public function _init()
    {
        $this->db = mysqli_connect('127.0.0.1', 'root', '', 'ygo');
    }

    public function mainSearch ($string) {
        $string = $this->rip_tags($string);
        $codeString = (is_numeric($string))? " OR code = " . $string : '';
        $sql = "SELECT * FROM cards WHERE name_de LIKE '%" . $string .
                                    "%' OR name_en LIKE '%" . $string .
                                    "%' OR name_en_alternate LIKE '%" . $string . "%'" .
                                     $codeString .
                                    " LIMIT 100";
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }
    public function getSingleCardByName($name) {
        $name = $this->rip_tags($name);
        $sql = 'SELECT * FROM cards
                WHERE name_de LIKE "%' . $name .
                '%" OR name_en LIKE "%' . $name .
                '%" OR name_en_alternate LIKE "%' . $name . '%"';
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }

    public function getSingleCardByCode($code) {

        $sql = 'SELECT * FROM cards WHERE code = ' . $code;
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }
    public function getSingleCardById($id) {

        $sql = 'SELECT * FROM cards WHERE id = ' . $id;
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }

    public function getAllCards($limitOld, $limitNew) {
        $sql = 'SELECT id, name_de, name_en, name_en_alternate FROM cards LIMIT' . $limitOld . ', ' . $limitNew;
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }

    public function getCardsByType ($type, $limitOld, $limitNew) {
        $sql = 'SELECT id, name_de, name_en, name_en_alternate FROM cards
                WHERE type LIKE "%' . $type . '%"LIMIT' . $limitOld . ', ' . $limitNew;
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }

    public function getMonsterCards ($limitOld, $limitNew) {
        $sql = 'SELECT id, name_de, name_en, name_en_alternate FROM cards
                WHERE atk IS NOT NULL AND def IS NOT NULL
                LIMIT' . $limitOld . ', ' . $limitNew;
        $result = $this->db->query($sql);
        return $this->createCardArray($result);
    }
    private function createCardArray ($result) {
        $cards = array();
        while ($card = $result->fetch_assoc()) {
            $cards[$card['id']] = $card;
        }
        return $cards;
    }
    private function rip_tags($string) {

        // ----- remove HTML TAGs -----
        $string = preg_replace ('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;

    }
}
