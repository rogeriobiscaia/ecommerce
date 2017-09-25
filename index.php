<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;   //namespace

use \Hcode\Page;  //namespace

use \Hcode\PageAdmin;  //namespace


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


	$page = new PageAdmin(); //carrega o header

	$page->setTpl("index"); //carrega o body

});


$app->run();

 ?>