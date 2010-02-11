<?php
	error_reporting(E_ALL | E_STRICT);

	define('FCPATH', __FILE__);
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
	define('ROOT', dirname(__FILE__).'/');
	define('BASEPATH', realpath('./').'/');
	define('APPPATH', realpath('./application').'/');

	require BASEPATH.'codeigniter/CodeIgniter.php';