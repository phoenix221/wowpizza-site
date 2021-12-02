<?php
/*
	Модуль для работы с текстовыми страницами, для вывода меню, выода подстраниц
*/
class Contact extends ActiveRecord
{
	
	function active()
	{
		if($this->get('url')==url(1)){
			return 'active';
		}
		return '';
	}
}

