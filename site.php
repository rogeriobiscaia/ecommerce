<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;


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

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
			]);
	}




	$previous = [];

	if ($page > 1) {

	for ($i=2; $i > 1; $i--) { 
		array_push($previous, [
			'previous'=>'/categories/'.$category->getidcategory().'?page='.($page-1),
			'previouspage'=>$i
			]);
	}

	}



	
	$next = [];

	$totalpages = $pagination['pages'];



	if ($page < $totalpages) {
	

	for ($i=1; $i < 2; $i++) { 
		array_push($next, [
			'next'=>'/categories/'.$category->getidcategory().'?page='.($page+1),
			'nextpage'=>$i
			]);
	}

	}


	

	



	$page = new Page(); 

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages,
		'previous'=>$previous,
		'next'=>$next
		]); 
	
});



$app->get("/products/:desurl", function($desurl){


	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
		]);

});


$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart");
});




?>