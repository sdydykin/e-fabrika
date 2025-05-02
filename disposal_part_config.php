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
		$spec_part_type_id = $_POST['selPartType'];   
		$spec_part_count = $_POST['edtCount'];  
		$spec_part_id = $_POST['selPart' . $spec_part_type_id];  
		
		//Определяем текущую цену элемента
		$query_result = mysqli_query($db,  "SELECT cost FROM part_list WHERE id = $spec_part_id");

		$row = mysqli_fetch_array($query_result);
		
		$spec_part_cost = $row['cost'];
		
		//Сохраняем информацию в БД
		if(is_numeric($spec_item_id)) //Редактирование информации об элементе спецификации в БД
		{
			//Обновление информации об элементе спецификации в БД
			mysqli_query($db, "UPDATE disposal_spec SET cost = \"$spec_part_cost\", count = \"$spec_part_count\", item_id = \"$spec_part_id\" WHERE id = \"$spec_item_id\"") or die ("<p>Не удалось обновить информацию об элементе спецификации в БД</p>");
		}
		else //Добавление нового элемента спецификации
		{
			//Запись нового элемента спецификации в базе данных
			mysqli_query($db, "INSERT INTO disposal_spec (cost, count, item_id, disposal_id, item_type) VALUES (\"$spec_part_cost\", \"$spec_part_count\", \"$spec_part_id\", \"$disposal_id\", \"part\")") or die ("<p>Не удалось добавить элемент спецификации</p>");
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

		<title>Элемент входящий в списание</title>
		
		<script>
			//Обработчик события изменения типа элемента
			function selPartType_change()
			{
				var sel_part_type = document.getElementsByName("selPartType")[0];
				
				var active_part_type = sel_part_type.value;

				for(var i = 0; i < sel_part_type.options.length; i++)
				{	
					var current_part_type = sel_part_type.options[i].value;
					var sel_part = document.getElementsByName("selPart" + current_part_type)[0];
					
					if(current_part_type == active_part_type)
						sel_part.hidden = false;
					else
						sel_part.hidden = true;
				}
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
	if(isset($spec_item_id))
	{
		//Загружаем информацию об элементе
		$query_result = mysqli_query($db,  "SELECT 
												part_type.id AS part_type_id,
												part_list.user_id AS part_user_id,
												disposal_spec.item_id AS part_id,
												disposal_spec.count AS count
											FROM disposal_spec INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON disposal_spec.item_id = part_list.id
											WHERE disposal_spec.id = $spec_item_id");

		$row = mysqli_fetch_array($query_result);
		
		$spec_part_type_id = $row['part_type_id'];
		$spec_part_id = $row['part_id'];
		$spec_part_count = $row['count'];
		$spec_part_user_id = $row['part_user_id'];
		
		//Проверяем принадлежность элемента входящего в списание пользователю
		if($spec_part_user_id != $user_id)
			die("Ошибка загрузки информации об элементе входящем в списание");
	}
	
	echo "			<form id = 'frmSpecItemConfig' method = 'post' action='disposal_part_config.php?disposal_id=$disposal_id&spec_item_id=$spec_item_id'>	
						<div class='mb-3'>
							<h4 class = 'text-center'>Элемент входящий в списание</h4>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='selPartType'>Тип элемента:</label>			
							<select class='form-select' name='selPartType' onchange='selPartType_change()'>";
								
	//Загружаем список типов элементов из БД
	$part_type_query_result = mysqli_query($db, "SELECT 
													id, 
													name
												FROM part_type
												WHERE user_id = $user_id
												ORDER BY name");
								
	$part_type_row_count = mysqli_num_rows($part_type_query_result);	
									
	if($part_type_row_count != 0)
	{
		echo "					<option value='0'>Выберите тип элемента</option>";
										
		for($i = 0; $i < $part_type_row_count; $i++)
		{
			$part_type_row = mysqli_fetch_array($part_type_query_result);
										
			$part_type_id = $part_type_row['id']; 
			$part_name = $part_type_row['name']; 
										
			echo "				<option value='$part_type_id' " . (($part_type_id == $spec_part_type_id) ? "selected" : "") . ">$part_name</option>";
		}
	}	
	else
		echo "					<option value='-1'>Типы элементов отсутствуют в системе</option>";
								
	echo "					</select>
						</div>
						<div class='mb-3'>
							<label class = 'form-label' for='selPart'>Элемент:</label>";

	//Возвращаемся к первой записи в списке типов элементов
	mysqli_data_seek($part_type_query_result, 0);

	if($part_type_row_count != 0)
	{
		//Для каждого типа элементов создаем список элементов
		for($i = 0; $i < $part_type_row_count; $i++)
		{	
			$part_type_row = mysqli_fetch_array($part_type_query_result);
										
			$part_type_id = $part_type_row['id']; 
		
			//Загружаем список элементов заданного типа из БД
			$part_query_result = mysqli_query($db, "SELECT 
														name,
														id
													FROM part_list 
													WHERE type_id = $part_type_id 
													ORDER BY name");
												
			$part_row_count = mysqli_num_rows($part_query_result);	
		
			if($part_row_count != 0)
			{
				echo "		<select class='form-select' name='selPart$part_type_id' " . ($part_type_id == $spec_part_type_id ? '' : 'hidden') . ">
								<option value='0'>Выберите элемент</option>";
		
				for($j = 0; $j < $part_row_count; $j++)
				{
					$part_row = mysqli_fetch_array($part_query_result);
			
					$part_id = $part_row['id']; 
					$part_name = $part_row['name']; 		
		
					echo "		<option value='$part_id' " . (($part_id == $spec_part_id) ? "selected" : "") . ">$part_name</option>";
				}
			
				echo "		</select>";			
			}
			else
				echo "		<select class='form-select' name='selPart$part_type_id' " . ($part_type_id == $spec_part_type_id ? '' : 'hidden') . ">
								<option value='0'>Элементы заданного типа в системе отсутствуют</option>
							</select>";
		}

		echo "				<select class='form-select' name='selPart0' " . (isset($spec_item_id) ? 'hidden' : '') . ">
								<option value='0'>Не выбран тип элемента</option>
							</select>";	
	}	
	else
		echo "				<select class='form-select' name='selPart'>
								<option value='0'>Элементы в системе отсутствуют</option>
							</select>";
								
	echo "				</div>
						<div class = 'mb-3'>
							<label class = 'form-label' for = 'edtCount'>Количество:</label>
							<input class = 'form-control' name = 'edtCount' type='text' value='$spec_part_count' placeholder='Количество элементов в списании'>
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