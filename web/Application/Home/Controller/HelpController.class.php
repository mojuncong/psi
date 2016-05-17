<?php

namespace Home\Controller;

use Think\Controller;

/**
 * 帮助Controller
 *
 * @author 李静波
 *        
 */
class HelpController extends PSIBaseController {
	private $HELP_DEFAUT_URL = "http://psi.mydoc.io/";
	private $HELP_URL = "http://psi.mydoc.io/?t=";

	public function index() {
		$key = I("get.t");
		switch ($key) {
			case "login" :
				redirect($this->HELP_URL . "50507");
				break;
			case "user" :
				redirect($this->HELP_URL . "54868");
				break;
			default :
				redirect($this->HELP_DEFAUT_URL);
		}
	}
}