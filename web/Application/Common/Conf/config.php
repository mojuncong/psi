<?php

function PSI_getMoPaasV2MySQLConfig() {
	$services = getenv("VCAP_SERVICES");
	$services_json = json_decode($services, true);
	
	// 数据库使用 MoPaaS提供的 MySQL-docker
	$mysql_config = $services_json["MySQL-docker-5.5"][0]["credentials"];
	
	return $mysql_config;
}

function PSI_getHost() {
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["host"];
	}
	
	// 本地单机部署，发现写IP地址比localhost，数据库要快很多
	return "127.0.0.1";
}

function PSI_getDBName() {
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["db"];
	}
	
	return "psi";
}

function PSI_getUser() {
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["user"];
	}
	
	return "root";
}

function PSI_getPassword() {
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["password"];
	}
	
	return "";
}

function PSI_getPort() {
	// MoPaaS V2
	$cfg = PSI_getMoPaasV2MySQLConfig();
	if ($cfg) {
		return $cfg["port"];
	}
	
	return 3306;
}

return array(
		'URL_CASE_INSENSITIVE' => false,
		'SHOW_ERROR_MSG' => true,
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => PSI_getHost(), // 服务器地址
		'DB_NAME' => PSI_getDBName(), // 数据库名
		'DB_USER' => PSI_getUser(), // 用户名
		'DB_PWD' => PSI_getPassword(), // 密码
		'DB_PORT' => PSI_getPort() // 端口
);

/*

// 也可以把上面的代码都删除掉，然后按下面的写法来配置

return array(
		'URL_CASE_INSENSITIVE' => false,
		'SHOW_ERROR_MSG' => true,
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => '127.0.0.1', // 服务器地址
		'DB_NAME' => 'psi', // 数据库名
		'DB_USER' => 'root', // 用户名
		'DB_PWD' => '', // 密码
		'DB_PORT' => 3306 // 端口
);
 
 */
