<?php
	//Системные настройки
	require $_SERVER['DOCUMENT_ROOT'] . "/sys_config.php";

	//Получаем данные из cookie
	$user_id = intval($_COOKIE["id"]);
	$user_password = $_COOKIE["password"];

	//Проверка на главную страницу сайта
	$is_index_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) == "index.php";	

	//Переходим на страницу авторизации если нет иннформации для авторизации
	if($user_id === 0 || !isset($user_password))
	{
		//Переадресация на страницу авторизации
		if(!$is_index_page)
		{
			header("Location: /user_login.php"); 
		
			exit;
		}	
	}
	else
	{
		//Подключение к БД
		$db = mysqli_connect($mysql_location, $mysql_user, $mysql_password) or die ("<p>Не удалось подключиться к серверу БД</p>");
		mysqli_select_db($db, $mysql_data_base) or die ("<p>Не удалось подключиться к базе данных</p>");
		mysqli_set_charset($db, 'utf8');
	
		//Ищем пользователя в базе данных
		$query_result = mysqli_query($db, "SELECT password, email, name, address, company FROM user WHERE id = \"$user_id\"");	
		$row_count = mysqli_num_rows($query_result);
	
		if($row_count != 0) //Пользователь найден в БД
		{
			$row = mysqli_fetch_array($query_result);
		
			$user_name   = $row['name'];
			$user_email  = $row['email'];
			$user_address = $row['address'];
			$user_company = $row['company'];
		
			if($row['password'] != $user_password) //Не верный пароль пользователя
			{
				//Переадресация на страницу авторизации
				if(!$is_index_page)
				{
					header("Location: /user_login.php"); 
		
					exit;
				}
			}	
		}
		else //Пользователь не найден в базе данных, переходим на страницу авторизации
		{
			//Переадресация на страницу авторизации
			if(!$is_index_page)
			{
				header("Location: /user_login.php"); 
		
				exit;
			
			}
		}
	}	
?>