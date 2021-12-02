<?php


class Document extends ActiveRecord
{

    function text()
    {
        $t = $this->get('text');

        $site = '<a href="/">'.d()->domain.'</a>';
        $t = str_replace('{site}', $site, $t);

        $req = d()->city->ur_requisites;
        $t = str_replace('{requisites}', $req, $t);

        $org = d()->city->ur_name;
        $t = str_replace('{organization}', $org, $t);

        $adr = d()->city->ur_address;
        $t = str_replace('{ur_address}', $adr, $t);

        return $t;
    }

}

