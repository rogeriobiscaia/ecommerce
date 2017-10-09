<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;

use \Hcode\Model;

use \Hcode\Mailer;

use \Hcode\Model\User;





class Cart extends Model {

	const SESSION = "Cart";

	public static function getFromSession()
	{

		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		} else {

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0) {

				$data = [
				'dessessionid'=>session_id()
				];

				if (User::checkLogin(false)) {

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

				


				
			}
		}

		return $cart;

		/*

		if (isset($_SESSION[Cart::SESSION])  // Verifica se a sessão existe,
		se existir faz a verificação seguinte

		&& $_SESSION[Cart::SESSION]['idcart'] > 0) 
		// Verifica se dentro da sessão está o id do carrinho
		ou seja o carrinho foi inserido no banco de dados

		*/
	}



	public function setToSession() 
	{

		$_SESSION[Cart::SESSION] = $this->getValues();
	}



	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
			]);

		if (count($results) > 0) {

		$this->setData($results[0]);

	    }

	}


	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
			]);

		if (count($results) > 0) {

		$this->setData($results[0]);

	    }


	}



	public function save()
	{

		$numberofdays = 10;

		$deszipcode = 0;


		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$numberofdays
			]);

		$this->setData($results[0]);
	}


	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO  tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
			]);
	}


	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		if ($all) {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
				]);

		} else {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND 
				dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
				]);
		}


		$newprice = 0;

		


		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($newprice);

		
	}

		

	



	public function getProducts()
	{

		$sql = new Sql();

		$rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
		 COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal FROM tb_cartsproducts a INNER JOIN tb_products b ON a.idproduct = b.idproduct
		 WHERE a.idcart = :idcart AND a.dtremoved IS NULL
		 GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
		 ORDER BY b.desproduct", [
		 'idcart'=>$this->getidcart()

		 ]);


		return Product::checkList($rows);
		
	}



	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, 
			COUNT(*) AS nrqtd 
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;", 
			[
			':idcart'=>$this->getidcart()
			]);

		if (count($results) > 0) {
			return $results[0];
		} else {
			return [];
		}
		
	}



	public function setFreight($nrzipcode)
	{

		$totals = $this->getProductsTotals();



	    $nrzipcode = str_replace('-', '', $nrzipcode);


	    $zipcodelength = (strlen($nrzipcode));


	    $freightvalue = 0;


	    $firstzipcodenumber = substr($nrzipcode,0,1);

	    if ($nrzipcode == 0 ) {

				throw new \Exception("O código postal não pode ser 0");

			}



		if ($zipcodelength < 7 || $zipcodelength > 7) {

			throw new \Exception("O código postal tem de ter 7 números");
		}




		if ($firstzipcodenumber == 0) {

			throw new \Exception("O código postal não pode começar por 0");
			
		}



		if ($firstzipcodenumber == 9) {

			$freightvalue = 10;
			
		}

		
		if ($totals['nrqtd'] > 0) {

			$this->setvlfreight($freightvalue);
			$this->setdeszipcode($nrzipcode);

			$this->save();

			
			
		} else {

		}


	}


	public static function formatValueToDecimal($value):float
	{

		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}



	public function getValues()
	{

		$this->getCalculateTotal();

		return parent::getValues();
	}


	public function getCalculateTotal()
	{

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);

		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());
	}




}

?>