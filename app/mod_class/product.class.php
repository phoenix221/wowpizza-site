<?php


class Product extends ActiveRecord
{

    function title(){
        return replece_fileds($this->get('title'));
    }

    function title_original(){
        return $this->get('title');
    }

    function sostav()
    {
        if(!$this->get('sostav')){
            return trim(replece_fileds(strip_tags($this->get('text'))));
        }
        return strip_tags($this->get('sostav'));
    }

    function sostav_original()
    {
        if(!$this->get('sostav')){
            return trim(replece_fileds($this->get('text')));
        }
        return $this->get('sostav');
    }

    function text()
    {
        /*$text = str_replace(' ', '', $this->get('text'));
        $text = replece_fileds($text);
        if(!$text)$text = replece_fileds($this->get('sostav'));*/
        $text = replece_fileds($this->get('text'));
        if(!$text)$text = replece_fileds($this->get('sostav'));
        return $text;
    }

    function likes_count()
    {
        return $this->get('likes');
        // $a = str_replace('||',',', $this->get('likes'));
        // $a = str_replace('|','', $a);
        // $a = array_filter(explode(',', $a));
        // return count($a);
    }

    function check_liked()
    {
        if($_COOKIE['like_'.$this->get('id')]){
            return 'liked';
        }
        return '';
    }

    function yml_sostav()
    {
        $sostav = strip_tags($this->get('sostav'));
        if(!$this->get('sostav')){
            $sostav = strip_tags($this->get('text'));
        }
        $dl = mb_strlen($sostav, 'utf-8');
        if($dl > 2500){
            $sostav  = mb_substr($sostav, 0, 2500);
            $sostav  = rtrim(trim($sostav), "!,.-")."…";
        }
        $x = str_replace('"', '&quot;', $sostav);
        $x = str_replace('&', '&amp;', $x);
        $x = str_replace('>', '&gt;', $x);
        $x = str_replace('<', '&lt;', $x);
        $x = str_replace("'", "&apos;", $x);
        return $x;
    }

    function number_type()
    {
        return 'шт';
        //return str_replace('.', '', $this->get('number_type'));
    }

    function weight_type()
    {
        return str_replace('.', '', $this->get('weight_type'));
    }

