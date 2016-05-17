<?php

namespace Home\Service;

use Home\Common\DemoConst;
use Home\Common\FIdConst;
use Home\DAO\UserDAO;

/**
 * 用户Service
 *
 * @author 李静波
 */
class UserService extends PSIBaseService {
	private $LOG_CATEGORY = "用户管理";

	public function getDemoLoginInfo() {
		if ($this->isDemo()) {
			return "您当前处于演示环境，默认的登录名和密码均为 admin <br/>更多帮助请点击 [帮助] 按钮来查看 <br /><div style='color:red'>请勿在演示环境中保存正式数据，演示数据库通常每天在21:00后会清空一次</div>";
		} else {
			return "";
		}
	}

	/**
	 * 判断当前用户是否有$fid对应的权限
	 *
	 * @param string $fid
	 *        	fid
	 * @return boolean true：有对应的权限
	 */
	public function hasPermission($fid = null) {
		$result = session("loginUserId") != null;
		if (! $result) {
			return false;
		}
		
		$userId = $this->getLoginUserId();
		
		if ($userId == DemoConst::ADMIN_USER_ID) {
			// admin 用户是超级管理员
			return true;
		}
		
		// 判断用户是否被禁用
		// 被禁用的用户，视为没有权限
		$ud = new UserDAO();
		if ($ud->isDisabled($userId)) {
			return false;
		}
		
		// 修改我的密码，重新登录，首页，使用帮助，关于，购买商业服务，这六个功能对所有的在线用户均不需要特别的权限
		$idList = array(
				FIdConst::CHANGE_MY_PASSWORD,
				FIdConst::RELOGIN,
				FIdConst::HOME,
				FIdConst::HELP,
				FIdConst::ABOUT,
				FIdConst::PSI_SERVICE
		);
		if ($fid == null || in_array($fid, $idList)) {
			return $result;
		}
		
		$sql = "select count(*) as cnt 
				from  t_role_user ru, t_role_permission rp, t_permission p 
				where ru.user_id = '%s' and ru.role_id = rp.role_id 
				      and rp.permission_id = p.id and p.fid = '%s' ";
		$data = M()->query($sql, $userId, $fid);
		
		return $data[0]["cnt"] > 0;
	}

	public function getLoginUserId() {
		return session("loginUserId");
	}

	public function getLoginUserName() {
		$sql = "select name from t_user where id = '%s' ";
		
		$data = M()->query($sql, $this->getLoginUserId());
		
		if ($data) {
			return $data[0]["name"];
		} else {
			return "";
		}
	}

	public function getLoignUserNameWithOrgFullName() {
		$userName = $this->getLoginUserName();
		if ($userName == "") {
			return $userName;
		}
		$sql = "select o.full_name
				from t_org o, t_user u
				where o.id = u.org_id and u.id = '%s' ";
		$data = M()->query($sql, $this->getLoginUserId());
		$orgFullName = "";
		if ($data) {
			$orgFullName = $data[0]["full_name"];
		}
		
		return addslashes($orgFullName . "\\" . $userName);
	}

	public function getLoginName() {
		$sql = "select login_name from t_user where id = '%s' ";
		
		$data = M()->query($sql, $this->getLoginUserId());
		
		if ($data) {
			return $data[0]["login_name"];
		} else {
			return "";
		}
	}

	public function doLogin($loginName, $password, $fromDevice) {
		$sql = "select id from t_user where login_name = '%s' and password = '%s' and enabled = 1";
		
		$user = M()->query($sql, $loginName, md5($password));
		
		if ($user) {
			session("loginUserId", $user[0]["id"]);
			
			$bls = new BizlogService();
			$bls->insertBizlog("登录系统");
			return $this->ok($user[0]["id"]);
		} else {
			return $this->bad("用户名或者密码错误");
		}
	}

	private function allOrgsInternal($parentId, $db) {
		$result = array();
		$sql = "select id, name, org_code, full_name, data_org 
				from t_org 
				where parent_id = '%s' 
				order by org_code";
		$data = $db->query($sql, $parentId);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["text"] = $v["name"];
			$result[$i]["orgCode"] = $v["org_code"];
			$result[$i]["fullName"] = $v["full_name"];
			$result[$i]["dataOrg"] = $v["data_org"];
			
			$c2 = $this->allOrgsInternal($v["id"], $db); // 递归调用自己
			
			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($c2) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}

