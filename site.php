<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

use \Hcode\DB\Sql;




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

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
		]);
});


$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;

});


$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});


$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});



$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();


	

	$cart->setFreight($_POST['zipcode']);
	

	header("Location: /cart");

	exit;
});





$app->get("/cart/freight/updatezipcode", function(){

	$cart = Cart::getFromSession();

	

	$deszipcode = ($_GET['zipcode']);

	/*

	var_dump($deszipcode);
	exit;

	 */


	 $deszipcode = str_replace('-', '', $deszipcode);

	$cart->setFreight($deszipcode);

	

	header("Location: /checkout?zipcode=$deszipcode&proceed=Finalizar+Compra");

	exit;
});






$app->get("/checkout", function(){

	User::verifyLogin(false);

	$address = new Address();

	$cart = Cart::getFromSession();

	if (isset($_GET['zipcode'])) {

		$_GET['zipcode'] = $cart->getdeszipcode();

	}


	if (isset($_GET['zipcode'])) {


		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	}


	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');
	

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getproducts(),
		'error'=>Address::getMsgError()

		]);


});


$app->post("/checkout", function(){

	User::verifyLogin(false);

	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {

		Address::setMsgError("Por favor indique o código postal.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {

		Address::setMsgError("Por favor indique o endereço.");
		header('Location: /checkout');
		exit;
	}


	if (!isset($_POST['descity']) || $_POST['descity'] === '') {

		Address::setMsgError("Por favor indique a cidade.");
		header('Location: /checkout');
		exit;
	}





	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
		]);


	$order->save();

	header("Location: /order/".$order->getidorder()."/paypal");
	exit;

});



$app->get("/order/:idorder/paypal", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		'header'=>false,
		'footer'=>false
		]);

	$page->setTpl("payment-paypal", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
		]);


});














$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
		]);


});


$app->post("/login", function(){

	try {

	User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());
	}

	header("Location: /checkout");
	exit;
});


$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;

});


$app->post("/register", function (){


	$_SESSION['registerValues'] = $_POST;


	if (!isset($_POST['name']) || $_POST['name'] == '') {

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}


	/*


	if (!isset($_POST['login']) || $_POST['login'] == '') {

		User::setErrorRegister("Preencha o seu login.");
		header("Location: /login");
		exit;
	}

	*/

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
	}


	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a sua senha.");
		header("Location: /login");
		exit;
	}



	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este endereço de e-mail já está a ser utilizado por outro utilizador.");
		header("Location: /login");
		exit;
	}


	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
		]);

	/*
	var_dump($user);
	exit;
	*/

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');

	exit;

});








$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");
});



$app->post("/forgot", function(){

	

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;
	
});



$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");
	
});



$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
		));
});



$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
		]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});



$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();



	/*
	var_dump($user);
	exit;
	*/



	$iduser = $user->getiduser();


	$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
			));

		//$this->setData($results[0]);

		
		$user = $results[0];


		$user['desperson'] = utf8_encode($user['desperson']);

		$user['deslogin'] = utf8_encode($user['deslogin']);


	  



	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user,
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
		]);




});


$app->post("/profile", function(){

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome");
		header('Location: /profile');
		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu email");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();

	if ($_POST['desemail'] != $user->getedesemail()) {

		if (User::checkLoginExist($_POST['desemail']) === true ) {

			User::setError("Este endereço de e-mail já está registado.");
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['deslogin'];

	$user->setData($_POST);

	$user->save();

	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');
	exit;

});












$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
		]);
});


$app->get("/profile/orders", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
		]);
});


$app->get("/profile/orders/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
		]);
});


$app->get("/profile/change-password", function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
		]);
});




$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {

		User::setError("Escreva a senha atual.");
		header("Location: /profile/change-password");
		exit;

	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {

		User::setError("Escreva a nova senha.");
		header("Location: /profile/change-password");
		exit;

	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {

		User::setError("Confirme a nova senha.");
		header("Location: /profile/change-password");
		exit;

	}

	if ($_POST['current_pass'] === $_POST['new_pass']) {

		User::setError("A nova senha deve ser diferente da senha atual.");
		header("Location: /profile/change-password");
		exit;
	}

	$user = User::getFromSession();

	if (!password_verify($_POST['current_pass'], $user->getdespassword())) {

		User::setError("A senha é inválida. Por favor introduza a senha correcta.");
		header("Location: /profile/change-password");
		exit;

	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setSuccess("Senha alterada com successo");

	header("Location: /profile/change-password");
	exit;


});


?>