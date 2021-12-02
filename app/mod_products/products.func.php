<?php



/**
* Контролер
*/
class ProductsController
{



	
	/**
	* Список всех элементов
	*/	
	function index()
	{
        d()->opt = d()->Option;

        // для ajax версии
        d()->url = url().'?'.$_SERVER['QUERY_STRING'];
        $url = Array(1=>url(1),2=>url(2),3=>url(3),4=>url(4),5=>url(5));
        if(url(1)=='ajax'){
            $url = Array(1=>url(2),2=>url(3),3=>url(4),4=>url(5),5=>url(6));
            get_city();
        }

        if($url[2] && !$url[3]){
            $chh = explode('/', url());
            if(count($chh)>3 && url(1)!='ajax'){
                d()->page_not_found();
                exit;
            }
            if($url[2]=='index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.$url[1]);
                exit;
            }
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }

	    if($url[2] && $url[3]=='index'){
            // список товаров

            // проверка на url /menu/rolly//////zharenyy-roll-s-kuritsey
            $chh = explode('/', url());
            if(count($chh)>3 && url(1)!='ajax'){
                d()->page_not_found();
                exit;
            }

            // если такой категории не создано в этом городе
            //d()->this = d()->Category->where('url=? AND city_id=? AND is_active=1', $url[2], d()->city->id);
            d()->category = d()->Category->where('url=? AND city_id=? AND is_active=1', $url[2], d()->city->id);
            // правило сортировки
            d()->sort_rule = d()->category->sort_rule;

            if(!d()->category->count){
                d()->page_not_found();
                exit;
            }
            d()->subcategories_list = d()->Subcategory->where('category_id = ?', d()->category->id);
            //d()->subcat_ids = d()->subcategories_list->fast_all_of('id');
            d()->subcat_pids = Array();

            // навигация
            d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/menu/" itemprop="item"><span itemprop="name">Меню</span><meta itemprop="position" content="2"></a></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->category->title.'<meta itemprop="position" content="3"></span></li>';
            // название каталога для электронной торговли
            d()->ec_list = "Меню";
            $_SESSION['ec_list'] = "Меню";

            d()->slides_list = d()->Slide->where('city_id=? AND is_active=1 AND is_hide_site != 1 AND category_id LIKE ?', d()->city->id, '%|'.d()->category->id.'|%');
            if(url(2) == d()->category->url && !d()->slides_list->is_empty){
                d()->show_slider = 1;
            }else{
                d()->show_slider = 0;
            }

            // если отсутствует title на конкретную страницу
            if(!d()->Seo->title){
                // ищем для раздела
                $seo = d()->Seoparam->where('multi_domain=? and razdel="categories" or multi_domain="" and razdel="categories"', $_SERVER['HTTP_HOST'])->order_by('multi_domain DESC')->limit(0,1);
                if($seo->id){
                    d()->Seo->title = $seo->title;
                    d()->Seo->description = $seo->description;
                    d()->Seo->keywords = $seo->keywords;
                }else{
                    d()->Seo->title = d()->category->title.' | «Аппетит»';
                }

            }

            // дополнительные массивы (обязательно перед каждой выборкой товаров)
            get_products_options(d()->category->id);
            //print d()->category->id;
            // список товаров
            //d()->products_list = d()->Product->where('category_id=? AND is_active=1 AND city_id=?', d()->this->id, d()->city->id)->order_by(d()->sort_rule.' asc');
            //d()->products_list = d()->Product->where('category_id LIKE ? AND is_active=1 AND city_id=?', '%|'.d()->this->id.'|%', d()->city->id)->order_by(d()->sort_rule.' asc');
            d()->products_list = d()->Product->where('category_id LIKE ? AND is_active=1 AND is_stop=0 AND city_id=? OR category_id=? AND is_active=1 AND is_stop=0 AND city_id=? OR dop_category LIKE ? AND is_active=1 AND is_stop=0 AND city_id=?', '%|'.d()->category->id.'|%', d()->city->id, d()->category->id, d()->city->id, '%|'.d()->category->id.'|%', d()->city->id)->order_by(d()->sort_rule.' asc');

            // выбираем ингридиенты для фильтра в этой категории
            d()->f_ids = Array();
            foreach(d()->products_list as $v){
                $m = explode(',', d()->products_list->filter);
                foreach($m as $key => $val){
                    if($val)d()->f_ids[] = $val;
                }
            }
            d()->f_ids = array_unique(d()->f_ids);
            d()->f_list = d()->Filter(d()->f_ids);

            // активность фильтра
            d()->factive = '';
            if($_GET['not'] || $_GET['like']){
                d()->factive = 'active';
            }

            // фильтруем результаты
            if($_GET['not']){
                d()->none_subcategories = 'none';
                $not = explode(';', $_GET['not']);
                $not = array_filter($not);
                foreach($not as $key=>$val){
                    d()->products_list->where('`filter` NOT LIKE ?','%,'.$val.',%');
                }
            }
            if($_GET['like']){
                d()->none_subcategories = 'none';
                $like = explode(';', $_GET['like']);
                $like = array_filter($like);
                foreach($like as $key=>$val){
                    d()->products_list->where('`filter` LIKE ?','%,'.$val.',%');
                }
            }

            // если небыло фильтров, то показываем чать товаров
            d()->loadmore = 0;
            //if(!$_GET['like'] && !$_GET['not']){
            if(!$_GET['like'] && !$_GET['not'] && $_GET['limit']=='yes'){
                // limit = по сколько выбираем
                // total = сколько уже выбрано
                d()->limit = 8;
                d()->total = d()->products_list->count();

                $start = $_POST['start'];
                if(!$_POST['start'])$start = 0;
                d()->products_list->limit($start, d()->limit);
                d()->selected = $start + d()->limit;

                // проверяем, нужна ли Показать еще
                if(d()->selected >= d()->total){
                    d()->loadmore = 0;
                }else{
                    d()->loadmore = 1;
                }

                if($_POST['start'] && url(1)=='ajax'){
                    $r = Array();
                    $r['loadmore'] = d()->loadmore;
                    $r['start'] = d()->selected;
                    $r['result'] = d()->product_list_tpl();
                    return json_encode($r);
                    exit;
                }
            }
            print d()->view();
        }elseif($url[2] && $url[3] && !$url[4]){
            $chh = explode('/', url());
            if(count($chh)>4 && url(1)!='ajax'){
                d()->page_not_found();
                exit;
            }
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: /'.url().'/');
            exit;
        }elseif($url[2] && $url[3] && $url[4]=='index'){
	        // проверка на url /menu/rolly/zharenyy-roll-s-kuritsey////
            $chh = explode('/', url());
	        if(count($chh)>4 && url(1)!='ajax'){
                d()->page_not_found();
                exit;
            }

            // товар детально
            d()->category = d()->Category->where('url=? AND city_id=? AND is_active=1', $url[2], d()->city->id);
            if(!d()->category->count){
                d()->page_not_found();
                exit;
            }

            get_products_options(d()->category->id);
            d()->this = d()->Product($url[3])->where('city_id=? AND is_active=1 AND is_stop=0 AND category_id LIKE ?', d()->city->id, '%|'.d()->category->id.'|%');
            if(d()->this->count){
                /*$c_cat = trim(d()->this->category_id, '|');
                d()->count_category = explode('|', $c_cat);
                $cat_id_first = d()->Category(d()->count_category[0]);
                if(count(d()->count_category) > 1 && $url[2] != $cat_id_first->url){
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: /menu/'.$cat_id_first->url.'/'.$url[3].'/');
                    exit;
                }*/

                if(d()->this->is_recommended){
                    d()->slider_class = 'slider-products';
                    $rec = d()->Recommend->where('city_id = ? AND product_id = ?', d()->city->id, d()->this->id);
                    if($rec->count){
                        $ids = json_decode($rec->text, true);
                        arsort($ids);
                        $ids = array_keys($ids);
                        $ids = array_slice($ids,0,10);
                        $order_ids = implode(',', $ids);
                        //if(count($ids)<4)d()->slider_class = '';
                        d()->products_list = d()->Product->where('city_id=? AND is_stop=0 AND id IN (?)', d()->city->id, $ids)->order_by('FIELD(id, '.$order_ids.')');
                        if(count(d()->products_list))d()->rec_show = 1;
                    }
                }

                // навигация
                d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span> <a href="/menu/" itemprop="item"><span itemprop="name">Меню</span><meta itemprop="position" content="2"></a></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/menu/'.d()->category->url.'/" itemprop="item"><span itemprop="name">'.d()->category->title.'</span><meta itemprop="position" content="3"></a></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="4"></span></li>';

                // название каталога для электронной торговли
                d()->ec_list = "Рекомендуемые (карточка товара)";

                // если отсутствует title на конкретную страницу
                if(!d()->Seo->title){
                    // ищем для раздела
                    $seo = d()->Seoparam->where('multi_domain=? and razdel="products" or multi_domain="" and razdel="products"', $_SERVER['HTTP_HOST'])->order_by('multi_domain DESC')->limit(0,1);
                    if($seo->id){
                        d()->Seo->title = $seo->title;
                        d()->Seo->description = $seo->description;
                        d()->Seo->keywords = $seo->keywords;
                    }else{
                        d()->Seo->title = d()->this->title.' / '.d()->category->title;
                    }
                }
                print d()->product_view_tpl();
            }else{
                d()->subcategories_list = d()->Subcategory->where("url = ? AND city_id = ?", $url[3], d()->city->id);
                if(d()->subcategories_list->count){
                    d()->subcat_pids = Array();
                    d()->mutli_subcat = 1;
                    d()->subcategory_title= d()->subcategories_list->title;
                    d()->subcategory_id= d()->subcategories_list->id;
                    d()->subtext = d()->subcategories_list->text;
                    d()->subaftertext = d()->subcategories_list->after_text;
                    d()->subflag = 1;

                    // навигация
                    d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/menu/" itemprop="item"><span itemprop="name">Меню</span><meta itemprop="position" content="2"></a></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/menu/'.d()->category->url.'/" itemprop="item"><span itemprop="name">'.d()->category->title.'</span><meta itemprop="position" content="3"></a></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->subcategories_list->title.'<meta itemprop="position" content="4"></span></li>';

                    // название каталога для электронной торговли
                    d()->ec_list = "Меню";
                    $_SESSION['ec_list'] = "Меню";

                    // если отсутствует title на конкретную страницу
                    if(!d()->Seo->title){
                        // ищем для раздела
                        //d()->category = d()->subcategories_list->limit(0,1);
                        $seo = d()->Seoparam->where('multi_domain=? and razdel="categories" or multi_domain="" and razdel="categories"', $_SERVER['HTTP_HOST'])->order_by('multi_domain DESC')->limit(0,1);
                        if($seo->id){
                            d()->Seo->title = $seo->title;
                            d()->Seo->description = $seo->description;
                            d()->Seo->keywords = $seo->keywords;
                        }else{
                            d()->Seo->title = d()->subcategory_title.' | «Аппетит»';
                        }

                    }

                    d()->sort_rule = d()->category->sort_rule;
                    d()->products_list = d()->Product->where('category_id LIKE ? AND subcategory_id LIKE ? AND is_active=1 AND is_stop=0 AND city_id=? OR category_id=? AND subcategory_id=? AND is_active=1 AND is_stop=0 AND city_id=?', '%|'.d()->subcategories_list->category_id.'|%', '%|'.d()->subcategories_list->id.'|%', d()->subcategories_list->city_id, d()->subcategories_list->category_id, d()->subcategories_list->id, d()->subcategories_list->city_id)->order_by(d()->sort_rule.' asc');

                    // выбираем ингридиенты для фильтра в этой категории
                    d()->f_ids = Array();
                    foreach(d()->products_list as $v){
                        $m = explode(',', d()->products_list->filter);
                        foreach($m as $key => $val){
                            if($val)d()->f_ids[] = $val;
                        }
                    }
                    d()->f_ids = array_unique(d()->f_ids);
                    d()->f_list = d()->Filter(d()->f_ids);

                    // активность фильтра
                    d()->factive = '';
                    if($_GET['not'] || $_GET['like']){
                        d()->factive = 'active';
                    }

                    // фильтруем результаты
                    if($_GET['not']){
                        d()->none_subcategories = 'none';
                        $not = explode(';', $_GET['not']);
                        $not = array_filter($not);
                        foreach($not as $key=>$val){
                            d()->products_list->where('`filter` NOT LIKE ?','%,'.$val.',%');
                        }
                    }
                    if($_GET['like']){
                        d()->none_subcategories = 'none';
                        $like = explode(';', $_GET['like']);
                        $like = array_filter($like);
                        foreach($like as $key=>$val){
                            d()->products_list->where('`filter` LIKE ?','%,'.$val.',%');
                        }
                    }
                    // если небыло фильтров, то показываем чать товаров
                    d()->loadmore = 0;
                    //if(!$_GET['like'] && !$_GET['not']){
                    if(!$_GET['like'] && !$_GET['not'] && $_GET['limit']=='yes'){
                        // limit = по сколько выбираем
                        // total = сколько уже выбрано
                        d()->limit = 8;
                        d()->total = d()->products_list->count();

                        $start = $_POST['start'];
                        if(!$_POST['start'])$start = 0;
                        d()->products_list->limit($start, d()->limit);
                        d()->selected = $start + d()->limit;

                        // проверяем, нужна ли Показать еще
                        if(d()->selected >= d()->total){
                            d()->loadmore = 0;
                        }else{
                            d()->loadmore = 1;
                        }

                        if($_POST['start'] && url(1)=='ajax'){
                            $r = Array();
                            $r['loadmore'] = d()->loadmore;
                            $r['start'] = d()->selected;
                            $r['result'] = d()->product_list_tpl();
                            return json_encode($r);
                            exit;
                        }
                    }

                    d()->view();
                }else{
                    d()->page_not_found();
                    exit;
                }
            }
        }else{
            d()->page_not_found();
        }
	}



}

