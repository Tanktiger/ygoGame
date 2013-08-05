<?php
class Cards
{
    public function getSingleCardByName($name) {
        $db = mysqli_connect('127.0.0.1', 'root', '', 'ygo');

        $sql = 'SELECT name, url, language FROM cards WHERE name LIKE "%' . $this->rip_tags($name) . '%"';
        $result = $db->query($sql);
        return $result->fetch_assoc();
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
