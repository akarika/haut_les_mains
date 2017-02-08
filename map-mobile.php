<?php

class map_mobile
{

    private $lat;
    private $lng;
    private $adress;


    public function __construct($position)
    {

        $this->setLat($position['lat']);
        $this->setLng($position['lng']);
        $this->setAdress($position['address']);


    }


    private function user()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/iPhone/', $useragent) && preg_match('/Mobile/', $useragent)) {
            $this->iphone();
        } elseif (preg_match('/Android/', $useragent) && preg_match('/Mobile/', $useragent)) {
            $this->android();
        } else {
            $this->def_mo();
        }
    }

    public function map()
    {
        $this->user();
    }

    public function iphone()
    {
        echo $this->getAdress()."<br>";
        $this->setAdress(urlencode($this->getAdress()));

        echo "<a href=\"http://maps.apple.com/?daddr=" . $this->getAdress() . "&sll" . $this->getLat() . "," . $this->getLng() . "&z=10&t=m\">Localiser sur son mobile</a>";
    }

    public function android()
    {
        echo $this->getAdress()."<br>";
        $this->setAdress(urlencode($this->getAdress()));

        echo "<a href=\"http://www.google.com/maps/place/" . $this->getAdress() . "/ " . $this->getLat() . ", " . $this->getLng() . "\">Localiser sur son mobile</a>";
    }

    public function def_mo()
    {

        echo "<p>" . $this->getAdress() . "</p>";
    }

    /**
     * @param mixed $lat
     */
    private function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @param mixed $lng
     */
    private function setLng($lng)
    {
        $this->lng = $lng;
    }


    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $adress
     */
    private function setAdress($adress)
    {
        $this->adress = $adress;
    }

    public function getAdress()
    {
        return $this->adress;
    }
}