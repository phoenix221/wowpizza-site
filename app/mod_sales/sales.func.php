<?php



/**
* Контролер
*/
class SalesController
{



	/**
	* Отображение элемента
	*/
	function show()
	{
        d()->actsales = 'actsales';
		//$url = url(2);
        $u = explode('/', $_SERVER['REQUEST_URI']);
        $url = str_replace('?','',str_replace($_SERVER['QUERY_STRING'], '', $u[2]));

        d()->this = d()->Sale($url)->where('is_active = 1 and city_id=?', d()->city->id);
        if(d()->this->is_empty || url(3)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
            exit;
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/sales/" itemprop="item"><span itemprop="name">Акции</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="3"></span></li>';

        if(d()->this->products){
            $p = explode(',', d()->this->products);
            $pr = Array();
            foreach($p as $v){
                $id = explode('_', $v);
                $pr[] = $id[0];
            }
            // дополнительные массивы (обязательно перед каждой выборкой товаров)
            get_products_options();
            d()->products_list = d()->Product($pr)->where('is_stop=0');
        }

        if(d()->this->products2){
            $p2 = explode(',', d()->this->products2);
            $pr2 = Array();
            foreach($p2 as $v2){
                $id2 = explode('_', $v2);
                $pr2[] = $id2[0];
            }
            // дополнительные массивы (обязательно перед каждой выборкой товаров)
            get_products_options();
            d()->products_list_2 = d()->Product($pr2)->where('is_stop=0');
        }

        // если отсутствует title на конкретную страницу
        if(!d()->Seo->title){
            // ищем для раздела
            $seo = d()->Seoparam->where('multi_domain=? and razdel="sales" or multi_domain="" and razdel="sales"', $_SERVER['HTTP_HOST'])->order_by('multi_domain DESC')->limit(0,1);
            if($seo->id){
                d()->Seo->title = $seo->title;
                d()->Seo->description = $seo->description;
                d()->Seo->keywords = $seo->keywords;
            }else{
                d()->Seo->title = d()->this->title.' / Акции';
            }
        }

        d()->sale_info_class = 'col-lg-8 col-md-8 col-sm-12 col-xs-12 sale-info';
        if(!d()->this->image && !d()->this->image2 && !d()->this->image3 && !d()->this->image4){
            d()->noimg = 'none';
            d()->sale_info_class = 'col-lg-12 col-md-12 col-sm-12 col-xs-12 sale-info';
        }

        if(d()->this->is_secret){
            // скрыть текстбэк
            $_SESSION['hide_textback'] = 1;
            d()->show_textback = 0;
        }

        print d()->view();
	}



	
	/**
	* Список всех элементов
	*/	
	function index()
	{
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Акции<meta itemprop="position" content="2"></span></li>';
        d()->actsales = 'actsales';
        if(!d()->Seo->title)d()->Seo->title = 'Акции';

        d()->sales_list = d()->Sale->where('is_secret != 1 AND is_active = 1 AND is_hide_site != 1 AND city_id=? AND category_id LIKE "%|9998|%"', d()->city->id)->order_by('sort desc');

        $array_categoria = Array();
        foreach (d()->sales_list as $k_sl=>$v_sl){
            $str_categoria = str_replace('|', ',', $v_sl->category_id);
            $str = trim($str_categoria, ',');
            $scl = explode(',', $str);
            foreach ($scl as $kscl=>$vscl){
                $k = array_search($vscl, $array_categoria);
                if($k == '') $array_categoria[$vscl] = $vscl;
            }
        }
        $str_id_cat = implode(',', $array_categoria);
        d()->active_all = 'active-cat';
        d()->sales_categories_list = d()->Category->where('city_id=? AND is_active=1 AND id IN ('.$str_id_cat.')', d()->city->id);
        if($_GET['categoria']){
            d()->sales_list = d()->Sale->where('is_secret != 1 AND is_active = 1 AND is_hide_site != 1 AND city_id=? AND category_id LIKE ?', d()->city->id, '%|'.$_GET['categoria'].'|%')->order_by('sort desc');
            if($_GET['categoria'] == 9998){
                d()->active_all = 'active-cat';
            }else{
                d()->active_all = '';
            }
        }
		print d()->view();
	}



}

