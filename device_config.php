<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор элемента в БД
	$id = $_GET['id'];
	
	//Сохранение устройства в БД
	if(isset($_POST['btnDeviceSave']))
	{		
		//Получаем значения введенные пользователем 
		$device_name = $_POST['edtDeviceName'];  
		$device_rev = $_POST['edtDeviceRev'];  
		$device_ver = $_POST['edtDeviceVer'];  
		
		$image_file_name = $_FILES['edtDeviceImage']['name'];
		$image_file_tmp = $_FILES['edtDeviceImage']['tmp_name'];
		
		//Копируем файл с картинкой
		if($image_file_name != "")
			move_uploaded_file($image_file_tmp, "/var/www/html/e-fabrika.pro/img/$user_id/$image_file_name");
	
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации об устройстве
		{
			//Обновление информации об устройстве в базе данных
			mysqli_query($db, "UPDATE device_list SET name = \"$device_name\", revision = \"$device_rev\", version = \"$device_ver\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию об устройстве</p>");
			
			if($image_file_name != "")
				mysqli_query($db, "UPDATE device_list SET image = \"$image_file_name\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию об устройстве</p>");
			
			//Переходим на страницу списка устройств
			header("Location: device_list.php"); exit();
		}
		else //Добавление нового устройства
		{
			//Запись нового устройства в базу данных
			mysqli_query($db, "INSERT INTO device_list (name, revision, version, image, user_id) VALUES (\"$device_name\", \"$device_rev\", \"$device_ver\", \"$image_file_name\", \"$user_id\")") or die ("<p>Не удалось добавить новое устройство</p>");
			
			//Получаем идентификатор только что добавленного устройства
			$new_device_id = mysqli_insert_id($db);
			
			//Возвращаемся на страницу редактирования устройства 
			header("Location: device_config.php?id=$new_device_id"); exit();
		}
	}
	
	//Удаление устройства из БД
	if(isset($_POST['btnDeviceDelete']))
	{
		//Удаляем устройство из БД
		mysqli_query($db, "DELETE FROM device_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить устройство</p>");
		
		//Удаляем связанные с устройством элементы и платы
		mysqli_query($db, "DELETE FROM device_spec WHERE device_id = \"$id\"") or die ("<p>Не удалось удалить связанные с устройством элементы и платы<p>");
		
		//Переходим на страницу списка плат
		header("Location: device_list.php"); exit();
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

		<title>Устройство</title>
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
		//Загружаем информацию об устройстве
		$query_result = mysqli_query($db,  "SELECT 
												name, 
												revision,
												version,
												image,
												user_id
											FROM device_list
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$device_name = $row['name'];
		$device_rev = $row['revision'];
		$device_ver = $row['version'];
		$device_image = $row['image'];
		$device_user_id = $row['user_id'];
		
		//Проверяем принадлежность устройства пользователю
		if($device_user_id != $user_id)
			die("Ошибка загрузки информации об устройстве");
		
		//Проверяем связанность записи в базе данных
		$query_result = mysqli_query($db,  "SELECT 
												COUNT(*) AS usage_count 
											FROM disposal_spec 
											WHERE item_id = \"$id\" AND item_type = \"device\"");

		$row = mysqli_fetch_array($query_result);

		if($row['usage_count'] > 0)
			$del_btn_enable = false;
		else
			$del_btn_enable = true;
	}
	
	echo "			<form id = 'frmDeviceConfig' method = 'post' action='device_config.php?id=$id'>	
						<div class='modal fade' id='device_delete_confirm' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<h5 class='modal-title' id='staticBackdropLabel'>Подтверждение удаления устройства</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Закрыть'></button>
									</div>
									<div class='modal-body'>
										<p>Подтверждаете удаление устройства? Операцию не возможно будет отменить.</p>
									</div>
									<div class='modal-footer'>
										<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Отмена</button>
										<button type='submit' name='btnDeviceDelete' class='btn btn-primary'>Удалить устройство</button>
									</div>
								</div>
							</div>
						</div>
						<div class='mb-3'>
							<h4 class = 'text-center'>Устройство</h4>
						</div>
						<div class = 'row'>
							<div class = 'col-8'>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtdeviceName'>Название:</label>
									<input class = 'form-control' name = 'edtDeviceName' type='text' value='$device_name' placeholder='Название устройства'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtDeviceRev'>Ревизия:</label>
									<input class = 'form-control' name = 'edtDeviceRev' type='text' value='$device_rev' placeholder='Ревизия устройства'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtDeviceVer'>Исполнение:</label>
									<input class = 'form-control' name = 'edtDeviceVer' type='text' value='$device_ver' placeholder='Исполнение устройства'>
								</div>
								
								
								<div class='mb-3'>
									<label class = 'form-label' for = 'edtDeviceImage'>Изображение для устройства:</label>
									<input class = 'form-control' type='file' id = 'edtdeviceImage' name = 'edtDeviceImage'>
								</div>
							</div>
							<div class = 'col-4 align-self-center'>
								<img class = 'img-fluid img-thumbnail' src = '/img/" . ($device_image ? "$user_id/$device_image" : "no_image.png") . "'></img>
							</div>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblBoardList'>Перечень плат:</label>
							<table class = 'table table-bordered table-sm' name = 'tblBoardList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Плата</th>
										<th>Ревизия</th>
										<th>Исполнение</th>
										<th>Количество</th>
									</tr>
								</thead>
								<tbody>";			
		
	//Загружаем входящие в устройство платы
	$query_result = mysqli_query($db,  "SELECT 
											board_list.name AS board_name,
											board_list.revision AS board_rev,
											board_list.version AS board_ver,
											device_spec.count AS board_count,
											device_spec.id AS spec_item_id
										FROM device_spec INNER JOIN board_list ON device_spec.item_id = board_list.id
										WHERE device_spec.device_id = \"$id\" AND device_spec.item_type = \"board\"");
								
	$row_count = mysqli_num_rows($query_result);				
		
	if($row_count)
	{
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$board_name = $row['board_name'];
			$board_rev = $row['board_rev'];
			$board_ver = $row['board_ver'];
			$board_count = $row['board_count'];
			$spec_item_id = $row['spec_item_id'];
	
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>
										<td><a class = 'text-decoration-none' href='device_board_config.php?device_id=$id&spec_item_id=$spec_item_id'>$board_name</a></td>
										<td>$board_count</td>
										<td>$board_rev</td>
										<td>$board_ver</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=5>Нет плат связанных с устройством</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblPartList'>Перечень элементов:</label>
							<table class = 'table table-bordered table-sm' name = 'tblPartList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Тип элемента</th>
										<th>Элемент</th>
										<th>Количество</th>
									</tr>
								</thead>
								<tbody>";			
		
	//Загружаем входящие в устройство элементы
	$query_result = mysqli_query($db,  "SELECT 
											part_type.name AS part_type_name,
											part_list.name AS part_name,
											device_spec.count AS part_count,
											device_spec.id AS spec_item_id
										FROM device_spec INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON device_spec.item_id = part_list.id 
										WHERE device_spec.device_id = \"$id\" AND device_spec.item_type = \"part\"");
								
	$row_count = mysqli_num_rows($query_result);				
		
	if($row_count)
	{
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$part_type_name = $row['part_type_name'];
			$part_name = $row['part_name'];
			$part_count = $row['part_count'];
			$spec_item_id = $row['spec_item_id'];
	
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>
										<td>$part_type_name</td>
										<td><a class = 'text-decoration-none' href='device_part_config.php?device_id=$id&spec_item_id=$spec_item_id'>$part_name</a></td>
										<td>$part_count</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=4>Нет элементов связанных с устройством</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	
	if(isset($id) && $del_btn_enable)
		echo "					<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#device_delete_confirm'>Удалить устройство</button>";
	
	if(isset($id))
		echo "					<button type='button' class='btn btn-primary' name='btnDeviceCopy' onclick=\"location.href='device_copy.php?id=$id'\">Создать копию</button>";
	
	echo " 						<button type='submit' class='btn btn-primary' name='btnDeviceSave'>Сохранить устройство</button>";
	
	if(isset($id))							
		echo "					<button type='button' class='btn btn-primary' name='btnDevicePart' onclick=\"location.href='device_part_config.php?device_id=$id'\">Добавить элемент</button>
								<button type='button' class='btn btn-primary' name='btnDeviceBoard' onclick=\"location.href='device_board_config.php?device_id=$id'\">Добавить плату</button>";
	
	echo "					</div>
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