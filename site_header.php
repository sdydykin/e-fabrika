<?php
	//Формируем меню
	$main_menu = array(
		array("name" => "Справочники", 		"href" => "", 					"submenu" => 2), 
		array("name" => "Поставщики", 		"href" => "supplier_list.php", 	"submenu" => 0),
		array("name" => "Типы элементов", 	"href" => "part_type_list.php", "submenu" => 0),
		array("name" => "Элементы", 		"href" => "part_list.php", 		"submenu" => 0),
		array("name" => "Платы", 			"href" => "board_list.php", 	"submenu" => 0),
		array("name" => "Устройства", 		"href" => "device_list.php", 	"submenu" => 0),
		array("name" => "Склад", 			"href" => "", 					"submenu" => 2),
		array("name" => "Поступления", 		"href" => "receipt_list.php", 	"submenu" => 0),
		array("name" => "Списания", 		"href" => "disposal_list.php", 	"submenu" => 0));
	
	//Определяем какой пункт меню нужно подсветить
	$page_name = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));	
	
	echo "	<nav class='navbar navbar-expand-lg navbar-dark bg-primary mb-3'>
				<div class='container-fluid'>
					<a class='navbar-brand' href='/index.php'>
						<img src='/img/logo.png' height='40' class='d-inline-block align-top' alt=''>
					</a>
					
					<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
						<span class='navbar-toggler-icon'></span>
					</button>
					
					<div class='collapse navbar-collapse' id='navbarNav'>
						<ul class='navbar-nav me-auto'>";
	
	$i = 0;
	while($i < count($main_menu))
	{
		if($main_menu[$i]["submenu"] != 0)
		{
			echo "			<li class='nav-item dropdown'>
								<a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown' aria-expanded='false'>" . $main_menu[$i]["name"] . "</a>
								<ul class='dropdown-menu' aria-labelledby='navbarDropdown'>";

			for($j = 0; $j < $main_menu[$i]["submenu"]; $j++)
				echo " 				<li><a class='dropdown-item' href='" . $main_menu[$i + $j + 1]["href"] . "'>" . $main_menu[$i + $j + 1]["name"] . "</a></li>";

			echo "          	</ul>
							</li>";
							
			$i = $i + $main_menu[$i]["submenu"];
		}
		else
		{
			if($page_name == $main_menu[$i]["href"])
				echo "		<li class='nav-item active'>";
			else	
				echo "		<li class='nav-item'>";
			
			echo "				<a class='nav-link' href='" . $main_menu[$i]["href"] . "'>" . $main_menu[$i]["name"] . "</a>
							</li>";
		}
		
		$i = $i + 1;
	}				

	echo "				</ul>";
	
	//Если пользователь авторизован, выводим ссылку на его профиль
	echo "				<ul class='navbar-nav'>";
	if(isset($user_name))
		echo "				<li class='nav-item'>
								<a class='nav-link' href='user_profile.php?id=$user_id'><span class='fas fa-user'></span> $user_name</a>
							</li>	
							<li class='nav-item'>
								<a class='nav-link' href='user_logout.php'><span class='fas fa-sign-out-alt'></span> Выход</a>
							</li>";
	else
		echo "				<li class='nav-item'>
								<a class='nav-link' href='user_login.php'><span class='fas fa-sign-in-alt'></span> Вход</a>
							</li>";
	
	echo "				</ul>
					</div>
				</div>
			</nav>";
?>