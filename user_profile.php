<?php
	//Идентификатор пользователя
	$id = intval($_GET["id"]);
	
	//Если задан идентификатор пользователя, проверяем его авторизацию
	if($id !== 0)
	{
		//Проверка авторизации пользователя
		require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";
		
		//Проверка авторизации прошла, прверяем корректность идентификатора пользователя
		if($id !== $user_id)
		{
			//Переадресация на страницу авторизации
			header("Location: /user_login.php"); 
		
			exit;
		}
	}
	
	//Сохранение информации о пользователе
	if(isset($_POST['btnUserSave']))
	{
		//Подключение к БД
		$db = mysqli_connect("localhost", "sfabr", "asrtY67Q!") or die ("<p>Не удалось подключиться к серверу БД</p>");
		mysqli_select_db($db, "parts_db") or die ("<p>Не удалось подключиться к базе данных</p>");
		mysqli_set_charset($db, 'utf8');
		
		//Чтение параметров введенных пользователем
		$user_name     = $_POST['edtUserName'];
		$user_company  = mysqli_escape_string($db, $_POST['edtUserCompany']);
		$user_email    = $_POST['edtUserEmail'];
		$user_address   = $_POST['edtUserAddress'];
		$user_password = $_POST['edtUserPassword'];
		
		if($id !== 0) //Редактирование текущего пользователя
		{
			//Обновление информации о пользователе в базе данных
			mysqli_query($db, "UPDATE user SET name = \"$user_name\", company = \"$user_company\", email = \"$user_email\", address = \"$user_address\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о пользователе</p>");
		}
		else //Добавление нового пользователя
		{
			//Получаем ответ от ReCaptcha
			$recaptcha = $_POST['g-recaptcha-response'];
 
			if(!empty($recaptcha)) 
			{
				//Получаем HTTP от ReCaptcha
				$recaptcha = $_REQUEST['g-recaptcha-response'];
				//Секретный ключ
				$secret = $recaptcha_secret_key;
				//Формируем utl адрес для запроса на сервер Гугл
				$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$secret ."&response=".$recaptcha."&remoteip=".$_SERVER['REMOTE_ADDR'];
 
				//Инициализация и настройка запроса
				$curl = curl_init();
		
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		
				//Выполняем запрос и получаем ответ от сервера Гугл
				$curlData = curl_exec($curl);

				curl_close($curl); 
		
				//Декодируем ответ
				$curlData = json_decode($curlData, true);
 
				//Проверяем ответ
				if($curlData['success']) 
				{
					//Шифруем пароль   
					$user_password = md5(md5($user_password));
			
					//Запись пользователя в базу данных
					mysqli_query($db, "INSERT INTO user (name, company, email, address, password) VALUES (\"$user_name\", \"$user_company\", \"$user_email\", \"$user_address\", \"$user_password\")") or die ("<p>Не удалось добавить нового пользователя</p>");
			
					//Получаем идентификатор пользователя
					$user_id = mysqli_insert_id($db);
			
					//Создаем папку для картинок
					mkdir("$site_root_folder/img/$user_id");
			
					//Сохраняем параметры входа
					setcookie("id", $user_id, time() + 60 * 60 * 24 * 30, "/");
					setcookie("password", $user_password, time() +  60 * 60 * 24 * 30, "/");
				}
				else 
				{
					die ("<p>Ошибка добавления пользователя. Не прошла проверка на Спам.</p>");
				}
			}
			else
			{
				die ("<p>Ошибка добавления пользователя. Не заполнена капча.</p>");
			}
		}
		
		//Переадресация на главную страницу
		header("Location: /index.php"); 
		
		exit;
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
	
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	
		<title>Профиль пользователя</title>
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
	echo "			<form id = 'frmUserProfile' method = 'post' action='user_profile.php?id=$id'>	
						<div class='mb-3'>
							<h4 class = 'text-center'>Профиль пользователя</h4>
						</div>	
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserName'>Имя пользователя:</label>
							<input class = 'form-control' name='edtUserName' type='text' value='$user_name' placeholder='Контактное лицо'>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserCompany'>Компания:</label>
							<input class = 'form-control' name='edtUserCompany' type='text' value='$user_company' placeholder='Компания'>
						</div>	
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserEmail'>Электронная почта:</label>
							<input class = 'form-control' name='edtUserEmail' type='email' value='$user_email' placeholder='Электронная почта пользователя'>
						</div>	
						<div class='mb-3'>
							<label class = 'form-label' for='edtUserAddress'>Адрес:</label>
							<input class = 'form-control' name='edtUserAddress' type='text' value='$user_address' placeholder='Адрес пользователя'>
						</div>";	
	
	if($id === 0)
		echo "			<div class='mb-3'>
							<label class = 'form-label' for='edtUserPassword'>Пароль:</label>
							<input class = 'form-control' name='edtUserPassword' type='password' value='' placeholder='Пароль для входа'>
						</div>
						
						<div class='mb-3 d-flex justify-content-center'>
							<div class='g-recaptcha' data-sitekey='$recaptcha_site_key'></div>
						</div>";

	echo "				<div class='row justify-content-center'>
							<div class='mb-3 text-center'>
								<button type='submit' class='btn btn-primary' name='btnUserSave'>Сохранить</button>
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