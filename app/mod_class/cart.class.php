<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Cart extends Model
{

    function f_price()
    {
        return $this->get('price')-$this->get('promo_discount');
    }

    function total_price_nd()
    {
        return $this->get('total_price')+$this->get('items_price');
    }

    function f_total_price()
    {
        return $this->get('total_price')-$this->get('total_promo_discount')+$this->get('items_price');
    }

    function cnt()
    {
        return $this->get('count');
    }

    function attr_id()
    {
        $ti = explode(',', $this->get('items'));
        $items = '';
        foreach($ti as $k=>$v){
            $tv = explode('|',$v);
            $items .= $tv[0];
        }
        $items = str_replace('_', '', $items);
        if(!$this->get('items'))$items=0;

        if($this->get('property')=='promo' || $this->get('property')=='gift_dr' || $this->get('property')=='gift_pickup' || $this->get('property')=='gift_cash'){
            //return $this->get('id').'_'.$this->get('gift_property').'_'.$this->get('property').'_'.$items;
            return $this->get('id').'_'.$this->get('gift_property').'_'.$this->get('property').'_0';
        }
        return $this->get('id').'_'.$this->get('property').'_'.$items;
    }

    function f_property_title()
    {
        $pused = $this->get('promo_used');
        $pt = '';
        $promo_title = '';
            if(
                $this->get('promo_title') && $this->get('promo_discount') && !isset($pused) ||
                $this->get('promo_title') && $this->get('promo_used') ||
                $this->get('promo_title') && $this->get('property')=='promo' ||
                $this->get('promo_title') && $this->get('property')=='gift_dr' ||
                $this->get('promo_title') && $this->get('property')=='gift_pickup' ||
                $this->get('promo_title') && $this->get('property')=='gift_cash'
            ){
            $promo_title = $this->get('promo_title');
        }

        if($this->get('property_title') && !$promo_title){
            $pt = $this->get('property_title');
        }else if(!$this->get('property_title') && $promo_title){
            $pt = $promo_title;
        }else if($this->get('property_title') && $promo_title){
            $pt = $this->get('property_title').' / '. $promo_title;
        }

        if($this->get('items')){
            if($pt)$pt .= ', ';
            $pt .= $this->get('items_title');
        }
        return $pt;
    }

    function f_property_title_ul()
    {
        $pused = $this->get('promo_used');
        $pt = '';
        $promo_title = '';
            if(
                $this->get('promo_title') && $this->get('promo_discount') && !isset($pused) ||
                $this->get('promo_title') && $this->get('promo_used') ||
                $this->get('promo_title') && $this->get('property')=='promo' ||
                $this->get('promo_title') && $this->get('property')=='gift_dr' ||
                $this->get('promo_title') && $this->get('property')=='gift_pickup' ||
                $this->get('promo_title') && $this->get('property')=='gift_cash'
            ){
            $promo_title = $this->get('promo_title');
        }

        if($this->get('property_title') && !$promo_title){
            $pt = '<li>'.$this->get('property_title').'</li>';
        }else if(!$this->get('property_title') && $promo_title){
            $pt = '<li>'.$promo_title.'</li>';
        }else if($this->get('property_title') && $promo_title){
            $pt = '<li>'.$this->get('property_title').'</li><li>'.$promo_title.'</li>';
        }

        if($this->get('items')){
            foreach(explode(',', $this->get('items_title')) as $k=>$v){
                $pt .= '<li>'.$v.'</li>';
            }
        }
        return $pt;
    }

    function f_property_title_1c()
    {
        $pused = $this->get('promo_used');
        $pt = '';
        $promo_title = '';
        if(
            $this->get('promo_title') && $this->get('promo_discount') && !isset($pused) ||
            $this->get('promo_title') && $this->get('promo_used') ||
            $this->get('promo_title') && $this->get('property')=='promo' ||
            $this->get('promo_title') && $this->get('property')=='gift_dr' ||
            $this->get('promo_title') && $this->get('property')=='gift_pickup' ||
            $this->get('promo_title') && $this->get('property')=='gift_cash'
        ){
            $promo_title = $this->get('promo_title');
        }

        if($this->get('property_title') && !$promo_title){
            $pt = $this->get('property_title');
        }else if(!$this->get('property_title') && $promo_title){
            $pt = $promo_title;
        }else if($this->get('property_title') && $promo_title){
            $pt = $this->get('property_title').' / '. $promo_title;
        }

        return $pt;
    }

    function items_array()
    {
        $t = Array();
        if($this->get('items')){
            $r = explode(',', $this->get('items'));
            $t = Array();
            foreach($r as $k=>$v){
                $tmp = explode('|', $v);
                $temp = explode('_', $tmp[0]);

                $cnt = $tmp[1];
                if($tmp[1]=='-')$cnt = 1;

                $t[] = Array(
                    'id'=>$temp[0],
                    'property'=>$temp[1],
                    'cnt'=>$cnt,
                );
            }
        }
        return $t;
    }


}

