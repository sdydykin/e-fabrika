<?php
	//Очищаем cookie
	setcookie("id", "", time() - 3600, "/");
	setcookie("password", "", time() - 3600, "/");
					
	//Переадресация на страницу авторизации
	header("Location: /user_login.php"); 
	
	exit();
?>