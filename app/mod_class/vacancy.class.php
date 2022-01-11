<?php


class Vacancy extends ActiveRecord
{

    function active()
    {
        if($this->get('is_active')){
            return '<strong style="color:darkgreen;">Да</strong>';
        }
        return '<strong style="color:darkorange;">Нет</strong>';
    }

    function main()
    {
        if($this->get('is_main')){
            return '<strong style="color:darkgreen;">Да</strong>';
        }
        return '<strong style="color:darkorange;">Нет</strong>';
    }

    function line()
    {
        $l = '';
        if($this->get('text3')){
            $l .= 'Условия / ';
        }
        if($this->get('text4')){
            $l .= 'Требования / ';
        }
        if($this->get('text5')){
            $l .= 'Обязанности / ';
        }
        if($l)$l = substr(trim($l),0,-1);
        return $l;
    }

    function adm_link()
    {
        d()->city = d()->City($this->get('city_id'));
        if(d()->city->id == 1){
            $d = 'wowpizza.ru';
        }else{
            $d = d()->city->code.'.wowpizza.ru';
        }
        if($this->get('is_main')){
            return '<a target="_blank" href="https://'.$d.'/vakansii/">https://'.$d.'/vakansii/</a>';
        }
        return '<a target="_blank" href="https://'.$d.'/vakansii/'.$this->get('url').'/">https://'.$d.'/vakansii/'.$this->get('url').'/</a>';
    }


}

