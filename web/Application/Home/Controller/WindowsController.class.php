<?php

namespace Home\Controller;

use Think\Controller;

/**
 * Windows客户端 Controller
 *
 * @author 李静波
 *        
 */
class WindowsController extends Controller {

	/**
	 * 客户端设置服务器地址，测试服务器地址是否设置正确
	 */
	public function test() {
		if (IS_POST) {
			$this->ajaxReturn(array(
					"success" => true
			));
		}
	}
}