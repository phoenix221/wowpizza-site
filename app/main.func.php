<?php

function main()
{

	if(substr($_SERVER['REQUEST_URI'],-5)=='index' && !$_GET){
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.substr($_SERVER['REQUEST_URI'],0,-5));
		exit;
	}

    //halloween
	//d()->hlhide = 'hl-none';

	//if(!$_COOKIE['check_utm']){
    //    $utm = '';
        //if($_GET){
        if($_GET['utm_source']){
            $utm = $_GET['utm_source'];
            if(count($_GET)>1)$utm .= '|';
            foreach($_GET as $k=>$v){
                if($k=='utm_source')continue;
                $utm .= $v.'|';
            }
            $utm = substr($utm,0,-1);
            setcookie("utm", $utm, time()+60*60*24*365*10, '/');
        }
	    // 10 лет
        //setcookie("utm", $utm, time()+60*60*24*365*10, '/');
        //setcookie("check_utm", 1, time()+60*60*24*365*10, '/');
    //}

    if($_GET['action']=='logout'){
        d()->Auth->logout();
        if($_SESSION['promocode']['is_auth']){
            clear_promo();
        }
        header('Location: /');
        exit;
    }

    // получаем инфо о городе
    get_city();

    // проверяем, нужно ли редиректнуть
    seo_redirect_module();

    // получаем настройки yandex suggest
    d()->options = d()->Option->where('city_id=?', d()->city->id);

    // получаем остальные города
    d()->city_list = d()->City->where('id != ?', d()->city->id);
    //if(d()->city_list->is_empty)d()->onecity = 'onecity';

    foreach(d()->city_list as $v){
        if(d()->city_list->code=='okt')continue;
        $c_check = 1;
    }
    //if(!$c_check)d()->onecity = 'onecity';

    // получаем нужные даты c учетом часового пояса города
    // d()->unix_time = UNIX время;
    // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
    // d()->n_week = Порядковый номер дня недели
    // d()->week_day = Названия дня недели
    // d()->worktime = Режим работы на сегодня
    get_dates(d()->city);

    // меню
    d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);


    // корзина
    get_cart();
    if(d()->cart_count>0)d()->show_cart_btn = 'show_cart_btn';

    // определяем способ доставки
    // самовывоз 1
    // доставка 2
    if(!$_SESSION['delivery']){
        $_SESSION['delivery'] = d()->city->delivery;
    }
    if($_SESSION['delivery']==1){
        d()->active_pickup = 'active';
    }else{
        d()->active_delivery = 'active';
    }

    // текст в модалку, об удалении промокода
    if($_SESSION['promocode']['delivery'] && $_SESSION['promocode']['delivery'] == $_SESSION['delivery']){
        $prtg = 'самовывоз';
        if($_SESSION['promocode']['delivery']==2)$prtg = 'доставку';
        d()->clear_promo_text = '<p class="clear_promo_text"><u>Промокод <strong class="text-uppercase">'.$_SESSION['promocode']['title'].'</strong> будет удален</u>. Этот промокод доступен только на '.$prtg.'</p>';
    }

    // метка, что информационная модалка по умолчанию скрыта
    d()->info_modal = '0';

	d()->o = d()->Option;

	//d()->nocache = rand(10000,9999999);
	d()->nocache = 3;
	d()->promo_min_sum = $_SESSION['promocode']['min_sum'];
	d()->promo_delivery = $_SESSION['promocode']['delivery'];

    // показать текстбэк
    d()->show_textback = 1;
    if($_SESSION['hide_textback'])d()->show_textback = 0;

    // модалка что мы закрыты
	if(d()->city->is_modal_wt && !$_SESSION['wt_modal'] && !check_wt()){
        d()->wt_modal = 1;
        $_SESSION['wt_modal'] = 1;
        d()->show_textback = 0;
    }
    // переменные для модалки Мы закрыты
    //if(d()->domain == 'radugavkusaufa.ru'){
    //    d()->close_img = 'logo_rv_close.png';
    //    d()->close_were = 'а';
    //    d()->close_style = 'background: #f2780b;';
    //}else{
        d()->close_img = 'logo_red.svg';
        d()->close_were = '';
        d()->close_style = '';
    //}

    // модалка что Октябрский закрыт
    if(d()->city->id == 5){
        d()->okt = 1;
        $_SESSION['okt'] = 1;
        d()->show_textback = 0;
    }

    // модалка что открываеться новый город
    /*if(d()->city->id == 8 && !$_SESSION['admin']){
        d()->new_city = 1;
        $_SESSION['tyumen'] = 1;
        d()->show_textback = 0;
    }*/

    // ограничение на самовывоз
    d()->wt_pickup = 0;
    if(d()->city->wt_pickup){
        d()->wt_pickup = check_wt_pickup();
    }
    d()->wt_pickup_modal = 0;
    if(d()->wt_pickup && !$_SESSION['wt_pickup_modal'] && $_SESSION['delivery'] == 1){
        $_SESSION['wt_pickup_modal'] = 1;
        d()->wt_pickup_modal = 1;
    }

	if($_SESSION['admin']=='developer'){
        //d()->wt_pickup_modal = 1;
    }

    d()->picked_deliv_gifts = 0;

	// если пользователь подтвердил E-mail в JustClick, показываем модалку
    if($_GET['succesjustclick']){
        $u = d()->User->where('id=? AND city=?', $_GET['succesjustclick'], d()->city->code)->limit(0,1);
        if($u->count){
            // проверяем hash
            $hash = g_user_hash($u);
            if($hash == $_GET['hash']){
                // ставим метку что E-mail подтвержден
                $u->conf_email = 1;
                $u->save;
                // показываем модалку, что E-mail подтвержден
                d()->info_modal = '1';
                d()->im_title = 'Подтверждение E-mail';
                d()->im_text = '<div class=" alert alert-success text-center">Спасибо, Ваш E-mail успешно подтвержден</div>';
                // запускаем функцию начисления бонусных баллов, если это необходимо
                run_bonus_personal($_GET['succesjustclick']);
            }
        }
    }

    // добавляем класс к меню на всех страницах кроме главной
    if(url()!='index'){
       d()->shadow_menu = 'head-menu-shadow';
    }

    d()->g_points = 0;
    if($_SESSION['points'])d()->g_points = $_SESSION['points'];
    if($_SESSION['promocode'])d()->promo = $_SESSION['promocode'];

    if(!d()->Auth->is_guest())d()->user = d()->Auth->user();
    if(!d()->Seo->title)d()->Seo->title = d()->this->title;

	d()->content = d()->content();
	print d()->render('main_tpl');
}

// загрузка картинок для CK Editor
function ajax_ckupload()
{
    if($_FILES['upload'])
    {
        if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])) ){
            $message = "Вы не выбрали файл";
        }else if ($_FILES['upload']["size"] == 0 OR $_FILES['upload']["size"] > 2050000) {
            $message = "Размер файла не соответствует нормам";
        }else if (($_FILES['upload']["type"] != "image/jpeg") AND ($_FILES['upload']["type"] != "image/jpeg") AND ($_FILES['upload']["type"] != "image/png")) {
            $message = "Допускается загрузка только картинок JPG и PNG.";
        }else if (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
            $message = "Что-то пошло не так. Попытайтесь загрузить файл ещё раз.";
        }else{
            $name =rand(1, 1000).'-'.md5($_FILES['upload']['name']).'.'.getex($_FILES['upload']['name']);
            move_uploaded_file($_FILES['upload']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/storage/ckeditor/".$name);
            $full_path = $_SERVER['DOCUMENT_ROOT'].'/storage/ckeditor/'.$name;
            $message = "Файл ".$_FILES['upload']['name']." загружен";
            $size = getimagesize($_SERVER['DOCUMENT_ROOT'].'/storage/ckeditor/'.$name);
            if($size[0]<50 OR $size[1]<50){
                unlink($_SERVER['DOCUMENT_ROOT'].'/storage/ckeditor/'.$name);
                $message = "Файл не является допустимым изображением";
                $full_path = "";
            }
        }
        $callback = $_REQUEST['CKEditorFuncNum'];
        //echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$callback.'", "'.$full_path.'", "'.$message.'" );</script>';

        $data = ['uploaded' => 1, 'fileName' => $name, 'url' => '/storage/ckeditor/'.$name];
        echo json_encode($data);
    }
}
function getex($filename) {
    return array_pop(explode(".", $filename));
}
// загрузка картинок для CK Editor

function test()
{
    if($_SESSION['admin']) {
        exit();
        // print '<pre>';
        // print_r($_SERVER);
        // print '</pre>';
        // exit;
    }
    d()->page_not_found();
}

// добавление в корзину
function ajax_change_cart($type='', $_id='', $_property='', $_items='')
{
    $_type = $type;
    if(!$type){
        $_id = str_replace('__', '_', $_POST['id']);
        $_property = $_POST['property'];
        $_type = $_POST['type'];
    }

    if(!$_items){
        $_items_original = str_replace(',', '', $_POST['items']);
        $ti = explode(',', $_POST['items']);
        $_items = '';
        foreach($ti as $k=>$v){
            $tv = explode('|',$v);
            $_items .= $tv[0];
        }
        if(!$_POST['items'])$_items = 0;
    }

    // TODO: cart_null
    $cart_log = Array(
        'type'=>$_type,
        'id'=>$_id,
        'property'=>$_property,
        'items'=>$_items,
        'time'=>date('d.m.Y H:i:s'),
    );
    setcookie("cart_log_".date('U'), json_encode($cart_log), time()+3600*24*7, '/');

    // добавление в корзину
    if($_type=='add') {
        if($_id){
            $cart = $_SESSION['cart'];
            $pid = $_id.'_'.$_property.'_'.str_replace('_', '', $_items);

            if(!$cart[$pid]['id']){
                if($_property!='promo' && $_property!='gift_dr' && $_property!='gift_pickup' && $_property!='gift_cash'){
                    // существует ли такое блюдо
                    $p = d()->Product($_id);
                    if(!$p->count()){
                        print 'error';
                        exit();
                    }
                    // существует ли такое свойство (если есть)
                    if($_property){
                        $property = d()->Property($_property);
                        if(!$property->count()){
                            print 'error';
                            exit();
                        }
                        $price = $property->price;
                        $old_price = $property->old_price;
                        $property_title = $property->title;
                        $property_image = $property->image;
                    }else{
                        $price = $p->price;
                        $old_price = $p->old_price;
                        $property_title = '';
                        $property_image = '';
                    }

                    if(!$old_price)$old_price = $price;

                    // переменные для seo_variables
                    if(check_seo_variables($p->title_original)){
                        d()->this = $p;
                        d()->category = d()->Category($p->f_category_id);
                        d()->done_price = $price;
                        //get_products_options(d()->category->id, $price);
                    }
                    // переменные для seo_variables

                    // для допов
                    $items_price = 0;
                    $items_title = '';
                    if($_POST['items']){
                        $i = explode(',', $_POST['items']);
                        $itms = Array();
                        d()->i_arr = Array();
                        //$_SESSION['dbg']['test'] = 33;
                        foreach($i as $k=>$vu){
                            $itmp = explode('|', $vu);
                            $item_cnt = $itmp[1];

                            $t = explode('_', $itmp[0]);
                            $itms[] = $t[0];
                            if(!d()->i_arr[$t[0]]){
                                d()->i_arr[$t[0]] = $t[1].'|'.$item_cnt;
                            }elseif(d()->i_arr[$t[0]] && !is_array(d()->i_arr[$t[0]])){
                                $tm = d()->i_arr[$t[0]];
                                unset(d()->i_arr[$t[0]]);
                                d()->i_arr[$t[0]][] = $tm;
                                d()->i_arr[$t[0]][] = $t[1].'|'.$item_cnt;
                            }else{
                                d()->i_arr[$t[0]][] = $t[1].'|'.$item_cnt;
                            }
                        }
                        //$_SESSION['dbg']['i_arr'] = d()->i_arr;
                        $icnt = array_count_values($itms);
                        $items = d()->Product($itms);
                        foreach($items as $vit){
                            //$items_title .= mb_strtolower($items->title).' ('.$item_cnt.' шт), ';
                            if(!is_array(d()->i_arr[$items->id])){
                                $sv = explode('|',d()->i_arr[$items->id]);
                                $prprt_pttl = '';
                                if($sv[0]){
                                    $prprt = d()->Property($sv[0]);
                                    $svcnt = $sv[1];
                                    $sht = ' / <i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт';
                                    if($sv[1]=='-'){
                                        $sht='';
                                        $svcnt = 1;
                                    }
                                    $prprt_pttl = ' ('.$prprt->title.$sht.')';
                                    $items_price += $prprt->price*$svcnt;
                                    $items_title .= mb_strtolower(str_replace(',', '.', $items->title.$prprt_pttl)).', ';
                                }else{
                                    $svcnt = $sv[1];
                                    $sht = ' (<i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт)';
                                    if($sv[1]=='-'){
                                        $sht='';
                                        $svcnt = 1;
                                    }
                                    $items_price += $items->price*$svcnt;
                                    $items_title .= mb_strtolower(str_replace(',', '.', $items->title)).$sht.', ';
                                }

                            }else{
                                foreach(d()->i_arr[$items->id] as $ik=>$iv){
                                    $prprt_pttl = '';
                                    $sv = explode('|',$iv);
                                    if($sv[0]){
                                        $prprt = d()->Property($sv[0]);
                                        $svcnt = $sv[1];
                                        $sht = ' / <i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт';
                                        if($sv[1]=='-'){
                                            $sht = '';
                                            $svcnt = 1;
                                        }
                                        $prprt_pttl = ' ('.$prprt->title.')';
                                        $items_price += $prprt->price*$svcnt;
                                        $items_title .= mb_strtolower(str_replace(',', '.', $items->title.$prprt_pttl)).$sht.', ';
                                    }else{
                                        $svcnt = $sv[1];
                                        $sht = ' (<i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт)';
                                        if($sv[1]=='-'){
                                            $sht = '';
                                            $svcnt = 1;
                                        }
                                        $items_price += $items->price*$svcnt;
                                        $items_title .= mb_strtolower(str_replace(',', '.', $items->title)).$sht.', ';
                                    }
                                }
                            }

                        }
                        $items_title = substr(trim($items_title),0,-1);
                    }
                    // проверяем есть ли у свойства картинка
                    if(!$property_image){
                        $image = $p->image;
                    }else{
                        $image = $property_image;
                    }

                    $cart[$pid] = Array(
                        'id' => $p->id,
                        'id_1c' => $p->id_1c,
                        'property' => $_property,
                        'property_title' => $property_title,
                        'count' => 1,
                        'title' => $p->title,
                        'category_id' => $p->f_category_id,
                        // цена за 1 товар
                        'price' => $price,
                        // цена за все товары
                        'total_price' => $price,
                        // цена за 1 товар с учетом скидки за самовывоз (независимо от выбранного способа доставки)
                        'dd_price' => get_discount_price($old_price, $p->not_dd, 1),
                        // скидка за самовывоз за 1 товар
                        'pickup_discount' => $old_price-$price,
                        // скидка за самовывоз за все товары
                        'total_pickup_discount' => $old_price-$price,
                        // товар не собственного производства
                        'not_dd' => $p->not_dd,
                        // допы
                        'items' => $_POST['items'],
                        'items_price' => $items_price,
                        'items_title' => $items_title,
                        // сумма без скидок за самовывоз и пр.
                        'image' => d()->preview($image, '120', '120'),
                        // автотовары
                        'autoadd' => $p->autoadd_products,
                        // приборы
                        'tableware' => $p->tableware
                    );


                    // проверяем, есть ли промокод со скидкой для этого товара
                    $dop_array = Array();
                    if($_SESSION['promocode']['type']==1 && $_SESSION['promocode']['discount_type']==1){
                        $products_temp = explode(',', $_SESSION['promocode']['products']);
                        foreach($products_temp as $k=>$v){
                            $a = explode('|', $v);

                            if($a[0] == $pid){
                                $promo_count = $a[1];
                                if(!$promo_count)$promo_count=9999;
                                $promo_discount = $price - $a[2];

                                // считаем скидку
                                $check = 1;
                                $total_promo_discount = 0;
                                $promo_used = 0;

                                // определем лимит для группы
                                $group = $a[3];
                                $lim = explode(',', $_SESSION['promocode']['products_limit']);
                                foreach($lim as $v){
                                    $limit = explode('_', $v);
                                    if($limit[1] == $group){
                                        $l = $limit[0];
                                    }
                                }
                                if(!$l)$l = 9999;

                                // проверка скидку у товаров из другой группы
                                foreach($cart as $k=>$v){
                                    if($v['promo_used']){
                                        if($v['promo_group'] != $group){
                                            $check = 0;
                                        }
                                    }
                                }

                                // проверка на максимальный лимит для группы
                                if($l <= $_SESSION['promocode']['products_used']){
                                    $check = 0;
                                }
                                if($check){
                                    $total_promo_discount = $promo_discount;
                                    $promo_used = 1;
                                    $_SESSION['promocode']['products_used']++;
                                }
                                $dop_array = Array(
                                    'promocode_id' => $_SESSION['promocode']['id'],
                                    'promo_title' => 'Скидка по промокоду '.strtoupper($_SESSION['promocode']['title']),
                                    'promo_count' => $promo_count,
                                    'promo_group' => $group,
                                    'promo_discount' => $promo_discount,
                                    'total_promo_discount' => $total_promo_discount,
                                    'promo_used' => $promo_used,
                                );
                            }
                        }
                    }

                    if($_SESSION['promocode']['type']==2){
                        if($_SESSION['promocode']['discount_type'] != 2 || !$p->not_dd){
                            $d = price_round($price, $_SESSION['promocode']['percent'], $_SESSION['promocode']['round']);
                            $dop_array = Array(
                                'promocode_id' => $_SESSION['promocode']['id'],
                                'promo_title' => 'Скидка по промокоду '.strtoupper($_SESSION['promocode']['title']),
                                'promo_discount' => $d,
                                'total_promo_discount' => $d,
                            );

                        }
                    }

                    $cart[$pid] = array_merge($cart[$pid], $dop_array);

                    // если есть товары, которые нужно добавить автоматически
                    if($p->autoadd_products){
                        $cart = autoadd_products($p->autoadd_products, $cart, 'add');
                    }

                }else{
                    $gid = explode('_', $_id);
                    $g = d()->Product($gid[0]);
                    if(!$g->id){
                        print 'error';
                        exit();
                    }

                    // существует ли такое свойство (если есть)
                    $property_title = '';
                    if($gid[1]){
                        $property = d()->Property($gid[1]);
                        if(!$property->count()){
                            print 'error';
                            exit();
                        }
                        $property_title = $property->title;
                    }


                    $promo_name = $_SESSION['promocode']['title'];
                    $mpt = 'подарок на День Рождения';
                    if($_property=='promo'){
                        $mpt = 'подарок по промокоду '.strtoupper($promo_name);
                    }
                    if($_property=='gift_pickup'){
                        $mpt = 'подарок за самовывоз';
                    }
                    if($_property=='gift_cash'){
                        $mpt = '<i class="free-to-order">подарок за оплату наличными (при заказе от '.d()->city->min_gift_cash.' руб.)</i>';
                    }
                    $cart[$pid] = Array(
                        'id' => $g->id,
                        'id_1c' => $g->id_1c,
                        'count' => 1,
                        'title' => $g->title,
                        'property_title' => $property_title,
                        'promo_title' => $mpt,
                        'property' => $_property,
                        'price' => 0,
                        'total_price' => 0,
                        'pickup_discount' => 0,
                        'total_pickup_discount' => 0,
                        'image' => d()->preview($g->image, '120', '120'),
                        'promocode' => $promo_name,
                        'gift_property' => $gid[1],
                        'autoadd' => $g->autoadd_products,
                        'tableware' => $g->tableware
                    );
                    // gift_property - дополнительное поле, которое пришлось ввести из за Подарки из меню

                    // если есть товары, которые нужно добавить автоматически
                    if($g->autoadd_products){
                        $cart = autoadd_products($g->autoadd_products, $cart, 'add');
                    }
                }

            }else{
                // TODO: костылек с ошибкой при количестве подарков больше 1 (не смог найти причину, поставил эту проверку, пример заказ 353659)
                if($cart[$pid]['auto'] || $_property=='gift_dr' || $_property=='gift_pickup' || $_property=='gift_cash' || $_property=='promo' && !$cart[$pid]['price']) {
                    $l = d()->Log->new;
                    $l->text = $_property.'|'.$pid;
                    $l->title = 'gift_count|double_add';
                    $l->save;
                }else{
                    // если есть персональная скидка у товара (промокод)
                    // = Array();
                    if ($cart[$pid]['promocode_id']) {
                        if ($_SESSION['promocode']['type'] == 2) {
                            $cart[$pid]['total_promo_discount'] += $cart[$pid]['promo_discount'];
                        } else {
                            // считаем скидку
                            $check = 1;

                            // индивидуальный лимит
                            if ($cart[$pid]['promo_count'] <= $cart[$pid]['promo_used']) {
                                $check = 0;
                            }

                            // определем лимит для группы
                            $products_temp = explode(',', $_SESSION['promocode']['products']);
                            foreach ($products_temp as $k => $v) {
                                $a = explode('|', $v);
                                if ($a[0] == $pid) {
                                    $group = $a[3];
                                }
                            }
                            $lim = explode(',', $_SESSION['promocode']['products_limit']);
                            $l = 0;
                            foreach ($lim as $v) {
                                $limit = explode('_', $v);
                                if ($limit[1] == $group) {
                                    $l = $limit[0];
                                }
                            }
                            if (!$l) $l = 9999;

                            if ($l <= $_SESSION['promocode']['products_used']) {
                                $check = 0;
                            }

                            // проверка скидку у товаров из другой группы
                            foreach ($cart as $k => $v) {
                                if ($v['promo_used']) {
                                    if ($v['promo_group'] != $group) {
                                        $check = 0;
                                    }
                                }
                            }

                            // проверям максимум для одинаковых блюд с разными допами
                            $pprdcts = explode(',', $_SESSION['promocode']['products']);
                            $pprdcts_arr = Array();
                            foreach ($pprdcts as $k => $v) {
                                $tmp = explode('|', $v);
                                if ($tmp[3] == $group) {
                                    $pprdcts_arr[$tmp[0]] = $tmp[1];
                                    if (!$tmp[1]) $pprdcts_arr[$tmp[0]] = 9999;
                                }
                            }
                            $fid = explode('_', $pid);
                            array_pop($fid);
                            $fid = implode('_', $fid);
                            $bl = 0;
                            foreach ($cart as $k) {
                                if (strpos($k, $fid . '_') !== false) $bl++;
                            }
                            if ($bl > 1) {
                                $brother_cnt = 0;
                                foreach ($cart as $k => $v) {
                                    if (strpos($k, $fid . '_') !== false) $brother_cnt += $v['promo_used'];
                                }
                                $bcnt_max = $pprdcts_arr[$fid] - $brother_cnt;
                                if ($bcnt_max == 1) {
                                    foreach ($cart as $k => $v) {
                                        if (strpos($k, $fid . '_') !== false) {
                                            if ($pid != $k) {
                                                $used = $v['promo_used'];
                                                $cart[$k]['promo_count'] = $used;
                                            }
                                        }
                                    }
                                }
                                if ($bcnt_max > 1) {
                                    foreach ($cart as $k => $v) {
                                        if (strpos($k, $fid . '_') !== false) {
                                            if ($pid != $k) {
                                                $count = $v['promo_count'] - 1;
                                                $cart[$k]['promo_count'] = $count;
                                            }
                                        }
                                    }
                                }
                                if ($bcnt_max < 1) {
                                    $check = 0;
                                    foreach ($cart as $k => $v) {
                                        if (strpos($k, $fid . '_') !== false) {
                                            $used = $v['promo_used'];
                                            $cart[$k]['promo_count'] = $used;
                                        }
                                    }
                                }
                            }

                            if ($check) {
                                $_SESSION['promocode']['products_used']++;
                                $cart[$pid]['promo_used']++;
                                $cart[$pid]['total_promo_discount'] = $cart[$pid]['promo_used'] * $cart[$pid]['promo_discount'];
                            }
                        }
                    }

                    // для допов
                    if ($cart[$pid]['items']) {
                        $pfo = $cart[$pid]['items_price'] / $cart[$pid]['count'];
                        $cart[$pid]['items_price'] = $pfo * ($cart[$pid]['count'] + 1);
                    }

                    $cart[$pid]['count'] += 1;
                    $cart[$pid]['total_price'] = $cart[$pid]['price'] * $cart[$pid]['count'];

                    // если есть товары, которые нужно добавить автоматически
                    if($cart[$pid]['autoadd']){
                        $cart = autoadd_products($cart[$pid]['autoadd'], $cart, 'plus');
                    }

                }
            }

            $cart = autoadd_gift_cash($cart, 'add_change');

            $_SESSION['cart'] = $cart;

            // пересчет оплаты баллами
            points_refresh();

            if(!$type){
                print 'ok';
                exit();
            }
        }
    }

    // +1
    if($_type=='plus') {

        if($_id) {
            $cart = $_SESSION['cart'];
            // TODO: костылек с ошибкой при количестве подарков больше 1 (не смог найти причину, поставил эту проверку, пример заказ 353659)
            if($cart[$_id]['auto'] || $cart[$_id]['property']=='gift_dr' || $cart[$_id]['property']=='gift_pickup' || $cart[$_id]['property']=='promo' && !$cart[$_id]['price']) {
                $l = d()->Log->new;
                $l->text = $_property.'|'.$pid;
                $l->title = 'gift_count|plus';
                $l->save;
            }else{
                // если есть персональная скидка у товара (промокод)
                if ($cart[$_id]['promocode_id']) {
                    if ($_SESSION['promocode']['type'] == 2) {
                        $cart[$_id]['total_promo_discount'] += $cart[$_id]['promo_discount'];
                    } else {
                        // считаем скидку
                        $check = 1;
                        $group = $cart[$_id]['promo_group'];
                        // индивидуальное количество
                        if ($cart[$_id]['promo_count'] <= $cart[$_id]['promo_used']) {
                            $check = 0;
                        }

                        // определем лимит для группы
                        $products_temp = explode(',', $_SESSION['promocode']['products']);
                        foreach ($products_temp as $k => $v) {
                            $a = explode('|', $v);
                            if ($a[0] == $pid) {
                                $group = $a[3];
                            }
                        }
                        $lim = explode(',', $_SESSION['promocode']['products_limit']);
                        $l = 0;
                        foreach ($lim as $v) {
                            $limit = explode('_', $v);
                            if ($limit[1] == $group) {
                                $l = $limit[0];
                            }
                        }
                        if (!$l) $l = 9999;
                        // определем лимит группы
                        if ($l <= $_SESSION['promocode']['products_used']) {
                            $check = 0;
                        }

                        // проверям максимум для одинаковых блюд с разными допами
                        $pprdcts = explode(',', $_SESSION['promocode']['products']);
                        $pprdcts_arr = Array();
                        foreach ($pprdcts as $k => $v) {
                            $tmp = explode('|', $v);
                            if ($tmp[3] == $group) {
                                $pprdcts_arr[$tmp[0]] = $tmp[1];
                                if (!$tmp[1]) $pprdcts_arr[$tmp[0]] = 9999;
                            }
                        }
                        $fid = explode('_', $_id);
                        array_pop($fid);
                        $fid = implode('_', $fid);
                        $bl = 0;
                        foreach ($cart as $k) {
                            if (strpos($k, $fid . '_') !== false) $bl++;
                        }
                        if ($bl > 1) {
                            $brother_cnt = 0;
                            foreach ($cart as $k => $v) {
                                if (strpos($k, $fid . '_') !== false) $brother_cnt += $v['promo_used'];
                            }
                            $bcnt_max = $pprdcts_arr[$fid] - $brother_cnt;
                            if ($bcnt_max == 1) {
                                foreach ($cart as $k => $v) {
                                    if (strpos($k, $fid . '_') !== false) {
                                        if ($_id != $k) {
                                            $used = $v['promo_used'];
                                            $cart[$k]['promo_count'] = $used;
                                        }
                                    }
                                }
                            }
                            if ($bcnt_max > 1) {
                                foreach ($cart as $k => $v) {
                                    if (strpos($k, $fid . '_') !== false) {
                                        if ($_id != $k) {
                                            $count = $v['promo_count'] - 1;
                                            $cart[$k]['promo_count'] = $count;
                                        }
                                    }
                                }
                            }
                            if ($bcnt_max < 1) {
                                $check = 0;
                                foreach ($cart as $k => $v) {
                                    if (strpos($k, $fid . '_') !== false) {
                                        $used = $v['promo_used'];
                                        $cart[$k]['promo_count'] = $used;
                                    }
                                }
                            }
                        }

                        if ($check) {
                            $_SESSION['promocode']['products_used']++;
                            $cart[$_id]['promo_used']++;
                            $cart[$_id]['total_promo_discount'] = $cart[$_id]['promo_used'] * $cart[$_id]['promo_discount'];
                        }
                    }
                }

                // для допов
                if ($cart[$_id]['items']) {
                    $ncnt = $cart[$_id]['count']+1;
                    $pfo = $cart[$_id]['items_price'] / $cart[$_id]['count'];
                    $cart[$_id]['items_price'] = $pfo*$ncnt;

                    $itms = explode(',', $cart[$_id]['items']);
                    $new_itms = '';
                    foreach($itms as $k=>$v){
                        $tv = explode('|', $v);
                        $nvclv = ($tv[1] / $cart[$_id]['count'])*$ncnt;
                        $new_itms .= $tv[0].'|'.$nvclv.',';
                    }
                    $cart[$_id]['items'] = substr($new_itms,0,-1);

                    $itms_ttl = explode(',', $cart[$_id]['items_title']);
                    $new_items_title = '';
                    foreach($itms_ttl as $k=>$v){
                        if(strpos($v, 'io-count') !== false){
                            $re = '/(?<=[\'"])\w+(?=[\'"])/u';
                            preg_match_all($re, $v, $m, PREG_SET_ORDER, 0);
                            $icnt = $m[0][0]*$ncnt;

                            $re2 = '#<i class="io-count"(.+?)</i>#is';
                            preg_match_all($re2, $v, $m2);
                            $p1 = '">'.strip_tags($m2[0][0]).'</i>';
                            $p2 = '">'.$icnt.'</i>';
                            $new_v = str_replace($p1, $p2, $v);

                            $new_items_title .= trim($new_v).', ';
                        }else{
                            $new_items_title .= trim($v).', ';
                        }
                    }
                    $cart[$_id]['items_title'] = substr(trim($new_items_title),0,-1);
                }

                $cart[$_id]['count'] += 1;
                $cart[$_id]['total_price'] = $cart[$_id]['price'] * $cart[$_id]['count'];
                $cart[$_id]['total_pickup_discount'] = $cart[$_id]['pickup_discount'] * $cart[$_id]['count'];

                // если есть товары, которые нужно добавить автоматически
                if($cart[$_id]['autoadd']){
                    $cart = autoadd_products($cart[$_id]['autoadd'], $cart, 'plus');
                }
                $cart = autoadd_gift_cash($cart, 'add_change');

                $_SESSION['cart'] = $cart;

                // пересчет оплаты баллами
                points_refresh();

                if (!$type) {
                    print 'ok';
                    exit();
                }
            }
        }
    }

    // -1
    if($_type=='minus') {
        if($_id){

            $cart = $_SESSION['cart'];

            // если есть персональная скидка у товара (промокод)
            if($cart[$_id]['promocode_id'] && $cart[$_id]['total_promo_discount']){
                if($_SESSION['promocode']['type']==2){
                    //$_SESSION['debug']=123;
                    $cart[$_id]['total_promo_discount'] -= $cart[$_id]['promo_discount'];
                }else{
                    // считаем скидку
                    if($cart[$_id]['promo_used'] == $cart[$_id]['count']){

                        $fid = explode('_',$_id);
                        array_pop($fid);
                        $fid = implode('_', $fid);
                        foreach($cart as $k=>$v){
                            if(strpos($k, $fid.'_') !== false){
                                if($_id != $k) {
                                    $cart[$k]['promo_count']++;
                                }
                            }
                        }

                        $cart[$_id]['promo_used']--;
                        $_SESSION['promocode']['products_used']--;
                        $cart[$_id]['total_promo_discount'] = $cart[$_id]['promo_used']*$cart[$_id]['promo_discount'];
                    }
                }
            }

            // для допов
            if($cart[$_id]['items']){
                $ncnt = $cart[$_id]['count']-1;
                $pfo = $cart[$_id]['items_price']/$cart[$_id]['count'];
                $cart[$_id]['items_price'] = $pfo*$ncnt;

                $itms = explode(',', $cart[$_id]['items']);
                $new_itms = '';
                foreach($itms as $k=>$v){
                    $tv = explode('|', $v);
                    $nvclv = ($tv[1] / $cart[$_id]['count'])*$ncnt;
                    // TODO: костыль с количеством допов 0.5
                    if($nvclv<1)$nvclv=1;

                    $new_itms .= $tv[0].'|'.$nvclv.',';
                }
                $cart[$_id]['items'] = substr($new_itms,0,-1);

                $itms_ttl = explode(',', $cart[$_id]['items_title']);
                $new_items_title = '';
                foreach($itms_ttl as $k=>$v){
                    if(strpos($v, 'io-count') !== false){
                        $re = '/(?<=[\'"])\w+(?=[\'"])/u';
                        preg_match_all($re, $v, $m, PREG_SET_ORDER, 0);
                        $icnt = $m[0][0]*$ncnt;

                        $re2 = '#<i class="io-count"(.+?)</i>#is';
                        preg_match_all($re2, $v, $m2);
                        $p1 = '">'.strip_tags($m2[0][0]).'</i>';
                        $p2 = '">'.$icnt.'</i>';
                        $new_v = str_replace($p1, $p2, $v);

                        $new_items_title .= trim($new_v).', ';
                    }else{
                        $new_items_title .= trim($v).', ';
                    }
                }
                $cart[$_id]['items_title'] = substr(trim($new_items_title),0,-1);
            }

            $cart[$_id]['count'] -= 1;
            if($cart[$_id]['count']<1){
                // если есть товары, которые были добавлены автоматически
                if($cart[$_id]['autoadd']){
                    $oldcntt = $cart[$_id]['count']+1;
                    $cart = autoadd_products($cart[$_id]['autoadd'], $cart, 'minus');
                }
                unset($cart[$_id]);
            }else{
                $cart[$_id]['total_price'] = $cart[$_id]['price']*$cart[$_id]['count'];
                $cart[$_id]['total_pickup_discount'] = $cart[$_id]['pickup_discount']*$cart[$_id]['count'];

                // если есть товары, которые были добавлены автоматически
                if($cart[$_id]['autoadd']){
                    $cart = autoadd_products($cart[$_id]['autoadd'], $cart, 'minus');
                }
            }

            $cart = autoadd_gift_cash($cart, 'minus_change');

            $_SESSION['cart'] = $cart;

            // пересчет оплаты баллами
            points_refresh();

            if(!$type){
                print 'ok';
                exit();
            }
        }
    }

    // удаление из корзины
    if($_type=='delete') {
        if($_id){
            // если есть персональная скидка у товара (промокод)
            if($_SESSION['cart'][$_id]['promocode_id'] && $_SESSION['cart'][$_id]['promo_used']){
                $_SESSION['promocode']['products_used'] -= $_SESSION['cart'][$_id]['promo_used'];

                $fid = explode('_',$_id);
                array_pop($fid);
                $fid = implode('_', $fid);
                foreach($_SESSION['cart'] as $k=>$v){
                    if(strpos($k, $fid.'_') !== false){
                        if($_id != $k) {
                            $_SESSION['cart'][$k]['promo_count'] += $_SESSION['cart'][$_id]['promo_used'];
                        }
                    }
                }

            }

            // если есть товары, которые были добавлены автоматически
            if($_SESSION['cart'][$_id]['autoadd']){
                $oldcntt = $_SESSION['cart'][$_id]['count'];
                $cart = autoadd_products($_SESSION['cart'][$_id]['autoadd'], $_SESSION['cart'], 'delete', $oldcntt);
            }

            autoadd_gift_cash($_SESSION['cart'], 'delete_change');

            unset($_SESSION['cart'][$_id]);

            // пересчет оплаты баллами
            points_refresh();

            if(!$type){
                print 'ok';
                exit();
            }
        }
    }

    if(!$type){
        d()->page_not_found();
    }
}

function points_refresh(){
    if($_SESSION['points']){
        get_cart();

        if((d()->cart_total_price-d()->discount_promocode)/2 < $_SESSION['points']){
            $_SESSION['points'] = floor((d()->cart_total_price-d()->discount_promocode)/2);
            return;
        }

        if($_SESSION['points'] < $_SESSION['old_points']){
            $max = floor((d()->cart_total_price-d()->discount_promocode)/2);
            while($_SESSION['points'] < $max && $_SESSION['points'] < $_SESSION['old_points']){
                $_SESSION['points']++;
            }
        }

    }
}

