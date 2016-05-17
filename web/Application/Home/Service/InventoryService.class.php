<?php

namespace Home\Service;

use Home\Common\FIdConst;
/**
 * 库存 Service
 *
 * @author 李静波
 */
class InventoryService extends PSIBaseService {

	public function warehouseList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select id, code, name from t_warehouse ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::INVENTORY_QUERY, "t_warehouse");
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by code";
		
		return M()->query($sql, $queryParams);
	}

	public function inventoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$warehouseId = $params["warehouseId"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$queryParams = array();
		$queryParams[] = $warehouseId;
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name,
				 v.in_count, v.in_price, v.in_money, v.out_count, v.out_price, v.out_money,
				 v.balance_count, v.balance_price, v.balance_money, v.afloat_count,
					v.afloat_money, v.afloat_price
				from t_inventory v, t_goods g, t_goods_unit u
				where (v.warehouse_id = '%s') and (v.goods_id = g.id) and (g.unit_id = u.id) ";
		if ($code) {
			$sql .= " and (g.code like '%s')";
			$queryParams[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s')";
			$queryParams[] = "%{$name}%";
			$queryParams[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParams[] = "%{$spec}%";
		}
		$sql .= " order by g.code
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		
		$data = $db->query($sql, $queryParams);
		
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["goodsId"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["inCount"] = $v["in_count"];
			$result[$i]["inPrice"] = $v["in_price"];
			$result[$i]["inMoney"] = $v["in_money"];
			$result[$i]["outCount"] = $v["out_count"];
			$result[$i]["outPrice"] = $v["out_price"];
			$result[$i]["outMoney"] = $v["out_money"];
			$result[$i]["balanceCount"] = $v["balance_count"];
			$result[$i]["balancePrice"] = $v["balance_price"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
			$result[$i]["afloatCount"] = $v["afloat_count"];
			$result[$i]["afloatPrice"] = $v["afloat_price"];
			$result[$i]["afloatMoney"] = $v["afloat_money"];
		}
		
		$queryParams = array();
		$queryParams[] = $warehouseId;
		$sql = "select count(*) as cnt 
				from t_inventory v, t_goods g, t_goods_unit u
				where (v.warehouse_id = '%s') and (v.goods_id = g.id) and (g.unit_id = u.id) ";
		if ($code) {
			$sql .= " and (g.code like '%s')";
			$queryParams[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s')";
			$queryParams[] = "%{$name}%";
			$queryParams[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParams[] = "%{$spec}%";
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function inventoryDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$warehouseId = $params["warehouseId"];
		$goodsId = $params["goodsId"];
		$dtFrom = $params["dtFrom"];
		$dtTo = $params["dtTo"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name," . " v.in_count, v.in_price, v.in_money, v.out_count, v.out_price, v.out_money," . "v.balance_count, v.balance_price, v.balance_money," . " v.biz_date,  user.name as biz_user_name, v.ref_number, v.ref_type " . " from t_inventory_detail v, t_goods g, t_goods_unit u, t_user user" . " where v.warehouse_id = '%s' and v.goods_id = '%s' " . "	and v.goods_id = g.id and g.unit_id = u.id and v.biz_user_id = user.id " . "   and (v.biz_date between '%s' and '%s' ) " . " order by v.id " . " limit " . $start . ", " . $limit;
		$data = $db->query($sql, $warehouseId, $goodsId, $dtFrom, $dtTo);
		
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["goodsId"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["inCount"] = $v["in_count"];
			$result[$i]["inPrice"] = $v["in_price"];
			$result[$i]["inMoney"] = $v["in_money"];
			$result[$i]["outCount"] = $v["out_count"];
			$result[$i]["outPrice"] = $v["out_price"];
			$result[$i]["outMoney"] = $v["out_money"];
			$result[$i]["balanceCount"] = $v["balance_count"];
			$result[$i]["balancePrice"] = $v["balance_price"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
			$result[$i]["bizDT"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["refType"] = $v["ref_type"];
		}
		
		$sql = "select count(*) as cnt from t_inventory_detail" . " where warehouse_id = '%s' and goods_id = '%s' " . "     and (biz_date between '%s' and '%s')";
		$data = $db->query($sql, $warehouseId, $goodsId, $dtFrom, $dtTo);
		
		return array(
				"details" => $result,
				"totalCount" => $data[0]["cnt"]
		);
	}
}