<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";
?>

<!doctype html>
<html lang="en">
	<head>
		<meta content='text/html; charset=utf-8' />  
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 
		<link rel="stylesheet" href="/bootstrap/css/bootstrap.css">
		<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
	
		<script src="https://kit.fontawesome.com/29a34755cb.js"></script>
	
		<title>Ведомость закупки элементов</title>
	</head>
	<body>   
		<div class = "container">
			<div class = "row">
				<div class = "col-12">
					<?php
						//Вывод "шапки" страницы
						require $_SERVER['DOCUMENT_ROOT'] . "/site_header.php";
					?>	
					<div class='mb-3'>
						<h4>Ведомость закупки элементов</h4>
					</div>	
<?php
	//Идентификатор списания элементов в БД
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
		die("Ошибка формирования ведомости закупки элементов");
			
	//Исключаем возможможность формирования ведомости на закупку элементов для заразервированных/забранных со склада элементов
	if($disposal_status != 'CREATED')
		die("Ошибка формирования ведомости закупки элементов");
	
	//Загружаем данные из БД
	$query_result = mysqli_query($db,  "SELECT 
											total_parts_in_disposal.part_id,
											total_parts_in_disposal.total_part_count,
											part_list.name AS part_name,
											part_list.count AS available_part_count,
											part_list.order_count AS order_part_count,
											part_list.reserve_count AS reserve_part_count,
											part_type.name AS part_type_name
										FROM
											(SELECT 
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
											GROUP BY part_id) AS total_parts_in_disposal INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON total_parts_in_disposal.part_id = part_list.id
										WHERE total_parts_in_disposal.total_part_count > (part_list.count + part_list.order_count - part_list.reserve_count)
										ORDER BY part_type.name");
										
	$row_count = mysqli_num_rows($query_result);	

	//Заполнение таблицы данными из БД
	echo "			<table class='table'>
						<thead>
							<tr class='mb-4'>
								<th>#</th>
								<th>Наименование</th>
								<th>Требуется</th>
							</tr>
						</thead>
						<tbody>";
						
	if($row_count)
	{							
		$cur_part_type_name = "";
		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$part_type_name = $row['part_type_name'];
			$part_name = $row['part_name'];
			$total_part_count = $row['total_part_count'];
			$available_part_count = $row['available_part_count'];		
			$order_part_count	= $row['order_part_count'];
			$reserve_part_count	= $row['reserve_part_count'];
					
			if($cur_part_type_name != $part_type_name) 
			{	
				echo "		<tr> 
								<td colspan=3 class='text-center font-weight-bold'>$part_type_name</td>
							</tr>"; 
								
				$cur_part_type_name = $part_type_name;
			}
			
			echo "			<tr>
								<th scope='row'>" . ($i + 1) . "</th>
								<td>$part_name</td>
								<td>" . round($total_part_count - ($available_part_count + $order_part_count - $reserve_part_count), 2) . "</td>
							</tr>";
		}
	}
	else
		echo "				<tr class='mb-4'> 
								<td colspan=3>Нет элементов для закупки</td>
							</tr>"; 
						
	echo "				</tbody>
					</table>
					<div class = 'row justify-content-center'>
						<div class='mb-3 text-center'>
							<button type='button' class='btn btn-primary' name='btnClose' onclick=\"location.href='disposal_config.php?id=$id'\">Закрыть</button>
						</div>
					</div>";

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