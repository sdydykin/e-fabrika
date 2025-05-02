<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор поставщика элементов в БД
	$id = $_GET['id'];
	
	//Сохранение поставщика элементов в БД
	if(isset($_POST['btnSupplierSave']))
	{		
		//Получаем значения введенные пользователем 
		$supplier_name = $_POST['edtSupplierName']; 
		
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о Поставщике элементов
		{
			//Обновление информации о поставщике элементов в базе данных
			mysqli_query($db, "UPDATE supplier_list SET name = \"$supplier_name\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о поставщике элементов</p>");
		}
		else //Добавление нового поставщика элементов
		{
			//Запись нового поставщика элементов в базу данных
			mysqli_query($db, "INSERT INTO supplier_list (name, user_id) VALUES (\"$supplier_name\", \"$user_id\")") or die ("<p>Не удалось добавить нового поставщика элементов</p>");
		}
		
		//Переходим на страницу списка типов элементов
		header("Location: supplier_list.php"); exit();
	}
	
	//Удаление поставщика элементов из БД
	if(isset($_POST['btnSupplierDelete']))
	{
		//Удаляем поставщика элементов из БД
		mysqli_query($db, "DELETE FROM supplier_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить поставщика элементов</p>");
		
		//Переходим на страницу списка поставщиков элементов
		header("Location: supplier_list.php"); exit();
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

		<title>Поставщик элементов</title>
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
											FROM supplier_list
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$supplier_name = $row['name']; 
		$supplier_user_id = $row['user_id'];
		
		//Проверяем принадлежность поставщика пользователю
		if($supplier_user_id != $user_id)
			die("Ошибка загрузки информации о поставщике");
		
		//Проверяем связанность записи в базе данных
		$query_result = mysqli_query($db,  "SELECT 
												COUNT(*) AS usage_count
											FROM receipt_list
											WHERE supplier_id = \"$id\"");

		$row = mysqli_fetch_array($query_result);
		
		if($row['usage_count'] > 0)
			$del_btn_enable = false;
		else
			$del_btn_enable = true;
	}
			
	echo "			<form id = 'frmSupplierConfig' method = 'post' action='supplier_config.php?id=$id'>	
						<div class = 'mb-3'>
							<h4 class = 'text-center'>Поставщик элементов</h4>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtSupplierName'>Наименование:</label>
							<input class = 'form-control' name = 'edtSupplierName' type='text' value='$supplier_name' placeholder='Название поставщика элементов'>
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($id) && $del_btn_enable)							
		echo "					<button type='submit' class='btn btn-primary' name='btnSupplierDelete'>Удалить</button>";
		
	echo " 						<button type='submit' class='btn btn-primary' name='btnSupplierSave'>Сохранить</button>
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