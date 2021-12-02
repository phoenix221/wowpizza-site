<?php


class Filter extends ActiveRecord
{

    function is_active_word()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    function f_class()
    {
        $id = ';'.$this->get('id').';';
        if($_GET['like']){
            $pos = strpos($_GET['like'], $id);
            if($pos!==false){
                return 'like';
            }

        }
        if($_GET['not']){
            $pos = strpos($_GET['not'], $id);
            if($pos!==false){
                return 'not';
            }

        }
        return '';
    }

}
