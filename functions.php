<?php

use \Hcode\Model\User;
use \Hcode\DB\Sql;
use \Hcode\Model\Cart;


function formatPrice($vlprice)
{

	if (!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");

}


function formatDate($date)
{

	return date('d/m/Y', strtotime($date));
}


function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}


function getUserName()
{

	$user = User::getFromSession();


	$iduser = $user->getiduser();


	$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
			));

		//$this->setData($results[0]);

		
		$data = $results[0];


		//$data['desperson'] = utf8_encode($data['desperson']);

		$data['deslogin'] = utf8_encode($data['deslogin']);

		$resultlogin = ($data['deslogin']);

		
		//var_dump($resultlogin);
		//exit;
		
	

	return $resultlogin;
	
}


function getCartNrQtd()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];
}



function getCartVlSubTotal()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);
}


?>