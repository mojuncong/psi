<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizlogService;
use Home\Common\FIdConst;
use Home\Service\UpdateDBService;

/**
 * 业务日志Controller
 *
 * @author 李静波
 *        
 */
class BizlogController extends PSIBaseController {

	/**
	 * 业务日志 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::BIZ_LOG)) {
			$this->initVar();
			
			$this->assign("title", "业务日志");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Bizlog/index");
		}
	}

	/**
	 * 查询业务日志
	 */
	public function logList() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$bs = new BizlogService();
			$this->ajaxReturn($bs->logList($params));
		}
	}

	/**
	 * 升级数据库
	 */
	public function updateDatabase() {
		if (IS_POST) {
			$bs = new UpdateDBService();
			$this->ajaxReturn($bs->updateDatabase());
		}
	}
}