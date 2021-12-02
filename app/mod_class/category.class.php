<?php


class Category extends ActiveRecord
{

    function title(){
        return replece_fileds($this->get('title'));
    }

    function property_title(){
        if($this->get('property_title')){
            return $this->get('property_title');
        }
        return 'Выбрать свойства';
    }

    function admin_image()
    {
        return '<img src="'.d()->preview($this->get('image'), 'auto', 75).'" />';
    }


    function text()
    {
        return replece_fileds($this->get('text'));
    }

    function after_text()
    {
        return replece_fileds($this->get('after_text'));
    }

    function is_active_word()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    function is_more_word()
    {
        if($this->get('is_more')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    function active()
    {
        if(url(2) == $this->get('url')){
            return 'active';
        }
        return '';
    }

    function act_cls()
    {
        if($_GET['categoria'] == $this->get('id')){
            return 'active-cat';
        }
        return '';
    }
}

