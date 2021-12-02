<?php


class City extends ActiveRecord
{
    function gift_dr_do_word()
    {
        return declOfNum($this->get('gift_dr_do'), 'день', 'дня', 'дней');
    }

    function gift_dr_posle_word()
    {
        return declOfNum($this->get('gift_dr_posle'), 'день', 'дня', 'дней');
    }

    function textback_window(){
        //$_SESSION['debug'] = $_COOKIE['tbm'];
        if($_COOKIE['tbm'])return '';
        return '<script>setTimeout(stb, 5000);</script>';
    }

    function date_in_cook(){
        return ($this->get('date_in_cook'));
    }

    function wtjsline(){
        // сегодня
        if($_SESSION['zone']){
            $start_t = (int)$_SESSION['zone']['time']+60;
        }else{
            $start_t = 120;
        }
        $start_h = floor($start_t / 60);
        $start_m = $start_t % 60;

        $time = date('U');
        d()->unix_time = $time + $this->get('timezone')*3600;
        // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
        $f_unix_time = d()->unix_time - 4*3600;
        d()->n_week = date('N', $f_unix_time);
        $worktime = d()->city['wt'.d()->n_week];
        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        // заказ минимум +2 часа от текущего времени
        $th = date('G')+$this->get('timezone');

        if($tmp2[0]<$th)$tmp2[0]=$th;

        $h = (int)$tmp2[0] + $start_h;
        $h_end = (int)$tmp3[0];
        $line = '';

        $i = 0;
        while($h != $h_end && $i <= 24) {
            $line .= $h.',';
            $h++;
            if($h == 24)$h = 0;
            $i++;
        }
        $line .= $h_end;
        return $line;
    }

    function wtjsline2(){
        // завтра
        $start = 2;
        $time = date('U');
        d()->unix_time = $time + $this->get('timezone')*3600;
        // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
        $f_unix_time = d()->unix_time - 4*3600;
        // затем делаем, что бы режим работы брался на завтрашний день
        $f_unix_time += 86400;

        d()->n_week = date('N', $f_unix_time);
        $worktime = d()->city['wt'.d()->n_week];

        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        $h = (int)$tmp2[0] + $start;
        $h_end = (int)$tmp3[0];
        $line = '';

        $i = 0;
        while($h != $h_end && $i <= 24) {
            $line .= $h.',';
            $h++;
            if($h == 24)$h = 0;
            $i++;
        }
        $line .= $h_end;
        return $line;
    }

    function wtjsline3(){
        // сегодня
        if($_SESSION['zone']){
            $start = (int)$_SESSION['zone']['time']+60;
        }else{
            $start = 120;
        }

        $time = date('U');
        d()->unix_time = $time + $this->get('timezone')*3600;
        // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
        $f_unix_time = d()->unix_time - 4*3600;
        d()->n_week = date('N', $f_unix_time);
        $worktime = d()->city['wt'.d()->n_week];
        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        // заказ минимум +2 часа от текущего времени
        $th = date('G:i');
        $th1 = explode(':', $th);
        $t_th = $th1[0]+$this->get('timezone');
        $t1 = 60 * $t_th + $th1[1];
        $t_tmp2 = 60 * $tmp2[0] + $tmp2[1];
        $t_tmp3 = 60 * $tmp3[0] + $tmp3[1];

        if($t_tmp2<$t1)$t_tmp2=$t1;

        $h = (int)$t_tmp2 + $start;
        $h_end = (int)$t_tmp3;
        $line = '';

        $i = 0;

        $hn = floor($h /60);
        $mn = $h % 60;
        $sm = floor($mn / 5);
        $s = $mn % 5;
        if($s >= 5){
            $m = $sm*5 + 0;
        }else{
            $m = $sm*5 + 5;
        }
        $line = $hn.',';
        while($m != $tmp3[1] && $i <= 12) {
            $line .= $m.',';
            $m += 5;
            if($m == 60)$m = 0;
            $i++;
        }
        //$line .= $h_end;
        $line = substr($line, 0 ,-1);
        return $line;
    }

    function wtjsline4(){
        // сегодня
        if($_SESSION['delivery'] == 2 && $_SESSION['zone']){
            $start_t = (int)$_SESSION['zone']['time']+60;
        }else{
            $start_t = 120;
        }
        $start_h = floor($start_t / 60);
        $start_m = $start_t % 60;
        $start = date('H:i', mktime($start_h, $start_m ));

        $time = date('U');
        d()->unix_time = $time + $this->get('timezone')*3600;
        // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
        $f_unix_time = d()->unix_time - 4*3600;
        d()->n_week = date('N', $f_unix_time);
        $worktime = d()->city['wt'.d()->n_week];
        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        // заказ минимум +2 часа от текущего времени
        $th = date('G:i');
        $th1 = explode(':', $th);
        $t_th = $th1[0]+$this->get('timezone');
        //$t1 = 60 * $t_th + $th1[1];
        $t1 = $t_th.':'.$th1[1];

        if($tmp2[0]<$t_th){
            $tmp2[0]=$t_th;
            $tmp2[1]=$th1[1];
        }

        list($h, $m) = explode(':', $t1);
        $th2 = date('H:i', strtotime("+$h hour $m minute", strtotime($start)));
        list($h1, $m2) = explode(':', $th2);
        if($m2 >= 30){
            $h1 += 1;
            $m2 = 0;
            $m3 = 30;
        }else{
            $m2 = 30;
            $m3 = 0;
        }
        //$th3 = date('H:i', mktime($h1, $m2));
        if($h1 == 24)$h1 = 0;

        if($h1 >= (int)$tmp3[0] && $h1 < (int)$tmp2[0]) $h1 = (int)$tmp3[0];
        if($_SESSION['delivery'] == 1){
            $h_end = 23;
            $m_end = 0;
        }else{
            $h_end = (int)$tmp3[0];
            $m_end = $tmp3[1];
        }
        $line2 = '';
        $i1 = 0;
        while($h1 != $h_end && $i1 <= 24){
            $line2 .= date('H:i', mktime($h1, $m2)).',';
            if($m2 == 30){
                $h1++;
                if($h1 == 24)$h1 = 0;
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
            }else{
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
                $h1++;
                if($h1 == 24)$h1 = 0;
            }
            $i1++;
        }
        $line2 .= date('H:i', mktime($h_end, $m_end));
        return $line2;
    }