// определение города
function get_city(){
    $domian = explode('.', $_SERVER['HTTP_HOST']);
    if(count($domian)==2){
        if($_SERVER['HTTP_HOST'] == 'radugavkusaufa.ru'){
            d()->domain = $_SERVER['HTTP_HOST'];
            d()->maindomain = 'appetitfood.ru';
            d()->subdomain = '';

            d()->site_url = $_SERVER['HTTP_HOST'];
            // d()->city = 0;
            d()->city = d()->City(6)->limit(0,1);
        }else{
            d()->domain = $_SERVER['HTTP_HOST'];
            d()->maindomain = $_SERVER['HTTP_HOST'];

            //if($_GET['admin']==1){
                d()->city_list = d()->City->order_by('title asc');

                d()->url = '';
                if(url() && url()!='index'){
                    d()->url = url();
                    $check = substr(url(), -6);
                    if($check == '/index')d()->url = substr(url(),0,-5);
                }
                if($_GET)d()->url .= '?'.$_SERVER['QUERY_STRING'];

                d()->Seo->title = '«Аппетит» - служба доставки еды, заказать на дом и в офис';
                d()->Seo->description = 'Бесплатная доставка от 500руб. Быстрая и не дорогая доставка еды - служба доставки «АППЕТИТ». Пицца, роллы, суши,  бургеры, шаурма и другие блюда с доставкой. ';

                print d()->domain_choose_tpl();
                exit;
            //}else{
            //    d()->city = d()->City->limit(0,1);
            //    header("HTTP/1.1 301 Moved Permanently");
            //    header('Location: https://'.d()->city->code.'.'.d()->site_url);
            //    exit;
            //}
        }
    }else{

        d()->domain = $domian[1].'.'.$domian[2];
        d()->maindomain = $domian[1].'.'.$domian[2];
        d()->subdomain = $domian[0];
        d()->site_url = $_SERVER['HTTP_HOST'];
        d()->city = d()->City->where('code=?', $domian[0])->limit(0,1);

        if(!count(d()->city)){
            header('Location: https://'.d()->domain);
            exit;
        }
    }
}
// дополнительные массивы перед выборкой товаров
function get_products_options($category=''){
    // указываем категорию и город, что бы сократить выборку
    //if(!$category){
        //d()->property_list = d()->Property->where('city_id=?', d()->city->id);
    //}else{
        //d()->property_list = d()->Property->where('category_id=? AND city_id=?', $category, d()->city->id);
        //d()->property_list = d()->Property->where('city_id=?', d()->city->id);
    //}
    d()->property_list = d()->Property->where('city_id=? AND is_active=1 AND is_stop=0', d()->city->id);
    d()->other_list = d()->Other->where('city_id=? AND is_active=1', d()->city->id);

    // пересобираем свойства, что бы сохранить модель Property
    d()->pa_list = Array();
    $i = 0;
    foreach(d()->property_list as $v){
        d()->pa_list[$i]['id'] = d()->property_list->id;
        d()->pa_list[$i]['price'] = d()->property_list->price;
        d()->pa_list[$i]['title'] = d()->property_list->title;
        d()->pa_list[$i]['is_default'] = d()->property_list->is_default;
        d()->pa_list[$i]['product_id'] = d()->property_list->product_id;
        d()->pa_list[$i]['city_id'] = d()->property_list->city_id;
        d()->pa_list[$i]['category_id'] = d()->property_list->category_id;
        d()->pa_list[$i]['weight'] = d()->property_list->weight;
        d()->pa_list[$i]['weight_type'] = d()->property_list->weight_type;
        d()->pa_list[$i]['number_persons'] = d()->property_list->number_persons;
        d()->pa_list[$i]['is_stop'] = d()->property_list->is_stop;
        $i++;
    }
    //d()->property_list = d()->Property->sql('SELECT `id`, `product_id` FROM properties WHERE `city_id`="'.d()->city->id.'"');


    // формируем массив для быстрого поиска по свойствам и допам
    d()->p_id_arr = array_column(d()->property_list->to_array(),  'product_id');
    d()->other_id_arr = array_column(d()->other_list->to_array(),  'product_id');

    // формируем массив для быстрого поиска по категориям
    d()->categories = d()->Category->where('city_id=? AND is_active=1', d()->city->id);
    //d()->categories = $cat_list;
    d()->cat_list = Array();
    foreach(d()->categories as $v){
        d()->cat_list[d()->categories->id]['url'] = d()->categories->url;
        d()->cat_list[d()->categories->id]['title'] = d()->categories->title;
        d()->cat_list[d()->categories->id]['property_title'] = d()->categories->property_title;
    }

    // стикеры
    $stickers = d()->Sticker;
    d()->stickers_list = Array();
    foreach($stickers as $v){
        d()->stickers_list[$stickers->id]['image'] = $stickers->image;
        d()->stickers_list[$stickers->id]['title'] = $stickers->title;
    }
}

