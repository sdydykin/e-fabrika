<?php
	//Системные настройки
	require $_SERVER['DOCUMENT_ROOT'] . "/sys_config.php";
	
	//Нажата кнопка "Регистрация"
	if(isset($_POST["btnUserRegister"]))
	{
		//Переадресация на страницу регистрации
		header("Location: /user_profile.php"); 
		
		exit;
	}
	
	//Нажата кнопка "Вход"
	if(isset($_POST["btnUserLogin"]))
	{
		//Получаем данные, введенные пользователем
		$user_email = $_POST["edtUserEmail"];
		$user_password = $_POST["edtUserPassword"];
	
		//Подключение к БД
		$db = mysqli_connect($mysql_location, $mysql_user, $mysql_password) or die ("<p>Не удалось подключиться к серверу БД</p>");
		mysqli_select_db($db, $mysql_data_base) or die ("<p>Не удалось подключиться к базе данных</p>");
		mysqli_set_charset($db, 'utf8');
	
		//Ищем пользователя в базе данных
		$query_result = mysqli_query($db, "SELECT id, password FROM user WHERE email = \"$user_email\"");
		
		$row_count = mysqli_num_rows($query_result);
			
		if($row_count > 0)
		{
			$row = mysqli_fetch_array($query_result);
			
			if($row['password'] === md5(md5($user_password)))
			{
				//Авторизация прошла успешно
				
				//Сохраняем параметры входа
				setcookie("id", $row['id'], time() + 60 * 60 * 24 * 30, "/");
				setcookie("password", $row['password'], time() +  60 * 60 * 24 * 30, "/");
					
				//Переходим на главную страницу
				header("Location: /index.php"); 
				
				exit();
			}
			else
			{
				//Пароль введенный пользователем не совпал с хранимым в БД
				die ("Ошибка при авторизации: не верный пароль");
			}
		}
		else
			die ("Ошибка при авторизации: не удалось найти пользователя с указанным email");
	}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta content='text/html; charset=utf-8' />  
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 	
		<link rel="stylesheet" href="/bootstrap/css/bootstrap.css">
		<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
	
		<script src="https://kit.fontawesome.com/29a34755cb.js"></script>
	
		<title>Вход в систему</title>
	</head>
	<body>
		<div class = "container">
			<div class = "row justify-content-center">
				<div class = "col-12">
					<?php	
						//Вывод "шапки" страницы
						require $_SERVER['DOCUMENT_ROOT'] . "/site_header.php";
					?>
				</div>
				<div class = "col-8">	
<?php			
	echo "			<form id = 'frmUserLogin' method = 'post' action='user_login.php'>	
						<div class='mb-3'>
							<h4 class = 'text-center'>Вход в систему</h4>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserEmail'>Электронная почта:</label>
							<input class = 'form-control' name='edtUserEmail' type='email' value='$user_email' placeholder='Электронная почта пользователя'>
						</div>	
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserPassword'>Пароль:</label>
							<input class = 'form-control' name='edtUserPassword' type='password' value='$user_password' placeholder='Пароль для входа'>
						</div>	
						<div class='row justify-content-center'>
							<div class='mb-3 text-center'>						
								<button type='submit' class='btn btn-primary' name='btnUserLogin'>Вход</button>
								<button type='submit' class='btn btn-primary' name='btnUserRegister'>Регистрация</button>
							</div>
						</div>
					</form>";
?>	
				</div>
				<div class = "col-12">
					<?php	
						//Вывод "подвала" страницы
						require $_SERVER['DOCUMENT_ROOT'] . "/site_footer.php";
					?>
				</div>	
			</div>
		</div>
	
		<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
		<script src="https://unpkg.com/popper.js"></script>
	</body>
</html>