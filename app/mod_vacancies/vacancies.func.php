<?php



/**
* Контролер
*/
class VacanciesController
{



	
	/**
	* Список всех элементов
	*/	
	function index()
	{
        d()->show_textback = 0;
	    $url = url(2);
        if($url == 'index'){
            d()->this = d()->Vacancy->where('city_id=? AND is_active=1 AND is_main=1', d()->city->id)->limit(0,1);
            if(!d()->this->count){
                d()->this = d()->Vacancy->where('city_id=? AND is_active=1', d()->city->id)->limit(0,1);
            }
        }else{
            d()->this = d()->Vacancy->find_by_url($url)->where('city_id=? AND is_active=1', d()->city->id);
        }

        if(d()->this->is_empty || url(2)!='index' && url(3)!='index'){
            if(substr(url(), -6)!='/index'){
                header("HTTP/1.1 301 Moved Permanently");
                header('Location: /'.url().'/');
                exit;
            }
            d()->page_not_found();
        }
        // навигация
        d()->nav = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">Вакансии<meta itemprop="position" content="2"></span></li><li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"><span class="mdi mdi-chevron-right"></span><span itemprop="name">'.d()->this->title.'<meta itemprop="position" content="2"></span></li>';
        if(!d()->Seo->title)d()->Seo->title = d()->this->title.' - вакансия «Аппетит» '.d()->city->title;

		d()->vacancies_list = d()->Vacancy->where('city_id=? AND is_active=1 AND id != ?', d()->city->id, d()->this->id);
		print d()->view();
	}



}

