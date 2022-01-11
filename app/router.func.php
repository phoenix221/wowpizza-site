<?php
//Автоматически регистрировать все контроллеры
route_all();

route('/error_404', 'error_404');
route('/', 'pages#');

route('/index', 'pages#index');
route('/search/index', 'pages#search');
route('/partners/index', 'main', 'pages#partners');
route('/ts-creator/index', 'main', 'pages#ts_creator');
route('/franchise/index', 'main', 'pages#franchise');

route('/otzyvy/index', 'pages#reviews');
route('/requisites/index', 'pages#requisites');
route('/personal/index', 'pages#personal');
route('/publichnaya-oferta/index', 'pages#oferta');
route('/reviews-application-form/index', 'pages#reviews_application');
route('/vakansii/', 'vacancies#index');
route('/photo-reviews-application-form/index', 'pages#photo_reviews_application');
route('/favorites/index', 'pages#favorites');
//route('/about/index', 'pages#about');
route('/delivery-terms/index', 'pages#delivery_terms');
route('/faq/index', 'pages#faqs');
route('/orders_status/index', 'pages#orders_status');
route('/app-mobile-reviews-application-form/index', 'pages#app_mobile_reviews_application');

route('/quiz/', 'pages#show');
route('/quiz/index', 'pages#quiz');
route('/quiz/history_rolls/', 'pages#quiz_history_rolls');
route('/quiz/quarantine/', 'pages#quiz_quarantine');
route('/quiz/city_history/', 'pages#quiz_city_history');
route('/quiz/erudition/', 'pages#quiz_erudition');

route('/menu/', 'products#index');
route('/menu/index', 'pages#menu');
//route('/menu', 'pages#menu');
route('/ajax/menu/', 'main', 'products#index');

route('/cabinet', 'cabinet#personal');
route('/ajax/cabinet', 'main', 'cabinet#personal');
route('/cabinet/balance', 'cabinet#balance');
route('/ajax/cabinet/balance', 'main', 'cabinet#balance');
route('/cabinet/history', 'cabinet#history');
route('/ajax/cabinet/history', 'main', 'cabinet#history');

route('/sales/index', 'sales#index');
route('/sales/', 'sales#show');

route('/news/index', 'news#index');
route('/news/', 'news#show');

route('/anketa/index', 'main', 'pages#anketa');
route('/anketa/finish/index', 'main', 'pages#anketa_finish');

route('/checkout', 'checkout#index');
route('/ajax/checkout', 'main', 'checkout#index');
route('/checkout/order', 'checkout#order');
route('/ajax/checkout/order', 'main', 'checkout#order');
route('/checkout/online_pay', 'main', 'checkout#online_pay');
route('/checkout/finish', 'checkout#finish');

route('/get/server', 'main', 'get_server');
route('/get/session', 'main', 'get_session');
route('/get/reviews.xml', 'main', 'get_reviews_1c');
route('/get/promo_history.xml', 'main', 'get_promo_history_1c');
route('/get/orders.xml', 'main', 'get_orders_1c');
route('/get/products.xml', 'main', 'get_products_1c');
route('/get/users.xml', 'main', 'get_users_1c');
route('/get/changed_products.xml', 'main', 'get_changed_products_1c');
route('/export/news.xml', 'main', 'get_export_news');
route('/export/promo.xml', 'main', 'get_export_promo');
route('/update/filter', 'main', 'update_filter_products');
//route('/update/url', 'main', 'update_url_product');
//route('/update/active_other', 'main', 'active_other');
route('/get/cancel_orders.xml', 'main', 'get_cancel_orders_1c');

route('/change/users', 'main', 'change_users_1c');
route('/change/promo_history', 'main', 'change_promo_history_1c');
route('/change/sleep_numbers', 'main', 'change_sleep_numbers_1c');
route('/change/delivery_time', 'main', 'change_delivery_time');
route('/change/intime_orders', 'main', 'change_intime_orders');

route('/admin/redirect/', 'main', 'redirect_module');
route('/admin/edit/users/', 'admin_save_data', 'save_users');
route('/admin/edit/add_zonis/', 'admin_save_data', 'save_zonis');
route('/admin/action', 'main', 'admin_action');

route('/ajax_page/', 'main', 'ajax_page');

