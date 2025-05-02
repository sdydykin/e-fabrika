<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор списания в БД
	$id = $_GET['disposal_id'];
	
	//Получаем информацию о списании элементов
	$query_result = mysqli_query($db,  "SELECT 
											status,
											user_id
										FROM disposal_list
										WHERE id = $id");

	$row = mysqli_fetch_array($query_result);
		
	$disposal_status = $row['status'];
	$disposal_user_id = $row['user_id'];
	
	//Проверяем принадлежность списания элементов пользователю
	if($disposal_user_id != $user_id)
		die("Ошибка резервирования элементов");
			
	//Исключаем возможможность повторного резервирования элементов
	if($disposal_status != 'CREATED')
		die("Ошибка резервирования элементов");
	
	//Загружаем данные из БД
	$query_result = mysqli_query($db,  "SELECT 
											part_id,
											SUM(part_count) AS total_part_count
										FROM
										(
											(SELECT 
												disposal_spec.item_id AS part_id,
												disposal_spec.count AS part_count
											FROM disposal_spec
											WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"part\")
												
											UNION ALL
												
											(SELECT 
												board_spec.part_id AS part_id,
												disposal_spec.count * board_spec.count AS part_count
											FROM disposal_spec INNER JOIN board_spec ON disposal_spec.item_id = board_spec.board_id
											WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"board\")
											
											UNION ALL
												
											(SELECT 
												device_spec.item_id AS part_id,
												disposal_spec.count * device_spec.count AS part_count
											FROM disposal_spec INNER JOIN device_spec ON disposal_spec.item_id = device_spec.device_id
											WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"device\" AND device_spec.item_type = \"part\")
												
											UNION ALL
												
											(SELECT 
												board_spec.part_id AS part_id,
												disposal_spec.count * device_spec.count * board_spec.count AS part_count
											FROM disposal_spec INNER JOIN (device_spec INNER JOIN board_spec ON device_spec.item_id = board_spec.board_id) ON disposal_spec.item_id = device_spec.device_id
											WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"device\" AND device_spec.item_type = \"board\")
										) AS parts_in_disposal
										GROUP BY part_id");
										
	$row_count = mysqli_num_rows($query_result);	
		
	for($i = 0; $i < $row_count; $i++)
	{
		$row = mysqli_fetch_array($query_result);
			
		$part_id = $row['part_id'];
		$part_count = $row['total_part_count'];

		//Обновляем текущее количество зарезервированных элементов
		mysqli_query($db, "UPDATE part_list SET reserve_count = (reserve_count + \"$part_count\") WHERE id = \"$part_id\"");
	}
	
	//Изменяем статус на "Зарезервировано"
	mysqli_query($db, "UPDATE disposal_list SET status = 'RESERVED' WHERE id = \"$id\"");

	//Переходим на страницу редактирования списания
	header("Location: disposal_config.php?id=$id"); exit();
?>
