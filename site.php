<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;


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



$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page(); 

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
		]); 
	
});




?>