route('/ajax/check_user', 'main', 'check_user');
route('/ajax/auth', 'main', 'ajax_auth');
route('/ajax/registration', 'main', 'ajax_registration');
route('/ajax/recaptcha', 'main', 'recaptcha');
route('/ajax/del_address', 'main', 'del_address');
route('/ajax/change_personal', 'main', 'change_personal');
route('/ajax/set_reg_phone', 'main', 'set_reg_phone');
route('/ajax/get_more', 'main', 'ajax_get_more');
route('/ajax/check_zone', 'main', 'ajax_check_zone');
route('/ajax/add_address', 'main', 'ajax_add_address');
route('/ajax/delivery_change', 'main', 'ajax_delivery_change');
route('/ajax/ckupload', 'main', 'ajax_ckupload');
route('/ajax/vk_comment', 'main', 'ajax_vk_comment');
route('/ajax/change_cart', 'main', 'ajax_change_cart');
route('/ajax/cart_list', 'main', 'ajax_cart_list');
route('/ajax/run_points', 'main', 'ajax_run_points');
route('/ajax/run_promo', 'main', 'ajax_run_promo');
route('/ajax/order_info', 'main', 'ajax_order_info');
route('/ajax/check_order', 'main', 'ajax_check_order');
route('/ajax/add_gift', 'main', 'ajax_add_gift');
route('/ajax/remove_gift', 'main', 'ajax_remove_gift');
route('/ajax/get_promo_gifts_admin', 'main', 'ajax_get_promo_gifts_admin');
route('/ajax/get_promo_products_admin', 'main', 'ajax_get_promo_products_admin');
route('/ajax/get_sales_products_admin', 'main', 'ajax_get_sales_products_admin');
route('/ajax/get_other_items_admin', 'main', 'ajax_get_other_items_admin');
route('/ajax/get_promo_required_products_admin', 'main', 'ajax_get_promo_required_products_admin');
route('/ajax/ajax_get_gift_products_admin', 'main', 'ajax_get_gift_products_admin');
route('/ajax/check_uniq_promo', 'main', 'ajax_check_uniq_promo');
route('/ajax/remove_gift_dr', 'main', 'ajax_remove_gift_dr');
route('/ajax/remove_gift_pickup', 'main', 'ajax_remove_gift_pickup');
route('/ajax/check_url_genereator', 'main', 'ajax_check_url_genereator');
route('/ajax/change_gifts_type', 'main', 'change_gifts_type');
route('/ajax/likes', 'main', 'ajax_likes');
route('/ajax/compress_img', 'main', 'ajax_compress_img');
route('/ajax/ts_creator_products', 'main', 'ajax_ts_creator_products');
route('/ajax/index', 'main', 'pages#index');
route('/ajax/other_modal', 'main', 'ajax_other_modal');
route('/ajax/other_modal_gift', 'main', 'ajax_other_modal_gift');
route('/ajax/gift_others', 'main', 'ajax_gift_others');
route('/ajax/check_order_conf', 'main', 'ajax_check_order_conf');
route('/ajax/resend_order_conf', 'main', 'ajax_resend_order_conf');
route('/ajax/upload_file', 'main', 'ajax_upload_file');
route('/ajax/get_dopedit_products_admin', 'main', 'ajax_get_dopedit_products_admin');
route('/ajax/get_autoadd_products_admin', 'main', 'ajax_get_autoadd_products_admin');
route('/ajax/check_banner_cookies', 'main', 'ajax_check_banner_cookies');
route('/ajax/get_dopcity_category', 'main', 'ajax_get_dopcity_category');
route('/ajax/get_dopedit_sales_admin', 'main', 'ajax_get_dopedit_sales_admin');
route('/ajax/get_dopedit_promos_admin', 'main', 'ajax_get_dopedit_promos_admin');
route('/ajax/get_autogoods_admin', 'main', 'ajax_get_dopedit_autogoods_admin');
route('/ajax/check_time_order', 'main', 'ajax_check_time_order');
route('/ajax/cancel_birthday', 'main', 'ajax_cancel_birthday');
route('/ajax/check_uniq_doppromo', 'main', 'ajax_check_uniq_doppromo');
route('/ajax/change_image', 'main', 'ajax_change_image');
route('/ajax/wtjsline', 'main', 'ajax_wtjsline');
route('/ajax/product_cart', 'main', 'ajax_product_cart');
route('/ajax/show_details', 'main', 'ajax_show_details');
route('/ajax/get_promo_zones_admin', 'main', 'ajax_get_promo_zones_admin');
route('/ajax/check_hints', 'main', 'ajax_check_hints');
route('/ajax/change_options', 'main', 'ajax_change_options');
route('/ajax/check_address', 'main', 'ajax_check_address');
route('/ajax/cache_address', 'main', 'ajax_cache_address');
route('/ajax/clear_zone', 'main', 'ajax_clear_zone');
route('/ajax/get_sales_products_admin_two', 'main', 'ajax_get_sales_products_admin_two');
route('/ajax/get_dopedit_properties_admin', 'main', 'ajax_get_dopedit_properties_admin');
route('/ajax/find_orders', 'main', 'ajax_find_orders');
route('/ajax/cancel_orders', 'main', 'ajax_cancel_orders');
route('/ajax/check_clinetID', 'main', 'ajax_check_clinetID');
route('/ajax/get_promo_sales_products_admin', 'main', 'ajax_get_promo_sales_products_admin');
route('/ajax/wt_modal', 'main', 'ajax_wt_modal');
route('/ajax/stop_cause', 'main', 'ajax_stop_cause');
route('/ajax/check_details_order_conf', 'main', 'ajax_check_details_order_conf');
route('/ajax/check_cancels_order_conf', 'main', 'ajax_check_cancels_order_conf');
route('/ajax/ajax_get_gift_cash_products_admin', 'main', 'ajax_get_gift_cash_products_admin');
route('/ajax/autoadd_gift_cash', 'main', 'autoadd_gift_cash');

