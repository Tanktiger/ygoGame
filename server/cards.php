<?php
class Cards
{
    public function getSingleCardByName($name) {
        $db = mysqli_connect('127.0.0.1', 'root', '', 'ygo');

        $sql = 'SELECT name, url, language FROM cards WHERE name LIKE "%' . $name . '%"';
        $result = $db->query($sql);
        return $result->fetch_assoc();
    }
}
