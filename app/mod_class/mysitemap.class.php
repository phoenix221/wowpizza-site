<?php
//Карта сайта

class Mysitemap extends UniversalSingletoneHelper
{
	protected $pages=array();
	function init()
	{
		$domain = $_SERVER['HTTP_HOST'];
		if(isset(d()->sitemap['settings']['domain'])){
			$domain = d()->sitemap['settings']['domain'];
		}
		foreach(d()->sitemap as $key=>$value){
			if($key == 'settings'){
				continue;
			}
			$table = $key;
			if(isset($value['table'])){
				$table = $value['table'];
			}

			if(isset($value['url'])){
				$url = $value['url'];
			}else{
				$url = '/'.$table.'/';
			}
			$last_change= false;
			$model_name = to_camel(to_o($table));

			get_city();
			if($table == 'products'){
			    get_products_options();
                d()->categ = d()->Category;
            }

			foreach(d()->{$model_name} as $row){
				$page_url = $row->url;
				if($page_url==''){
					$page_url = $row->id;
				}

				if($page_url=='index'){
					$page_url = '';
				}

                // categories
                if($table == 'categories'){
                    if($row->city_id != d()->city->id || !$row->is_active)continue;
                }
                // products
                if($table == 'products'){
                    if($row->city_id != d()->city->id || !$row->is_active)continue;
                    if(!d()->categ->find_by_id($row->f_category_id)->is_active)continue;
                    $url = '';
                    $page_url = $row->link;
                }
                // news
                if($table == 'news'){
                    if($row->city_id != d()->city->id)continue;
                }
                // sales
                if($table == 'sales'){
                    if($row->city_id != d()->city->id)continue;
                }
                // pages
                if($table == 'pages'){
                    if($row->city_id != d()->city->id)continue;
                }

				$uri =  'https://'.$domain . $url . $page_url;
				$data = array();
				$data['priority']='0.5';

				$last = substr($uri, -1);
				if($last != '/')$uri .= '/';

				$data['url'] = $uri;
				if($url . $page_url == '/'){
					$data['priority']='1';
				}
				$data['last_change']=substr($row->updated_at,0,10);

				$this->add_row($data);
				if($last_change==false || $last_change < $row->updated_at){
					$last_change=$row->updated_at;
				}
			}

			if(isset($value['root'])){
				if($last_change==false){
					 $last_change = Date("Y-m-d H:i:s");
				}
				$data = array();
				$data['priority']='0.5';
				$uri =  'https://'.$domain . $value['root'];
				$data['url']=$uri;
				$data['last_change']=substr($last_change,0,10);
				$this->add_row($data);
			}
		}
	}
	function add_row($array=array()){

		$this->pages[$array['url']]=$array;
	}
	function to_xml()
	{
		d()->sitemap_pages = $this->pages;
		return d()->sitemap_xml_tpl();
	}

}
