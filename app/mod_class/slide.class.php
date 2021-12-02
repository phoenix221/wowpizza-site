<?php


class Slide extends ActiveRecord
{

    function is_active_word()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    function link_admin()
    {
        $url = $this->get('url');
        if(strpos($url,'http') !== false){
            return '<a href="'.$this->get('url').'" class="btn btn-mini" target="_blank">Просмотр</a>';
        }

        if(!d()->clist->count){
            d()->clist = d()->City;
        }
        list($domain, $maindomain, $zone) = explode(".", $_SERVER['HTTP_HOST']);

        $code = '';
        if($this->get('city_id') != 6){
            $code = d()->clist->find_by_id($this->get('city_id'))->code;
            $code .= '.';
        }

        if($_SERVER['HTTP_HOST']=='apf.su'){
            return '<a href="https://apf.su/'.$this->get('url').'" class="btn btn-mini" target="_blank">Просмотр</a>';
        }
        if($_SERVER['HTTP_HOST']=='foodcosmos.ru'){
            return '<a href="https://foodcosmos.ru/'.$this->get('url').'" class="btn btn-mini" target="_blank">Просмотр</a>';
        }
        return '<a href="https://'.$code.$maindomain.'.'.$zone.'/'.$this->get('url').'" class="btn btn-mini" target="_blank">Просмотр</a>';
    }


}
