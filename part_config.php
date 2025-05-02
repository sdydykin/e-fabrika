<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор элемента в БД
	$id = $_GET['id'];
	
	//Сохранение элемента в БД
	if(isset($_POST['btnPartSave']))
	{		
		//Получаем значения введенные пользователем 
		$part_name = $_POST['edtPartName']; 
		$part_type_id = $_POST['selPartType']; 
		$part_description = $_POST['edtPartDescription'];
		$part_storage = $_POST['edtPartStorage'];
		
		$image_file_name = $_FILES['edtPartImage']['name'];
		$image_file_tmp = $_FILES['edtPartImage']['tmp_name'];
		
		//Копируем файл с картинкой
		if($image_file_name != "")
			move_uploaded_file($image_file_tmp, "/var/www/html/e-fabrika.pro/img/$user_id/$image_file_name");
		
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о элементе
		{
			//Обновление информации о элементе в базе данных
			mysqli_query($db, "UPDATE part_list SET name = \"$part_name\", description = \"$part_description\", storage = \"$part_storage\", type_id = \"$part_type_id\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о элементе</p>");
		
			if($image_file_name != "")
				mysqli_query($db, "UPDATE part_list SET image = \"$image_file_name\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о элементе</p>");

		}
		else //Добавление нового элемента
		{
			//Запись нового элемента в базу данных
			mysqli_query($db, "INSERT INTO part_list (name, description, storage, type_id, image, user_id) VALUES (\"$part_name\", \"$part_description\", \"$part_storage\", \"$part_type_id\", \"$image_file_name\", \"$user_id\")") or die ("<p>Не удалось добавить новый элемент</p>");
		}
		
		//Переходим на страницу списка элементов
		header("Location: part_list.php"); exit();
	}
	
	//Удаление элемента из БД
	if(isset($_POST['btnPartDelete']))
	{
		//Удаляем элемент из БД
		mysqli_query($db, "DELETE FROM part_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить элемент</p>");
		
		//Переходим на страницу списка элементов
		header("Location: part_list.php"); exit();
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

		<title>Элемент</title>
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
		//Загружаем информацию об элементe
		$query_result = mysqli_query($db,  "SELECT 
												name,
												type_id,
												description,
												storage,
												image,
												user_id
											FROM part_list
											WHERE id = \"$id\"");

		$row = mysqli_fetch_array($query_result);
		
		$part_name        = $row['name'];
		$part_type_id     = $row['type_id'];
		$part_description = $row['description'];
		$part_storage     = $row['storage'];
		$part_image       = $row['image'];
		$Part_user_id     = $row['user_id'];
		
		//Проверяем принадлежность элемента пользователю
		if($Part_user_id != $user_id)
			die("Ошибка загрузки информации об элементе");
		
		//Проверяем связанность записи в базе данных
		$query_result = mysqli_query($db,  "SELECT 
												(
													(SELECT COUNT(*) AS usage_count FROM receipt_spec WHERE part_id = \"$id\") + 
													(SELECT COUNT(*) AS usage_count FROM board_spec WHERE part_id = \"$id\") +	
													(SELECT COUNT(*) AS usage_count FROM device_spec WHERE item_id = \"$id\" AND item_type = \"part\") +
													(SELECT COUNT(*) AS usage_count FROM disposal_spec WHERE item_id = \"$id\" AND item_type = \"part\")
												) AS usage_count");

		$row = mysqli_fetch_array($query_result);
		
		if($row['usage_count'] > 0)
			$del_btn_enable = false;
		else
			$del_btn_enable = true;
	}
			
	echo "			<form id = 'frmPartConfig' enctype='multipart/form-data' method = 'post' action='part_config.php?id=$id'>	
						<div class='modal fade' id='part_delete_confirm' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<h5 class='modal-title' id='staticBackdropLabel'>Подтверждение удаления элемента</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Закрыть'></button>
									</div>
									<div class='modal-body'>
										<p>Подтверждаете удаление элемента? Операцию не возможно будет отменить.</p>
									</div>
									<div class='modal-footer'>
										<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Отмена</button>
										<button type='submit' name='btnPartDelete' class='btn btn-primary'>Удалить элемент</button>
									</div>
								</div>
							</div>
						</div>
						<div class=class = 'mb-3'>
							<h4 class = 'text-center'>Элемент</h4>
						</div>
						<div class = 'row'>
							<div class = 'col-8'>
								<div class='mb-3'>
									<label class = 'form-label' for='selPartType'>Тип элемента:</label>			
									<select class='form-select' name='selPartType'>";
								
	//Загружаем список типов элементов из БД
	$query_result = mysqli_query($db, "SELECT 
											id, 
											name
										FROM part_type
										WHERE user_id = $user_id
										ORDER BY name");
								
	$row_count = mysqli_num_rows($query_result);	
									
	if($row_count != 0)
	{
		echo "							<option value='0'>Выберите тип элемента</option>";
										
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
										
			$type_id = $row['id']; 
			$name = $row['name']; 
										
			echo "						<option value='$type_id' " . (($type_id == $part_type_id) ? "selected" : "") . ">$name</option>";
		}
	}	
	else
		echo "							<option value='-1'>Типы элементов отсутствуют в системе</option>";
								
	echo "							</select>
								</div>					
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtPartName'>Название:</label>
									<input class = 'form-control' name = 'edtPartName' type='text' value='$part_name' placeholder='Название для элемента'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtPartDescription'>Описание:</label>
									<input class = 'form-control' name = 'edtPartDescription' type='text' value='$part_description' placeholder='Описание для элемента'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtPartStorage'>Место хранения:</label>
									<input class = 'form-control' name = 'edtPartStorage' type='text' value='$part_storage' placeholder='Место хранения элемента'>
								</div>
								<div class='mb-3'>
									<label class = 'form-label' for = 'edtPartImage'>Изображение для элемента:</label>
									<input class = 'form-control' type='file' id = 'edtPartImage' name = 'edtPartImage'>
								</div>
							</div>
							<div class = 'col-4 align-self-center'>
								<img class = 'img-fluid img-thumbnail' src = '/img/" . ($part_image ? "$user_id/$part_image" : "no_image.png") . "'></img>
							</div>
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($id) && $del_btn_enable)							
		echo "					<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#part_delete_confirm'>Удалить элемент</button>";
		
	if(isset($id))
		echo "					<button type='button' class='btn btn-primary' name='btnPartCopy' onclick=\"location.href='part_copy.php?id=$id'\">Создать копию</button>";
		
	echo " 						<button type='submit' class='btn btn-primary' name='btnPartSave'>Сохранить элемент</button>
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