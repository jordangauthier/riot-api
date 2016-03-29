<?php

/**
 * Created by PhpStorm.
 * User: Jordan Gauthier
 * Date: 3/26/2016
 * Time: 2:52 PM
 */
class jrapi
{
    private $_region;
    private $_url;
    private $_error = array(
        '400',
        '401',
        '404',
        '429',
        '500',
        '503',
    );

    //api key
    const API_KEY = 'a8dee79a-8f00-44a5-b5ab-ce734c69b770';
    const V_1_2 = '/v1.2/';
    const V_1_4 = '/v1.4/';
    const V_1_3 = '/v1.3/';
    const V_2_5 = '/v2.5/';
    const V_2_2 = '/v2.2/';
    const V_2_4 = '/v2.4/';
    const NO_RESULT = false;
    const ERR_FALSE = false;
    const NB_FREE_CHAMP = 9;

    public function __construct($region)
    {
        $this->_region = $region;
        $this->_url = 'https://'.$this->_region.'.api.pvp.net/api/lol/'.$this->_region;
    }

    /*
     * fais une requete au serveur de lol et renvoi un tableau conetenant sois les info demander ou un code derreur avec une definition
     */
    public function request($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);

        $rep = json_decode($result,true);

        $errTest = $this->err_test($rep); // on execute la fonction pour savoir si dans la requete ($rep) il y a un code derreur

        if($errTest != self::ERR_FALSE) //si il a presence dun code derreur on $rep vaut maintenant un array contenant le code derreur et son explication
            $rep = $errTest;

        return $rep;
    }

    //retourne un tableau contenant les id des free champion de la semaine
    public function getFreeChampId()
    {
        $url = $this->_url.self::V_1_2.'champion?freeToPlay=true&api_key='.self::API_KEY;
        $rep = $this->request($url);

        $idArray = array();

        for($i = 0 ; $i <= self::NB_FREE_CHAMP ; $i++)
            array_push($idArray , $rep['champions'][$i]['id']);

        return $idArray;
    }

    //renvoie les nom des champion ou true si erreur
    public function getFreeChampName(array $idTab)
    {
        foreach($idTab as $cle => $ele)
        {
            if(!is_int($cle))
                return trigger_error('Les cle du tableau passer en parametre doive etre de type int' , E_USER_WARNING);
        }

        for($i = 0 ; $i <= count($idTab) -1 ; $i++)
        {
            $idTab[$i] = $this->getChampName($idTab[$i]);

            $test = $this->checkReturn($idTab[$i]);

            if($test)
                return $result = $test;
        }

        return $idTab;
    }

    public function getChampName($idChamp)
    {
        $url = 'https://global.api.pvp.net/api/lol/static-data/'.$this->_region.self::V_1_2.'champion/'.$idChamp.'?api_key='.self::API_KEY;
        $rep = $this->request($url);

        if(isset($rep['name']))
            $result = $rep['name'];
        else
            $result = $rep;

        return $result;
    }

    //retourne le id d'un summoner
    public function getSummonerIdByName($name)
    {
        $result = '';

        if(is_string($name))
        {
            $name = strtolower($name);
            $name = str_replace(" " , "" , $name);
            $name = rawurlencode($name);
            $url = $this->_url.jrapi::V_1_4.'summoner/by-name/'.$name.'?api_key='.jrapi::API_KEY;
            $rep = $this->request($url);

            if(isset($rep[$name]))
                $result = $rep[$name]['id'];
            else
                $result = $rep;

            return $result;
        }
        else
            trigger_error('Le parametre name doit abosulement etre de type string' , E_USER_WARNING);

    }

    //retourne le nom d'un summoner selon son id
    public function getSummonerNameById($id)
    {
        if(is_int($id))
        {
            $url = $this->_url.jrapi::V_1_4.'summoner/'.$id.'?api_key='.jrapi::API_KEY;
            $rep = $this->request($url);

            if(isset($rep[$id]['name']))
                return $rep[$id]['name'];
            else
                return $rep;
        }
        else
            trigger_error('Le parametre id doit absolument etre de type int' , E_USER_WARNING);
    }

    /*
     * retourne un tableau contenant les stats desirer pour la saison 16
     * peu retourner false dans le cas ou un id existe mais quil na pas de stats pour le $nb specifier
     *
     * 2ieme parametre:
     * -1 pour retourner le tableau au complet
     * 0 = cap5v5
     * 1 = coopvsAI
     * 2 = rankedTeam3v3
     * 3 = rankedTeam5v5
     * 4 = unraked3v3
     * 5 = odinunranked
     * 6 = rankedsolo5v5
     * 7 = aramUnranked5v5
     * 8 = unranked
     */
    public function getStatsById($id,$nb = -1)
    {
        if(is_int($nb) and is_int($id))
        {
            $url = $this->_url.jrapi::V_1_3.'stats/by-summoner/'.$id.'/summary?season=SEASON2016&api_key='.jrapi::API_KEY;
            $rep = $this->request($url);

            if($nb == -1)
                $result = $rep;
            else if($nb > -1 && $nb < 9)
            {
                if(isset( $rep['playerStatSummaries'][$nb]))
                    $result =  $rep['playerStatSummaries'][$nb];
                else
                    $result = self::NO_RESULT;
            }

            else
                $result = trigger_error('NB doit etre un chiffre entre 0 compris et 8 compris' , E_USER_WARNING);
        }
        else
            $result = trigger_error('le id doit absoluement etre un chiffre' , E_USER_WARNING);

        return $result;
    }

    /*
     * teste si il y a presence ou non derreur dans un tableau
     */
    public function err_test($varToTest)
    {

        if(isset($varToTest['status']['status_code']))
        {
            $statusCode = $varToTest['status']['status_code'];

            for($i = 0 ; $i <= count($this->_error) -1 ; $i++)
            {
                if($statusCode == $this->_error[$i])
                    return array(
                        $this->_error[$i] => $varToTest['status']['message']
                    );
                else
                    $result = self::ERR_FALSE;
            }
        }
        else
            $result = self::ERR_FALSE;

        return $result;
    }

    //retourne faux si il na pas derreur et true si il a une erreur
    public function checkReturn($var)
    {
        $error = false;

        for($i = 0 ; $i <= count($this->_error) -1 ; $i++)
        {
            if(isset($var[$this->_error[$i]]))
                $error = true;
        }

        return $error;
    }
}