	public function allOrgs() {
		$ds = new DataOrgService();
		$queryParams = array();
		$rs = $ds->buildSQL(FIdConst::USR_MANAGEMENT, "t_org");
		
		$sql = "select id, name, org_code, full_name, data_org 
				from t_org 
				where parent_id is null ";
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		$sql .= " order by org_code";
		$db = M();
		$orgList1 = $db->query($sql, $queryParams);
		$result = array();
		
		// 第一级组织
		foreach ( $orgList1 as $i => $org1 ) {
			$result[$i]["id"] = $org1["id"];
			$result[$i]["text"] = $org1["name"];
			$result[$i]["orgCode"] = $org1["org_code"];
			$result[$i]["fullName"] = $org1["full_name"];
			$result[$i]["dataOrg"] = $org1["data_org"];
			
			// 第二级
			$c2 = $this->allOrgsInternal($org1["id"], $db);
			
			$result[$i]["children"] = $c2;
			$result[$i]["leaf"] = count($c2) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}

	public function users($params) {
		$orgId = $params["orgId"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select id, login_name,  name, enabled, org_code, gender, birthday, id_card_number, tel,
				    tel02, address, data_org 
				from t_user
				where org_id = '%s' 
				order by org_code
				limit %d , %d ";
		
		$data = $db->query($sql, $orgId, $start, $limit);
		
		$result = array();
		
		foreach ( $data as $key => $value ) {
			$result[$key]["id"] = $value["id"];
			$result[$key]["loginName"] = $value["login_name"];
			$result[$key]["name"] = $value["name"];
			$result[$key]["enabled"] = $value["enabled"];
			$result[$key]["orgCode"] = $value["org_code"];
			$result[$key]["gender"] = $value["gender"];
			$result[$key]["birthday"] = $value["birthday"];
			$result[$key]["idCardNumber"] = $value["id_card_number"];
			$result[$key]["tel"] = $value["tel"];
			$result[$key]["tel02"] = $value["tel02"];
			$result[$key]["address"] = $value["address"];
			$result[$key]["dataOrg"] = $value["data_org"];
		}
		
		$sql = "select count(*) as cnt
				from t_user 
				where org_id = '%s' ";
		
		$data = $db->query($sql, $orgId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 做类似这种增长 '0101' => '0102'，组织机构的数据域+1
	 */
	private function incDataOrg($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 2);
		$seed = intval(substr($dataOrg, - 2)) + 1;
		
		return $pre . str_pad($seed, 2, "0", STR_PAD_LEFT);
	}

	/**
	 * 做类似这种增长 '01010001' => '01010002', 用户的数据域+1
	 */
	private function incDataOrgForUser($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 4);
		$seed = intval(substr($dataOrg, - 4)) + 1;
		
		return $pre . str_pad($seed, 4, "0", STR_PAD_LEFT);
	}

	private function modifyFullName($db, $id) {
		$sql = "select full_name from t_org where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return true;
		}
		
		$fullName = $data[0]["full_name"];
		
		$sql = "select id, name from t_org where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$idChild = $v["id"];
			$nameChild = $v["name"];
			$fullNameChild = $fullName . "\\" . $nameChild;
			$sql = "update t_org set full_name = '%s' where id = '%s' ";
			$rc = $db->execute($sql, $fullNameChild, $idChild);
			if ($rc === false) {
				return false;
			}
			
			$rc = $this->modifyFullName($db, $idChild); // 递归调用自身
			if ($rc === false) {
				return false;
			}
		}
		
		return true;
	}

	private function modifyDataOrg($db, $parentId, $id) {
		// 修改自身的数据域
		$dataOrg = "";
		if ($parentId == null) {
			$sql = "select data_org from t_org 
					where parent_id is null and id <> '%s'
					order by data_org desc limit 1";
			$data = $db->query($sql, $id);
			if (! $data) {
				$dataOrg = "01";
			} else {
				$dataOrg = $this->incDataOrg($data[0]["data_org"]);
			}
		} else {
			$sql = "select data_org from t_org 
					where parent_id = '%s' and id <> '%s'
					order by data_org desc limit 1";
			$data = $db->query($sql, $parentId, $id);
			if ($data) {
				$dataOrg = $this->incDataOrg($data[0]["data_org"]);
			} else {
				$sql = "select data_org from t_org where id = '%s' ";
				$data = $db->query($sql, $parentId);
				$dataOrg = $data[0]["data_org"] . "01";
			}
		}
		
		$sql = "update t_org
				set data_org = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $dataOrg, $id);
		if ($rc === false) {
			return false;
		}
		
