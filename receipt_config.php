<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор поставки в БД
	$id = $_GET['id'];
	
	//Сохранение поставки в БД
	if(isset($_POST['btnReceiptSave']))
	{		
		//Получаем значения введенные пользователем 
		$receipt_supplier_id   = $_POST['selSupplier'];
		$receipt_order_date    = $_POST['edtOrderDate'];
		$receipt_delivery_date = $_POST['edtReceiptDate'];
		$receipt_delivery_cost = $_POST['edtDeliveryCost'];
		$receipt_note          = $_POST['edtNote'];
		$receipt_create_date   = date('Y-m-d');
		
		if($receipt_delivery_cost == "")
			$receipt_delivery_cost = 0;
			
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о поставке
		{
			//Обновление информации о поступлении элементов в базе данных
			if($receipt_supplier_id != "")
				mysqli_query($db, "UPDATE receipt_list SET supplier_id = \"$receipt_supplier_id\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о поставщике элементов</p>");
			
			mysqli_query($db, "UPDATE receipt_list SET delivery_cost = \"$receipt_delivery_cost\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о цене доставки элементов</p>");
			
			mysqli_query($db, "UPDATE receipt_list SET note = \"$receipt_note\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить примечание к поставке элементов</p>");
			
			if($receipt_delivery_date != "")
				mysqli_query($db, "UPDATE receipt_list SET receipt_date = \"$receipt_delivery_date\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о дате поставки элементов</p>");
			
			if($receipt_order_date != "")
				mysqli_query($db, "UPDATE receipt_list SET order_date = \"$receipt_order_date\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о дате заказа элементов</p>");
			
			//Возвращаемся на страницу редактирования поставки
			header("Location: receipt_config.php?id=$id"); exit();
		}
		else //Добавление новой поставки
		{
			//Запись новой поставки элементов в базу данных
			mysqli_query($db, "INSERT INTO receipt_list (supplier_id, delivery_cost, note, user_id, create_date) VALUES (\"$receipt_supplier_id\", \"$receipt_delivery_cost\", \"$receipt_note\", \"$user_id\", \"$receipt_create_date\")") or die ("<p>Не удалось добавить новую поставку элементов</p>");
			
			//Получаем идентификатор только что добавленной поставки элементов
			$new_receipt_id = mysqli_insert_id($db);
			
			if($receipt_delivery_date != "")
				mysqli_query($db, "UPDATE receipt_list SET receipt_date = \"$receipt_delivery_date\" WHERE id = \"$new_receipt_id\"") or die ("<p>Не удалось добавить информацию о дате поставки элементов</p>");
			
			if($receipt_order_date != "")
				mysqli_query($db, "UPDATE receipt_list SET order_date = \"$receipt_order_date\" WHERE id = \"$new_receipt_id\"") or die ("<p>Не удалось добавить информацию о дате заказа элементов</p>");
			
			//Возвращаемся на страницу редактирования поставки
			header("Location: receipt_config.php?id=$new_receipt_id"); exit();
		}
	}
	
	//Удаление поставки из БД
	if(isset($_POST['btnReceiptDelete']))
	{
		//Удаляем поставку элементов из БД
		mysqli_query($db, "DELETE FROM receipt_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить поставку элементов</p>");
		
		//Удаляем связанные с поставкой элементы
		mysqli_query($db, "DELETE FROM receipt_spec WHERE receipt_id = \"$id\"") or die ("<p>Не удалось удалить связанные с поставкой элементы<p>");
		
		//Переходим на страницу списка поставок
		header("Location: receipt_list.php"); exit();
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

		<title>Поступление элементов</title>
		
		<link href="/calendar/terrace/dhtmlxcalendar.css" rel="stylesheet" type="text/css" />
		<script src="/calendar/dhtmlxcalendar.js"></script>	
		
		<script type="text/javascript">
			//Добавляем обработчик события загрузки страницы
			window.addEventListener("load", init_calendar, false);
			
			//Инициализация календаря
			function init_calendar()
			{
				//Добавление календарей к полям ввода
				var calendar = new dhtmlXCalendarObject();

				calendar.hideTime();
				calendar.setSkin('dhx_terrace');
				calendar.loadUserLanguage('ru');
				calendar.attachObj(document.getElementById('edtOrderDate'));
				calendar.attachObj(document.getElementById('edtReceiptDate'));
			}	
		</script>			
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
	if(isset($id))
	{
		//Загружаем информацию о поставке элементов
		$query_result = mysqli_query($db,  "SELECT 
												supplier_id,
												order_date,
												receipt_date,
												delivery_cost,
												note,
												status,
												user_id
											FROM receipt_list
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$receipt_supplier_id   = $row['supplier_id'];
		$receipt_delivery_date = $row['receipt_date'];
		$receipt_order_date    = $row['order_date'];
		$receipt_delivery_cost = round($row['delivery_cost'], 2);
		$receipt_note          = $row['note'];
		$receipt_status        = $row['status'];
		$receipt_user_id       = $row['user_id'];
		
		$disable_input = $receipt_status == 'DELIVERED' ? 'disabled': '';
		
		//Проверяем принадлежность поступления элементов пользователю
		if($receipt_user_id != $user_id)
			die("Ошибка загрузки информации о поставке элементов");
		
		//Рассчитываем общую стоимость элементов
		$query_result = mysqli_query($db,  "SELECT SUM(cost * count) AS parts_total_cost
											FROM receipt_spec
											WHERE receipt_id = \"$id\"");
	
		$row = mysqli_fetch_array($query_result);
		
		$parts_total_cost = $row['parts_total_cost'];
	
		$receipt_total_cost = round($parts_total_cost + $receipt_delivery_cost, 2); 
		
		if($parts_total_cost)
			$delivery_coef = 1 + $receipt_delivery_cost / $parts_total_cost;
		else
			$delivery_coef = 1;
	}
	else
		$receipt_status = 'CREATED';
	
	echo "			<form id = 'frmReceiptConfig' enctype='multipart/form-data' method = 'post' action='receipt_config.php?id=$id'>	
						<div class='modal fade' id='receipt_delete_confirm' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<h5 class='modal-title' id='staticBackdropLabel'>Подтверждение удаления поступления элементов</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Закрыть'></button>
									</div>
									<div class='modal-body'>
										<p>Подтверждаете удаление поступления элементов? Операцию не возможно будет отменить.</p>
									</div>
									<div class='modal-footer'>
										<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Отмена</button>
										<button type='submit' name='btnReceiptDelete' class='btn btn-primary'>Удалить поставку</button>
									</div>
								</div>
							</div>
						</div>
						<div class = 'mb-3'>
							<h4 class = 'text-center'>Поступление элементов</h4>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for='selSupplier'>Поставщик:</label>			
							<select class='form-select' name='selSupplier' $disable_input>";
								
	//Загружаем список поставщиков элементов из БД
	$query_result = mysqli_query($db, "SELECT 
											id, 
											name
										FROM supplier_list
										WHERE user_id = $user_id
										ORDER BY name");
								
	$row_count = mysqli_num_rows($query_result);	
									
	if($row_count != 0)
	{
		echo "					<option value='0'>Выберите поставщика</option>";
										
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
										
			$supplier_id = $row['id']; 
			$supplier_name = $row['name']; 
										
			echo "				<option value='$supplier_id' " . (($supplier_id == $receipt_supplier_id) ? "selected" : "") . ">$supplier_name</option>";
		}
	}	
	else
		echo "					<option value='-1'>Поставщики элементов отсутствуют в системе</option>";
								
	echo "					</select>
						</div>	
						
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtOrderDate'>Дата заказа:</label>
							<input class = 'form-control' name = 'edtOrderDate' id = 'edtOrderDate' type='text' value='$receipt_order_date' placeholder='ГГГГ-ММ-ДД' $disable_input>
						</div>
						
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtReceiptDate'>Дата поставки:</label>
							<input class = 'form-control' name = 'edtReceiptDate' id = 'edtReceiptDate' type='text' value='$receipt_delivery_date' placeholder='ГГГГ-ММ-ДД' $disable_input>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtDeliveryCost'>Стоимость доставки:</label>
							<input class = 'form-control' name = 'edtDeliveryCost' type='text' value='$receipt_delivery_cost' placeholder='Стоимость доставки' $disable_input>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtNote'>Примечание:</label>
							<input class = 'form-control' name = 'edtNote' type='text' value='$receipt_note' placeholder='Примечание к поставке' $disable_input>
						</div>";
						
	
	//Загружаем элементы входящие в поставку из БД
	$query_result = mysqli_query($db,  "SELECT 
											part_type.name AS part_type_name,
											part_list.name AS part_name,
											receipt_spec.cost AS cost,
											receipt_spec.count AS part_count,
											receipt_spec.id AS spec_item_id
										FROM receipt_spec INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON receipt_spec.part_id = part_list.id
										WHERE receipt_spec.receipt_id = \"$id\"
										ORDER BY part_type.name");
								
	$row_count = mysqli_num_rows($query_result);				
				
	echo 				"<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblPartList'>Перечень элементов:</label>
							<table class = 'table table-bordered table-sm' name = 'tblPartList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Элемент</th>
										<th>Количество</th>
										<th>Цена закупки</th>
										<th>Цена с учетом доставки</th>
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
			$cost = round($row['cost'], 2);
			$count = $row['part_count'];
			$spec_item_id = $row['spec_item_id'];
			
			if($cur_part_type_name != $part_type_name) 
			{	
				echo "				<tr> 
											<td colspan=5 class='text-center font-weight-bold'>$part_type_name</td>
									</tr>"; 
								
				$cur_part_type_name = $part_type_name;
			}
			
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>";
										
			if($receipt_status != 'ORDERED' && $receipt_status != 'DELIVERED')
				echo "					<td><a class = 'text-decoration-none' href='receipt_part_config.php?receipt_id=$id&spec_item_id=$spec_item_id'>$part_name</a></td>";
			else
				echo "					<td>$part_name</td>";				
				
			echo "						<td>$count</td>
										<td>$cost</td>
										<td>" . round($cost * $delivery_coef, 2) . "</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=5>Нет элементов в поставке</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtTotalCost'>Общая стоимость:</label>
							<input class = 'form-control' name = 'edtTotalCost' type='text' value='$receipt_total_cost' placeholder='Общая стоимость (заполняется автоматически)' disabled>
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($id) and $receipt_status == 'CREATED')							
		echo "					<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#receipt_delete_confirm'>Удалить поставку</button>";

	if($receipt_status != 'DELIVERED')		
		echo " 					<button type='submit' class='btn btn-primary' name='btnReceiptSave'>Сохранить поставку</button>";
		
	if(isset($id) and $receipt_status == 'CREATED')							
		echo "					<button type='button' class='btn btn-primary' name='btnAddPart' onclick=\"location.href='receipt_part_config.php?receipt_id=$id'\">Добавить элемент</button>";

	if(isset($id) and $receipt_status == 'CREATED')							
		echo "					<button type='button' class='btn btn-primary' name='btnToOrder' onclick=\"location.href='receipt_state_created_to_ordered.php?receipt_id=$id'\">Сформировать заказ</button>";	
		
	if(isset($id) and $receipt_status == 'ORDERED')							
		echo "					<button type='button' class='btn btn-primary' name='btnToWarehouse' onclick=\"location.href='receipt_state_ordered_to_delivered.php?receipt_id=$id'\">Передать на склад</button>";		
	
	if($receipt_status == 'DELIVERED')							
		echo "					<button type='button' class='btn btn-primary' name='btnClose' onclick=\"location.href='receipt_list.php'\">Закрыть страницу</button>";		
	
	echo "					</div>
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