function recaptcha()
{
    if (isset($_POST['token']) && isset($_POST['action'])) {
        $captcha_token = $_POST['token'];
        $captcha_action = $_POST['action'];
    } else {
        die('Капча работает некорректно. Обратитесь к администратору!');
    }

    //$o = d()->Option;
    get_city();

    // если это подтверждение телефона, проверям последнее время отправки кода
    if($captcha_action=='phone_confirmation' || $captcha_action=='phone_reconfirmation'){
        $phone = d()->convert_phone($_POST['phone']);
        // проверяем есть ли лимиты на отправку СМС и не подтверждали ли телефон до этого
        if(d()->city->send_code_time && $_SESSION['confirmation'][$phone]['code']!='conf'){
            if($_SESSION['confirmation']){
                $check_time = date('U') - $_SESSION['confirmation']['time'];
                if($check_time < d()->city->send_code_time){
                    $ct = d()->city->send_code_time - $check_time;
                    $sec  = declOfNum ($ct, array('секунду', 'секунды', 'секунд'));
                    $txt = 'СМС с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';
                    if(d()->city->send_code_type != 1)$txt = 'Авто-звонок с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';
                    $array = [
                        'result' => 2,
                        'action' => $captcha_action,
                        'text' => $txt
                    ];
                    echo json_encode($array);
                    exit;
                }
            }
        }else{
            // телефон уже подтвержден
            $array = [
                'result' => 'conf',
                'action' => $captcha_action,
            ];
            // записываем инфо в сессию
            $_SESSION['reg_phone'] = $phone;
            echo json_encode($array);
            exit;
        }
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $params = [
        'secret' => d()->city->rc_server_key,
        'response' => $captcha_token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    if(!empty($response)) $r = json_decode($response);

    $success = false;
    $success = $r;
    $success->result = 0;
    $success->text = 'Внимание. Сработала защита "reCAPTCHA". Ваши действия похожи на автоматический скрипт. Для продолжения регистрации, попробуйте обновить страницу, сменить браузер или устройство.';

    if ($r && $r->success && $r->action == $captcha_action) {
        if ($r->score >= d()->city->rc_filtr) {
            $success->result = 1;

            if($captcha_action=='phone_confirmation' || $captcha_action=='phone_reconfirmation') {
                $phone = d()->convert_phone($_POST['phone']);
                // получаем инфо о городе
                get_city();

                $_SESSION['city'] = d()->city->smsc_login;
                $code = d()->send_code($phone, $_GET['first']);
                // записываем инфо в сессию
                $_SESSION['reg_phone'] = $phone;
                if(!$_GET['first'])$_SESSION['confirmation']['time'] = date('U');
                $_SESSION['confirmation'][$phone]['code'] = $code;

                // логируем сессию
                //$l = d()->Log->new;
                //$l->title = 'noregphone2|'.$phone.'|'.$code;
                //$l->text = json_encode($_SESSION);
                //$l->save;

                $success->phone = $phone;
                if($captcha_action=='phone_reconfirmation'){
                    $success->text = 'код отправлен повторно';
                    if(d()->city->send_code_type != 1)$success->text = 'авто-звонок отправлен повторно';
                    if($_SESSION['smsc']['sms']){
                        $success->sms = 1;
                        $success->text = '';
                    }
                }
            }

            if($captcha_action=='writeguide') {
                // получаем инфо о городе
                get_city();

                $l_title = 'Связь с руководством';
                $l_text = '<p><strong>Сообщение:</strong> '.$_POST['text'].'<br><strong>Контакт:</strong> '.$_POST['contact'].'<br><strong>Город:</strong> '.d()->city->title;

                $file = '';
                if($_SESSION['upload_name_files']){
                    foreach ($_SESSION['upload_name_files'] as $vfn){
                        if($_POST['file'] || $_POST['file2'] || $_POST['file3']){
                            //$file = 'https://'.d()->site_url.'/storage/otzyvy/'.md5($_POST['file'].'salt').'.'.strtolower( substr(strrchr($_POST['file'], '.'), 1));
                            $file = 'https://'.d()->site_url.'/storage/otzyvy/'.$vfn;
                            $l_text .= '<br><strong>Вложение:</strong> <a href="'.$file.'" target="_blank">скачать</a>';
                        }
                    }
                    /*if($_POST['file']){
                        //$file = 'https://'.d()->site_url.'/storage/otzyvy/'.md5($_POST['file'].'salt').'.'.strtolower( substr(strrchr($_POST['file'], '.'), 1));
                        $file = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn1.'.'.strtolower( substr(strrchr($_POST['file'], '.'), 1));
                        $l_text .= '<br><strong>Вложение:</strong> <a href="'.$file.'" target="_blank">скачать</a>';
                    }
                    if($_POST['file2']){
                        //$file = 'https://'.d()->site_url.'/storage/otzyvy/'.md5($_POST['file'].'salt').'.'.strtolower( substr(strrchr($_POST['file'], '.'), 1));
                        $file2 = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn2.'.'.strtolower( substr(strrchr($_POST['file2'], '.'), 1));
                        $l_text .= '<br><strong>Вложение:</strong> <a href="'.$file2.'" target="_blank">скачать</a>';
                    }
                    if($_POST['file3']){
                        //$file = 'https://'.d()->site_url.'/storage/otzyvy/'.md5($_POST['file'].'salt').'.'.strtolower( substr(strrchr($_POST['file'], '.'), 1));
                        $file3 = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn3.'.'.strtolower( substr(strrchr($_POST['file3'], '.'), 1));
                        $l_text .= '<br><strong>Вложение:</strong> <a href="'.$file3.'" target="_blank">скачать</a>';
                    }*/
                }
                $e = explode(',', d()->city->email_reviews);
                foreach($e as $email){
                    d()->Mail->to(trim($email));
                    d()->Mail->set_smtp(d()->city->smtp_server,d()->city->smtp_port,d()->city->smtp_mail,d()->city->smtp_password,d()->city->smtp_protocol);
                    d()->Mail->from(d()->city->smtp_mfrom,d()->city->smtp_tfrom);
                    d()->Mail->subject($l_title);
                    d()->Mail->message($l_text);
                    d()->Mail->send();
                }

                $phone = '';
                $email = '';
                if($_POST['contact']){
                    // email
                    $pattern = "/[-a-z0-9!#$%&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i";
                    $text = $_POST['contact'];
                    preg_match_all($pattern, $text, $result);
                    $r = array_unique(array_map(function ($i) { return $i[0]; }, $result));
                    $email = $r[0];

                    // phone
                    $text = str_replace($email, '', $_POST['contact']);
                    $phone = preg_replace('/[^0-9]/', '', $text);
                    $phone = d()->convert_phone($phone);
                }

                /*$r = d()->Review->new;
                $r->city_id = d()->city->id;
                $r->text = $_POST['text'];
                $r->type = "Руководству";
                $r->status = 0;
                $r->phone = $phone;
                $r->email = $email;
                $r->status = 0;
                $r->date = date('U');
                $file1 = '';
                $file2 = '';
                $file3 = '';
                list($fn1, $fn2, $fn3) = $_SESSION['upload_name_files'];
                if($fn1) $file1 = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn1;
                if($fn2) $file2 = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn2;
                if($fn3) $file3 = 'https://'.d()->site_url.'/storage/otzyvy/'.$fn3;
                $r->file = $file1;
                $r->file2 = $file2;
                $r->file3 = $file3;
                $r->upload_name_files = json_encode($_SESSION['upload_name_files']);
                $r->save;*/

                $success->text = 'Спасибо, сообщение успешно отправлено. В ближайшее время мы свяжемся с Вами.';
                unset($_SESSION['upload_name_files']);
            }

            if($captcha_action=='details_order'){
                $phone = d()->convert_phone($_POST['phone']);
                // получаем инфо о городе
                get_city();

                $_SESSION['city'] = d()->city->smsc_login;
                $code = d()->send_code($phone, $_GET['first']);
                // записываем инфо в сессию
                $_SESSION['reg_phone'] = $phone;
                if(!$_GET['first'])$_SESSION['order_details']['time'] = date('U');
                $_SESSION['order_details'][$phone]['code'] = $code;

                $success->phone = $phone;
            }

            if($captcha_action=='cancel_order'){
                $phone = d()->convert_phone($_POST['phone']);
                // получаем инфо о городе
                get_city();

                $_SESSION['city'] = d()->city->smsc_login;
                $code = d()->send_code($phone, $_GET['first']);
                // записываем инфо в сессию
                $_SESSION['reg_phone'] = $phone;
                if(!$_GET['first'])$_SESSION['order_cancel']['time'] = date('U');
                $_SESSION['order_cancel'][$phone]['code'] = $code;

                $success->phone = $phone;
            }
        }
    }

    echo json_encode($success);
}

function auth_guard()
{
	if(d()->Auth->is_guest()){
        header('Location: /');
        exit;
    }
}

function redirect_module()
{
    if(url(3)=='crm'){
        get_city();
        if($_SESSION['admin']){
            $getline = '?action=login&user='.$_SESSION['admin'].'&hash='.md5('435gfghngf298sdfjdvkksd2'.$_SESSION['admin'].'!345vbvc3t6YEJDV8dv234');
        }
        //header('Location: https://crm.'.d()->domain.'/'.$getline);
        header('Location: https://crm.appetitfood.ru/'.$getline);
        exit;
    }
}

function printr($m = Array()){
    print '<pre>';
    print_r($m);
    print '</pre>';
    exit();
}

function check_user(){
    if(!$_POST['phone']){
        return 'error';
    }
    $phone = d()->convert_phone($_POST['phone']);
    get_city();
    $u = d()->User->where('phone=? AND city=?', $phone,d()->city->code)->limit(0,1);
    if(count($u)){
        return 'auth';
    }
    return 'reg';
}

// генерация рандомной строки
function randString($length = 10) {
    $characters = 'abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789,.<>/?;:[]{}|~!@#$%^&*()-_+=';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// регистрация пользователей
function ajax_registration(){
    if($_GET['action']=='checkcode'){
        // проверяем совпадает ли код
        $code = str_replace(' ', '', $_POST['code']);
        $ph = $_SESSION['reg_phone'];
        $conf_code = $_SESSION['confirmation'][$ph]['code'];

        if(!$ph && $_POST['phone']){
            $ph = d()->convert_phone($_POST['phone']);
            $_SESSION['reg_phone'] = $ph;
            $c = d()->Code->where('phone = ?', $ph)->order_by('id DESC')->limit(0,1);
            $conf_code = $c->code;
        }else{
            $l = d()->Log->new;
            $l->title = $ph.'|'.$code.'|этот код ввел пользователь';
            $l->text = json_encode($_SESSION);
            $l->save;
        }

        if($conf_code == $code || !$ph){
            if(!$ph){
                // логируем сессию
                $l = d()->Log->new;
                $l->title = 'noregphone';
                $l->text = json_encode($_SESSION);
                $l->save;
            }
            $r = [
                'result' => 1
            ];
            $_SESSION['confirmation'][$ph]['code'] = 'conf';
        }else{
            $r = [
                'result' => 0,
                'text' => 'код подтверждения введен неверно',
                'code' => $code,
            ];
        }
        print json_encode($r);
        exit();
    }

    if(!$_POST['password'] || !$_POST['confirm_password']){
        return 'error';
    }
    if($_POST['password'] != $_POST['confirm_password']){
        return 'Пароли не совпадают';
    }

    $phone = d()->convert_phone($_SESSION['reg_phone']);

    // генерируем пароль, как в битриксе
    $salt = randString(8);
    $pass = $salt.md5($salt.$_POST['password']);
    // генерируем пароль, как в битриксе

    // получаем город из домена
    get_city();
    if(!$_POST['forgot']){
        // регистрация
        $u = d()->User->new;
        $u->phone = $phone;
        $u->password = $pass;
        $u->points = d()->city->points;
        $u->city = d()->city->code;
        // проверяем, есть ли информация из 1С по этому пользователю
        $binfo = d()->Birthday->where('phone = ?', $phone);
        if(!$binfo->is_empty){
            $u->birthday = $binfo->birthday;
            $binfo->delete();
        }
        $user = $u->save_and_load();

        // проверяем, делал ли пользователь заказы с этого номера раньше
        $olist = d()->Order->where('phone = ? AND city_id = ? AND user_id = 0 OR phone = ? AND city_id = ? AND user_id IS NULL', $phone, d()->city->id, $phone, d()->city->id);
        $_SESSION['dbg2'] = $olist;
        if(!$olist->is_empty){
            foreach($olist as $v){
                $o = d()->Order($olist->id);
                $o->user_id = $user->id;
                $o->save;
            }
        }

        // создаем историю начисления баллов
        create_ph($user->id, d()->city->code, 1, d()->city->points);

        d()->Auth->login($user->id);

        return 'success';
    }else{
        // смена пароля
        $u = d()->User->where('phone=? AND city=?',$phone,d()->city->code)->limit(0,1);
        if(!count($u))return 'Ошибка сервера #2';
        $u->password = $pass;
        $u->save;

        return 'forgot_success';
    }


}

// авторизация пользователей
function ajax_auth(){
    if(!$_POST['phone'] || !$_POST['password']){
        return 'error';
    }
    // получаем город из домена
    get_city();
    $phone = d()->convert_phone($_POST['phone']);
    $u = d()->User->where('phone=? AND city=?', $phone, d()->city->code)->limit(0,1);
    if(count($u)){
        $salt = substr($u->password, 0, 8);
        $pass = $salt.md5($salt.$_POST['password']);
        if($pass == $u->password){
            d()->Auth->login($u->id);

            $u->last_login = date('Y-m-d H:i:s');
            $u->save();

            sync_likes($u->id);
            return 'success';
        }
    }
    return 'error';
}

function page_not_found()
{
	ob_end_clean();
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	header('Status: 404 Not Found');
    d()->content = d()->error_404_tpl();
	print d()->main_tpl();
	exit;
}

function get_server(){
    if($_SESSION['admin']) {
        print '<pre>';
        print_r($_SERVER);
        print '</pre>';
        exit;
    }
    d()->page_not_found();
}

function get_session(){
    if($_SESSION['admin']) {
        if($_GET['action']=='clear'){
            foreach($_SESSION as $k=>$v){
                if($k=='admin' || $k=='auth')continue;
                unset($_SESSION[$k]);
            }
            setcookie("wide_single_promo", '', time()-3600, '/');
            header('Location: /get/session');
            exit;
        }
        if($_GET['action']=='clear_zone'){
            unset($_SESSION['zone']);
            unset($_SESSION['delivery_price']);
            header('Location: /get/session');
            exit;
        }

        print '<pre>';
        print_r($_SESSION);
        print '</pre>';
        print '<a href="?action=clear">Очистить</a><br>';
        print '<a href="?action=clear_zone">Очистить зону</a>';
        print '<hr>';
        print '<pre>';
        print_r($_COOKIE);
        print '</pre>';
        exit;
    }
    d()->page_not_found();
}

// удалить адрес из базы
function del_address(){
    if($_POST['id']) {
        $adr = d()->Address->where('id=? AND user_id=?', $_POST['id'], d()->Auth->id)->limit('0, 1');
        if($adr->count){
            $adr->delete;
            exit;
        }
    }
    d()->page_not_found();
}

// редактирование личных данных
function change_personal(){

    if($_POST['type'] && !d()->Auth->is_guest()) {
        $u = d()->Auth->user();
        if($_POST['type']=='name'){
            $u->name = $_POST['data'];
        }
        if($_POST['type']=='phone'){
            $u->phone = d()->convert_phone($_POST['data']);
        }
        if($_POST['type']=='email' && $u->email != $_POST['data']){
            // проверяем email на валидность
            if(!d()->valid_email($_POST['data'], '') && $_POST['data']){
                print 'novalidemail';
                exit;
            }

            $em = d()->User->where('email = ?', $_POST['data']);
            if(!$em->is_empty){
                print 'dbyesemail';
                exit;
            }

            $u->email = trim($_POST['data']);
            // email не подтвержден
            $u->conf_email = 0;

            // запускаем функцию подтверждения E-mail, если он заполнен
            if($_POST['data']) $conf_email = conf_email(trim($_POST['data']), $u);
        }
        if($_POST['type']=='gender'){
            $u->gender = $_POST['data'];
        }
        if($_POST['type']=='haschild'){
            $u->haschild = $_POST['data'];
        }
        if($_POST['type']=='birthday'){
            $u->birthday = $_POST['data'];
        }
        $u->save;

        // запускаем функцию начисления бонусных баллов, если это необходимо
        run_bonus_personal(d()->Auth->id);

        if($_POST['type']=='email' && $u->email == $_POST['data'] && $u->conf_email) {
            print 'nochangeemail';
            exit;
        }

        print 'success';
        exit;
    }
    d()->page_not_found();
}

// редактирование пользователей из админки
function save_users()
{
    $user = d()->User(url(4))->limit(0,1);

    // смена пароля
    if($_POST['data']['password']){
        if($_POST['data']['password'] != $_POST['data']['r_password']){
            header('Location: /admin/edit/users/'.$user->id.'?error=Пароли не совпадают');
            exit;
        }
        // генерируем пароль, как в битриксе
        $salt = randString(8);
        $user->password = $salt.md5($salt.$_POST['data']['password']);
    }

    $user->name = $_POST['data']['name'];
    $user->email = $_POST['data']['email'];
    $user->gender = $_POST['data']['gender'];
    $user->birthday = $_POST['data']['birthday'];
    $user->points = $_POST['data']['points'];
    //$user->utm = $_POST['data']['utm'];

    $user->save();

    header('Location: /admin/list/users?phone='.$_POST['data']['phone']);
    exit;
}

// обновление зон из админки
function save_zonis()
{
    $city = d()->City(url(5))->limit(0,1);

    $page = file_get_contents($_POST['data']['link']);

    // обрезать все, что до символа '"features":'
    $str = strpos($page, '"features":');
    $page = substr($page, $str);

    // обрезать все, после симола ,"map"::
    $str = strpos($page, ',"map":');
    $page = substr($page, 0, $str);

    // получаем массив значений
    $json = '{'.$page.'}';
    $r = json_decode($json, true);

    if(count($r['features'])){
        d()->Check->sql('DELETE FROM `zonis` WHERE `city_id` = '. $city->id);
    }

    // записываем в базу только нужное
    foreach($r['features'] as $k=>$v){
        if($v['type']=='placemark')continue;
        if(!$v['title'])continue;

        // print $v['title'];
        // print '<br><br>';
        // print json_encode($v['geometry']['coordinates'][0]);
        // print '<br><br><br><br>';

        $z = d()->Zoni->new;
        $z->text = $v['title'];
        $z->city_id = $city->id;
        // автозаполнение полей
        $data = explode('.', $v['title']);
        $sub_data = explode('!', $data[3]);
        $tm = substr($sub_data[1], strpos($sub_data[1], ":")+1);
        $tm = str_replace('1 час', '60 мин.', $tm);
        $tm = str_replace('2 часа', '120 мин.', $tm);
        $t = explode('.', $tm);
        $time = preg_replace("/[^0-9]/", '', $t[0])+preg_replace("/[^0-9]/", '', $t[1]);
        $z->title = $data[0];

        $z->price = preg_replace("/[^0-9]/", '', $data[1]);
        $z->min_order = preg_replace("/[^0-9]/", '', $data[2]);
        $z->free = preg_replace("/[^0-9]/", '', $sub_data[0]);
        $z->time = $time.' мин.';

        // автозаполнение полей
        $z->coords = '['.json_encode($v['geometry']['coordinates'][0]).']';
        $z->save;
    }

    // print '<pre>';
    // print_r($r);
    // print '</pre>';
    // exit();

    $_SESSION['check_zone'] = 1;

    header('Location: /admin/list/zonis/city_id/'.url(5));
    exit;
}

// валидация E-mail
function my_valid_email($value,$params)
{
    $value=strtolower($value);
    return ( 1 == preg_match(
            '/^[-a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~]+(?:\.[-a-z0-9!' .
            '\#\$\%\&\'\*\+\/\=\?\^\_\`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*'.
            '(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/' ,$value));
}

// подтверждение E-mail
function conf_email($email, $u){
    // получаем инфо о городе
    get_city();
    // Логин в системе Джастклик
    $user_rs['user_id'] = d()->city->jc_login;
    // Ключ для формирования подписи. См. раздел API (ссылка в правом нижнем углу в личном кабинете)
    $user_rs['user_rps_key'] = d()->city->jc_key;

    // для идентификации
    $check_hash = g_user_hash($u);
    $done_url = 'https://'.$_SERVER['HTTP_HOST'].'/?succesjustclick='.$u->id.'&hash='.$check_hash;

    //$_SESSION['dbg'] = $done_url;

    // Формируем массив данных для передачи в API
    $send_data = array(
        'rid[0]' =>  d()->city->jc_group1, // группа
        'lead_name' => $u->name,
        'lead_email' => $email,
        'lead_phone' => '+'.$u->phone,
        'lead_city' => d()->city->title,
        // адрес после подтверждения подписки
        'doneurl2' => $done_url,
        'activation' => true, // требуем подтверждение подписки
    );
    // Формируем подпись к передаваемым данным
    $send_data['hash'] = JC_GetHash($send_data, $user_rs);
    // Вызываем функцию AddLeadToGroup в API и декодируем полученные данные
    $resp = json_decode(JC_Send('http://'.d()->city->jc_login.'.justclick.ru/api/AddLeadToGroup', $send_data));

    $_SESSION['dbg'] = $resp;

    // Проверяем ответ сервиса
     if(!JC_CheckHash($resp, $user_rs)){
         return "errorhash";
     }
     if($resp->error_code == 0){
         return "success";
         //return "Пользователь добавлен в группу {$send_data['rid[0]']}. Ответ сервиса: {$resp->error_code}";
     }else{
         return "error ".$resp->error_code;
         //return "Ошибка код:{$resp->error_code} - описание: {$resp->error_text}";
     }

}

function ajax_order_info() {
    if($_POST){
        $_SESSION['order_info'][$_POST['name']] = $_POST['val'];
    }
}

function get_cashback($cb=0) {
    return floor(d()->cart_discount_price/100*$cb);
}

// генерация секрутного HASH для пользователя
function g_user_hash($u){
    return md5('SOLn^&'.$u->id.'fjm#^)MNA#XG'.$u->created_at);
}

// служебныефункция для работы API JustClick
// Отправляем запрос в API сервиса
function JC_Send($url, $data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // выводим ответ в переменную
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
// Формируем подпись к передаваемым в API данным
function JC_GetHash($params, $user_rs) {
    $params = http_build_query($params);
    $user_id = $user_rs['user_id'];
    $secret = $user_rs['user_rps_key'];
    $params = "$params::$user_id::$secret";
    return md5($params);
}

// Проверяем полученную подпись к ответу
function JC_CheckHash($resp, $user_rs) {
    $secret = $user_rs['user_rps_key'];
    $code = $resp->error_code;
    $text = $resp->error_text;
    $hash = md5("$code::$text::$secret");
    if($hash == $resp->hash)
        return true; // подпись верна
    else
        return false; // подпись не верна
}
// функция для работы API JustClick

// функция начисления бонусных баллов
function run_bonus_personal($id){
    $u = d()->User->where('id=?', $id)->limit(0,1);
    if(!$u->count){
        return 'error';
    }
    // если бонус уже начислялся
    if($u->bonus_personal){
        return 'error';
    }
    if($u->name && $u->email && $u->conf_email && $u->gender && $u->birthday && $u->haschild){
        // получаем инфо о городе
        get_city();
        $bonus = d()->city->points_personal;

        // начисляем бонусы
        $u->points += $bonus;
        $u->bonus_personal = 1;
        $u->save;

        // создаем историю начислений
        create_ph($u->id, d()->city->code, 2, $bonus);
    }
}

// функция установки телефона в сессию
function set_reg_phone(){
    if($_POST['action']=='set_reg_phone'){
        $_SESSION['reg_phone'] = d()->Auth->user()->phone;
    }
    d()->page_not_found();
}

// функция создания истории начислений баллов
function create_ph($user_id='', $city='', $type='', $value='', $order_id='', $text=''){

    if(!$user_id || !$city || !$type || !$value){
        return;
    }

    // TYPE
    if($type==1){
        $title = 'Баллы за регистрацию';
    }elseif($type==2){
        $title = 'Дополнительные баллы за заполнение личных данных';
    }elseif($type==3){
        $title = 'Списание баллов за заказ №'.$order_id;
    }elseif($type==4){
        $title = 'Возврат';
    }elseif($type==5){
        // любое другое начисление
        $title = $text;
    }elseif($type==6){
        // любое другое списание
        $title = $text;
    }

    get_city();
    $b = d()->Point->new;
    $b->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
    $b->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
    $b->user_id = $user_id;
    $b->city = $city;
    $b->title = $title;
    $b->type = $type;
    $b->value = $value;
    $b->save;

}

// генерация кнопки Показать еще
function g_loadmore($table='', $limit='', $total='', $user_id=''){
    if(!$table){
        return;
    }
    // limit = по сколько выбираем
    // total = сколько уже выбрано

    if($table=='points'){
        $cnt = d()->Point->where('user_id=?',d()->this->id)->count;

        for($i = 2020; $i <= date('Y')-1; $i++){
            $t = 'points_'.$i;
            $cnt += d()->$t->sql('select * from '.$t.' where user_id="'.d()->this->id.'" order by id desc')->count;
        }
        // проверяем, нужна ли кнопка Показать еще
        if($cnt <= $total){
            return;
        }
        return '<div class="loadmore-wrap"><button class="btn btn-loadmore" data-table="'.$table.'" data-limit="'.$limit.'" data-total="'.$total.'" onclick="cab_loadmore(this)">Показать еще</button><img src="/images/loading.gif" alt=""></div>';
    }

    if($table=='orders'){
        $cnt = d()->Order->where('user_id=? AND city_id=?', d()->this->id, d()->city->id)->count;
        for($i = 2020; $i <= date('Y'); $i++){
            $t = 'orders_'.$i;
            $cnt += d()->$t->sql('SELECT * FROM '.$t.' WHERE city_id="'.d()->city->id.'" AND user_id="'.d()->this->id.'" ORDER BY id DESC')->count;
        }
        // проверяем, нужна ли кнопка Показать еще
        if($cnt <= $total){
            return;
        }
        return '<div class="loadmore-wrap"><button class="btn btn-loadmore" data-table="'.$table.'" data-limit="'.$limit.'" data-total="'.$total.'" onclick="cab_loadmore(this)">Показать еще</button><img src="/images/loading.gif" alt=""></div>';
    }

    if($table=='news'){
        $cnt = d()->News->where('city_id=? AND is_active=1', d()->city->id)->count;
        // проверяем, нужна ли кнопка Показать еще
        if($cnt <= $total){
            return;
        }
        return '<div class="loadmore-wrap"><button class="btn btn-loadmore" data-table="'.$table.'" data-limit="'.$limit.'" data-total="'.$total.'" onclick="cab_loadmore(this)">Показать еще</button><img src="/images/loading.gif" alt=""></div>';
    }

    return;
}

// выборка строк по кнопке Показать еще
function ajax_get_more(){
    if(!$_POST['table'] || !$_POST['limit'] || !$_POST['total']){
        d()->page_not_found();
        exit;
    }
    if($_POST['table']=='points'){
        $points_new = d()->Point->where('user_id=?', d()->Auth->id)->order_by('id desc')->to_array();
        for($i = 2020; $i <= date('Y')-1; $i++){
            $t = 'points_'.$i;
            $points_old = d()->Point->sql('select * from '.$t.' where user_id="'.d()->Auth->id.'" order by id desc')->to_array();
            foreach ($points_old as $kpold=>$vpold){
                $points_new[] = $vpold;
            }
        }
        $arr_point = array_slice($points_new, $_POST['total'], $_POST['limit']);
        d()->points_list = d()->Model($arr_point);
        d()->points_list = d()->Point_m($arr_point);
        $cnt = count($points_new);
        //$cnt = d()->points_list->count;

        //d()->points_list->limit($_POST['total'], $_POST['limit']);
        $total = $_POST['limit'] + $_POST['total'];
        $more = 1;
        if($cnt <= $total){
            $more = 0;
        }

        $r = Array();
        $r['result'] = d()->points_line_tpl();
        $r['total'] = $total;
        $r['more'] = $more;

        print json_encode($r);
        exit;
    }

    if($_POST['table']=='orders'){
        get_city();
        $orders_new = d()->Order->where('user_id=? AND city_id=?', d()->Auth->id, d()->city->id)->order_by('id desc')->to_array();
        for($i = 2020; $i <= date('Y'); $i++){
            $t = 'orders_'.$i;
            $orders_old = d()->Order->sql('select * from '.$t.' where city_id="'.d()->city->id.'" and user_id="'.d()->Auth->id.'" order by id desc')->to_array();
            foreach ($orders_old as $kor_old=>$vor_old){
                $orders_new[] = $vor_old;
            }
        }
        $arr_order = array_slice($orders_new, $_POST['total'], $_POST['limit']);
        $cnt = count($orders_new);
        d()->orders_list = d()->Model($arr_order);
        d()->orders_list = d()->Order_c($arr_order);
        //$cnt = d()->orders_list->count;
        //d()->orders_list->limit($_POST['total'], $_POST['limit']);
        $total = $_POST['limit'] + $_POST['total'];
        $more = 1;
        if($cnt <= $total){
            $more = 0;
        }

        $r = Array();
        $r['result'] = d()->orders_line_tpl();
        $r['total'] = $total;
        $r['more'] = $more;

        print json_encode($r);
        exit;
    }

    if($_POST['table']=='news'){
        get_city();
        d()->news_list = d()->News->where('city_id=?', d()->city->id)->order_by('id desc');
        $cnt = d()->news_list->count;
        d()->news_list->limit($_POST['total'], $_POST['limit']);
        $total = $_POST['limit'] + $_POST['total'];
        $more = 1;
        if($cnt <= $total){
            $more = 0;
        }

        $r = Array();
        $r['result'] = d()->news_line_tpl();
        $r['total'] = $total;
        $r['more'] = $more;

        print json_encode($r);
        exit;
    }

    d()->page_not_found();
}

// определяем зону доставки
function ajax_check_zone(){
    $mass = Array();
    if(!$_POST['adr'] && !$_POST['address_id']){
        $mass['result']='zone_error';
        return json_encode($mass);
    }

    $lon = $_POST['lon'];
    $lat = $_POST['lat'];
    $small_adr = $_POST['small_adr'];
    $adr = $_POST['adr'];
    $_SESSION['zone']['address_id'] = '';
    $mass['post'] = $_POST;
    //$_SESSION['address_logs'][] = $small_adr;

    get_city();
    if($_POST['address_id']){
        $al = d()->Address($_POST['address_id'])->limit(0,1);
        $_SESSION['zone']['address_id'] = $_POST['address_id'];
        if($al->lon && $al->lat){
            $lon = $al->lon;
            $lat = $al->lat;
        }else{
            $c = d()->City->where('code=?', $al->city);
            $tbl = 'geo_'.$c->code.'coords';
            if(strpos($al->street, $c->title) !== false){
                $adr = $al->street;
            }else{
                $adr = $c->title.', '.$al->street;
            }

            $geo = d()->Geo_kzncoord->sql('SELECT * FROM `'.$tbl.'` WHERE `title` LIKE "'.$adr.'"');
            if(!$geo->is_empty){
                $crds = explode(',', $geo->coords);
                $lon = $crds[0];
                $lat = $crds[1];
            }
        }
    }else{
        $code = d()->city->code;
        $tbl = 'geo_'.$code.'coords';
        //$_SESSION['adr'] = $adr;
        $geo = d()->Geo_kzncoord->sql('SELECT * FROM `'.$tbl.'` WHERE `title` LIKE "'.$adr.'"');
        if(!$geo->is_empty){
            $crds = explode(',', $geo->coords);
            $lon = $crds[0];
            $lat = $crds[1];
        }
    }


    // используем координаты от Dadata
    //if($lon && $lat || d()->city->id == 5){
    if($lon && $lat){
        $points = Array();
        $points[0] = $lon;
        $points[1] = $lat;
        // TODO: костыль для Окт. Используем координаты DaDaTa
        // if(!$lon && d()->city->id == 5 || !$lat && d()->city->id == 5){
        // unset($points);
        // $points = explode(',', $_POST['ll']);
        // }
        $mass['coords_type'] = 'address_id or caching';
    }else{
        // используем координаты от Яндекса
        $adrold = $adr;

        $adr = str_replace('(бывш. Сибирский тракт)', '', $adr);
        $adr = str_replace('Галеева', 'Бари Галеева', $adr);
        $adr = str_replace('(Отары)', 'Отары', $adr);
        $adr = str_replace('(19 км)', '19 километр', $adr);
        $adr = str_replace('(п Мехзавод)', '19 километр', $adr);
        $adr = str_replace('Нижнегородская', 'Нижнегородская посёлок Сухая Самарка', $adr);
        $adr = str_replace('поселок ОПМС-42', 'улица ОПМС-42', $adr);
        $adr = str_replace('(18 км)', '18 километр', $adr);
        $adr = str_replace('(Константиновка)', 'Константиновка', $adr);
        $adr = str_replace('(Чебакса)', 'Чебакса', $adr);
        $adr = str_replace('(Кадышево)', 'Кадышево', $adr);
        $adr = str_replace('(Салмачи)', 'Салмачи', $adr);
        $adr = str_replace('(Вознесенское)', 'Вознесенское', $adr);
        $adr = str_replace('(Самосырово)', 'Самосырово', $adr);
        $adr = str_replace('(Борисоглебское)', 'Борисоглебское', $adr);

        $_SESSION['dbg11'] = $adr;

        $adr = urlencode($adr);

        //$adr = urlencode('Россия, Респ Татарстан, г Казань, ул Тимирязева д 10/2');

        $ll = '';
        if($_POST['ll'])$ll = '&ll='.$_POST['ll'];
        $url = 'http://geocode-maps.yandex.ru/1.x/?geocode='.$adr.'&format=json&apikey='.d()->city->ya_geo_apikey.$ll;

        $r = json_decode(file_get_contents($url), true);
        //$_SESSION['geocode_maps_yandex_ru'] = $r;
        $pointpos = $r['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
        $mass['pointpos'] = $pointpos;
        $mass['yandex_response'] = $r['response']['GeoObjectCollection']['featureMember'];
        if(
            strpos($adrold, 'Новая Сосновка') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'Трактовая (Исаково)') !== false && strpos($adrold, 'Челябинск') !== false ||
            strpos($adrold, '(свх Кряж)') !== false && strpos($adrold, 'Самара') !== false ||
            strpos($adrold, '(бывш. ул Бари Галеева)') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'г Казань, ул Галеева, д 4А') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'г Казань, ул Галеева, д 8А') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, '(18 км), литер А') !== false && strpos($adrold, 'Самара') !== false ||
            strpos($adrold, '(23 км)') !== false && strpos($adrold, 'Самара') !== false ||
            strpos($adrold, '(Малые Клыки)') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, '(Большие Клыки)') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, '(бывш. Сибирский тракт)') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'г Казань, ул Оренбургский Тракт, зд 209В') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'тер Военный городок-33') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'тер Студеный Овраг, проезд 3-я линия 9-й') !== false && strpos($adrold, 'Самара') !== false ||
            strpos($adrold, 'ул Заречная (Большие Дербышки)') !== false && strpos($adrold, 'Казань') !== false ||
            strpos($adrold, 'тер Коллективный сад N20 ОСТ ОАО УМПО') !== false && strpos($adrold, 'Уфа') !== false ||
            $pointpos == '50.101783 53.195538' ||
            $pointpos == '50.251155 53.323717' ||
            $pointpos == '53.206891 56.852676' ||
            $pointpos == '49.108795 55.796289' ||
            $pointpos == '53.45064 56.830871' ||
            $pointpos == '50.100193 53.195873' ||
            $pointpos == '50.26887 53.277798' ||
            $pointpos == '49.106405 55.796127' ||
            $pointpos == '50.80485 56.659666'
        ){
            // если яндекс определил херню то используем DaDaTa
            $points = explode(',', $_POST['ll']);
            $mass['coords_type'] = 'yandex to dadata';

            $checkcoords = $points[1].', '.$points[0];
            // cstl для адреса г Ижевск, ул Городок Машиностроителей, д 99А
            if($checkcoords == '56.8312216, 53.1118923' && strpos($adrold,'99') !== false){
                $points[1] = '56.830935';
                $points[0] = '53.110672';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Самара, шоссе Московское (п Мехзавод), д 7
            if($checkcoords == '53.1950306, 50.1069518' && strpos($adrold,'д 7') !== false){
                $points[1] = '53.304738';
                $points[0] = '50.292190';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Челябинск, Трактовая (Исаково)
            if($checkcoords == '55.1602624, 61.4008078' && strpos($adrold,'Трактовая (Исаково)') !== false){
                $points[1] = '55.043979';
                $points[0] = '61.421104';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Самара, шоссе Московское (18 км), д 20
            if($checkcoords == '53.1950306, 50.1069518' && strpos($adrold,'г Самара, шоссе Московское (18 км), д 20') !== false){
                $points[1] = '53.281341';
                $points[0] = '50.277862';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Самара, шоссе Московское (18 км), литер А
            if($checkcoords == '53.1950306, 50.1069518' && strpos($adrold,'г Самара, шоссе Московское (18 км), литер А') !== false){
                $points[1] = '53.281475';
                $points[0] = '50.256410';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Казань, ул Озерная (Отары), д 50
            if($checkcoords == '55.73702, 49.239499' && strpos($adrold,'г Казань, ул Озерная (Отары), д 50') !== false){
                $points[1] = '55.707912';
                $points[0] = '49.097826';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
            // cstl для адреса г Казань, Сибирский тракт, д 34в
            if($checkcoords == '55.8190122, 49.1839059' && strpos($adrold,'г Казань, ул Сибирский Тракт (бывш. Сибирский тракт), д 34В') !== false){
                $points[1] = '55.816374';
                $points[0] = '49.181504';
                $mass['coords_type'] = 'yandex to dadata to cstl';
            }
        }else{
            $points = explode(' ', $pointpos);
            $mass['coords_type'] = 'yandex';
        }

        // кэшируем полученные координаты
        if($points[0] && $points[1] && $adrold){
            get_city();
            $code = d()->city->code;
            $tbl = 'geo_'.$code.'coords';
            $pnts = implode(',', $points);
            $geo = d()->Geo_kzncoord->sql('INSERT INTO `'.$tbl.'` (`coords`, `title`) VALUES ("'.$pnts.'", "'.$adrold.'")');
        }
    }
    $coords_type = $mass['coords_type'];

    if($points[0] && $points[1]){
        $geomob = json_decode(d()->geomob($points[0],$points[1]), true);
        $_SESSION['geomob'] = $geomob;
        // не входит в зону доставки
        if($geomob['city_id']){

            $mass = $geomob;
            $mass['tst'] = $adrold;

            $mass['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
            $mass['result'] = 'success';
            $mass['coords_type'] = $coords_type;

            // TODO: Переделать на флаг
            if($_POST['order_flag']){
                $_SESSION['zone']['address'] = $small_adr;
                $_SESSION['zone']['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
                $_SESSION['zone']['title'] = $mass['title'];
                $_SESSION['zone']['text'] = $mass['text'];
                $_SESSION['zone']['price'] = $mass['price'];
                $_SESSION['zone']['min_order'] = $mass['min_order'];
                $_SESSION['zone']['free'] = $mass['free'];
                $_SESSION['zone']['time'] = $mass['time'];
                $_SESSION['zone']['time2'] = $mass['time2'];
                $_SESSION['zone']['time3'] = $mass['time3'];
                $_SESSION['zone']['lat'] = $points[1];
                $_SESSION['zone']['lon'] = $points[0];
                $_SESSION['zone']['category_id'] = $mass['category_id'];

                $_SESSION['delivery_price'] = $mass['price'];
            }
            return json_encode($mass);
        }
    }
    unset($_SESSION['zone']);
    $mass['result']='zone_error';
    $mass["coords"] = $points[1].', '.$points[0];
    return json_encode($mass);
}

function geomob($long=0, $lat=0, $ordersave=0){

    if($_GET["long"] && $_GET["lat"]){
        $long = $_GET["long"];
        $lat = $_GET["lat"];
    }
    get_city();
    $data = Array();
    $zonis = d()->Zoni->where('city_id=?', d()->city->id)->order_by('id desc')->to_array();
    //unset($_SESSION['polygon']);
    //unset($_SESSION['$pointt']);
    $pointt = [$long, $lat];
    foreach($zonis as $k=>$v){
        $zzz = json_decode($v["coords"]);
        //$_SESSION['$pointt'][] = $pointt;
        foreach($zzz as $key=>$value){
            $polygonn = $value;
            //$_SESSION['polygon'][] = $polygonn;
            if(IsPointInside($polygonn, $pointt)==1){
                $data["city_id"] = $v["city_id"];
                $data["min_order"] = $v["min_order"];
                $data["price"] = $v["price"];
                $data["free"] = $v["free"];
                $data["title"] = $v["title"];
                $data["text"] = $v["text"];
                $data["time"] = $v["time"];
                // проверка на наличие промокода с зоной доставки
                if($_SESSION['promocode']['zones']){
                    $zl = explode(',', $_SESSION['promocode']['zones']);
                    $i = 0;
                    foreach ($zl as $v_zl){
                        $zone = str_replace('|', '', $v_zl);
                        $z_list = d()->Zoni($zone);
                        if($z_list->title == $v["title"]){
                            $i = 1;
                        }
                    }
                    if($i == 1) $data["time3"] = $v["time3"];
                }
                //проверка, если есть пиццы в заказе и зона доставки зеленая
                if($v['category_id'] != '' && !$_SESSION['promocode']['zones']){
                    $cat_str = trim($v['category_id'], '|');
                    $cat_id = explode('|', $cat_str);
                    $str_flag = '';
                    foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
                        foreach ($cat_id as $v_cid){
                            if($v_cart['category_id'] == $v_cid) $str_flag .= $v_cart['category_id'].',';
                        }
                    }
                    if(!$str_flag) $data["time2"] = $v["time2"];
                }
                $data['category_id'] = $v['category_id'];
                $data["coords"] = $lat.', '.$long;
            }
        }
    }
    if($_GET["source"]=="1c"){
        if($data["text"]){
            print $data["text"];
        }else{
            print 'not found';
        }
        exit();
    }

    return json_encode($data);
}

// вычисления зон доставки
function IsPointInside($polygon, $point) {
    if(count($polygon) <= 1)
        return false;

    $intersections_num = 0;
    $prev = count($polygon) - 1;
    $prev_under = $polygon[$prev][1] < $point[1];

    for($i = 0; $i < count($polygon); ++$i){
        $cur_under = $polygon[$i][1] < $point[1];

        $a[0] = $polygon[$prev][0] - $point[0];
        $a[1] = $polygon[$prev][1] - $point[1];
        $b[0] = $polygon[$i][0] - $point[0];
        $b[1] = $polygon[$i][1] - $point[1];

        /*
        echo '<br>$a[0] = '.$a[0];
        echo '<br>$a[1] = '.$a[1];
        echo '<br>$b[0] = '.$b[0];
        echo '<br>$a[1] = '.$a[1];
        echo '<hr>';*/

        $t = ($a[0] * ($b[1] - $a[1]) - $a[1] * ($b[0] - $a[0]));
        if($cur_under && !$prev_under){
            if($t > 0)
                $intersections_num += 1;
        }
        if(!$cur_under && $prev_under){
            if($t < 0)
                $intersections_num += 1;
        }

        $prev = $i;
        $prev_under = $cur_under;
    }
    //echo '<h2>$intersections_num = '.$intersections_num.'<h2>';

    return !($intersections_num == 0 || $intersections_num%2 == 0);
}

// сохранение адреса доставки
function ajax_add_address() {
    if(!$_POST['address']){
        return 'address_error';
    }
    if(!$_POST['title']){
        return 'title_error';
    }
    if(d()->Auth->is_guest()){
        return 'auth_error';
    }
    if(!$_POST['is_private']){
        if(!$_POST['room_number'] || !$_POST['entrance'] || !$_POST['floor']){
            return 'room_error';
        }
    }

    get_city();

    if(!$_POST['edit']){
        $a = d()->Address->new;
        $a->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
        $a->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
        $a->user_id = d()->Auth->id;
        $a->city = d()->city->code;
        $a->title = $_POST['title'];
        $a->street = $_POST['address'];
        if($_POST['is_private']){
            $a->is_private = 1;
            $a->apartment = '';
            $a->entrance = '';
            $a->floor = '';
        }else{
            $a->apartment = $_POST['room_number'];
            $a->entrance = $_POST['entrance'];
            $a->floor = $_POST['floor'];
        }
        $a->lat = $_POST['lat'];
        $a->lon = $_POST['lon'];
        $a->save;
        return 'success';
    }else{
        $a = d()->Address->where('id=? AND user_id=?', $_POST['edit'], d()->Auth->id);
        if(!$a->count){
            return 'auth_error';
        }
        $a->user_id = d()->Auth->id;
        $a->title = $_POST['title'];
        $a->street = $_POST['address'];
        if($_POST['is_private']){
            $a->is_private = 1;
            $a->apartment = '';
            $a->entrance = '';
            $a->floor = '';
        }else{
            $a->apartment = $_POST['room_number'];
            $a->entrance = $_POST['entrance'];
            $a->floor = $_POST['floor'];
        }
        $a->lat = $_POST['lat'];
        $a->lon = $_POST['lon'];
        $a->save;
        return 'success_edit';
    }

}

// смена способа доставки
function ajax_delivery_change() {
    if($_POST['type']){
        $_SESSION['delivery'] = $_POST['type'];
        $r = Array();

        // удаляем подарки за самовывоз если есть
        if($_POST['type']==2){
            ajax_remove_gift_pickup();
        }
        if($_POST['type']==1){
            get_cart();
        }
        //if($_POST[''])
        // если пришел запрос на обновление корзины
        if($_POST['change_cart']){
            // пересобираем корзину
            $c = $_SESSION['cart'];
            unset($_SESSION['cart']);
            if($_SESSION['promocode']['products_used'])$_SESSION['promocode']['products_used']=0;
            foreach($c as $k=>$v){
                if($v['property'] == ''){
                    $v['property'] = 0;
                }
                if($v['property'] == 'gift_cash') $v['id'] = $v['id'].'_'.$v['gift_property'];
                ajax_change_cart('add', $v['id'], $v['property']);
                $pid = $v['id'].'_'.$v['property'];

                // добавляем количество, если больше 1 и если это не подарок по промокоду
                if($v['property']=='promo')continue;
                for($i=0;$i<$v['count']-1;$i++){
                    ajax_change_cart('plus', $pid);
                }
            }
            // пересобираем корзину
            get_cart();
            $r['newcart'] = d()->cart_list_tpl();
            $r['total'] = d()->cart_total_price;
            $r['count'] = d()->cart_count;
            $r['points'] = $_SESSION['points'];
            $r['pickup_discount'] = d()->cart_pickup_discount;
            $r['discount_promocode'] = d()->discount_promocode;
            $r['cart_discount_price'] = d()->cart_discount_price;
            $r['header_cart_total_price'] = d()->header_cart_total_price;
        }

        // если промокод нужно удалить
        if($_POST['type'] != $_SESSION['promocode']['delivery'] && $_SESSION['promocode']['delivery']){
            clear_promo();
            $r['del_promo'] = 1;
        }

        $sum = d()->cart_total_price - d()->points_pay; // сумма корзины с учетом баллов
        // отключаем проверку на минимальную сумму при добавлении промокода
        // if($_SESSION['promocode']['min_sum'] && $_SESSION['promocode']['min_sum'] > $sum) {
        //     clear_promo();
        //     $r['del_promo'] = 1;
        // }

        // добавляем проверку, если сумма с учетом промокода будет меньше 0
        if($_SESSION['promocode']['type']==1 && !$_SESSION['promocode']['discount_type'] && ($sum - $_SESSION['promocode']['value'])<1) {
            clear_promo();
            $r['del_promo'] = 1;
        }


        if($r['del_promo'] == 1){
            // пересобираем корзину
            get_cart();
            $r['newcart'] = d()->cart_list_tpl();
            $r['total'] = d()->cart_total_price;
            $r['count'] = d()->cart_count;
            $r['points'] = $_SESSION['points'];
            $r['pickup_discount'] = d()->cart_pickup_discount;
            $r['cart_discount_price'] = d()->cart_discount_price;
        }

        $r['result'] = 'ok';
        return json_encode($r);
        exit;
    }
    d()->page_not_found();
}

// смена способа доставки
function ajax_vk_comment() {
    if($_POST['last_comment'] && $_POST['date']){
        get_city();
        $o = d()->Option;
        if(!d()->Auth->is_guest){
            d()->user = d()->Auth->user;
        }

        $l_subject = 'Новый отзыв с '.$_SERVER['HTTP_HOST'].' (VK)';
        $l_text = '<p><strong>Отзыв:</strong> '.$_POST['last_comment'].'<br><strong>Дата:</strong> '.date('d.m.Y, G:i').'</p>';

        $e = explode(',', d()->city->email_reviews);
        foreach($e as $email){
            d()->Mail->to(trim($email));
            d()->Mail->set_smtp(d()->city->smtp_server,d()->city->smtp_port,d()->city->smtp_mail,d()->city->smtp_password,d()->city->smtp_protocol);
            d()->Mail->from(d()->city->smtp_mfrom,d()->city->smtp_tfrom);
            d()->Mail->subject($l_subject);
            d()->Mail->message($l_text);
            d()->Mail->send();
        }

        $r = d()->Review->new;
        $r->city_id = d()->city->id;
        $r->text = $_POST['last_comment'];
        $r->type = "VK";
        $r->status = 0;
        if(!d()->Auth->is_guest)$r->phone = d()->user->phone;
        $r->date = date('U');
        $r->save;

        exit();
    }
    d()->page_not_found();
}

// получаем UNIX TIME с учетом часового пояса города
function get_dates($city=Array()){
    $time = time();
    d()->unix_time = $time + $city->timezone*3600;
    // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
    $f_unix_time = d()->unix_time - 4*3600;


    d()->n_week = date('N', $f_unix_time);
    d()->fn_week = date('N', d()->unix_time);
    d()->week_day = get_week_day(d()->n_week);
    d()->worktime = d()->city['wt'.d()->n_week];

    $city = d()->city->to_array();

    if(date('H', d()->unix_time)>=0 && date('H', d()->unix_time)<=14){
        d()->f_worktime = explode('-', $city[0]['wt'.d()->fn_week]);
        d()->f_week_day = f_get_week_day(d()->fn_week);
    }else{
        d()->f_worktime = explode('-', $city[0]['wt'.d()->fn_week+1]);
        d()->f_week_day = f_get_week_day(d()->fn_week+1);
    }
    if(d()->f_week_day == 'Вторник' || d()->f_week_day == 'вторник')d()->bukva = 'о';
    d()->f_worktime = d()->f_worktime[0];
}

// получаем день недели с учетом часового пояса города
function get_week_day($n){
    switch($n){
        case 1: return "Понедельник";
        case 2: return "Вторник";
        case 3: return "Среда";
        case 4: return "Четверг";
        case 5: return "Пятница";
        case 6: return "Суббота";
        case 7: return "Воскресенье";
    }
}
function f_get_week_day($n){
    switch($n){
        case 1: return "понедельник";
        case 2: return "вторник";
        case 3: return "среду";
        case 4: return "четверг";
        case 5: return "пятницу";
        case 6: return "субботу";
        case 7: return "воскресенье";
    }
}

// получаем корзину
function get_cart(){
    d()->cart_list = d()->Cart(array_values($_SESSION['cart']));

    // общая сумма корзины без учета скидки за самовывоз и прочих акций
    d()->cart_total_price_nd = 0;
    // общая сумма корзины
    d()->cart_total_price = 0;
    d()->header_cart_total_price = 0;
    // сумма допов
    d()->items_total_price = 0;
    // корзина с учетом скидок
    d()->cart_discount_price = 0;
    // итоговая сумма к оплате
    d()->order_price = 0;
    // оплата баллами
    d()->points_pay = (int) $_SESSION['points'];
    // полная сумма скидки
    d()->discount_sum = 0;
    // сумма скидки за самовывоз
    d()->cart_pickup_discount = 0;
    // количество блюд в корзине
    d()->cart_count = 0;
    // проверка на наличие подарка в корзине (акция день рождения)
    d()->dr_used = 0;
    // проверка на наличие подарка в корзине (за самовывоз)
    d()->gift_pickup_used = 0;
    // скидка по промокоду
    d()->discount_promocode = 0;
    // скидка по промокоду (только на определенные товары), для отображения в хедере
    d()->header_discount_promocode = 0;
    // всего приборов
    d()->total_tableware = 0;
    // сумма товаров Не собственого производства
    d()->total_not_dd = 0;

    // стоимость доставки
    if($_SESSION['delivery']==2){
        d()->delivery_price =  (int) $_SESSION['delivery_price'];
        d()->delivery_price_word = '<em>-</em>';
    }

    if($_SESSION['promocode']['type']==1 && !$_SESSION['promocode']['discount_type']){
        d()->discount_promocode = $_SESSION['promocode']['value'];
    }

    d()->xs_cart_btn = 'none';

    foreach($_SESSION['cart'] as $k=>$v){
        d()->items_total_price += $v['items_price'];
        d()->cart_total_price += $v['total_price']+$v['items_price'];
        d()->cart_pickup_discount += $v['total_pickup_discount'];
        d()->cart_count += $v['count'];
        d()->xs_cart_btn = '';
        if($v['property']=='gift_dr')d()->dr_used = 1;
        if($v['property']=='gift_pickup')d()->gift_pickup_used = 1;

        if($_SESSION['promocode']['type']==1 && $_SESSION['promocode']['discount_type']==1 || $_SESSION['promocode']['type']==2){
            d()->discount_promocode += $v['total_promo_discount'];
            if($v['promo_group']){
                d()->promo_group = $v['promo_group'];
            }
        }

        if($v['tableware']){
            d()->total_tableware += $v['tableware']*$v['count'];
        }

        if($v['not_dd']){
            d()->total_not_dd += $v['total_price'];
        }
    }

    d()->cart_discount_price = d()->cart_total_price - d()->points_pay - d()->discount_promocode;

    d()->order_price = d()->cart_discount_price + d()->delivery_price;
    if(d()->delivery_price)d()->delivery_price_word = '<em>'.d()->delivery_price.'</em><i class="rub">q</i>';

    // определена зона доставки, проверяем стоимость доставки (возможно бесплатно)
    if($_SESSION['zone']['free'] && $_SESSION['zone']['free'] <= d()->cart_discount_price){
        // пересчитаем переменные get_cart
        d()->delivery_price =  0;
        d()->delivery_price_word = '<em style="color:#00a517;">бесплатно</em>';
        d()->order_price = d()->cart_discount_price + d()->delivery_price;
    }

    d()->discount_sum = d()->cart_pickup_discount + d()->discount_promocode;
    d()->cart_total_price_nd = d()->cart_pickup_discount + d()->cart_total_price;

    // для отображения цены корзины с учетом скидки на определенные товары вычитаем из общей стоимости только скидку на определенные товары
    d()->header_cart_total_price = d()->cart_total_price;
    if($_SESSION['promocode']['type']==1 && $_SESSION['promocode']['discount_type']==1 || $_SESSION['promocode']['type']==2){
        d()->header_cart_total_price -= d()->discount_promocode;
        d()->header_discount_promocode = d()->discount_promocode;
    }
}

function check_order_zone(){
    d()->check_zone = 0;
    d()->check_min_sum = 0;

    if(!$_SESSION['zone']['address']){
        d()->check_zone = 0;
        d()->btn_disabled = 'disabled';
        return;
    }
    d()->check_zone = 1;

    if($_SESSION['zone']['min_order'] > d()->cart_discount_price){
        d()->check_min_sum = 0;
        d()->btn_disabled = 'disabled';
        d()->order_error = 'Минимальная сумма заказа, для Вашего адреса (без учета стоимости доставки): <strong>'.$_SESSION['zone']['min_order'].' руб.</strong>';
        d()->order_error_show = 'block';
        return;
    }
    d()->check_min_sum = 1;
}

// cart_list
function ajax_cart_list(){
    get_cart();

    $r = Array();
    $r['list'] = d()->cart_list_tpl();
    $r['ch_list'] = d()->cart_list_ch_tpl();
    $r['total_price'] = d()->cart_total_price;
    $r['total_count'] = d()->cart_count;
    // сумма корзины, с учетом скидки по промокоду
    $r['price_not_points'] = d()->cart_total_price - d()->discount_promocode;
    $r['discount_price'] = d()->cart_discount_price;
    // скидка по промокоду (только на определенные товары)
    $r['header_discount_promocode'] = d()->header_discount_promocode;
    $r['promo_products_used'] = $_SESSION['promocode']['products_used'];
    // скидка по промокоду
    $r['discount_promocode'] = d()->discount_promocode;
    // скидка в процентах
    $r['discount_percent'] = $_SESSION['promocode']['percent'];

    $r['header_cart_total_price'] = d()->header_cart_total_price;

    return json_encode($r);
}

function ajax_remove_gift_dr(){
    foreach($_SESSION['cart'] as $k=>$v){
        if($v['property']=='gift_dr')unset($_SESSION['cart'][$k]);
    }
    if($_POST['check']=='sclear')unset($_SESSION['show_gifts_type']);
}

function ajax_remove_gift_pickup(){
    foreach($_SESSION['cart'] as $k=>$v){
        if($v['property']=='gift_pickup'){
            if($v['autoadd']!= ''){
                $id_gifts = explode(',', $v['autoadd']);
                foreach ($id_gifts as $kig=>$vig){
                    $g = explode('|', $vig);
                    $_SESSION['cart'][$g[0]]['count'] -= $g[1];
                    $_SESSION['gifts_count'] = $_SESSION['cart'][$g[0]]['count'];
                    if($_SESSION['cart'][$g[0]]['count'] == 0){
                        unset($_SESSION['cart'][$g[0]]);
                    }
                }
            }
            unset($_SESSION['cart'][$k]);
        }
    }
    if($_POST['check']=='sclear')unset($_SESSION['show_gifts_type']);
}

function ajax_add_gift(){
    if($_POST['id']){
        $r = Array();
        $pid = explode('_', $_POST['id']);
        $g = d()->Product($pid[0]);
        $error = 0;
        if(!$g->id)$error = 1;

        // проверка на наличие в корзине подарка по акции др
        //foreach($_SESSION['cart'] as $k=>$v){
        //    if($v['property']=='gift_dr')$error = 1;
        //}

        if($error){
            $r['result'] = 'error';
            return json_encode($r);
        }

        $type = $_POST['type'];

        ajax_change_cart('add', $_POST['id'], $type);

        get_cart();
        $r['ch_cart'] = d()->cart_list_ch_tpl();
        $r['cart'] = d()->cart_list_tpl();
        $r['cart_count'] = d()->cart_count;
        $r['header_cart_total_price'] = d()->header_cart_total_price;

        $dop_check = 0;
        $dop_array = Array();
        $checkl = d()->Other->where('product_id = ? AND is_active=1', $g->id);
        if(!$checkl->is_empty){
            $dop_check = 1;
            $dop_array[] = $_POST['id'];
        }
        $r['others'] = $dop_check;
        $r['others_array'] = $dop_array;
        $r['g_id'] = $g->id;

        $r['result'] = 'success';
        return json_encode($r);
    }
    d()->page_not_found();
}

function ajax_run_points(){
    if(isset($_POST['points'])){
        $r = Array();
        if(d()->Auth->is_guest()){
            $r['result'] = 'error';
            $r['text'] = 'пожалуйста <a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>';
            return json_encode($r);
            exit();
        }

        get_cart();
        get_city();
        $points_used = d()->city->points_used/100;
        if((d()->cart_total_price-d()->discount_promocode)*$points_used < $_POST['points']){
            $r['result'] = 'error';
            $r['text'] = 'максимум '.d()->city->points_used.'% от суммы заказа';
            return json_encode($r);
            exit();
        }

        $u = d()->Auth->user();
        if($u->points < $_POST['points']){
            $r['result'] = 'error';
            $r['text'] = 'недостаточно баллов';
            return json_encode($r);
            exit();
        }

        if($_SESSION['promocode']['is_not_points']){
            $r['result'] = 'error';
            $r['text'] = 'не сочетается с промокодом: '.strtoupper($_SESSION['promocode']['title']).'<br>(удалите промкод чтобы списать баллы)';
            return json_encode($r);
            exit();
        }

        $r['result'] = 'success';
        $_SESSION['points'] = $_POST['points'];
        $_SESSION['old_points'] = $_POST['points'];
        return json_encode($r);
        exit();
    }

    d()->page_not_found();
}

function clear_promo(){
    $log = Array();
    $log['old_session'] = $_SESSION;

    // если промокод = подарок, удаляем подарки из корзины
    if($_SESSION['promocode']['type']==3){
        foreach($_SESSION['cart'] as $k=>$v){
            $pos = strpos($k, 'promo');
            if($pos!== false){
                if($v['autoadd']!= ''){
                    $id_gifts = explode(',', $v['autoadd']);
                    foreach ($id_gifts as $kig=>$vig){
                        $g = explode('|', $vig);
                        $_SESSION['cart'][$g[0]]['count'] -= $g[1];
                        $_SESSION['gifts_count'] = $_SESSION['cart'][$g[0]]['count'];
                        if($_SESSION['cart'][$g[0]]['count'] == 0){
                            unset($_SESSION['cart'][$g[0]]);
                        }
                    }
                }
                unset($_SESSION['cart'][$k]);
            }
        }
    }
    // если промокод скидка на определенные товары
    if($_SESSION['promocode']['type']==1 && $_SESSION['promocode']['discount_type']==1 || $_SESSION['promocode']['type']==2){
        foreach($_SESSION['cart'] as $k=>$v){
            if($v['promocode_id']){
                unset($_SESSION['cart'][$k]['promocode_id']);
                unset($_SESSION['cart'][$k]['promo_title']);
                unset($_SESSION['cart'][$k]['promo_count']);
                unset($_SESSION['cart'][$k]['promo_group']);
                unset($_SESSION['cart'][$k]['promo_used']);
                unset($_SESSION['cart'][$k]['promo_discount']);
                unset($_SESSION['cart'][$k]['total_promo_discount']);
            }
        }
    }
    unset($_SESSION['promocode']);

    $log['new_session'] = $_SESSION;

    $l = d()->Log->new;
    $l->title = 'clear_promo';
    $l->text = json_encode($log);
    $l->save;

    points_refresh();
}

function ajax_run_promo(){
    if(isset($_POST['promo'])){
        $r = Array();
        // удаление промокода
        if($_POST['promo_clear']==1){
            clear_promo();
            $r['result'] = 'success';
            if($_POST['get_cart']){
                get_city();
                get_cart();
                $r['ch_cart'] = d()->cart_list_ch_tpl();
                $r['cart'] = d()->cart_list_tpl();
            }
            return json_encode($r);
        }

        get_city();
        get_cart();

        // проверяем, есть ли такой промокод среди активных промокодов этого города
        $promo_ru = d()->promo_trans_en($_POST['promo']);
        $promo_en = d()->promo_trans_ru($_POST['promo']);

        //$promo_ru = $_POST['promo'];
        //$promo_en = $_POST['promo'];

        $promo = d()->Promocode->where('name=? AND city_id=? OR name=? AND city_id=?', $promo_ru, d()->city->id, $promo_en, d()->city->id)->limit(0,1);
        if($promo->is_empty()){
            $r['result'] = 'error';
            $r['text'] = 'промокод не найден';
            return json_encode($r);
        }

        $check = 1;
        // проверка на период действия
        if($promo->start_date || $promo->end_date){
            $d = date('U') + d()->city->timezone*3600;
            if($promo->start_date && $promo->start_date > $d){
                $check = 0;
            }
            if($promo->end_date && $promo->end_date+86399 < $d){
                $check = 0;
            }
        }
        // проверка на использование одноразового уникального
        if($promo->is_single && $promo->used){
            $check = 0;
        }
        if(!$promo->is_active || !$check){
            $r['result'] = 'error';
            $r['text'] = 'промокод не активен';
            return json_encode($r);
        }
        if($promo->only_app){
            $r['result'] = 'error';
            $r['text'] = 'промокод для мобильного приложения';
            return json_encode($r);
        }
        if($promo->delivery){
            if(!$_SESSION['delivery'])$_SESSION['delivery'] = d()->city->delivery;
            if($promo->delivery != $_SESSION['delivery']){
                $r['result'] = 'error';
                $r['text'] = 'промокод только для доставки';
                if($promo->delivery==1){
                    $r['text'] = 'промокод только для самовывоза';
                }
                return json_encode($r);
            }
        }
        if($promo->is_auth && d()->Auth->is_guest()){
            $r['result'] = 'error';
            $r['text'] = 'пожалуйста <a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>';
            return json_encode($r);
        }

        if($promo->is_sleep && d()->Auth->is_guest()){
            $r['result'] = 'error';
            $r['text'] = 'пожалуйста <a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>';
            return json_encode($r);
        }

        if($promo->is_sleep && !d()->Auth->is_guest()){
            $u = d()->Auth->user();
            $sleep_check = d()->Sleep_phone->where('phone = ? AND promocode_id = ?', $u->phone, $promo->id);
            if($sleep_check->is_empty){
                $r['result'] = 'error';
                $r['text'] = 'к сожалению, этот промокод для вас недоступен';
                return json_encode($r);
            }
        }

        // проверка на нового клиента
        if($promo->is_new && d()->Auth->is_guest()){
            $r['result'] = 'error';
            $r['text'] = 'пожалуйста <a href="#auth-modal" data-toggle="modal">авторизуйтесь</a>';
            return json_encode($r);
        }

        if($promo->is_new && !d()->Auth->is_guest()){
            $new_u = d()->Auth->user();
            $orders = d()->Order->where('phone = ?', $new_u->phone)->count;
            $str_order = 0;
            if($orders == 0){
                for ($i = 2020; $i <= date('Y'); $i++) {
                    $t = 'orders_' . $i;
                    $orders_old = d()->Order->sql('select * from ' . $t . ' where `phone` ='.$new_u->phone)->count;
                    $str_order = $orders_old;
                }
            }else{
                $str_order = $orders;
            }
            if($str_order != 0){
                $r['result'] = 'error';
                $r['text'] = 'к сожалению, этот промокод для вас недоступен';
                return json_encode($r);
            }
        }

        // проверка на дни недели
        if($promo->week_days){
            $n = date('N', date('U')+d()->city->timezone*3600);
            if(strpos($promo->week_days, $n) === false){
                $r['result'] = 'error';
                $r['text'] = 'сегодня промокод не доступен';
                return json_encode($r);
            }
        }

        // проверка на даты исключения
        if($promo->exceptions_dates){
            $n = date('d.m', date('U')+d()->city->timezone*3600);
            if(strpos($promo->exceptions_dates, $n) !== false){
                $r['result'] = 'error';
                $r['text'] = 'сегодня промокод не доступен';
                return json_encode($r);
            }
        }


        // отключаем проверку на минимальную сумму при добавлении промокода
        //if($promo->min_sum && $promo->min_sum > d()->cart_discount_price){
        //    $r['result'] = 'error';
        //    $r['text'] = 'минимальная сумма заказа '.$promo->min_sum.' руб.';
        //    return json_encode($r);
        //}

        // защита от отрицательной суммы, если промокод скидка в руб на весь заказ
        if($promo->type==1 && !$promo->discount_type && (d()->cart_discount_price-$promo->value)<1){
           $r['result'] = 'error';
            $r['text'] = 'очень маленькая сумма заказа';
            return json_encode($r);
        }


        if($promo->is_wide_single) {
            // проверяем промокод в куках
            $pos = strpos($_COOKIE['wide_single_promo'], '|' . $promo->id . '|');
            if ($pos !== false) {
                $r['result'] = 'error';
                $r['text'] = 'промокод уже использован';
                return json_encode($r);
            }
        }
        // проверка на наличие акционных товаров в корзине (к которым должна применяться скидка)
        if($promo->type == 1 && $promo->discount_type == 1) {
            $incart = 0;
            $products_temp = explode(',', $promo->products);
            d()->products = Array();
            $search_keys = '|'.implode('|', array_keys($_SESSION['cart']));
            foreach($products_temp as $k=>$v){
                $a = explode('|', $v);
                d()->products[$a[0]] = $a;

                $findme = '|'.$a[0].'_';
                if(strpos($search_keys, $findme) !== false)$incart = 1;
                //if($_SESSION['cart'][$a[0]]['id'])$incart = 1;
            }
            if(!$incart){
                $r['result'] = 'error';
                $r['text'] = 'в корзине отсутствуют блюда по промокоду';
                return json_encode($r);
            }
        }

        // проверка на наличие обязательных блюд в корзине
        if($promo->required_products) {
            $incart = 0;
            $products_temp = explode(',', $promo->required_products);
            $cnt = count($products_temp);
            d()->r_products = Array();
            $i = 0;
            $search_keys = '|'.implode('|', array_keys($_SESSION['cart']));
            foreach($products_temp as $k=>$v){
                $a = str_replace('|', '', $v);
                d()->r_products[$a] = $a;
                $findme = '|'.$a.'_';
                if(strpos($search_keys, $findme) !== false){
                    $incart = 1;
                    $i++;
                }
                //if($_SESSION['cart'][$a]['id'])$incart = 1;
            }
            if(!$incart){
                $r['result'] = 'error';
                $r['text'] = 'в корзине отсутствуют обязательные блюда';
                return json_encode($r);
            }elseif ($promo->allorseveral == 1) {
                if($cnt != $i){
                    $r['result'] = 'error';
                    $r['text'] = 'в корзине добавлены не все обязательные блюда';
                    return json_encode($r);
                }
            }elseif ($promo->allorseveral == 2) {
                if($promo->several_quantity != $i){
                    $r['result'] = 'error';
                    $r['text'] = 'в корзине добавлены не все обязательные блюда';
                    return json_encode($r);
                }
            }
        }

        // проверка промокода на категории
        if($promo->sales_category) {
            $sc = trim($promo->sales_category, '|');
            $sc1 = explode('|', $sc);
            $category_incart = '';
            foreach ($sc1 as $k_sc1=>$v_sc1){
                foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
                    if($v_cart['category_id'] == $v_sc1) $category_incart .= $v_cart['category_id'].',';
                }
            }
            if($category_incart){
                $t_cat = trim($category_incart, ',');
                $title_cat = explode(',', $t_cat);
                $str_title_cat = '';
                foreach ($title_cat as $vtcat){
                    $str1 = d()->Categorie($vtcat);
                    $str_title_cat .= $str1->title;
                }
                $r['result'] = 'error';
                $r['text'] = 'Промокод не применяется к товарам категории '.$str_title_cat;
                return json_encode($r);
            }
        }

        // проверка промокода на определенный товар
        if($promo->sales_products) {
            $sp = explode(',', $promo->sales_products);
            $str_sales_products = 0;
            d()->products4 = Array();
            $i = 0;
            $search_products = '|'.implode('|', array_keys($_SESSION['cart']));
            foreach ($sp as $vsp){
                $spp = str_replace('|', '', $vsp);
                d()->products4[$spp] = $spp;
                $findme2 = '|'.$spp.'_';
                if(strpos($search_products, $findme2) !== false){
                    $str_sales_products = 1;
                    $i++;
                }
            }
            if($str_sales_products){
                $str_title_pr = '';
                foreach (d()->products4 as $vtpr){
                    $t_p = explode('_', $vtpr);
                    $str1 = d()->Product($t_p[0]);
                    $str_title_pr .= $str1->title;
                }
                $r['result'] = 'error';
                $r['text'] = 'Промокод не применяется к товару '.$str_title_pr;
                return json_encode($r);
            }
        }

        // проверка на временные рамки
        $check = check_promotime($promo->start_time, $promo->end_time);
        if(!$check){
            $r['result'] = 'error';
            if($promo->start_time && !$promo->end_time){
                $r['text'] = 'промокод доступен с '.$promo->start_time.':00';
            }elseif(!$promo->start_time && $promo->end_time){
                $r['text'] = 'промокод доступен до '.$promo->end_time.':00';
            }else{
                $r['text'] = 'промокод доступен с '.$promo->start_time.':00 до '.$promo->end_time.':00';
            }
            return json_encode($r);
        }

        // промокод не сочетается с баллами
        if($promo->is_not_points && $_SESSION['points']) {
            $r['result'] = 'error';
            $r['text'] = 'промокод не сочетается с баллами<br>(отмените списание баллов, чтобы применить промокод)';
            return json_encode($r);
        }

        // промокод не сочетается с подаками на ДР
        if($promo->is_not_dr) {
            foreach($_SESSION['cart'] as $vl){
                if($vl['property']=='gift_dr' && $promo->is_not_dr){
                    $r['result'] = 'error';
                    $r['text'] = 'промокод не сочетается с подарками на День Рождения';
                    return json_encode($r);
                    break;
                }
            }
        }

        //промокод не сочитается с подарками за самовывоз
        if($promo->is_not_delivery) {
            foreach($_SESSION['cart'] as $vl){
                if($vl['property']=='gift_pickup' && $promo->is_not_delivery){
                    $r['result'] = 'error';
                    $r['text'] = 'промокод не сочетается с подарками за Самовывоз';
                    return json_encode($r);
                    break;
                }
            }

        }

        // если промокод - скидка %
        $percent = 0;
        if($promo->type == 2){
            $percent = $promo->value;
        }

        $_SESSION['promocode'] = Array(
            'title' => $promo->name,
            'id' => $promo->id,
            'start_date' => $promo->start_date,
            'end_date' => $promo->end_date,
            'delivery' => $promo->delivery,
            'is_auth' => $promo->is_auth,
            'is_single' => $promo->is_single,
            'min_sum' => $promo->min_sum,
            'min_sum_points' => $promo->min_sum_points,
            'is_wide_single' => $promo->is_wide_single,
            'type' => $promo->type,
            'discount_type' => $promo->discount_type,
            'value' => $promo->value,
            'percent' => $percent,
            'round' => $promo->round,
            'products' => $promo->products,
            'products_limit' => $promo->products_limit,
            'products_used' => 0,
            'required_products' => $promo->required_products,
            'week_days' => $promo->week_days,
            'is_not_points' => $promo->is_not_points,
            'is_not_dr' => $promo->is_not_dr,
            'is_not_delivery' => $promo->is_not_delivery,
            'exceptions_dates' => $promo->exceptions_dates,
            'birthday' => $promo->birthday,
            'min_sum_notdd' => $promo->min_sum_notdd,
            'zones' => $promo->zones,
            'allorseveral' => $promo->allorseveral,
            'several_quantity' => $promo->several_quantity,
            'sales_category' => $promo->sales_category,
            'sales_products' => $promo->sales_products,
            'is_new' => $promo->is_new,
        );

        // если промокод - подарок, добавляем его в корзину
        if($promo->type == 3){
            $gifts = explode(',', $promo->gift);
            $dop_check = 0;
            $dop_array = Array();
            foreach($gifts as $k=>$v){
                $v = str_replace('|', '', $v);
                $pid = explode('_', $v);
                $g = d()->Product($pid[0]);
                if(!$g->id)continue;
                ajax_change_cart('add', $v, 'promo');

                $dop = explode('_', $v);
                $checkl = d()->Other->where('product_id = ? AND is_active=1', $dop[0]);
                if(!$checkl->is_empty){
                    $dop_check = 1;
                    $dop_array[] = $v;
                }
            }
        }

        $discount = 0;

        // если промокод - скидка руб
        //unset($_SESSION['dbg']);
        if($promo->type == 1){
            // если скидка только на определенные товары
            if($promo->discount_type == 1){
                $pp_count = 0;
                $check_group = 0;
                // массив для отслеживания количества с учетом допов
                $maxcnt = Array();
                foreach($_SESSION['cart'] as $k=>$v){
                    // айдишник без допов
                    $tmp = explode('_',$k);
                    array_pop($tmp);
                    $ktmp = implode('_', $tmp);

                    // определяем группу товара
                    $group = d()->products[$ktmp][3];
                    // проверка скидку у товаров из другой группы
                    if($check_group != $group && $check_group) {
                        continue;
                    }

                    // лимит группы
                    $lim = explode(',', $promo->products_limit);
                    foreach($lim as $vvv){
                        $limit = explode('_', $vvv);
                        if($limit[1] == $group){
                            $products_limit = $limit[0];
                        }
                    }
                    if(!$products_limit)$products_limit = 9999;

                    //$_SESSION['dbg'][$k][$pp_count] = $pp_count;
                    //$_SESSION['dbg'][$k][$products_limit] = $products_limit;

                    $pcnt = d()->products[$ktmp][1];
                    if(!$pcnt)$pcnt = 9999;

                    // проверка на колво с допами
                    if($maxcnt[$ktmp]){
                        if($pcnt <= $maxcnt[$ktmp])continue;
                        $pcnt = $pcnt - $maxcnt[$ktmp];
                    }

                    if(d()->products[$ktmp][0] && $products_limit > $pp_count && $pcnt){
                        if($_SESSION['cart'][$k]['property']=='gift_dr' || $_SESSION['cart'][$k]['property']=='gift_pickup')continue;

                        $check_group = $group;

                        $_SESSION['cart'][$k]['promocode_id'] = $_SESSION['promocode']['id'];
                        $_SESSION['cart'][$k]['promo_title'] = 'Скидка по промокоду '.strtoupper($promo->name);

                        $_SESSION['cart'][$k]['promo_count'] = $pcnt;
                        $promo_count = $_SESSION['cart'][$k]['count'];
                        if($_SESSION['cart'][$k]['count'] > $pcnt)$promo_count = $pcnt;

                        if($products_limit <= ($pp_count+$promo_count)){
                            $promo_count = $products_limit - $pp_count;
                        }

                        $_SESSION['cart'][$k]['promo_discount'] = $_SESSION['cart'][$k]['price'] - d()->products[$ktmp][2];
                        $_SESSION['cart'][$k]['total_promo_discount'] = $_SESSION['cart'][$k]['promo_discount'] * $promo_count;
                        $_SESSION['cart'][$k]['promo_used'] = $promo_count;
                        $_SESSION['cart'][$k]['promo_group'] = $group;

                        $discount += $_SESSION['cart'][$k]['total_promo_discount'];
                        $pp_count += $promo_count;
                        $_SESSION['promocode']['products_used'] += $promo_count;

                        if(!$maxcnt[$ktmp]){
                            $maxcnt[$ktmp] = $promo_count;
                        }else{
                            $maxcnt[$ktmp] += $promo_count;
                            $dopfrch = 1;
                        }
                    }
                }
                // если есть 2 одинаковых товара с разными допами
                // крутим foreach что бы проверить promo_count
                if($dopfrch){
                    foreach($_SESSION['cart'] as $k=>$v){
                        // айдишник без допов
                        $tmp = explode('_',$k);
                        array_pop($tmp);
                        $ktmp = implode('_', $tmp);

                        $pcnt = d()->products[$ktmp][1];
                        if(!$pcnt)$pcnt = 9999;
                        // проверка на колво с допами
                        if($maxcnt[$ktmp]){
                            if($maxcnt[$ktmp] >= $pcnt){
                                $_SESSION['cart'][$k]['promo_count'] = $_SESSION['cart'][$k]['promo_used'];
                            }else{
                                $_SESSION['cart'][$k]['promo_count'] = $_SESSION['cart'][$k]['promo_used']+$pcnt-$maxcnt[$ktmp];
                            }
                        }
                    }
                }
            }
            // пересчет баллов
            points_refresh();
        }

        // если промокод - скидка %
        if($promo->type == 2){
            foreach($_SESSION['cart'] as $k=>$v){
                // если скидка на весь заказ, кроме товаров не собственнго производства и этот товар не собственного производства
                if($promo->discount_type == 2 && $_SESSION['cart'][$k]['not_dd'])continue;

                if($_SESSION['cart'][$k]['property']=='gift_dr' || $_SESSION['cart'][$k]['property']=='gift_pickup')continue;

                $ip = $_SESSION['cart'][$k]['items_price']/$_SESSION['cart'][$k]['count'];
                $d = price_round($_SESSION['cart'][$k]['price']+$ip, $percent, $promo->round);
                $_SESSION['cart'][$k]['promocode_id'] = $_SESSION['promocode']['id'];
                $_SESSION['cart'][$k]['promo_title'] = 'Скидка по промокоду '.strtoupper($promo->name);
                $_SESSION['cart'][$k]['promo_discount'] = $d;
                $_SESSION['cart'][$k]['total_promo_discount'] = $d * $_SESSION['cart'][$k]['count'];
                $discount += $d * $_SESSION['cart'][$k]['count'];
            }
            // пересчет баллов
            points_refresh();
        }

        //проверка на день рождение
        /*if($promo->birthday == 1){
            $r['birthday'] = 1;
            return json_encode($r);
        }*/

        get_cart();
        $r = Array(
            'result' => 'success',
            'title' => $promo->name,
            'delivery' => $promo->delivery,
            'min_sum' => $promo->min_sum,
            'type' => $promo->type,
            'discount_type' => $promo->discount_type,
            'value' => $promo->value,
            'percent' => $percent,
            'promo_sum' => d()->discount_promocode,
            'round' => $promo->round,
            'products' => $promo->products,
            'products_limit' => $promo->products_limit,
            'products_group' => d()->promo_group,
            'products_discount' => $discount,
            'total_count' => d()->cart_count,
            'header_cart_total_price' => d()->header_cart_total_price,
            'required_products' => $promo->required_products,
            'ch_cart' => d()->cart_list_ch_tpl(),
            'cart' => d()->cart_list_tpl(),
            'week_days' => $promo->week_days,
            'is_not_points' => $promo->is_not_points,
            'is_not_dr' => $promo->is_not_dr,
            'is_not_delivery' => $promo->is_not_delivery,
            'exceptions_dates' => $promo->exceptions_dates,
            'others' => $dop_check,
            'others_array' => $dop_array,
            'birthday' => $promo->birthday,
            'min_sum_notdd' => $promo->min_sum_notdd,
            'zones' => $promo->zones,
            'allorseveral' => $promo->allorseveral,
            'several_quantity' => $promo->several_quantity,
            'sales_category' => $promo->sales_category,
            'sales_products' => $promo->sales_products,
            'is_new' => $promo->is_new,
        );
        return json_encode($r);
    }

    d()->page_not_found();
}

// получение цены с учетом скидки
function get_discount_price($p='', $not=0, $d=''){
    $price = $p;

    // для ajax запросов
    if(!d()->city->id)get_city();

    // скидка за самовынос
    if(!$not){
        // переменная $d для того что бы посчитать скидку независимо от текущего состояния доставки
        if(!$d){
            $d = $_SESSION['delivery'];
            if(!$d) $d = d()->city->delivery;
        }

        if(d()->city->discount_delivery && $d==1){
            $cf = 100 - d()->city->discount_delivery;
            $price = $p/100*$cf;
            switch (d()->city->dd_round) {
                case 0:
                    $price = ceil($price);
                    break;
                case 1:
                    $price = ceil($price/5) * 5;
                    break;
                case 2:
                    $price = ceil($price/10) * 10;
                    break;
            }
        }
    }
    // скидка за самовынос

    if($p<$price) return $p;
    return $price;
}


// изменение properties not_dd, в зависимости от изминения products
function admin_properties_edit(){
    if($_POST['element_id']=='add'){
        // событие на создание свойства
        $p = d()->Product($_POST['data']['product_id']);
        $_POST['data']['not_dd'] = $p->not_dd;

        unset($_POST['data']['products']);
    }else{
        // событие на радактирование продукта
        d()->Property->sql('UPDATE properties SET not_dd="'.$_POST['data']['not_dd'].'" WHERE product_id="'.$_POST['element_id'].'"');

        // проверяем, нужно ли редактировать другие товары
        if($_POST['data']['products']){
            $prds = explode(',', $_POST['data']['products']);
            // переменные для изменения в доп товарах
            $check = d()->Product($_POST['element_id']);
            $change = Array();
            if($check->is_active != $_POST['data']['is_active'])$change[] = 'is_active';
            if($check->title != $_POST['data']['title'])$change[] = 'title';
            if($check->image != $_POST['data']['image'])$change[] = 'image';
            if($check->image_alt != $_POST['data']['image_alt'])$change[] = 'image_alt';
            if($check->image_title != $_POST['data']['image_title'])$change[] = 'image_title';
            if($check->sostav != $_POST['data']['sostav'])$change[] = 'sostav';
            if($check->text != $_POST['data']['text'])$change[] = 'text';
            if($check->url != $_POST['data']['url'])$change[] = 'url';
            if($check->price != $_POST['data']['price'])$change[] = 'price';
            if($check->weight != $_POST['data']['weight'])$change[] = 'weight';
            if($check->weight_type != $_POST['data']['weight_type'])$change[] = 'weight_type';
            if($check->number != $_POST['data']['number'])$change[] = 'number';
            if($check->not_dd != $_POST['data']['not_dd'])$change[] = 'not_dd';
            if($check->sticker != $_POST['data']['sticker'])$change[] = 'sticker';

            foreach($prds as $k=>$v){
                $p = d()->Product($v);
                foreach($change as $key => $val){
                    $p[$val] = $_POST['data'][$val];
                }
                $p->save;
            }
        }
        unset($_POST['data']['products']);
    }
    if(strpos($_POST['data']['category_id'], '|') === false){
        $_POST['data']['category_id'] = '|'.$_POST['data']['category_id'].'|';
    }
}

function admin_promocodes_edit(){
    if($_POST['data']['start_date']){
        $_POST['data']['start_date'] = strtotime($_POST['data']['start_date']);
    }else{
        $_POST['data']['start_date'] = 0;
    }

    if($_POST['data']['end_date']){
        $_POST['data']['end_date'] = strtotime($_POST['data']['end_date']);
    }else{
        $_POST['data']['end_date'] = 0;
    }

    if($_POST['data']['discount_type']==1){
        $_POST['data']['value'] = 0;
    }
    //$_SESSION['debug'] = $_POST['data'];
}

function admin_cities_edit(){
    if($_POST['data']['dd_type']==1){
        $_POST['data']['discount_delivery'] = 0;
        $_POST['data']['dd_round'] = 0;
    }
    if(!$_POST['data']['dd_type']){
        $_POST['data']['dd_gifts'] = '';
    }
}

function do_robots(){
    $file = fopen($_SERVER['DOCUMENT_ROOT'].'/robots.txt', 'w');
    fwrite($file, $_POST['data']['text']);
    fclose($file);
    return  "<script>window.open('','_self','');window.close();</script>";
}

function do_cities_wt(){
    $c = d()->City(url(4));
    $c->wt1 = $_POST['data']['wt1'];
    $c->wt2 = $_POST['data']['wt2'];
    $c->wt3 = $_POST['data']['wt3'];
    $c->wt4 = $_POST['data']['wt4'];
    $c->wt5 = $_POST['data']['wt5'];
    $c->wt6 = $_POST['data']['wt6'];
    $c->wt7 = $_POST['data']['wt7'];
    $c->is_error_wt = $_POST['data']['is_error_wt'];
    $c->is_modal_wt = $_POST['data']['is_modal_wt'];
    $c->wt_pickup = $_POST['data']['wt_pickup'];

    $c->is_stop_order = $_POST['data']['is_stop_order'];
    $c->stop_cause = $_POST['data']['stop_cause'];
    $c->pre_order_today = $_POST['data']['pre_order_today'];
    $c->pre_order_tomorrow = $_POST['data']['pre_order_tomorrow'];
    $c->is_info = $_POST['data']['is_info'];
    $c->info_cause = $_POST['data']['info_cause'];

    $c->save;

    header('Location: /admin/list/cities');
    exit();
}

function do_products(){
    $element_id = $_POST['element_id'];
    if($element_id == 'add'){
        $type = 'add';

        if(count($_POST['dopcity'])){
            foreach($_POST['dopcity'] as $k=>$v){

                if($v['category']){
                    $city = $v['id'];
                    $category = '|'.str_replace(',', '|', $v['category']).'|';
                    $subcategory = '';
                    if($v['subcategory']){
                        $subcategory = '|'.str_replace(',', '|', $v['subcategory']).'|';
                    }
                    $p = d()->Product->new;
                    foreach($_POST['data'] as $key=>$value){
                        if($key=='city_id')$value = $city;
                        if($key=='category_id')$value = $category;
                        if($key=='subcategory_id')$value = $subcategory;

                        /*if($key=='filter'){
                            $fs = explode(',', $value);
                            $value = '';
                            foreach($fs as $fv){
                                $f = d()->Filter($fv);
                                $filter = d()->Filter->where('city_id = ?', $city)->search('title', $f->title);
                                if(!$filter->is_empty){
                                    $value .= $filter->id.',';
                                }
                            }
                            if($value)$value = substr($value,0,-1);
                        }*/
                        //if($key=='autoadd_products')$value = '';
                        if($key=='autoadd_products'){
                            if($value){
                                $auto_array = Array();
                                $ap = explode(',', $value);
                                foreach ($ap as $kap=>$vap){
                                    $autop = explode('|', $vap);
                                    $aprod = explode('_', $autop[0]);
                                    $prod_auto = d()->Product($aprod[0]);
                                    if($prod_auto){
                                        $dop_auto = d()->Product->where('title =? AND city_id =? AND price = 0 OR title =? AND city_id =? AND price != 0', $prod_auto->title, $city, $prod_auto->title, $city)->to_array();
                                        if($dop_auto){
                                            foreach ($dop_auto as $k_dop_auto=>$v_dop_auto){
                                                if($prod_auto->price > 0 && $v_dop_auto['price'] > 0){
                                                    $auto_array[] = $v_dop_auto['id'].'_'.$aprod[1].'|'.$autop[1];
                                                }elseif ($prod_auto->price == 0 && $v_dop_auto['price'] == 0){
                                                    $auto_array[] = $v_dop_auto['id'].'_'.$aprod[1].'|'.$autop[1];
                                                }
                                            }
                                        }else{
                                            $dop_auto_2 = d()->Product->where('id_1c =? AND city_id =? AND price = 0 OR id_1c =? AND city_id =? AND price != 0', $prod_auto->id_1c, $city, $prod_auto->id_1c, $city)->to_array();
                                            if($dop_auto_2){
                                                foreach ($dop_auto_2 as $kdopauto2=>$vdopauto2){
                                                    if($prod_auto->price > 0 && $vdopauto2['price'] > 0){
                                                        $auto_array[] = $vdopauto2['id'].'_'.$aprod[1].'|'.$autop[1];
                                                    }elseif ($prod_auto->price == 0 && $vdopauto2['price'] == 0){
                                                        $auto_array[] = $vdopauto2['id'].'_'.$aprod[1].'|'.$autop[1];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $value = implode(',', $auto_array);
                            }
                        }
                        if($key=='dop_category'){
                            $dc = trim($value, '|');
                            $dc1 = explode('|', $dc);
                            foreach ($dc1 as $k_dc1=>$v_dc1){
                                $dop_cat = d()->Categorie->where('id =?', $v_dc1);
                                $dop_cat_list = d()->Categorie->where('title = ? AND city_id = ?', $dop_cat->title, $city);
                                if(!$dop_cat_list->is_empty){
                                    $value .= '|'.$dop_cat_list->id.'|';
                                }
                            }
                        }

                        //$_SESSION['debug']['foreach'][$city][$key] = $value;
                        $p[$key] = $value;
                    }
                    $product = $p->save_and_load();
                    $_SESSION['debug']['save_and_load'][] = $product->id;

                    $l = d()->Log_product->new;
                    $l->type = $type;
                    $l->product_id = $product->id;
                    $l->save;
                }
            }
        }

        // костылек
        $e = d()->Product->new;
        $e->title = 'test';
        $elem = $e->save_and_load();
        $element_id = $elem->id+1;
        $e = d()->Product($elem->id);
        $e->delete;
        // костылек

        // если есть еще города, в которые нужно добавить этот товар
        $_SESSION['POST'] = $_POST;

    }elseif($_POST['_action'] == 'admin_delete_element'){
        $type = 'delete';
    }else{
        $type = 'edit';
        //$_SESSION['POST']=$_POST;
        //$_SESSION['products']=$_POST['products'];

        $edit_product = d()->Product->where('id=?', $_POST['element_id']);
        $ar = Array();
        foreach ($_POST['data'] as $k_pd=>$v_pd){
            if($_POST['data'][$k_pd] != strip_tags($edit_product[$k_pd])) $ar[$k_pd] = $v_pd;
        }

        if(count($_POST['products'])){
            foreach($_POST['products'] as $k=>$v){
                $dop_product = explode(',', $v);
                foreach ($dop_product as $k_dp=>$v_dp)
                {
                    $product_element = d()->Product->where('id=?', $v_dp);
                    if(!$product_element->is_empty){
                        $city = $product_element->city_id;
                        $category = $product_element->category_id;
                        $subcategory = $product_element->subcategory_id;
                        //$autoadd = $product_element->autoadd_products;
                        foreach ($ar as $k_d=>$v_d){
                            if($k_d == 'city_id')$v_d = $city;
                            if($k_d == 'category_id') $v_d = $category;
                            if($k_d == 'subcategory_id') $v_d = $subcategory;
                            //if($k_d == 'autoadd_products') $v_d = $autoadd;
                            if($k_d == 'autoadd_products'){
                                $auto_array = Array();
                                $ap = explode(',', $v_d);
                                foreach ($ap as $kap=>$vap){
                                    $autop = explode('|', $vap);
                                    $aprod = explode('_', $autop[0]);
                                    $prod_auto = d()->Product($aprod[0]);
                                    if($prod_auto){
                                        $dop_auto = d()->Product->where('title =? AND city_id =? AND price = 0 OR title =? AND city_id =? AND price != 0', $prod_auto->title, $city, $prod_auto->title, $city)->to_array();
                                        if($dop_auto){
                                            foreach ($dop_auto as $k_dop_auto=>$v_dop_auto){
                                                if($prod_auto->price > 0 && $v_dop_auto['price'] > 0){
                                                    $auto_array[] = $v_dop_auto['id'].'_'.$aprod[1].'|'.$autop[1];
                                                }elseif ($prod_auto->price == 0 && $v_dop_auto['price'] == 0){
                                                    $auto_array[] = $v_dop_auto['id'].'_'.$aprod[1].'|'.$autop[1];
                                                }
                                            }
                                        }else{
                                            $dop_auto_2 = d()->Product->where('id_1c =? AND city_id =? AND price = 0 OR id_1c =? AND city_id =? AND price != 0', $prod_auto->id_1c, $city, $prod_auto->id_1c, $city)->to_array();
                                            if($dop_auto_2){
                                                foreach ($dop_auto_2 as $kdopauto2=>$vdopauto2){
                                                    if($prod_auto->price > 0 && $vdopauto2['price'] > 0){
                                                        $auto_array[] = $vdopauto2['id'].'_'.$aprod[1].'|'.$autop[1];
                                                    }elseif ($prod_auto->price == 0 && $vdopauto2['price'] == 0){
                                                        $auto_array[] = $vdopauto2['id'].'_'.$aprod[1].'|'.$autop[1];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $v_d = implode(',', $auto_array);
                            }
                            if($k_d=='dop_category'){
                                $dc = trim($v_d, '|');
                                $dc1 = explode('|', $dc);
                                $v_d = '|';
                                foreach ($dc1 as $k_dc1=>$v_dc1){
                                    $dop_cat = d()->Categorie->where('id =?', $v_dc1);
                                    $dop_cat_list = d()->Categorie->where('title = ? AND city_id = ?', $dop_cat->title, $city);
                                    if(!$dop_cat_list->is_empty){
                                        $v_d .= $dop_cat_list->id.'|';
                                    }
                                }
                            }
                            $product_element[$k_d] = $v_d;
                        }
                    }
                    $product = $product_element->save_and_load();
                }
                $l = d()->Log_product->new;
                $l->type = $type;
                $l->product_id = $product->id;
                $l->save;
            }
        }
    }

    $l = d()->Log_product->new;
    $l->type = $type;
    $l->product_id = $element_id;
    $l->save;
}

function do_properties(){

    $type = 'edit';
    $element_id = $_POST['data']['product_id'];
    if($_POST['_action'] == 'admin_delete_element'){
        $property = d()->Property($_POST['element_id']);
        $element_id = $property->product_id;
        // если удаление дефолтного свойства, то ищем новое и ставим его цену товару
        if($property->is_default){
            $ps = d()->Property->where('product_id=? AND id!=?', $element_id, $property->id);
            foreach($ps as $v){
                if($ps->is_default){
                    $p = d()->Product($element_id);
                    $p->price = $ps->price;
                    $p->save;
                    break;
                }
            }
        }
    }
    // если элемент по умолчанию, то меняем цену товара для грамотной сортировки
    if($_POST['data']['is_default']){
        $p = d()->Product($element_id);
        $p->price = $_POST['data']['price'];
        $p->save;
    }

    // делаем запись в логах
    $l = d()->Log_product->new;
    $l->type = $type;
    $l->product_id = $element_id;
    $l->save;
}

function do_style(){
    $f = $_SERVER['DOCUMENT_ROOT'].'/images/style.less';
    $file = fopen($f, 'r');
    $data = fread($file, filesize($f));
    fclose($file);

    $file = fopen($f, 'w');

    $t = explode('@color1:', trim($data));
    $t = explode(';', $t[1]);
    $data = str_replace('@color1:'.$t[0].';', '@color1: '.$_POST['data']['color1'].';',  $data);

    $t = explode('@color2:', trim($data));
    $t = explode(';', $t[1]);
    $data = str_replace('@color2:'.$t[0].';', '@color2: '.$_POST['data']['color2'].';',  $data);

    $t = explode('@color3:', trim($data));
    $t = explode(';', $t[1]);
    $data = str_replace('@color3:'.$t[0].';', '@color3: '.$_POST['data']['color3'].';',  $data);

    $t = explode('@color4:', trim($data));
    $t = explode(';', $t[1]);
    $data = str_replace('@color4:'.$t[0].';', '@color4: '.$_POST['data']['color4'].';',  $data);

    $t = explode('@color5:', trim($data));
    $t = explode(';', $t[1]);
    $data = str_replace('@color5:'.$t[0].';', '@color5: '.$_POST['data']['color5'].';',  $data);

    fwrite($file, $data);
    fclose($file);
    //printr($t1);
    //print $data;
}

function style_php(){
    get_city();

    $color1 = d()->city->color1;
    $color4 = d()->city->color4;
    $color2 = d()->city->color2;
    $color3 = d()->city->color3;
    $color5 = d()->city->color5;

    $dir = substr(dirname(__FILE__),0,-4);

    //header("Content-type: application/javascript; charset=utf-8");
    header("Content-type: text/css; charset=utf-8");

    $style_out = file_get_contents($dir.'/images/style.css');

    $style_out = str_replace('-color1', $color1, $style_out);
    $style_out = str_replace('-color4', $color4, $style_out);
    $style_out = str_replace('-color2', $color2, $style_out);
    $style_out = str_replace('-color3', $color3, $style_out);
    $style_out = str_replace('-color5', $color5, $style_out);

    echo $style_out;
}

function ajax_get_promo_gifts_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }
    if($_POST['gifts']){
        d()->glist = explode(',', $_POST['gifts']);
    }
    d()->gifts_list = d()->Product->where('city_id=?', d()->city->id);

    d()->id_title = 'promo1';
    if($_POST['type']=='dr'){
        d()->id_title = 'dr';
        d()->dr_type = '_dr';
    }

    print d()->ajax_admin_gifts_tpl();
}

function ajax_get_promo_products_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
//    get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_products_groups_tpl();
}

function ajax_get_sales_products_admin(){
    d()->city = d()->City($_POST['city_id']);
    //get_city();
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
//    get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_products_fs_tpl();
}

function ajax_get_dopedit_products_admin(){
    $cities = d()->City;
    d()->cities = Array();
    foreach($cities as $k=>$v){
        d()->cities[$cities->id] = $cities->title;
    }

    d()->products_list = d()->Product->where('id != ?', $_POST['noid'])->order_by('title ASC');
    print d()->ajax_admin_dopedit_products_tpl();
}

function ajax_get_autoadd_products_admin(){
    d()->city = d()->City($_POST['city_id']);
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        d()->cnt_array = Array();
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            d()->cnt_array[$a[0]] = $a[1];
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
    get_products_options();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_autoadd_products_tpl();
    //print d()->ajax_admin_products_fs_tpl();
}

function ajax_get_other_items_admin(){
    d()->city = d()->City($_POST['city_id']);
    //get_city();
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
    get_products_options();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    //print d()->ajax_admin_products_fs_tpl();
    print d()->ajax_admin_items_tpl();
}

function ajax_get_promo_required_products_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }

    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = str_replace('|', '', $v);
            $id = explode('_', $a);
            d()->products[$a][0] = $a;
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$id[0].'|';
        }
        $sortline .= 'sort ASC';
    }

    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);
//    get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=? AND is_active=1', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_required_products_tpl();
}

function ajax_get_gift_products_admin(){


    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }



    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = str_replace('|', '', $v);
            $id = explode('_', $a);
            d()->products[$a][0] = $a;
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$id[0].'|';
        }
        $sortline .= 'sort ASC';
        //$_SESSION['debug']['check'] = 1;
    }


    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
    //get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_gift_products_tpl();
}

function ajax_check_order(){
    $_SESSION['POST']=$_POST;
    if($_POST['info']){
        $r = Array();
        // пустая корзина
        if(!count($_SESSION['cart'])){
            $m = $_SESSION;
            foreach($_COOKIE as $ck=>$cv){
                if(strpos($ck, 'cart_log') === false)continue;
                $m['cookie'][$ck] = json_decode($cv, true);
            }

            $l = d()->Log->new();
            $l->title = 'cart_null';
            $l->text = json_encode($m);
            $l->save;

            $r['result'] = 'error';
            $r['t'] = 'cart_null';
            $r['error_text'] = 'Ошибка сервера, сессия потеряна. Пожалуйста соберите корзину еще раз.';
            return json_encode($r);
            exit;
        }

        get_city();
        get_cart();
        $u = d()->Auth->user();

        $info = json_decode($_POST['info'], true);
        $info['zone'] = $_SESSION['zone'];
        $info['points'] = $_SESSION['points'];
        // проверяем включен ли прием заказа
        if(d()->city->is_stop_order){
            $r['result'] = 'error';
            $r['error_text'] = 'По техническим причинам, мы не можем принять заказ. Приносим свои извинения.';
            return json_encode($r);
            exit;
        }

        // проверка на черный список
        if($info['phone']){
            $phone = d()->convert_phone($info['phone']);
            $bl = d()->Blacklist->where('phone = ?', $phone)->limit(0,1);
            if(!$bl->is_empty){
                $r['result'] = 'error';
                $r['text'] = 'К сожалению, по техническим причинам оформление заказа c помощью приложения невозможно. Для оформления заказа Вы можете позвонить в колл-центр.';
                return json_encode($r);
                exit;
            }
        }

        // проверяем способ доставки
        if($info['delivery_type']==1){
            // samovivoz
            // проверяем выбран ли филиал
            if(!$info['office_id']){
                $r['result'] = 'error';
                $r['error_text'] = 'Не выбран филиал. Пожалуйста выберите откуда забрать заказ';
                return json_encode($r);
                exit;
            }
            // проверяем есть ли ограничения на самовывоз в этом городе
            if(d()->city->wt_pickup){
                $wt_check = check_wt_pickup(1);
                if($wt_check){
                    $r['result'] = 'error';
                    $r['error_text'] = 'В связи с повышением мер безопасности по нераспространению коронавирусной инфекции, заказы на самовывоз принимаются до '.d()->city->wt_pickup.'. Самовывоз осуществляется до '.d()->wt_pickup_hour.'.';
                    return json_encode($r);
                    exit;
                }
            }

            // проверка на подарок за самовывоз
            if(d()->cart_discount_price >= d()->city->dd_gifts_ms){
                if(!count($_SESSION['promocode']) || $_SESSION['promocode']['is_not_delivery'] != 1){
                    $i_cart = 0;
                    foreach ($_SESSION['cart'] as $kc=>$vc){
                        if(strpos($kc, 'gift_pickup')){
                            $i_cart = 1;
                        }
                    }
                    if(!$i_cart){
                        $r['result'] = 'error';
                        $r['error_text'] = 'Вам доступен подарок за самовывоз, пожалуйста добавьте его в корзину. <a href="/checkout">Выбрать подарок</a>';
                        return json_encode($r);
                        exit;
                    }
                }
            }
        }else{
            // dostavka
            if($_SESSION['zone']['address_id']){
                // esli adres vibran iz spiska
                $adr = d()->Address($_SESSION['zone']['address_id'])->limit(0,1);

                // если не заполнены КВ, ПОД или ЭТ
                if(!$adr->is_private){
                    if(!$adr->floor || !$adr->entrance || !$adr->apartment){
                        $r['result'] = 'error';
                        $r['t'] = 'room';
                        $r['error_text'] = 'Необходимо заполнить в адресе квартиру, подъезд и этаж. Это можно сделать в Личном кабинете, в разделе "<a href="/cabinet" target="_blank">Адреса доставки</a>"';
                        return json_encode($r);
                        exit;
                    }
                }

                $info['zone']['address'] = $adr->street;
                $info['floor'] = $adr->floor;
                $info['entrance'] = $adr->entrance;
                $info['room_number'] = $adr->apartment;
                $info['is_private'] = $adr->is_private;

                // log
                $log_street = d()->Address_log->new;
                $log_street->text = $adr->street;
                $log_street->title = d()->convert_phone($info['phone']);
                $log_street->user_id = $_SESSION['auth'];
                $log_street->type = 1;
                $log_street->save;
            }

            // TODO: кастыл, пока не нашел проблему почему не выбирается улица (пример заказов без улицы: 1124786, 1117213)
            if(!$_SESSION['zone']['address_id'] && !$_SESSION['zone']['address']){
                $adr = d()->Address->where('user_id =?', $_SESSION['auth'])->limit(0,1);

                // если не заполнены КВ, ПОД или ЭТ
                if(!$adr->is_private){
                    if(!$adr->floor || !$adr->entrance || !$adr->apartment){
                        $r['result'] = 'error';
                        $r['t'] = 'room';
                        $r['error_text'] = 'Необходимо заполнить в адресе квартиру, подъезд и этаж. Это можно сделать в Личном кабинете, в разделе "<a href="/cabinet" target="_blank">Адреса доставки</a>"';
                        return json_encode($r);
                        exit;
                    }
                }

                $info['zone']['address'] = $adr->street;
                $info['floor'] = $adr->floor;
                $info['entrance'] = $adr->entrance;
                $info['room_number'] = $adr->apartment;
                $info['is_private'] = $adr->is_private;

                // log
                $log_street = d()->Address_log->new;
                $log_street->text = $adr->street;
                $log_street->title = d()->convert_phone($info['phone']);
                $log_street->user_id = $_SESSION['auth'];
                $log_street->type = 2;
                $log_street->save;
            }

            // proverka na min summu zoni
            if($info['zone']['min_order'] > d()->cart_discount_price){
                $r['result'] = 'error';
                $r['error_text'] = 'Минимальная сумма заказа, для Вашего адреса (без учета стоимости доставки): '.$info['zone']['min_order'].' руб.';
                return json_encode($r);
                exit;
            }

            // proverka, esli ne vibrano vremya progotovleniya
            if(!$info['cook_time']){
                $r['result'] = 'error';
                $r['t'] = 'ztime';
                $r['error_text'] = 'Необходимо указать дату и время приготовления';
                return json_encode($r);
                exit;
            }
            // если не заполнены КВ, ПОД, ЭТ
            if(!$info['is_private'] && !$_SESSION['zone']['address_id']){
                if(!isset($info['room_number']) || !isset($info['floor']) || !isset($info['entrance'])){
                    $r['result'] = 'error';
                    $r['t'] = 'room';
                    $r['error_text'] = 'Необходимо указать квартиру, подъезд и этаж';
                    return json_encode($r);
                    exit;
                }
            }
            // проверка, совпадает ли зона достаки промокода с выбраной зоной доставки
            if($_SESSION['promocode']['zones']){
                $zl = explode(',', $_SESSION['promocode']['zones']);
                d()->zones = Array();
                foreach ($zl as $k_zl=>$v_zl){
                    $zone = str_replace('|', '', $v_zl);
                    $title = d()->Zoni($zone);
                    d()->zones[$zone] = $title->title;
                }
                $i = 0;
                foreach (d()->zones as $key_z=>$value_z){
                    if($value_z == $_SESSION['zone']['title']){
                        $i = 1;
                    }
                }
                if($i == 0){
                    $r['result'] = 'error';
                    $r['error_text'] = 'К сожалению, промокод '.$_SESSION['promocode']['title'].' не доступен для заказа, на указанный адрес доставки. Пожалуйста укажите другой адрес доставки, или используйте другой промокод.';
                    return json_encode($r);
                    exit;
                }
            }
            // проверка на минимальное время доставки
            if($info['cook_time']!='now' && $info['cook_time']){
                $u_cooktime = strtotime($info['cook_time']);
                $tc = date('Y-m-d, H:i', $u_cooktime);
                $time_c = explode(',', $tc);

                $this_time = date("Y-m-d, H:i");
                $t_time = explode(',', $this_time);
                if($t_time[0] < $time_c[0]){
                    list($hours, $minutes) = explode(':', $time_c[1]);
                    $total_time_c = 60 * $hours + $minutes + 1440;
                    list($hours, $minutes) = explode(':', $t_time[1]);
                    $total_this_time = 60 * $hours + $minutes;
                    $t = $total_time_c - $total_this_time;
                }else{
                    list($hours, $minutes) = explode(':', $time_c[1]);
                    $total_time_c = 60 * $hours + $minutes;
                    list($hours, $minutes) = explode(':', $t_time[1]);
                    $total_this_time = 60 * $hours + $minutes;
                    $t = $total_time_c - $total_this_time;
                }
                if($t < $info['zone']['time'])
                {
                    $r['result'] = 'error';
                    $r['error_text'] = 'К сожалению мы не успеем доставить заказ, к указанному времени. Минимальное время доставки на ваш адрес: '.$info['zone']['time'].' мин.';
                    return json_encode($r);
                    exit();
                }
            }

            // проверка на время работы
            if(!check_wt()){
                $r['result'] = 'error';
                $r['error_text'] = 'К сожалению вы пытаетесь оформить заказ в нерабочее время, ожидаем ваши заказы в Часы работы.<br>Ознакомиться с режимом работы, вы можете в разделе <a href="/contacts">Контакты</a>.';
                return json_encode($r);
                exit;
            }
        }

        // проверка на минимальную сумму заказа без товаров не собственого производства
        if(!d()->check_new_promo_minsum()){
            $arr_title = Array();
            foreach ($_SESSION['cart'] as $k_cart=>$v_cart)
            {
                if($v_cart['not_dd']) $arr_title[] = $v_cart['title'];
            }
            if($_SESSION['promocode']['min_sum_notdd'] && $arr_title)
            {
                $r['result'] = 'error';
                $r['error_text'] = 'Минимальная сумма заказа для промокода '.$_SESSION['promocode']['title'].': <strong>'.$_SESSION['promocode']['min_sum'].' руб.</strong> '.implode(",", $arr_title).' не участвуют в формировании минимальной суммы, так как являются блюдом не собственного производства. Вам осталось заказать еще на <strong>'.d()->eshe.' руб.</strong>';
                return json_encode($r);
                exit();

            }else{
                $r['result'] = 'error';
                $r['error_text'] = 'Минимальная сумма заказа для промокода '.$_SESSION['promocode']['title'].': <strong>'.$_SESSION['promocode']['min_sum'].' руб.</strong> Вам осталось заказать еще на <strong>'.d()->eshe.' руб.</strong>';
                return json_encode($r);
                exit();
            }
        }

        // proverka oplati ballami
        if($info['points']){
            // сумма с учетом скидки по промокоду
            $promo_cart_sum = d()->cart_total_price - d()->discount_promocode;
            $points_used = d()->city->points_used/100;
            if($promo_cart_sum*$points_used < $info['points']){
                $r['result'] = 'error';
                $r['error_text'] = 'максимум '.d()->city->points_used.'% от суммы заказа';
                return json_encode($r);
                exit();
            }
            if($u->points < $info['points']){
                $r['result'] = 'error';
                $r['error_text'] = 'Недостаточно баллов';
                return json_encode($r);
                exit();
            }
        }

        // ошибка Не заполнено имя
        if(!$info['name']){
            $r['result'] = 'error';
            $r['t'] = 'fio';
            $r['error_text'] = 'Необходимо указать, как к Вам обращаться';
            return json_encode($r);
            exit();
        }

        // ошибка Не заполнено количество приборов
        if(!$info['persons'] && d()->total_tableware){
            $r['result'] = 'error';
            $r['t'] = 'person';
            $r['error_text'] = 'Необходимо указать Количество приборов';
            return json_encode($r);
            exit();
        }

        // ошибка если количество приборов больше допустимого
        if(d()->total_tableware && $info['persons']>d()->total_tableware){
            $r['result'] = 'error';
            $r['t'] = 'person';
            $r['error_text'] = 'Максимальное допустимое количество приборов для Вашего заказа: '.d()->total_tableware;
            return json_encode($r);
            exit();
        }

        // ошибка Не заполнен телефон
        if(!$info['phone']){
            $r['result'] = 'error';
            $r['t'] = 'phone';
            $r['error_text'] = 'Необходимо указать контактный телефон';
            return json_encode($r);
            exit();
        }

        // проверка на длину номера телефона
        if(strlen(d()->convert_phone($info['phone'])) < 11){
            $r['result'] = 'error';
            $r['t'] = 'phone';
            $r['error_text'] = 'Некорректно указан номер телефона';
            return json_encode($r);
            exit();
        }

        if($_SESSION['promocode']['id']) {
            $promo = d()->Promocode($_SESSION['promocode']['id'])->limit(0, 1);
            // если промокод одноразовый, общий - проверяем в истории использования
            if($promo->is_wide_single){
                $promo_log = d()->Promo_history->where('promocode_id = ? AND phone = ?', $promo->id, d()->convert_phone($info['phone']));
                if(!$promo_log->is_empty){
                    $r['result'] = 'error';
                    $r['error_text'] = 'Этот промокод уже использовался. Промокод можно использовать только один раз.';
                    return json_encode($r);
                    exit();
                }
            }
            // промокод только для приложения
            if($promo->only_app){
                $r['result'] = 'error';
                $r['error_text'] = 'Этот промокод только для мобильного приложения.';
                return json_encode($r);
                exit();
            }
        }

        // проверка промокода на категории
        $promo_category = trim($_SESSION['promocode']['sales_category'], '|');
        if($promo_category) {
            $sc1 = explode('|', $promo_category);
            $category_incart = '';
            foreach ($sc1 as $k_sc1=>$v_sc1){
                foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
                    if($v_cart['category_id'] == $v_sc1) $category_incart .= $v_cart['category_id'].',';
                }
            }
            if($category_incart){
                $t_cat = trim($category_incart, ',');
                $title_cat = explode(',', $t_cat);
                $str_title_cat = '';
                foreach ($title_cat as $vtcat){
                    $str1 = d()->Categorie($vtcat);
                    $str_title_cat .= $str1->title;
                }
                $r['result'] = 'error';
                $r['error_text'] = 'Промокод не применяется к товарам категории '.$str_title_cat;
                return json_encode($r);
                exit();
            }
        }

        // проверка промокода на определенный товар
        if($_SESSION['promocode']['sales_products']) {
            $sp = explode(',', $_SESSION['promocode']['sales_products']);
            $str_sales_products = 0;
            d()->products4 = Array();
            $i = 0;
            $search_products = '|'.implode('|', array_keys($_SESSION['cart']));
            foreach ($sp as $vsp){
                $spp = str_replace('|', '', $vsp);
                d()->products4[$spp] = $spp;
                $findme2 = '|'.$spp.'_';
                if(strpos($search_products, $findme2) !== false){
                    $str_sales_products = 1;
                    $i++;
                }
            }
            if($str_sales_products){
                $str_title_pr = '';
                foreach (d()->products4 as $vtpr){
                    $t_p = explode('_', $vtpr);
                    $str1 = d()->Product($t_p[0]);
                    $str_title_pr .= $str1->title;
                }
                $r['result'] = 'error';
                $r['error_text'] = 'Промокод не применяется к товару '.$str_title_pr;
                return json_encode($r);
                exit();
            }
        }

        // проверка на обязательные блюда промокода в корзине
        if($_SESSION['promocode']['required_products']){
            $incart = 0;
            $products_temp = explode(',', $_SESSION['promocode']['required_products']);
            $cnt = count($products_temp);
            d()->r_products = Array();
            $i = 0;
            $search_keys = '|'.implode('|', array_keys($_SESSION['cart']));
            foreach($products_temp as $k=>$v){
                $a = str_replace('|', '', $v);
                d()->r_products[$a] = $a;
                $findme = '|'.$a.'_';
                if(strpos($search_keys, $findme) !== false){
                    $incart = 1;
                    $i++;
                }
            }
            if(!$incart){
                $r['result'] = 'error';
                $r['error_text'] = 'в корзине отсутствуют обязательные блюда';
                return json_encode($r);
            }elseif ($_SESSION['promocode']['allorseveral'] == 1) {
                if($cnt != $i){
                    $r['result'] = 'error';
                    $r['error_text'] = 'в корзине добавлены не все обязательные блюда';
                    return json_encode($r);
                }
            }elseif ($_SESSION['promocode']['allorseveral'] == 2) {
                if($_SESSION['promocode']['several_quantity'] != $i){
                    $r['result'] = 'error';
                    $r['error_text'] = 'в корзине добавлены не все обязательные блюда';
                    return json_encode($r);
                }
            }
        }

        // проверка на временные рамки промокода (если есть)
        if($_SESSION['promocode']['id']){
            // проверка на временные рамки
            if($info['cook_time']!='now'){
                $check = check_promotime($promo->start_time, $promo->end_time, "15", $info['cook_time']);
            }else{
                $check = check_promotime($promo->start_time, $promo->end_time, "15");
            }
            if(!$check){
                $r['result'] = 'error';
                if($promo->start_time && !$promo->end_time){
                    $r['error_text'] = 'Промокод доступен только с '.$promo->start_time.':00';
                }elseif(!$promo->start_time && $promo->end_time){
                    $r['error_text'] = 'Промокод доступен только до '.$promo->end_time.':00';
                }else{
                    $r['error_text'] = 'Промокод доступен только с '.$promo->start_time.':00 до '.$promo->end_time.':00';
                }
                return json_encode($r);
            }
        }

        // проверка на период действия + заказ на следующий день
        if($_SESSION['promocode']['id'] && $promo->end_date){
            $check = check_promodate($promo->end_date, $info['cook_time']);
            if(!$check){
                $r['result'] = 'error';
                $ed = date('d.m.Y', $promo->end_date);
                $r['error_text'] = 'Промокод "'.$_SESSION['promocode']['title'].'" действителен только до '.$ed.' (включительно)';
                return json_encode($r);
            }
        }

        // проверка на дни недели промокода (если есть)
        if($_SESSION['promocode']['id'] && $promo->week_days){
            if($info['cook_time']!='now'){
                // если предзаказ на другое время
                $n = date('N', strtotime($info['cook_time']));
                if(strpos($promo->exceptions_dates, $n) !== false){
                    $r['error_text'] = 'Промокод <strong>'.d()->promo->name.'</strong> не доступен на указанную дату и время';
                    return json_encode($r);
                }
            }else{
                $n = date('N', date('U')+d()->city->timezone*3600);
                if(strpos($promo->week_days, $n) === false){
                    $r['error_text'] = 'Cегодня промокод <strong>'.d()->promo->name.'</strong> не доступен';
                    return json_encode($r);
                }
            }
        }

        // проверка на даты исключения
        if($_SESSION['promocode']['id'] && $promo->exceptions_dates){
            // если предзаказ на другое время
            if($info['cook_time']!='now'){
                $n = date('d.m', strtotime($info['cook_time']));
                if(strpos($promo->exceptions_dates, $n) !== false){
                    $r['error_text'] = 'Промокод <strong>'.d()->promo->name.'</strong> не доступен на указанную дату и время';
                    return json_encode($r);
                }
            }else{
                $n = date('d.m', date('U')+d()->city->timezone*3600);
                if(strpos($promo->exceptions_dates, $n) !== false){
                    $r['error_text'] = 'Cегодня промокод <strong>'.d()->promo->name.'</strong> не доступен';
                    return json_encode($r);
                }
            }
        }

        // промокод не сочетается с баллами
        if($_SESSION['promocode']['id'] && $promo->is_not_points && $_SESSION['points']) {
            $r['error_text'] = 'Промокод не сочетается с бонусными баллами';
            return json_encode($r);
        }

        // промокод не сочетается с подаками на ДР
        if($_SESSION['promocode']['id'] && $promo->is_not_dr || $_SESSION['promocode']['id'] &&  $promo->is_not_delivery) {
            foreach($_SESSION['cart'] as $vl){
                if($vl['property']=='gift_dr' && $promo->is_not_dr){
                    $r['error_text'] = 'Промокод не сочетается с подарками на День Рождения';
                    return json_encode($r);
                    break;
                }
                if($vl['property']=='gift_pickup' && $promo->is_not_delivery){
                    $r['error_text'] = 'Промокод не сочетается с подарками за Самовывоз';
                    return json_encode($r);
                    break;
                }
            }
        }

        // 2 промокода в корзине
        $two_promo = 0;
        $prm = 0;
        foreach($_SESSION['cart'] as $k=>$v){
            if($v['property']=='promo'){
                if($prm && $prm != $v['promocode']){
                    $two_promo = 1;
                    break;
                }else{
                    $prm = $v['promocode'];
                }
            }
        }
        if($two_promo) {
            $r['error_text'] = 'В заказе может быть использован только один промокод';
            return json_encode($r);
        }

        // определяем, повторный заказ
        $repeat_order = 0;
        $rsecs = 1800;
        $repeat_time = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600-$rsecs);
        //$_SESSION['dbg'] = $repeat_time;
        $ph = d()->convert_phone($info['phone']);
        if($info['delivery_type']==1){
            $repeat_check = d()->Order->where('phone = ? AND created_at >= ?', $ph, $repeat_time)->limit(0,1);
        }else{
            if(!$info['is_private']) {
                $repeat_check = d()->Order->where('phone = ? AND created_at >= ? OR street = ? AND room_number = ? AND created_at >= ?', $ph, $repeat_time, $info['zone']['address'], $info['room_number'], $repeat_time)->limit(0,1);
            }else{
                $repeat_check = d()->Order->where('phone = ? AND created_at >= ? OR street = ? AND is_private = ? AND created_at >= ?', $ph, $repeat_time, $info['zone']['address'], $info['is_private'], $repeat_time)->limit(0,1);
            }
        }
        if(!$repeat_check->is_empty){
            $repeat_order = 1;
        }

        // дата рождения по акции ДР
        $dr_date = '';
        if($_SESSION['dr_date'] && $_SESSION['promocode']['birthday'] == 1){
            $dr_date = $_SESSION['dr_date'];
            //$c = 0;
            /*foreach($_SESSION['cart'] as $k=>$v){
                if(strpos($k, 'gift_dr') !== false)$c = 1;
            }*/

            //if($c)$dr_date = $_SESSION['dr_date'];
        }

        // если есть подарок за самовывоз
        $gift_pickup_is_present = 0;
        foreach($_SESSION['cart'] as $k=>$v){
            if(strpos($k, 'gift_pickup') !== false)$gift_pickup_is_present = 1;
        }
        if($gift_pickup_is_present){
            // проверяем способ доставки
            if($info['delivery_type']!=1){
                $r['error_text'] = 'Подарок доступен только на самовывоз. Пожалуйста удалите подарок за самовывоз из корзины.';
                return json_encode($r);
                exit();
            }
            // проверяем на минимальную сумму
            $chs = d()->order_price - d()->delivery_price;
            if($chs < d()->city->dd_gifts_ms){
                $r['error_text'] = 'Минимальная сумма заказа (без учета стоимости доставки) по акции «Подарки за самовывоз» '.d()->city->dd_gifts_ms.' руб. Пожалуйста удалите подарок за самовывоз или выполните условие этой акции.';
                return json_encode($r);
                exit();
            }
        }

        // проверяем заказы ко времени в базе сайта
        if($info['cook_time']!='now' && $info['cook_time']){
            $t_cook = date('Y-m-d H:i:s', strtotime($info['cook_time']));
            d()->t_cook_list = d()->Order->where('cook_time LIKE ? AND city_id LIKE ? OR cook_time=? AND city_id=?', $t_cook, d()->city->id, $t_cook, d()->city->id)->count;
            // проверяем заказы ко времени в базе 1С
            $t_cook_1c = date('Y.m.d H', strtotime($info['cook_time']));
            $t_cook_1c_2 = date('Y.m.d G', strtotime($info['cook_time']));
            d()->t_cook_1c_list = d()->Intime_order->where('city_id=? AND cook_time LIKE ? OR city_id=? AND cook_time LIKE ?', d()->city->id, '%'.$t_cook_1c.'%', d()->city->id, '%'.$t_cook_1c_2.'%')->count;

            $total_orders = d()->t_cook_list+d()->t_cook_1c_list;
            if($total_orders >= d()->city->max_intime_orders && d()->city->max_intime_orders){
                $r['error_text'] = 'На '.$info['cook_time'].' оформлено много заказов. Пожалуйста укажите другую дату или время.';
                return json_encode($r);
            }
        }

        // проверка в заказе на удаленные товары
        foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
            $pc = d()->Product($v_cart['id']);
            if($pc->is_empty){
                $_SESSION['EMPTY'] = "EMPTY";
                $r['error_text'] = 'К сожалению блюдо '.$v_cart['title'].' в данный момент отсутствует в нашем меню. Для продолжения оформления заказа, пожалуйста удалите его из корзины.';
                return json_encode($r);
            }
            if(!$pc->is_active){
                $_SESSION['SALES'] = '';
                $_SESSION['PROMOCODE'] = '';
                $_SESSION['DOPS'] = '';
                $_SESSION['GIFTS DELIVERY'] = '';
                $_SESSION['GIFTS BIRTHDAY'] = '';
                $_SESSION['AUTOGOODS'] = '';
                $_SESSION['CHECK NULL'] = '';
                $_SESSION['PROMOCODE GIFT'] = '';
                $check_p = 0;
                $id_pc = $pc->id.'_'.(int)$v_cart['property'];
                // проверка в акциях
                $pcs = d()->Sale->where('products LIKE ? AND is_active=1 AND city_id=?', '%'.$id_pc.'%', d()->city->id);
                if(!$pcs->is_empty){
                    foreach ($pcs as $k_pcs=>$v_pcs){
                        $items_p = explode(',', $v_pcs->products);
                        foreach ($items_p as $k_ip=>$v_ip){
                            if($v_ip == $id_pc){
                                $check_p = 1;
                                $_SESSION['SALES'] = "SALES";
                            }
                        }
                    }
                }

                if(!$check_p){
                    // проверка в промокодах
                    $pcp = d()->Promocode->where('products LIKE ? AND is_active=1 AND city_id=?', '%'.$id_pc.'%', d()->city->id);
                    if(!$pcp->is_empty){
                        foreach ($pcp as $k_pcp=>$v_pcp){
                            $items_pcp = explode(',', $v_pcp->products);
                            foreach ($items_pcp as $k_ipcp=>$v_ipcp){
                                $i_pcp = explode('|', $v_ipcp);
                                if($i_pcp[0] == $id_pc) {
                                    $check_p = 1;
                                    $_SESSION['PROMOCODE'] = "PROMOCODE";
                                }
                            }
                        }
                    }else{
                        $pcpg = d()->Promocode->where('gift LIKE ? AND is_active=1 AND city_id=?', '%'.$id_pc.'%', d()->city->id);
                        if(!$pcpg->is_empty){
                            foreach ($pcpg as $k_pcpg=>$v_pcpg){
                                $items_pcpg = explode(',', $v_pcpg->gift);
                                foreach ($items_pcpg as $k_ipcpg=>$v_ipcpg){
                                    if($v_ipcpg == '|'.$id_pc.'|') {
                                        $check_p = 1;
                                        $_SESSION['PROMOCODE GIFT'] = "PROMOCODE GIFT";
                                    }
                                }
                            }
                        }
                    }
                }

                if(!$check_p){
                    // проверка в допах
                    $pcd = d()->Other->where('product_id LIKE ? AND city_id=? AND is_active=1', $pc->id, d()->city->id);
                    if(!$pcd->is_empty) {
                        $check_p = 1;
                        $_SESSION['DOPS'] = "DOPS";
                    }
                }

                if(!$check_p){
                    // проверка в подарках за самовывоз
                    $dd_gifts = explode(',', d()->city->dd_gifts);
                    foreach ($dd_gifts as $k_ddg=>$v_ddg) {
                        if ($v_ddg == '|' . $id_pc . '|') {
                            $check_p = 1;
                            $_SESSION['GIFTS DELIVERY'] = "GIFTS DELIVERY";
                        }
                    }
                    //$pcgdl = d()->City->where('id LIKE ? AND dd_gifts LIKE ?', d()->city->id, '%|'.$id_pc.'|%');
                    //if(!$pcgdl->is_empty) $check_p = 1;
                }

                if(!$check_p){
                    // проверка в подарках на ДР
                    $gift_dr = explode(',', d()->city->gift_dr);
                    foreach ($gift_dr as $k_drg=>$v_drg){
                        if($v_drg == '|' . $id_pc . '|') {
                            $check_p = 1;
                            $_SESSION['GIFTS BIRTHDAY'] = "GIFTS BIRTHDAY";
                        }
                    }
                    //$pcgdl = d()->City->where('id LIKE ? AND gift_dr LIKE ?', d()->city->id, '%|'.$id_pc.'|%');
                    //if(!$pcgdl->is_empty) $check_p = 1;
                }

                if(!$check_p){
                    // проверка в автотоварах
                    $pcauto_add = d()->Product->where('city_id=? AND autoadd_products LIKE ?', d()->city->id, '%'.$id_pc.'%');
                    if(!$pcauto_add->is_empty){
                        foreach ($pcauto_add as $k_pcauto=>$v_pcauto){
                            $items_pcauto = explode(',', $v_pcauto->autoadd_products);
                            foreach ($items_pcauto as $k_ipcauto=>$v_ipcauto){
                                $ii_auto = explode('|', $v_ipcauto);
                                if($ii_auto[0] == $id_pc) {
                                    $check_p = 1;
                                    $_SESSION['AUTOGOODS'] = "AUTOGOODS";
                                }
                            }
                        }
                    }
                }

                if(!$check_p){
                    $_SESSION['CHECK NULL'] = "CHECK NULL";
                    $r['error_text'] = 'К сожалению блюдо '.$v_cart['title'].' в данный момент отсутствует в нашем меню. Для продолжения оформления заказа, пожалуйста удалите его из корзины.';
                    return json_encode($r);
                }
            }
        }

        // проверка, нужно ли подтверждать заказ
        $ph = d()->convert_phone($info['phone']);
        $c = d()->city->confirmation;
        //if($c && !$_SESSION['order_conf'][$ph]['result'] && d()->Auth->is_guest() && d()->order_price < 2000){
        if($c && !$_SESSION['order_conf'][$ph]['result'] && d()->Auth->is_guest()){
            // проверям последнее время отправки кода и лимиты
            $txt = '';
            if(d()->city->send_code_time){
                if($_SESSION['order_conf']['time']){
                    $check_time = date('U') - $_SESSION['order_conf']['time'];
                    if($check_time < d()->city->send_code_time){
                        $ct = d()->city->send_code_time - $check_time;
                        $sec  = declOfNum ($ct, array('секунду', 'секунды', 'секунд'));
                        $txt = 'СМС с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';
                        if(d()->city->send_code_type != 1)$txt = 'Авто-звонок с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';
                    }
                }
            }

            // отправляем звонок/смс
            if(!$txt){
                $code = d()->send_code($ph, $_GET['first']);
                $_SESSION['order_conf'][$ph]['code'] = $code;
                if(!$_GET['first'])$_SESSION['order_conf']['time'] = date('U');
            }

            $r['result'] = 'confirmation';
            $r['text'] = $txt;
            return json_encode($r);
        }

        $utm = $_COOKIE['utm'];
        if(!$utm){
            $utm = $u->utm;
        }

        // определяем статус заказа
        $status = 0; // новый
        $ac = 0; // подтверждение оператором
        //if(d()->city->confirmation && d()->order_price < 2000){
        if(d()->city->confirmation){
            //$status = 9; // принят
            $ac = 1; // авто подтверждение
        }

        // создаем строчку о сроках доставки
        $_SESSION['is_fast_delivery'] = 0;
        if($_SESSION['delivery'] == 2){
            if($_SESSION['geomob']['time3']){
                $_SESSION['running_order_time'] = $_SESSION['geomob']['time3'];
                $_SESSION['is_fast_delivery'] = 1;
            }elseif($_SESSION['geomob']['time2']){
                $_SESSION['running_order_time'] = $_SESSION['geomob']['time2'];
                $_SESSION['is_fast_delivery'] = 2;
            }else{
                $_SESSION['running_order_time'] = $_SESSION['geomob']['time'];
            }
        }else{
            $_SESSION['running_order_time'] = d()->city->pickup_time;
        }

        // сохраняем в базу время приготовления заказа
        if($info['cook_time'] == 'now'){
            $tm1 = date('U') + d()->city->timezone*3600 + 60*$_SESSION['running_order_time'];
            $tm2 = $tm1 + 10*60;
            d()->tm = date('H:i', $tm1).' - '.date('H:i', $tm2);
        }else{
            $tm = strtotime($info['cook_time']);
            d()->tm = date('d.m.Y, H:i', $tm);
        }

        // определяем id филиала
        if($info['delivery_type']==1){
            $office = $info['office_id'];
        }else{
            $zntemp = explode('.', $info['zone']['f_title']);
            $srt = 'asc';
            if(
                d()->check_for_number($zntemp[0]) && d()->city->id == 3 ||
                d()->check_for_number($zntemp[0]) && d()->city->id == 6
            ){
                $srt = 'desc';
            }
            $office = d()->Office->where('city_id = ?', d()->city->id)->order_by('id '.$srt)->limit(0,1)->id;
        }

        // определяем по номеру телефона есть ли такой пользователь в базе
        $uid = '';
        if($_POST['user']){
            $uid = $u->id;
        }else{
            $checkuser = d()->User->where('phone = ? AND city = ?', $phone, d()->city->code)->limit(0,1);
            if(!$checkuser->is_empty){
                $uid = $checkuser->id;
            }
        }

        // sozdaem zakaz v baze
        $o = d()->Order->new;
        $o->city_id = d()->city->id;
        if($uid)$o->user_id = $uid;
        $o->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
        $o->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
        $o->phone = d()->convert_phone($info['phone']);
        $o->name = $info['name'];
        $o->cart = json_encode($_SESSION['cart']);
        $o->delivery = $info['delivery_type'];
        $o->office_id = $office;
        if($info['delivery_type']==2){
            $house_marker = '';
            if(!$info['is_private']){
                $o->room_number = $info['room_number'];
                $o->floor = $info['floor'];
                $o->entrance = $info['entrance'];
                $o->is_not_intercom = $info['is_not_intercom'];
                if($info['house_marker'] && $info['house_marker'] != $info['zone']['address']){
                    $house_marker = ' (корпус '.$info['house_marker'].')';
                }
            }else{
                $o->is_private = $info['is_private'];
            }

            $o->street = $info['zone']['address'].$house_marker;

            $o->delivery_price = d()->delivery_price;
            $o->delivery_zone = $info['zone']['f_title'];
        }
        $o->points = $info['points'];
        $o->discount_promocode = d()->discount_promocode;
        $o->finish_price = d()->order_price;
        $o->pay = $info['pay_type'];
        $o->banknote = $info['banknote'];
        $o->comment = $info['comment'];
        if($info['persons']){
            $o->persons = $info['persons'];
        }
        if($info['cook_time']!='now'){
            $o->cook_time = date('Y-m-d H:i:s', strtotime($info['cook_time']));
        }else{
            $o->cook_time = $info['cook_time'];
        }
        if($_SESSION['promocode']['id']){
            $o->promocode_id = $_SESSION['promocode']['id'];
        }
        $o->utm = $utm;
        $o->status = $status;
        $o->repeat_order = $repeat_order;
        $o->dr_date = $dr_date;
        $o->running_order_time = d()->tm;
        if($_SESSION['is_fast_delivery'])$o->is_fast_delivery = $_SESSION['is_fast_delivery'];
        $order = $o->save_and_load();

        // если был промокод, ставим +1 к использован
        if($_SESSION['promocode']['id']){
            $promo = d()->Promocode($_SESSION['promocode']['id'])->limit(0,1);
            $promo->used = $promo->used + 1;
            $promo->save();
            // если он одноразовый общий записываем в куки и пишем в историю
            if($_SESSION['promocode']['is_wide_single']){
                setcookie("wide_single_promo", '|'.$_SESSION['promocode']['id'].'|', time()+3600*24*30*12, '/');

                $promo_log = d()->Promo_history->new;
                $promo_log->promocode_id = $promo->id;
                $promo_log->phone = $order->phone;
                $promo_log->save;
            }
            // если для спящих, удаляем его из базы
            if($promo->is_sleep){
                $sleep = d()->Sleep_phone->where('phone = ?', $order->phone);
                $sleep->delete();
            }
        }

        // сохраняем адрес, если нужно
        if($info['address_title'] && $_SESSION['zone']['address']){
            $adr = d()->Address->new;
            $adr->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
            $adr->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
            $adr->title = $info['address_title'];
            $adr->street = $_SESSION['zone']['address'];
            $adr->city = d()->city->code;
            $adr->lat = $info['zone']['lat'];
            $adr->lon = $info['zone']['lon'];
            if(!$info['is_private']) {
                $adr->floor = $info['floor'];
                $adr->entrance = $info['entrance'];
                $adr->apartment = $info['room_number'];
            }else{
                $adr->is_private = 1;
            }
            $adr->user_id = $u->id;
            $adr->save;
        }

        // списываем баллы если были использованы
        if($info['points']){
            // логируем списание баллов (для пользователей-накрутивальщиков)
            if($info['points'] >= 350){
                $lp = d()->Log_point->new;
                $lp->user_id = $u->id;
                $lp->user_phone = d()->convert_phone($info['phone']);
                $lp->points = $info['points'];
                $lp->old_points = $u->points;
                $lp->order_id = $order->id;
                $lp->save;
                // отправка уведомления
                $l_subject = 'ВНИМНИЕ! Списание большое количество баллов: '.$_SERVER['SERVER_NAME'];
                $l_text = '<p><strong>Город:</strong> '.d()->city->title.'<br><strong>Номер чека:</strong> '.$order->id.'<br><strong>Пользователь:</strong> '.$info['name']. ' ('.$u->id.')<br><strong>Номер телефона:</strong> '.$info['phone'].'<br><strong>Списаные баллы:</strong> '.$info['points'].'</p>';
                d()->Mail->to('slp410@bk.ru');
                d()->Mail->set_smtp(d()->city->smtp_server,d()->city->smtp_port,d()->city->smtp_mail,d()->city->smtp_password,d()->city->smtp_protocol);
                d()->Mail->from(d()->city->smtp_mfrom,d()->city->smtp_tfrom);
                d()->Mail->subject($l_subject);
                d()->Mail->message($l_text);
                d()->Mail->send();
            }

            d()->cashback_points = $info['points'];
            $u->points = $u->points - d()->cashback_points;
            $u->save;
            // создаем историю
            $p = d()->Point->new;
            $p->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
            $p->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
            $p->user_id = $u->id;
            $p->city = d()->city->code;
            $p->value = d()->cashback_points;
            $p->title = 'Списание баллов за заказ №'.$order->id;
            $p->type = 3;
            $p->save;
        }

        // начисляем баллы если нужно
        if(!d()->Auth->is_guest){
            // вычисляем, как давно клиент делал последний заказ
            $cb = d()->city->points_cashback;
            $check = d()->Order->where('user_id = ? AND id != ? OR phone = ? AND id != ?', $u->id, $order->id, d()->convert_phone($info['phone']), $order->id);
            if(!$check->is_empty){
                $cdgw = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $check = $check->where('created_at >= ?', $cdgw);
                if($check->is_empty){
                    $cb = d()->city->points_cashback2;
                }
            }
            d()->cashback_points = d()->get_cashback($cb);
            if(d()->cashback_points > 0){
                // создаем заявку на начисление кешбека
                $cttime_temp = date('U')+d()->city->timezone* 3600;
                $cttime = date('G', $cttime_temp);
                if($info['cook_time']=='now'){
                    $ctdate = date('d.m.Y', strtotime('+1 day'));
                    if($cttime < 5)$ctdate = date('d.m.Y', $cttime_temp);
                }else{
                    $ctdate = date('d.m.Y', strtotime($info['cook_time'])+86400);
                }

                $ct = d()->Cashback_task->new;
                $ct->value = d()->cashback_points;
                $ct->percent = $cb;
                $ct->status = 0;
                $ct->order_id = $order->id;
                $ct->user_id = $u->id;
                $ct->date = $ctdate;
                $ct->save;

                /*$u->points = $u->points + d()->cashback_points;
                $u->save;
                // создаем историю
                $p = d()->Point->new;
                $p->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
                $p->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
                $p->user_id = $u->id;
                $p->city = d()->city->code;
                $p->value = d()->cashback_points;
                $p->title = 'Кешбэк за заказ №'.$order->id;
                $p->type = 4;
                $p->save;*/
            }
        }

        // создаем строчку для выгрузки в 1С
        $exp = d()->Export_order->new;
        $exp->city_id = d()->city->id;
        $exp->order_id = $order->id;
        $exp->save;

        // создаем строчку для выгрузки в яндекс метрику
        $exp_analytics = d()->Export_analytic->new;
        $exp_analytics->city_id = d()->city->id;
        $exp_analytics->order_id = $order->id;
        $exp_analytics->clientid = $info['clientID'];
        $exp_analytics->revenue = d()->order_price;
        $exp_analytics->user_id = $u->id;
        $exp_analytics->user_phone = d()->convert_phone($info['phone']);
        $exp_analytics->user_name = $info['name'];
        $exp_analytics->user_created_at = $u->created_at;
        $exp_analytics->user_update_at = $u->updated_at;
        $exp_analytics->order_status = $status;
        $exp_analytics->save;

        // логируем адресс
        /*if($_SESSION['delivery'] == 2){
            $al = d()->Address_log->new;
            $al->title = d()->convert_phone($info['phone']);
            $al->text = json_encode($_SESSION['address_logs']);
            if($u->id) $al->user_id = $u->id;
            $al->order_id = $order->id;
            $al->save;
            unset($_SESSION['address_logs']);
        }*/

        // чистим сессию
        foreach($_SESSION as $k=>$v){
            if(
                $k=='admin' ||
                $k=='auth' ||
                $k=='delivery' ||
                $k=='order_conf' ||
                $k=='dbg' ||
                $k=='reg_phone' ||
                $k=='confirmation' ||
                $k=='SALES' ||
                $k=='PROMOCODE' ||
                $k=='DOPS' ||
                $k=='GIFTS DELIVERY' ||
                $k=='GIFTS BIRTHDAY' ||
                $k=='AUTOGOODS' ||
                $k=='CHECK NULL' ||
                $k=='running_order_time' ||
                $k=='PROMOCODE GIFT'
            )continue;
            unset($_SESSION[$k]);
        }

        $r['order_id'] = $order->id;
        $r['ac'] = $ac;
        $r['result'] = 'success';

        // мнеяем последний заказ в файле для CRM
        $arr = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/orderinfo.txt', true),true);
        $arr[d()->city->id] = $order->id;
        $file = fopen($_SERVER['DOCUMENT_ROOT'].'/orderinfo.txt', 'w');
        fwrite($file, json_encode($arr));
        fclose($file);
        // мнеяем последний заказ в файле для CRM

        // сохраняем пользователю его последний заказ
        setcookie("last_order", $order->id, time()+60*60*24*365*10, '/');
        setcookie("last_zone_title", $info['zone']['title'], time()+60*60*24*365*10, '/');

        // создаем файл со статусом заказа для CRM
        //$file2 = fopen('/web/sites/crm.appetitfood.ru/www/orderinfo/'.$order->id.'.txt', 'w');
        //fwrite($file2, 0);
        //fclose($file2);
        // создаем файл со статусом заказа для CRM

        return json_encode($r);
        exit;
    }
    d()->page_not_found();
}

function mtest(){
    unset($_SESSION['cart']['954_69_0']);
}

function ajax_check_uniq_promo(){
    if($_POST['promo']){
        if($_POST['city_id']){
            d()->city = d()->City($_POST['city_id']);
        }else{
            get_city();
        }

        $r = Array();
        $p = d()->Promocode->where('name=? AND city_id=?', $_POST['promo'], d()->city->id)->limit(0,1);
        if($_POST['id'] && $_POST['id']==$p->id){
            $r['result'] = 'success';
            return json_encode($r);
        }
        if($p->id){
            $r['text'] = '<span style="color: orangered;">промокод найден в базе</span>';
            $r['result'] = 'error';
            return json_encode($r);
        }
        $r['text'] = '<span style="color: green;">промокод уникален</span>';
        $r['result'] = 'success';
        return json_encode($r);
    }
    d()->page_not_found();
}

function ajax_check_uniq_doppromo(){
    if($_POST['promo']){
        if($_POST['city_id']){
            d()->city = d()->City($_POST['city_id']);
        }else{
            get_city();
        }

        $r = Array();
        $p = d()->Promocode->where('name=? AND city_id=?', $_POST['promo'], d()->city->id)->limit(0,1);
        if($_POST['id'] && $_POST['id']==$p->id){
            $r['result'] = 'success';
            return json_encode($r);
        }
        if($p->id){
            $r['text'] = '<span style="color: orangered;">промокод найден в базе</span>';
            $r['result'] = 'error';
            return json_encode($r);
        }
        $r['text'] = '<span style="color: green;">промокод уникален</span>';
        $r['result'] = 'success';
        return json_encode($r);
    }
    d()->page_not_found();
}

function ajax_check_time_order(){
    if($_POST){
        get_city();
        $r = 'ok';
        $ttime = $_POST['time'];
        /*$tc = explode(',', $ttime);
        $hm = explode(':', $tc[1]);
        $t1 = $tc[0].',';
        $t2 = $tc[0].',';
        if($hm[1] >= 00 && $hm[1] < 30){
            $t1 .= $hm[0].':00';
            $t2 .= $hm[0].':25';
        }elseif($hm[1] >= 30){
            $t1 .= $hm[0].':30';
            $t2 .= $hm[0].':55';
        }*/

        // количество в базе сайта
        $t_cook1 = date('Y-m-d H:i:s', strtotime($ttime));
        //$t_cook1 = date('Y-m-d H:i:s', strtotime($t1));
        //$t_cook2 = date('Y-m-d H:i:s', strtotime($t2));
        //d()->t_cook_list = d()->Order->where('city_id LIKE ? AND  cook_time BETWEEN ? AND ? OR city_id=? AND cook_time > ? < ? ', d()->city->id, $t_cook1, $t_cook2, d()->city->id, $t_cook1, $t_cook2)->count;
        //d()->t_cook_list = d()->Order->where('city_id LIKE ? AND  cook_time BETWEEN ? AND ? OR city_id=? AND cook_time >= ? AND cook_time <= ? ', d()->city->id, $t_cook1, $t_cook2, d()->city->id, $t_cook1, $t_cook2)->count;
        d()->t_cook_list = d()->Order->where('city_id LIKE ? AND  cook_time LIKE ? OR city_id=? AND cook_time = ? ', d()->city->id, $t_cook1, d()->city->id, $t_cook1)->count;

        // количество в базе 1С
        $t_cook_1c_1 = date('Y.m.d H:i:s', strtotime($ttime));
        //$t_cook_1c_1 = date('Y.m.d H:i:s', strtotime($t1));
        //$t_cook_1c_2 = date('Y.m.d H:i:s', strtotime($t2));
        //d()->t_cook_1c_list = d()->Intime_order->where('city_id=? AND cook_time BETWEEN ? AND ?', d()->city->id, '%'.$t_cook_1c_1.'%', '%'.$t_cook_1c_2.'%')->count;
        //d()->t_cook_1c_list = d()->Intime_order->where('city_id=? AND cook_time BETWEEN ? AND ?', d()->city->id, $t_cook_1c_1, $t_cook_1c_2)->count;
        d()->t_cook_1c_list = d()->Intime_order->where('city_id=? AND cook_time LIKE ?', d()->city->id, $t_cook_1c_1)->count;

        $total_orders = d()->t_cook_list+d()->t_cook_1c_list;
        if($total_orders >= d()->city->max_intime_orders && d()->city->max_intime_orders){
            $r = 'error';
        }
        return $r;
    }
    d()->page_not_found();
}

// проверка на неглавного админа
function not_boss(){
    if($_SESSION['admin'] != 'admin' && $_SESSION['admin'] != 'developer'){
        return true;
    }
    return false;
}

// проверка на доступ к редактированию таблицы
function admin_access_edit(){
    if(not_boss()){
        $str = url(3).'_edit';
        $show_btns = 0;
        if(in_array($str, $_SESSION['d_whitelist']))$show_btns = 1;
        if(!$show_btns)return false;
    }
    // доступ разрешен
    return true;
}

// проверка на отображение пунктов меню
function admin_access_topmenu($table='', $title=''){
    if(not_boss()){
        $show_btns = 0;
        if($title=='CRM'){
            $dwl = implode(',', $_SESSION['d_whitelist']);
            if(strpos($dwl, 'crm_') !== false)$show_btns = 1;
        }else{
            if(in_array($table, $_SESSION['whitelist']))$show_btns = 1;
        }
        if(!$show_btns)return false;
    }
    // доступ разрешен
    return true;
}


function do_points(){
    //$_SESSION['debug'] = $_POST;
    $u = d()->User($_POST['data']['user_id']);
    if(!$u->is_empty){
        if($_POST['data']['type']==3 || $_POST['data']['type']==5){
            $u->points = $u->points - $_POST['data']['value'];
        }else{
            $u->points = $u->points + $_POST['data']['value'];
        }
        $u->save;
    }

    get_city();

    $n = d()->Point->new;
    $n->city_id = $_POST['data']['city_id'];
    $n->user_id = $_POST['data']['user_id'];
    $n->created_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
    $n->updated_at = date('Y-m-d H:i:s', date('U')+d()->city->timezone*3600);
    $n->title = $_POST['data']['title'];
    $n->value = $_POST['data']['value'];
    $n->type = $_POST['data']['type'];
    $n->save;

    return  "<script>window.open('','_self','');window.close();</script>";
}

function do_htaccess(){
    $value = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/.htaccess', true);
    $file = fopen($_SERVER['DOCUMENT_ROOT'].'/.htaccess', 'w+');

    $arr = explode('#!for_seo', $value);
    $two_arr = explode('#for_seo!', $arr[1]);
    $result = $arr[0];
    $result .= '#!for_seo';
    $result .= '
';
    $result .= $_POST['data']['text'];
    $result .= '
';
    $result .= '#for_seo!';
    $result .= $two_arr[1];

    fwrite($file, $result);
    fclose($file);
    return  "<script>window.open('','_self','');window.close();</script>";
}

function orderinfo_crm(){
    header('Content-type: text/html');
    header('Access-Control-Allow-Origin: *');
    $file = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/orderinfo.txt', true);
    print $file;
}

// Карта сайта
function show_sitemap()

{
    header ('Content-Type:application/xml');
    print d()->Mysitemap->to_xml;
    exit();
}

// Robots.txt
function show_robots()
{
    header('Content-type: text/plain');
    $r = d()->Robot;
    $robots = str_replace('{domain}',$_SERVER['HTTP_HOST'], $r->text);
    print $robots;
}

// YML турбо страницы
function show_yml()
{
    header ('Content-Type:application/xml');
    get_city();
    d()->categories_list = d()->Category->where('city_id = ? AND is_active = 1', d()->city->id);
    d()->check_list = d()->categories_list->fast_all_of('id');

    d()->delivery_cost = d()->Zoni->where('city_id = ?', d()->city->id)->order_by('price ASC')->limit(0,1)->price;

    get_products_options();
    d()->products_list = d()->Product->where('city_id = ? AND is_active = 1', d()->city->id);

    print d()->yml_tpl();
    exit();
}

// YML турбо страницы
function yml($x='')
{
    $x = str_replace('"', '&quot;', $x);
    $x = str_replace('&', '&amp;', $x);
    $x = str_replace('>', '&gt;', $x);
    $x = str_replace('<', '&lt;', $x);
    $x = str_replace("'", "&apos;", $x);
    return $x;
}

function nq($x='')
{
    $x = str_replace('"', '', $x);
    $x = str_replace("'", "", $x);
    return $x;
}

function send_code($phone='', $first=0){
    $code = rand(1000, 9999);
    if(!$first){
        if(d()->city->send_code_type != 1){
            // отправляем код подтверждения звонком
            // если выбран Newtel
            if(!d()->city->send_code_type){
                $time = time();
                $murl = 'call/start-password-call';
                $k1 = d()->city->nt_key1;
                $k2 = d()->city->nt_key2;
                $client_phone = $phone;
                $nt_code = '1'.$code;
                $data_string = json_encode(Array("async"=>1,"dstNumber"=>$client_phone,"pin"=>$nt_code));
                $key = $k1.$time.hash( 'sha256' , $murl."\n".$time."\n".$k1."\n".$data_string."\n".$k2);
                $curl = curl_init('https://api.new-tel.net/'.$murl);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer '.$key,
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                );
                $mresult = curl_exec($curl);
                curl_close($curl);
            }
            // если выбран smsc.ru
            if(d()->city->send_code_type == 2){
                $check = d()->Code->where('phone = ? AND is_tg != 1', $phone)->order_by('id desc')->limit(0,1);
                $c = date('U') - strtotime($check->created_at);
                if(!$check->is_empty && $c <= 600) {
                    // отправляем код подтверждения по смс
                    $text = $code.' код подтверждения на сайте '.d()->site_url;
                    $mresult = file_get_contents('https://smsc.ru/sys/send.php?login='.d()->city->smsc_login.'&psw='.d()->city->smsc_password.'&phones='.$phone.'&mes='.$text);
                    $_SESSION['smsc']['result'] = $mresult;
                    $_SESSION['smsc']['code'] = $code;
                    $_SESSION['smsc']['sms'] = 1;
                    //$rtext = 'на указанный номер отправлено смс с кодом подтверждения';
                }else{
                    $mresult = file_get_contents('https://smsc.ru/sys/send.php?login='.d()->city->smsc_login.'&psw='.d()->city->smsc_password.'&phones='.$phone.'&mes=code&call=1');
                    $temp = explode(',', $mresult);
                    $tmp = explode('-', $temp[2]);
                    $tm = trim($tmp[1]);
                    $code = substr($tm, 2);
                    if(!$code)$code = rand(1000,9999);
                    $_SESSION['smsc']['result'] = $mresult;
                    $_SESSION['smsc']['code'] = $code;
                }
            }
        }else{
            // отправляем код подтверждения по смс
            $text = $code.' код подтверждения на сайте '.d()->site_url;
            $mresult = file_get_contents('https://smsc.ru/sys/send.php?login='.d()->city->smsc_login.'&psw='.d()->city->smsc_password.'&phones='.$phone.'&mes='.$text);
        }
        //$mresult .= json_encode($_SESSION);
        // логируем звонки
        //$l = d()->Log->new;
        //$l->title = $phone.'|'.$code;
        //$l->text = $mresult.'|session:'.json_encode($_SESSION);
        //$l->save;
    }

    // сохраняем код в базу
    $c = d()->Code->new;
    $c->phone = $phone;
    $c->code = $code;
    if($first)$c->is_tg = 1;
    $c->save;

    return $code;
}

// оставляем только корни, для поиска
class Stemming{
    private $VERSION = "0.02";
    private $unset_predlog = true; //удалять пердлоги ?
    private $Stem_Caching = 0;
    private $Stem_Cache = array();
    private $VOWEL = '/аеиоуыэюя/u';
    private $PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/u';
    private $REFLEXIVE = '/(с[яь])$/u';
    private $ADJECTIVE = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|еых|ую|юю|ая|яя|ою|ею)$/u';
    private $PARTICIPLE = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/u';
    private $VERB = '/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/u';
    private $NOUN = '/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|и|ы|ь|ию|ью|ю|ия|ья|я)$/u';
    private $RVRE = '/^(.*?[аеиоуыэюя])(.*)$/u';
    private $DERIVATIONAL = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/u';
    private $PREDLOG = '/(^|\s)(и|для|в|на|под|из|с|по)(\s|$)/u';

    private function s(&$s, $re, $to) {
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }

    private function m($s, $re) {
        return preg_match($re, $s);
    }

    public function stem_string($words) { // string stem_string( string $words )
        $word = explode(' ', $words);
        for ($i = 0; $i <= count($word); $i++) {
            if ($this->unset_predlog === TRUE)
                $word[$i] = preg_replace($this->PREDLOG, '', $word[$i]);
            $word[$i] = $this->stem_word($word[$i]);
            if (empty($word[$i]))
                unset($word[$i]);
        }
        return implode(' ', $word); //if you need return array change on -> return $word;
    }

    private function stem_word($word) {
        mb_regex_encoding('UTF-8');
        mb_internal_encoding('UTF-8');
        $word = mb_strtolower($word);
        $word = str_ireplace('ё', 'е', $word);
        # Check against cache of stemmed words
        if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
            return $this->Stem_Cache[$word];
        }

        $stem = $word;
        do {
            if (!preg_match($this->RVRE, $word, $p))
                break;
            $start = $p[1];
            $RV = $p[2];
            if (!$RV)
                break;

            # Step 1
            if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
                $this->s($RV, $this->REFLEXIVE, '');

                if ($this->s($RV, $this->ADJECTIVE, '')) {
                    $this->s($RV, $this->PARTICIPLE, '');
                } else {
                    if (!$this->s($RV, $this->VERB, ''))
                        $this->s($RV, $this->NOUN, '');
                }
            }

            # Step 2
            $this->s($RV, '/и$/', '');

            # Step 3
            if ($this->m($RV, $this->DERIVATIONAL))
                $this->s($RV, '/ость?$/', '');

            # Step 4
            if (!$this->s($RV, '/ь$/', '')) {
                $this->s($RV, '/ейше?/', '');
                $this->s($RV, '/нн$/', 'н');
            }

            $stem = $start . $RV;
        } while (false);
        if ($this->Stem_Caching)
            $this->Stem_Cache[$word] = $stem;
        return $stem;
    }

    private function stem_caching($parm_ref) {
        $caching_level = @$parm_ref['-level'];
        if ($caching_level) {
            if (!$this->m($caching_level, '/^[012]$/')) {
                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
            }
            $this->Stem_Caching = $caching_level;
        }
        return $this->Stem_Caching;
    }

    public function clear_stem_cache() {
        $this->Stem_Cache = array();
    }

}