//route('/admin/edit/products/',  'admin_save_data',  'admin_properties_edit, admin_save_data');

// вычисление зон доставки
route('/api/geocheck', 'main', 'geomob');
route('/api/get_geo_1c', 'main',  'api_get_geo_1c');
route('/api/anket_phones', 'main',  'api_anket_phones_1c');
route('/api/sigma', 'main',  'sigma_api');
route('/api/birthdays', 'main',  'birthdays_api');
route('/api/tgbot', 'main',  'pages#tgbot');

route('/cron/send_ankets', 'main',  'cron_send_ankets');
route('/cron/ankets_histories', 'main',  'cron_ankets_histories');
//route('/cron/sigma_test', 'main',  'sigma_test');
route('/cron/clear_codes', 'main',  'cron_clear_codes');
route('/cron/clear_sleep_numbers', 'main', 'cron_clear_sleep_numbers');
route('/cron/check_sales', 'main', 'cron_check_sales');

route('/export/products', 'main',  'export_products');

route('/ajax/orderinfo', 'main', 'orderinfo_crm');

route('/megrate/update_subsection', 'main', 'megrates#m_update_subsection');
route('/megrate/update_products', 'main', 'megrates#m_update_products');
route('/megrate/update_properties', 'main', 'megrates#m_update_properties');
route('/megrate/update_others', 'main', 'megrates#m_update_others');
route('/megrate/update_table_sales', 'main', 'megrates#megrates_table_sales');
route('/megrate/update_table_promocodes', 'main', 'megrates#megrates_table_promocodes');
route('/megrate/update_table_category', 'main', 'megrates#megrates_table_categories');
route('/megrate/update_table_filters', 'main', 'megrates#megrates_table_filters');
route('/megrate/update_table_subcategories', 'main', 'megrates#megrates_table_subcategories');
route('/megrate/update_table_properties', 'main', 'megrates#megrates_table_properties');
route('/megrate/update_table_others', 'main', 'megrates#megrates_table_others');
route('/megrate/update_table_products', 'main', 'megrates#megrates_table_products');

// удаление стикеров
//route('/delete_sticker', 'main', 'delete_sticker');

// для получения переменных из php
//route('/style.css', 'main', 'style_php');
//route('/style', 'main', 'do_style');

route('/sitemap.xml', 'main', 'show_sitemap');
route('/robots.txt', 'main', 'show_robots');
route('/yandex_yml', 'main', 'show_yml');

route('/mtest', 'main', 'mtest');


//route('/news/index', 'content', 'news#index');
//route('/news/index', 'news#index');
//зарегистрировать контроллер newscontroller по адресу /news/
//route('news');
//зарегистрировать контроллер newscontroller по адресу /press/
//route('/press/','news#');


