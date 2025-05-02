<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор элемента спецификации в БД
	$spec_item_id = $_GET['spec_item_id'];
	
	//Идентификатор списания элементов в БД
	$disposal_id = $_GET['disposal_id'];
	
	//Сохранение элемента спецификации в БД
	if(isset($_POST['btnSpecItemSave']))
	{		
		//Получаем значения введенные пользователем 
		$spec_board_id = $_POST['selBoard'];   
		$spec_board_count = $_POST['edtCount'];  
		
		//Определяем текущую цену платы
		$query_result = mysqli_query($db,  "SELECT 
												SUM(part_list.cost * board_spec.count) AS board_cost 
											FROM board_spec INNER JOIN part_list ON board_spec.part_id = part_list.id 
											WHERE board_spec.board_id = $spec_board_id");

		$row = mysqli_fetch_array($query_result);
		
		$spec_board_cost = $row['board_cost'];
		
		//Сохраняем информацию в БД
		if(is_numeric($spec_item_id)) //Редактирование информации об элементе спецификации в БД
		{
			//Обновление информации об элементе спецификации в БД
			mysqli_query($db, "UPDATE disposal_spec SET cost = \"$spec_board_cost\", count = \"$spec_board_count\", item_id = \"$spec_board_id\" WHERE id = \"$spec_item_id\"") or die ("<p>Не удалось обновить информацию об элементе спецификации в БД</p>");
		}
		else //Добавление нового элемента спецификации
		{
			//Запись нового элемента спецификации в базе данных
			mysqli_query($db, "INSERT INTO disposal_spec (cost, count, item_id, disposal_id, item_type) VALUES (\"$spec_board_cost\", \"$spec_board_count\", \"$spec_board_id\", \"$disposal_id\", \"board\")") or die ("<p>Не удалось добавить элемент спецификации</p>");
		}
		
		//Переходим на страницу редактирования списаний элементов
		header("Location: disposal_config.php?id=$disposal_id"); exit();
	}
	
	//Удаление элемента спецификации из БД
	if(isset($_POST['btnSpecItemDelete']))
	{
		//Удаляем элемент спецификации из БД
		mysqli_query($db, "DELETE FROM disposal_spec WHERE id = \"$spec_item_id\"") or die ("<p>Не удалось удалить элемент спецификации</p>");
		
		//Переходим на страницу редактирования списания элементов
		header("Location: disposal_config.php?id=$disposal_id"); exit();
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

		<title>Плата входящая в списание</title>
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
	if(isset($spec_item_id))
	{
		//Загружаем информацию о плате
		$query_result = mysqli_query($db,  "SELECT 
												disposal_spec.item_id AS board_id,
												disposal_spec.count AS count,
												board_list.user_id AS board_user_id
											FROM disposal_spec INNER JOIN board_list ON disposal_spec.item_id = board_list.id
											WHERE disposal_spec.id = $spec_item_id");

		$row = mysqli_fetch_array($query_result);
		
		$spec_board_id = $row['board_id'];
		$spec_board_count = $row['count'];
		$spec_board_user_id = $row['board_user_id'];
		
		//Проверяем принадлежность платы пользователю
		if($spec_board_user_id != $user_id)
			die("Ошибка загрузки информации о плате входящей в списание");
	}
	
	echo "			<form id = 'frmSpecItemConfig' method = 'post' action='disposal_board_config.php?disposal_id=$disposal_id&spec_item_id=$spec_item_id'>	
						<div class='mb-3'>
							<h4 class = 'text-center'>Плата входящая в списание</h4>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='selBoard'>Плата:</label>			
							<select class='form-select' name='selBoard'>";
								
	//Загружаем список плат из БД
	$board_query_result = mysqli_query($db, "SELECT 
													id, 
													name,
													revision, 
													version
												FROM board_list
												WHERE user_id = $user_id
												ORDER BY name, revision, version");
								
	$board_row_count = mysqli_num_rows($board_query_result);	
									
	if($board_row_count != 0)
	{
		echo "					<option value='0'>Выберите плату</option>";
										
		for($i = 0; $i < $board_row_count; $i++)
		{
			$board_row = mysqli_fetch_array($board_query_result);
										
			$board_id = $board_row['id']; 
			$board_name = $board_row['name']; 
			$board_revision = $board_row['revision']; 
			$board_version = $board_row['version']; 
										
			echo "				<option value='$board_id' " . (($board_id == $spec_board_id) ? "selected" : "") . ">$board_name (ревизия $board_revision, исполнение $board_version)</option>";
		}
	}	
	else
		echo "					<option value='-1'>Платы отсутствуют в системе</option>";
								
	echo "					</select>
						</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtCount'>Количество:</label>
							<input class = 'form-control' name = 'edtCount' type='text' value='$spec_board_count' placeholder='Количество плат в списании'>
						</div>						  
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	if(isset($spec_item_id))							
		echo "					<button type='submit' class='btn btn-primary' name='btnSpecItemDelete'>Удалить</button>";
		
	echo " 						<button type='submit' class='btn btn-primary' name='btnSpecItemSave'>Сохранить</button>
							</div>
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