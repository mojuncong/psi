<?php

namespace Home\Service;

use Home\Service\IdGenService;
use Home\Service\BizlogService;
use Home\Common\FIdConst;

/**
 * 基础数据仓库Service
 *
 * @author 李静波
 */
class WarehouseService extends PSIBaseService {
	private $LOG_CATEGORY = "基础数据-仓库";

	/**
	 * 所有仓库的列表信息
	 */
	public function warehouseList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select id, code, name, inited, data_org from t_warehouse ";
		$ds = new DataOrgService();
		$queryParams = array();
		$rs = $ds->buildSQL(FIdConst::WAREHOUSE, "t_warehouse");
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by code";
		
		$db = M();
		$result = array();
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["inited"] = $v["inited"];
			$result[$i]["dataOrg"] = $v["data_org"];
		}
		
		return $result;
	}

	/**
	 * 新建或编辑仓库
	 */
	public function editWarehouse($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$db = M();
		
		$db->startTrans();
		
		$log = null;
		
		if ($id) {
			// 修改
			// 检查同编号的仓库是否存在
			$sql = "select count(*) as cnt from t_warehouse where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [$code] 的仓库已经存在");
			}
			
			$sql = "update t_warehouse 
					set code = '%s', name = '%s', py = '%s' 
					where id = '%s' ";
			$rc = $db->execute($sql, $code, $name, $py, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑仓库：编码 = $code,  名称 = $name";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			
			// 检查同编号的仓库是否存在
			$sql = "select count(*) as cnt from t_warehouse where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [$code] 的仓库已经存在");
			}
			
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			$companyId = $us->getCompanyId();
			
			$sql = "insert into t_warehouse(id, code, name, inited, py, data_org, company_id) 
					values ('%s', '%s', '%s', 0, '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $code, $name, $py, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新增仓库：编码 = {$code},  名称 = {$name}";
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
	 * 删除仓库
	 */
	public function deleteWarehouse($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select code, name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的仓库不存在");
		}
		
		// 判断仓库是否能删除
		$warehouse = $data[0];
		$warehouseName = $warehouse["name"];
		if ($warehouse["inited"] == 1) {
			$db->rollback();
			return $this->bad("仓库[{$warehouseName}]已经建账，不能删除");
		}
		
		// 判断仓库是否在采购入库单中使用
		$sql = "select count(*) as cnt from t_pw_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在采购入库单中使用，不能删除");
		}
		
		// 判断仓库是否在采购退货出库单中使用
		$sql = "select count(*) as cnt from t_pr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在采购退货出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售出库单中使用
		$sql = "select count(*) as cnt from t_ws_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在销售出库单中使用，不能删除");
		}
		
		// 判断仓库是否在销售退货入库单中使用
		$sql = "select count(*) as cnt from t_sr_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在销售退货入库单中使用，不能删除");
		}
		
		// 判断仓库是否在调拨单中使用
		$sql = "select count(*) as cnt from t_it_bill 
				where from_warehouse_id = '%s' or to_warehouse_id = '%s' ";
		$data = $db->query($sql, $id, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在调拨单中使用，不能删除");
		}
		
		// 判断仓库是否在盘点单中使用
		$sql = "select count(*) as cnt from t_ic_bill where warehouse_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]已经在盘点单中使用，不能删除");
		}
		
		$sql = "delete from t_warehouse where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除仓库： 编码 = {$warehouse['code']}， 名称 = {$warehouse['name']}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function queryData($queryKey, $fid) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select id, code, name from t_warehouse 
					where (code like '%s' or name like '%s' or py like '%s' ) ";
		$key = "%{$queryKey}%";
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1003-01", "t_warehouse");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code";
		
		return M()->query($sql, $queryParams);
	}

	public function editDataOrg($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$dataOrg = $params["dataOrg"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select name, data_org from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要编辑数据域的仓库不存在");
		}
		
		$name = $data[0]["name"];
		$oldDataOrg = $data[0]["data_org"];
		if ($oldDataOrg == $dataOrg) {
			$db->rollback();
			return $this->bad("数据域没有改动，不用保存");
		}
		
		// 检查新数据域是否存在
		$sql = "select count(*) as cnt from t_user where data_org = '%s' ";
		$data = $db->query($sql, $dataOrg);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			$db->rollback();
			return $this->bad("数据域[{$dataOrg}]不存在");
		}
		
		$sql = "update t_warehouse
				set data_org = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $dataOrg, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "把仓库[{$name}]的数据域从旧值[{$oldDataOrg}]修改为新值[{$dataOrg}]";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}