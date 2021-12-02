<?php


class Landing_partner extends ActiveRecord
{

    function city_title()
    {
        return d()->City($this->get('city_id'))->title;
    }

}

