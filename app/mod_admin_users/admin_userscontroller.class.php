<?php

//Дополнительные пользователи
class Admin_usersController
{
    function index()
    {
        d()->admin_users_list = d()->Admin_user;
        print d()->view();
    }
    function save()
    {
        if(!iam()){
            //До этого не должно дойти
            exit();
        }
        if(!iam('admin') && !iam('developer')){
            //До этого не должно дойти
            exit();
        }

        if(d()->validate()){
            if($_POST['element_id']=='add'){
                if(empty(d()->params['password'])){
                    d()->add_notice('Вы не ввели пароль','password');
                }
            }
        }
        if(d()->validate()){
            if(d()->params['login']=='admin' || d()->params['login']=='developer'){
                d()->add_notice('Имена admin и developer использовать нельзя.','login');
            }
        }
        if(d()->validate()){
            if($_POST['element_id']=='add'){
                if (! d()->Admin_user->is_empty){
                    if(!d()->Admin_user->where('login = ?',d()->params['login'])->is_empty){
                        d()->add_notice('Такой логин уже создан','login');
                    }
                }
            }else{
                if (! d()->Admin_user->is_empty){
                    if(!d()->Admin_user->where('login = ?',d()->params['login'])->is_empty && d()->Admin_user->where('login = ?',d()->params['login'])->id!=$_POST['element_id']){
                        d()->add_notice('Такой логин уже создан','login');
                    }
                }
            }


        }
        if(d()->validate()){
            if($_POST['element_id']=='add'){
                $user = d()->Admin_user->new;
            }else{
                $user = d()->Admin_user($_POST['element_id']);
                if($user->is_empty){
                    d()->add_notice('Такой пользователь не найден');
                    print '$(".notice_container").html(' . json_encode(d()->notice(array('bootstrap'))) . '); ';
                    d()->reload();
                    exit();
                    //return 'Пользователь не найден';
                }
            }
            $user->login = d()->params['login'];
            $user->phone = d()->params['phone'];
            $user->name = d()->params['name'];
            $wl = '';
            $d_wl = '';

            $user->cities = d()->params['cities'];

            // доступ к главной вкладе Контент сайта
            if(
                d()->params['is_cities_view'] ||
                d()->params['is_cities_edit'] ||
                d()->params['is_cities_wt_edit'] ||
                d()->params['is_products_view'] ||
                d()->params['is_products_edit'] ||
                d()->params['is_promocodes_view'] ||
                d()->params['is_promocodes_edit'] ||
                d()->params['is_sales_view'] ||
                d()->params['is_sales_edit'] ||
                d()->params['is_news_view'] ||
                d()->params['is_news_edit'] ||
                d()->params['is_pages_view'] ||
                d()->params['is_pages_edit'] ||
                d()->params['is_users_view'] ||
                d()->params['is_users_edit']
            ){
                $wl .= 'cities,';
            }
            // зависимые таблицы ГОРОДОВ
            if(d()->params['is_cities_view'] || d()->params['is_cities_edit']){
                $wl .= 'offices,add_zonis,zonis,';
            }
            if(d()->params['is_cities_view']){
                $d_wl .= 'cities_view,offices_view,add_zonis_view,zonis_view,';
            }
            if(d()->params['is_cities_edit']){
                $d_wl .= 'cities_edit,offices_edit,add_zonis_edit,zonis_edit,';
            }
            if(d()->params['is_cities_wt_edit']){
                $wl .= 'cities_wt,';
                $d_wl .= 'cities_wt_edit,';
            }

            // МЕНЮ и все зависимые таблицы
            if(d()->params['is_products_view'] || d()->params['is_products_edit']){
                $wl .= 'products,categories,subcategories,properties,filters,stickers,others,other_items,';
            }
            if(d()->params['is_products_view']){
                $d_wl .= 'products_view,categories_view,subcategories_view,properties_view,filters_view,stickers_view,others_view,other_items_view,';
            }
            if(d()->params['is_products_edit']){
                $d_wl .= 'products_edit,categories_edit,subcategories_edit,properties_edit,filters_edit,stickers_edit,others_edit,other_items_edit,';
            }

            // ПРОМОКОДЫ и все зависимые таблицы
            if(d()->params['is_promocodes_view'] || d()->params['is_promocodes_edit']){
                $wl .= 'promocodes,';
                if(d()->params['is_promocodes_view'])$d_wl .= 'promocodes_view,';
                if(d()->params['is_promocodes_edit'])$d_wl .= 'promocodes_edit,';
            }

            // АКЦИИ и все зависимые таблицы
            if(d()->params['is_sales_view'] || d()->params['is_sales_edit']){
                $wl .= 'sales,';
                if(d()->params['is_sales_view'])$d_wl .= 'sales_view,';
                if(d()->params['is_sales_edit'])$d_wl .= 'sales_edit,';
            }

            // СЛАЙДЫ и все зависимые таблицы
            if(d()->params['is_slides_view'] || d()->params['is_slides_edit']){
                $wl .= 'slides,';
                if(d()->params['is_slides_view'])$d_wl .= 'slides_view,';
                if(d()->params['is_slides_edit'])$d_wl .= 'slides_edit,';
            }

            // НОВОСТИ и все зависимые таблицы
            if(d()->params['is_news_view'] || d()->params['is_news_edit']){
                $wl .= 'news,';
                if(d()->params['is_news_view'])$d_wl .= 'news_view,';
                if(d()->params['is_news_edit'])$d_wl .= 'news_edit,';
            }

            // ТЕКСТОВЫЕ СТРАНИЦЫ и все зависимые таблицы
            if(d()->params['is_pages_view'] || d()->params['is_pages_edit']){
                $wl .= 'pages,documents,';
                if(d()->params['is_pages_view'])$d_wl .= 'pages_view,documents_view,';
                if(d()->params['is_pages_edit'])$d_wl .= 'pages_edit,documents_edit,';
            }

            // ВАКАНСИИ и все зависимые таблицы
            if(d()->params['is_vacancies_view'] || d()->params['is_vacancies_edit']){
                $wl .= 'vacancies,,';
                if(d()->params['is_vacancies_view'])$d_wl .= 'vacancies_view,';
                if(d()->params['is_vacancies_edit'])$d_wl .= 'vacancies_edit,';
            }

            // ПОДАРКИ и все зависимые таблицы
            if(d()->params['is_gifts_view'] || d()->params['is_gifts_edit']){
                $wl .= 'gifts,';
                if(d()->params['is_gifts_view'])$d_wl .= 'gifts_view,';
                if(d()->params['is_gifts_edit'])$d_wl .= 'gifts_edit,';
            }

            // ПОЛЬЗОВАТЕЛИ и все зависимые таблицы
            if(d()->params['is_users_view'] || d()->params['is_users_edit']){
                $wl .= 'users,addresses,points,';
                if(d()->params['is_users_view'])$d_wl .= 'users_view,addresses_view,points_view,';
                if(d()->params['is_users_edit'])$d_wl .= 'users_edit,addresses_edit,points_edit,';
            }

            // CRM
            if(d()->params['is_crm_orders_view'] || d()->params['is_crm_orders_edit']){
                $wl .= 'crm_orders,';
                if(d()->params['is_crm_orders_view'])$d_wl .= 'crm_orders_view,';
                if(d()->params['is_crm_orders_edit'])$d_wl .= 'crm_orders_edit,';
            }
            if(d()->params['is_crm_users_view'] || d()->params['is_crm_users_edit']){
                $wl .= 'crm_users,points,';
                if(d()->params['is_crm_users_view'])$d_wl .= 'crm_users_view,crm_points_view,';
                if(d()->params['is_crm_users_edit'])$d_wl .= 'crm_users_edit,crm_points_edit,';
            }
            if(d()->params['is_ankets_view'] || d()->params['is_ankets_edit']){
                $wl .= 'ankets,';
                if(d()->params['is_ankets_view'])$d_wl .= 'ankets_view,';
                if(d()->params['is_ankets_edit'])$d_wl .= 'ankets_edit,';
            }
            if(d()->params['is_crm_reviews_view'] || d()->params['is_crm_reviews_edit']){
                $wl .= 'reviews,';
                if(d()->params['is_crm_reviews_view'])$d_wl .= 'reviews_view,';
                if(d()->params['is_crm_reviews_edit'])$d_wl .= 'reviews_edit,';
            }
            if(d()->params['is_crm_promocodes_view']){
                $wl .= 'crm_promocodes,';
                $d_wl .= 'crm_promocodes_view,';
            }
            if(d()->params['is_crm_actions_view'] || d()->params['is_crm_actions_edit']){
                $wl .= 'crm_actions,crm_channel_actions,';
                if(d()->params['is_crm_actions_view'])$d_wl .= 'crm_actions_view,crm_channel_actions_view,';
                if(d()->params['is_crm_actions_edit'])$d_wl .= 'crm_actions_edit,crm_channel_actions_edit,';
            }
            if(d()->params['is_crm_reviews_applications_view'] || d()->params['is_crm_reviews_applications_edit']){
                $wl .= 'crm_reviews_applications,reviews_applications,';
                if(d()->params['is_crm_reviews_applications_view'])$d_wl .= 'crm_reviews_applications_view,reviews_applications_view,';
                if(d()->params['is_crm_reviews_applications_edit'])$d_wl .= 'crm_reviews_applications_edit,reviews_applications_edit,';
            }


            // МОДУЛЬ СЕО и все зависимые таблицы
            if(d()->params['is_seoparams_view'] || d()->params['is_seoparams_edit']){
                $wl .= 'seoparams,robots,redirects,';
                if(d()->params['is_seoparams_view'])$d_wl .= 'seoparams_view,robots_view,redirects_view,';
                if(d()->params['is_seoparams_edit'])$d_wl .= 'seoparams_edit,robots_edit,redirects_edit,';
            }

            $wl .= 'ts_histories,';
            $d_wl .= 'ts_histories_view,ts_histories_edit,';

            $wl = substr($wl,0,-1);
            $d_wl = substr($d_wl,0,-1);
            $user->whitelist = $wl;
            $user->d_whitelist = $d_wl;

            if(d()->params['password']!=''){
                $user->password = md5(d()->params['password']);
            }
            $user->save();

            print 'document.location.href="/admin/list/admin_users";';
            exit();
        }
        if(d()->notice(array('bootstrap'))){
            print '$(".notice_container").html(' . json_encode(d()->notice(array('bootstrap'))) . '); ';
        }
        d()->reload();
    }
    function edit()
    {

        if(!iam('admin') && !iam('developer')){
            return 'Только главный администратор может управлять доступом.';
        }
        if(url(4)!='add'){
            d()->user = d()->Admin_user(url(4));
            if(d()->user->is_empty){
                return 'Пользователь не найден';
            }
        }else{
            d()->user = d()->Admin_user->limit(0);
        }
        print d()->view();
    }
}