function get_orders_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
    //if($_GET['key'] == d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    //$temp = '';
    if($_GET['id']){
        d()->orders_list = d()->Order($_GET['id']);
        get_products_options();
        //header ('Content-Type:application/xml; charset=utf-8');
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_orders_1c_tpl();
    }else{
        $check = d()->Export_order->where('city_id = ? AND status = 0', d()->city->id);
        if($check->count){
            $ids = $check->fast_all_of('order_id');
            d()->orders_list = d()->Order($ids);
            get_products_options();
            //$_SESSION['dbg'] = d()->cat_list;
            d()->Export_order->sql('UPDATE export_orders SET status=1 WHERE order_id IN ('.implode(',', $ids).')');

            //$temp = ' success';
            //header ('Content-Type:application/xml; charset=utf-8');
            header("Content-type: text/xml; charset=utf-8");
            print d()->export_orders_1c_tpl();
        }
    }

    //$l = d()->Log->new;
    //$l->title = 'Export 1C'.$temp;
    //$l->text = d()->city->code;
    //$l->save;
}

function get_reviews_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }
    d()->reviews_list = d()->Review->where('city_id = ? AND unload = 0', d()->city->id);
    if(d()->reviews_list->count){
        d()->Review->sql('UPDATE reviews SET unload=1 WHERE city_id = '.d()->city->id.' AND unload=0');
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_reviews_1c_tpl();
        exit;
    }
    print 'empty';
}

