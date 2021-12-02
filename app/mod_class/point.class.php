<?php


class Point extends ActiveRecord
{

    function date()
    {
        return date('d.m.Y, H:i', strtotime($this->get('created_at')));
    }

    function f_date()
    {
        $date = date('d.m.Y', strtotime($this->get('created_at')));
        $date .= ', <span class="c999">'.date('H:i', strtotime($this->get('created_at'))).'</span>';
        return $date;
    }

    function value_f()
    {
        if($this->get('type')==3 || $this->get('type')==5){
            $znak = '-';
            return '<span style="color:#ed1c24;">'.$znak.$this->get('value').'<span>';
        }
        $znak = '+';
        return '<span style="color:#118000;">'.$znak.$this->get('value').'<span>';
    }

}

class Point_m extends Model
{

    function date()
    {
        return date('d.m.Y, H:i', strtotime($this->get('created_at')));
    }

    function f_date()
    {
        $date = date('d.m.Y', strtotime($this->get('created_at')));
        $date .= ', <span class="c999">'.date('H:i', strtotime($this->get('created_at'))).'</span>';
        return $date;
    }

    function value_f()
    {
        if($this->get('type')==3 || $this->get('type')==5){
            $znak = '-';
            return '<span style="color:#ed1c24;">'.$znak.$this->get('value').'<span>';
        }
        $znak = '+';
        return '<span style="color:#118000;">'.$znak.$this->get('value').'<span>';
    }

}

