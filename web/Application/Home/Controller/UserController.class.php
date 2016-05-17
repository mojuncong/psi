<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\InstallService;

/**
 * 用户管理Controller
 *
 * @author 李静波
 *        
 */
class UserController extends PSIBaseController {

	/**
	 * 用户管理-主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::USR_MANAGEMENT)) {
			$this->initVar();
			
			$this->assign("title", "用户管理");
			
			$this->assign("pAddOrg", $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_ORG) ? 1 : 0);
			$this->assign("pEditOrg", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_ORG) ? 1 : 0);
			$this->assign("pDeleteOrg", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_ORG) ? 1 : 0);
			$this->assign("pAddUser", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_USER) ? 1 : 0);
			$this->assign("pEditUser", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_USER) ? 1 : 0);
			$this->assign("pDeleteUser", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_USER) ? 1 : 0);
			$this->assign("pChangePassword", 
					$us->hasPermission(FIdConst::USER_MANAGEMENT_CHANGE_USER_PASSWORD) ? 1 : 0);
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/User/index");
		}
	}

	/**
	 * 登录页面
	 */
	public function login() {
		if (session("loginUserId")) {
			// 已经登录了，就返回首页
			redirect(__ROOT__);
		}
		
		// 自动初始化数据库
		$installService = new InstallService();
		$installService->autoInstallWhenFirstRun();
		
		$this->initVar();
		
		$this->assign("title", "登录");
		
		$this->assign("returnPage", I("get.returnPage"));
		
		$us = new UserService();
		$this->assign("demoInfo", $us->getDemoLoginInfo());
		
		$this->display();
	}

	/**
	 * 页面：修改我的密码
	 */
	public function changeMyPassword() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::CHANGE_MY_PASSWORD)) {
			$this->initVar();
			
			$this->assign("loginUserId", $us->getLoginUserId());
			$this->assign("loginName", $us->getLoginName());
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$this->assign("title", "修改我的密码");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/User/changeMyPassword");
		}
	}

	/**
	 * 修改我的密码，POST方法
	 */
	public function changeMyPasswordPOST() {
		if (IS_POST) {
			$us = new UserService();
			$params = array(
					"userId" => I("post.userId"),
					"oldPassword" => I("post.oldPassword"),
					"newPassword" => I("post.newPassword")
			);
			
			$result = $us->changeMyPassword($params);
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 用户登录，POST方法
	 */
	public function loginPOST() {
		if (IS_POST) {
			$loginName = I("post.loginName");
			$password = I("post.password");
			$fromDevice = I("post.fromDevice");
			$ip = I("post.ip");
			$ipFrom = I("post.ipFrom");
			
			session("PSI_login_user_ip", $ip);
			session("PSI_login_user_ip_from", $ipFrom);
			
			$us = new UserService();
			$this->ajaxReturn($us->doLogin($loginName, $password, $fromDevice));
		}
	}

	/**
	 * 获得组织机构树
	 */
	public function allOrgs() {
		$us = new UserService();
		$data = $us->allOrgs();
		
		$this->ajaxReturn($data);
	}

	/**
	 * 获得组织机构下的用户列表
	 */
	public function users() {
		if (IS_POST) {
			$us = new UserService();
			$params = array(
					"orgId" => I("post.orgId"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$this->ajaxReturn($us->users($params));
		}
	}

	/**
	 * 新建或编辑组织结构
	 */
	public function editOrg() {
		if (IS_POST) {
			$us = new UserService();
			$id = I("post.id");
			$name = I("post.name");
			$parentId = I("post.parentId");
			$orgCode = I("post.orgCode");
			
			if ($id) {
				// 编辑组织机构
				if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_ORG)) {
					$this->ajaxReturn($this->noPermission("编辑组织机构"));
					return;
				}
			} else {
				// 新增组织机构
				if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_ORG)) {
					$this->ajaxReturn($this->noPermission("新增组织机构"));
					return;
				}
			}
			
			$result = $us->editOrg($id, $name, $parentId, $orgCode);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 获得组织机构的名称
	 */
	public function orgParentName() {
		if (IS_POST) {
			$us = new UserService();
			$id = I("post.id");
			$data = $us->orgParentName($id);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 删除组织机构
	 */
	public function deleteOrg() {
		if (IS_POST) {
			$us = new UserService();
			
			if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_ORG)) {
				$this->ajaxReturn($this->noPermission("删除组织机构"));
				return;
			}
			
			$id = I("post.id");
			$data = $us->deleteOrg($id);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 新增或编辑用户
	 */
	public function editUser() {
		if (IS_POST) {
			$us = new UserService();
			
			if (I("post.id")) {
				// 编辑用户
				if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_USER)) {
					$this->ajaxReturn($this->noPermission("编辑用户"));
					return;
				}
			} else {
				// 新增用户
				if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_USER)) {
					$this->ajaxReturn($this->noPermission("新增用户"));
					return;
				}
			}
			
			$params = array(
					"id" => I("post.id"),
					"loginName" => I("post.loginName"),
					"name" => I("post.name"),
					"orgCode" => I("post.orgCode"),
					"orgId" => I("post.orgId"),
					"enabled" => I("post.enabled") == "true" ? 1 : 0,
					"gender" => I("post.gender"),
					"birthday" => I("post.birthday"),
					"idCardNumber" => I("post.idCardNumber"),
					"tel" => I("post.tel"),
					"tel02" => I("post.tel02"),
					"address" => I("post.address")
			);
			
			$result = $us->editUser($params);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 删除用户
	 */
	public function deleteUser() {
		if (IS_POST) {
			$us = new UserService();
			
			if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_USER)) {
				$this->ajaxReturn($this->noPermission("删除用户"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			
			$result = $us->deleteUser($params);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 修改用户的密码
	 */
	public function changePassword() {
		if (IS_POST) {
			$us = new UserService();
			
			if (! $us->hasPermission(FIdConst::USER_MANAGEMENT_CHANGE_USER_PASSWORD)) {
				$this->ajaxReturn($this->noPermission("修改用户密码"));
				return;
			}
			
			$params = array(
					"id" => I("post.id"),
					"password" => I("post.password")
			);
			
			$result = $us->changePassword($params);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 用户自定义字段，查询数据
	 */
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$us = new UserService();
			$this->ajaxReturn($us->queryData($queryKey));
		}
	}

	/**
	 * 根据数据域返回可用的组织机构
	 */
	public function orgWithDataOrg() {
		if (IS_POST) {
			$us = new UserService();
			$this->ajaxReturn($us->orgWithDataOrg());
		}
	}

	/**
	 * 选择数据域自定义字段， 查询数据
	 */
	public function queryUserDataOrg() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$us = new UserService();
			$this->ajaxReturn($us->queryUserDataOrg($queryKey));
		}
	}
}