		// 修改 人员的数据域
		$sql = "select id from t_user
				where org_id = '%s'
				order by org_code ";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$userId = $v["id"];
			$index = str_pad($i + 1, 4, "0", STR_PAD_LEFT);
			$udo = $dataOrg . $index;
			
			$sql = "update t_user
					set data_org = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $udo, $userId);
			if ($rc === false) {
				return false;
			}
		}
		
		// 修改下级组织机构的数据域
		$rc = $this->modifySubDataOrg($db, $dataOrg, $id);
		
		if ($rc === false) {
			return false;
		}
		
		return true;
	}

	private function modifySubDataOrg($db, $parentDataOrg, $parentId) {
		$sql = "select id from t_org where parent_id = '%s' order by org_code";
		$data = $db->query($sql, $parentId);
		foreach ( $data as $i => $v ) {
			$subId = $v["id"];
			
			$next = str_pad($i + 1, 2, "0", STR_PAD_LEFT);
			$dataOrg = $parentDataOrg . $next;
			$sql = "update t_org
					set data_org = '%s'
					where id = '%s' ";
			$db->execute($sql, $dataOrg, $subId);
			
			// 修改该组织机构的人员的数据域
			$sql = "select id from t_user
				where org_id = '%s'
				order by org_code ";
			$udata = $db->query($sql, $subId);
			foreach ( $udata as $j => $u ) {
				$userId = $u["id"];
				$index = str_pad($j + 1, 4, "0", STR_PAD_LEFT);
				$udo = $dataOrg . $index;
				
				$sql = "update t_user
					set data_org = '%s'
					where id = '%s' ";
				$rc = $db->execute($sql, $udo, $userId);
				if ($rc === false) {
					return false;
				}
			}
			
			$rc = $this->modifySubDataOrg($db, $dataOrg, $subId); // 递归调用自身
			if ($rc === false) {
				return false;
			}
		}
		
		return true;
	}

	public function editOrg($id, $name, $parentId, $orgCode) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		if ($this->isDemo()) {
			if ($id == DemoConst::ORG_COMPANY_ID) {
				return $this->bad("在演示环境下，组织机构[公司]不希望被您修改，请见谅");
			}
			if ($id == DemoConst::ORG_INFODEPT_ID) {
				return $this->bad("在演示环境下，组织机构[信息部]不希望被您修改，请见谅");
			}
		}
		
		$db = M();
		$db->startTrans();
		
		$log = null;
		
		if ($id) {
			// 编辑
			if ($parentId == $id) {
				$db->rollback();
				return $this->bad("上级组织不能是自身");
			}
			$fullName = "";
			
			$sql = "select parent_id from t_org where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的组织机构不存在");
			}
			$oldParentId = $data[0]["parent_id"];
			
			if ($parentId == "root") {
				$parentId = null;
			}
			
			if ($parentId == null) {
				$fullName = $name;
				$sql = "update t_org 
						set name = '%s', full_name = '%s', org_code = '%s', parent_id = null 
						where id = '%s' ";
				$rc = $db->execute($sql, $name, $fullName, $orgCode, $id);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$tempParentId = $parentId;
				while ( $tempParentId != null ) {
					$sql = "select parent_id from t_org where id = '%s' ";
					$d = $db->query($sql, $tempParentId);
					if ($d) {
						$tempParentId = $d[0]["parent_id"];
						
						if ($tempParentId == $id) {
							$db->rollback();
							return $this->bad("不能选择下级组织作为上级组织");
						}
					} else {
						$tempParentId = null;
					}
				}
				
				$sql = "select full_name from t_org where id = '%s' ";
				$data = $db->query($sql, $parentId);
				if ($data) {
					$parentFullName = $data[0]["full_name"];
					$fullName = $parentFullName . "\\" . $name;
					
					$sql = "update t_org 
							set name = '%s', full_name = '%s', org_code = '%s', parent_id = '%s' 
							where id = '%s' ";
					$rc = $db->execute($sql, $name, $fullName, $orgCode, $parentId, $id);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					$log = "编辑组织机构：名称 = {$name} 编码 = {$orgCode}";
				} else {
					$db->rollback();
					return $this->bad("上级组织不存在");
				}
			}
			
			if ($oldParentId != $parentId) {
				// 上级组织机构发生了变化，这个时候，需要调整数据域
				$rc = $this->modifyDataOrg($db, $parentId, $id);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 同步下级组织的full_name字段
			$rc = $this->modifyFullName($db, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		} else {
			// 新增
			$idGenService = new IdGenService();
			$id = $idGenService->newId();
			
			$sql = "select full_name from t_org where id = '%s' ";
			$parentOrg = $db->query($sql, $parentId);
			$fullName = "";
			if (! $parentOrg) {
				$parentId = null;
				$fullName = $name;
			} else {
				$fullName = $parentOrg[0]["full_name"] . "\\" . $name;
			}
			
			if ($parentId == null) {
				$dataOrg = "01";
				$sql = "select data_org from t_org 
						where parent_id is null
						order by data_org desc limit 1";
				$data = $db->query($sql);
				if ($data) {
					$dataOrg = $this->incDataOrg($data[0]["data_org"]);
				}
				
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id, data_org) 
						values ('%s', '%s', '%s', '%s', null, '%s')";
				
				$rc = $db->execute($sql, $id, $name, $fullName, $orgCode, $dataOrg);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$dataOrg = "";
				$sql = "select data_org from t_org
						where parent_id = '%s' 
						order by data_org desc limit 1";
				$data = $db->query($sql, $parentId);
				if ($data) {
					$dataOrg = $this->incDataOrg($data[0]["data_org"]);
				} else {
					$sql = "select data_org from t_org where id = '%s' ";
					$data = $db->query($sql, $parentId);
					if (! $data) {
						$db->rollback();
						return $this->bad("上级组织机构不存在");
					}
					$dataOrg = $data[0]["data_org"] . "01";
				}
				
				$sql = "insert into t_org (id, name, full_name, org_code, parent_id, data_org) 
						values ('%s', '%s', '%s', '%s', '%s', '%s')";
				
				$rc = $db->execute($sql, $id, $name, $fullName, $orgCode, $parentId, $dataOrg);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "新增组织机构：名称 = {$name} 编码 = {$orgCode}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function orgParentName($id) {
		$db = M();
		$result = array();
		
		$data = $db->query("select parent_id, name, org_code from t_org where id = '%s' ", $id);
		
		if ($data) {
			$parentId = $data[0]["parent_id"];
			$result["name"] = $data[0]["name"];
			$result["orgCode"] = $data[0]["org_code"];
			$result["parentOrgId"] = $parentId;
			
			$data = $db->query("select full_name from t_org where id = '%s' ", $parentId);
			
			if ($data) {
				$result["parentOrgName"] = $data[0]["full_name"];
			}
		}
		
		return $result;
	}

	public function deleteOrg($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		if ($this->isDemo()) {
			if ($id == DemoConst::ORG_COMPANY_ID) {
				return $this->bad("在演示环境下，组织机构[公司]不希望被您删除，请见谅");
			}
			if ($id == DemoConst::ORG_INFODEPT_ID) {
				return $this->bad("在演示环境下，组织机构[信息部]不希望被您删除，请见谅");
			}
		}
		
		$db = M();
		$db->startTrans();
		
		$sql = "select name, org_code from t_org where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的组织机构不存在");
		}
		$name = $data[0]["name"];
		$orgCode = $data[0]["org_code"];
		
		$sql = "select count(*) as cnt from t_org where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("当前组织机构还有下级组织，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_user where org_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("当前组织机构还有用户，不能删除");
		}
		
		// 检查当前组织机构在采购订单中是否使用了
		$sql = "select count(*) as cnt from t_po_bill where org_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("当前组织机构在采购订单中使用了，不能删除");
		}
		
		$sql = "delete from t_org where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除组织机构： 名称 = {$name} 编码  = {$orgCode}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 新增或编辑用户
	 */
	public function editUser($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$loginName = $params["loginName"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		$orgId = $params["orgId"];
		$enabled = $params["enabled"];
		$gender = $params["gender"];
		$birthday = $params["birthday"];
		$idCardNumber = $params["idCardNumber"];
		$tel = $params["tel"];
		$tel02 = $params["tel02"];
		$address = $params["address"];
		
		if ($this->isDemo()) {
			if ($id == DemoConst::ADMIN_USER_ID) {
				return $this->bad("在演示环境下，admin用户不希望被您修改，请见谅");
			}
		}
		
		$pys = new PinyinService();
		$py = $pys->toPY($name);
		
		$db = M();
		$db->startTrans();
		
		if ($id) {
			// 修改
			// 检查登录名是否被使用
			$sql = "select count(*) as cnt from t_user where login_name = '%s' and id <> '%s' ";
			$data = $db->query($sql, $loginName, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("登录名 [$loginName] 已经存在");
			}
			
			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				$db->rollback();
				return $this->bad("组织机构不存在");
			}
			
			// 检查编码是否存在
			$sql = "select count(*) as cnt from t_user 
					where org_code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $orgCode, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码[$orgCode]已经被其他用户使用");
			}
			
			$sql = "select org_id, data_org from t_user where id = '%s'";
			$data = $db->query($sql, $id);
			$oldOrgId = $data[0]["org_id"];
			$dataOrg = $data[0]["data_org"];
			if ($oldOrgId != $orgId) {
				// 修改了用户的组织机构， 这个时候要调整数据域
				$sql = "select data_org from t_user 
						where org_id = '%s' 
						order by data_org desc limit 1";
				$data = $db->query($sql, $orgId);
				if ($data) {
					$dataOrg = $this->incDataOrg($data[0]["data_org"]);
				} else {
					$sql = "select data_org from t_org where id = '%s' ";
					$data = $db->query($sql, $orgId);
					$dataOrg = $data[0]["data_org"] . "0001";
				}
				$sql = "update t_user
					set login_name = '%s', name = '%s', org_code = '%s',
					    org_id = '%s', enabled = %d, py = '%s',
					    gender = '%s', birthday = '%s', id_card_number = '%s',
					    tel = '%s', tel02 = '%s', address = '%s', data_org = '%s'
					where id = '%s' ";
				$rc = $db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, $py, 
						$gender, $birthday, $idCardNumber, $tel, $tel02, $address, $dataOrg, $id);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$sql = "update t_user
					set login_name = '%s', name = '%s', org_code = '%s',
					    org_id = '%s', enabled = %d, py = '%s',
					    gender = '%s', birthday = '%s', id_card_number = '%s',
					    tel = '%s', tel02 = '%s', address = '%s'
					where id = '%s' ";
				$rc = $db->execute($sql, $loginName, $name, $orgCode, $orgId, $enabled, $py, 
						$gender, $birthday, $idCardNumber, $tel, $tel02, $address, $id);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "编辑用户： 登录名 = {$loginName} 姓名 = {$name} 编码 = {$orgCode}";
		} else {
			// 新建
			// 检查登录名是否被使用
			$sql = "select count(*) as cnt from t_user where login_name = '%s' ";
			$data = $db->query($sql, $loginName);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("登录名 [$loginName] 已经存在");
			}
			
			// 检查组织机构是否存在
			$sql = "select count(*) as cnt from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			$cnt = $data[0]["cnt"];
			if ($cnt != 1) {
				$db->rollback();
				return $this->bad("组织机构不存在");
			}
			
			// 检查编码是否存在
			$sql = "select count(*) as cnt from t_user where org_code = '%s' ";
			$data = $db->query($sql, $orgCode);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码[$orgCode]已经被其他用户使用");
			}
			
			// 新增用户的默认密码
			$password = md5("123456");
			
			// 生成数据域
			$dataOrg = "";
			$sql = "select data_org 
					from t_user
					where org_id = '%s'
					order by data_org desc limit 1";
			$data = $db->query($sql, $orgId);
			if ($data) {
				$dataOrg = $this->incDataOrgForUser($data[0]["data_org"]);
			} else {
				$sql = "select data_org from t_org where id = '%s' ";
				$data = $db->query($sql, $orgId);
				if ($data) {
					$dataOrg = $data[0]["data_org"] . "0001";
				} else {
					$db->rollback();
					return $this->bad("组织机构不存在");
				}
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			$sql = "insert into t_user (id, login_name, name, org_code, org_id, enabled, password, py,
					gender, birthday, id_card_number, tel, tel02, address, data_org) 
					values ('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s',
					'%s', '%s', '%s', '%s', '%s', '%s', '%s') ";
			$rc = $db->execute($sql, $id, $loginName, $name, $orgCode, $orgId, $enabled, $password, 
					$py, $gender, $birthday, $idCardNumber, $tel, $tel02, $address, $dataOrg);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新建用户： 登录名 = {$loginName} 姓名 = {$name} 编码 = {$orgCode}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除用户
	 */
	public function deleteUser($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		if ($id == DemoConst::ADMIN_USER_ID) {
			return $this->bad("不能删除系统管理员用户");
		}
		
		// 检查用户是否存在，以及是否能删除
		$db = M();
		$db->startTrans();
		
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的用户不存在");
		}
		$userName = $data[0]["name"];
		
		// 判断在采购入库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_pw_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在采购入库单中使用了，不能删除");
		}
		
		// 判断在销售出库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_ws_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在销售出库单中使用了，不能删除");
		}
		
		// 判断在销售退货入库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_sr_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在销售退货入库单中使用了，不能删除");
		}
		
		// 判断在采购退货出库单中是否使用了该用户
		$sql = "select count(*) as cnt from t_pr_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在采购退货出库单中使用了，不能删除");
		}
		
		// 判断在调拨单中是否使用了该用户
		$sql = "select count(*) as cnt from t_it_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在调拨单中使用了，不能删除");
		}
		
		// 判断在盘点单中是否使用了该用户
		$sql = "select count(*) as cnt from t_ic_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在盘点单中使用了，不能删除");
		}
		
		// 判断在收款记录中是否使用了该用户
		$sql = "select count(*) as cnt from t_receiving where rv_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在收款记录中使用了，不能删除");
		}
		
		// 判断在付款记录中是否使用了该用户
		$sql = "select count(*) as cnt from t_payment where pay_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在盘点单中使用了，不能删除");
		}
		
		// 判断在采购订单中是否使用了该用户
		$sql = "select count(*) as cnt from t_po_bill where biz_user_id = '%s' or input_user_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("用户[{$userName}]已经在采购订单中使用了，不能删除");
		}
		
		// TODO 如果增加了其他单据，同样需要做出判断是否使用了该用户
		
		$sql = "delete from t_role_user where user_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_user where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$bs = new BizlogService();
		$bs->insertBizlog("删除用户[{$userName}]", $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function changePassword($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		if ($this->isDemo() && $id == DemoConst::ADMIN_USER_ID) {
			return $this->bad("在演示环境下，admin用户的密码不希望被您修改，请见谅");
		}
		
		$password = $params["password"];
		if (strlen($password) < 5) {
			return $this->bad("密码长度不能小于5位");
		}
		
		$db = M();
		$db->startTrans();
		
		$sql = "select login_name, name from t_user where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要修改密码的用户不存在");
		}
		$loginName = $data[0]["login_name"];
		$name = $data[0]["name"];
		
		$sql = "update t_user 
				set password = '%s' 
				where id = '%s' ";
		$rc = $db->execute($sql, md5($password), $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "修改用户[登录名 ={$loginName} 姓名 = {$name}]的密码";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function clearLoginUserInSession() {
		session("loginUserId", null);
	}

	public function changeMyPassword($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$userId = $params["userId"];
		$oldPassword = $params["oldPassword"];
		$newPassword = $params["newPassword"];
		
		if ($this->isDemo() && $userId == DemoConst::ADMIN_USER_ID) {
			return $this->bad("在演示环境下，admin用户的密码不希望被您修改，请见谅");
		}
		
		if ($userId != $this->getLoginUserId()) {
			return $this->bad("服务器环境发生变化，请重新登录后再操作");
		}
		
		// 检验旧密码
		$db = M();
		$sql = "select count(*) as cnt from t_user where id = '%s' and password = '%s' ";
		$data = $db->query($sql, $userId, md5($oldPassword));
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("旧密码不正确");
		}
		
		if (strlen($newPassword) < 5) {
			return $this->bad("密码长度不能小于5位");
		}
		
		$sql = "select login_name, name from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		if (! $data) {
			return $this->bad("要修改密码的用户不存在");
		}
		$loginName = $data[0]["login_name"];
		$name = $data[0]["name"];
		
		$sql = "update t_user set password = '%s' where id = '%s' ";
		$db->execute($sql, md5($newPassword), $userId);
		
		$log = "用户[登录名 ={$loginName} 姓名 = {$name}]修改了自己的登录密码";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "用户管理");
		
		return $this->ok();
	}

	public function queryData($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, login_name, name from t_user 
				where (login_name like '%s' or name like '%s' or py like '%s') ";
		$key = "%{$queryKey}%";
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("-8999-02", "t_user");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by login_name 
				limit 20";
		$data = M()->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["loginName"] = $v["login_name"];
			$result[$i]["name"] = $v["name"];
		}
		return $result;
	}

	/**
	 * 判断指定用户id的用户是否存在
	 *
	 * @return true: 存在
	 */
	public function userExists($userId, $db) {
		if (! $db) {
			$db = M();
		}
		if (! $userId) {
			return false;
		}
		
		$sql = "select count(*) as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		return $data[0]["cnt"] == 1;
	}

	/**
	 * 判断指定的组织机构是否存储
	 *
	 * @return boolean true: 存在
	 */
	public function orgExists($orgId, $db) {
		if (! $db) {
			$db = M();
		}
		if (! $orgId) {
			return false;
		}
		
		$sql = "select count(*) as cnt from t_org where id = '%s' ";
		$data = $db->query($sql, $orgId);
		return $data[0]["cnt"] == 1;
	}

	/**
	 * 获得登录用户的数据域
	 */
	public function getLoginUserDataOrg() {
		if ($this->isNotOnline()) {
			return null;
		}
		
		$loginUserId = $this->getLoginUserId();
		$db = M();
		$sql = "select data_org from t_user where id = '%s' ";
		$data = $db->query($sql, $loginUserId);
		if ($data) {
			return $data[0]["data_org"];
		} else {
			return null;
		}
	}

	/**
	 * 获得当前登录用户的某个功能的数据域
	 *
	 * @param unknown $fid        	
	 */
	public function getDataOrgForFId($fid) {
		if ($this->isNotOnline()) {
			return array();
		}
		
		$result = array();
		$loginUserId = $this->getLoginUserId();
		
		if ($loginUserId == DemoConst::ADMIN_USER_ID) {
			// admin 是超级管理员
			$result[] = "*";
			return $result;
		}
		
		$db = M();
		$sql = "select distinct rpd.data_org
				from t_role_permission rp, t_role_permission_dataorg rpd,
					t_role_user ru
				where ru.user_id = '%s' and ru.role_id = rp.role_id
					and rp.role_id = rpd.role_id and rp.permission_id = rpd.permission_id
					and rpd.permission_id = '%s' ";
		$data = $db->query($sql, $loginUserId, $fid);
		
		foreach ( $data as $v ) {
			$result[] = $v["data_org"];
		}
		
		return $result;
	}

	public function orgWithDataOrg() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select id, full_name
				from t_org ";
		
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("-8999-01", "t_org");
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by full_name";
		
		$db = M();
		$data = $db->query($sql, $queryParams);
		
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["fullName"] = $v["full_name"];
		}
		
		return $result;
	}

	/**
	 * 获得当前登录用户所属公司的Id
	 */
	public function getCompanyId() {
		$result = null;
		
		$userId = $this->getLoginUserId();
		if (! $userId) {
			return $result;
		}
		
		// 获得当前登录用户所属公司的算法：
		// 从最底层的组织机构向上找，直到parent_id为null的那个组织机构就是所属公司
		
		$db = M();
		$sql = "select org_id from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		if (! $data) {
			return null;
		}
		$orgId = $data[0]["org_id"];
		$found = false;
		while ( ! $found ) {
			$sql = "select id, parent_id from t_org where id = '%s' ";
			$data = $db->query($sql, $orgId);
			if (! $data) {
				return $result;
			}
			
			$orgId = $data[0]["parent_id"];
			
			$result = $data[0]["id"];
			$found = $orgId == null;
		}
		
		return $result;
	}

	/**
	 * 查询用户数据域列表
	 */
	public function queryUserDataOrg($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, data_org, name from t_user
				where (login_name like '%s' or name like '%s' or py like '%s' or data_org like '%s') ";
		$key = "%{$queryKey}%";
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::WAREHOUSE_EDIT_DATAORG, "t_user");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by data_org
				limit 20";
		$data = M()->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["dataOrg"] = $v["data_org"];
			$result[$i]["name"] = $v["name"];
		}
		return $result;
	}
}