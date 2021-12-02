<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Option extends ActiveRecord
{

	function city()
	{
		return d()->City($this->get('city_id'))->title;
	}
}

