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
	
		<title>Списания элементов</title>
	</head>
	<body>   
		<div class = "container">
			<div class = "row">
				<div class = "col-12">
					<?php
						//Вывод "шапки" страницы
						require $_SERVER['DOCUMENT_ROOT'] . "/site_header.php";
					?>	
<?php
	//Загружаем данные о списании элементов в работе из БД
	$query_result = mysqli_query($db,  "SELECT 
											disposal_list.id,
											disposal_list.disposal_date,
											(SELECT SUM(disposal_spec.cost * disposal_spec.count) FROM disposal_spec WHERE disposal_spec.disposal_id = disposal_list.id) AS total_cost
										FROM disposal_list 
										WHERE disposal_list.user_id = $user_id AND disposal_list.status = 'CREATED'
										ORDER BY disposal_list.disposal_date");
								
	$row_count = mysqli_num_rows($query_result);	

	echo "			
					<div class='mb-3'>
						<h4>В работе</h4>
					</div>	
						<table class='table'>
							<thead>
								<tr class='mb-4'>
									<th>#</th>
									<th>Списание</th>
									<th>Стоимость</th>
								</tr>
							</thead>
							<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$disposal_id = $row['id'];
			$disposal_date = $row['disposal_date'];
			$disposal_total_cost = round($row['total_cost'], 2);
			
			echo "				<tr class='mb-4'>
									<th scope='row'>" . ($i + 1) . "</th>
									<td><a class = 'text-decoration-none' href='disposal_config.php?id=$disposal_id'>Списание элементов №" . str_pad($disposal_id, 4, "0", STR_PAD_LEFT) . " от $disposal_date</a></td>
									<td>$disposal_total_cost</td>
								</tr>";
		}
	else
		echo "					<tr class='mb-4'> 
									<td colspan=3>Нет списаний элементов</td>
								</tr>"; 
						
	echo "				</tbody>
					</table>";

	//Загружаем данные о зарезервированных элементах из БД
	$query_result = mysqli_query($db,  "SELECT 
											disposal_list.id,
											disposal_list.disposal_date,
											(SELECT SUM(disposal_spec.cost * disposal_spec.count) FROM disposal_spec WHERE disposal_spec.disposal_id = disposal_list.id) AS total_cost
										FROM disposal_list 
										WHERE disposal_list.user_id = $user_id AND disposal_list.status = 'RESERVED'
										ORDER BY disposal_list.disposal_date");
								
	$row_count = mysqli_num_rows($query_result);	

	echo "			
					<div class='mb-3'>
						<h4>Резерв</h4>
					</div>	
						<table class='table'>
							<thead>
								<tr class='mb-4'>
									<th>#</th>
									<th>Списание</th>
									<th>Стоимость</th>
								</tr>
							</thead>
							<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$disposal_id = $row['id'];
			$disposal_date = $row['disposal_date'];
			$disposal_total_cost = round($row['total_cost'], 2);
			
			echo "				<tr class='mb-4'>
									<th scope='row'>" . ($i + 1) . "</th>
									<td><a class = 'text-decoration-none' href='disposal_config.php?id=$disposal_id'>Резерв элементов №" . str_pad($disposal_id, 4, "0", STR_PAD_LEFT) . " от $disposal_date</a></td>
									<td>$disposal_total_cost</td>
								</tr>";
		}
	else
		echo "					<tr class='mb-4'> 
									<td colspan=3>Нет зарезервированных элементов</td>
								</tr>"; 
						
	echo "				</tbody>
					</table>";		

	//Загружаем данные о списанных элементах из БД
	$query_result = mysqli_query($db,  "SELECT 
											disposal_list.id,
											disposal_list.disposal_date,
											(SELECT SUM(disposal_spec.cost * disposal_spec.count) FROM disposal_spec WHERE disposal_spec.disposal_id = disposal_list.id) AS total_cost
										FROM disposal_list 
										WHERE disposal_list.user_id = $user_id AND disposal_list.status = 'UTILIZED'
										ORDER BY disposal_list.disposal_date");
								
	$row_count = mysqli_num_rows($query_result);	

	echo "			
					<div class='mb-3'>
						<h4>Списано</h4>
					</div>	
						<table class='table'>
							<thead>
								<tr class='mb-4'>
									<th>#</th>
									<th>Списание</th>
									<th>Стоимость</th>
								</tr>
							</thead>
							<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$disposal_id = $row['id'];
			$disposal_date = $row['disposal_date'];
			$disposal_total_cost = round($row['total_cost'], 2);
			
			echo "				<tr class='mb-4'>
									<th scope='row'>" . ($i + 1) . "</th>
									<td><a class = 'text-decoration-none' href='disposal_config.php?id=$disposal_id'>Передача элементов №" . str_pad($disposal_id, 4, "0", STR_PAD_LEFT) . " от $disposal_date</a></td>
									<td>$disposal_total_cost</td>
								</tr>";
		}
	else
		echo "					<tr class='mb-4'> 
									<td colspan=3>Нет списанных элементов</td>
								</tr>"; 
						
	echo "				</tbody>
					</table>";

	echo "			<form id='frmReceiptList' method='post' action='disposal_config.php'>		
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>
								<button type='submit' class='btn btn-primary' name='btnNewDisposal'>Новое списание</button>
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