<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор элемента спецификации в БД
	$spec_item_id = $_GET['spec_item_id'];
	
	//Идентификатор списания элементов в БД
	$disposal_id = $_GET['disposal_id'];
	
	//Сохранение элемента спецификации в БД
	if(isset($_POST['btnSpecItemSave']))
	{		
		//Получаем значения введенные пользователем 
		$spec_device_id = $_POST['selDevice'];   
		$spec_device_count = $_POST['edtCount'];  
	
		//Определяем стоимость входящих в устройство элементов
		$query_result = mysqli_query($db,  "SELECT 
												SUM(part_list.cost * device_spec.count) AS parts_cost 
											FROM device_spec INNER JOIN part_list ON device_spec.item_id = part_list.id 
											WHERE device_spec.device_id = $spec_device_id AND device_spec.item_type = \"part\"");

		$row = mysqli_fetch_array($query_result);
		
		$spec_parts_cost = $row['parts_cost'];
		
		//Определяем стоимость входящих в устройство плат
		$query_result = mysqli_query($db,  "SELECT 
												SUM((SELECT 
														SUM(part_list.cost * board_spec.count) 
													 FROM board_spec INNER JOIN part_list ON board_spec.part_id = part_list.id 
													 WHERE board_spec.board_id = device_spec.item_id) * device_spec.count) AS boards_cost 
											FROM device_spec 
											WHERE device_spec.device_id = $spec_device_id AND device_spec.item_type = \"board\"");

		$row = mysqli_fetch_array($query_result);
		
		$spec_boards_cost = $row['boards_cost'];

		//Определяем текущую цену устройства				
		$spec_device_cost = $spec_parts_cost + $spec_boards_cost;
		
		//Сохраняем информацию в БД
		if(is_numeric($spec_item_id)) //Редактирование информации об элементе спецификации в БД
		{
			//Обновление информации об элементе спецификации в БД
			mysqli_query($db, "UPDATE disposal_spec SET cost = \"$spec_device_cost\", count = \"$spec_device_count\", item_id = \"$spec_device_id\" WHERE id = \"$spec_item_id\"") or die ("<p>Не удалось обновить информацию об элементе спецификации в БД</p>");
		}
		else //Добавление нового элемента спецификации
		{
			//Запись нового элемента спецификации в базе данных
			mysqli_query($db, "INSERT INTO disposal_spec (cost, count, item_id, disposal_id, item_type) VALUES (\"$spec_device_cost\", \"$spec_device_count\", \"$spec_device_id\", \"$disposal_id\", \"device\")") or die ("<p>Не удалось добавить элемент спецификации</p>");
		}
		
		//Переходим на страницу редактирования списаний элементов
		header("Location: disposal_config.php?id=$disposal_id"); exit();
	}
	
	//Удаление элемента спецификации из БД
	if(isset($_POST['btnSpecItemDelete']))
	{
		//Удаляем элемент спецификации из БД
		mysqli_query($db, "DELETE FROM disposal_spec WHERE id = \"$spec_item_id\"") or die ("<p>Не удалось удалить элемент спецификации</p>");
		
		//Переходим на страницу редактирования списания элементов
		header("Location: disposal_config.php?id=$disposal_id"); exit();
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

		<title>Устройство входящее в списание</title>
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
	if(isset($spec_item_id))
	{
		//Загружаем информацию об устройстве
		$query_result = mysqli_query($db,  "SELECT 
												disposal_spec.item_id AS device_id,
												disposal_spec.count AS count,
												device_list.user_id AS device_user_id
											FROM disposal_spec INNER JOIN device_list ON disposal_spec.item_id = device_list.id 
											WHERE disposal_spec.id = $spec_item_id");

		$row = mysqli_fetch_array($query_result);
		
		$spec_device_id = $row['device_id'];
		$spec_device_count = $row['count'];
		$spec_device_user_id = $row['device_user_id'];
		
		//Проверяем принадлежность платы пользователю
		if($spec_device_user_id != $user_id)
			die("Ошибка загрузки информации о плате входящей в списание");
	}
	
	echo "			<form id = 'frmSpecItemConfig' method = 'post' action='disposal_device_config.php?disposal_id=$disposal_id&spec_item_id=$spec_item_id'>	
						<div class='mb-3'>
							<h4 class = 'text-center'>Устройство входящее в списание</h4>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='selDevice'>Устройство:</label>			
							<select class='form-select' name='selDevice'>";
								
	//Загружаем список устройств из БД
	$device_query_result = mysqli_query($db,   "SELECT 
													id, 
													name,
													revision, 
													version
												FROM device_list
												WHERE user_id = $user_id
												ORDER BY name, revision, version");
								
	$device_row_count = mysqli_num_rows($device_query_result);	
									
	if($device_row_count != 0)
	{
		echo "					<option value='0'>Выберите устройство</option>";
										
		for($i = 0; $i < $device_row_count; $i++)
		{
			$device_row = mysqli_fetch_array($device_query_result);
										
			$device_id = $device_row['id']; 
			$device_name = $device_row['name']; 
			$device_revision = $device_row['revision']; 
			$device_version = $device_row['version']; 
										
			echo "				<option value='$device_id' " . (($device_id == $spec_device_id) ? "selected" : "") . ">$device_name (ревизия $device_revision, исполнение $device_version)</option>";
		}
	}	
	else
		echo "					<option value='-1'>Устройства отсутствуют в системе</option>";
								
	echo "					</select>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtCount'>Количество:</label>
							<input class = 'form-control' name = 'edtCount' type='text' value='$spec_device_count' placeholder='Количество устройств в списании'>
						</div>						  
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($spec_item_id))							
		echo "					<button type='submit' class='btn btn-primary' name='btnSpecItemDelete'>Удалить</button>";
		
	echo " 						<button type='submit' class='btn btn-primary' name='btnSpecItemSave'>Сохранить</button>
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