<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор платы в БД
	$id = $_GET['id'];
	
	//Сохранение платы в БД
	if(isset($_POST['btnBoardSave']))
	{		
		//Получаем значения введенные пользователем 
		$board_name = $_POST['edtBoardName'];  
		$board_rev = $_POST['edtBoardRev'];  
		$board_ver = $_POST['edtBoardVer'];  
		
		$image_file_name = $_FILES['edtBoardImage']['name'];
		$image_file_tmp = $_FILES['edtBoardImage']['tmp_name'];
		
		//Копируем файл с картинкой
		if($image_file_name != "")
			move_uploaded_file($image_file_tmp, "/var/www/html/e-fabrika.pro/img/$user_id/$image_file_name");
		
		//Сохраняем информацию в БД
		if(is_numeric($id)) //Редактирование информации о плате
		{
			//Обновление информации о плате в базе данных
			mysqli_query($db, "UPDATE board_list SET name = \"$board_name\", revision = \"$board_rev\", version = \"$board_ver\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о плате</p>");
			
			if($image_file_name != "")
				mysqli_query($db, "UPDATE board_list SET image = \"$image_file_name\" WHERE id = \"$id\"") or die ("<p>Не удалось обновить информацию о плате</p>");
			
			//Переходим на страницу списка плат
			header("Location: board_list.php"); exit();
		}
		else //Добавление новой платы
		{
			//Запись новой платы в базу данных
			mysqli_query($db, "INSERT INTO board_list (name, revision, version, image, user_id) VALUES (\"$board_name\", \"$board_rev\", \"$board_ver\", \"$image_file_name\", \"$user_id\")") or die ("<p>Не удалось добавить новую плату</p>");
			
			//Получаем идентификатор только что добавленной платы
			$new_board_id = mysqli_insert_id($db);
			
			//Возвращаемся на страницу редактирования платы
			header("Location: board_config.php?id=$new_board_id"); exit();
		}
	}
	
	//Удаление платы из БД
	if(isset($_POST['btnBoardDelete']))
	{
		//Удаляем плату из БД
		mysqli_query($db, "DELETE FROM board_list WHERE id = \"$id\"") or die ("<p>Не удалось удалить плату</p>");
		
		//Удаляем связанные с платой элементы
		mysqli_query($db, "DELETE FROM board_spec WHERE board_id = \"$id\"") or die ("<p>Не удалось удалить связанные с платой элементы<p>");
		
		//Переходим на страницу списка плат
		header("Location: board_list.php"); exit();
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

		<title>Плата</title>
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
		//Загружаем информацию о плате
		$query_result = mysqli_query($db,  "SELECT 
												name, 
												revision,
												version,
												image,
												user_id
											FROM board_list
											WHERE id = $id");

		$row = mysqli_fetch_array($query_result);
		
		$board_name = $row['name'];
		$board_rev = $row['revision'];
		$board_ver = $row['version'];
		$board_image = $row['image'];
		$board_user_id = $row['user_id'];
		
		//Проверяем принадлежность платы пользователю
		if($board_user_id != $user_id)
			die("Ошибка загрузки информации о плате");
		
		//Проверяем связанность записи в базе данных
		$query_result = mysqli_query($db,  "SELECT 
												(
													(SELECT COUNT(*) AS usage_count FROM disposal_spec WHERE item_id = \"$id\" AND item_type = \"board\") + 
													(SELECT COUNT(*) AS usage_count FROM device_spec WHERE item_id = \"$id\" AND item_type = \"board\")
												) AS usage_count");

		$row = mysqli_fetch_array($query_result);
		
		if($row['usage_count'] > 0)
			$del_btn_enable = false;
		else
			$del_btn_enable = true;
	}
	
	echo "			<form id = 'frmBoardConfig' method = 'post' action='board_config.php?id=$id'>	
						<div class='modal fade' id='board_delete_confirm' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
							<div class='modal-dialog'>
								<div class='modal-content'>
									<div class='modal-header'>
										<h5 class='modal-title' id='staticBackdropLabel'>Подтверждение удаления платы</h5>
										<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Закрыть'></button>
									</div>
									<div class='modal-body'>
										<p>Подтверждаете удаление платы? Операцию не возможно будет отменить.</p>
									</div>
									<div class='modal-footer'>
										<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Отмена</button>
										<button type='submit' name='btnBoardDelete' class='btn btn-primary'>Удалить плату</button>
									</div>
								</div>
							</div>
						</div>
						<div class='mb-3'>
							<h4 class = 'text-center'>Плата</h4>
						</div>
						<div class = 'row'>
							<div class = 'col-8'>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtBoardName'>Название:</label>
									<input class = 'form-control' name = 'edtBoardName' type='text' value='$board_name' placeholder='Название платы'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtBoardRev'>Ревизия:</label>
									<input class = 'form-control' name = 'edtBoardRev' type='text' value='$board_rev' placeholder='Ревизия платы'>
								</div>
								<div class = 'mb-3'>
									<label class = 'form-label' for = 'edtBoardVer'>Исполнение:</label>
									<input class = 'form-control' name = 'edtBoardVer' type='text' value='$board_ver' placeholder='Исполнение платы'>
								</div>
								<div class='mb-3'>
									<label class = 'form-label' for = 'edtBoardImage'>Изображение для платы:</label>
									<input class = 'form-control' type='file' id = 'edtBoardImage' name = 'edtBoardImage'>
								</div>
							</div>
							<div class = 'col-4 align-self-center'>
								<img class = 'img-fluid img-thumbnail' src = '/img/" . ($board_image ? "$user_id/$board_image" : "no_image.png") . "'></img>
							</div>
						</div>";
				
	//Загружаем перечень элементов на плату из БД
	$query_result = mysqli_query($db,  "SELECT 
											part_type.name AS part_type_name,
											part_list.name AS part_name,
											board_spec.ref_des AS ref_des,
											board_spec.count AS count,
											board_spec.id AS spec_item_id
										FROM board_spec INNER JOIN (part_list INNER JOIN part_type ON part_list.type_id = part_type.id) ON board_spec.part_id = part_list.id
										WHERE board_spec.board_id = \"$id\"
										ORDER BY part_type.name, board_spec.ref_des");
								
	$row_count = mysqli_num_rows($query_result);				
				
	echo 				"<div class = 'mb-3'>
							<label class = 'form-label' for = 'tblPartList'>Перечень элементов:</label>
							<table class = 'table table-bordered table-sm' name = 'tblPartList'>
								<thead>
									<tr>
										<th>#</th>
										<th>Обозначение</th>
										<th>Элемент</th>
										<th>Количество</th>
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
			$ref_des = $row['ref_des'];
			$count = $row['count'];
			$spec_item_id = $row['spec_item_id'];
			
			if($cur_part_type_name != $part_type_name) 
			{	
				echo "				<tr> 
											<td colspan=4 class='text-center font-weight-bold'>$part_type_name</td>
									</tr>"; 
								
				$cur_part_type_name = $part_type_name;
			}
			
			echo "					<tr>
										<th scope='row'>" . ($i + 1) . "</th>
										<td>$ref_des</td>
										<td><a class = 'text-decoration-none' href='board_part_config.php?board_id=$id&spec_item_id=$spec_item_id'>$part_name</a></td>
										<td>$count</td>
									</tr>";
		}
	}
	else
		echo "						<tr> 
										<td colspan=4>Нет элементов связанных с платой</td>
									</tr>"; 
								
	echo "						</tbody>
							</table>	
						</div>			
						<div class = 'row justify-content-center'>
							<div class='mb-3 text-center'>";
	
	if(isset($id) && $del_btn_enable)							
		echo "					<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#board_delete_confirm'>Удалить плату</button>";
	
	if(isset($id))
		echo "					<button type='button' class='btn btn-primary' name='btnBoardCopy' onclick=\"location.href='board_copy.php?id=$id'\">Создать копию</button>";
	
	
	echo " 						<button type='submit' class='btn btn-primary' name='btnBoardSave'>Сохранить плату</button>";
	
	if(isset($id))							
		echo "					<button type='button' class='btn btn-primary' name='btnAddPart' onclick=\"location.href='board_part_config.php?board_id=$id'\">Добавить элемент</button>";
	
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