<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;   //namespace

use \Hcode\Page;  //namespace

use \Hcode\PageAdmin;  //namespace

use \Hcode\Model\User;  //namespace

use \Hcode\Model\Category;  //namespace


$app = new Slim();

$app->config('debug', true);

require_once("site.php");

require_once("admin.php");

require_once("admin-users.php");

require_once("admin-categories.php");

require_once("admin-products.php");


$app->run();

 ?>