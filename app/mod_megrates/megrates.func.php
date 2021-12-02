<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class MegratesController
{

    // обновление category_id у subcategories
    function m_update_subsection()
    {
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $cat = d()->Subcategory->where('city_id = ?', $city);

        foreach($cat as $k=>$v){
            $c = d()->Category->where('oldid = ? AND city_id=?', $cat->category_id, $city);
            if($c->is_empty)continue;

            $u = d()->Subcategory($cat->id);
            $u->category_id = $c->id;
            $u->save;

            print 'Updated: '.$cat->id.'<br>';
        }

        print 'ok';
    }

    // обновление category_id, subcategory_id, filter у products
    // TODO: сделать обновление autoadd_products
    function m_update_products()
    {
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';

        // меняем subcategory_id
        $scat = d()->Subcategory->where('city_id = ?', $city);
        print 'Update subcategory_id<br>';
        foreach($scat as $k=>$v){
            if(!$scat->oldid)continue;

            $scid = '|'.$scat->oldid.'|';
            $new_scid = '|'.$scat->id.'|';
            $ps = d()->Product->where('city_id = ? AND subcategory_id LIKE ?', $city, '%'.$scid.'%');
            foreach($ps as $key=>$val){
                $p = d()->Product($ps->id);
                $scid_line = str_replace($scid, $new_scid, $p->subcategory_id);
                $p->subcategory_id = $scid_line;
                $p->save;

                print 'updated: '.$p->id.'<br>';
            }
        }
        print '<hr>';

        // меняем category_id
        $cat = d()->Category->where('city_id=?', $city);
        print 'Update category_id<br>';
        foreach($cat as $k=>$v){
            if(!$cat->oldid)continue;

            $cid = '|'.$cat->oldid.'|';
            $new_cid = '|'.$cat->id.'|';
            $ps = d()->Product->where('city_id = ? AND category_id LIKE ?', $city, '%'.$cid.'%');
            foreach($ps as $key=>$val){
                $p = d()->Product($ps->id);
                $cid_line = str_replace($cid, $new_cid, $p->category_id);
                $p->category_id = $cid_line;
                $p->save;

                print 'updated: '.$p->id.'<br>';
            }
        }
        print '<hr>';

        // меняем filter
        $fil = d()->Filter->where('city_id=?', $city);
        print 'Update filter<br>';
        foreach($fil as $k=>$v){
            if(!$fil->oldid)continue;

            $fid = ','.$fil->oldid.',';
            $new_fid = ','.$fil->id.',';
            $ps = d()->Product->where('city_id = ? AND filter LIKE ?', $city, '%'.$fid.'%');
            foreach($ps as $key=>$val){
                $p = d()->Product($ps->id);
                $filter_line = str_replace($fid, $new_fid, $p->filter);
                $p->filter = $filter_line;
                $p->save;

                print 'updated: '.$p->id.'<br>';
            }
        }
        print '<hr>';

        // меняем автотовары
        print 'Update autogoods<br>';
        $product_list = d()->Product->where('city_id = ? AND autoadd_products != ""', $city);
        foreach ( $product_list as $k_pl=>$v_pl){
            $auto_str = "";
            $str = $v_pl->autoadd_products;
            $s1 = explode(',', $str);
            foreach ($s1 as $ks1=>$vs1){
                $s2 = explode('_', $vs1);
                $temp_product = d()->Product($s2[0]);
                $s3 = d()->Product->where('title = ? AND city_id =?', $temp_product->title, $city);
                $s4 = $s3->id.'_'.$s2[1];
                $auto_str .= $s4.',';
            }
            $auto_str = trim($auto_str, ',');
            $auto_pr = d()->Product($v_pl->id);
            $auto_pr->autoadd_products = $auto_str;
            $auto_pr->save;
        }
        print '<hr>';

        print 'ok';
    }

    // обновление category_id, product_id у properties
    function m_update_properties()
    {
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $pr = d()->Property->where('city_id = ?', $city);

        // category_id
        print 'category_id <br>';
        foreach($pr as $k=>$v){
            if(!$pr->category_id)continue;
            $c = d()->Category->where('oldid = ? AND city_id=?', $pr->category_id, $city);
            if($c->is_empty)continue;

            $u = d()->Property($pr->id);
            $u->category_id = $c->id;
            $u->save;

            print 'Updated: '.$pr->id.'<br>';
        }
        print '<hr>';

        // product_id
        print 'product_id <br>';
        foreach($pr as $k=>$v){
            if(!$pr->product_id)continue;
            $p = d()->Product->where('oldid = ? AND city_id=?', $pr->product_id, $city);
            if($p->is_empty)continue;

            $u = d()->Property($pr->id);
            $u->product_id = $p->id;
            $u->save;

            print 'Updated: '.$pr->id.'<br>';
        }

        print 'ok';
    }

    // обновление product_id, text у others
    function m_update_others()
    {
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $o = d()->Other->where('city_id = ? AND is_active=1', $city);

        // product_id
        print 'product_id <br>';
        foreach($o as $k=>$v){
            if(!$o->product_id)continue;
            $p = d()->Product->where('oldid = ? AND city_id=?', $o->product_id, $city);
            if($p->is_empty)continue;

            $u = d()->Other($o->id);
            $u->product_id = $p->id;
            $u->save;

            print 'Updated: '.$o->id.'<br>';
        }
        print '<hr>';

        // text
        print 'text <br>';
        foreach($o as $k=>$v){
            if(!$o->text)continue;
            $temp_text = explode(',', $o->text);
            $new_text = '';
            foreach($temp_text as $k=>$v){
                $temp = explode('_', $v);
                $p = d()->Product->where('oldid=? AND city_id=?', $temp[0], $city);
                $property = 0;
                if($temp[1]){
                    $pr = d()->Property->where('oldid=? AND city_id=?', $temp[1], $city);
                    $property = $pr->id;
                }

                $t = $p->id.'_'.$property.',';
                $new_text .= $t;
            }
            $new_text = substr($new_text,0,-1);

            $u = d()->Other($o->id);
            $u->text = $new_text;
            $u->save;

            //print 'Updated: '.$o->id.', $new_text: '.$new_text.', $old_text: '.$old_text.'<br>';
            print 'Updated: '.$o->id.'<br>';
        }
        print '<hr>';

        print 'ok';
    }

    // добавление в таблице sales
    function megrates_table_sales(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $sl = d()->Sale->where('city_id = ?', $old_city)->to_array();

        foreach ($sl as $k=>$v){
            $e = d()->Sale->new;
            foreach($sl[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || $kk == 'products' || $kk == 'products2' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $e['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $e[$kk]= $city;
                }else{
                    $e[$kk]= $vv;
                }
            }
            $r = $e->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице promocodes
    function megrates_table_promocodes(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $pc = d()->Promocode->where('city_id = ?', $old_city)->to_array();

        foreach ($pc as $k=>$v){
            $p = d()->Promocode->new;
            foreach($pc[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || $kk == 'gift' || $kk == 'products' || $kk == 'products_limit' || $kk == 'required_products' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $p['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $p[$kk]= $city;
                }else{
                    $p[$kk]= $vv;
                }
            }
            $r = $p->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице categories
    function megrates_table_categories(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $cat = d()->Category->where('city_id = ?', $old_city)->to_array();

        foreach ($cat as $k=>$v){
            $c = d()->Category->new;
            foreach($cat[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $c['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $c[$kk]= $city;
                }else{
                    $c[$kk]= $vv;
                }
            }
            $r = $c->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице filters
    function megrates_table_filters(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $flt = d()->Filter->where('city_id = ?', $old_city)->to_array();

        foreach ($flt as $k=>$v){
            $f = d()->Filter->new;
            foreach($flt[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $f['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $f[$kk]= $city;
                }else{
                    $f[$kk]= $vv;
                }
            }
            $r = $f->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице subcategories
    function megrates_table_subcategories(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $sbc = d()->Subcategory->where('city_id = ?', $old_city)->to_array();

        foreach ($sbc as $k=>$v){
            $sc = d()->Subcategory->new;
            foreach($sbc[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $sc['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $sc[$kk]= $city;
                }else{
                    $sc[$kk]= $vv;
                }
            }
            $r = $sc->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице properties
    function megrates_table_properties(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $pr = d()->Property->where('city_id = ?', $old_city)->to_array();

        foreach ($pr as $k=>$v){
            $pp = d()->Property->new;
            foreach($pr[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $pp['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $pp[$kk]= $city;
                }else{
                    $pp[$kk]= $vv;
                }
            }
            $r = $pp->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице others
    function megrates_table_others(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $other = d()->Other->where('city_id = ?', $old_city)->to_array();

        foreach ($other as $k=>$v){
            $o = d()->Other->new;
            foreach($other[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $o['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $o[$kk]= $city;
                }else{
                    $o[$kk]= $vv;
                }
            }
            $r = $o->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }

    // добавление в таблице products
    function megrates_table_products(){
        exit();
        if(!$_SESSION['admin']) {
            d()->page_not_found();
            exit();
        }

        $city = '8';
        $old_city = '7';
        $product = d()->Product->where('city_id = ?', $old_city)->to_array();

        foreach ($product as $k=>$v){
            $p = d()->Product->new;
            foreach($product[$k] as $kk=>$vv){
                if($kk == 'created_at' || $kk == 'updated_at' ||  $kk == 'admin_options' ||  $kk == 'multi_domain' || $kk == 'table' || $kk == 'oldid' || $kk == 'likes' || is_numeric($kk))continue;
                if($kk == 'id'){
                    $p['oldid'] = $vv;
                }elseif($kk == 'city_id'){
                    $p[$kk]= $city;
                }else{
                    $p[$kk]= $vv;
                }
            }
            $r = $p->save_and_load();
            print $r->id.'<br>';
        }
        print '<hr>';
        print 'Ok';
    }
}
