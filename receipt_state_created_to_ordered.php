<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор поставки в БД
	$id = $_GET['receipt_id'];
	
	//Получаем информацию о поступлении элементов
	$receipt_list_query = mysqli_query($db,    "SELECT 
													delivery_cost, 
													status,
													user_id
												FROM receipt_list
												WHERE id = $id");

	$receipt_list_row = mysqli_fetch_array($receipt_list_query);
		
	$receipt_delivery_cost = $receipt_list_row['delivery_cost'];
	$receipt_status = $receipt_list_row['status'];
	$receipt_user_id = $receipt_list_row['user_id'];
	
	//Проверяем принадлежность поступления элементов пользователю
	if($receipt_user_id != $user_id)
		die("Ошибка передачи элементов в заказ");
			
	//Исключаем возможможность повторной передачи элементов на склад
	if($receipt_status != 'CREATED')
		die("Ошибка передачи элементов в заказ");
	
	//Рассчитываем общую стоимость элементов
	$receipt_spec_query = mysqli_query($db,    "SELECT SUM(cost * count) AS parts_total_cost
												FROM receipt_spec
												WHERE receipt_id = \"$id\"");
	
	$receipt_spec_row = mysqli_fetch_array($receipt_spec_query);
		
	$parts_total_cost = $receipt_spec_row['parts_total_cost'];
	
	//Рассчитываем наценку за доставку на каждый рубль цены элемента	
	$receipt_total_cost = $parts_total_cost + $receipt_delivery_cost; 
	
	if($parts_total_cost != 0)
		$delivery_coef = 1 + $receipt_delivery_cost / $parts_total_cost;
	else 
		$delivery_coef = 0;
	
	//Получаем список элементов перемещаемых в заказ
	$receipt_spec_query = mysqli_query($db,    "SELECT 
													cost,
													count,
													part_id
												FROM receipt_spec 
												WHERE receipt_spec.receipt_id = \"$id\"");

	$receipt_spec_row_count = mysqli_num_rows($receipt_spec_query);

	for($i = 0; $i < $receipt_spec_row_count; $i++)
	{
		$receipt_spec_row = mysqli_fetch_array($receipt_spec_query);
		
		$add_part_cost  = $receipt_spec_row['cost'] * $delivery_coef;
		$add_part_count = $receipt_spec_row['count'];
		$part_id    = $receipt_spec_row['part_id'];

		//Получаем текущую цену и количество заказанных элементов
		$part_list_query = mysqli_query($db,   "SELECT 
													cost,
													count,
													order_count
												FROM part_list
												WHERE id = \"$part_id\"");
		
		$part_list_row = mysqli_fetch_array($part_list_query);
		
		
		$current_part_cost  = $part_list_row['cost'];
		$current_part_count = $part_list_row['count'];
		$current_part_order_count = $part_list_row['order_count'];
		
		$current_part_total_count = $current_part_count + $current_part_order_count;
		
		//Рассчитываем новую цену и количество элементов на складе
		$new_part_cost = ($add_part_cost * $add_part_count + $current_part_cost * $current_part_total_count) / ($add_part_count + $current_part_total_count);
		$new_part_count = $add_part_count + $current_part_order_count;
		
		//Обновляем текущую цену и количество заказанных элементов
		mysqli_query($db, "UPDATE part_list SET cost = \"$new_part_cost\", order_count = \"$new_part_count\" WHERE id = \"$part_id\"");
	}
	
	//Меняем статус поставки на "Заказано"
	mysqli_query($db, "UPDATE receipt_list SET status = 'ORDERED' WHERE id = \"$id\"");
		
	//Возвращаемся на страницу редактирования поставки
	header("Location: receipt_config.php?id=$id"); exit();
?>
