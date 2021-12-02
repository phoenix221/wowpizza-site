<?php


class Property extends ActiveRecord
{

    function is_default_word()
    {
        if($this->get('is_default')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    // цена
    function price()
    {
        // если не установлена галочка, Не учавствует в акции: скидка за самовывоз
        $not = $this->get('not_dd');

        // цена с учетом скидки за самовывоз
        $price = get_discount_price($this->get('price'), $not);

        return $price;
    }

    // старая цена
    function old_price()
    {
        d()->old_price = 0;

        // если не установлена галочка, Не учавствует в акции: скидка за самовывоз
        $not = $this->get('not_dd');

        // цена с учетом скидки за самовывоз
        $price = get_discount_price($this->get('price'), $not);
        // старая цена, если она отличается от новой
        if ($price != $this->get('price')) {
            d()->old_price = $this->get('price');
        }

        return d()->old_price;
    }

    function weight_type()
    {
        return str_replace('.', '', $this->get('weight_type'));
    }

    function weight_adm()
    {
        return $this->get('weight').' '.str_replace('.', '', $this->get('weight_type'));
    }

    function is_active_word()
    {
        if($this->get('is_active')){
            return '<strong style="color:green;">Да</strong>';
        }
        return '<strong style="color:orangered;">Нет</strong>';
    }

    function is_stop_word()
    {
        if($this->get('is_stop')){
            return '<strong style="color:orangered;">Да</strong>';
        }
        return '<strong style="color:green;">Нет</strong>';
    }

}

