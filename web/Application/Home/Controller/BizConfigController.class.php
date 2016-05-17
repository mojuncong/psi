<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizConfigService;
use Home\Common\FIdConst;

/**
 * 业务设置Controller
 *
 * @author 李静波
 *        
 */
class BizConfigController extends PSIBaseController {

	/**
	 * 业务设置 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::BIZ_CONFIG)) {
			$this->initVar();
			
			$this->assign("title", "业务设置");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/BizConfig/index");
		}
	}

	/**
	 * 获得所有的配置项目
	 */
	public function allConfigs() {
		if (IS_POST) {
			$bs = new BizConfigService();
			
			$params = array(
					"companyId" => I("post.companyId")
			);
			
			$this->ajaxReturn($bs->allConfigs($params));
		}
	}

	/**
	 * 获得所有的配置项目以及配置项目附带的数据
	 */
	public function allConfigsWithExtData() {
		if (IS_POST) {
			$bs = new BizConfigService();
			
			$params = array(
					"companyId" => I("post.companyId")
			);
			
			$this->ajaxReturn($bs->allConfigsWithExtData($params));
		}
	}

	/**
	 * 编辑配置项
	 */
	public function edit() {
		if (IS_POST) {
			$bs = new BizConfigService();
			
			$params = array(
					"companyId" => I("post.companyId"),
					"9000-01" => I("post.value9000-01"),
					"9000-02" => I("post.value9000-02"),
					"9000-03" => I("post.value9000-03"),
					"9000-04" => I("post.value9000-04"),
					"9000-05" => I("post.value9000-05"),
					"1003-02" => I("post.value1003-02"),
					"2001-01" => I("post.value2001-01"),
					"2002-01" => I("post.value2002-01"),
					"2002-02" => I("post.value2002-02"),
					"9001-01" => I("post.value9001-01"),
					"9002-01" => I("post.value9002-01"),
					"9003-01" => I("post.value9003-01"),
					"9003-02" => I("post.value9003-02"),
					"9003-03" => I("post.value9003-03"),
					"9003-04" => I("post.value9003-04"),
					"9003-05" => I("post.value9003-05"),
					"9003-06" => I("post.value9003-06"),
					"9003-07" => I("post.value9003-07"),
					"9003-08" => I("post.value9003-08")
			);
			
			$this->ajaxReturn($bs->edit($params));
		}
	}

	/**
	 * 获得当前用户可以设置的公司
	 */
	public function getCompany() {
		if (IS_POST) {
			$bs = new BizConfigService();
			$this->ajaxReturn($bs->getCompany());
		}
	}
}