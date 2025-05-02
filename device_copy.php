<?php
	//Проверка авторизации пользователя
	require $_SERVER['DOCUMENT_ROOT'] . "/check_user.php";

	//Идентификатор копируемого устройства в БД
	$id = $_GET['id'];
	
	//Копируем плату
	mysqli_query($db, "INSERT INTO device_list (name, revision, version, image, user_id) 
					   SELECT CONCAT('Копия ', name), revision, version, image, user_id FROM device_list WHERE id = \"$id\"") or die ("<p>Не удалось скопировать устройство</p>");

	$copy_device_id = mysqli_insert_id($db);
	
	//Копируем входящие в устройство элементы и платы
	mysqli_query($db, "INSERT INTO device_spec (device_id, count, item_id, item_type) 
					   (SELECT \"$copy_device_id\" AS device_id, count, item_id, item_type FROM device_spec WHERE device_id = \"$id\")") or die ("<p>Не удалось скопировать элементы спецификации</p>");

	//Переходим на страницу редактирования скопированного устройства
	header("Location: device_config.php?id=$copy_device_id"); exit();
?>
