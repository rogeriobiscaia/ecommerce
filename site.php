<?php

use \Hcode\Page;
use \Hcode\Model\Product;


$app->get('/', function() {
    
	//$sql = new Hcode\DB\Sql();

	//$results = $sql->select("SELECT * FROM tb_users");

	//echo json_encode($results);

	$products = Product::listAll();

	$page = new Page(); //carrega o header

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
		]); //carrega o body

});



?>