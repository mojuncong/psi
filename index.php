<?php
require "Mobile_Detect.php";

$_isCGI = (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0;

if ($_isCGI) {
	// CGI/FASTCGI模式下
	$_temp = explode('.php', $_SERVER['PHP_SELF']);
	$_phpFile = rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0] . '.php'), '/');
} else {
	$_phpFile = rtrim($_SERVER['SCRIPT_NAME'], '/');
}

$_root = rtrim(dirname($_phpFile), '/');
$_root = ($_root == '/' || $_root == '\\') ? '' : $_root;

$detect = new Mobile_Detect();
if ($detect->isMobile()) {
	header('Location: ' . $_root . '/web/Mobile');
} else {
	header('Location: ' . $_root . '/web/');
}