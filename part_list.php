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
	
		<title>Элементы</title>
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
						<h4>Элементы</h4>
					</div>	
<?php
	//Загружаем данные из БД
	$query_result = mysqli_query($db,  "SELECT 
											part_type.name AS part_type_name, 
											part_list.name AS part_name,
											part_list.description AS part_description,
											part_list.storage AS part_storage,
											part_list.image AS part_image,
											part_list.count AS part_count,
											part_list.reserve_count AS part_reserve_count,
											part_list.order_count AS part_order_count,
											part_list.cost AS part_cost,
											part_list.id AS part_id
										FROM part_list INNER JOIN part_type ON part_list.type_id = part_type.id
										WHERE part_list.user_id = $user_id 
										ORDER BY part_type_name, part_storage");
								
	$row_count = mysqli_num_rows($query_result);	
				
	//Заполнение таблицы данными из БД
	echo "			<form id='frmPartList' method='post' action='part_config.php'>	
						<table class='table'>
							<thead>
								<tr>
									<th rowspan=2>#</th>
									<th rowspan=2>Наименование</th>
									<th colspan=3>Количество</th>
									<th rowspan=2>Цена</th>
									<th rowspan=2>Место хранения</th>
									<th rowspan=2>Описание</th>
									<th rowspan=2>Фото</th>
								</tr>
								<tr>
									<th>Всего</th>
									<th>Резерв</th>
									<th>Заказано</th>
								</tr>
							</thead>
							<tbody>";
						
	if($row_count)	
	{
		$cur_part_type_name = "";
	
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$part_id          	= $row['part_id'];
			$part_name        	= $row['part_name'];
			$part_type_name   	= $row['part_type_name'];
			$part_storage     	= $row['part_storage'];
			$part_description 	= $row['part_description'];
			$part_image       	= $row['part_image'];
			$part_count       	= $row['part_count'];
			$part_reserve_count	= round($row['part_reserve_count'], 2);
			$part_order_count   = round($row['part_order_count'], 2);
			$part_cost        	= round($row['part_cost'], 2);
	
			if($cur_part_type_name != $part_type_name) 
			{	
				echo "			<tr> 
									<td colspan=9><b>$part_type_name</b></td>
								</tr>"; 
								
				$cur_part_type_name = $part_type_name;
			}
			
			echo "				<tr>
									<th scope='row'>" . ($i + 1) . "</th>
									<td><a class = 'text-decoration-none' href='part_config.php?id=$part_id'>$part_name</a></td>
									<td>$part_count</td>
									<td>$part_reserve_count</td>
									<td>$part_order_count</td>
									<td>$part_cost</td>
									<td>$part_storage</td>
									<td>$part_description</td>
									<td><img style = 'height: 64px' src = '/img/" . ($part_image ? "$user_id/$part_image" : "no_image.png") . "'></img></td>
								</tr>";
		}
	}	
	else
		echo "					<tr> 
									<td colspan=9>Элементы отсутствуют в системе</td>
								</tr>"; 
						
	echo "					</tbody>
						</table>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>
								<button type='submit' class='btn btn-primary' name='btnNewPart'>Новый элемент</button>
							</div>
						</div>
					</form>";

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