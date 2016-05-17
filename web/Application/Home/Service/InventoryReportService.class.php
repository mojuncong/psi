<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 库存报表Service
 *
 * @author 李静波
 */
class InventoryReportService extends PSIBaseService {

	/**
	 * 安全库存明细表 - 数据查询
	 */
	public function safetyInventoryQueryData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$result = array();
		
		$db = M();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::REPORT_SAFETY_INVENTORY, "w");
		$queryParams = array();
		
		$sql = "select w.code as warehouse_code, w.name as warehouse_name,
					g.code as goods_code, g.name as goods_name, g.spec as goods_spec,
					u.name as unit_name,
					s.safety_inventory, i.balance_count
				from t_inventory i, t_goods g, t_goods_unit u, t_goods_si s, t_warehouse w
				where i.warehouse_id = w.id and i.goods_id = g.id and g.unit_id = u.id
					and s.warehouse_id = i.warehouse_id and s.goods_id = g.id
					and s.safety_inventory > i.balance_count ";
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		$sql .= " order by w.code, g.code
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["warehouseCode"] = $v["warehouse_code"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["goodsCode"] = $v["goods_code"];
			$result[$i]["goodsName"] = $v["goods_name"];
			$result[$i]["goodsSpec"] = $v["goods_spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["siCount"] = $v["safety_inventory"];
			$result[$i]["invCount"] = $v["balance_count"];
			$result[$i]["delta"] = $v["safety_inventory"] - $v["balance_count"];
		}
		
		$sql = "select count(*) as cnt
				from t_inventory i, t_goods g, t_goods_unit u, t_goods_si s, t_warehouse w
				where i.warehouse_id = w.id and i.goods_id = g.id and g.unit_id = u.id
					and s.warehouse_id = i.warehouse_id and s.goods_id = g.id
					and s.safety_inventory > i.balance_count ";
		$queryParams = array();
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
			;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 库存超上限明细表 - 数据查询
	 */
	public function inventoryUpperQueryData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$result = array();
		
		$db = M();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::REPORT_INVENTORY_UPPER, "w");
		
		$sql = "select w.code as warehouse_code, w.name as warehouse_name,
					g.code as goods_code, g.name as goods_name, g.spec as goods_spec,
					u.name as unit_name,
					s.inventory_upper, i.balance_count
				from t_inventory i, t_goods g, t_goods_unit u, t_goods_si s, t_warehouse w
				where i.warehouse_id = w.id and i.goods_id = g.id and g.unit_id = u.id
					and s.warehouse_id = i.warehouse_id and s.goods_id = g.id
					and s.inventory_upper < i.balance_count
					and s.inventory_upper <> 0 and s.inventory_upper is not null ";
		$queryParams = array();
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		$sql .= " order by w.code, g.code
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["warehouseCode"] = $v["warehouse_code"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["goodsCode"] = $v["goods_code"];
			$result[$i]["goodsName"] = $v["goods_name"];
			$result[$i]["goodsSpec"] = $v["goods_spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["iuCount"] = $v["inventory_upper"];
			$result[$i]["invCount"] = $v["balance_count"];
			$result[$i]["delta"] = $v["balance_count"] - $v["inventory_upper"];
		}
		
		$sql = "select count(*) as cnt
				from t_inventory i, t_goods g, t_goods_unit u, t_goods_si s, t_warehouse w
				where i.warehouse_id = w.id and i.goods_id = g.id and g.unit_id = u.id
					and s.warehouse_id = i.warehouse_id and s.goods_id = g.id
					and s.inventory_upper < i.balance_count
					and s.inventory_upper <> 0 and s.inventory_upper is not null
				";
		$queryParams = array();
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}