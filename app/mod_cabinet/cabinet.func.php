<?php



/**
* Контролер
*/
class CabinetController
{

	function history()
	{
        d()->auth_guard();
        // скрываем меню блюд
        // d()->hide_menu = 1;
        // active для пункта меню
        d()->history = 'active';
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/cabinet/" itemprop="item"><span itemprop="name">Личный кабинет</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">История заказов<meta itemprop="position" content="3"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'История заказов / Личный кабинет';

        d()->this = d()->Auth->user();
        if(url(1)=='ajax')get_city();

        $cnt = 10;
	    //d()->orders_list = d()->Order->where('city_id=? AND user_id=?', d()->city->id, d()->this->id)->order_by('id desc')->limit(0, $cnt);
	    $or_list = d()->Order->where('city_id=? AND user_id=?', d()->city->id, d()->this->id)->order_by('id desc')->to_array();
	    for($i = 2021; $i <= date('Y'); $i++){
	        $t = 'orders_'.$i;
	        $or_old_list = d()->Check->sql('select * from '.$t.' where city_id="'.d()->city->id.'" and user_id="'.d()->this->id.'" order by id desc')->to_array();
            foreach ($or_old_list as $kor_old=>$vor_old){
                $or_list[] = $vor_old;
            }
        }
	    $all_arr_order = array_slice($or_list, 0, $cnt);
        d()->orders_list = d()->Model($all_arr_order);
        d()->orders_list = d()->Order_c($all_arr_order);

        // кнопка Показать еще
        d()->load_more = g_loadmore('orders', $cnt, $cnt, d()->this->id);

        if(!d()->orders_list->count)d()->no_results = '<p class="no-results">Заказы отсутствуют...</p>';

        $offices = d()->Office;
        d()->office = Array();
        foreach($offices as $v){
            if($offices->netmonet)d()->office[$offices->id] = $offices->netmonet;
        }
        d()->lk_user_phone = d()->this->phone;
	}

	function balance()
	{
        d()->auth_guard();
        // скрываем меню блюд
        // d()->hide_menu = 1;
        // active для пункта меню
        d()->balance = 'active';
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/cabinet/" itemprop="item"><span itemprop="name">Личный кабинет</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Бонусные баллы<meta itemprop="position" content="3"></span></li>';

        if(!d()->Seo->title)d()->Seo->title = 'Бонусные баллы / Личный кабинет';
        if(url(1)=='ajax')get_city();

        d()->this = d()->Auth->user();

        // количество показываемых строк на старте
        $cnt = 8;
        $points_new = d()->Point->where('user_id=?', d()->this->id)->order_by('id desc')->to_array();

        /*for($i = 2021; $i <= date('Y')-1; $i++){
            $t = 'points_'.$i;
            $points_old = d()->Point->sql('select * from '.$t.' where user_id="'.d()->this->id.'" order by id desc')->to_array();
            foreach ($points_old as $kpold=>$vpold){
                $points_new[] = $vpold;
            }
        }*/
        $new_arr_points = array_slice($points_new, 0, $cnt);
        d()->points_list = d()->Model($new_arr_points);
        d()->points_list = d()->Point_m($new_arr_points);

        // кнопка Показать еще
        d()->load_more = g_loadmore('points', $cnt, $cnt, d()->this->id);
	}

	function personal()
	{
	    d()->m_cabinet = "m_cabinet";
        d()->auth_guard();
        get_city();
        d()->key = d()->city->ya_geo_apikey;
        // скрываем меню блюд
        // d()->hide_menu = 1;
        // active для пункта меню
        d()->personal = 'active';
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/cabinet/" itemprop="item"><span itemprop="name">Личный кабинет</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Личные данные<meta itemprop="position" content="3"></span></li>';

        if(!d()->Seo->title)d()->Seo->title = 'Личные данные / Личный кабинет';

        if(url(1)=='ajax')get_city();

	    d()->this = d()->Auth->user();
	    d()->adresses_list = d()->Address->where('user_id=?', d()->this->id);
	    d()->ball_word = declOfNum (d()->this->points, array('балл', 'балла', 'баллов'));

	    if(d()->this->gender=='male') d()->male_select = 'selected';
	    if(d()->this->gender=='female') d()->female_select = 'selected';

	    if(d()->this->haschild=='1') d()->child_select = 'selected';
	    if(d()->this->haschild=='2') d()->nochild_select = 'selected';

	    d()->success_alert = 'none';
	    d()->error_alert = 'none';

	    if($_GET['action']=='add_address'){
	        d()->success_message = 'Адрес успешно добавлен';
            d()->success_alert = '';
        }
	    if($_GET['action']=='edit_address'){
	        d()->success_message = 'Адрес успешно отредактирован';
            d()->success_alert = '';
        }
	}

}

