<?php


class Order extends ActiveRecord
{

    function f_date()
    {
        return date('d.m.Y', strtotime($this->get('created_at'))).', <span class="c999">'.date('H:i', strtotime($this->get('created_at'))).'</span>';
    }

    function products_total_count()
    {
        $cart = json_decode(d()->this->cart, true);
        $c = count($cart);
        //$c = 0;
        //foreach($cart as $v){
            // += $v['count'];
        //}
        $w = declOfNum ($c, array('блюдо', 'блюда', 'блюд'));
        return $c.' '.$w;
    }

    function status_color()
    {
        if($this->get('status')==1){
            return 'lblue';
        }
        if($this->get('status')==2){
            return 'gray';
        }
        if($this->get('status')==6){
            return 'lpurple';
        }
        if($this->get('status')==7){
            return 'lgreen';
        }
        if($this->get('status')==8){
            return 'biruze';
        }
        if($this->get('status')==9){
            return 'lgray';
        }
        if($this->get('status')==10){
            return 'black';
        }
        return 'orange';
    }

    function status_word()
    {
        if($this->get('status')==1){
            return 'Обработан';
        }
        if($this->get('status')==2){
            return 'Отказ';
        }
        if($this->get('status')==6){
            return 'Кухня';
        }
        if($this->get('status')==7){
            return 'Доставлен';
        }
        if($this->get('status')==8){
            return 'Курьер';
        }
        if($this->get('status')==9){
            return 'Принят';
        }
        if($this->get('status')==10){
            return 'Не дозвонились';
        }
        return 'Новый';
    }

    function cart_sum()
    {
        $s = 0;
        $cart = json_decode($this->get('cart'), true);
        foreach($cart as $k=>$v){
            $s += $v['price']*$v['count']+$v['items_price']-$v['total_promo_discount'];
        }
        return $s;
    }

    function cart_finish_sum()
    {
        $s = 0;
        $cart = json_decode($this->get('cart'), true);
        foreach($cart as $k=>$v){
            $s += $v['price']*$v['count']+$v['items_price']-$v['total_promo_discount'];
        }
        return $s;
    }

    function fcart()
    {
        return array_values(json_decode($this->get('cart'), true));
    }

}

class Order_c extends Model
{

    function f_date()
    {
        return date('d.m.Y', strtotime($this->get('created_at'))) . ', <span class="c999">' . date('H:i', strtotime($this->get('created_at'))) . '</span>';
    }

    function products_total_count()
    {
        $cart = json_decode(d()->this->cart, true);
        $c = count($cart);
        //$c = 0;
        //foreach($cart as $v){
        // += $v['count'];
        //}
        $w = declOfNum($c, array('блюдо', 'блюда', 'блюд'));
        return $c . ' ' . $w;
    }

    function status_color()
    {
        if ($this->get('status') == 1) {
            return 'lblue';
        }
        if ($this->get('status') == 2) {
            return 'gray';
        }
        if ($this->get('status') == 6) {
            return 'lpurple';
        }
        if ($this->get('status') == 7) {
            return 'lgreen';
        }
        if ($this->get('status') == 8) {
            return 'biruze';
        }
        if ($this->get('status') == 9) {
            return 'lgray';
        }
        if ($this->get('status') == 10) {
            return 'black';
        }
        return 'orange';
    }

    function status_word()
    {
        if ($this->get('status') == 1) {
            return 'Обработан';
        }
        if ($this->get('status') == 2) {
            return 'Отказ';
        }
        if ($this->get('status') == 6) {
            return 'Кухня';
        }
        if ($this->get('status') == 7) {
            return 'Доставлен';
        }
        if ($this->get('status') == 8) {
            return 'Курьер';
        }
        if ($this->get('status') == 9) {
            return 'Принят';
        }
        if ($this->get('status') == 10) {
            return 'Не дозвонились';
        }
        return 'Новый';
    }

    function cart_sum()
    {
        $s = 0;
        $cart = json_decode($this->get('cart'), true);
        foreach ($cart as $k => $v) {
            $s += $v['price'] * $v['count'] + $v['items_price'] - $v['total_promo_discount'];
        }
        return $s;
    }

    function cart_finish_sum()
    {
        $s = 0;
        $cart = json_decode($this->get('cart'), true);
        foreach ($cart as $k => $v) {
            $s += $v['price'] * $v['count'] + $v['items_price'] - $v['total_promo_discount'];
        }
        return $s;
    }

    function fcart()
    {
        return array_values(json_decode($this->get('cart'), true));
    }

}


