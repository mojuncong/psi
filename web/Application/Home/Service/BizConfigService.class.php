<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 业务设置Service
 *
 * @author 李静波
 */
class BizConfigService extends PSIBaseService {
	private $LOG_CATEGORY = "业务设置";

	/**
	 * 返回所有的配置项
	 */
	public function allConfigs($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$companyId = $params["companyId"];
		
		$sql = "select id, name, value, note  
				from t_config
				where company_id = '%s'
				order by show_order";
		$data = M()->query($sql, $companyId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $id;
			$result[$i]["name"] = $v["name"];
			$result[$i]["value"] = $v["value"];
			
			if ($id == "1001-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "使用不同计量单位" : "使用同一个计量单位";
			} else if ($id == "1003-02") {
				$result[$i]["displayValue"] = $v["value"] == 0 ? "移动平均法" : "先进先出法";
			} else if ($id == "2002-01") {
				$result[$i]["displayValue"] = $v["value"] == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
			} else if ($id == "2001-01" || $id == "2002-02") {
				$result[$i]["displayValue"] = $this->getWarehouseName($v["value"]);
			} else {
				$result[$i]["displayValue"] = $v["value"];
			}
			$result[$i]["note"] = $v["note"];
		}
		
		return $result;
	}

	private function getDefaultConfig() {
		return array(
				array(
						"id" => "9000-01",
						"name" => "公司名称",
						"value" => "",
						"note" => "",
						"showOrder" => 100
				),
				array(
						"id" => "9000-02",
						"name" => "公司地址",
						"value" => "",
						"note" => "",
						"showOrder" => 101
				),
				array(
						"id" => "9000-03",
						"name" => "公司电话",
						"value" => "",
						"note" => "",
						"showOrder" => 102
				),
				array(
						"id" => "9000-04",
						"name" => "公司传真",
						"value" => "",
						"note" => "",
						"showOrder" => 103
				),
				array(
						"id" => "9000-05",
						"name" => "公司邮编",
						"value" => "",
						"note" => "",
						"showOrder" => 104
				),
				array(
						"id" => "2001-01",
						"name" => "采购入库默认仓库",
						"value" => "",
						"note" => "",
						"showOrder" => 200
				),
				array(
						"id" => "2002-02",
						"name" => "销售出库默认仓库",
						"value" => "",
						"note" => "",
						"showOrder" => 300
				),
				array(
						"id" => "2002-01",
						"name" => "销售出库单允许编辑销售单价",
						"value" => "0",
						"note" => "当允许编辑的时候，还需要给用户赋予权限[销售出库单允许编辑销售单价]",
						"showOrder" => 301
				),
				array(
						"id" => "1003-02",
						"name" => "存货计价方法",
						"value" => "0",
						"note" => "",
						"showOrder" => 401
				),
				array(
						"id" => "9001-01",
						"name" => "增值税税率",
						"value" => "17",
						"note" => "",
						"showOrder" => 501
				),
				array(
						"id" => "9002-01",
						"name" => "产品名称",
						"value" => "开源进销存PSI",
						"note" => "",
						"showOrder" => 0
				),
				array(
						"id" => "9003-01",
						"name" => "采购订单单号前缀",
						"value" => "PO",
						"note" => "",
						"showOrder" => 601
				),
				array(
						"id" => "9003-02",
						"name" => "采购入库单单号前缀",
						"value" => "PW",
						"note" => "",
						"showOrder" => 602
				),
				array(
						"id" => "9003-03",
						"name" => "采购退货出库单单号前缀",
						"value" => "PR",
						"note" => "",
						"showOrder" => 603
				),
				array(
						"id" => "9003-04",
						"name" => "销售出库单单号前缀",
						"value" => "WS",
						"note" => "",
						"showOrder" => 604
				),
				array(
						"id" => "9003-05",
						"name" => "销售退货入库单单号前缀",
						"value" => "SR",
						"note" => "",
						"showOrder" => 605
				),
				array(
						"id" => "9003-06",
						"name" => "调拨单单号前缀",
						"value" => "IT",
						"note" => "",
						"showOrder" => 606
				),
				array(
						"id" => "9003-07",
						"name" => "盘点单单号前缀",
						"value" => "IC",
						"note" => "",
						"showOrder" => 607
				),
				array(
						"id" => "9003-08",
						"name" => "销售订单单号前缀",
						"value" => "SO",
						"note" => "",
						"showOrder" => 608
				)
		);
	}

