<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;   //namespace

use \Hcode\Page;  //namespace

use \Hcode\PageAdmin;  //namespace

use \Hcode\Model\User;  //namespace


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

	User::verifylogin();

	$page = new PageAdmin(); //carrega o header

	$page->setTpl("index"); //carrega o body

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
});


$app->run();

 ?>