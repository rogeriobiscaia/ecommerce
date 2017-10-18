<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;

use \Hcode\Model;

use \Hcode\Model\Cart;

use \Hcode\Model\User;







class Address extends Model {

	const SESSION_ERROR = "AddressError";

	public static function getCEP($nrcep)
	{

		/*

		$cart = new Cart();

		$data = $cart->getFromSessionID();

		*/

		
		$nrcep = str_replace("-", "", $nrcep);



		//http://codigospostais.appspot.com/cp7?codigo=1250261

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://codigospostais.appspot.com/cp7?codigo=$nrcep");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		

		return $data;

		
		
		
		
	}



	public function loadFromCEP($nrcep)
	{

		$data = Address::getCEP($nrcep);

		/*
		var_dump($data);
		exit;
		*/
		

		if (isset($data['arteria']) && $data['arteria']) {

			$this->setdesaddress($data['arteria']);
			$this->setdescomplement($data['troco']);
			$this->setdescity($data['localidade']);
			$this->setdescountry('Portugal');
			$this->setdeszipcode($nrcep);

		}

		
	}


	public function save()
	{

		$sql = new Sql();


		
		$pidaddress = 0;




		$desstate = "semestado";

		$desdistrict = "semdistrito";

		


		//":desperson"=>$this->getdesperson(),

		


		$results = $sql->select("CALL sp_addresses_save(:pidaddress, :pidperson, :pdesaddress, :pdescomplement, :pdescity, :pdesstate, :pdescountry, :pdeszipcode, :pdesdistrict)", [
			':pidaddress'=>$this->getidaddress(),
			//':pidaddress'=>$pidaddress,
			':pidperson'=>$this->getidperson(),
			':pdesaddress'=>$this->getdesaddress(),
			':pdescomplement'=>$this->getdescomplement(),
			':pdescity'=>$this->getdescity(),
			':pdesstate'=>$desstate,
			//':pdesstate'=>$this->getdesstate(),
			':pdescountry'=>$this->getdescountry(),
			':pdeszipcode'=>$this->getdeszipcode(),
			':pdesdistrict'=>$desdistrict
			//':pdesdistrict'=>$this->getdesdistrict()

			]);


			



		if (count($results) > 0) {

			$this->setData($results[0]);
		}
	}

	/*

	public function getValues()
	{

		$idaddress=$this->getidaddress();
	    $idperson=$this->getidperson();
	    $desaddress=$this->getdesaddress();
		$descomplement=$this->getdescomplement();
		$descity=$this->getdescity();
		$descountry=$this->getdescountry();
		$deszipcode=$this->getdeszipcode();
	}

	*/





	public static function setMsgError($msg)
	{

		$_SESSION[Address::SESSION_ERROR] = $msg;
	}


	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;

	}


	public static function clearMsgError()
	{

		$_SESSION[Address::SESSION_ERROR] = NULL;
	}



}

?>