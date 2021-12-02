<?php


class Promocode extends ActiveRecord
{

    function is_active_word()
    {
        $check = 1;
        if($this->get('start_date') || $this->get('end_date')){
            get_city();
            $d = date('U') + d()->city->timezone*3600;
            if($this->get('start_date') && $this->get('start_date') > $d){
                $check = 0;
            }
            if($this->get('end_date') && $this->get('end_date')+86399 < $d){
                $check = 0;
            }
        }
        if($this->get('is_single') && $this->get('used')){
            $check = 0;
        }
        if($this->get('is_active') && $check){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }


}
