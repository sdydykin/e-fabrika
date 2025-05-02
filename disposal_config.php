<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор списания в БД
	$id = $_GET['id'];
	
	//Сохранение списания в БД
	if(isset($_POST['btnDisposalSave']))
	{		
		//Получаем значения введенные пользователем 
		$disposal_date          = $_POST['edtDisposalDate'];
		$disposal_note          = $_POST['edtNote'];
		
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о списании
		{
			//Обновление информации о списании элементов в базе данных
			mysqli_query($db, "UPDATE disposal_list SET disposal_date = \"$disposal_date\", note = \"$disposal_note\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о списании элементов</p>");
			
			//Переходим на страницу списка списаний
			header("Location: disposal_list.php"); exit();
		}
		else //Добавление нового списания
		{
			//Запись нового списания в базе данных
			mysqli_query($db, "INSERT INTO disposal_list (disposal_date, note, user_id) VALUES (\"$disposal_date\", \"$disposal_note\", \"$user_id\")") or die ("<p>Не удалось добавить новое списание элементов</p>");
			
			//Получаем идентификатор только что добавленного списания элементов
			$new_disposal_id = mysqli_insert_id($db);
			
			//Возвращаемся на страницу редактирования cписания
			header("Location: disposal_config.php?id=$new_disposal_id"); exit();
		}
	}
	
	//Удаление списания из БД
	if(isset($_POST['btnDisposalDelete']))
	{
		//Удаляем списание элементов из БД
		mysqli_query($db, "DELETE FROM disposal_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить списание элементов</p>");
		
		//Удаляем связанные со списанием элементы, платы и устройства
		mysqli_query($db, "DELETE FROM disposal_spec WHERE disposal_id = \"$id\"") or die ("<p>Не удалось удалить связанные со списанием элементы, платы и устройства<p>");
		
		//Переходим на страницу списка списаний
		header("Location: disposal_list.php"); exit();
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

		<title>Списание элементов</title>
		
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
				calendar.attachObj(document.getElementById('edtDisposalDate'));
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
												disposal_date,
												note,
												status,
												user_id
											FROM disposal_list
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$disposal_date          = $row['disposal_date'];
		$disposal_note          = $row['note'];
		$disposal_status        = $row['status'];
		$disposal_user_id       = $row['user_id'];
		
		$disable_input = $disposal_status == 'UTILIZED' ? 'disabled': '';
		
		//Проверяем принадлежность списания элементов пользователю
		if($disposal_user_id != $user_id)
			die("Ошибка загрузки информации о списании элементов");
		
		//Рассчитываем общую стоимость списания
		$query_result = mysqli_query($db,  "SELECT SUM(cost * count) AS disposal_total_cost
											FROM disposal_spec
											WHERE disposal_id = \"$id\"");
	
		$row = mysqli_fetch_array($query_result);
		
		$disposal_total_cost = round($row['disposal_total_cost'], 2);
		
		//Определяем необходимость закупки элементов
		if($disposal_status == 'CREATED')
		{
			$query_result = mysqli_query($db,  "SELECT 
													COUNT(*) AS parts_not_ready_to_disposal
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
													GROUP BY part_id) AS total_parts_in_disposal INNER JOIN part_list ON total_parts_in_disposal.part_id = part_list.id
												WHERE total_parts_in_disposal.total_part_count > (part_list.count - part_list.reserve_count)");

			$row_count = mysqli_num_rows($query_result);	
			$row = mysqli_fetch_array($query_result);
			$parts_not_ready_to_disposal = $row['parts_not_ready_to_disposal'];
			
			$query_result = mysqli_query($db,  "SELECT 
													COUNT(*) AS parts_not_ready_to_reserve
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
													GROUP BY part_id) AS total_parts_in_disposal INNER JOIN part_list ON total_parts_in_disposal.part_id = part_list.id
												WHERE total_parts_in_disposal.total_part_count > (part_list.count + part_list.order_count - part_list.reserve_count)");

			$row_count = mysqli_num_rows($query_result);	

			$row = mysqli_fetch_array($query_result);

			$parts_not_ready_to_reserve = $row['parts_not_ready_to_reserve'];
		}
		
		if($disposal_status == 'RESERVED')
		{
			$query_result = mysqli_query($db,  "SELECT 
													COUNT(*) AS parts_not_ready_to_disposal
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
													GROUP BY part_id) AS total_parts_in_disposal INNER JOIN part_list ON total_parts_in_disposal.part_id = part_list.id
												WHERE total_parts_in_disposal.total_part_count > part_list.count");

			$row_count = mysqli_num_rows($query_result);	
			$row = mysqli_fetch_array($query_result);
			$parts_not_ready_to_disposal = $row['parts_not_ready_to_disposal'];
		}
	}
	else
		$disposal_status = 'CREATED';
			
	echo "			<form id = 'frmDisposalConfig' enctype='multipart/form-data' method = 'post' action='disposal_config.php?id=$id'>	
						<div class='modal fade' id='disposal_delete_confirm' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<h5 class='modal-title' id='staticBackdropLabel'>Подтверждение удаления списания</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Закрыть'></button>
									</div>
									<div class='modal-body'>
										<p>Подтверждаете удаление списания? Операцию не возможно будет отменить.</p>
									</div>
									<div class='modal-footer'>
										<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Отмена</button>
										<button type='submit' name='btnDisposalDelete' class='btn btn-primary'>Удалить списание</button>
									</div>
								</div>
							</div>
						</div>
						<div class = 'mb-3'>
							<h4 class = 'text-center'>Списание элементов</h4>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtDisposalDate'>Дата списания:</label>
							<input class = 'form-control' name = 'edtDisposalDate' id = 'edtDisposalDate' type='text' value='$disposal_date' placeholder='ГГГГ-ММ-ДД' $disable_input>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtNote'>Примечание:</label>
							<input class = 'form-control' name = 'edtNote' type='text' value='$disposal_note' placeholder='Примечание к списанию' $disable_input>
						</div>";
						
	
	//Загружаем элементы входящие в списание из БД
	$query_result = mysqli_query($db,  "SELECT 
											part_type.name AS part_type_name,
											part_list.name AS part_name,
											disposal_spec.cost AS part_cost,
											disposal_spec.count AS part_count,
											disposal_spec.id AS spec_item_id
										FROM disposal_spec INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON disposal_spec.item_id = part_list.id
										WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"part\"
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
										<th>Цена</th>
										<th>Итого</th>
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
			$part_cost = round($row['part_cost'], 2);
			$part_count = $row['part_count'];
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
										
			if($disposal_status == 'CREATED')
				echo "					<td><a class = 'text-decoration-none' href='disposal_part_config.php?disposal_id=$id&spec_item_id=$spec_item_id'>$part_name</a></td>";
			else
				echo "					<td>$part_name</td>";
				
			echo "						<td>$part_count</td>
										<td>$part_cost</td>
										<td>" . round($part_count * $part_cost, 2) . "</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=5>Нет элементов в списание</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>";
						
	//Загружаем платы входящие в списание из БД
	$query_result = mysqli_query($db,  "SELECT 
											board_list.name AS board_name,
											board_list.revision AS board_rev, 
											board_list.version AS board_ver,
											disposal_spec.cost AS board_cost,
											disposal_spec.count AS board_count,
											disposal_spec.id AS spec_item_id
										FROM disposal_spec INNER JOIN board_list ON disposal_spec.item_id = board_list.id
										WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"board\"
										ORDER BY board_list.name");
								
	$row_count = mysqli_num_rows($query_result);				
				
	echo 				"<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblBoardList'>Перечень плат:</label>
							<table class = 'table table-bordered table-sm' name = 'tblBoardList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Плата</th>
										<th>Количество</th>
										<th>Цена</th>
										<th>Итого</th>
									</tr>
								</thead>
								<tbody>";			
				
	if($row_count)
	{
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$board_name = $row['board_name'];
			$board_rev = $row['board_rev'];
			$board_ver = $row['board_ver'];
			$board_cost = round($row['board_cost'], 2);
			$board_count = $row['board_count'];
			$spec_item_id = $row['spec_item_id'];
			
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>";
										
			if($disposal_status == 'CREATED')
				echo "					<td><a class = 'text-decoration-none' href='disposal_board_config.php?disposal_id=$id&spec_item_id=$spec_item_id'>$board_name (ревизия $board_rev, исполнение $board_ver)</a></td>";
			else
				echo "					<td>$board_name (ревизия $board_rev, исполнение $board_ver)</td>";
				
			echo "						<td>$board_count</td>
										<td>$board_cost</td>
										<td>" . round($board_count * $board_cost, 2) . "</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=5>Нет плат в списании</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>";
						
	//Загружаем устройства входящие в списание из БД
	$query_result = mysqli_query($db,  "SELECT 
											device_list.name AS device_name,
											device_list.revision AS device_rev, 
											device_list.version AS device_ver,
											disposal_spec.cost AS device_cost,
											disposal_spec.count AS device_count,
											disposal_spec.id AS spec_item_id
										FROM disposal_spec INNER JOIN device_list ON disposal_spec.item_id = device_list.id
										WHERE disposal_spec.disposal_id = \"$id\" AND disposal_spec.item_type = \"device\"
										ORDER BY device_list.name");
								
	$row_count = mysqli_num_rows($query_result);				
				
	echo 				"<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblDeviceList'>Перечень устройств:</label>
							<table class = 'table table-bordered table-sm' name = 'tblDeviceList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Устройство</th>
										<th>Количество</th>
										<th>Цена</th>
										<th>Итого</th>
									</tr>
								</thead>
								<tbody>";			
				
	if($row_count)
	{
		for($i = 0; $i < $row_count; $i++)
		{
			$row = mysqli_fetch_array($query_result);
			
			$device_name = $row['device_name'];
			$device_rev = $row['device_rev'];
			$device_ver = $row['device_ver'];
			$device_cost = round($row['device_cost'], 2);
			$device_count = $row['device_count'];
			$spec_item_id = $row['spec_item_id'];
			
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>";
										
			if($disposal_status == 'CREATED')
				echo "					<td><a class = 'text-decoration-none' href='disposal_device_config.php?disposal_id=$id&spec_item_id=$spec_item_id'>$device_name (ревизия $device_rev, исполнение $device_ver)</a></td>";
			else
				echo "					<td>$device_name (ревизия $device_rev, исполнение $device_ver)</td>";
				
			echo "						<td>$device_count</td>
										<td>$device_cost</td>
										<td>" . round($device_count * $device_cost, 2) . "</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=5>Нет устройств в списании</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>";
						
	echo "				<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtTotalCost'>Общая стоимость:</label>
							<input class = 'form-control' name = 'edtTotalCost' type='text' value='$disposal_total_cost' placeholder='Общая стоимость (заполняется автоматически)' disabled>
						</div>
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	
	if(isset($id) and $disposal_status == 'CREATED')							
		echo "					<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#disposal_delete_confirm'>Удалить списание</button>";
		
	if($disposal_status != 'UTILIZED')
		echo " 					<button type='submit' class='btn btn-primary' name='btnDisposalSave'>Сохранить списание</button>";
	
	if(isset($id) and $disposal_status == 'CREATED')							
		echo "					
									<button class='btn btn-primary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
										Добавить
									</button>
									<ul class='dropdown-menu'>
										<li><a class='dropdown-item' href=\"disposal_part_config.php?disposal_id=$id\">Элемент</a></li>
										<li><a class='dropdown-item' href=\"disposal_board_config.php?disposal_id=$id\">Плату</a></li>
										<li><a class='dropdown-item' href=\"disposal_device_config.php?disposal_id=$id\">Устройство</a></li>
									</ul>	
								";
		
	if(isset($id) and $disposal_status == 'CREATED')							
	{
		echo "					<button type='button' class='btn btn-primary' name='btnUpdateCost' onclick=\"location.href='disposal_update_cost.php?disposal_id=$id'\">Обновить цены</button>";
		
		if($parts_not_ready_to_disposal == 0)
			echo "				<button type='button' class='btn btn-primary' name='btnFromWarehouse' onclick=\"location.href='disposal_state_created_to_utilized.php?disposal_id=$id'\">Забрать со склада</button>";		
		else if($parts_not_ready_to_reserve == 0)
			echo "				<button type='button' class='btn btn-primary' name='btnFromOrder' onclick=\"location.href='disposal_state_created_to_reserved.php?disposal_id=$id'\">Зарезервировать элементы</button>";		
		else
			echo "				<button type='button' class='btn btn-primary' name='btnPurchaseList' onclick=\"location.href='disposal_purchase_list.php?disposal_id=$id'\">Ведомость на закупку</button>";
	}
	
	if(isset($id) and $disposal_status == 'RESERVED')
	{
		echo "					<button type='button' class='btn btn-primary' name='btnBackOrder' onclick=\"location.href='disposal_state_reserved_to_created.php?disposal_id=$id'\">Снять с резерва</button>";	

		if($parts_not_ready_to_disposal == 0)
		echo "					<button type='button' class='btn btn-primary' name='btnFromWarehouse' onclick=\"location.href='disposal_state_reserved_to_utilized.php?disposal_id=$id'\">Забрать со склада</button>";		
	}
	
	if(isset($id) and $disposal_status == 'UTILIZED')							
		echo "					<button type='button' class='btn btn-primary' name='btnClose' onclick=\"location.href='disposal_list.php'\">Закрыть страницу</button>
								<button type='button' class='btn btn-primary' name='btnBackWarehouse' onclick=\"location.href='disposal_state_utilized_to_created.php?disposal_id=$id'\">Вернуть на склад</button>		
								<button type='button' class='btn btn-primary' name='btnDisposalSpec' onclick=\"location.href='disposal_utilize_spec.php?disposal_id=$id'\">Ведомость на списание</button>";		
	
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