    function wtjsline5(){
        // завтра
        if($_SESSION['delivery'] == 2 && $_SESSION['zone']){
            $start_t = (int)$_SESSION['zone']['time']+60;
        }else{
            $start_t = 120;
        }
        $start_h = floor($start_t / 60);
        $start_m = $start_t % 60;
        $start = date('H:i', mktime($start_h, $start_m ));

        $time = date('U');
        d()->unix_time = $time + $this->get('timezone')*3600;
        // выбираем день, для отображения режима работы (с 00.00 до 04.00 утра показываем вчерашний день)
        $f_unix_time = d()->unix_time - 4*3600;
        // затем делаем, что бы режим работы брался на завтрашний день
        $f_unix_time += 86400;

        d()->n_week = date('N', $f_unix_time);
        $worktime = d()->city['wt'.d()->n_week];

        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        list($h, $m) = explode(':', $tmp1[0]);
        $th2 = date('H:i', strtotime("+$h hour $m minute", strtotime($start)));
        list($h1, $m2) = explode(':', $th2);
        if($m2 == 0){
            $m2 = 0;
            $m3 = 30;
        }else{
            if($m2 >= 30){
                $h1 += 1;
                $m2 = 0;
                $m3 = 30;
            }else{
                $m2 = 30;
                $m3 = 0;
            }
        }
        //$th3 = date('H:i', mktime($h1, $m2));
        if($_SESSION['delivery'] == 1){
            $h_end = 23;
            $m_end = 0;
        }else{
            $h_end = (int)$tmp3[0];
            $m_end = $tmp3[1];
        }
        $line2 = '';
        $i1 = 0;
        while($h1 != $h_end && $i1 <= 24){
            $line2 .= date('H:i', mktime($h1, $m2)).',';
            if($m2 == 30){
                $h1++;
                if($h1 == 24)$h1 = 0;
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
            }else{
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
                $h1++;
                if($h1 == 24)$h1 = 0;
            }
            $i1++;
        }
        $line2 .= date('H:i', mktime($h_end, $m_end));
        return $line2;
    }

    function wtjsline6(){
        // Выброный день из бд
        if($_SESSION['delivery'] == 2 && $_SESSION['zone']){
            $start_t = (int)$_SESSION['zone']['time']+60;
        }else{
            $start_t = 120;
        }
        $start_h = floor($start_t / 60);
        $start_m = $start_t % 60;
        $start = date('H:i', mktime($start_h, $start_m ));

        $days_in_bd = strtotime($this->get('date_in_cook'));
        //d()->n_week = date('N', $days_in_bd);
        //$worktime = d()->city['wt'.d()->n_week];
        $worktime = d()->city['wt10'];

        $tmp1 = explode('-', $worktime);
        $tmp2 = explode(':', $tmp1[0]);
        $tmp3 = explode(':', $tmp1[1]);

        list($h, $m) = explode(':', $tmp1[0]);
        $th2 = date('H:i', strtotime("+$h hour $m minute", strtotime($start)));
        list($h1, $m2) = explode(':', $th2);
        if($m2 == 0){
            $m2 = 0;
            $m3 = 30;
        }else{
            if($m2 >= 30){
                $h1 += 1;
                $m2 = 0;
                $m3 = 30;
            }else{
                $m2 = 30;
                $m3 = 0;
            }
        }
        //$th3 = date('H:i', mktime($h1, $m2));
        if($_SESSION['delivery'] == 1){
            $h_end = 23;
            $m_end = 0;
        }else{
            $h_end = (int)$tmp3[0];
            $m_end = $tmp3[1];
        }
        $line2 = '';
        $i1 = 0;
        while($h1 != $h_end && $i1 <= 24){
            $line2 .= date('H:i', mktime($h1, $m2)).',';
            if($m2 == 30){
                $h1++;
                if($h1 == 24)$h1 = 0;
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
            }else{
                if($h1 != $h_end)$line2 .= date('H:i', mktime($h1, $m3)).',';
                $h1++;
                if($h1 == 24)$h1 = 0;
            }
            $i1++;
        }
        $line2 .= date('H:i', mktime($h_end, $m_end));
        return $line2;
    }
}
