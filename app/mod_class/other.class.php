<?php


class Other extends ActiveRecord
{

    function items_arr()
    {
        return explode(',', $this->get('text'));
    }

    function type()
    {
        if($this->get('max')==1){
            return 'radio';
        }
        return 'checkbox';
    }

    function is_active_other()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

}
