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

    //api key
    const API_KEY = 'YOU API KEY HERE';
    const V_1_2 = '/v1.2/';
    const V_1_4 = '/v1.4/';
    const V_1_3 = '/v1.3/';
    const V_2_5 = '/v2.5/';
    const V_2_2 = '/v2.2/';
    const V_2_4 = '/v2.4/';

    public function __construct($region)
    {
        $this->_region = $region;
        $this->_url = 'https://'.$this->_region.'.api.pvp.net/api/lol/'.$this->_region;
    }

    public function request($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);

        $rep = json_decode($result,true);

        return $rep;
    }

    //retourne le id d'un summoner
    public function getSummonerIdByName($name)
    {
        if(is_string($name))
        {
            $name = strtolower($name);
            $name = str_replace(" " , "" , $name);
            $name = rawurlencode($name);
            $url = $this->_url.jrapi::V_1_4.'summoner/by-name/'.$name.'?api_key='.jrapi::API_KEY;
            $rep = $this->request($url);
            return $rep[$name]['id'];
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
            return $rep[$id]['name'];
        }
        else
            trigger_error('Le parametre id doit absolument etre de type int' , E_USER_WARNING);
    }


    /*
     * retourne un tableau contenant les stats desirer pour la saison 16
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
                return $rep;
            else
                return $rep['playerStatSummaries'][$nb];
        }
        else
            trigger_error('Les variable nb et id se doivent d\'etre de type int' , E_USER_WARNING);
    }

}
