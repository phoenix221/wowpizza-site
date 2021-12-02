<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Seoparam extends ActiveRecord
{

    function title()
    {
        $text = replece_fileds($this->get('title'));
        return $text;
    }

    function title_nof()
    {
        return $this->get('title');
    }

    function description()
    {
        $text = replece_fileds($this->get('description'));
        return $text;
    }

    function keywords()
    {
        $text = replece_fileds($this->get('keywords'));
        return $text;
    }

    function info()
    {
        $text = '';
        if($this->get('type')==1){
            $text .= 'Страница: <strong>'.$this->get('page_url').'</strong>';
        }elseif($this->get('type')==2){
            if($this->get('razdel')=='news'){
                $razdel = 'Новости детально';
            }elseif($this->get('razdel')=='sales'){
                $razdel = 'Акции детально';
            }elseif($this->get('razdel')=='categories'){
                $razdel = 'Разделы с товарами';
            }elseif($this->get('razdel')=='products'){
                $razdel = 'Товары детально';
            }

            $text .= 'Раздел: <strong>'.$razdel.'</strong>';
        }

        if(!$this->get('multi_domain')){
            $text .= '<br><small style="color:#e8900b;">Все поддомены</small>';
        }else{
            $text .= '<br><small style="color:#d038a8;">'.$this->get('multi_domain').'</small>';
        }
        return $text;
    }

}

