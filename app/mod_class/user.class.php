<?php


class User extends ActiveRecord
{

    function city_ttl()
    {
        return d()->City->where('code=?', $this->get('city'))->limit(0,1)->title;
    }

    function gender_word()
    {
        if($this->get('gender')=='male'){
            return 'Мужской';
        }
        if($this->get('gender')=='female'){
            return 'Женский';
        }
        return 'Не указан';
    }

    function haschild_word()
    {
        if($this->get('haschild')==1){
            return 'Есть';
        }
        if($this->get('haschild')==2){
            return 'Нет';
        }
        return 'Не указано';
    }

    function phone_not_seven()
    {
        return substr($this->get('phone'), 1);
    }

}

