<?php 
	
	function admin_edit_my_password(){
		if(d()->validate('admin_edit_my_password')){
			$md5 = md5(d()->params['password']);
			
			$result="[admin.editor]
login=admin
password=$md5";
			file_put_contents(__DIR__ . '/password.init.ini',$result);
			
			$_SESSION["flash"] = "<b>Готово!</b> Пароль изменён. <a href='/'>На главную</a>";
			
		}
		print d()->admin_edit_password_tpl();
	}