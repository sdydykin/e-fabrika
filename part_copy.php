<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор копируемого элемента в БД
	$id = $_GET['id'];
	
	//Копируем элемент
	mysqli_query($db, "INSERT INTO part_list (name, description, storage, type_id, image, user_id) 
					   SELECT CONCAT('Копия ', name), description, storage, type_id, image, user_id FROM part_list WHERE id = \"$id\"") or die ("<p>Не удалось скопировать элемент</p>");

	$copy_part_id = mysqli_insert_id($db);
	
	//Переходим на страницу редактирования скопированного элемента
	header("Location: part_config.php?id=$copy_part_id"); exit();
?>
