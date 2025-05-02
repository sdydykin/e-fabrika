<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор типа элемента в БД
	$id = $_GET['id'];
	
	//Сохранение типа элемента в БД
	if(isset($_POST['btnPartTypeSave']))
	{		
		//Получаем значения введенные пользователем 
		$part_type_name = $_POST['edtPartTypeName']; 
		
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о типе элемента
		{
			//Обновление информации о типе элемента в базе данных
			mysqli_query($db, "UPDATE part_type SET name = \"$part_type_name\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о типе элемента</p>");
		}
		else //Добавление нового типа элемента
		{
			//Запись нового типа элемента в базу данных
			mysqli_query($db, "INSERT INTO part_type (name, user_id) VALUES (\"$part_type_name\", \"$user_id\")") or die ("<p>Не удалось добавить новый тип элемента</p>");
		}
		
		//Переходим на страницу списка типов элементов
		header("Location: part_type_list.php"); exit();
	}
	
	//Удаление типа элемента из БД
	if(isset($_POST['btnPartTypeDelete']))
	{
		//Удаляем тип элемента из БД
		mysqli_query($db, "DELETE FROM part_type WHERE id = \"$id\"") or die ("<p>Не удалось удалить тип элемента</p>");
		
		//Переходим на страницу списка типов элементов
		header("Location: part_type_list.php"); exit();
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

		<title>Тип элемента</title>
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
	if(isset($id))
	{
		//Загружаем информацию о типе элемента
		$query_result = mysqli_query($db,  "SELECT 
												name,
												user_id
											FROM part_type
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$part_type_name = $row['name']; 
		$part_type_user_id = $row['user_id'];
		
		//Проверяем принадлежность типа элемента пользователю
		if($part_type_user_id != $user_id)
			die("Ошибка загрузки информации о типе элемента");
			
		//Проверяем связанность записи в базе данных
		$query_result = mysqli_query($db,  "SELECT 
												COUNT(*) AS usage_count
											FROM part_list
											WHERE type_id = \"$id\"");

		$row = mysqli_fetch_array($query_result);
		
		if($row['usage_count'] > 0)
			$del_btn_enable = false;
		else
			$del_btn_enable = true;
	}
			
	echo "			<form id = 'frmPartTypeConfig' method = 'post' action='part_type_config.php?id=$id'>	
						<div class = 'mb-3'>
							<h4 class = 'text-center'>Тип элемента</h4>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtPartTypeName'>Название:</label>
							<input class = 'form-control' name = 'edtPartTypeName' type='text' value='$part_type_name' placeholder='Название для группы элементов'>
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($id) && $del_btn_enable)							
		echo "					<button type='submit' class='btn btn-primary' name='btnPartTypeDelete'>Удалить</button>";
		
	echo " 						<button type='submit' class='btn btn-primary' name='btnPartTypeSave'>Сохранить</button>
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