    function admin_image()
    {
        return '<img src="'.d()->preview($this->get('image'), 100, 'auto').'" />';
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

    function is_active_simple_word()
    {
        if($this->get('is_active')){
            return 'Да';
        }
        return 'Нет';
    }

    function incart()
    {
        $s = array_keys($_SESSION['cart']);
        foreach($s as $key=>$value){
            $find   = '_gift_dr';
            if(strpos($value, $find) !== false)unset($s[$key]);

            $find   = '_promo';
            if(strpos($value, $find) !== false)unset($s[$key]);
        }
        $s = '|'.implode($s, '|');
        $f   = '|'.$this->get('id').'_';
        $r = strpos($s, $f);
        if($r !== false){
            return 'none';
        }
        return '';
    }

    function outcart()
    {
        $s = array_keys($_SESSION['cart']);
        foreach($s as $key=>$value){
            $find   = '_gift_dr';
            if(strpos($value, $find) !== false)unset($s[$key]);

            $find   = '_promo';
            if(strpos($value, $find) !== false)unset($s[$key]);
        }
        $s = '|'.implode($s, '|');
        $f   = '|'.$this->get('id').'_';
        $r = strpos($s, $f);
        if($r !== false){
            return '';
        }
        return 'none';
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

    // цена
    function price()
    {
        // цена с учетом скидки за самовывоз
        $price = $this->get('price');

        // если не установлена галочка, Не учавствует в акции: скидка за самовывоз
        $not = $this->get('not_dd');

        if(!$not){
            $price = get_discount_price($this->get('price'), $not);
        }

        return $price;
    }

    function stickers_array(){
        if($this->get('sticker')){
            return explode(',',$this->get('sticker'));
        }
        return '';
    }

    function admin_info(){
        $i = '';
        $price = $this->get('price');
        // TODO: оптимизировать
        $property = d()->Property->where('product_id=?', $this->get('id'));
        if(count($property)){
            $price = $property->where('is_default = 1')->price;
            if(!$price){
                $price = d()->Property->where('product_id=?', $this->get('id'))->limit(0,1)->price;
            }
        }
        // TODO: оптимизировать
        $i .= 'Стоимость: <strong>'.$price.' руб.</strong><br>';
        if($this->get('weight')){
            $i .= 'Характеристики: <strong>'. $this->get('weight').' '.str_replace('.', '', $this->get('weight_type')).'.</strong><br>';
        }
        return $i;
    }

    function ec_view()
    {
        $cat_title = 0;
        if(url(1)=='menu' && url(2) && url(3)=='index'){
            $cat_url = url(2);
        }elseif(url(1)=='ajax' && url(2)=='menu' && url(3) && url(4)=='index'){
            $cat_url = url(3);
        }else{
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            $cat_title = d()->cat_list[$c[0]]['title'];
        }
        if(!$cat_title){
            foreach(d()->cat_list as $key=>$vl){
                if($cat_url == $vl['url']) {
                    $cat_title = $vl['title'];
                    break;
                }
            }
        }

        $v = "{";
        $v .= "'name': '".str_replace('"', '', str_replace("'", "", $this->get('title')))."',";
        $v .= "'id': '".$this->get('id')."',";
        $v .= "'price': '".$this->get('price')."',";
        $v .= "'brand': '',";
        $v .= "'category': '".$cat_title."',";
        $v .= "'variant': '',";
        $v .= "'list': '".d()->ec_list."',";
        $v .= "'position': ''";
        $v .= "},";
        return $v;
    }

    function category_word()
    {
        $cat_title = 0;
        if(url(1)=='menu' && url(2) && url(3)=='index'){
            $cat_url = url(2);
        }elseif(url(1)=='ajax' && url(2)=='menu' && url(3) && url(4)=='index'){
            $cat_url = url(3);
        }else{
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            $cat_title = d()->cat_list[$c[0]]['title'];
        }
        if(!$cat_title){
            foreach(d()->cat_list as $key=>$vl){
                if($cat_url == $vl['url']) {
                    $cat_title = $vl['title'];
                    break;
                }
            }
        }

        return $cat_title;
    }

    function link()
    {
        $url = $this->get('id');
        if($this->get('url')){
            $url = $this->get('url');
        }

        if(url(1)=='menu' && url(2) && url(3)){
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            $category = d()->cat_list[$c[0]]['url'];
//            $category = url(2);
        }elseif(url(1)=='ajax' && url(2)=='menu' && url(3) && url(4)){
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            $category = d()->cat_list[$c[0]]['url'];
            //$category = url(3);
        }else{
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            //$_SESSION['debug'] = $c;
            $category = d()->cat_list[$c[0]]['url'];
        }

        return '/menu/'.$category.'/'.$url.'/';
    }

    function f_category_id()
    {
        $cat_id = 0;
        if(url(1)=='menu' && url(2) && url(3)=='index'){
            $cat_url = url(2);
        }elseif(url(1)=='ajax' && url(2)=='menu' && url(3) && url(4)=='index'){
            $cat_url = url(3);
        }else{
            $c = array_values(array_filter(explode('|', $this->get('category_id'))));
            $cat_id = $c[0];
        }
        if(!$cat_id){
            foreach(d()->cat_list as $key=>$vl){
                if($cat_url == $vl['url']) {
                    $cat_id = $key;
                    break;
                }
            }
        }
        return $cat_id;
    }


    function property_header()
    {
        $cats = d()->categories->to_array();
        //$_SESSION['debug'] = $cats;
        if(url(1)!='menu' && url(2)!='menu'){
            $catid = explode('|', $this->get('category_id'));
            $title = d()->cat_list[$catid[1]]['property_title'];
        }else{
            $title = d()->category->property_title;
        }
        return $title;
    }

    // список свойст блюда
    function p_list()
    {
        $key = array_search($this->get('id'), d()->p_id_arr);
        $other = array_search($this->get('id'), d()->other_id_arr);

        if ($key!==FALSE || $other!==FALSE){
            if($key!==FALSE){
                // надо именно !== а не !=, ведь номер первого элемента — 0
                $keys = array_keys(d()->p_id_arr, $this->get('id'));
                $r = Array();
                $def = 0;
                // проверяем, есть ли в корзине нужное свойство
                if(count($_SESSION['cart'])){
                    foreach($keys as $v){
                        $r[$v] = d()->pa_list[$v];
                        $cc = $_SESSION['cart'][$this->get('id').'_'.$r[$v]['id']];
                        if(count($cc) && !$def){
                            $def = 1;
                            $r[$v]['checked'] = 'checked';
                            // цена на товар
                            d()->product_price = $r[$v]['price'];
                            // id свойства по умолчанию
                            d()->property_id = $r[$v]['id'];
                        }
                    }
                }

                // если в корзине нет, то проверяем, есть ли свойство по дефолту
                if(!$def){
                    foreach($keys as $v){
                        $r[$v] = d()->pa_list[$v];
                        if(d()->property_list[$v]['is_default'] && !$def || count($cc) && !$def){
                            $def = 1;
                            $r[$v]['checked'] = 'checked';
                            // цена на товар
                            d()->product_price = $r[$v]['price'];
                            // id свойства по умолчанию
                            d()->property_id = $r[$v]['id'];
                        }
                    }
                }

                // если цена по умолчанию отсутствует, ставим цену по умолчанию у первого товара
                if(!$def){
                    $r[$key]['is_default'] = 1;
                    $r[$key]['checked'] = 'checked';
                    // цена на товар
                    d()->product_price = $r[$key]['price'];
                    // id свойства по умолчанию
                    d()->property_id = $r[$key]['id'];
                }
                return array_values($r);
            }else{
                d()->product_price = $this->get('price');
                return 1;
            }
        }
        return false;
    }

    function short_text()
    {
        $text = substr($this->get('text'), 0, 200);
        $text = replece_fileds($text);
        return $text;
    }

}
