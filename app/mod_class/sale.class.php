<?php


class Sale extends ActiveRecord
{

    function period()
    {
        if($this->get('start_date') && $this->get('end_date')) {
            return $this->get('start_date')." - ".$this->get('end_date');
        }elseif($this->get('start_date') && !$this->get('end_date')){
            return 'с '.$this->get('start_date');
        }elseif(!$this->get('start_date') && $this->get('end_date')){
            return 'до '.$this->get('end_date');
        }
        return '';
    }

    function link_admin()
    {
        if(!d()->clist->count){
            d()->clist = d()->City;
        }
        list($domain, $maindomain, $zone) = explode(".", $_SERVER['HTTP_HOST']);

        $code = '';
        if($this->get('city_id') != 6){
            $code = d()->clist->find_by_id($this->get('city_id'))->code;
            $code .= '.';
        }

        if($_SERVER['HTTP_HOST']=='apfd.ru'){
            return '<a href="https://apfd.ru/sales/'.$this->get('url').'/" class="btn btn-mini" target="_blank">Просмотр</a>';
        }
        return '<a href="https://'.$code.$maindomain.'.'.$zone.'/sales/'.$this->get('url').'/" class="btn btn-mini" target="_blank">Просмотр</a>';
    }

    function secret_admin()
    {
        if($this->get('is_secret')){
            return '<strong style="color:purple;">Секретная</strong>';
        }
        return '<strong style="color:green;">Классическая</strong>';
    }

    function promocode_fu()
    {
        if($this->get('promocode')){
            return ': "'.$this->get('promocode').'"';
        }
        return '';
    }

    function is_active_word()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

}