function get_promo_history_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }
    d()->promo_list = d()->Promo_history->where('upload = 0');
    if(d()->promo_list->count){
        d()->Promo_histories->sql('UPDATE promo_histories SET upload=1 WHERE upload=0');
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_promo_history_1c_tpl();
        exit;
    }
    print 'empty';
}

function test_tpl(){
    header('Content-type: text/html; charset=windows-1251');
    print d()->tt_tpl();
}

function get_products_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    if($_GET['id']){
        get_products_options();
        if($_GET['id']=='all'){
            d()->products_list = d()->Product->where('city_id = ?', d()->city->id);
        }else{
            d()->products_list = d()->Product->where('city_id = ? AND id = ?', d()->city->id, $_GET['id']);
        }
        header ('Content-Type:application/xml; charset=utf-8');
        print d()->export_products_1c_tpl();
    }
}

function get_changed_products_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }


    $log = d()->Log_product->where('checked=0');
    if(!$log->is_empty){
        d()->type_info = Array();
        foreach($log as $v){
            d()->type_info[] = Array('type'=>$log->type, 'id'=>$log->product_id);
            $l = d()->Log_product($log->id);
            $l->checked = 1;
            $l->save;
        }
        get_products_options();
        header ('Content-Type:application/xml; charset=utf-8');
        print d()->export_change_products_1c_tpl();
        exit;
    }
    print 'empty';
}

