<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор поставки в БД
	$id = $_GET['receipt_id'];
	
	//Получаем информацию о поступлении элементов
	$receipt_list_query = mysqli_query($db,    "SELECT 
													status,
													user_id
												FROM receipt_list
												WHERE id = $id");

	$receipt_list_row = mysqli_fetch_array($receipt_list_query);
		
	$receipt_status = $receipt_list_row['status'];
	$receipt_user_id = $receipt_list_row['user_id'];
	
	//Проверяем принадлежность поступления элементов пользователю
	if($receipt_user_id != $user_id)
		die("Ошибка передачи элементов на склад");
			
	//Исключаем возможможность повторной передачи элементов на склад
	if($receipt_status != 'ORDERED')
		die("Ошибка передачи элементов на склад");
	
	//Получаем список элементов перемещаемых на склад
	$receipt_spec_query = mysqli_query($db,    "SELECT 
													count,
													part_id
												FROM receipt_spec 
												WHERE receipt_spec.receipt_id = \"$id\"");

	$receipt_spec_row_count = mysqli_num_rows($receipt_spec_query);

	for($i = 0; $i < $receipt_spec_row_count; $i++)
	{
		$receipt_spec_row = mysqli_fetch_array($receipt_spec_query);
		
		$add_part_count = $receipt_spec_row['count'];
		$part_id    = $receipt_spec_row['part_id'];

		//Обновляем текущее количество элементов на складе
		mysqli_query($db, "UPDATE part_list SET count = (count + \"$add_part_count\"), order_count = (order_count - \"$add_part_count\") WHERE id = \"$part_id\"");
	}
	
	//Меняес статус поставки на "Передано на склад"
	mysqli_query($db, "UPDATE receipt_list SET status = 'DELIVERED' WHERE id = \"$id\"");
		
	//Возвращаемся на страницу редактирования поставки
	header("Location: receipt_config.php?id=$id"); exit();
?>
