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
	
		<title>Поступления элементов</title>
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
	//Подключение к БД
	$db = mysqli_connect("localhost", "sfabr", "asrtY67Q!") or die ("<p>Не удалось подключиться к серверу БД</p>");
	mysqli_select_db($db, "parts_db") or die ("<p>Не удалось подключиться к базе данных</p>");
	mysqli_set_charset($db, 'utf8');
	
	//Загружаем данные о поставках элементов в работе из БД
	$query_result = mysqli_query($db,  "SELECT 
											receipt_list.id,
											supplier_list.name AS supplier_name,
											receipt_list.create_date,
											receipt_list.status,
											(receipt_list.delivery_cost + (SELECT SUM(receipt_spec.cost * receipt_spec.count) FROM receipt_spec WHERE receipt_spec.receipt_id = receipt_list.id)) AS total_cost
										FROM receipt_list INNER JOIN supplier_list ON receipt_list.supplier_id = supplier_list.id 
										WHERE receipt_list.user_id = $user_id AND status = 'CREATED'
										ORDER BY receipt_list.create_date");
								
	$row_count = mysqli_num_rows($query_result);	
	
	echo "			<div class='mb-3'>
						<h4>В работе</h4>
					</div>	
					<table class='table'>
						<thead>
							<tr class='mb-4'>
								<th>#</th>
								<th>Поставка</th>
								<th>Поставщик</th>
								<th>Стоимость</th>
							</tr>
						</thead>
						<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$receipt_id = $row['id'];
			$receipt_supplier = $row['supplier_name'];
			$receipt_create_date = $row['create_date'];
			$receipt_total_cost = round($row['total_cost'], 2);
			
			echo "			<tr class='mb-4'>
								<th scope='row'>" . ($i + 1) . "</th>
								<td><a class = 'text-decoration-none' href='receipt_config.php?id=$receipt_id'>Поставка элементов №" . str_pad($receipt_id, 4, "0", STR_PAD_LEFT) . " от $receipt_create_date</a></td>
								<td>$receipt_supplier</td>
								<td>$receipt_total_cost</td>
							</tr>";
		}
	else
		echo "				<tr class='mb-4'> 
								<td colspan=4>Нет поступлений элементов</td>
							</tr>"; 
						
	echo "				</tbody>
					</table>";

	//Загружаем данные о заказах элементов из БД
	$query_result = mysqli_query($db,  "SELECT 
											receipt_list.id,
											supplier_list.name AS supplier_name,
											receipt_list.order_date,
											receipt_list.status,
											(receipt_list.delivery_cost + (SELECT SUM(receipt_spec.cost * receipt_spec.count) FROM receipt_spec WHERE receipt_spec.receipt_id = receipt_list.id)) AS total_cost
										FROM receipt_list INNER JOIN supplier_list ON receipt_list.supplier_id = supplier_list.id 
										WHERE receipt_list.user_id = $user_id AND status = 'ORDERED'
										ORDER BY receipt_list.order_date");
								
	$row_count = mysqli_num_rows($query_result);	
	
	echo "			<div class='mb-3'>
						<h4>Заказано</h4>
					</div>	
					<table class='table'>
						<thead>
							<tr class='mb-4'>
								<th>#</th>
								<th>Поставка</th>
								<th>Поставщик</th>
								<th>Стоимость</th>
							</tr>
						</thead>
						<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$receipt_id = $row['id'];
			$receipt_supplier = $row['supplier_name'];
			$receipt_order_date = $row['order_date'];
			$receipt_total_cost = round($row['total_cost'], 2);
			
			echo "			<tr class='mb-4'>
								<th scope='row'>" . ($i + 1) . "</th>
								<td><a class = 'text-decoration-none' href='receipt_config.php?id=$receipt_id'>Заказ элементов №" . str_pad($receipt_id, 4, "0", STR_PAD_LEFT) . " от $receipt_order_date</a></td>
								<td>$receipt_supplier</td>
								<td>$receipt_total_cost</td>
							</tr>";
		}
	else
		echo "				<tr class='mb-4'> 
								<td colspan=4>Нет заказанных элементов</td>
							</tr>"; 
						
	echo "				</tbody>
					</table>";

	//Загружаем данные о поступивших элементах из БД
	$query_result = mysqli_query($db,  "SELECT 
											receipt_list.id,
											supplier_list.name AS supplier_name,
											receipt_list.receipt_date,
											receipt_list.status,
											(receipt_list.delivery_cost + (SELECT SUM(receipt_spec.cost * receipt_spec.count) FROM receipt_spec WHERE receipt_spec.receipt_id = receipt_list.id)) AS total_cost
										FROM receipt_list INNER JOIN supplier_list ON receipt_list.supplier_id = supplier_list.id 
										WHERE receipt_list.user_id = $user_id AND status = 'DELIVERED'
										ORDER BY receipt_list.receipt_date");
								
	$row_count = mysqli_num_rows($query_result);	
	
	echo "			<div class='mb-3'>
						<h4>Поступило</h4>
					</div>	
					<table class='table'>
						<thead>
							<tr class='mb-4'>
								<th>#</th>
								<th>Поставка</th>
								<th>Поставщик</th>
								<th>Стоимость</th>
							</tr>
						</thead>
						<tbody>";

	if($row_count)		
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$receipt_id = $row['id'];
			$receipt_supplier = $row['supplier_name'];
			$receipt_delivery_date = $row['receipt_date'];
			$receipt_total_cost = round($row['total_cost'], 2);
			
			echo "			<tr class='mb-4'>
								<th scope='row'>" . ($i + 1) . "</th>
								<td><a class = 'text-decoration-none' href='receipt_config.php?id=$receipt_id'>Передача элементов №" . str_pad($receipt_id, 4, "0", STR_PAD_LEFT) . " от $receipt_delivery_date</a></td>
								<td>$receipt_supplier</td>
								<td>$receipt_total_cost</td>
							</tr>";
		}
	else
		echo "				<tr class='mb-4'> 
								<td colspan=4>Нет поступивших элементов</td>
							</tr>"; 
						
	echo "				</tbody>
					</table>";

	echo "			<form id='frmReceiptList' method='post' action='receipt_config.php'>		
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>
								<button type='submit' class='btn btn-primary' name='btnNewReceipt'>Новое поступление</button>
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