function get_users_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    if($_GET['id']){
        d()->users_list = d()->User->where('id = ? AND city = ?', $_GET['id'], d()->city->code);
        header ('Content-Type:application/xml; charset=utf-8');
        print d()->export_users_1c_tpl();
    }
}

function change_users_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    if($_GET['phone']){
        $user = d()->User->where('phone = ? AND city = ?', $_GET['phone'], d()->city->code)->limit(0,1);
        if(!$user->is_emty()){
            if($_GET['birthday'])$user->birthday = $_GET['birthday'];
            $user->save;
        }
    }
}

function change_promo_history_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    if($_GET['phone'] && $_GET['promo']){
        $pn1 = strtolower($_GET['promo']);
        $pn2 = strtoupper($_GET['promo']);

        $p = d()->Promocode->where('name = ? AND city_id = ? OR name = ? AND city_id = ?', $pn1, d()->city->id, $pn2, d()->city->id);
        $e = d()->Promo_history->new;
        $e->promocode_id = $p->id;
        $e->phone = $_GET['phone'];
        $e->upload = 1;
        $e->save;
        print 'ok';
    }
}

function change_sleep_numbers_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
        exit;
    }

    if($_GET['promo']) {
        $promo = d()->Promocode->where('name = ? AND city_id = ?', $_GET['promo'], d()->city->id);
    }

    if($promo->is_empty){
        print 'promo not found';
        exit;
    }

    if($_GET['phones']) {
        $phones = json_decode($_GET['phones'], true);
        foreach($phones as $v) {
            $s = d()->Sleep_phone->new;
            $s->phone = $v;
            $s->promocode_id = $promo->id;
            $s->save;
        }

        print 'ok';
        exit;
    }
}

function change_delivery_time(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
        exit;
    }

    if($_GET['time'] && $_GET['zone']) {
        if($_GET['zone'] == 'pickup'){
            d()->city->pickup_time = $_GET['time'];
            d()->city->save;
        }

        $zonis = d()->Zoni->where('city_id = ? AND title LIKE ?', d()->city->id, $_GET['zone']);
        if($zonis->is_empty){
            print 'zone not found';
            exit;
        }

        foreach($zonis as $v){
            $z = d()->Zoni($zonis->id);
            $z->time = $_GET['time'];
            $z->save;
        }
        // логирование изменение времени из 1С
        $l = d()->Content_log->new;
        $l->text = json_encode($_GET);
        $l->title = $_GET['zone'];
        $l->type = 'Update time';
        $l->city_id = d()->city->id;
        $l->save;

        print 'ok';
        exit;
    }

    print 'error1';
    exit;
}

function change_intime_orders(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
        exit;
    }

    if($_GET['time']) {
        $i = d()->Intime_order->new;
        $i->cook_time = $_GET['time'];
        $i->city_id = d()->city->id;
        $i->save;

        print 'ok';
        exit;
    }

    print 'error';
    exit;
}

function cron_clear_sleep_numbers(){
    d()->Sleep_phone->sql('TRUNCATE TABLE `sleep_phones`');
    d()->page_not_found();
    exit;
}

function xml($x){
    $x = strip_tags($x);
    //$x = str_replace('"', '&quot;', $x);
    $x = str_replace('©', '&copy;', $x);
    $x = str_replace('®', '&reg;', $x);
    $x = str_replace('™', '&trade;', $x);
    $x = str_replace('„', '&bdquo;', $x);
    $x = str_replace('“', '&ldquo;', $x);
    $x = str_replace('«', '&laquo;', $x);
    $x = str_replace('»', '&raquo;', $x);
    $x = str_replace('>', '&gt;', $x);
    $x = str_replace('<', '&lt;', $x);
    $x = str_replace('≥', '&ge;', $x);
    $x = str_replace('≤', '&le;', $x);
    $x = str_replace('≈', '&asymp;', $x);
    $x = str_replace('≠', '&ne;', $x);
    $x = str_replace('≡', '&equiv;', $x);
    $x = str_replace('§', '&sect;', $x);
    $x = str_replace('&', '&amp;', $x);
    $x = str_replace('∞', '&infin;', $x);

    return $x;
}

function ajax_check_url_genereator(){
    if($_POST['url'] && $_POST['table'] && $_POST['id'] && $_POST['city']){
        $url = $_POST['url'];
        $temp_url = '';
        $w = '';
        if($_POST['id']!='add')$w = ' AND id != '.$_POST['id'];
        $c = d()->Model->sql('SELECT * FROM '.$_POST['table'].' WHERE url = "'.$url.'" AND city_id = '.$_POST['city'].$w);

        $i = 1;
        while(!$c->is_empty()){
            $temp_url = '-'.$i;
            $c = d()->Model->sql('SELECT * FROM '.$_POST['table'].' WHERE url = "'.$url.$temp_url.'"');
            $i++;
        }

        print $url.$temp_url;
        exit;
    }
    d()->page_not_found();
}

function ajax_page(){
    get_city();
    $url = url(2);
    if($url == 'publichnaya-oferta'){
        d()->this = d()->Document(1);
        $arr['title'] = d()->this->title;
        $arr['text'] = d()->document_tpl();
    }elseif($url == 'personal'){
        d()->this = d()->Document(2);
        $arr['title'] = $arr['title'] = d()->this->title;
        $arr['text'] = d()->document_tpl();
    }elseif($url == 'requisites'){
        $arr['title'] = 'Реквизиты компании';
        $arr['text'] = d()->city->ur_requisites;
    }else{
        d()->this = d()->Page->find_by_url($url)->where('city_id=?', d()->city->id);
        if(d()->this->is_empty){
            d()->page_not_found();
        }
        $arr = Array();
        $arr['title'] = d()->this->title;
        $arr['text'] = d()->this->text;
    }
    return json_encode($arr);
}

function admin_action(){
    if($_SESSION['admin']){
        if($_GET['auth']){
            d()->Auth->login($_GET['auth']);
            header('Location: /');
        }

    }
}

function check_new_promo_minsum(){
    // если промокод есть
    if($_SESSION['promocode']['id'] && $_SESSION['promocode']['min_sum']){
        // если Минимальная сумма НЕ суммируется с баллами
        $sum = d()->cart_discount_price;
        d()->eshe = $_SESSION['promocode']['min_sum'] - d()->cart_discount_price;
        if($_SESSION['promocode']['min_sum_points']){
            // если Минимальная сумма суммируется с баллами
            $sum += d()->points_pay;
            d()->eshe -= d()->points_pay;
        }
        if($_SESSION['promocode']['min_sum_notdd']){
            $sum -= d()->total_not_dd;
            d()->eshe = $_SESSION['promocode']['min_sum'] - $sum;
        }
        if($sum < $_SESSION['promocode']['min_sum']){
            return false;
        }
    }
    return true;
}

function price_round($price='', $percent='', $rule=0){
    // вычисляем размер скидки
    $v = $price / 100 * $percent;
    $sum = $price-$v;
    // корректируем скидку
    switch ($rule) {
        case 0:
            $sum = ceil($sum);
            break;
        case 1:
            $sum = ceil($sum / 5) * 5;
            break;
        case 2:
            $sum = ceil($sum / 10) * 10;
            break;
    }
    $v = $price - $sum;
    return $v;
}

function check_promodate($end='', $ct=''){
    if($ct == 'now'){
        $t = date('U') + d()->city->timezone*60*60;
        $check = date('H', $t);
        if($check >= 0 && $check <= 4){
            // если текущее время 0, 1, 2, 3, 4 часа ночи, то для проверки берем предыдущий день
            $t = $t-86400;
        }
    }else{
        $t = strtotime($ct);
    }
    $e = $end+86399;
    if($t>$e)return 0;
    return 1;
}

function check_promotime($start_time='', $end_time='', $dop=0, $ct=''){
    if(d()->city->is_empty())get_city();
    if($start_time || $end_time) {
        if($ct){
            $time = date('Hi',strtotime($ct));
        }else{
            $time = date('Hi',date('U')+d()->city->timezone*3600);
        }

        $start = $start_time.'00';
        $end = $end_time.'00';
        if($dop && $end_time)$end = $end_time.$dop;

        if($start=='00')$start = 0;
        if($end=='00')$end = 2400;

        if($time<$start || $time>$end)return 0;
    }
    return 1;
}

function enformat($v){
    return number_format($v, 0, ' ', ' ');
}

function replece_fileds($text=''){

    $cat_title = 0;
    if(url(1)=='menu' && url(2) && url(3)=='index'){
        $cat_url = url(2);
    }elseif(url(1)=='ajax' && url(2)=='menu' && url(3) && url(4)=='index'){
        $cat_url = url(3);
    }else{
        $c = array_values(array_filter(explode('|', d()->this->category_id)));
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


    if(url(1)=='menu' && url(3)=='index'){
        $text = str_replace('{itemname}', d()->category->title, $text);
    }else{
        $ttl = d()->subcategory_title;
        if(!$ttl)$ttl = d()->this->title;
        $text = str_replace('{itemname}', $ttl, $text);
    }
    $text = str_replace('{parentname}', $cat_title, $text);
    $text = str_replace('{company}', d()->city->name, $text);
    $text = str_replace('{phone}', d()->city->phone, $text);

    if(!d()->done_price){
        $price = d()->product_price;
        if(!d()->this->p_list)$price = d()->this->price;
    }else{
        $price = d()->done_price;
    }
    $text = str_replace('{price}', $price, $text);

    $weight = d()->this->weight.' '.d()->this->weight_type.'.';
    if(!d()->this->weight && d()->this->number)$weight = d()->this->number.' '.d()->this->number_type.'.';
    $text = str_replace('{weight}', $weight, $text);

    $sostav =   strip_tags(d()->this->text);
    if(d()->this->sostav)$sostav = strip_tags(d()->this->sostav);
    $sostav = str_replace("'", "", htmlspecialchars_decode($sostav, ENT_QUOTES));
    $dl = mb_strlen($sostav, 'utf-8');
    $pos = strpos($text, '{compos:');
    if ($pos === false) {
        $lngt = 80;
        $fnd = '{compos}';
    }else{
        $temp = explode('}', str_replace('{compos:','', substr($text, $pos)));
        $lngt = $temp[0];
        $fnd = '{compos:'.$lngt.'}';
    }
    if($dl > $lngt){
        $sostav  = mb_substr($sostav, 0, $lngt);
        $sostav  = rtrim(trim($sostav), "!,.-")."…";
    }
    $text = str_replace($fnd, $sostav, $text);

    $text = str_replace('{city}', d()->city->title, $text);
    $text = str_replace('{city_r}', morphos\Russian\GeographicalNamesInflection::getCase(d()->city->title, 'родительный'), $text);
    $text = str_replace('{city_d}', morphos\Russian\GeographicalNamesInflection::getCase(d()->city->title, 'дательный'), $text);
    $text = str_replace('{city_v}', morphos\Russian\GeographicalNamesInflection::getCase(d()->city->title, 'винительный'), $text);
    $text = str_replace('{city_t}', morphos\Russian\GeographicalNamesInflection::getCase(d()->city->title, 'творительный'), $text);
    $text = str_replace('{city_p}', morphos\Russian\GeographicalNamesInflection::getCase(d()->city->title, 'предложный'), $text);
    return $text;
}

function check_seo_variables($text=''){
    $c = 0;
    if(strpos($text, '{itemname}') !== false)$c = 1;
    if(strpos($text, '{parentname}') !== false)$c = 1;
    if(strpos($text, '{company}') !== false)$c = 1;
    if(strpos($text, '{phone}') !== false)$c = 1;
    if(strpos($text, '{price}') !== false)$c = 1;
    if(strpos($text, '{weight}') !== false)$c = 1;
    if(strpos($text, '{compos') !== false)$c = 1;
    if(strpos($text, '{city}') !== false)$c = 1;
    if(strpos($text, '{city_r}') !== false)$c = 1;
    if(strpos($text, '{city_d}') !== false)$c = 1;
    if(strpos($text, '{city_v}') !== false)$c = 1;
    if(strpos($text, '{city_t}') !== false)$c = 1;
    if(strpos($text, '{city_p}') !== false)$c = 1;

    if($c)return true;
    return false;
}

function seo_redirect_module(){
    $domain = $_SERVER['HTTP_HOST'];
    $ch = d()->Redirect->sql('
        SELECT `id`, `lnk`, `multi_domain` 
        FROM `redirects` 
        WHERE multi_domain="" OR multi_domain="'.$domain.'"
    ')->to_array();

    $url = str_replace('?','',str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));

    foreach($ch as $v){
        // поиск на прямое совпадение
        if($v['lnk'] == $url){
            $co = d()->Redirect->sql('
                SELECT `id`, `goto`
                FROM `redirects` 
                WHERE id='.$v["id"].'
                LIMIT 0, 1
            ');
            $goto = $co->goto;
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: '.$goto);
            exit;
        }
        // поиск по регулярным вырожениям
        $result = preg_match($v['lnk'],$url,$f);
        if($result == 1){
            $co = d()->Redirect->sql('
                SELECT `id`, `goto`
                FROM `redirects` 
                WHERE id='.$v["id"].'
                LIMIT 0, 1
            ');
            $goto = str_replace('$1', $f[1], $co->goto);
            $goto = str_replace('$2', $f[2], $goto);
            $goto = str_replace('$3', $f[4], $goto);
            $goto = str_replace('$4', $f[5], $goto);
            $goto = str_replace('$5', $f[5], $goto);
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: '.$goto);
            exit;
        }
    }

}

function change_gifts_type(){
    if($_POST['type']){
        $_SESSION['show_gifts_type']=$_POST['type'];
        if($_POST['type']=='dr'){
            // удаляем баллы
            $_SESSION['points'] = 0;
            $_SESSION['old_points'] = 0;
            // удаляем баллы
        }
        if($_POST['type'] == 'dr'){
            $_SESSION['dr_date']=$_POST['dr_date'].'.????';
        }
        print 'ok';
        exit;
    }
    d()->page_not_found();
}

function ajax_likes(){
    if($_POST['id']){
        $p = d()->Product($_POST['id']);
        if(!$p->is_empty){
            if(!$_SESSION['auth']){
                if(!$_COOKIE['likes']){
                    $str = $p->id.',';
                    setcookie('likes', $str, time()+60*60*24*365*10, '/');
                }else{
                    $str = $_COOKIE['likes'];
                    $str .= $p->id.',';
                    setcookie('likes', $str, time()+60*60*24*365*10, '/');
                }
            }else{
                $u = d()->User($_SESSION['auth']);
                if(!$u->likes){
                    $u->likes = $p->id.',';
                }else{
                    $str = $u->likes;
                    $str .= $p->id.',';
                    $u->likes = $str;
                }
                $u->save;
            }

            if(!$_COOKIE['like_'.$p->id]){
                $p->likes = $p->likes+1;
                setcookie("like_".$p->id, 1, time()+60*60*24*365*10, '/');
            }else{
                $p->likes = $p->likes-1;
                setcookie("like_".$p->id, 0, time()+60*60*24*365*10, '/');
            }
            $p->save;

            print 'ok';
            exit;
        }
    }
    d()->page_not_found();
}

function check_wt(){
    if(d()->city->is_empty)get_city();

    $w = Array();
    $w[1] = d()->city->wt1;
    $w[2] = d()->city->wt2;
    $w[3] = d()->city->wt3;
    $w[4] = d()->city->wt4;
    $w[5] = d()->city->wt5;
    $w[6] = d()->city->wt6;
    $w[7] = d()->city->wt7;

    // вычисляем время
    $timezone = d()->city->timezone;
    $time = date('H')*3600+date('i')*60+date('s');
    //$time = 23*3600+00*60;

    // вычисляем день недели, с учетом таймзоны
    $time_tz = time();
    $time_tz += $timezone * 3600;
    $dn = date('N', $time_tz);
    $time_f = date('H', $time_tz)*3600+date('i', $time_tz)*60+date('s', $time_tz);
    // вычисляем режим работы, с учетом таймзоны
    if($time_f<14400){
        // берем режим работы вчерашнего дня
        $dn = $dn-1;
        if($dn<1){
            $dn=7;
        }
    }
    // правильного определения времени с учетом часового пояса
    $time = $time+$timezone*3600;

    // получаем режим работы в 2 переменные
    list($w_ot, $w_do) = explode('-',$w[$dn]);

    //$w_ot = '22:21';

    // преобразовываем промежуток работы филиала в секунды
    list($h1, $m1) = explode(':', $w_ot); //преобразовываем в секунды
    $w_ot_s=($h1 * 3600)+($m1 * 60);
    list($h2, $m2) = explode(':', $w_do); //преобразовываем в секунды
    $w_do_s=($h2 * 3600)+($m2 * 60);


    $timeot = $w_ot_s;
    $timedo = $w_do_s;
    // костыль время после 00:00
    if(!$timedo || $timedo<14400){
        $timedo = $timedo+86400;
    }
    // если время после 00:00
    if($time<14400 || !$time){
        $time = $time+86400;
    }

    //print $timedo;
    // +30 мин на заказ
    //$timedo = $timedo+1800;

    // if($USER->GetLogin()=='79050383338'){
    // print $timeot.'<br>';
    // print $time.'<br>';
    // print $timedo.'<br>';
    // }

    // проверка на работу филиала
    if($timeot<$time && $time<=$timedo){
        return true;
    }else{
        return false;
    }
}

function my_test(){
    print 'ok';
}

function ajax_compress_img($img=''){
    $img = '/storage/slides/500h500-10.jpg';
    if(!$img)return;
    $file = $_SERVER['DOCUMENT_ROOT'].$img;
    $mime = mime_content_type($file);
    $info = pathinfo($file);
    $name = $info['basename'];
    $output = new CURLFile($file, $mime, $name);
    $data = array(
        "files" => $output,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=80');
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $result = curl_error($ch);
    }
    curl_close ($ch);

    var_dump($result);
}

function ajax_ts_creator_products()
{
    if ($_POST['search']) {
        //$p = d()->Product->where('is_active=1')->search('title', $_POST['search']);
        $p = d()->Product->search('title', $_POST['search']);
        $r = '<div class="search_result">';
        $pl = d()->Propertie;
        foreach ($p as $k => $v) {
            $city = d()->City->where('id=?', $p->city_id);
            $properties = $pl->find_by_product_id($p->id);
            if ($p->is_active != 1){
                $active = '<strong style="color:orangered;">не активный</strong>';
            }else{
                $active = '<strong style="color:green;">активный</strong>';
            }
            if($properties->count){
                foreach ($properties as $pk=>$pv)
                {
                    $w = '';
                    if ($properties->weight) {
                        $w = $properties->weight . ' ' . $properties->weight_type;
                    }
                    $c = '';
                    if ($properties->number) {
                        $c = $properties->number;
                    }
                    $r .= '<div><a href="#" onclick="pick(this);" data-weight="' . $w . '" data-count="' . $c . '" data-price="' . $properties->price . '" >' . $p->title.', '.$properties->title . ' [ '.$city->title.' | '.$active.' ]</a></div>';
                }
            }else{

                $w = '';
                if ($p->weight) {
                    $w = $p->weight . ' ' . $p->weight_type;
                }
                $c = '';
                if ($p->number) {
                    $c = $p->number;
                }
                $r .= '<div><a href="#" onclick="pick(this);" data-weight="' . $w . '" data-count="' . $c . '" data-price="' . $p->price . '" >' . $p->title . ' [ '.$city->title.' | '.$active.' ]</a></div>';
            }
        }
        $r .= '</div>';
        print $r;
    }
}

function api_get_geo_1c(){
    //print 123;
    if($_GET['adr']){
        $adr = urlencode($_GET['adr']);
        $url = 'http://geocode-maps.yandex.ru/1.x/?geocode='.$adr.'&format=json&apikey=55c7d4ff-9ffe-4869-bc03-f0e3ad6f1b9f';
        $r = json_decode(file_get_contents($url), true);
        $pointpos = $r['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
        if($pointpos){
            print $pointpos;
        }else{
            print 'not found';
        }
        exit;
    }
    d()->page_not_found();
}

function api_anket_phones_1c(){
    if($_GET['key']){
        header('Content-type: application/json');
        get_city();
        if($_GET['key'] == d()->city->key_1c){
            $POST = file_get_contents('php://input');
            $data = json_decode($POST, true);
            if(!count($data))return 'no data';
            foreach($data as $k=>$v){
                $a = d()->Ankets_phone->new;
                $a->phone = d()->convert_phone($v['phone']);
                $a->city_id = d()->city->id;
                $a->filial_id_1c = $v['filial'];
                $a->name = $v['name'];
                $a->is_1c = 1;
                $a->save;
            }
            print 'OK';
            exit;
        }else{
            print 'incorrect key';
            exit;
        }
    }
    d()->page_not_found();
}

function ajax_other_modal(){
    if($_POST){
        get_city();
        // дополнительные массивы (обязательно перед каждой выборкой товаров)
        get_products_options();
        d()->this = d()->Product($_POST['id']);
        //$_SESSION['dbg1'] = d()->this;

        d()->other_list = d()->Other->where('product_id=? AND city_id=? AND is_active=1', d()->this->id, d()->city->id);
        $arr = Array();
        d()->items_array = Array();
        $flg_arr = Array();
        foreach(d()->other_list as $v){
            $e = explode(',', d()->other_list->text);
            foreach($e as $ve){
                $t = explode('_', $ve);
                $arr[] = $t[0];
                if(!d()->items_array[$t[0]]){
                    d()->items_array[$t[0]] = $t[1];
                }else{
                    d()->items_array[$t[0]] .= ','.$t[1];
                    if(!in_array($t[0], $flg_arr))$flg_arr[] = $t[0];
                }
            }
        }
        foreach($flg_arr as $k=>$v){
            $r = d()->items_array[$v];
            d()->items_array[$v] = explode(',', $r);
        }
        d()->items_list = d()->Product($arr)->where('is_stop=0');
        //$_SESSION['debug'] = d()->items_array;

        print d()->ajax_other_modal_tpl();
        exit;
    }
    d()->page_not_found();
}

function ajax_product_cart(){
    if($_POST){
        get_city();
        // дополнительные массивы (обязательно перед каждой выборкой товаров)
        get_products_options();
        d()->this = d()->Product($_POST['id']);

        if(d()->this->autoadd_products){
            $a = explode(',', d()->this->autoadd_products);
            $arr = Array();
            foreach ($a as $k_auto=>$v_auto){
                $auto = explode('|', $v_auto);
                $p_id = explode('_', $auto[0]);
                d()->auto_list = d()->Product($p_id[0])->to_array();
                d()->auto_list[0]['count'] = $auto[1];
                $arr[] = d()->auto_list[0];
            }
            d()->auto_products_list = $arr;
        }else{
            d()->auto_products_list = 0;
        }
        print d()->ajax_product_cart_tpl();
        exit;
    }
    d()->page_not_found();
}

function ajax_other_modal_gift(){
    if($_POST){
        get_city();

        d()->prtype = 'promo';
        if($_POST['type'])d()->prtype = $_POST['type'];

        d()->lists = Array();
        foreach($_POST['id'] as $vl){
            $ar = explode('_', $vl);
            $product = d()->Product($ar[0])->where('is_stop=0')->to_array();
            if($ar[1]){
                d()->property = d()->Property($ar[1])->to_array();
            }
            d()->other_list = d()->Other->where('product_id=? AND city_id=? AND is_active=1', $ar[0], d()->city->id);

            d()->lists[]  = Array(
                'product' => $product,
                'property' => d()->property,
                'others' => d()->other_list->to_array(),
            );
        }

        print d()->ajax_other_modal_gift_tpl();
        exit;
    }
    d()->page_not_found();
}

function ajax_gift_others(){
    if($_POST){
        $cart = $_SESSION['cart'];
        $ids = explode(',', $_POST['ids']);
        $types = explode(',', $_POST['types']);
        $items = explode('-', $_POST['items']);
        foreach($ids as $k=>$v){
            $t = explode('_', $v);
            if(!$t[1])$ids[$k] .= '0';
        }

        foreach($cart as $k=>$v){
            foreach($ids as $ik=>$iv){
                $s = $iv.'_'.$types[$ik];
                if(strpos($k, $s) !== false){
                    $cart[$k]['items'] = $items[$ik];
                    // для допов
                    $items_price = 0;
                    $items_title = '';
                    if($items[$ik]){
                        $i = explode(',', $items[$ik]);
                        $itms = Array();
                        d()->i_arr = Array();
                        foreach($i as $ku=>$vu){
                            $itmp = explode('|', $vu);
                            $item_cnt = $itmp[1];

                            $t = explode('_', $itmp[0]);
                            $itms[] = $t[0];
                            if(!d()->i_arr[$t[0]]){
                                d()->i_arr[$t[0]] = $t[1].'|'.$item_cnt;
                            }elseif(d()->i_arr[$t[0]] && !is_array(d()->i_arr[$t[0]])){
                                $tm = d()->i_arr[$t[0]];
                                unset(d()->i_arr[$t[0]]);
                                d()->i_arr[$t[0]][] = $tm;
                                d()->i_arr[$t[0]][] = $t[1].'|'.$item_cnt;
                            }else{
                                d()->i_arr[$t[0]][] = $t[1].'|'.$item_cnt;
                            }
                        }
                        $icnt = array_count_values($itms);
                        $itemss = d()->Product($itms);
                        foreach($itemss as $vit){
                            if(!is_array(d()->i_arr[$itemss->id])){
                                $sv = explode('|',d()->i_arr[$itemss->id]);
                                $prprt_pttl = '';
                                if($sv[0]){
                                    $prprt = d()->Property($sv[0]);
                                    $prprt_pttl = ' ('.$prprt->title.' / <i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт)';
                                    $items_price += $prprt->price*$sv[1];
                                    $items_title .= mb_strtolower(str_replace(',', '.', $itemss->title.$prprt_pttl)).', ';
                                }else{
                                    $items_price += $itemss->price*$sv[1];
                                    $items_title .= mb_strtolower(str_replace(',', '.', $itemss->title)).' (<i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт), ';
                                }

                            }else{
                                foreach(d()->i_arr[$itemss->id] as $ik=>$iv){
                                    $prprt_pttl = '';
                                    $sv = explode('|',$iv);
                                    if($sv[0]){
                                        $prprt = d()->Property($sv[0]);
                                        $prprt_pttl = ' ('.$prprt->title.' / <i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт)';
                                        $items_price += $prprt->price*$sv[1];
                                        $items_title .= mb_strtolower(str_replace(',', '.', $itemss->title.$prprt_pttl)).', ';
                                    }else{
                                        $items_price += $itemss->price*$sv[1];
                                        $items_title .= mb_strtolower(str_replace(',', '.', $itemss->title)).' (<i class="io-count" data-count="'.$sv[1].'">'.$sv[1].'</i> шт), ';
                                    }
                                }
                            }

                        }
                        $items_title = substr(trim($items_title),0,-1);
                    }
                    $cart[$k]['items_price'] = $items_price;
                    $cart[$k]['items_title'] = $items_title;
                }
            }
        }
        $_SESSION['cart'] = $cart;
        get_cart();

        $r = Array();
        $r['ch_cart'] = d()->cart_list_ch_tpl();
        $r['cart'] = d()->cart_list_tpl();

        print json_encode($r);
        exit;
    }
    d()->page_not_found();
}


// Универсальная функция отправки
function sigma_apiRequest($first = false, $data = false, $url_path = false, $token = false, $file = false) {
    global $token_filename;
    $api_url = 'https://online.sigmasms.ru/api/';
    $login = d()->city->sigma_login;
    $pass = d()->city->sigma_password;
    // Get Token
    if ($first) {
        $fields = array(
            'username' => $login,
            'password' => $pass,
            'type'     => 'local'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url.'login');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json; charset=UTF-8"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            $response = json_encode(array('error' => 'true'));
        } else {
            file_put_contents($token_filename, $response);
        }
    } elseif ($url_path && $token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url.$url_path);
        if ($file) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: ".mime_content_type($data['file']), "Content-length: ".filesize($data['file']), "Authorization: ".$token));
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($data['file']));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=UTF-8", "Accept: application/json", "Authorization: ".$token));
            if ($data && is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $data ));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            $response = json_encode(array('error' => 'true'));
        }
    }
    header("Content-Type: application/json;charset=UTF-8");
    return $response;
}
// Авторизация и получение токена
function sigma_apiAuth() {
    // Название файла для сохранения токена
    $token_filename = 'sigmatoken.txt';
    // проверяем токен
    if (file_exists($token_filename) && (date('Y-m-d H:i:s', filemtime($token_filename)) > date('Y-m-d H:i:s', strtotime('-23 hours')))) {
        $result = file_get_contents($token_filename, true);
    } else {
        $result = sigma_apiRequest(true);
    }
    //
    $unjes = json_decode($result);
    if (isset($unjes->token) && !empty($unjes->token)) {
        $token = (string) $unjes->token;
    } else {
        $token = null;
    }
    return $token;
}
function sigma_clear_phone($phone) {
    $phone_number = preg_replace('/[() -]+/', '', $phone);
    return $phone_number;
}
// Загрузка файла
function sigma_uploadFile($file_path) {
    $token = sigma_apiAuth();
    if ($token) {
        $dataFile = array('file' => dirname(__FILE__).'/'.$file_path);
        return sigma_apiRequest(false, $dataFile, 'storage', $token, true);
    }
}
// Отправка одиночного сообщения
function sigma_sendOneMess($type, $recipient, $sender, $text, $button = null, $image = null) {
    $token = sigma_apiAuth();
    if ($token) {
        $params = array(
            "type"       => $type,
            "recipient"  => sigma_clear_phone($recipient),
            "payload"    => array(
                "sender" => $sender,
                "text"   => $text,
                "button" => $button,
                "image"  => $image
            )
        );
        return sigma_apiRequest(false, $params, 'sendings', $token);
    }
}
// Отправка каскада
function sigma_sendCascade($data) {
    $token = sigma_apiAuth();
    if ($token) {
        return sigma_apiRequest(false, $data, 'sendings', $token);
    }
}
// Проверка статуса
function sigma_checkStatus($id) {
    if ($id) {
        $token = sigma_apiAuth();
        if ($token) {
            return sigma_apiRequest(false, false, 'sendings/'.$id, $token);
        }
    }
}

function sigma_api(){
    /* Тесты */
    //$myphone = '+79050383338';
    //$myphone = '+79061248503';

    //echo 'Тест СМС: '.PHP_EOL;
    //$sendSms = sigma_sendOneMess('sms', $myphone, 'B-Media', 'Это тестовое сообщение.\nИзменённый текст будет отправлен на модерацию.');
    //var_dump($sendSms);

    //echo PHP_EOL.'Проверка статуса сообщения: '.PHP_EOL;
    //var_dump(sigma_checkStatus('6035fe28-2f60-4973-8681-jhjh887990087'));

    //echo PHP_EOL.'Загрузка картинки: '.PHP_EOL;
    //$upload_image = sigma_uploadFile('test.png');
    //var_dump($upload_image);
    //echo 'Проверить корректность загрузки можно по ссылке: https://online.sigmasms.ru/api/storage/{user_id}/{image_key}'.PHP_EOL;

    //echo PHP_EOL.'Тест Viber: '.PHP_EOL;
    //$msg_image = sigma_json_decode($upload_image);
    //if (isset($msg_image->key)) {
        //var_dump(sigma_sendOneMess('viber', $myphone, 'MediaGorod', 'Это тестовое сообщение.\nИзменённый текст будет отправлен на модерацию.', array('text' => 'Текст кнопки', 'url' => 'https://google.ru'), $msg_image->key));
    //}

//    echo PHP_EOL.'Каскадная переотправка VK->Viber->SMS: '.PHP_EOL;
//    $cascadeData = array(
//        "type"       => 'vk',
//        "recipient"  => sigma_clear_phone($myphone),
//        "payload"    => array(
//            "sender" => 'sigmasmsru',
//            "text"   => 'Это тестовое сообщение. Изменённый текст будет отправлен на модерацию.',
//        ),
//        "fallbacks"  => [
//            array(
//                "type"       => 'viber',
//                "payload"    => array(
//                    "sender" => 'MediaGorod',
//                    "text"   => 'Это тестовое сообщение. Изменённый текст будет отправлен на модерацию.',
//                    "image"  => "",
//                    "button" => array(
//                        "text" => "Текст кнопки",
//                        "url"  => 'https://google.ru',
//                    ),
//                ),
//                '$options' => array(
//                    "onStatus" => ["failed"],
//                    "onTimeout" => array(
//                        "timeout" => 120,
//                        "except"  => ["delivered", "seen"]
//                    )
//                )
//            ),
//            array(
//                "type"    => "sms",
//                "payload" => array(
//                    "sender" => "B-Media",
//                    "text"   => 'Это тестовое сообщение. Изменённый текст будет отправлен на модерацию.'
//                ),
//                '$options' => array(
//                    "onStatus" => ["failed"],
//                    "onTimeout" => array(
//                        "timeout" => 120,
//                        "except"  => ["delivered", "seen"]
//                    )
//                )
//            )
//        ]
//    );
//    var_dump(sigma_sendCascade($cascadeData));
}

