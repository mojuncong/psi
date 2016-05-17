<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\BizConfigService;
use Home\Service\UserService;

/**
 * PSI Base Controller
 *
 * @author 李静波
 *        
 */
class PSIBaseController extends Controller {

	/**
	 * 初始化每个页面都需要的参数值
	 */
	protected function initVar() {
		// 产品名称
		$bcs = new BizConfigService();
		$this->assign("productionName", $bcs->getProductionName());
		
		// JS调用的base uri
		$this->assign("uri", __ROOT__ . "/");
		
		// 当前登录用户名
		$us = new UserService();
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		// 时间标志，用于浏览器及时刷新JS文件
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
	}

	/**
	 * 跳转到登录页面
	 */
	protected function gotoLoginPage($returnPage = null) {
		$url = __ROOT__ . "/Home/User/login";
		
		if ($returnPage) {
			$url .= "?returnPage=" . __ROOT__ . $returnPage;
		}
		
		redirect($url);
	}
	
	/**
	 * 没有权限
	 */
	protected function noPermission($m) {
		return array(
				"success" => false,
				"msg" => "您没有[$m]的操作权限"
		);
	}
}