<?php



/**
* Контролер
*/
class NewsController
{



	/**
	* Отображение элемента
	*/
	function show()
	{
        //$url = url(2);
        $u = explode('/', $_SERVER['REQUEST_URI']);
        $url = str_replace('?','',str_replace($_SERVER['QUERY_STRING'], '', $u[2]));


	    d()->this = d()->News->find_by_url($url)->where('city_id=? AND is_active=1', d()->city->id);
	    if(d()->this->is_empty || url(3)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><a href="/news/" itemprop="item"><span itemprop="name">Новости</span><meta itemprop="position" content="2"></a></li>';
        d()->nav .= '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="3"></span></li>';
        // если отсутствует title на конкретную страницу
        if(!d()->Seo->title){
            // ищем для раздела
            $seo = d()->Seoparam->where('multi_domain=? and razdel="news" or multi_domain="" and razdel="news"', $_SERVER['HTTP_HOST'])->order_by('multi_domain DESC')->limit(0,1);
            if($seo->id){
                d()->Seo->title = $seo->title;
                d()->Seo->description = $seo->description;
                d()->Seo->keywords = $seo->keywords;
            }else{
                d()->Seo->title = d()->this->title.' / Новости';
            }
        }

        print d()->view();
	}



	
	/**
	* Список всех элементов
	*/	
	function index()
	{
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Новости<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = 'Новости';

        // количество показываемых строк на старте
        $cnt = 6;
        d()->news_list = d()->News->where('city_id=? AND is_active=1', d()->city->id)->order_by('sort desc')->limit(0, $cnt);
        //d()->news_list = d()->News->where('city_id=?', d()->city->id)->order_by('sort desc')->slice(3);

        // кнопка Показать еще
        d()->load_more = g_loadmore('news', $cnt, $cnt, '');

		print d()->view();
	}



}