function ajax_check_order_conf(){
    if($_POST['code'] && $_POST['phone']){
        $r = Array();
        $ph = d()->convert_phone($_POST['phone']);
        $code = $_SESSION['order_conf'][$ph]['code'];
        if(!$code){
            $c = d()->Code->where('phone = ?', $ph)->order_by('id DESC')->limit(0,1);
            $code = $c->code;
            if($c->is_empty){
                // логируем косяк
                $l = d()->Log->new;
                $l->title = 'noregphone2';
                $l->text = json_encode($_SESSION);
                $l->save;
            }
        }
        $post_code = str_replace(' ', '', $_POST['code']);

        if($post_code == $code){
            $r['result'] = 1;
            $_SESSION['order_conf'][$ph]['result'] = 1;
        }else{
            $r['result'] = 0;
            $r['text'] = 'код подтверждения введен неверно';
        }
        //$r['post_code'] = $post_code;
        //$r['session_code'] = $code;
        print json_encode($r);
        exit;
    }
    d()->page_not_found();
}

function ajax_resend_order_conf(){
    if($_POST['phone']){
        get_city();
        $r = Array();
        $ph = d()->convert_phone($_POST['phone']);
        // проверям последнее время отправки кода и лимиты
        $txt = '';
        if(d()->city->send_code_time){
            if($_SESSION['order_conf']['time']){
                $check_time = date('U') - $_SESSION['order_conf']['time'];
                if($check_time < d()->city->send_code_time){
                    $ct = d()->city->send_code_time - $check_time;
                    $sec  = declOfNum ($ct, array('секунду', 'секунды', 'секунд'));
                    $txt = 'СМС с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';
                    if(d()->city->send_code_type != 1)$txt = 'Авто-звонок с кодом подтверждения можно отправить через '.$ct.' '.$sec.'.';

                    $r['result'] = 0;
                    $r['text'] = $txt;
                }
            }
        }

        // отправляем звонок/смс
        if(!$txt){
            $r['result'] = 1;
            $r['text'] = 'код отправлен повторно';
            if(d()->city->send_code_type != 1)$r['text'] = 'авто-звонок отправлен повторно';

            $code = d()->send_code($ph);
            if($_SESSION['smsc']['sms']){
                $r['sms'] = 1;
                $r['text'] = '';
            }
            $_SESSION['order_conf'][$ph]['code'] = $code;
            $_SESSION['order_conf']['time'] = date('U');
        }
        return json_encode($r);
    }
    d()->page_not_found();
}

function cron_send_ankets(){
    // собираем массив городов
    $cities = d()->City->to_array();
    $c = Array();
    foreach($cities as $v){
        $c[$v['id']] = $v;
    }
    // собираем массив настроек
    $options = d()->Option->to_array();
    $o = Array();
    foreach($options as $v){
        $o[$v['city_id']] = $v;
    }
    // собираем массив филиалов
    $filials = d()->Office->to_array();
    $f = Array();
    foreach($filials as $v){
        $f[$v['1c_id']] = $v;
    }

    /*print '<pre>';
    print_r($o);
    print '</pre>';*/

    // выбираем все телефоны за вчера
    $phones = d()->Ankets_phone;
    // готовим массивы для отправки
    $sp = Array();
    foreach($phones as $v){
        $ci = $phones->city_id;
        // проверяем, включена ли отправка для этого города
        if(!$o[$ci]['send_anket_type'])continue;
        // проверяем, не превышен ли максимум отправки в день
        if($o[$ci]['send_anket_max'] && count($sp[$ci]) >= $o[$ci]['send_anket_max']){
            continue;
        }
        // проверяем, была ли отправка для этого клиента раньше
        if($o[$ci]['send_anket_rate']){
            $d = date('Y-m-d', strtotime('-'.$o[$ci]['send_anket_rate'].' days'));
            $check = d()->Ankets_history->where('created_at > ? AND title = ?', $d, $phones->phone);
            if(!$check->is_empty)continue;
        }
        // проверяем, есть ли этот телефон в массиве
        $flg = 0;
        foreach($sp[$ci] as $kh=>$vh){
            if($vh['phone'] == $phones->phone){
                $flg = 1;
                break;
            }
        }
        if($flg)continue;
        // добавляем в массив рассылки
        // $office_id = $f[$phones->filial_id_1c]['id'];
        $sp[$ci][] = Array(
            'phone' => $phones->phone,
            'name' => $phones->name,
            'office_id' => $phones->filial_id_1c,
            'type' => $o[$ci]['send_anket_type'],
        );
    }
    // рассылка
    foreach($sp as $k=>$v){
        $code = $c[$k]['code'];
        $vkname = $c[$k]['sigma_vk_sender'];
        if(d()->city->id == 6){
            $l = 'https://radugavkusaufa.ru/anketa/';
        }else{
            $l = 'https://'.$code.'.appetitfood.ru/anketa/';
        }

        foreach($v as $key=>$value){
            // переводим телефон в 16-ричную систему исчисления
            $p16 = dechex($value['phone']);
            $name = $value['name'];
            if(!$name)$name = 'Дорогой друг';
            $link = $l.'?p='.$p16.'&filial='.$value['office_id'];
            $msg = str_replace('{link}', $link, $o[$k]['send_anket_text']);
            $msg = str_replace('{name}', $name, $msg);
            // если сигма
            if($value['type'] == 2){
                $r = sigma_sendOneMess('vk', $value['phone'], $vkname, $msg);
                // создаем историю по этому телефону
                $h = d()->Ankets_history->new;
                $h->title = $value['phone'];
                $h->save;
            }
            // если телеграм
            if($value['type'] == 1){
                $parr = Array();
                $parr[] = $value['phone'];
                $result = d()->sendMessageTG($msg, $parr);
                $r = json_decode($result, true);
                print '<pre>';
                print_r($r);
                print '</pre>';
                if($r['status'] == 'ok'){
                    // создаем историю по этому телефону
                    $h = d()->Ankets_history->new;
                    $h->title = $value['phone'];
                    $h->save;
                }
            }
        }
    }
    // записываем в статистику количество отправленных смс
    $date = date('U');
    foreach($sp as $k=>$v){
        $s = d()->Anketstat->new;
        $s->date = $date;
        $s->city_id = $k;
        $s->count = count($v);
        $s->save;
    }

    // очищаем таблицу
    d()->Ankets_phone->sql('TRUNCATE TABLE `ankets_phones`');
}

function sendMessageTG($text = '', $users = Array()){
    $site = 2;
    $url = 'https://tgbot.appetitfood.ru/api.php?method=send_message&text=tst&site='.$site;

    $data = array(
        'text' => $text,
        'users' => $users,
        'force' => 1
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function sigma_test(){
    d()->city = d()->City(1);
    $k = '79061248503';
    //$k = '79050383338';
    $vkname = d()->city->sigma_vk_sender;
    $link = 'https://'.d()->city->code.'.appetitfood.ru/anketa/';
    //$link = 'https://kzn.appetitfood.ru/anketa/';
    $name = 'Вахтанг';
    //$name = 'Кирилл';
    $text = $name.', Здравствуйте.
    Вчера Вы делали заказ в «Аппетит».
    Оставьте, пожалуйста, обратную связь, уделив не более 1 минуты.
    Это поможет нам стать лучше.
    '.$link.'
    4-5 быстрых вопроса, ответив на которые Вы поможете выявить наши слабые места и повысить качество нашей доставки.
    Давайте строить лучшую доставку вместе!
    '.$link;
    var_dump(sigma_sendOneMess('vk', $k, $vkname, $text));
}

function ajax_upload_file(){
    if($_FILES){
        $name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $name_files = date("U");
        //$picture_adress = '/storage/otzyvy/'.md5($name.'salt').'.' . strtolower( substr(strrchr($name, '.'), 1));
        $picture_adress = '/storage/otzyvy/'.$name_files.'.' . strtolower( substr(strrchr($name, '.'), 1));
        $r = move_uploaded_file($tmp_name,$_SERVER['DOCUMENT_ROOT'].$picture_adress);
        if(file_exists($_SERVER['DOCUMENT_ROOT'].$picture_adress) && $r == true){
            $f = 1;
        }else{
            $f = 2;
        }
        if($f == 1){
            $_SESSION['upload_name_files'][] = $name_files.'.' . strtolower( substr(strrchr($name, '.'), 1));
            return json_encode($r);
        }elseif($f == 2){
            $r = 'error';
            return json_encode($r);
        }
    }
    d()->page_not_found();
}

function export_products()
{
    if(!$_GET['city'] && !$_GET['category']){
        d()->page_not_found();
        exit;
    }

    if($_GET['city']){
        d()->city = d()->City($_GET['city']);
        get_products_options();
        d()->products_list = d()->Product->where('city_id = ?', $_GET['city']);
    }
    if($_GET['category']){
        d()->category = d()->Category($_GET['category']);
        d()->city = d()->City(d()->category->city_id);
        get_products_options($_GET['category']);
        d()->products_list = d()->Product->search('category_id', '|'.$_GET['category'].'|');
    }

    // Подключение класса для работы с Excel
    require_once($_SERVER['DOCUMENT_ROOT']."/vendors/export_excel/PHPExcel.php");
    // Подключение класса для вывода данных в формате Excel
    require_once($_SERVER['DOCUMENT_ROOT']."/vendors/export_excel/PHPExcel/Writer/Excel5.php");

    // Создание объекта класса PHPExcel
    $myXls = new PHPExcel();
    // Указание на активный лист
    $myXls->setActiveSheetIndex(0);
    // Получение активного листа
    $mySheet = $myXls->getActiveSheet();
    // Указание названия листа книги
    $mySheet->setTitle("Новый лист");

    // объединяем ячейки для заголовка
    //$mySheet->mergeCells('A1:L1');
    //$mySheet->getStyle("A1")->getFont()->setSize(20);

    // Указываем значения для отдельных ячеек
    //$mySheet->setCellValue("A1", d()->period);

    // ширина столбцов
    $mySheet->getColumnDimension("A")->setAutoSize(true);
    $mySheet->getColumnDimension("B")->setAutoSize(true);
    $mySheet->getColumnDimension("C")->setAutoSize(true);
    $mySheet->getColumnDimension("D")->setAutoSize(true);
    $mySheet->getColumnDimension("E")->setAutoSize(true);
    $mySheet->getColumnDimension("F")->setAutoSize(true);
    $mySheet->getColumnDimension("G")->setAutoSize(true);
    $mySheet->getColumnDimension("H")->setAutoSize(true);
    $mySheet->getColumnDimension("I")->setAutoSize(true);
    $mySheet->getColumnDimension("J")->setAutoSize(true);
    $mySheet->getColumnDimension("K")->setAutoSize(true);

    // жирный шрифт заголовков
    $styleArray = array('font' => array('bold' => true));

    $mySheet->setCellValue("A1", "Название")->getStyle('A1')->applyFromArray($styleArray);
    $mySheet->setCellValue("B1", "Город")->getStyle('B1')->applyFromArray($styleArray);
    $mySheet->setCellValue("C1", "Активность")->getStyle('C1')->applyFromArray($styleArray);
    $mySheet->setCellValue("D1", "Категория")->getStyle('D1')->applyFromArray($styleArray);
    $mySheet->setCellValue("E1", "Подкатегория")->getStyle('E1')->applyFromArray($styleArray);
    $mySheet->setCellValue("F1", "Цена (руб)")->getStyle('F1')->applyFromArray($styleArray);
    $mySheet->setCellValue("G1", "URL блюда")->getStyle('G1')->applyFromArray($styleArray);
    $mySheet->setCellValue("H1", "URL картинки")->getStyle('H1')->applyFromArray($styleArray);
    $mySheet->setCellValue("I1", "Вес / объем")->getStyle('I1')->applyFromArray($styleArray);
    $mySheet->setCellValue("J1", "Количество (шт)")->getStyle('J1')->applyFromArray($styleArray);
    $mySheet->setCellValue("K1", "Состав")->getStyle('K1')->applyFromArray($styleArray);

    $city = Array();
    d()->cities_list = d()->City;
    foreach(d()->cities_list as $v){
        $city[d()->cities_list->id] = d()->cities_list->title;
    }

    $city_codes = Array();
    d()->cities_list = d()->City;
    foreach(d()->cities_list as $v){
        $city_codes[d()->cities_list->id] = d()->cities_list->code;
    }

    $categories = Array();
    d()->categories_list = d()->Category;
    foreach(d()->categories_list as $v){
        $categories[d()->categories_list->id] = d()->categories_list->title;
    }

    $subcategories = Array();
    d()->subcategories_list = d()->Subcategory;
    foreach(d()->subcategories_list as $v){
        $subcategories[d()->subcategories_list->id] = d()->subcategories_list->title;
    }

    // начальная строка
    $i = 2;
    foreach(d()->products_list as $k=>$v) {
        // категория
        $cat_title = '';
        $c = array_values(array_filter(explode('|', $v->category_id)));
        foreach($c as $key=>$val){
            $cat_title .= $categories[$val].', ';
        }
        $cat_title = substr(trim($cat_title),0,-1);
        // url
        $url = 'https://'.$city_codes[$v->city_id].'.appetitfood.ru';
        if($v->city_id == 6)$url = 'https://radugavkusaufa.ru';
        // вес / объем
        $w = '';
        if($v->weight)$w = $v->weight.' '.$v->weight_type.'.';
        // кол-во
        $n = '';
        if($v->number)$n = $v->number.' '.$v->number_type.'.';

        if(is_array($v->p_list) && $v->p_list){
            foreach($v->p_list as $pl) {

                // вес / объем
                $w = '';
                if($pl['weight'])$w = $pl['weight'].' '.$pl['weight_type'].'.';

                $mySheet->setCellValue("A" . $i, $v->title.', '.$pl['title']);
                $mySheet->setCellValue("B" . $i, $city[$v->city_id]);
                $mySheet->setCellValue("C" . $i, $v->is_active_simple_word);
                $mySheet->setCellValue("D" . $i, $cat_title);
                $mySheet->setCellValue("E" . $i, $subcategories[$v->subcategory_id]);
                $mySheet->setCellValue("F" . $i, $pl['price']);
                $mySheet->setCellValue("G" . $i, $url.$v->link);
                $mySheet->setCellValue("H" . $i, $url.$v->image);
                $mySheet->setCellValue("I" . $i, $w);
                $mySheet->setCellValue("J" . $i, $n);
                $mySheet->setCellValue("K" . $i, $v->sostav);

                $i++;
            }
        }else{
            $mySheet->setCellValue("A" . $i, d()->products_list->title);
            $mySheet->setCellValue("B" . $i, $city[$v->city_id]);
            $mySheet->setCellValue("C" . $i, $v->is_active_simple_word);
            $mySheet->setCellValue("D" . $i, $cat_title);
            $mySheet->setCellValue("E" . $i, $subcategories[$v->subcategory_id]);
            $mySheet->setCellValue("F" . $i, $v->price);
            $mySheet->setCellValue("G" . $i, $url.$v->link);
            $mySheet->setCellValue("H" . $i, $url.$v->image);
            $mySheet->setCellValue("I" . $i, $w);
            $mySheet->setCellValue("J" . $i, $n);
            $mySheet->setCellValue("K" . $i, $v->sostav);

            $i++;
        }
    }

    // HTTP-заголовки
    header ("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("Content-type: application/vnd.ms-excel");
    header ("Content-Disposition: attachment; filename=Блюда Аппетит ".date('d-m-Y').".xls");

    // Вывод файла
    $objWriter = new PHPExcel_Writer_Excel5($myXls);
    $objWriter->save("php://output");
}

function delete_sticker() {
    $s1 = '14,';
    $s2 = ',14';
    $s3 = '14';

    $p = d()->Product->search('sticker', $s1);
    foreach($p as $v){
        //$pr = d()->Product($p->id);
        //print '<b>'.$pr->sticker.'</b> / '.$pr->id.'<br>';
    }
    //print '<hr>';

    $p = d()->Product->search('sticker', $s2);
    foreach($p as $v){
        //$pr = d()->Product($p->id);
        //print '<b>'.$pr->sticker.'</b> / '.$pr->id.'<br>';
    }
    //print '<hr>';

    $p = d()->Product->search('sticker', $s3);
    //$p = d()->Product->where('sticker=?', $s3);
    foreach($p as $v){
        $pr = d()->Product($p->id);
        print '<b>'.$pr->sticker.'</b> / '.$pr->id.'<br>';
        //$pr->sticker = str_replace($s3, '', $pr->sticker);
        //$pr->save;
    }
}

function promo_trans_en($text=''){
    $text = strtr($text,array(
        "У"=>"Y",
        "К"=>"K",
        "Е"=>"E",
        "Н"=>"H",
        "Х"=>"X",
        "В"=>"B",
        "А"=>"A",
        "Р"=>"P",
        "О"=>"O",
        "М"=>"M",
        "Т"=>"T",
        "у"=>"y",
        "к"=>"k",
        "е"=>"e",
        "н"=>"h",
        "х"=>"x",
        "в"=>"b",
        "а"=>"a",
        "р"=>"p",
        "о"=>"o",
        "м"=>"m",
        "т"=>"t"
    ));
    return $text;
}

function promo_trans_ru($text=''){
    $text = strtr($text,array(
        "Y"=>"У",
        "K"=>"К",
        "E"=>"Е",
        "H"=>"Н",
        "X"=>"Х",
        "B"=>"В",
        "A"=>"А",
        "P"=>"Р",
        "O"=>"О",
        "M"=>"М",
        "T"=>"Т",
        "y"=>"у",
        "k"=>"к",
        "e"=>"е",
        "h"=>"н",
        "x"=>"х",
        "b"=>"в",
        "a"=>"а",
        "p"=>"р",
        "o"=>"о",
        "m"=>"м",
        "t"=>"т"
    ));
    return $text;
}

function autoadd_products($str = '', $cart = Array(), $type = '', $oldcnt='')
{
    $t1 = explode(',', $str);
    foreach ($t1 as $k => $v) {
        $t2 = explode('|', $v);
        $t3 = explode('_', $t2[0]);

        $p = d()->Product($t3[0])->where('is_stop=0');
        $property = '';
        $_property = '';
        $_property_title = '<i class="free-to-order">бесплатно к заказу</i>';
        $price = $p->price;
        $old_price = $p->old_price;
        if($t3[1]){
            $property = d()->Property($t3[1]);
            $_property = $property->id;
            if($property->title){
                $_property_title .= ', '.$property->title;
            }
            $price = $property->price;
            $old_price = $property->old_price;
        }
        $count = $t2[1];
        if(!$old_price)$old_price = $price;

        if($p->is_empty || $property->is_empty && $t3[1])continue;

        if($type == 'add' || $type == 'plus'){
            if(!$cart[$t2[0]]) {
                // добавляем новый
                $cart[$t2[0]] = Array(
                    'id' => $p->id,
                    'id_1c' => $p->id_1c,
                    'property' => $_property,
                    'property_title' => $_property_title,
                    'count' => $count,
                    'title' => $p->title,
                    'category_id' => $p->f_category_id,
                    // цена за 1 товар
                    'price' => $price,
                    // цена за все товары
                    'total_price' => $price * $count,
                    // цена за 1 товар с учетом скидки за самовывоз (независимо от выбранного способа доставки)
                    'dd_price' => get_discount_price($old_price, $p->not_dd, 1),
                    // скидка за самовывоз за 1 товар
                    'pickup_discount' => $old_price - $price,
                    // скидка за самовывоз за все товары
                    'total_pickup_discount' => $old_price - $price,
                    // товар не собственного производства
                    'not_dd' => $p->not_dd,
                    // сумма без скидок за самовывоз и пр.
                    'image' => d()->preview($p->image, '120', '120'),
                    'auto' => 1,
                    // приборы
                    'tableware' => $p->tableware
                );
            }else{
                // увеличиваем количество
                $new_count = $cart[$t2[0]]['count'] + $count;
                $cart[$t2[0]]['count'] = $new_count;
                $cart[$t2[0]]['total_price'] = $price*$new_count;
            }
        }

        if($type == 'minus'){
            // уменьшаем количество
            $new_count = $cart[$t2[0]]['count'] - $count;
            if($new_count>0) {
                $cart[$t2[0]]['count'] = $new_count;
                $cart[$t2[0]]['total_price'] = $price * $new_count;
            }else{
                unset($cart[$t2[0]]);
            }
        }

        if($type == 'delete'){
            // удаляем или уменьшаем количество
            $new_count = $cart[$t2[0]]['count'] - $count*$oldcnt;
            if($new_count>0){
                $_SESSION['cart'][$t2[0]]['count'] = $new_count;
                $_SESSION['cart'][$t2[0]]['total_price'] = $price*$new_count;
            }else{
                unset($_SESSION['cart'][$t2[0]]);
            }
        }
    }

    return $cart;
}

function ajax_check_banner_cookies(){
    if($_POST['check']){
        setcookie("cookies_banner", 1, time()+60*60*24*365*10, '/');
        print 'ok';
        exit;
    }
    d()->page_not_found();
}

function check_twopromo(){
    $s = $_SESSION;
    $promo_id = $s['promocode']['id'];
    $promo_type = $s['promocode']['type'];
    if($promo_id){
        $flg = 0;
        foreach($s['cart'] as $k=>$v){
            // для промо Скидка
            if($v['promocode_id'] && $v['promocode_id'] != $promo_id){
                unset($_SESSION['cart'][$k]['promocode_id']);
                unset($_SESSION['cart'][$k]['promo_title']);
                unset($_SESSION['cart'][$k]['promo_count']);
                unset($_SESSION['cart'][$k]['promo_group']);
                unset($_SESSION['cart'][$k]['promo_used']);
                unset($_SESSION['cart'][$k]['promo_discount']);
                unset($_SESSION['cart'][$k]['total_promo_discount']);
                $flg = 1;
            }
            // для промо подарок
            if($promo_type != 3 && strpos($k, 'promo') !== false){
                unset($_SESSION['cart'][$k]);
                $flg = 1;
            }
        }
        if($flg){
            $log = Array();
            $log['old_session'] = $s;
            $log['new_session'] = $_SESSION;
            $l = d()->Log->new;
            $l->title = 'twopromo';
            $l->text = json_encode($log);
            $l->save;

            return true;
        }
    }
    return false;
}

function check_twogiftpickup(){
    $s = $_SESSION;
    $flg = 0;
    $check = 0;
    foreach($s['cart'] as $k=>$v){
        // если есть один подарок, то ставим метку
        if(strpos($k, 'gift_pickup') !== false && !$check){
            $check = 1;
            continue;
        }
        // если это второй подарок, удаляем его
        if(strpos($k, 'gift_pickup') !== false && $check){
            unset($_SESSION['cart'][$k]);
            $flg = 1;
        }
    }
    if($flg){
        $log = Array();
        $log['old_session'] = $s;
        $log['new_session'] = $_SESSION;

        $l = d()->Log->new;
        $l->title = 'twogiftpickup';
        $l->text = json_encode($log);
        $l->save;

        return true;
    }

    return false;
}

function ajax_get_dopcity_category(){
    if($_POST['city_id']){
        d()->categories_list = d()->Category->where('city_id = ?', $_POST['city_id']);
        d()->subcategories_list = d()->Subcategory->where('city_id = ?', $_POST['city_id']);
        print d()->ajax_get_dopcity_category_tpl();
        exit;
    }
    d()->page_not_found();
}

function get_export_promo(){
    get_city();
    d()->name = d()->city->name;
    d()->url_main = $_SERVER['HTTP_HOST'];
    d()->counter = d()->city->yandex_counter;
    //d()->url_city = d()->city->code;
    //d()->page_not_found();
    d()->xmlpromos_list = d()->Sale->where('city_id = ? AND is_active = 1 AND is_secret = 0', d()->city->id);
    if(d()->xmlpromos_list->count){
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_promo_tpl();
        exit;
    }
    print 'empty';
}

function get_export_news(){
    get_city();
    d()->name = d()->city->name;
    d()->url_main = $_SERVER['HTTP_HOST'];
    d()->counter = d()->city->yandex_counter;
    //d()->url_city = d()->city->code;
    //d()->page_not_found();
    d()->xmlnews_list = d()->News->where('city_id = ?', d()->city->id);
    if(d()->xmlnews_list->count){
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_news_tpl();
        exit;
    }
    print 'empty';
}

function onlynumbers($s=''){
    return preg_replace("/[^,.0-9]/", '', $s);
}

function ajax_get_dopedit_sales_admin(){
    $cities = d()->City;
    d()->cities = Array();
    foreach($cities as $k=>$v){
        d()->cities[$cities->id] = $cities->title;
    }

    d()->sales_list = d()->Sale->where('id != ?', $_POST['noid'])->order_by('title ASC');
    print d()->ajax_admin_dopedit_sales_tpl();
}

function do_sales(){
    //$_SESSION['POST']=$_POST;
    $element_id = $_POST['element_id'];
    if($element_id == 'add'){
        $type = 'add';
        // костылек
        $e = d()->Sale->new;
        $e->title = 'test';
        $elem = $e->save_and_load();
        $element_id = $elem->id;
        $e = d()->Sale($elem->id);
        $e->delete;
        // костылек
        if(count($_POST['dopcity'])){
            foreach($_POST['dopcity'] as $k=>$v){
                $city = $k;
                $s = d()->Sale->new;
                $element_id++;
                foreach($_POST['data'] as $key=>$value){
                        if($key=='city_id')$value = $city;
                        if($key=='products')$value = '';
                        if($key=='products2')$value = '';
                        if($key=='url'){
                            if($value == '')$value = 'sale'.$element_id;
                        }
                        //$_SESSION['debug']['foreach'][$city][$key] = $value;
                        $s[$key] = $value;
                    }
                    $sales = $s->save_and_load();
                    $_SESSION['debug']['save_and_load'][] = $sales->id;
                }
            }

        // если есть еще города, в которые нужно добавить этот товар
        $_SESSION['POST'] = $_POST;

    }elseif($_POST['_action'] == 'admin_delete_element'){
        $type = 'delete';
    }else{
        $type = 'edit';

        $edit_sale = d()->Sale->where('id=?', $_POST['element_id']);
        $ar = Array();
        foreach ($_POST['data'] as $k_pd=>$v_pd){
            if(trim($_POST['data'][$k_pd]) != trim($edit_sale[$k_pd])) $ar[$k_pd] = $v_pd;
        }

        if(count($_POST['sales'])){
            foreach($_POST['sales'] as $k=>$v){
                $sales = explode(",", $v);
                foreach($sales as $k1=>$v1){
                    $s = d()->Sale->where('id = ?', $v1);
                    $city = $s->city_id;
                    $products = $s->products;
                    $products2 = $s->products2;
                    $url = $s->url;
                    foreach ($ar as $key=>$value){
                        if($key=='city_id')$value = $city;
                        if($key=='products')$value = $products;
                        if($key=='products2')$value = $products2;
                        if($key=='url'){
                            if($value == ''){
                                //$value = 'sale'.$v1;
                                $value = $url;
                            }
                        }
                        $s[$key] = $value;
                    }
                    $s->save();
                }
            }
        }
    }

    /*$l = d()->Log_product->new;
    $l->type = $type;
    $l->product_id = $element_id;
    $l->save;*/
}

function ajax_get_dopedit_promos_admin(){
    $cities = d()->City;
    d()->cities = Array();
    foreach($cities as $k=>$v){
        d()->cities[$cities->id] = $cities->title;
    }

    d()->promocodes_list = d()->Promocode->where('id != ?', $_POST['noid'])->order_by('name ASC');
    print d()->ajax_admin_dopedit_promos_tpl();
}

function do_promos(){
    $element_id = $_POST['element_id'];
    if($element_id == 'add'){
        $type = 'add';

        if(count($_POST['dopcity'])){
            foreach($_POST['dopcity'] as $k=>$v){
                $city = $k;
                $s = d()->Promocode->new;
                foreach($_POST['data'] as $key=>$value){
                    if($key=='city_id')$value = $city;
                    if($key=='products')$value = '';
                    if($key=='required_products')$value = '';
                    if($key=='gift')$value = '';
                    //$_SESSION['debug']['foreach'][$city][$key] = $value;
                    $s[$key] = $value;
                }
                $sales = $s->save_and_load();
                $_SESSION['debug']['save_and_load'][] = $sales->id;
            }
        }

        // костылек
        /*$e = d()->Product->new;
        $e->title = 'test';
        $elem = $e->save_and_load();
        $element_id = $elem->id+1;
        $e = d()->Product($elem->id);
        $e->delete;*/
        // костылек

        // если есть еще города, в которые нужно добавить этот товар
        $_SESSION['POST'] = $_POST;

    }elseif($_POST['_action'] == 'admin_delete_element'){
        $type = 'delete';
    }else{
        $type = 'edit';

        $edit_promocode = d()->Promocode->where('id=?', $_POST['element_id']);
        $ar = Array();
        foreach ($_POST['data'] as $k_pd=>$v_pd){
            if(trim($_POST['data'][$k_pd]) != trim($edit_promocode[$k_pd])) $ar[$k_pd] = $v_pd;
        }

        if(count($_POST['promos'])){
            foreach($_POST['promos'] as $k=>$v){
                $promos = explode(",", $v);
                foreach($promos as $k1=>$v1){
                    $s = d()->Promocode->where('id = ?', $v1);
                    $city = $s->city_id;
                    $products = $s->products;
                    $products_limit = $s->products_limit;
                    $required_products = $s->required_products;
                    $gift = $s->gift;
                    foreach ($ar as $key=>$value){
                        if($key=='city_id')$value = $city;
                        if($key=='products')$value = $products;
                        if($key=='products_limit')$value = $products_limit;
                        if($key=='required_products')$value = $required_products;
                        if($key=='gift')$value = $gift;
                        $s[$key] = $value;
                    }
                    $s->save();
                }
            }
        }
    }

    /*$l = d()->Log_product->new;
    $l->type = $type;
    $l->product_id = $element_id;
    $l->save;*/
}

function ajax_get_dopedit_autogoods_admin(){
    d()->city = d()->City($_POST['city_id']);
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        d()->cnt_array = Array();
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            d()->cnt_array[$a[0]] = $a[1];
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
    get_products_options();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_dopedit_autogoods_tpl();
    //print d()->ajax_admin_products_fs_tpl();
}

function do_categoria(){
    $element_id = $_POST['element_id'];

    if($element_id == 'add'){
        $type = 'add';

    }elseif($_POST['_action'] == 'admin_delete_element'){
        $type = 'delete';
    }else{
        $type = 'edit';
        //получение старого списка автотоваров
        $old_goods = d()->Categorie->where('id=?', $element_id);
        $old = explode(',', $old_goods->autogoods);
        $old_array_autogoods = Array();
        foreach ($old as $k_old=>$v_old){
            $oldautogoods = explode('|', $v_old);
            $old_array_autogoods[$oldautogoods[0]] = $oldautogoods;
        }

        //массив нового списка автотоваров
        $autogoods = explode(',',$_POST['data']['autogoods']);
        $autogoods_element_id = Array();
        foreach($autogoods as $k_ag=>$v_ag) {
            $autogoods_id = explode('|', $v_ag);
            $autogoods_element_id[$autogoods_id[0]] = $autogoods_id;
        }

        //проверка списков на разницу
        $this_array_autogoods = array_diff_assoc($old_array_autogoods, $autogoods_element_id);

        //проверяем автотовары у продуктов
        $products = d()->Product->where('category_id =? AND city_id=? OR category_id LIKE ? AND city_id LIKE ?', '%|'.$element_id.'|%', $_POST['data']['city_id'], '%|'.$element_id.'|%', $_POST['data']['city_id']);
        foreach ($products as $key_p=>$value_p){
            foreach ($value_p as $k_pro=>$v_pro){
                $p = d()->Product->where('id=?', $v_pro['id']);
                $autoadd_products = explode(',', $p->autoadd_products);
                $auto_product = Array();
                foreach($autoadd_products as $k_aap=>$v_aap){
                    $aap_id = explode('|', $v_aap);
                    $auto_product[$aap_id[0]] = $aap_id;
                }

                $autoadd_pro_array = array_diff_assoc($auto_product, $this_array_autogoods);

                $this_auotadd_pro = array_diff_assoc($autogoods_element_id, $autoadd_pro_array);
                $result = array_merge($autoadd_pro_array, $this_auotadd_pro);
                $str_result = "";
                foreach ($result as $key_r=>$value_r){
                    $str_result .= $value_r[0].'|'.$value_r[1].',';
                }
                $str_result = trim($str_result, '|,');
                $p->autoadd_products = $str_result;
                $p->save;
            }
        }
    }
}

function cron_clear_codes(){
    $d = strtotime('-1 day');
    $date = date('Y-m-d H:i:s', $d);
    print $date;
    d()->Code->sql('DELETE FROM `codes` WHERE `created_at` <= "'.$date.'"');

    // очищаем логи возрат записи которой более 5 дней
    //$time = date('d.m.Y');
    $clear_days = date('Y-m-d', strtotime("- 5 days"));
    d()->Check->sql('DELETE FROM `content_logs` WHERE `created_at` <= "'.$clear_days.'"');
    d()->Check->sql('DELETE FROM `logs` WHERE `created_at` <= "'.$clear_days.'"');
    d()->Check->sql('DELETE FROM `address_logs` WHERE `created_at` <= "'.$clear_days.'"');

    // очищаем записи созданы более месяца
    $month = date('Y-m-d', strtotime("- 1 month"));
    d()->Check->sql('DELETE FROM `cashback_tasks` WHERE `created_at` <= "'.$month.'"');
    d()->Check->sql('DELETE FROM `export_orders` WHERE `created_at` <= "'.$month.'"');

    /*$month2 = date('Y-m-d', strtotime("- 2 month"));
    $name_db = 'orders_' . date('Y');
    d()->Check->sql('INSERT INTO `'.$name_db.'` SELECT * FROM `orders` WHERE `created_at` <= "'.$month2.'"');
    d()->Check->sql('DELETE FROM `orders` WHERE `created_at` <= "'.$month2.'"');*/
    print 'ok';
}

function check_wt_pickup($order_save = ''){
    $wts = str_replace(':', '', d()->city->wt_pickup);
    $wtt = date('Gi') + d()->city->timezone*100;
    if($wtt >= 0 && $wtt <= 500)$wtt += 2400;

    $wttemp = explode(':', d()->city->wt_pickup);
    $wth = $wttemp[0]+1;
    if($wth >= 24)$wth -= 24;
    if($wth < 10)$wth = '0'.$wth;
    d()->wt_pickup_hour = $wth.':'.$wttemp[1];

    if($wtt >= $wts){
        return 1;
    }

    // если готовить ко времени
    if($_SESSION['order_info']['cook_time_value'] && $_SESSION['order_info']['cook_time'] == 'in_time' && $order_save){
        $ctv = explode(',', $_SESSION['order_info']['cook_time_value']);
        $cth = explode(':', $ctv[1]);
        $rct = $cth[0].'00';
        if($rct >= 0 && $rct <= 500)$rct += 2400;
        if($rct > $wts+100){
            return 1;
        }
    }
    return 0;
}

function cron_ankets_histories(){
    // чистим ankets_histories
    $dd = strtotime('-35 days');
    $d = date('Y-m-d', $dd);
    $r = d()->Ankets_history->sql('DELETE FROM `ankets_histories` WHERE `created_at`<"'.$d.'"');

    // чистим intime_orders
    $dd2 = strtotime('-1 day');
    $d2 = date('Y-m-d', $dd2);
    $d22 = date('Y.m.d', $dd2);
    $r2 = d()->Intime_order->sql('DELETE FROM `intime_orders` WHERE `cook_time` LIKE "%'.$d2.'%" OR `cook_time` LIKE "%'.$d22.'%"');
}

function update_filter_products(){
    d()->filter_list = d()->Filter->where('city_id=3')->to_array();
    d()->all_filter = d()->Filter->to_array();
    d()->product_list = d()->Product->to_array();
    foreach (d()->product_list as $key=>$value){
        if($value['filter']){
            $filter = $value['filter'];
            //if(d()->product_list[2107]['filter']) $filter = d()->product_list[2107]['filter'];
            $str_filter = "";
            $f = explode(',', trim($filter, ','));
            foreach ($f as $k=>$v){
                foreach (d()->all_filter as $k_af=>$v_af)
                {
                    if($v == $v_af['id'])$title = $v_af['title'];
                }
                print $title.'</br>';
                //$filter_id = d()->Filter->where('id=?', $v);
                //$title = $filter_id->title;
                $filter_element = "";
                foreach (d()->filter_list as $k_lt=>$v_lt)
                {
                    if($v_lt['title'] == $title || mb_strtolower($v_lt['title']) == mb_strtolower($title)) $filter_element = $v_lt['id'];
                }
                $str_filter .= $filter_element.',';
            }
            d()->this_product = d()->Product->where('id=?', $value['id']);
            //d()->this_product = d()->Product->where('id=?', d()->product_list[2107]['id']);
            print d()->this_product->filter.'</br>';
            d()->this_product->filter = $str_filter;
            d()->this_product->save;
            print $str_filter.'</br>';
            $str_filter = "";
            print $str_filter.'</br>';
        }
    }
}

function ajax_cancel_birthday(){
    if($_POST){
        if($_POST['birthday'] == 1){
            unset($_SESSION['show_gifts_type']);
            unset($_SESSION['dr_date']);
        }
    }
    d()->page_not_found();
}

function birthdays_api(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
        exit;
    }

    if($_GET['info']) {
        $info = json_decode($_GET['info'], true);
        foreach($info as $k=>$v){
            $u = d()->User->where('phone = ?', $k);
            if(!$u->is_empty){
                if(!$u->birthday){
                    $u->birthday = $v;
                    $u->save;
                }
                continue;
            }
            $b = d()->Birthday->where('phone = ?', $k);
            if($b->is_empty){
                $b = d()->Birthday->new();
                $b->phone = $k;
                $b->city_id = d()->city->id;
            }
            $b->birthday = $v;
            $b->save;
        }
        print 'ok';
        exit;
    }
}

function update_url_product(){
    $categoria = d()->Categorie->where('title=? || title=?', 'Пицца', 'Пиццы')->to_array();
    $str1 = '-21-sm';
    $str2 = '-32-sm';
    $str3 = '-21sm';
    $str4 = '-32sm';
    foreach ($categoria as $key_category=>$value_category){
        $list_product = d()->Product->where('category_id =?', '|'.$value_category['id'].'|')->to_array();
        foreach ($list_product as $key=>$value){
            if(strpos($value['url'], $str1) !== false){
                $r = str_replace($str1, '', $value['url']);
                $temp = d()->Product($value['id']);
                $temp->url = $r;
                $temp->save;
                print $value['url'].' | '.$r.'</br>';
            }
            if(strpos($value['url'], $str2) !== false){
                $r = str_replace($str2, '', $value['url']);
                $temp = d()->Product($value['id']);
                $temp->url = $r;
                $temp->save;
                print $value['url'].' | '.$r.'</br>';
            }
            if(strpos($value['url'], $str3) !== false){
                $r = str_replace($str3, '', $value['url']);
                $temp = d()->Product($value['id']);
                $temp->url = $r;
                $temp->save;
                print $value['url'].' | '.$r.'</br>';
            }
            if(strpos($value['url'], $str4) !== false){
                $r = str_replace($str4, '', $value['url']);
                $temp = d()->Product($value['id']);
                $temp->url = $r;
                $temp->save;
                print $value['url'].' | '.$r.'</br>';
            }
        }
        /*print '<PRE>';
        print_r ($list_product);
        print '</PRE>';*/
    }
}

function check_product_in_stop(){
    if($_SESSION['cart']){
        $cart = $_SESSION['cart'];
        $a_p = Array();
        foreach ($cart as $kc=>$vc){
            if($vc['property'] != 0){
                $pr = d()->Propertie($vc['property']);
                if($pr->is_stop != 0){
                    $a_p[] = $pr->title;
                }
            }
            $p = d()->Product($vc['id']);
            if($p->is_stop != 0){
                $a_p[] = $p->title;
            }
        }
        if($a_p){
            return $a_p;
        }
    }
}

function ajax_change_image(){
    if($_POST['id']){
        $p = d()->Propertie($_POST['id']);
        d()->image_p = $p->image;
        d()->image_alt_p = $p->image_alt;
        d()->person = $p->number_persons;
        if(d()->image_p){
            $str = d()->image_p.','.d()->image_alt_p.','.d()->person;
            return $str;
        }elseif(d()->person){
            $str = d()->person;
            return $str;
        }else{
            return '';
        }
    }
}

function ajax_wtjsline(){
    // сегодня
    get_city();
    if($_SESSION['delivery'] == 2 && $_SESSION['zone']){
        $start_t = (int)$_SESSION['zone']['time']+60;
    }else{
        $start_t = 120;
    }
    $start_h = floor($start_t / 60);
    $start_m = $start_t % 60;
    $start = date('H:i', mktime($start_h, $start_m ));

    $time = date('U');
    d()->unix_time = $time + d()->city->timezone*3600;
    // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
    $f_unix_time = d()->unix_time - 4*3600;
    d()->n_week = date('N', $f_unix_time);
    $worktime = d()->city['wt'.d()->n_week];
    $tmp1 = explode('-', $worktime);
    $tmp2 = explode(':', $tmp1[0]);
    $tmp3 = explode(':', $tmp1[1]);

    // заказ минимум +2 часа от текущего времени
    $th = date('G:i');
    $th1 = explode(':', $th);
    $t_th = $th1[0]+d()->city->timezone;
    //$t1 = 60 * $t_th + $th1[1];
    $t1 = $t_th.':'.$th1[1];

    if($tmp2[0]<$t_th){
        $tmp2[0]=$t_th;
        $tmp2[1]=$th1[1];
    }

    list($h, $m) = explode(':', $t1);
    $th2 = date('H:i', strtotime("+$h hour $m minute", strtotime($start)));
    list($h1, $m2) = explode(':', $th2);
    if($m2 >= 30){
        $h1 += 1;
        $m2 = 0;
        $m3 = 30;
    }else{
        $m2 = 30;
        $m3 = 0;
    }
    //$th3 = date('H:i', mktime($h1, $m2));
    if($h1 == 24)$h1 = 0;

    if($h1 >= (int)$tmp3[0] && $h1 < (int)$tmp2[0]) $h1 = (int)$tmp3[0];
    if($_SESSION['delivery'] == 1){
        $h_end = 23;
        $m_end = 0;
    }else{
        $h_end = (int)$tmp3[0];
        $m_end = $tmp3[1];
    }
    $line2 = '';
    $i1 = 0;
    while($h1 != $h_end && $i1 <= 24){
        $line2 .= date('H:i', mktime($h1, $m2)).',';
        if($m2 == 30){
            $h1++;
            if($h1 == 24)$h1 = 0;
            if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
        }else{
            if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
            $h1++;
            if($h1 == 24)$h1 = 0;
        }
        $i1++;
    }
    $line2 .= date('H:i', mktime($h_end, $m_end));
    return $line2;
}

function do_logs(){
    // логирование данных
    $cl = d()->Content_log->new;
    $cl->user_admin = $_SESSION['admin'];
    $cl->title = $_POST['element_id'];
    $cl->type = url(3);
    $cl->text = json_encode($_POST);
    $cl->save;
}

function check_properties(){
    if($_POST){
        $properties_list = d()->Propertie->where('product_id LIKE ?', $_POST['data']['product_id'])->to_array();
        $cnt = count($properties_list);
        $i = 0;
        foreach ($properties_list as $key=>$value){
            if($value['id'] != $_POST['element_id']){
                if($value['is_stop'] == 1) $i++;
            }else{
                if($_POST['data']['is_stop'] == 1) $i++;
            }
        }
        $p = d()->Product($_POST['data']['product_id']);
        if($i == $cnt && $p->is_stop != 1){
            $p->is_stop = 1;
            $p->save;
        }elseif($i == $cnt && $p->is_stop == 1){
            $p->is_stop = 1;
            $p->save;
        }else{
            $p->is_stop = 0;
            $p->save;
        }
    }
}

function sync_likes($id){
    $user_id = $id;
    $u = d()->User($user_id);
    if(!$u->is_empty){
        if(!$u->likes){
            if($_COOKIE['likes']) $u->likes = $_COOKIE['likes'];
            $u->save;
        }else{
            if($_COOKIE['likes']){
                $str1 = trim($u->likes, ',');
                $likes_db = explode(',', $str1);
                $str2 = trim($_COOKIE['likes'], ',');
                $likes_cookie = explode(',', $str2);

                $likes = array_diff($likes_cookie, $likes_db);
                $str_likes = '';
                foreach ($likes as $value){
                    $str_likes .= $value.',';
                }
                $u->likes .= $str_likes;
                $u->save;
            }
        }
    }
}

function ajax_show_details(){
    if($_SESSION['zone']){
        $str = $_SESSION['zone']['text'].'*<br>*время доставки может изменятся из-за нескольких причин: большого количества заказов, пробки на дорогах, перекрытие улиц.';
        $_SESSION['dbg1'] = $str;
        return $str;
    }
}

function ajax_get_promo_zones_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }

    $sortline = 'sort ASC';
    if($_POST['zones']){
        $p = explode(',', $_POST['zones']);
        d()->zones = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = str_replace('|', '', $v);
            d()->zones[$a] = $a;
            $sortline .= 'id='.$a.' DESC, ';
            d()->picked_id .= '|'.$a.'|';
        }
        $sortline .= 'sort ASC';
    }

    d()->sortline = $sortline;
    d()->zones_list = d()->Zoni->where('city_id=?', d()->city->id);
    //get_products_options();
    //d()->products_list = d()->Product->where('city_id=? AND is_active=1', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_zones_tpl();
}

