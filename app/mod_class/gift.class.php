<?php


class Gift extends ActiveRecord
{

    function is_pick()
    {
        if (in_array($this->get('id'), d()->glist)) {
            echo "picked";
        }
        return '';
    }

    function admin_image()
    {
        return '<img src="'.d()->preview($this->get('image'), 100, 'auto').'" />';
    }

}

