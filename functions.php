<?php

use \Hcode\Model\User;
use \Hcode\DB\Sql;


function formatPrice($vlprice)
{

	if (!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");

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


	

	return $resultlogin;
	
}




?>