function ajax_check_hints(){
    if($_POST){
        $_SESSION['select_address'] = $_POST['id'];
    }
    get_city();
    if(d()->city->hints_address){
        return d()->city->hints_address;
    }
}

function ajax_change_options(){
    $str = Array();
    get_city();
    d()->options = d()->Option->where('city_id=?', d()->city->id);
    if(!d()->options->is_empty){
        $str['y_oundedby'] = d()->options->y_oundedby;
        $str['replace'] = d()->options->y_replace;
        return json_encode($str);
    }
}

function ajax_check_address(){
    $mass = Array();
    if($_POST){
        $adr = $_POST['adr'];
        $smladr = $_POST['smladr'];
        $flag = $_POST['order_flag'];
        $_SESSION['zone']['address_id'] = '';
        $result = Array();
        get_city();
        $code = d()->city->code;
        $tbl = 'geo_'.$code.'coords';

        if($_POST['address_id']){
            //$al = d()->Address->where("id = ? AND created_at >= '2021-04-25'", $_POST['address_id'])->limit(0,1);
            $al = d()->Address($_POST['address_id'])->limit(0,1);
            $_SESSION['zone']['address_id'] = $_POST['address_id'];
            if($al->created_at >= '2021-05-06' && $al->lon && $al->lat){
                $lon = $al->lon;
                $lat = $al->lat;
            }else{
                $geo = d()->Geo_kzncoord->sql('SELECT * FROM `'.$tbl.'` WHERE `title` LIKE "%'.$al->street.'%"');
                if(!$geo->is_empty){
                    $crds = explode(',', $geo->coords);
                    $lon = $crds[0];
                    $lat = $crds[1];
                }else{
                    unset($_SESSION['zone']);
                    $result['type'] = 'error-1';
                    $result['text'] = 'Такого адреса нету в базе.';
                    $result['street'] = $al->street;
                    return json_encode($result);
                    exit;
                }
            }
        }else{
            $geo = d()->Geo_kzncoord->sql('SELECT * FROM `'.$tbl.'` WHERE `title` LIKE "'.$adr.'"');
            if(!$geo->is_empty){
                if($geo->type == 2){
                    unset($_SESSION['zone']);
                    $result['type'] = 'error-2';
                    $result['text'] = 'Неполный адрес. Укажите номер дома.';
                    return json_encode($result);
                    exit;
                }else{
                    $crds = explode(',', $geo->coords);
                    $lon = $crds[0];
                    $lat = $crds[1];
                }
            }else{
                unset($_SESSION['zone']);
                $result['type'] = 'error-1';
                $result['text'] = 'Такого адреса нету в базе.';
                return json_encode($result);
                exit;
            }
        }
    }

    // определение зоны доставки
    $points = Array();
    $points[0] = $lon;
    $points[1] = $lat;

    if($points[0] && $points[1]){
        $geomob = json_decode(d()->geomob($points[0],$points[1]), true);
        $_SESSION['geomob'] = $geomob;
        // не входит в зону доставки
        if($geomob['city_id']){

            $mass = $geomob;
            $mass['tst'] = $adr;
            $mass['type'] = $geo->type;

            $mass['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
            $mass['result'] = 'success';
            $mass['coords_type'] = 'cache_address';

            if($flag){
                $_SESSION['zone']['address'] = $smladr;
                $_SESSION['zone']['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
                $_SESSION['zone']['title'] = $mass['title'];
                $_SESSION['zone']['text'] = $mass['text'];
                $_SESSION['zone']['price'] = $mass['price'];
                $_SESSION['zone']['min_order'] = $mass['min_order'];
                $_SESSION['zone']['free'] = $mass['free'];
                $_SESSION['zone']['time'] = $mass['time'];
                $_SESSION['zone']['time2'] = $mass['time2'];
                $_SESSION['zone']['time3'] = $mass['time3'];
                $_SESSION['zone']['lat'] = $points[1];
                $_SESSION['zone']['lon'] = $points[0];
                $_SESSION['zone']['type'] = $geo->type;
                $_SESSION['zone']['category_id'] = $mass['category_id'];

                $_SESSION['delivery_price'] = $mass['price'];
            }
            return json_encode($mass);
        }
    }
    unset($_SESSION['zone']);
    $mass['result']='zone_error';
    $mass["coords"] = $points[1].', '.$points[0];
    return json_encode($mass);
}

function ajax_cache_address(){
    if($_POST){
        $mass = Array();
        $coords = $_POST['coords'];
        $address = $_POST['adr'];
        $type = $_POST['type'];
        $smladr = $_POST['smladr'];
        $flag = $_POST['order_flag'];

        // кэшируем адрес
        get_city();
        $code = d()->city->code;
        $tbl = 'geo_'.$code.'coords';
        $geo = d()->Geo_kzncoord->sql('INSERT INTO `'.$tbl.'` (`coords`, `title`,`type`) VALUES ("'.$coords.'", "'.$address.'", "'.$type.'")');

        // определяем зону доставки
        $points = explode(',', $coords);
        if($points){
            $geomob = json_decode(d()->geomob($points[0],$points[1]), true);
            $_SESSION['geomob'] = $geomob;
            // не входит в зону доставки
            if($geomob['city_id']){

                $mass = $geomob;
                $mass['tst'] = $address;
                $mass['type'] = $type;

                $mass['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
                $mass['result'] = 'success';
                $mass['coords_type'] = 'yandex';

                if($flag){
                    $_SESSION['zone']['address'] = $smladr;
                    $_SESSION['zone']['f_title'] = $mass['title'].'. Стоимость доставки '.$mass['price'].' руб. От '.$mass['free'].' руб. - доставка БЕСПЛАТНО.';
                    $_SESSION['zone']['title'] = $mass['title'];
                    $_SESSION['zone']['text'] = $mass['text'];
                    $_SESSION['zone']['price'] = $mass['price'];
                    $_SESSION['zone']['min_order'] = $mass['min_order'];
                    $_SESSION['zone']['free'] = $mass['free'];
                    $_SESSION['zone']['time'] = $mass['time'];
                    $_SESSION['zone']['time2'] = $mass['time2'];
                    $_SESSION['zone']['time3'] = $mass['time3'];
                    $_SESSION['zone']['lat'] = $points[1];
                    $_SESSION['zone']['lon'] = $points[0];
                    $_SESSION['zone']['type'] = $type;
                    $_SESSION['zone']['category_id'] = $mass['category_id'];

                    $_SESSION['delivery_price'] = $mass['price'];
                }
                return json_encode($mass);
            }
        }
        unset($_SESSION['zone']);
        $mass['result']='zone_error';
        $mass["coords"] = $points[1].', '.$points[0];
        return json_encode($mass);
    }
}

function ajax_clear_zone(){
    unset($_SESSION['zone']);
}

function ajax_get_sales_products_admin_two(){
    d()->city = d()->City($_POST['city_id']);
    //get_city();
    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = explode('|', $v);
            d()->products[$a[0]] = $a;
            $id = explode('_', $a[0]);
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$a[0].'|';
        }
        $sortline .= 'sort ASC';
    }
    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
//    get_products_options();
    get_products_options_admin();
    d()->products_list_2 = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_products_fs_two_tpl();
}

// дополнительные массивы перед выборкой товаров в админке сайта (промокоды и акции)
function get_products_options_admin(){
    d()->property_list = d()->Property->where('city_id=?', d()->city->id);
    d()->other_list = d()->Other->where('city_id=?', d()->city->id);

    // пересобираем свойства, что бы сохранить модель Property
    d()->pa_list = Array();
    $i = 0;
    foreach(d()->property_list as $v){
        d()->pa_list[$i]['id'] = d()->property_list->id;
        d()->pa_list[$i]['price'] = d()->property_list->price;
        d()->pa_list[$i]['title'] = d()->property_list->title;
        d()->pa_list[$i]['is_default'] = d()->property_list->is_default;
        d()->pa_list[$i]['product_id'] = d()->property_list->product_id;
        d()->pa_list[$i]['city_id'] = d()->property_list->city_id;
        d()->pa_list[$i]['category_id'] = d()->property_list->category_id;
        d()->pa_list[$i]['weight'] = d()->property_list->weight;
        d()->pa_list[$i]['weight_type'] = d()->property_list->weight_type;
        d()->pa_list[$i]['number_persons'] = d()->property_list->number_persons;
        d()->pa_list[$i]['is_stop'] = d()->property_list->is_stop;
        $i++;
    }

    // формируем массив для быстрого поиска по свойствам и допам
    d()->p_id_arr = array_column(d()->property_list->to_array(),  'product_id');
    d()->other_id_arr = array_column(d()->other_list->to_array(),  'product_id');

    // формируем массив для быстрого поиска по категориям
    d()->categories = d()->Category->where('city_id=? AND is_active=1', d()->city->id);
    //d()->categories = $cat_list;
    d()->cat_list = Array();
    foreach(d()->categories as $v){
        d()->cat_list[d()->categories->id]['url'] = d()->categories->url;
        d()->cat_list[d()->categories->id]['title'] = d()->categories->title;
        d()->cat_list[d()->categories->id]['property_title'] = d()->categories->property_title;
    }

    // стикеры
    $stickers = d()->Sticker;
    d()->stickers_list = Array();
    foreach($stickers as $v){
        d()->stickers_list[$stickers->id]['image'] = $stickers->image;
        d()->stickers_list[$stickers->id]['title'] = $stickers->title;
    }
}


/**
 * Get header Authorization *
 **/
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header *
 **/
function getBearerToken() {
    $headers = d()->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function cron_check_sales(){
    d()->sales_list = d()->Sale->where('is_archive != 1 AND is_active = 1 AND end_date != "" OR is_archive != 1 AND is_secret = 1 AND end_date != ""');
    if(!d()->sales_list->is_empty){
        $time = date('d.m.Y');
        $t = date('U', strtotime($time."- 7 day"));
        $i = 0;
        foreach (d()->sales_list as $k_list=>$v_list){
            $time_end = date('U', strtotime($v_list->end_date));
            if($time_end < $t){
                d()->sale = d()->Sale($v_list->id);
                d()->sale->is_active = 0;
                d()->sale->is_secret = 0;
                d()->sale->is_archive = 1;
                d()->sale->save;
                $i++;
            }
        }
    }
}

function do_property(){
    //$_SESSION['POST']=$_POST;
    $element_id = $_POST['element_id'];
    if($element_id == 'add'){
        $type = 'add';
        // костылек
        /*$e = d()->Sale->new;
        $e->title = 'test';
        $elem = $e->save_and_load();
        $element_id = $elem->id;
        $e = d()->Sale($elem->id);
        $e->delete;*/
        // костылек
        if(count($_POST['dopcity'])){
            foreach($_POST['dopcity'] as $k=>$v){
                $city = $k;
                $product = d()->Product($_POST['data']['product_id']);
                $product_id = d()->Product->where('title = ? AND city_id = ?', $product->title, $city);

                $pr = d()->Propertie->new;
                //$element_id++;
                foreach($_POST['data'] as $key=>$value){
                    if($key=='city_id')$value = $city;
                    if($key=='product_id')$value = $product_id->id;
                    $pr[$key] = $value;
                }
                $propertie = $pr->save_and_load();
                $_SESSION['debug']['save_and_load'][] = $propertie->id;
            }
        }

        // если есть еще города, в которые нужно добавить этот товар
        $_SESSION['POST'] = $_POST;

    }elseif($_POST['_action'] == 'admin_delete_element'){
        $type = 'delete';
    }else{
        $type = 'edit';

        $edit_propertie = d()->Propertie->where('id=?', $_POST['element_id']);
        $ar = Array();
        foreach ($_POST['data'] as $k_pd=>$v_pd){
            if(trim($_POST['data'][$k_pd]) != trim($edit_propertie[$k_pd])) $ar[$k_pd] = $v_pd;
        }

        if(count($_POST['properties'])){
            foreach($_POST['properties'] as $k=>$v){
                $properties = explode(",", $v);
                foreach($properties as $k1=>$v1){
                    $pr = d()->Propertie->where('id = ?', $v1);
                    $city = $pr->city_id;
                    $products_id = $pr->products_id;
                    foreach ($ar as $key=>$value){
                        if($key=='city_id')$value = $city;
                        if($key=='products_id')$value = $products_id;
                        $pr[$key] = $value;
                    }
                    $pr->save();
                }
            }
        }
    }

    /*$l = d()->Log_product->new;
    $l->type = $type;
    $l->product_id = $element_id;
    $l->save;*/
}

function ajax_get_dopedit_properties_admin(){
    $cities = d()->City;
    d()->cities = Array();
    foreach($cities as $k=>$v){
        d()->cities[$cities->id] = $cities->title;
    }

    d()->properties_list = d()->Propertie->where('id != ?', $_POST['noid'])->order_by('title ASC');
    print d()->ajax_admin_dopedit_properties_tpl();
}

function ajax_find_orders() {
    if($_POST['id']){
        $phone = d()->convert_phone($_POST['id']);
        $orders_id = d()->Order->where('id = ? OR phone = ?', $_POST['id'], $phone)->limit(0,1)->order_by('id DESC');
        /*if($orders_id->is_empty){
            for ($i = date('Y'); $i >= 2020; $i--) {
                $t = 'orders_'.$i;
                $orders_id_l = d()->Order->sql('SELECT * FROM '.$t.' WHERE `id`= "'.$_POST['id'].'" OR `phone`= "'.$phone.'" ORDER BY id desc')->limit(0,1)->to_array();
                //$orders_id = d()->Model($orders_id_l);
                //$orders_id = d()->Order_c($orders_id_l);
                //if(!$orders_id->is_empty) break;
                if($orders_id_l) break;
            }
        }*/
        $str = Array();
        $time = date('Y-m-d');
        $d = strtotime("+1 day");
        $tomorrow = date("Y-m-d", $d);
        if(!$orders_id->is_empty){
            if($orders_id->cook_time == 'now'){
                $s1 = explode(' ', $orders_id->created_at);
                if($s1[0] == $time){
                    $str['id'] = $orders_id->id;
                    $str['name'] = $orders_id->name;
                    $str['phone'] = $orders_id->phone;
                    if($orders_id->street){
                        $str['street'] = $orders_id->street;
                    }else{
                        $str['street'] = 'Самовывоз';
                    }
                    $str['price'] = $orders_id->finish_price;
                    $str['str_f'] = 0;
                    if($orders_id->status == 0 || $orders_id->status == 9 || $orders_id->status == 6){
                        $str['status'] = 'Заказ готовиться';
                        if($orders_id->delivery == 2){
                            $str['str'] = '<strong>Ваш заказ принят и мы начали готовить</strong><br>Доставим Ваш заказ к '.$orders_id->running_order_time;
                            $str['str_f'] = 1;
                        }else{
                            $str['str'] = '<strong>Ваш заказ принят и мы начали готовить</strong>';
                            $str['str_f'] = 1;
                        }
                    }
                    if($orders_id->status == 8){
                        $str['status'] = 'Заказ у курьера';
                        if($orders_id->delivery == 2){
                            $str['str'] = '<strong>Ваш заказ передан курьеру</strong><br>Мы доставим Ваш заказ к '.$orders_id->running_order_time;
                            $str['str_f'] = 2;
                        }
                    }
                    if($orders_id->status == 7){
                        if($orders_id->delivery == 2){
                            $str['status'] = 'Заказ доставлен';
                            $str['str'] = '<strong>Ваш заказ доставлен &#128522;</strong>';
                            $str['str_f'] = 3;
                        }else{
                            $str['status'] = 'Заказ выдан';
                            $str['str'] = '<strong>Ваш заказ выдан &#128522;</strong>';
                            $str['str_f'] = 3;
                        }
                    }
                    if($orders_id->status == 2){
                        $str['status'] = 'Заказ отменен';
                        $str['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                        $str['str_f'] = 5;
                    }
                    if($orders_id->status == 1) $str['status'] = 'Заказ обработан';
                    if($orders_id->status == 10) $str['status'] = 'Не дозвонились';

                    return json_encode($str);
                }else{
                    if($orders_id->status == 7){
                        if($orders_id->delivery == 2){
                            $str['status'] = 'Заказ доставлен';
                            $str['str'] = '<strong>Ваш заказ доставлен &#128522;</strong>';
                            $str['str_f'] = 4;
                        }else{
                            $str['status'] = 'Заказ выдан';
                            $str['str'] = '<strong>Ваш заказ выдан &#128522;</strong>';
                            $str['str_f'] = 4;
                        }
                    }
                    if($orders_id->status == 2){
                        $str['status'] = 'Заказ отменен';
                        $str['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                        $str['str_f'] = 5;
                    }
                    return json_encode($str);
                }
            }else{
                $s2 = explode(' ', $orders_id->cook_time);
                if($s2[0] == $tomorrow || $s2[0] == $time){
                    $str['id'] = $orders_id->id;
                    $str['name'] = $orders_id->name;
                    $str['phone'] = $orders_id->phone;
                    if($orders_id->street){
                        $str['street'] = $orders_id->street;
                    }else{
                        $str['street'] = 'Самовывоз';
                    }
                    $str['price'] = $orders_id->finish_price;
                    $str['str_f'] = 0;
                    if($orders_id->status == 0 || $orders_id->status == 9 || $orders_id->status == 6){
                        $str['status'] = 'Заказ готовиться';
                        if($orders_id->delivery == 2){
                            $str['str'] = '<strong>Ваш заказ принят, мы начнем готовить заказ к '.$orders_id->cook_time.'</strong><br>Доставим Ваш заказ к '.$orders_id->running_order_time;
                            $str['str_f'] = 1;
                        }else{
                            $str['str'] = '<strong>Ваш заказ принят, мы начнем готовить заказ к '.$orders_id->cook_time.'</strong>';
                            $str['str_f'] = 1;
                        }
                    }
                    if($orders_id->status == 8){
                        $str['status'] = 'Заказ у курьера';
                        if($orders_id->delivery == 2){
                            $str['str'] = '<strong>Ваш заказ передан курьеру</strong><br>Мы доставим Ваш заказ к '.$orders_id->running_order_time;
                            $str['str_f'] = 2;
                        }
                    }
                    if($orders_id->status == 7){
                        if($orders_id->delivery == 2){
                            $str['status'] = 'Заказ доставлен';
                            $str['str'] = '<strong>Ваш заказ доставлен &#128522;</strong>';
                            $str['str_f'] = 3;
                        }else{
                            $str['status'] = 'Заказ выдан';
                            $str['str'] = '<strong>Ваш заказ выдан &#128522;</strong>';
                            $str['str_f'] = 3;
                        }
                    }
                    if($orders_id->status == 2){
                        $str['status'] = 'Заказ отменен';
                        $str['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                        $str['str_f'] = 5;
                    }
                    if($orders_id->status == 1) $str['status'] = 'Заказ обработан';
                    if($orders_id->status == 10) $str['status'] = 'Не дозвонились';

                    return json_encode($str);
                }else{
                    if($orders_id->status == 7){
                        if($orders_id->delivery == 2){
                            $str['status'] = 'Заказ доставлен';
                            $str['str'] = '<strong>Ваш заказ доставлен &#128522;</strong>';
                            $str['str_f'] = 4;
                        }else{
                            $str['status'] = 'Заказ выдан';
                            $str['str'] = '<strong>Ваш заказ выдан &#128522;</strong>';
                            $str['str_f'] = 4;
                        }
                    }
                    if($orders_id->status == 2){
                        $str['status'] = 'Заказ отменен';
                        $str['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                        $str['str_f'] = 5;
                    }
                    return json_encode($str);
                }
            }
        }else{
            $text = 'Заказ с таким номером не найден';
            $str['error'] = $text;
            return json_encode($str);
        }
    }
}

function ajax_cancel_orders() {
    if($_POST['id']){
        $phone = d()->convert_phone($_POST['id']);
        $orders_id = d()->Order->where('id = ? OR phone = ?', $_POST['id'], $phone)->limit(0,1)->order_by('id DESC');
        $res = Array();
        if(!$orders_id->is_empty){
            if($orders_id->status == 7){
                $res['error'] = 'Заказ уже доставлен';
                $res['str'] = '<strong>Ваш заказ доставлен &#128522;</strong>';
                $res['str_f'] = 4;
                return json_encode($res);
            }elseif ($orders_id->status != 2){
                $orders_id->status = 2;
                //$orders_id->rejection = $_POST['str'];
                $orders_id->save;

                $cancel = d()->Cancel_order->new;
                $cancel->city_id = $orders_id->city_id;
                $cancel->order_id = $orders_id->id;
                $cancel->reason = $_POST['str'];
                $cancel->save;

                if($_POST['lk']){
                    $res['status'] = 'Отказ';
                }else{
                    $res['status'] = 'Заказ отменен';
                    $res['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                    $res['str_f'] = 5;
                }
                return json_encode($res);
            }else{
                $res['error'] = 'Данный заказ уже отменен';
                $res['str'] = '<strong>Ваш заказ отменен, но мы с радостью ждём вас снова &#128578;</strong>';
                $res['str_f'] = 5;
                return json_encode($res);
            }
        }else{
            $res['error'] = 'Заказ с таким номером не существует';
            return json_encode($res);
        }
    }
}

function get_cancel_orders_1c(){
    get_city();
    if($_GET['key'] != d()->city->key_1c){
        //if($_GET['key'] == d()->city->key_1c){
        get_dates(d()->city);
        get_cart();
        d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);

        d()->page_not_found();
    }

    //$temp = '';
    if($_GET['id']){
        d()->orders_list = d()->Cancel_order->where('order_id = ?', $_GET['id']);
        get_products_options();
        //header ('Content-Type:application/xml; charset=utf-8');
        header("Content-type: text/xml; charset=utf-8");
        print d()->export_cancel_orders_1c_tpl();
    }else{
        $check = d()->Cancel_order->where('city_id = ? AND status = 0', d()->city->id);
        if($check->count){
            $ids = $check->fast_all_of('order_id');
            d()->orders_list = $check;
            //d()->orders_list = d()->Cancel_order->where('order_id IN (?)', $ids);
            get_products_options();
            d()->Cancel_order->sql('UPDATE cancel_orders SET status=1 WHERE order_id IN ('.implode(',', $ids).')');

            header("Content-type: text/xml; charset=utf-8");
            print d()->export_cancel_orders_1c_tpl();
        }
    }
}

function check_for_number($str) {
    $i = strlen($str);
    while ($i--) {
        if (is_numeric($str[$i])) return true;
    }
    return false;
}

function ajax_check_clinetID() {
    if($_SESSION['auth']){
        get_city();
        $user_id = $_SESSION['auth'];
        $u = d()->User($user_id);
        if($_POST['clientid']) $clinentid = $_POST['clientid'];
        $analytic = d()->Export_analytic->new;
        $analytic->clientid = $clinentid;
        $analytic->user_id = $user_id;
        $analytic->user_phone = $u->phone;
        $analytic->user_name = $u->name;
        $analytic->user_created_at = $u->created_at;
        $analytic->user_update_at = $u->updated_at;
        $analytic->city_id = d()->city->id;
        $analytic->save;
    }
}

function ajax_get_promo_sales_products_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }

    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = str_replace('|', '', $v);
            $id = explode('_', $a);
            d()->products[$a][0] = $a;
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$id[0].'|';
        }
        $sortline .= 'sort ASC';
    }

    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=? AND is_active=1', d()->city->id);
//    get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=? AND is_active=1', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_sales_products_tpl();
}

function ajax_wt_modal(){
    if($_POST['flag_wt']){
        get_city();
        get_dates(d()->city);
        $wt = [
            'bukva' => d()->bukva,
            'f_week_day' => d()->f_week_day,
            'f_worktime' => d()->f_worktime,
        ];
        $str = json_encode($wt);
        return $str;
    }
}

function ajax_stop_cause(){
    if($_POST['flag_sc'] == 1){
        get_city();
        $sc = [
            'text' => d()->city->stop_cause,
        ];
        $str = json_encode($sc);
        return $str;
    }elseif($_POST['flag_sc'] == 2){
        $_SESSION['info'] = 1;
        get_city();
        $sc = [
            'text' => d()->city->info_cause,
        ];
        $str = json_encode($sc);
        return $str;
    }
}

function ajax_check_details_order_conf(){
    if($_POST['code'] && $_POST['phone']){
        $r = Array();
        $ph = d()->convert_phone($_POST['phone']);
        $code = $_SESSION['order_details'][$ph]['code'];
        if(!$code){
            $c = d()->Code->where('phone = ?', $ph)->order_by('id DESC')->limit(0,1);
            $code = $c->code;
            if($c->is_empty){
                // логируем косяк
                $l = d()->Log->new;
                $l->title = 'noregphone2';
                $l->text = json_encode($_SESSION);
                $l->save;
            }
        }
        $post_code = str_replace(' ', '', $_POST['code']);

        if($post_code == $code){
            $r['result'] = 1;
            $_SESSION['order_details'][$ph]['result'] = 1;
        }else{
            $r['result'] = 0;
            $r['text'] = 'код подтверждения введен неверно';
        }
        //$r['post_code'] = $post_code;
        //$r['session_code'] = $code;
        print json_encode($r);
        exit;
    }
    d()->page_not_found();
}

function ajax_check_cancels_order_conf(){
    if($_POST['code'] && $_POST['phone']){
        $r = Array();
        $ph = d()->convert_phone($_POST['phone']);
        $code = $_SESSION['order_cancel'][$ph]['code'];
        if(!$code){
            $c = d()->Code->where('phone = ?', $ph)->order_by('id DESC')->limit(0,1);
            $code = $c->code;
            if($c->is_empty){
                // логируем косяк
                $l = d()->Log->new;
                $l->title = 'noregphone2';
                $l->text = json_encode($_SESSION);
                $l->save;
            }
        }
        $post_code = str_replace(' ', '', $_POST['code']);

        if($post_code == $code){
            $r['result'] = 1;
            $_SESSION['order_cancel'][$ph]['result'] = 1;
        }else{
            $r['result'] = 0;
            $r['text'] = 'код подтверждения введен неверно';
        }
        //$r['post_code'] = $post_code;
        //$r['session_code'] = $code;
        print json_encode($r);
        exit;
    }
    d()->page_not_found();
}

function ajax_get_gift_cash_products_admin(){
    if($_POST['city_id']){
        d()->city = d()->City($_POST['city_id']);
    }else{
        get_city();
    }

    $sortline = 'sort ASC';
    if($_POST['products']){
        $p = explode(',', $_POST['products']);
        d()->products = Array();
        $sortline = '';
        d()->picked_id = '';
        foreach($p as $k=>$v){
            $a = str_replace('|', '', $v);
            $id = explode('_', $a);
            d()->products[$a][0] = $a;
            $sortline .= 'id='.$id[0].' DESC, ';
            d()->picked_id .= '|'.$id[0].'|';
        }
        $sortline .= 'sort ASC';
        //$_SESSION['debug']['check'] = 1;
    }


    d()->sortline = $sortline;
    d()->categories_list = d()->Category->where('city_id=?', d()->city->id);
    //get_products_options();
    get_products_options_admin();
    d()->products_list = d()->Product->where('city_id=?', d()->city->id)->order_by($sortline);

    print d()->ajax_admin_gift_cash_products_tpl();
}

function autoadd_gift_cash($cart = Array(), $type = ''){
    $in_cart = 0;
    foreach ($_SESSION['cart'] as $k=>$v){
        if($_SESSION['cart'][$k]['property'] == 'gift_cash') $in_cart = 1;
    }
    if($in_cart == 1 && $type == 'add_change'){
        return $cart;
    }
    if($in_cart == 1 && $_POST['type'] == 'add'){
        return;
    }

    /*if(d()->city->is_empty)*/ get_city();
    if(d()->city->is_cash == 1){
        $gift_cash = d()->city->g_cash;

        $c1 = trim($gift_cash, '|');
        $c2 = explode('_', $c1);

        $pid = $c1.'_gift_cash_0';

        $p = d()->Product($c2[0])->where('is_stop=0');
        $mpt = '<i class="free-to-order">подарок за оплату наличными (при заказе от '.d()->city->min_gift_cash.' руб.)</i>';
        $_property = 'gift_cash';
        if($c2[1]){
            $property = d()->Property($c2[1])->where('is_stop=0');
            //$_property = $property->id;
            if($property->title){
                $property_title = $property->title;
            }
        }
        $t_price = 0;
        $t_promo = 0;
        foreach ($cart as $k_cart=>$v_cart){
            $t_price += $cart[$k_cart]['total_price'];
            $t_promo += $cart[$k_cart]['total_promo_discount'];
        }
        $total_price = $t_price-$t_promo;
        if($_SESSION['points']) $total_price -= $_SESSION['points'];
        if($type == 'add_change' && $total_price >= d()->city->min_gift_cash && $_SESSION['order_info']['pay'] == 'pay_1' || $type == 'add_change' && $total_price >= d()->city->min_gift_cash && !$_SESSION['order_info']['pay']){
            // добавляем новый
            $cart[$pid] = Array(
                'id' => $p->id,
                'id_1c' => $p->id_1c,
                'count' => 1,
                'title' => $p->title,
                'property_title' => $property_title,
                'promo_title' => $mpt,
                'property' => $_property,
                'price' => 0,
                'total_price' => 0,
                'pickup_discount' => 0,
                'total_pickup_discount' => 0,
                'image' => d()->preview($p->image, '120', '120'),
                'promocode' => '',
                'gift_property' => $c2[1],
                'autoadd' => $p->autoadd_products,
                'tableware' => $p->tableware
            );
        }
        $t2_price = 0;
        $t2_promo = 0;
        foreach ($_SESSION['cart'] as $k_cart=>$v_cart){
            $t2_price += $_SESSION['cart'][$k_cart]['total_price'];
            $t2_promo += $_SESSION['cart'][$k_cart]['total_promo_discount'];
        }
        $all_total_price = $t2_price-$t2_promo;
        if($t2_promo == 0 && $_SESSION['promocode']) $all_total_price = $t2_price-$_SESSION['promocode']['value'];
        if($_SESSION['points']) $all_total_price -= $_SESSION['points'];
        if($_POST['type'] == 'add' && $all_total_price >= d()->city->min_gift_cash && $_POST['flag_gift'] == 1 && $_POST['pay'] == 'pay_1' || $_POST['type'] == 'add' && $all_total_price >= d()->city->min_gift_cash && $_POST['flag_gift'] == 1 && !$_SESSION['order_info']['pay'] || $_POST['type'] == 'add' && $all_total_price >= d()->city->min_gift_cash && $_POST['flag_gift'] == 1 && $_SESSION['order_info']['pay'] == 'pay_1'){
            // добавляем новый
            $_SESSION['cart'][$pid] = Array(
                'id' => $p->id,
                'id_1c' => $p->id_1c,
                'count' => 1,
                'title' => $p->title,
                'property_title' => $property_title,
                'promo_title' => $mpt,
                'property' => $_property,
                'price' => 0,
                'total_price' => 0,
                'pickup_discount' => 0,
                'total_pickup_discount' => 0,
                'image' => d()->preview($p->image, '120', '120'),
                'promocode' => '',
                'gift_property' => $c2[1],
                'autoadd' => $p->autoadd_products,
                'tableware' => $p->tableware
            );
            return json_encode($_SESSION['cart'][$pid]);
        }
        if($type == 'minus_change' && $total_price < d()->city->min_gift_cash){
            unset($cart[$pid]);
        }
        if($type == 'delete_change'){
            $cnt = 0;
            foreach ($cart as $k2=>$v2){
                $cnt += $cart[$k2]['count'];
            }
            if($cnt == 0){
                unset($cart[$pid]);
            }
        }
        if($_POST['type'] == 'delete' && $_POST['flag_gift'] == 1 && $all_total_price < d()->city->min_gift_cash || $_POST['type'] == 'delete' && $_POST['flag_gift'] == 2){
            unset($_SESSION['cart'][$pid]);
            return $pid;
        }

        return $cart;
    }else{
        return $cart;
    }
}
