<?php

namespace Home\Service;

use Home\Common\DemoConst;
use Home\Common\FIdConst;

/**
 * 权限 Service
 *
 * @author 李静波
 */
class PermissionService extends PSIBaseService {
	private $LOG_CATEGORY = "权限管理";

	public function roleList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select r.id, r.name from t_role r ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "r");
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= "	order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql, $queryParams);
		
		return $data;
	}

	public function permissionList($roleId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		$sql = "select p.id, p.name
				from t_role r, t_role_permission rp, t_permission p 
				where r.id = rp.role_id and r.id = '%s' and rp.permission_id = p.id 
				order by convert(p.name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $roleId);
		
		$result = array();
		foreach ( $data as $i => $v ) {
			$pid = $v["id"];
			$result[$i]["id"] = $pid;
			$result[$i]["name"] = $v["name"];
			
			$sql = "select data_org
					from t_role_permission_dataorg
					where role_id = '%s' and permission_id = '%s' ";
			$od = $db->query($sql, $roleId, $pid);
			if ($od) {
				$dataOrg = "";
				foreach ( $od as $j => $item ) {
					if ($j > 0) {
						$dataOrg .= ";";
					}
					$dataOrg .= $item["data_org"];
				}
				$result[$i]["dataOrg"] = $dataOrg;
			} else {
				$result[$i]["dataOrg"] = "*";
			}
		}
		
		return $result;
	}

	public function userList($roleId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select u.id, u.login_name, u.name, org.full_name 
				from t_role r, t_role_user ru, t_user u, t_org org 
				where r.id = ru.role_id and r.id = '%s' and ru.user_id = u.id and u.org_id = org.id ";
		
		$sql .= " order by convert(org.full_name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql, $roleId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["orgFullName"] = $v["full_name"];
			$result[$i]["loginName"] = $v["login_name"];
		}
		
		return $result;
	}

	public function editRole($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$name = $params["name"];
		$permissionIdList = $params["permissionIdList"];
		$dataOrgList = $params["dataOrgList"];
		$userIdList = $params["userIdList"];
		
		if ($this->isDemo() && $id == DemoConst::ADMIN_ROLE_ID) {
			return $this->bad("在演示环境下，系统管理角色不希望被您修改，请见谅");
		}
		
		$db = M();
		$db->startTrans();
		
		$pid = explode(",", $permissionIdList);
		$doList = explode(",", $dataOrgList);
		$uid = explode(",", $userIdList);
		
		if ($id) {
			// 编辑角色
			
			$sql = "update t_role set name = '%s' where id = '%s' ";
			$rc = $db->execute($sql, $name, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "delete from t_role_permission where role_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "delete from t_role_user where role_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			if ($pid) {
				foreach ( $pid as $i => $v ) {
					$sql = "insert into t_role_permission (role_id, permission_id) 
								values ('%s', '%s')";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 权限的数据域
					$sql = "delete from t_role_permission_dataorg 
								where role_id = '%s' and permission_id = '%s' ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					$dataOrg = $doList[$i];
					$oList = explode(";", $dataOrg);
					foreach ( $oList as $item ) {
						if (! $item) {
							continue;
						}
						
						$sql = "insert into t_role_permission_dataorg(role_id, permission_id, data_org)
									values ('%s', '%s', '%s')";
						$rc = $db->execute($sql, $id, $v, $item);
						if ($rc === false) {
							$db->rollback();
							return $this->sqlError(__LINE__);
						}
					}
				}
			}
			
			if ($uid) {
				foreach ( $uid as $v ) {
					$sql = "insert into t_role_user (role_id, user_id) 
								values ('%s', '%s') ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
			
			$log = "编辑角色[{$name}]";
		} else {
			// 新增角色
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$us = new UserService();
			$loginUserDataOrg = $us->getLoginUserDataOrg();
			$companyId = $us->getCompanyId();
			
			$sql = "insert into t_role (id, name, data_org, company_id) 
					values ('%s', '%s', '%s', '%s') ";
			$rc = $db->execute($sql, $id, $name, $loginUserDataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			if ($pid) {
				foreach ( $pid as $i => $v ) {
					$sql = "insert into t_role_permission (role_id, permission_id) 
								values ('%s', '%s')";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 权限的数据域
					$sql = "delete from t_role_permission_dataorg 
								where role_id = '%s' and permission_id = '%s' ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					$dataOrg = $doList[$i];
					$oList = explode(";", $dataOrg);
					foreach ( $oList as $item ) {
						if (! $item) {
							continue;
						}
						
						$sql = "insert into t_role_permission_dataorg(role_id, permission_id, data_org)
									values ('%s', '%s', '%s')";
						$rc = $db->execute($sql, $id, $v, $item);
						if ($rc === false) {
							$db->rollback();
							return $this->sqlError(__LINE__);
						}
					}
				}
			}
			
			if ($uid) {
				foreach ( $uid as $v ) {
					$sql = "insert into t_role_user (role_id, user_id) 
								values ('%s', '%s') ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
			
			$log = "新增角色[{$name}]";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function selectPermission($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select id, name from t_permission 
				order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql);
		
		$index = 0;
		
		foreach ( $data as $v ) {
			if (! in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];
				
				$index ++;
			}
		}
		
		return $result;
	}

	public function selectUsers($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select u.id, u.name, u.login_name, o.full_name 
				from t_user u, t_org o 
				where u.org_id = o.id ";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "u");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by convert(u.name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql, $queryParams);
		
		$index = 0;
		
		foreach ( $data as $v ) {
			if (! in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];
				$result[$index]["loginName"] = $v["login_name"];
				$result[$index]["orgFullName"] = $v["full_name"];
				
				$index ++;
			}
		}
		
		return $result;
	}

	/**
	 * 删除角色
	 */
	public function deleteRole($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		if ($this->isDemo() && $id == DemoConst::ADMIN_ROLE_ID) {
			return $this->bad("在演示环境下，系统管理角色不希望被您删除，请见谅");
		}
		
		$db = M();
		$db->startTrans();
		
		$sql = "select name from t_role where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的角色不存在");
		}
		$roleName = $data[0]["name"];
		
		$sql = "delete from t_role_permission_dataorg where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_role_permission where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_role_user  where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_role where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除角色[{$roleName}]";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function dataOrgList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$roleId = $params["roleId"];
		$permissionId = $params["permissionId"];
		
		$db = M();
		$sql = "select data_org
				from t_role_permission_dataorg
				where role_id = '%s' and permission_id = '%s' ";
		$data = $db->query($sql, $roleId, $permissionId);
		$result = array();
		if ($data) {
			foreach ( $data as $i => $v ) {
				$dataOrg = $v["data_org"];
				$result[$i]["dataOrg"] = $dataOrg;
				if ($dataOrg == "*") {
					$result[$i]["fullName"] = "[全部数据]";
				} else if ($dataOrg == "#") {
					$result[$i]["fullName"] = "[本人数据]";
				} else {
					$fullName = "";
					$sql = "select full_name from t_org where data_org = '%s'";
					$data = $db->query($sql, $dataOrg);
					if ($data) {
						$fullName = $data[0]["full_name"];
					} else {
						$sql = "select o.full_name, u.name
							from t_org o, t_user u
							where o.id = u.org_id and u.data_org = '%s' ";
						$data = $db->query($sql, $dataOrg);
						if ($data) {
							$fullName = $data[0]["full_name"] . "\\" . $data[0]["name"];
						}
					}
					
					$result[$i]["fullName"] = $fullName;
				}
			}
		} else {
			$result[0]["dataOrg"] = "*";
			$result[0]["fullName"] = "[全部数据]";
		}
		
		return $result;
	}

	public function selectDataOrg() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$result = array();
		$db = M();
		$sql = "select full_name, data_org
				from t_org ";
		$queryParams = array();
		$ds = new DataOrgService();
		
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "t_org");
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		$sql .= " order by convert(full_name USING gbk) collate gbk_chinese_ci";
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["fullName"] = $v["full_name"];
			$result[$i]["dataOrg"] = $v["data_org"];
		}
		
		return $result;
	}
	
	/**
	 * const: 全部权限
	 */
	private $ALL_CATEGORY = "[全部]";

	/**
	 * 获得权限分类
	 */
	public function permissionCategory() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$result = array();
		
		$result[0]["name"] = $this->ALL_CATEGORY;
		
		$db = M();
		$sql = "select distinct category
				from t_permission
				order by convert(category USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql);
		foreach ( $data as $i => $v ) {
			$result[$i + 1]["name"] = $v["category"];
		}
		
		return $result;
	}

	/**
	 * 按权限分类查询权限项
	 */
	public function permissionByCategory($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$category = $params["category"];
		
		$sql = "select id, name
				from t_permission ";
		
		$queryParams = array();
		if ($category != $this->ALL_CATEGORY) {
			$queryParams[] = $category;
			
			$sql .= " where category = '%s' ";
		}
		
		$sql .= " order by convert(name USING gbk) collate gbk_chinese_ci";
		$db = M();
		$data = $db->query($sql, $queryParams);
		
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
		}
		
		return $result;
	}
}