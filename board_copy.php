<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор копируемой платы в БД
	$id = $_GET['id'];
	
	//Копируем плату
	mysqli_query($db, "INSERT INTO board_list (name, revision, version, image, user_id) 
					   SELECT CONCAT('Копия ', name), revision, version, image, user_id FROM board_list WHERE id = \"$id\"") or die ("<p>Не удалось скопировать плату</p>");

	$copy_board_id = mysqli_insert_id($db);
	
	//Копируем входящие в плату элементы
	mysqli_query($db, "INSERT INTO board_spec (ref_des, count, part_id, board_id) 
					   (SELECT ref_des, count, part_id, \"$copy_board_id\" AS board_id FROM board_spec WHERE board_id = \"$id\")") or die ("<p>Не удалось скопировать элементы спецификации</p>");

	//Переходим на страницу редактирования скопированной платы
	header("Location: board_config.php?id=$copy_board_id"); exit();
?>
