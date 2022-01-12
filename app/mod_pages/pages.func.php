<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class PagesController
{
	function show()
	{
		$url = url(1);
		d()->this = d()->Page->find_by_url($url)->where('city_id=?', d()->city->id);

        if(d()->this->is_empty || url(2)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
        }
        d()->slides_list = d()->Slide->where('city_id=? AND is_hide_site != 1', d()->city->id);
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = d()->this->title;
	}

    function menu()
    {
        $url = url(1);
        d()->this = d()->Page->find_by_url($url)->where('city_id=?', d()->city->id);
        if(d()->this->is_empty || url(2)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = d()->this->title;
    }

	function search()
	{
        $url = url(1);
        d()->this = d()->Page->find_by_url($url)->where('city_id=?', d()->city->id);
        if(d()->this->is_empty || url(2)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
        }
	    // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = d()->this->title;

        // название каталога для электронной торговли
        d()->ec_list = "Поиск";
        $_SESSION['ec_list'] = "Поиск";

        if($_GET['query']){

            // исправление ошибок от Яндекса
            d()->ecr_query = str_replace("'", "", htmlspecialchars($_GET['query']));
            $checker = json_decode(file_get_contents("http://speller.yandex.net/services/spellservice.json/checkText?text=".urlencode(d()->nq($_GET['query']))));
            $checked_str = d()->nq($_GET['query']);
            foreach($checker as $word) {
                $checked_str = str_replace($word->word,$word->s[0],$checked_str);
            }
            if(mb_strtolower($checked_str,'utf8')!=mb_strtolower($_GET['query'],'utf8') && !empty($checked_str)) {
                $checked_str = htmlspecialchars($checked_str);
                d()->speller = '<em>Возможно вы имели ввиду: <a href="/search?query='.$checked_str.'">'.$checked_str.'</a></em>';
            }

            if(!d()->speller){
                d()->mnmp = 'minmargin';
            }

            // выделяем корни
            $stemming = new Stemming();
            $stem_str = $stemming->stem_string($_GET['query']);

            $findkey = trim($stem_str);
            $findkey = htmlspecialchars($findkey);

            d()->findkey = htmlspecialchars(trim($_GET['query']));

            $keyarr =  explode(' ', $findkey);

            // дополнительные массивы (обязательно перед каждой выборкой товаров)
            get_products_options();
            // если запрос состоит из 2х и более слов
            if(count($keyarr)>1){
                d()->products_list = d()->Product->where('city_id = ? AND is_active=1 AND is_stop=0', d()->city->id)->search('title', d()->findkey);
                if(!d()->products_list->count){
                    foreach($keyarr as $key=>$val){
                        d()->products_list = d()->products_list->plus(d()->Product->where('city_id = ? AND is_active=1 AND is_stop=0', d()->city->id)->search('title', d()->nq($val)));
                    }
                }
            }else{
                $x = d()->nq(trim($stem_str));
                d()->products_list = d()->Product->where('city_id = ? AND is_active=1 AND is_stop=0', d()->city->id)->search('title', $x);
            }

            if(!d()->products_list->count){
                d()->noresult = '<p class="no-results"><em>Поиск не дал результатов...</em></p>';
            }

        }else{
            d()->noresult = '<p class="no-results"><em>Поиск не дал результатов...</em></p>';
        }

	}
	
	function index()
	{
        //halloween
        //d()->hlhide = '';

        if(url(1)!='ajax' && url(1)!='index'){
            d()->page_not_found();
        }

	    if(url(1)=='ajax'){
            get_city();
        }
        // название каталога для электронной торговли
        d()->ec_list = "Популярное (главная)";
        $_SESSION['ec_list'] = "Популярное (главная)";
        // дополнительные массивы (обязательно перед каждой выборкой товаров)
        get_products_options();
		d()->products_list = d()->Product->where('is_active=1 AND city_id=? AND is_stop=0', d()->city->id)->limit(0,8)->order_by('is_index desc');

		d()->this = d()->Page->where('city_id=? AND url="index"', d()->city->id);

	    d()->slides_list = d()->Slide->where('city_id=? AND is_active=1 AND is_hide_site != 1 AND category_id LIKE "%|9999|%"', d()->city->id);
	}

	function reviews()
	{
        if($_POST['text'] && $_POST['contact']){
            header('Location: /reviews/?action=writeguide');
            exit();
        }
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='otzyvy' || url(2)!='index'){
            d()->page_not_found();
        }
	    // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Отзывы<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Отзывы';

        d()->r_active = 'active';
        d()->g_active = '';
        d()->r_none = '';
        d()->g_none = 'none';

        if($_GET['show']=='guid'){
            d()->r_active = '';
            d()->g_active = 'active';
            d()->r_none = 'none';
            d()->g_none = '';
        }

        get_city();
        d()->reviews_vk = d()->city->reviews_vk;
        d()->reviews_telegram = d()->city->reviews_telegram;
        d()->reviews_viber = d()->city->reviews_viber;
        d()->reviews_whatsapp = d()->city->reviews_whatsapp;
        d()->reviews_tel = d()->city->phone;

        $_SESSION['dbg2'] = d()->city->id;
        d()->options = d()->Option->where('city_id=?', d()->city->id);
        $_SESSION['dbg1'] = d()->options;
        d()->rewiews_flamp = d()->options->reviews_flamp;
        d()->rewiews_ya_map = d()->options->reviews_ya_map;

        if(d()->options->is_flamp == 1){
            d()->flamp_active = 'reviews-items-active';
            d()->yamap_active = '';
            d()->flamp_none = '';
            d()->yamap_none = 'none';
        }else{
            d()->yamap_active = 'reviews-items-active';
            d()->yamap_none = '';
        }
	}

    function oferta(){

        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='publichnaya-oferta' || url(2)!='index'){
                d()->page_not_found();
            }
        }

        d()->this = d()->Document(1);
        if(d()->this->is_empty){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Публичная оферта<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Публичная оферта';
        print d()->document_tpl();
    }

    function requisites(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='requisites' || url(2)!='index'){
                d()->page_not_found();
            }
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Реквизиты компании<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Реквизиты компании';
        print d()->requisites_tpl();
    }

    function personal(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='personal' || url(2)!='index'){
                d()->page_not_found();
            }
        }

        d()->this = d()->Document(2);
        if(d()->this->is_empty){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Обработка персональных данных<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Обработка персональных данных';
        print d()->document_tpl();
    }

    function reviews_application(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='reviews-application-form' || url(2)!='index'){
            d()->page_not_found();
        }


        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Заявка на начисление баллов за отзыв<meta itemprop="position" content="2"></span></li>';

        if(!d()->Seo->title)d()->Seo->title = 'Заявка на начисление баллов за отзыв';
        d()->ph = d()->user->phone_not_seven;
        if($_POST){
            d()->ph = $_POST['phone'];
            if($_POST['flamp'] || $_POST['zoon'] || $_POST['otzovik'] || $_POST['yandex'] || $_POST['google'] || $_POST['2gis']){
                if($_POST['order_date']){
                    if($_POST['phone']){
                        $phone = d()->convert_phone($_POST['phone']);
                        $u = d()->User->where('phone = ? AND city = ?', $phone, d()->city->code);
                        if(!$u->is_empty){
                            $sites = '';
                            $points = 0;
                            $l_subject = 'Заявка на начисление баллов: '.$_SERVER['SERVER_NAME'];
                            $l_text = '<p><strong>Город:</strong> '.d()->city->title.'<br><strong>Номер чека:</strong> '.$_POST['check_number'].'<br><strong>Дата заказа:</strong> '.$_POST['order_date'].'<br><strong>Номер телефона:</strong> '.$_POST['phone'].'</p>';
                            $l_text .= '<p><strong>Сайты на которых оставлен отзыв:</strong></p>';
                            $l_text .= '<ul>';
                            if($_POST['flamp']){
                                $l_text .= '<li>Flamp</li>';
                                $sites .= 'Flamp, ';
                                $points += 25;
                            }
                            if($_POST['zoon']){
                                $l_text .= '<li>Zoon</li>';
                                $sites .= 'Zoon, ';
                                $points += 25;
                            }
                            if($_POST['otzovik']){
                                $l_text .= '<li>Otzovik</li>';
                                $sites .= 'Otzovik, ';
                                if(d()->city->id == 1){
                                    $points += 100;
                                }else{
                                    $points += 25;
                                }
                            }
                            if($_POST['yandex']){
                                $l_text .= '<li>Яндекс Карты</li>';
                                $sites .= 'Яндекс Карты, ';
                                $points += 25;
                            }
                            if($_POST['google']){
                                $l_text .= '<li>Гугл мой бизнес</li>';
                                $sites .= 'Гугл мой бизнес, ';
                                $points += 25;
                            }
                            if($_POST['2gis']){
                                $l_text .= '<li>2ГИС</li>';
                                $sites .= '2ГИС, ';
                                $points += 25;
                            }
                            if($_POST['irecommend']){
                                $l_text .= '<li>Irecommend</li>';
                                $sites .= 'Irecommend, ';
                                $points += 25;
                            }
                            $l_text .= '</ul>';
                            $sites = substr($sites,0,-2);

                            $e = explode(',', d()->city->email_application);
                            $max_points = d()->city->points_max_comment;
                            $user_points = d()->Reviews_application->where('phone=? AND text != ""', $phone);
                            $all_points = 0;
                            if(!$user_points->is_empty) {
                                foreach ($user_points as $k_user => $v_user) {
                                    $all_points += $user_points->points;
                                }
                            }
                            if($all_points <= $max_points)
                            {
                                $col_points = $max_points-$all_points;
                                if($points <= $col_points)
                                {
                                    foreach($e as $email){
                                        d()->Mail->to(trim($email));
                                        d()->Mail->set_smtp(d()->city->smtp_server,d()->city->smtp_port,d()->city->smtp_mail,d()->city->smtp_password,d()->city->smtp_protocol);
                                        d()->Mail->from(d()->city->smtp_mfrom,d()->city->smtp_tfrom);
                                        d()->Mail->subject($l_subject);
                                        d()->Mail->message($l_text);
                                        d()->Mail->send();
                                    }

                                    $a = d()->Reviews_application->new;
                                    $a->city_id = d()->city->id;
                                    $a->user_id = $u->id;
                                    $a->phone = $phone;
                                    $a->text = $sites;
                                    $a->check_number = $_POST['check_number'];
                                    $a->order_date = $_POST['order_date'];
                                    $a->points = $points;
                                    $a->save;

                                    header('Location: /reviews-application-form/?action=send');
                                    exit;
                                }else{
                                    d()->error = '<div class="alert alert-danger ">Баллы за отзывы можно получить только 1 раз</div>';
                                }
                            }else{
                                d()->error = '<div class="alert alert-danger ">Вы уже получили максимальное количество баллов за отзывы</div>';
                            }
                        }else{
                            d()->error = '<div class="alert alert-danger ">Пользователь с таким телефоном не зарегистрирован</div>';
                        }
                    }else{
                        d()->error = '<div class="alert alert-danger ">Необходимо указать номер телефона с которого вы делали заказ</div>';
                    }
                }else{
                    d()->error = '<div class="alert alert-danger ">Необходимо указать дату Вашего заказа</div>';
                }
            }else{
                d()->error = '<div class="alert alert-danger ">Необходимо выбрать сайт на котором Вы оставили отзыв</div>';
            }
        }
    }

    function quiz_quarantine(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='quiz/quarantine/index'){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Кто ты на карантине?<meta itemprop="position" content="2"></span></li>';
        d()->show_textback = 0;

        if(!d()->Seo->title)d()->Seo->title = 'Кто ты на карантине?';
    }

    function quiz_history_rolls(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='quiz/history_rolls/index'){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Как хорошо ты знаешь историю роллов?<meta itemprop="position" content="2"></span></li>';
        d()->show_textback = 0;
        if(!d()->Seo->title)d()->Seo->title = 'Как хорошо ты знаешь историю роллов?';
    }

    function quiz(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='quiz/index'){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Какой ты ролл?<meta itemprop="position" content="2"></span></li>';
        d()->show_textback = 0;
        if(!d()->Seo->title)d()->Seo->title = 'Какой ты ролл?';
    }

    function quiz_city_history(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='quiz/city_history/index'){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Как хорошо ты знаешь историю города?<meta itemprop="position" content="2"></span></li>';
        d()->show_textback = 0;
        if(!d()->Seo->title)d()->Seo->title = 'Как хорошо ты знаешь историю города?';
    }

    function quiz_erudition(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='quiz/erudition/index'){
            d()->page_not_found();
        }

        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Достаточно ли вы эрудированы?<meta itemprop="position" content="2"></span></li>';
        d()->show_textback = 0;
        if(!d()->Seo->title)d()->Seo->title = 'Достаточно ли вы эрудированы?';
    }

    function anketa(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='anketa/index'){
            d()->page_not_found();
        }
        get_city();
        if(!$_GET['p']){
            d()->order_id = $_GET['o'];
            if(!d()->order_id){
                d()->order_id = $_COOKIE['last_order'];
            }
            $o = d()->Order(d()->order_id);
            if($o){
                d()->dlv = $o->delivery;
            }
        }else{
            // переводим из 16-ти ричной системы в десятичную
            $phone = hexdec($_GET['p']);
            d()->phone = d()->convert_phone($phone);
        }
        if($_GET['filial']){
            $f = d()->Office->where('1c_id = ?', $_GET['filial']);
            d()->office = $f->id;
        }
    }

    function anketa_finish(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url()!='anketa/finish/index'){
            d()->page_not_found();
        }
        get_city();
        $_SESSION['dbg1'] = $_POST;
        if(
            isset($_POST['rating']) &&
            $_POST['operator-time'] &&
            $_POST['courier-polite'] &&
            $_POST['courier-view'] &&
            $_POST['courier-time'] &&
            $_POST['courier-bag'] &&
            isset($_POST['order-view']) &&
            isset($_POST['order-taste']) ||
            isset($_POST['rating']) &&
            $_POST['operator-time'] &&
            $_POST['kassir-polite'] &&
            $_POST['kassir-view'] &&
            $_POST['kassir-time'] &&
            isset($_POST['order-view']) &&
            isset($_POST['order-taste'])
        ){
            $phone = '';
            $email = '';
            if($_POST['email']){
                $user = d()->User->where('city=? AND email=?', d()->city->code, $_POST['email']);
                $phone = $user->phone;
                $email = $_POST['email'];
            }
            if($_POST['order']){
                $order = d()->Order($_POST['order']);
                $phone = $order->phone;
                $user = d()->User->where('phone=? AND city=?', $phone, d()->city->code);
                $email = $user->email;
            }

            if(!$phone){
                $phone = $_POST['phone'];
            }

            // определяем филиал, если его нет из 1С
            $office = 0;
            if($_POST['office']){
                $office = $_POST['office'];
            }else{
                $order_id = $_COOKIE['last_order'];
                if($_POST['order'])$order_id = $_POST['order'];
                $delivery = 0;
                if(!$order_id && $phone || $order_id == '' && $phone){
                    $o = d()->Order->where('phone = ?', $phone)->order_by('id desc')->limit(0, 1);
                    $order_id = $o->id;
                }
                if($order_id){
                    $o = d()->Order($order_id);
                    if(!$o->is_empty){
                        // если самовывоз, берем выбранный филиал
                        $delivery = $o->delivery;
                        if($o->delivery == 1){
                            $office = $o->office_id;
                        }else{
                            // если доставка
                            //$zt = $_COOKIE['last_zone_title'];
                            $z = explode('.', $o->delivery_zone);
                            $zt = $z[0];
                            if($zt){
                                // проверяем на Казань
                                if($o->city_id == 3){
                                    // если в названии зоны есть 2 или 3, то это Глушко
                                    if(strpos($zt, '2') !== false || strpos($zt, '3') !== false){
                                        $office = 6;
                                    }else{
                                        $office = 3;
                                    }
                                }elseif($o->city_id == 6){
                                    // проверяем на Уфу
                                    // если в названии зоны есть 2 или 3, то это Уфимское шоссе
                                    if(strpos($zt, '2') !== false || strpos($zt, '3') !== false){
                                        $office = 9;
                                    }else{
                                        $office = 7;
                                    }
                                }else{
                                    $office = d()->Office->where('city_id = ? AND is_active = 1', $o->city_id)->id;
                                }
                            }
                        }
                    }
                }else{
                    $delivery = $_POST['delivery'];
                }
            }
            // определяем филиал

            $a = d()->Anket->new;
            $a->city_id = d()->city->id;
            $a->rating = $_POST['rating'];
            //$a->uslugi = $_POST['uslugi'];
            //$a->assortiment = $_POST['assortiment'];
            $a->ideal = $_POST['ideal'];
            $a->company = $_POST['company'];
            $a->prichina = $_POST['prichina'];
            $a->date = strtotime(date('d.m.Y'))+10800;
            $a->email = $email;
            $a->phone = $phone;
            $a->delivery = $delivery;
            if($order_id)$a->order_id = $order_id;
            $a->office_id = $office;

            if(isset($_POST['operator'])){
                $a->operator = $_POST['operator'];
            }else{
                $a->operator = 6;
            }
            if($_POST['online-order']==1){
                $a->online_order = $_POST['online-order'];
            }
            $a->operator_time = $_POST['operator-time'];
            $a->courier_polite = $_POST['courier-polite'];
            $a->courier_view = $_POST['courier-view'];
            $a->courier_time = $_POST['courier-time'];
            $a->courier_bag = $_POST['courier-bag'];
            $a->kassir_polite = $_POST['kassir-polite'];
            $a->kassir_view = $_POST['kassir-view'];
            $a->kassir_time = $_POST['kassir-time'];
            $a->order_view = $_POST['order-view'];
            $a->order_view_desc = $_POST['order-view-desc'];
            $a->order_taste = $_POST['order-taste'];
            $a->order_taste_desc = $_POST['order-taste-desc'];

            $a->save;

            // логируем отправку
            $log = d()->Log->new;
            $log->title = 'Ankets |'.$phone.'|'.d()->city->id;
            $log->text = json_encode($_POST);
            $log->save;

            header('Location: /anketa/finish/?office='.$office.'&rating='.$_POST['rating']);
            exit;
            //print 123;
        }

        d()->info = d()->Option;
        if($_GET['office']){
            d()->office = d()->Office($_GET['office']);
        }
    }

    function partners(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='partners' || url(2)!='index'){
                d()->page_not_found();
            }
        }
        get_city();
        d()->this = d()->Landing_partner->where('city_id=?', d()->city->id)->limit(0,1);
        // навигация
        if(!d()->Seo->title)d()->Seo->title = 'Реклама и партнерство - доствка еды '.d()->city->name;

        d()->ttl = 'WOW! Pizza';
        if(d()->city->title == 'Уфа'){
            d()->ttl = 'Радуга Вкуса Уфа';
        }

	}

    function ts_creator(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='ts-creator' || url(2)!='index'){
                d()->page_not_found();
            }
        }
        if(!$_SESSION['admin']){
            header('Location: /admin?key=kmsne73jxxliYn2szB100ketndjHDn1spc53');
            exit;
        }
        if($_GET['action']=='clear'){
            unset($_SESSION['ts']);
            header('Location: /ts-creator/');
            exit;
        }
        if($_GET['action']=='in_crm'){
            foreach ($_SESSION['ts']['city'] as $k_tcc=>$v_tcc){
                $id_tc = d()->Crm_action->where('id_ts_creator=? AND city_id=?',$_SESSION['ts']['id_ts_creator'], $v_tcc);
                if($id_tc->is_empty) {
                    $str = str_replace('<br>','',$_SESSION['ts']['desc']);
                    $d1 = 0;
                    $d2 = 0;
                    if($_SESSION['ts']['date1'])$d1 = strtotime($_SESSION['ts']['date1']);
                    if($_SESSION['ts']['date2'])$d2 = strtotime($_SESSION['ts']['date2']);
                    $c = d()->Crm_action->new;
                    //$c->city_id = $_SESSION['ts']['city'];
                    $c->city_id = $v_tcc;
                    //$c->title = str_replace('<br>','',$_SESSION['ts']['desc']);
                    $c->title = $str;
                    $c->reklama = $_SESSION['ts']['kanal'];
                    //$d1 = 0;
                    //$d2 = 0;
                    //if($_SESSION['ts']['date1'])$d1 = strtotime($_SESSION['ts']['date1']);
                    //if($_SESSION['ts']['date2'])$d2 = strtotime($_SESSION['ts']['date2']);
                    $c->date1 = $d1;
                    $c->date2 = $d2;
                    $c->usloviya = $_SESSION['ts']['text'];
                    $c->promo = $_SESSION['ts']['promocodes'];
                    $c->id_ts_creator = $_SESSION['ts']['id_ts_creator'];
                    $c->active = 1;
                    $c->save;
                }
            }
            unset($_SESSION['ts']);

            header('Location: /ts-creator/?alert=in_crm');
            exit;
        }

        if($_GET['action']=='history_in_crm'){
            $t = d()->Ts_history($_GET['id']);
            $_SESSION['ts'] = json_decode($t->json, true);
            $_SESSION['ts']['city'] = $t->city_id;
            $_SESSION['ts']['id_ts_creator'] = $t->id_ts_creator;

            header('Location: /ts-creator/?action=in_crm');
            exit;
        }

        if($_GET['action']=='history_edit'){
            $t = d()->Ts_history($_GET['id']);
            $_SESSION['ts'] = json_decode($t->json, true);
            $_SESSION['ts']['city'] = $t->city_id;
            $_SESSION['ts']['id_ts_creator'] = $t->id_ts_creator;

            header('Location: /ts-creator/');
            exit;
        }

        if($_SESSION['ts']['id_ts_creator'] != NULL){
            $id_ts = $_SESSION['ts']['id_ts_creator'];
        }else{
            $id_ts = date("U");
        }

        get_city();
        d()->cities_list = d()->City;
        if(!d()->Seo->title)d()->Seo->title = 'Генератор ТЗ';
        d()->ts = $_SESSION['ts'];
        d()->old_ts_creator = $_SESSION['ts'];
        d()->channels_list = d()->Crm_channel_action;

        if($_POST){
            $ar = Array();
            foreach ($_POST as $k_pd=>$v_pd){
                if($_POST[$k_pd] != d()->old_ts_creator[$k_pd]) $ar[$k_pd] = $v_pd;
            }
            foreach ($ar as $k_ar=>$v_ar){
                if($k_ar != 'city') $id_ts = date("U");
            }
            d()->show_result = 1;
            d()->scroll = 1;
            d()->array_city = $_POST['city'];

            foreach ($_POST['city'] as $k_city=>$v_city){


                /*d()->ts_site = 'WOW! Pizza';
                if(d()->domain == 'radugavkusaufa.ru'){
                    d()->ts_site = 'Радуга Вкуса';
                }*/

                d()->maket = $_POST['maket'];
                d()->kanal = d()->Crm_channel_action($_POST['kanal'])->title;

                d()->city_title = d()->City($v_city)->title;
                d()->day = $_POST['day'];

                d()->desc = '';
                //if($_POST['type']==1){
                $items = count($_POST['data'])/8;
                $i = 1;
                $line = Array();
                for($i=1;$i<=$items;$i++){
                    $_POST['data']['product'.$i] = trim(preg_replace('/\[.*?\]/', '', $_POST['data']['product'.$i]));
                    $w = '';
                    if($_POST['data']['weight'.$i])$w = ' '.$_POST['data']['weight'.$i].', ';
                    $c = '';
                    if($_POST['data']['count'.$i])$c = ' '.$_POST['data']['count'.$i].' шт., ';
                    $op = '';
                    if($_POST['data']['old_price'.$i])$op = ' (<s>'.$_POST['data']['old_price'.$i].' руб</s>)';
                    $p = '';
                    if($_POST['data']['price'.$i])$p = ' '.$_POST['data']['price'.$i].' руб';
                    $v = '';
                    if($_POST['data']['profit'.$i])$v = ' выг '.$_POST['data']['profit'.$i].' руб ';
                    $promo = '';
                    if($_POST['data']['promo'.$i])$promo = ': ПР.'.$_POST['data']['promo'.$i].' ';

                    if($op && $v){
                        $op .= ',';
                    }
                    if(!$op && $v){
                        $p .= ',';
                    }

                    $l = $_POST['data']['str'.$i];
                    if(!$l)$l = 0;
                    if($line[$l])$line[$l] .= ', ';
                    $line[$l] .= str_replace('"','',$_POST['data']['product'.$i]).$w.$c.$p.$op.$v.$promo;
                }
                foreach($line as $v){
                    d()->desc .= $v.'; <br>';
                }

                d()->date1 = $_POST['date1'];
                d()->date2 = $_POST['date2'];
                d()->promocode = '<p><strong>Промокод: '.$_POST['promocodes'].'</strong></p>';
                d()->usl = '<p>'.$_POST['text'].'</p>';
                $_SESSION['ts'] = $_POST;
                //$_SESSION['ts']['city'] = $v_city;
                $_SESSION['ts']['desc'] = d()->desc;
                $_SESSION['ts']['id_ts_creator'] = $id_ts;
                d()->ts = $_SESSION['ts'];


                $text1 = '<p><strong>'.d()->ts_site.',</strong> '.d()->maket.'</p><p><strong>'.d()->kanal.' '.d()->city_title.' на '.d()->day.'</strong></p>'.d()->desc.d()->promocode;
                if(d()->date1 && d()->date2){
                    $text2 = '<p><strong>'.d()->kanal.' '.d()->city_title.' на '.d()->day.'</strong></p><p>Период действия: с '.d()->date1.' до '.d()->date2.'</p>'.d()->desc.d()->promocode.d()->usl;
                }else{
                    if(d()->date1 && !d()->date2){
                        $text2 = '<p><strong>'.d()->kanal.' '.d()->city_title.' на '.d()->day.'</strong></p><p>Период действия: с '.d()->date1.'</p>'.d()->desc.d()->promocode.d()->usl;
                    }else{
                        if(!d()->date1 && d()->date2){
                            $text2 = '<p><strong>'.d()->kanal.' '.d()->city_title.' на '.d()->day.'</strong></p><p>Период действия: до '.d()->date2.'</p>'.d()->desc.d()->promocode.d()->usl;

                        }else{
                            $text2 = '<p><strong>'.d()->kanal.' '.d()->city_title.' на '.d()->day.'</strong></p>'.d()->desc.d()->promocode.d()->usl;
                        }
                    }
                }

                $h = d()->Ts_history->new;
                $h->title = date('d.m.Y H:i');
                $h->text = $text1;
                $h->text2 = $text2;
                $h->post = json_decode($_POST);
                //$h->city_id = $_SESSION['ts']['city'];
                $h->city_id = $v_city;
                $h->id_ts_creator = $_SESSION['ts']['id_ts_creator'];
                $h->json = json_encode($_SESSION['ts']);
                $h->save;
            }
        }
    }

    function photo_reviews_application()
    {
        if (substr(url(), -6) != '/index') {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /' . url() . '/');
            exit;
        }
        if (url(1) != 'photo-reviews-application-form' || url(2) != 'index') {
            d()->page_not_found();
        }


        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Заявка на начисление баллов за отзыв<meta itemprop="position" content="2"></span></li>';

        if (!d()->Seo->title) d()->Seo->title = 'Заявка на участие в фотоконкурсе';
        d()->ph = d()->user->phone_not_seven;
        if ($_POST) {
            $_SESSION['POST_FOTO'] = $_POST;
            d()->ph = $_POST['phone'];
            if ($_POST['check_link']) {
                if ($_POST['order_date']){
                    if ($_POST['phone']){
                        $phone = d()->convert_phone($_POST['phone']);
                        $u = d()->User->where('phone = ? AND city = ?', $phone, d()->city->code);
                        if (!$u->is_empty) {
                            $sites = '';
                            $points = 0;
                            $max_points = d()->city->points_photo_reviews;
                            $l_subject = 'Заявка на участие в фотоконкурсе: ' . $_SERVER['SERVER_NAME'];
                            $l_text = '<p><strong>Город:</strong> ' . d()->city->title . '<br><strong>Номер чека:</strong> ' . $_POST['check_number'] . '<br><strong>Дата заказа:</strong> ' . $_POST['order_date'] . '<br><strong>Номер телефона:</strong> ' . $_POST['phone'] . '</p>';
                            $l_text .= '<p><strong>Ссылка на публикацию поста:</strong> ' . $_POST['check_link'] . ' </p>';
                            $points = $max_points;
                            $sites = substr($sites, 0, -2);

                            $e = explode(',', d()->city->email_application);
                            $user_points = d()->Reviews_application->where('phone=? AND photo_reviews!=""', $phone)->to_array();
                            $_SESSION['$user_points'] = $user_points;
                            if (!$user_points) {
                                foreach ($e as $email) {
                                    d()->Mail->to(trim($email));
                                    d()->Mail->set_smtp(d()->city->smtp_server, d()->city->smtp_port, d()->city->smtp_mail, d()->city->smtp_password, d()->city->smtp_protocol);
                                    d()->Mail->from(d()->city->smtp_mfrom, d()->city->smtp_tfrom);
                                    d()->Mail->subject($l_subject);
                                    d()->Mail->message($l_text);
                                    d()->Mail->send();
                                }

                                $a = d()->Reviews_application->new;
                                $a->city_id = d()->city->id;
                                $a->user_id = $u->id;
                                $a->phone = $phone;
                                $a->photo_reviews = $_POST['check_link'];
                                $a->check_number = $_POST['check_number'];
                                $a->order_date = $_POST['order_date'];
                                $a->points = $points;
                                $a->save;

                                header('Location: /photo-reviews-application-form/?action=send');
                                exit;
                            } else {
                                foreach ($user_points as $k_up=>$v_up){
                                    $_SESSION['status'] = $v_up['status'];
                                    if($v_up['status'] != 1){
                                        d()->error = '<div class="alert alert-danger ">У вас уже есть активная заявка на получение баллов</div>';
                                    }else{
                                        d()->error = '<div class="alert alert-danger ">Вы уже получили максимальное количество баллов за участие в фотоконкурсе</div>';
                                    }
                                }
                            }
                        } else {
                            d()->error = '<div class="alert alert-danger ">Пользователь с таким телефоном не зарегистрирован</div>';
                        }
                    } else {
                        d()->error = '<div class="alert alert-danger ">Необходимо указать номер телефона с которого вы делали заказ</div>';
                    }
                } else {
                    d()->error = '<div class="alert alert-danger ">Необходимо указать дату Вашего заказа</div>';
                }
            } else {
                d()->error = '<div class="alert alert-danger ">Необходимо указать ссылку на Вашу публикацию</div>';
            }
        }
    }

    function favorites(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='favorites' || url(2)!='index'){
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Избранное<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Избранное';


        if($_SESSION['auth']){
            $u = d()->User($_SESSION['auth']);
            $str = trim($u->likes, ',');
            $likes = explode(',', $str);
            if(count($likes)) d()->likes_list = d()->Product($likes);
            get_products_options(d()->likes_list->category_id);
        }else{
            if($_COOKIE['likes']){
                $str = trim($_COOKIE['likes'], ',');
                $likes = explode(',', $str);
                if(count($likes)) d()->likes_list = d()->Product($likes);
                get_products_options(d()->likes_list->category_id);
            }
        }
    }

    function about(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='about' || url(2)!='index'){
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">О нас<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'О нас';

        d()->this = d()->About;
    }

    function franchise(){
        d()->page_not_found();
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='ajax_page'){
            if(url(1)!='franchise' || url(2)!='index'){
                d()->page_not_found();
            }
        }
        get_city();
        //d()->this = d()->Landing_partner->where('city_id=?', d()->city->id)->limit(0,1);
        // навигация
        if(!d()->Seo->title)d()->Seo->title = 'Инвестируй в кафе доставки '.d()->city->name;

        d()->ttl = 'WOW! Pizza';
        if(d()->city->title == 'Уфа'){
            d()->ttl = 'Радуга Вкуса Уфа';
        }
    }

    function tgbot(){
        d()->get_city();
        d()->option = d()->Option->where('city_id = ?', d()->city->id);
	    if($_GET['method']=='auth'){
            header("Content-type: application/json");
            $r = Array();
            $post = json_decode(file_get_contents('php://input'), true);
            $token = d()->getBearerToken();
            if($token != d()->option->tgbot_authorization_token){
                $r['status'] = 'error';
                $r['code'] = 'incorrect authorization token';
                $r['error_text'] = 'Неверный Authorization token';
                print json_encode($r);
                exit;
            }

	        if($post['phone']){
	            $e = d()->Code->where('phone = ?', $post['phone'])->order_by('id desc')->limit(0,1);
	            if($e->is_empty){
	                $r['status'] = 'error';
                    $r['code'] = 'code not found';
                    $r['error_text'] = 'Код подтверждения не найден';
                    print json_encode($r);
                    exit;
                }
	            $data = Array();
	            $data['code'] = $e->code;
                $r['status'] = 'ok';
                $r['data'] = $data;
                print json_encode($r);
                exit;
            }
            $r['status'] = 'error';
            $r['code'] = 'phone is empty';
            $r['error_text'] = 'Отсутствует телефон';
            print json_encode($r);
            exit;
        }

	    if($_GET['method']=='register '){
            header("Content-type: application/json");
            $r = Array();
            $post = json_decode(file_get_contents('php://input'), true);
            $token = d()->getBearerToken();
            if($token != d()->option->tgbot_authorization_token){
                $r['status'] = 'error';
                $r['code'] = 'incorrect authorization token';
                $r['error_text'] = 'Неверный Authorization token';
                print json_encode($r);
                exit;
            }

	        /*if($post['phone']){
	            $e = d()->Code->where('phone = ?', $post['phone'])->order_by('id desc')->limit(0,1);
	            if($e->is_empty){
	                $r['status'] = 'error';
                    $r['code'] = 'code not found';
                    $r['error_text'] = 'Код подтверждения не найден';
                    print json_encode($r);
                    exit;
                }
	            $data = Array();
	            $data['code'] = $e->code;
                $r['status'] = 'ok';
                $r['data'] = $data;
                print json_encode($r);
                exit;
            }
            $r['status'] = 'error';
            $r['code'] = 'phone is empty';
            $r['error_text'] = 'Отсутствует телефон';
            print json_encode($r);
            exit;*/
        }
        d()->page_not_found();
    }

    function delivery_terms(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='delivery-terms' || url(2)!='index'){
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Условия доставки<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Условия доставки';

        get_city();
        d()->key = d()->city->ya_geo_apikey;
    }

    function faqs(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='faq' || url(2)!='index'){
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Часто задаваемые вопросы<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Часто задаваемые вопросы';

        d()->faqs_list = d()->Faq;
    }

    function orders_status(){
        if(substr(url(), -6)!='/index'){
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }
        if(url(1)!='orders_status' || url(2)!='index'){
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Проверить статус заказа<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Проверить статус заказа';

        d()->order_status = 1;
        $phone = $_SESSION['reg_phone'];
        if($_SESSION['order_details'][$phone]['result']){
            d()->details_result = 1;
        }
    }

    function app_mobile_reviews_application(){
        if (substr(url(), -6) != '/index') {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /' . url() . '/');
            exit;
        }
        if (url(1) != 'app-mobile-reviews-application-form' || url(2) != 'index') {
            d()->page_not_found();
        }


        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Заявка на начисление баллов за мобильное приложение<meta itemprop="position" content="2"></span></li>';

        if (!d()->Seo->title) d()->Seo->title = 'Заявка на начисление баллов за мобильное приложение';
        d()->ph = d()->user->phone_not_seven;
        if ($_POST) {
            $_SESSION['app_mobile'] = $_POST;
            d()->ph = $_POST['phone'];
            if ($_POST['app_mobile']) {
                if ($_POST['order_date']){
                    if ($_POST['phone']){
                        $phone = d()->convert_phone($_POST['phone']);
                        $u = d()->User->where('phone = ? AND city = ?', $phone, d()->city->code);
                        if (!$u->is_empty) {
                            $sites = '';
                            $points = 0;
                            $max_points = d()->city->points_app_mobile_reviews;
                            $l_subject = 'Заявка на начисление баллов за мобильное приложение: ' . $_SERVER['SERVER_NAME'];
                            $l_text = '<p><strong>Город:</strong> ' . d()->city->title . '<br><strong>Имя:</strong> ' . $_POST['name'] . '<br><strong>Дата заказа:</strong> ' . $_POST['order_date'] . '<br><strong>Номер телефона:</strong> ' . $_POST['phone'] . '</p>';
                            if($_POST['app_mobile'] == 1){
                                $l_text .= '<p><strong>Мобильное приложение, которое использовали:</strong> App Store </p>';
                            }else{
                                $l_text .= '<p><strong>Мобильное приложение, которое использовали:</strong> Play Market </p>';
                            }
                            $points = $max_points;
                            $sites = substr($sites, 0, -2);

                            $e = explode(',', d()->city->email_application);
                            $user_points = d()->Reviews_application->where('phone=? AND app_mobile_reviews!=""', $phone)->to_array();
                            $_SESSION['$user_points'] = $user_points;
                            if (!$user_points) {
                                foreach ($e as $email) {
                                    d()->Mail->to(trim($email));
                                    d()->Mail->set_smtp(d()->city->smtp_server, d()->city->smtp_port, d()->city->smtp_mail, d()->city->smtp_password, d()->city->smtp_protocol);
                                    d()->Mail->from(d()->city->smtp_mfrom, d()->city->smtp_tfrom);
                                    d()->Mail->subject($l_subject);
                                    d()->Mail->message($l_text);
                                    d()->Mail->send();
                                }

                                $a = d()->Reviews_application->new;
                                $a->city_id = d()->city->id;
                                $a->user_id = $u->id;
                                $a->phone = $phone;
                                $a->app_mobile_reviews = $_POST['app_mobile'];
                                $a->user_name = $_POST['name'];
                                $a->order_date = $_POST['order_date'];
                                $a->points = $points;
                                $a->save;

                                header('Location: /app-mobile-reviews-application-form/?action=send');
                                exit;
                            } else {
                                foreach ($user_points as $k_up=>$v_up){
                                    $_SESSION['status'] = $v_up['status'];
                                    if($v_up['status'] != 1){
                                        d()->error = '<div class="alert alert-danger ">У вас уже есть активная заявка на получение баллов</div>';
                                    }else{
                                        d()->error = '<div class="alert alert-danger ">Вы уже получили максимальное количество баллов за использование мобильного приложения</div>';
                                    }
                                }
                            }
                        } else {
                            d()->error = '<div class="alert alert-danger ">Пользователь с таким телефоном не зарегистрирован</div>';
                        }
                    } else {
                        d()->error = '<div class="alert alert-danger ">Необходимо указать номер телефона с которого вы делали заказ</div>';
                    }
                } else {
                    d()->error = '<div class="alert alert-danger ">Необходимо указать дату Вашего заказа</div>';
                }
            } else {
                d()->error = '<div class="alert alert-danger ">Необходимо выбрать платформу мобильного приложения, которое использовали</div>';
            }
        }
    }

}

