<?php


class Address extends ActiveRecord
{

    function city_ttl()
    {
        return d()->City->where('code=?', $this->get('city'))->limit(0,1)->title;
    }
}

