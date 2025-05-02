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
	
		<title>Устройства</title>
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
						<h4>Устройства</h4>
					</div>	
<?php
	//Загружаем данные из БД
	$query_result = mysqli_query($db,  "SELECT 
											id,
											name,
											revision,
											version
										FROM device_list
										WHERE user_id = $user_id
										ORDER BY name");
								
	$row_count = mysqli_num_rows($query_result);	
				
	//Заполнение таблицы данными из БД
	echo "			<form id='frmDeviceList' method='post' action='device_config.php'>	
						<table class='table'>
							<thead>
								<tr class='mb-4'>
									<th>#</th>
									<th>Название</th>
									<th>Ревизия</th>
									<th>Исполнение</th>
								</tr>
							</thead>
							<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$device_id = $row['id'];
			$device_name = $row['name'];
			$device_rev = $row['revision'];
			$device_ver = $row['version'];
			
			echo "				<tr class='mb-4'>
									<th scope='row'>" . ($i + 1) . "</th>
									<td><a class = 'text-decoration-none' href='device_config.php?id=$device_id'>$device_name</a></td>
									<td>$device_rev</td>
									<td>$device_ver</td>
								</tr>";
		}
	else
		echo "					<tr class='mb-4'> 
									<td colspan=4>Устройства в системе отсутствуют</td>
								</tr>"; 
						
	echo "					</tbody>
						</table>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>
								<button type='submit' class='btn btn-primary' name='btnNewDevice'>Новое устройство</button>
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