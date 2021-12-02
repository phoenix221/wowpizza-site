<?php



/**
 * Контролер
 */
class CheckoutController
{




    /**
     * Список всех элементов
     */
    function index()
    {

        if(substr(url(), -6)=='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.substr(url(), 0, -6));
            exit;
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Корзина<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Корзина';

        // проверка на нерабочее время
        if(!check_wt()){
            d()->error = '<div class="alert alert-danger" id="order-error">К сожалению вы пытаетесь оформить заказ в нерабочее время, ожидаем ваши заказы в Часы работы.<br>Ознакомиться с режимом работы, вы можете в разделе <a href="/contacts">Контакты</a>.</div>';
            d()->next_disabled = 1;
        }

        get_city();
        if($_SESSION['delivery'] == 1 && d()->city->id == 3 || $_SESSION['delivery'] == 1 && d()->city->id == 6){
            d()->order_warning = '<div class="alert alert-warning" id="order-warning">Внимание! Самовывоз осуществляется только при предъявлении qr-кода о вакцинации</div>';
        }
        // название каталога для электронной торговли
        d()->ec_list = "Рекомендуемые (корзина)";
        $_SESSION['ec_list'] = "Рекомендуемые (корзина)";

        if(url(1)=='ajax'){
            get_cart();
            get_city();
        }

        if($_GET['error']=='minsumm'){
            if(!d()->check_new_promo_minsum()){
                $arr_title = Array();
                foreach ($_SESSION['cart'] as $k_cart=>$v_cart)
                {
                    if($v_cart['not_dd']) $arr_title[] = $v_cart['title'];
                }
                if($_SESSION['promocode']['min_sum_notdd'] && $arr_title)
                {
                    d()->error = '<div class="alert alert-danger " id="order-error">Минимальная сумма заказа для промокода '.$_SESSION['promocode']['title'].': <strong>'.$_SESSION['promocode']['min_sum'].' руб.</strong> '.implode(",", $arr_title).' не участвуют в формировании минимальной суммы, так как являются блюдом не собственного производства. Вам осталось заказать еще на <strong>'.d()->eshe.' руб.</strong> </div>';
                }else{
                    d()->error = '<div class="alert alert-danger " id="order-error">Минимальная сумма заказа для промокода '.$_SESSION['promocode']['title'].': <strong>'.$_SESSION['promocode']['min_sum'].' руб.</strong> Вам осталось заказать еще на <strong>'.d()->eshe.' руб.</strong></div>';
                }
            }
        }

        if($_GET['error']=='stop'){
            $prod = strpos($_GET['prod'], ',');
            if(!$prod){
                d()->error = '<div class="alert alert-danger " id="order-error">К сожалению '.$_GET["prod"].' закончился. Приносим свои извинения, для продолжения удалите товар из корзины.</div>';
            }else{
                d()->error = '<div class="alert alert-danger " id="order-error">К сожалению '.$_GET["prod"].' закончились. Приносим свои извинения, для продолжения удалите товар из корзины.</div>';
            }
        }

        if($_GET['error']=='twopromo'){
            d()->error = '<div class="alert alert-danger " id="order-error">В заказ добавлены два промокода, которые не сочетаются между собой. Корзина обновлена, один из промокодов удален.</div>';
        }

        if($_GET['error']=='twogiftpickup'){
            d()->error = '<div class="alert alert-danger " id="order-error">В заказ добавлены два подарка за самовывоз, которые не сочетаются между собой. Корзина обновлена, один из подарков удален.</div>';
        }

        get_products_options();

        d()->order_index = 1;
        // выборка рекомендуемых товаров
        d()->slider_class = 'slider-products';
        $rs = Array();
        $free_ss = 0;
        foreach($_SESSION['cart'] as $k=>$v){
            $rs[] = $v['id'];
            // проверка на наличие бесплатного соевого соуса
            $ttl = mb_strtolower($v['title']);
            if(strpos($ttl, 'соевый соус') !== false && !$v['price']){
                $free_ss = 1;
            }
        }
        $ids = Array();
        // массив для соевого соуса
        if($free_ss){
            $ss_id = Array();
            $ss = d()->Product->where('title="Соевый соус" AND city_id = ? AND is_active = 1 AND is_stop = 0 OR title="Имбирь" AND city_id = ? AND is_active = 1 AND is_stop = 0 OR title="Васаби" AND city_id = ? AND is_active = 1 AND is_stop = 0', d()->city->id, d()->city->id, d()->city->id)->limit(0,3);
            if($ss->count){
                foreach($ss as $v){
                    $ss_id[] = $ss->id;
                }
                d()->ss_flag = 1;
            }
        }
        $rec = d()->Recommend->where('city_id = ? AND product_id IN (?)', d()->city->id, $rs);
        if($rec->count){
            foreach($rec as $k=>$v){
                $t = json_decode($rec->text, true);
                foreach($t as $ke=>$va){
                    if(!in_array($ke, $rs))$ids[$ke] += $va;
                }
            }
            arsort($ids);
            $ids = array_keys($ids);
            if(count($ss_id)){
                foreach($ss_id as $ssk=>$ssv){
                    $search = array_search($ssv, $ids);
                    if($search !== false)unset($ids[$search]);
                    array_unshift($ids, $ssv);
                }
            }
            $ids = array_slice($ids,0,10);
            $order_ids = implode(',', $ids);
            //if(count($ids)<4)d()->slider_class = '';
        }elseif (count($ss_id)){
            foreach($ss_id as $ssk=>$ssv) {
                $ids[] = $ssv;
            }
            $order_ids = implode(',', $ids);
        }
        if(count($ids)){
            d()->products_list = d()->Product->where('city_id=? AND is_stop=0 AND id IN (?)', d()->city->id, $ids)->order_by('FIELD(id, ' . $order_ids . ')');
            if (count(d()->products_list)) d()->rec_show = 1;
        }

        d()->url = url();
        d()->empty_cart = 'none';
        if(!d()->cart_count){
            d()->empty_cart = '';
            d()->isset_cart = 'none';
        }

        if(d()->Auth->is_guest()){
            d()->data_guest = 1;
        }else{
            d()->user = d()->Auth->user();
        }

        $do = d()->city->gift_dr_do*(-1);
        $posle = d()->city->gift_dr_posle;
        d()->dates = Array();
        $start = $do;
        while($start >= $do && $start <= $posle){
            d()->dates[] = date('d.m', strtotime(date('d.m.Y').' '.$start. 'days'));
            $start++;
        }

        // акция, подарки за самовывоз
        if(d()->city->dd_type==1 && $_SESSION['delivery']==1){
            if(d()->city->dd_gifts_ms<=d()->cart_discount_price && $_SESSION['promocode']['is_not_delivery']!=1){
                d()->show_gifts = 'block';
            }
            $gifts = explode(',', d()->city->dd_gifts);
            d()->picked_id = Array();
            d()->picked_pid = Array();
            foreach($gifts as $k=>$v){
                $a = str_replace('|', '', $v);
                $pid = explode('_', $a);
                d()->picked_id[] = $pid[0];
                d()->picked_pid[$pid[0]] = $pid[1];
            }
            //print '<pre>';
            //print_r(d()->picked_products);
            //print '</pre>';
            $gifts_list = d()->Product(d()->picked_id)->where('is_stop=0')->to_array();
            d()->gifts_list = Array();
            foreach($gifts_list as $k=>$v){
                $p = '';
                if(d()->picked_pid[$v['id']])$p = d()->Property->sql("SELECT id, title FROM properties WHERE id=".d()->picked_pid[$v['id']]." LIMIT 0,1")->title;
                d()->gifts_list[] = Array(
                    "title" => $v['title'],
                    "image" => $v['image'],
                    "id" => $v['id'].'_'.d()->picked_pid[$v['id']],
                    "property_title" => $p,
                );
            }
        }

        if($_SESSION['show_gifts_type']=='dr' && d()->city->gift_dr_minsum <= d()->cart_discount_price && $_SESSION['promocode']['is_not_dr']!=1){
            d()->dr_show_gifts = 'block';

            $gifts = explode(',', d()->city->gift_dr);
            d()->picked_id = Array();
            d()->picked_pid = Array();
            foreach($gifts as $k=>$v){
                $a = str_replace('|', '', $v);
                $pid = explode('_', $a);
                d()->picked_id[] = $pid[0];
                d()->picked_pid[$pid[0]] = $pid[1];
            }
            $gifts_list = d()->Product(d()->picked_id)->to_array();
            d()->dr_gifts_list = Array();
            foreach($gifts_list as $k=>$v){
                $p = '';
                if(d()->picked_pid[$v['id']])$p = d()->Property->sql("SELECT id, title FROM properties WHERE id=".d()->picked_pid[$v['id']]." LIMIT 0,1")->title;
                d()->dr_gifts_list[] = Array(
                    "title" => $v['title'],
                    "image" => $v['image'],
                    "id" => $v['id'].'_'.d()->picked_pid[$v['id']],
                    "property_title" => $p,
                );
            }
        }

        if($_SESSION['points']){
            d()->np_payment = 'none';
            d()->p_payment = 'block_important';
            d()->points = $_SESSION['points'];
            d()->ppay_text = '<em id="points-em" data-points="'.$_SESSION['old_points'].'">'.$_SESSION['points'].'</em><i class="rub">q</i><i class="mdi mdi-close" title="Отменить" onclick="cancel_points(this);"></i>';
        }

        if($_SESSION['promocode']){
            d()->np_promo = 'none';
            d()->p_promo = 'block_important';
            d()->promocode = '<em>'.$_SESSION['promocode']['title'].'</em><i id="cancel-promo" class="mdi mdi-close" title="Отменить" onclick="cancel_promo(this);"></i>';
            if($_SESSION['promocode']['type']==1 || $_SESSION['promocode']['type']==2 && d()->discount_promocode){
                // отображение скидки от промокода внизу страницы
                d()->d_promocode = 'block_important';
                if($_SESSION['promocode']['discount_type']==1){
                    d()->d_promocode = '';
                }
            }
        }

        if(d()->dr_used)d()->dr_gdis = 'disabled';
        if(d()->gift_pickup_used)d()->gdis = 'disabled';

        print d()->view();
    }

    function order()
    {
        if(url(1)=='ajax'){
            get_cart();
            get_city();
            d()->user = d()->Auth->user();
        }

        // проверка на количество товаров в корзине
        if(!d()->cart_count){
            header('Location: /checkout');
            exit;
        }

        // выбаны ли подарки за самовывоз
        if(d()->city->dd_type==1 && d()->city->dd_gifts_ms<=d()->cart_discount_price &&  $_SESSION['promocode']['is_not_delivery']!=1 && !d()->gift_pickup_used){
            d()->picked_deliv_gifts = 1;
        }

        // проверка на минимальную сумму заказа
        if(!d()->check_new_promo_minsum()){
            header('Location: /checkout?error=minsumm');
            exit;
        }

        $ar = d()->check_product_in_stop();
        if($ar){
            $prod = implode(',', $ar);
            header('Location: /checkout?error=stop&prod='.$prod);
            exit;
        }
        // TODO: не нашел причину этой проблемы. Вставил костыль.
        // проверка на 2 промокода в корзине
        if(d()->check_twopromo()){
            header('Location: /checkout?error=twopromo');
            exit;
        }

        // TODO: не нашел причину этой проблемы. Вставил костыль.
        // проверка на 2 подарка за самовывоз в корзине
        if(d()->check_twogiftpickup()){
            header('Location: /checkout?error=twogiftpickup');
            exit;
        }

        // проверка на нерабочее время
        if(!check_wt() && !$_SESSION['admin']){
            header('Location: /checkout');
            exit;
        }

        d()->order_check = 1;
        d()->url = url();
        d()->key = d()->city->ya_geo_apikey;

        d()->order_error = '';
        d()->fcheck = '';

        if($_SESSION['delivery']==2) {
            // доставка
            // если адрес уже заполнялся
            if($_SESSION['zone']['address']){
                d()->address_success = 'success';
                d()->address = $_SESSION['zone']['address'];
                d()->address_hint = $_SESSION['zone']['f_title'].'<button class="btn btn-link" onclick="show_details();">подробнее</button>';
            }else{
                d()->address_id = 0;
                d()->address_hint = 'пожалуйста укажите полный адрес доставки';
                if(!d()->Auth->is_guest) {
                    d()->adresses_list = d()->Address->where('user_id=?', d()->Auth->id);
                }

                if(d()->adresses_list->count) {
                    d()->aform = 'none';
                }else{
                    d()->opacity = 'opacity__';
                    d()->btn_disabled = 'disabled';
                }
            }
            d()->delivery_time = $_SESSION['zone']['time'].' мин.';
            if($_SESSION['promocode']['zones']){
                $zl = explode(',', $_SESSION['promocode']['zones']);
                $i = 0;
                $time = 0;
                foreach ($zl as $k_zl=>$v_zl){
                    $z = str_replace('|', '', $v_zl);
                    $zone = d()->Zoni($z);
                    if($zone->title == $_SESSION['zone']['title']){
                       $i = 1;
                       $time = $zone->time3;
                    }
                }
                if($i == 1){
                    if($time){
                        $_SESSION['zone']['time3'] = $time;
                        d()->delivery_time = $time.' мин.';
                    }
                }
            }

            if($_SESSION['zone']['category_id'] != '' && !$_SESSION['promocode']['zones']){
                $cat_str = trim($_SESSION['zone']['category_id'], '|');
                $cat_id = explode('|', $cat_str);
                $str_flag = '';
                foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
                    foreach ($cat_id as $v_cid){
                        if($v_cart['category_id'] == $v_cid) $str_flag .= $v_cart['category_id'].',';
                    }
                }
                if(!$str_flag){
                    $this_zone = d()->Zoni->where('title = ? AND city_id = ?', $_SESSION['zone']['title'], d()->city->id)->limit(0,1);
                    if($this_zone->time2){
                        $_SESSION['zone']['time2'] = $this_zone->time2;
                        d()->delivery_time = $this_zone->time2.' мин.';
                    }
                }
            }

            // проверяем условия зон
            check_order_zone();
        }else{
            // самовывоз
            d()->offices_list = d()->Office->where('city_id=? AND is_active=1', d()->city->id);

            // проверка на ограничения на самовывоз
            if(d()->wt_pickup){
                d()->btn_disabled = 'disabled';
                d()->order_error = 'В связи с повышением мер безопасности по нераспространению коронавирусной инфекции, заказы на самовывоз принимаются до '.d()->city->wt_pickup.'. Самовывоз осуществляется до '.d()->wt_pickup_hour.'.';
                d()->order_error_show = 'show';
            }

            if(d()->city->id == 3 || d()->city->id == 6){
                d()->order_warning = '<div class="alert alert-warning" id="order-warning">Внимание! Самовывоз осуществляется только при предъявлении qr-кода о вакцинации</div>';
            }
        }
        if($_SESSION['select_address']){
            d()->slcid = $_SESSION['select_address'];
        }else{
            d()->slcid = d()->adresses_list[0]->id;
        }

        d()->cashback_points = get_cashback();
        d()->points_word = declOfNum(d()->cashback_points, 'балл', 'балла', 'баллов');

        d()->address_title = $_SESSION['order_info']['title'];
        d()->floor = $_SESSION['order_info']['floor'];
        d()->user_name = $_SESSION['order_info']['order_n'];
        if(!d()->user_name)d()->user_name=d()->user->name;
        d()->user_phone = $_SESSION['order_info']['order_ph'];
        if(!d()->user_phone)d()->user_phone=d()->user->phone_not_seven;
        d()->entrance = $_SESSION['order_info']['entrance'];
        d()->room_number = $_SESSION['order_info']['room-number'];
        d()->house_marker = $_SESSION['order_info']['house-marker'];
        d()->banknote = $_SESSION['order_info']['banknote'];
        d()->comment = $_SESSION['order_info']['comment'];
        d()->persons = $_SESSION['order_info']['persons'];
        if(!d()->persons)d()->persons=0;

        d()->cook_time_value = $_SESSION['order_info']['cook_time_value'];

        if($_SESSION['order_info']['pay']=='pay_2'){
            d()->ap2 = 'active';
        }elseif($_SESSION['order_info']['pay']=='pay_3'){
            d()->ap3 = 'active';
        }else{
            d()->ap1 = 'active';
        }
        if($_SESSION['order_info']['cook_time']=='in_time'){
            d()->acttime = 'active';
            d()->scttime = 'show_hwrap';
        }else{
            d()->actnow = 'active';
        }

        // ссылка Дополнительно
        d()->dopolnitelno = d()->Category->where('is_active=1 AND city_id=? AND title="Дополнительно"', d()->city->id)->url;

        // текущее время для времянок
        d()->this_time = date('d.m.Y');
        d()->next_time = date("d.m.Y", strtotime(d()->this_time.'+ 1 days'));

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span>';
        d()->nav .= '<a href="/checkout/" itemprop="item"><span itemprop="name">Корзина</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Оформление заказа<meta itemprop="position" content="3"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Оформление заказа';

        print d()->view();
    }

    function finish()
    {
        d()->order_id = $_GET['order_id'];

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/checkout/" itemprop="item"><span itemprop="name">Корзина</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Заказ оформлен<meta itemprop="position" content="3"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Заказ оформлен';

        d()->this = d()->Order(d()->order_id);
        if(d()->this->promocode_id){
            d()->promo = d()->Promocode(d()->this->promocode_id);
        }
        $p = json_decode(d()->this->cart, true);
        d()->ec_products = '';
        foreach($p as $k=>$v){
            d()->ec_products .= "{'name': '".d()->nq($v['title'])."','id': '".$v['id']."','price': '".$v['price']."','brand': '','category': '', 'variant': '', 'quantity': '".d()->nq($v['count'])."', 'coupon': ''},";
        }

        if(d()->this->cook_time == 'now'){
            $tm1 = strtotime(d()->this->created_at) + 60*$_SESSION['running_order_time'];
            $tm2 = $tm1 + 10*60;
            d()->tm = date('H:i', $tm1).' - '.date('H:i', $tm2);

        }else{
            $tm = strtotime(d()->this->cook_time);
            d()->tm = date('d.m.Y, H:i', $tm);
        }

        print d()->view();
    }

    function online_pay()
    {
        print d()->view();
    }

}

