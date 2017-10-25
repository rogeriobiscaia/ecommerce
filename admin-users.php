<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;



$app->get('/admin/admin/users', function() {

	header("Location: /admin/users");
	exit;
});



$app->get('/admin/users/admin/users', function() {

	header("Location: /admin/users");
	exit;
});


$app->get('/admin/orders/admin/users', function() {

	header("Location: /admin/users");
	exit;
});





/*

Abaixo seguem-se as funções de Administração CRUD

Create (C) Read (R) Update (U) e Delete (D)

*/



/*

Função Read (R) para ler e mostrar todos os utilizadores

*/



$app->get("/admin/users", function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') { 

		$pagination = User::getPageSearch($search, $page, 3);

	} else {

		$pagination = User::getPage($page, 3);
	}


	
	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
				]),
			'text'=>$x+1

			]);
	}

	$users = User::getPage($page);

	$page = new PageAdmin();


	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
		));
});



/*

Função Create (C) para criar um novo utilizador

*/



$app->get("/admin/users/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});





/*

Função Delete (D) para apagar um utilizador

Nota importante, o método Delete

$app->get("/admin/users/:iduser/delete", function($iduser)

deve ficar sempre acima do método Update

$app->get("/admin/users/:iduser", function($iduser)


*/



$app->get("/admin/users/:iduser/delete", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});







/*

Função Update (U) para alterar um utilizador

$app->get("/admin/users/:iduser", function($iduser)

Ao inserir o endereço com o :iduser a alterar

por exemplo para o :iduser = 4

http://www.hcodecommerce.com/admin/users/4

O programa vai assumir que a variável $iduser 

é igual a 4

ou seja function(4)

*/



$app->get("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
		));
	
});



$app->post("/admin/users/create", function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});



$app->post("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
});




?>