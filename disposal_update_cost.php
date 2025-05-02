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
		die("Ошибка обновления цены");
			
	//Исключаем возможможность пересчета цены списанным и зарезервированным элементам
	if($disposal_status != 'CREATED')
		die("Ошибка обновления цены");
	
	//Получаем элементы входящие в списание
	$disposal_spec_result = mysqli_query($db,  "SELECT 
													disposal_spec.item_id,
													disposal_spec.item_type,
													disposal_spec.cost,
													disposal_spec.id
												FROM disposal_spec 
												WHERE disposal_spec.disposal_id = \"$id\"");
								
	$disposal_spec_row_count = mysqli_num_rows($disposal_spec_result);		
	
	for($i = 0; $i < $disposal_spec_row_count; $i++)
	{
		$disposal_spec_row = mysqli_fetch_array($disposal_spec_result);
		
		$disposal_spec_item_id   = $disposal_spec_row['item_id'];
		$disposal_spec_item_type = $disposal_spec_row['item_type'];
		$disposal_spec_item_cost = $disposal_spec_row['cost'];
		$disposal_spec_id        = $disposal_spec_row['id'];
		
		if($disposal_spec_item_type == 'part')
		{
			//Определяем новую цену элемента
			$part_cost_result = mysqli_query($db,  "SELECT cost FROM part_list WHERE id = $disposal_spec_item_id");

			$part_cost_row = mysqli_fetch_array($part_cost_result);
		
			$new_cost = $part_cost_row['cost'];
		}
		else if($disposal_spec_item_type == 'board')
		{
			//Определяем новую цену платы
			$board_cost_result = mysqli_query($db,     "SELECT 
															SUM(part_list.cost * board_spec.count) AS board_cost 
														FROM board_spec INNER JOIN part_list ON board_spec.part_id = part_list.id 
														WHERE board_spec.board_id = $disposal_spec_item_id");

			$board_cost_row = mysqli_fetch_array($board_cost_result);
		
			$new_cost = $board_cost_row['board_cost'];
		}
		else if($disposal_spec_item_type == 'device')
		{
			//Определяем новую стоимость входящих в устройство элементов
			$part_cost_result = mysqli_query($db,  "SELECT 
														SUM(part_list.cost * device_spec.count) AS parts_cost 
													FROM device_spec INNER JOIN part_list ON device_spec.item_id = part_list.id 
													WHERE device_spec.device_id = $disposal_spec_item_id AND device_spec.item_type = \"part\"");

			$part_cost_row = mysqli_fetch_array($part_cost_result);
			
			$new_parts_cost = $part_cost_row['parts_cost'];
		
			//Определяем новую стоимость входящих в устройство плат
			$board_cost_result = mysqli_query($db, "SELECT 
														SUM(
															(SELECT 
																SUM(part_list.cost * board_spec.count) 
															FROM board_spec INNER JOIN part_list ON board_spec.part_id = part_list.id 
															WHERE board_spec.board_id = device_spec.item_id) * device_spec.count
														) AS boards_cost 
													FROM device_spec 
													WHERE device_spec.device_id = $disposal_spec_item_id AND device_spec.item_type = \"board\"");

			$board_cost_row = mysqli_fetch_array($board_cost_result);
		
			$new_boards_cost = $board_cost_row['boards_cost'];

			//Определяем новую цену устройства				
			$new_cost = $new_parts_cost + $new_boards_cost;
		}
		
		if(round($new_cost, 1) != round($disposal_spec_item_cost, 1))
		{
			//Обновление информации о цене
			mysqli_query($db, "UPDATE disposal_spec SET cost = \"$new_cost\" WHERE id = \"$disposal_spec_id\"");
		}
	}
	
	//Переходим на страницу редактирования списания
	header("Location: disposal_config.php?id=$id"); exit();
?>
