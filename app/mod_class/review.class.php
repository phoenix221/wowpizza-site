<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Review extends ActiveRecord
{

    function text_1c()
    {
        return str_replace('<br>', ' / ', $this->get('text'));
    }

    function status_word()
    {
        if($this->get('status')==1)return 'В работе';
        if($this->get('status')==2)return 'Обработан';
        if($this->get('status')==5)return 'В архиве';
        return 'Новый';
    }

    function type_word()
    {
        if($this->get('type')=='soc')return 'Соц сети';
        if($this->get('type')=='sluzh')return 'Служба качества';
        return $this->get('type');
    }

}

