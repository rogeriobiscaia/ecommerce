<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;   //namespace

use \Hcode\Page;  //namespace

use \Hcode\PageAdmin;  //namespace

use \Hcode\Model\User;  //namespace

use \Hcode\Model\Category;  //namespace


$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	//$sql = new Hcode\DB\Sql();

	//$results = $sql->select("SELECT * FROM tb_users");

	//echo json_encode($results);

	$page = new Page(); //carrega o header

	$page->setTpl("index"); //carrega o body

});


$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin(); //carrega o header

	$page->setTpl("index"); //carrega o body

});



$app->get('/admin/', function() {

	header("Location: /admin");
	exit;
});



$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

	$page->setTpl("login");
});


$app->post('/admin/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});


$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});




$app->get('/admin/admin/users', function() {

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

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
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



$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

	$page->setTpl("forgot");
});



$app->post("/admin/forgot", function(){

	

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
	
});



$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

	$page->setTpl("forgot-sent");
	
});



$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
		));
});



$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
		]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

	$page->setTpl("forgot-reset-success");

});


$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", ['categories'=>$categories
		]);

});



$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});



$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});


$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});



$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
		]);
});



$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();
	
	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});



$app->run();

 ?>