	/**
	 * 返回所有的配置项，附带着附加数据集
	 */
	public function allConfigsWithExtData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$companyId = $params["companyId"];
		
		$db = M();
		$result = $this->getDefaultConfig();
		
		foreach ( $result as $i => $v ) {
			$sql = "select value
				from t_config
				where company_id = '%s' and id = '%s'
				";
			$data = $db->query($sql, $companyId, $v["id"]);
			if ($data) {
				$result[$i]["value"] = $data[0]["value"];
			}
		}
		
		$extDataList = array();
		
		$sql = "select id, name from t_warehouse ";
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::BIZ_CONFIG, "t_warehouse");
		$queryParams = array();
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by code ";
		$data = $db->query($sql, $queryParams);
		$warehouse = array(
				array(
						"id" => "",
						"name" => "[没有设置]"
				)
		);
		
		$extDataList["warehouse"] = array_merge($warehouse, $data);
		
		return array(
				"dataList" => $result,
				"extData" => $extDataList
		);
	}

	/**
	 * 保存配置项
	 */
	public function edit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		$db->startTrans();
		
		$defaultConfigs = $this->getDefaultConfig();
		
		$companyId = $params["companyId"];
		
		$sql = "select name from t_org where id = '%s' ";
		$data = $db->query($sql, $companyId);
		if (! $data) {
			$db->rollback();
			return $this->bad("没有选择公司");
		}
		$companyName = $data[0]["name"];
		
		$refPreList = array(
				"9003-01",
				"9003-02",
				"9003-03",
				"9003-04",
				"9003-05",
				"9003-06",
				"9003-07",
				"9003-08"
		);
		
		// 检查值是否合法
		foreach ( $params as $key => $value ) {
			if ($key == "9001-01") {
				$v = intval($value);
				if ($v < 0) {
					$db->rollback();
					return $this->bad("增值税税率不能为负数");
				}
				if ($v > 17) {
					$db->rollback();
					return $this->bad("增值税税率不能大于17");
				}
			}
			
			if ($key == "9002-01") {
				if (! $value) {
					$value = "开源进销存PSI";
				}
			}
			
			if ($key == "1003-02") {
				// 存货计价方法
				$sql = "select name, value from t_config 
						where id = '%s' and company_id = '%s' ";
				$data = $db->query($sql, $key, $companyId);
				if (! $data) {
					continue;
				}
				$oldValue = $data[0]["value"];
				if ($value == $oldValue) {
					continue;
				}
				
				if ($value == "1") {
					$db->rollback();
					return $this->bad("当前版本还不支持先进先出法");
				}
				
				$sql = "select count(*) as cnt from t_inventory_detail
						where ref_type <> '库存建账' ";
				$data = $db->query($sql);
				$cnt = $data[0]["cnt"];
				if ($cnt > 0) {
					$db->rollback();
					return $this->bad("已经有业务发生，不能再调整存货计价方法");
				}
			}
			
			if (in_array($key, $refPreList)) {
				if ($value == null || $value == "") {
					$db->rollback();
					return $this->bad("单号前缀不能为空");
				}
			}
		}
		
		foreach ( $params as $key => $value ) {
			if ($key == "companyId") {
				continue;
			}
			
			if ($key == "9001-01") {
				$value = intval($value);
			}
			
			if ($key == "9002-01") {
				if ($this->isDemo()) {
					// 演示环境下，不让修改产品名称
					$value = "开源进销存PSI";
				}
			}
			
			if (in_array($key, $refPreList)) {
				// 单号前缀保持大写
				$value = strtoupper($value);
			}
			
			$sql = "select name, value from t_config 
					where id = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $key, $companyId);
			$itemName = "";
			if (! $data) {
				foreach ( $defaultConfigs as $dc ) {
					if ($dc["id"] == $key) {
						$sql = "insert into t_config(id, name, value, note, show_order, company_id)
								values ('%s', '%s', '%s', '%s', %d, '%s')";
						$rc = $db->execute($sql, $key, $dc["name"], $value, $dc["note"], 
								$dc["showOrder"], $companyId);
						if ($rc === false) {
							$db->rollback();
							return $this->sqlError(__LINE__);
						}
						
						$itemName = $dc["name"];
						
						break;
					}
				}
			} else {
				$itemName = $data[0]["name"];
				
				$oldValue = $data[0]["value"];
				if ($value == $oldValue) {
					continue;
				}
				
				$sql = "update t_config set value = '%s'
				where id = '%s' and company_id = '%s' ";
				$rc = $db->execute($sql, $value, $key, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 记录业务日志
			$log = null;
			if ($key == "1003-02") {
				$v = $value == 0 ? "移动平均法" : "先进先出法";
				$log = "把[{$itemName}]设置为[{$v}]";
			} else if ($key == "2001-01") {
				$v = $this->getWarehouseName($value);
				$log = "把[{$itemName}]设置为[{$v}]";
			} else if ($key == "2002-01") {
				$v = $value == 1 ? "允许编辑销售单价" : "不允许编辑销售单价";
				$log = "把[{$itemName}]设置为[{$v}]";
			} else if ($key == "2002-02") {
				$v = $this->getWarehouseName($value);
				$log = "把[{$itemName}]设置为[{$v}]";
			} else {
				$log = "把[{$itemName}]设置为[{$value}]";
			}
			
			if ($log) {
				$log = "[" . $companyName . "], " . $log;
				$bs = new BizlogService();
				$bs->insertBizlog($log, $this->LOG_CATEGORY);
			}
		}
		
		$db->commit();
		
		return $this->ok();
	}

	private function getWarehouseName($id) {
		$data = M()->query("select name from t_warehouse where id = '%s' ", $id);
		if ($data) {
			return $data[0]["name"];
		} else {
			return "[没有设置]";
		}
	}

	/**
	 * 获得增值税税率
	 */
	public function getTaxRate() {
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$sql = "select value from t_config 
				where id = '9001-01' and company_id = '%s' ";
		$data = $db->query($sql, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			return intval($result);
		} else {
			return 17;
		}
	}

	/**
	 * 获得本产品名称，默认值是：开源进销存PSI
	 */
	public function getProductionName() {
		$defaultName = "开源进销存PSI";
		
		$db = M();
		if (! $this->columnExists($db, "t_config", "company_id")) {
			// 兼容旧代码
			return $defaultName;
		}
		
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$sql = "select value from t_config 
				where id = '9002-01' and company_id = '%s' ";
		$data = $db->query($sql, $companyId);
		if ($data) {
			return $data[0]["value"];
		} else {
			return $defaultName;
		}
	}

	/**
	 * 获得存货计价方法
	 * 0： 移动平均法
	 * 1：先进先出法
	 */
	public function getInventoryMethod() {
		// 2015-11-19 为发布稳定版本，临时取消先进先出法
		$result = 0;
		
		// $db = M();
		// $sql = "select value from t_config where id = '1003-02' ";
		// $data = $db->query($sql);
		// if (! $data) {
		// return $result;
		// }
		
		// $result = intval($data[0]["value"]);
		
		return $result;
	}

	/**
	 * 获得采购订单单号前缀
	 */
	public function getPOBillRefPre() {
		$result = "PO";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-01";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PO";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购入库单单号前缀
	 */
	public function getPWBillRefPre() {
		$result = "PW";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-02";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PW";
			}
		}
		
		return $result;
	}

	/**
	 * 获得采购退货出库单单号前缀
	 */
	public function getPRBillRefPre() {
		$result = "PR";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-03";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "PR";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售出库单单号前缀
	 */
	public function getWSBillRefPre() {
		$result = "WS";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-04";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "WS";
			}
		}
		
		return $result;
	}

	/**
	 * 获得销售退货入库单单号前缀
	 */
	public function getSRBillRefPre() {
		$result = "SR";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-05";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "SR";
			}
		}
		
		return $result;
	}

	/**
	 * 获得调拨单单号前缀
	 */
	public function getITBillRefPre() {
		$result = "IT";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-06";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "IT";
			}
		}
		
		return $result;
	}

	/**
	 * 获得盘点单单号前缀
	 */
	public function getICBillRefPre() {
		$result = "IC";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-07";
		$sql = "select value from t_config 
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "IC";
			}
		}
		
		return $result;
	}

	/**
	 * 获得当前用户可以设置的公司
	 */
	public function getCompany() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		$result = array();
		
		$us = new UserService();
		
		$companyId = $us->getCompanyId();
		
		$sql = "select id, name
				from t_org
				where (parent_id is null) ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::BIZ_CONFIG, "t_org");
		
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by org_code ";
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
		}
		
		return $result;
	}

	/**
	 * 获得销售订单单号前缀
	 */
	public function getSOBillRefPre() {
		$result = "PO";
		
		$db = M();
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$id = "9003-08";
		$sql = "select value from t_config
				where id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $id, $companyId);
		if ($data) {
			$result = $data[0]["value"];
			
			if ($result == null || $result == "") {
				$result = "SO";
			}
		}
		
		return $result;
	}
}