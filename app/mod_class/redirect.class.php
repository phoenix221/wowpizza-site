<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Redirect extends ActiveRecord
{

    function info()
    {
        $text = $this->get('lnk');
        if(!$this->get('multi_domain')){
            $text .= '<br><small style="color:#e8900b;">Все поддомены</small>';
        }else{
            $text .= '<br><small style="color:#d038a8;">'.$this->get('multi_domain').'</small>';
        }
        return $text;
    }

}

