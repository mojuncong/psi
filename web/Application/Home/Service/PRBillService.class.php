<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 采购退货出库单Service
 *
 * @author 李静波
 */
class PRBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购退货出库";

	/**
	 * 生成新的采购退货出库单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getPRBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_pr_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	public function prBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
			$db = M();
			$sql = "select p.ref, p.bill_status, p.warehouse_id, w.name as warehouse_name,
						p.biz_user_id, u.name as biz_user_name, pw.ref as pwbill_ref,
						s.name as supplier_name, s.id as supplier_id,
						p.pw_bill_id as pwbill_id, p.bizdt, p.receiving_type
					from t_pr_bill p, t_warehouse w, t_user u, t_pw_bill pw, t_supplier s
					where p.id = '%s' 
						and p.warehouse_id = w.id
						and p.biz_user_id = u.id
						and p.pw_bill_id = pw.id
						and p.supplier_id = s.id ";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $result;
			}
			
			$result["ref"] = $data[0]["ref"];
			$result["billStatus"] = $data[0]["bill_status"];
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["warehouseId"] = $data[0]["warehouse_id"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			$result["pwbillRef"] = $data[0]["pwbill_ref"];
			$result["supplierId"] = $data[0]["supplier_id"];
			$result["supplierName"] = $data[0]["supplier_name"];
			$result["pwbillId"] = $data[0]["pwbill_id"];
			$result["bizDT"] = $this->toYMD($data[0]["bizdt"]);
			$result["receivingType"] = $data[0]["receiving_type"];
			
			$items = array();
			$sql = "select p.pwbilldetail_id as id, p.goods_id, g.code as goods_code, g.name as goods_name,
						g.spec as goods_spec, u.name as unit_name, p.goods_count,
						p.goods_price, p.goods_money, p.rejection_goods_count as rej_count,
						p.rejection_goods_price as rej_price, p.rejection_money as rej_money
					from t_pr_bill_detail p, t_goods g, t_goods_unit u
					where p.prbill_id = '%s'
						and p.goods_id = g.id
						and g.unit_id = u.id
					order by p.show_order";
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["goods_code"];
				$items[$i]["goodsName"] = $v["goods_name"];
				$items[$i]["goodsSpec"] = $v["goods_spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["rejCount"] = $v["rej_count"];
				$items[$i]["rejPrice"] = $v["rej_price"];
				$items[$i]["rejMoney"] = $v["rej_money"];
			}
			
			$result["items"] = $items;
		} else {
			// 新建
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
		}
		
		return $result;
	}

	/**
	 * 新建或编辑采购退货出库单
	 */
	public function editPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		$db->startTrans();
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("选择的仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("选择的业务人员不存在，无法保存");
		}
		
		$pwBillId = $bill["pwBillId"];
		$sql = "select supplier_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $pwBillId);
		if (! $data) {
			$db->rollback();
			return $this->bad("选择采购入库单不存在，无法保存");
		}
		$supplierId = $data[0]["supplier_id"];
		
		$receivingType = $bill["receivingType"];
		
		$items = $bill["items"];
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			$db->rollback();
			return $this->bad("业务日期不正确");
		}
		
		$idGen = new IdGenService();
		$us = new UserService();
		
		$log = null;
		
		if ($id) {
			// 编辑采购退货出库单
			$sql = "select ref, bill_status, data_org, company_id
						from t_pr_bill
						where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的采购退货出库单不存在");
			}
			$ref = $data[0]["ref"];
			$companyId = $data[0]["company_id"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("采购退货出库单(单号：$ref)已经提交，不能再被编辑");
			}
			$dataOrg = $data[0]["data_org"];
			
			// 明细表
			$sql = "delete from t_pr_bill_detail where prbill_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
						goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
						prbill_id, pwbilldetail_id, data_org, company_id)
						values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$pwbillDetailId = $v["id"];
				$goodsId = $v["goodsId"];
				$goodsCount = $v["goodsCount"];
				$goodsPrice = $v["goodsPrice"];
				$goodsMoney = $goodsCount * $goodsPrice;
				$rejCount = $v["rejCount"];
				$rejPrice = $v["rejPrice"];
				$rejMoney = $v["rejMoney"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, 
						$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId, 
						$dataOrg, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$sql = "select sum(rejection_money) as rej_money 
						from t_pr_bill_detail 
						where prbill_id = '%s' ";
			$data = $db->query($sql, $id);
			$rejMoney = $data[0]["rej_money"];
			if (! $rejMoney) {
				$rejMoney = 0;
			}
			
			$sql = "update t_pr_bill
						set rejection_money = %f,
							bizdt = '%s', biz_user_id = '%s',
							date_created = now(), input_user_id = '%s',
							warehouse_id = '%s', receiving_type = %d
						where id = '%s' ";
			$rc = $db->execute($sql, $rejMoney, $bizDT, $bizUserId, $us->getLoginUserId(), 
					$warehouseId, $receivingType, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑采购退货出库单，单号：$ref";
		} else {
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			$companyId = $us->getCompanyId();
			
			// 新增采购退货出库单
			$id = $idGen->newId();
			$ref = $this->genNewBillRef();
			
			// 主表
			$sql = "insert into t_pr_bill(id, bill_status, bizdt, biz_user_id, supplier_id, date_created,
							input_user_id, ref, warehouse_id, pw_bill_id, receiving_type, data_org, company_id)
						values ('%s', 0, '%s', '%s', '%s', now(), '%s', '%s', '%s', '%s', %d, '%s', '%s')";
			$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $supplierId, $us->getLoginUserId(), 
					$ref, $warehouseId, $pwBillId, $receivingType, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细表
			$sql = "insert into t_pr_bill_detail(id, date_created, goods_id, goods_count, goods_price,
						goods_money, rejection_goods_count, rejection_goods_price, rejection_money, show_order,
						prbill_id, pwbilldetail_id, data_org, company_id)
						values ('%s', now(), '%s', %d, %f, %f, %d, %f, %f, %d, '%s', '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$pwbillDetailId = $v["id"];
				$goodsId = $v["goodsId"];
				$goodsCount = $v["goodsCount"];
				$goodsPrice = $v["goodsPrice"];
				$goodsMoney = $goodsCount * $goodsPrice;
				$rejCount = $v["rejCount"];
				$rejPrice = $v["rejPrice"];
				$rejMoney = $v["rejMoney"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, 
						$goodsMoney, $rejCount, $rejPrice, $rejMoney, $i, $id, $pwbillDetailId, 
						$dataOrg, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$sql = "select sum(rejection_money) as rej_money 
						from t_pr_bill_detail 
						where prbill_id = '%s' ";
			$data = $db->query($sql, $id);
			$rejMoney = $data[0]["rej_money"];
			
			$sql = "update t_pr_bill
						set rejection_money = %f
						where id = '%s' ";
			$rc = $db->execute($sql, $rejMoney, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新建采购退货出库单，单号：$ref";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function selectPWBillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ref = $params["ref"];
		$supplierId = $params["supplierId"];
		$warehouseId = $params["warehouseId"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		
		$result = array();
		
		$db = M();
		
		$sql = "select p.id, p.ref, p.biz_dt, s.name as supplier_name, p.goods_money,
					w.name as warehouse_name, u1.name as biz_user_name, u2.name as input_user_name
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id) 
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id) 
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$sql .= " order by p.ref desc limit %d, %d";
		$queryParamas[] = $start;
		$queryParamas[] = $limit;
		
		$data = $db->query($sql, $queryParamas);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = $this->toYMD($v["biz_dt"]);
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["amount"] = $v["goods_money"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id)
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$data = $db->query($sql, $queryParamas);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function getPWBillInfoForPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		$result = array();
		
		$db = M();
		
		$sql = "select p.ref,s.id as supplier_id, s.name as supplier_name,
					w.id as warehouse_id, w.name as warehouse_name 
				from t_pw_bill p, t_supplier s, t_warehouse w
				where p.supplier_id = s.id
					and p.warehouse_id = w.id
					and p.id = '%s' ";
		
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$result["ref"] = $data[0]["ref"];
		$result["supplierId"] = $data[0]["supplier_id"];
		$result["supplierName"] = $data[0]["supplier_name"];
		$result["warehouseId"] = $data[0]["warehouse_id"];
		$result["warehouseName"] = $data[0]["warehouse_name"];
		
		$items = array();
		
		$sql = "select p.id, g.id as goods_id, g.code as goods_code, g.name as goods_name,
					g.spec as goods_spec, u.name as unit_name, 
					p.goods_count, p.goods_price, p.goods_money
				from t_pw_bill_detail p, t_goods g, t_goods_unit u
				where p.goods_id = g.id
					and g.unit_id = u.id
					and p.pwbill_id = '%s'
				order by p.show_order ";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$items[$i]["id"] = $v["id"];
			$items[$i]["goodsId"] = $v["goods_id"];
			$items[$i]["goodsCode"] = $v["goods_code"];
			$items[$i]["goodsName"] = $v["goods_name"];
			$items[$i]["goodsSpec"] = $v["goods_spec"];
			$items[$i]["unitName"] = $v["unit_name"];
			$items[$i]["goodsCount"] = $v["goods_count"];
			$items[$i]["goodsPrice"] = $v["goods_price"];
			$items[$i]["goodsMoney"] = $v["goods_money"];
			$items[$i]["rejPrice"] = $v["goods_price"];
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	public function prbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		$supplierId = $params["supplierId"];
		$receivingType = $params["receivingType"];
		
		$db = M();
		$result = array();
		$queryParams = array();
		$sql = "select p.id, p.ref, p.bill_status, w.name as warehouse_name, p.bizdt,
					p.rejection_money, u1.name as biz_user_name, u2.name as input_user_name,
					s.name as supplier_name, p.date_created, p.receiving_type
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.supplier_id = s.id) ";
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (p.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$sql .= " order by p.bizdt desc, p.ref desc
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待出库" : "已出库";
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["rejMoney"] = $v["rejection_money"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizDT"] = $this->toYMD($v["bizdt"]);
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["receivingType"] = $v["receiving_type"];
		}
		
		$sql = "select count(*) as cnt
				from t_pr_bill p, t_warehouse w, t_user u1, t_user u2, t_supplier s
				where (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.supplier_id = s.id) ";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (p.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function prBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$db = M();
		$sql = "select g.code, g.name, g.spec, u.name as unit_name, 
					p.rejection_goods_count as rej_count, p.rejection_goods_price as rej_price, 
					p.rejection_money as rej_money
				from t_pr_bill_detail p, t_goods g, t_goods_unit u
				where p.goods_id = g.id and g.unit_id = u.id and p.prbill_id = '%s'
					and p.rejection_goods_count > 0
				order by p.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["rejCount"] = $v["rej_count"];
			$result[$i]["rejPrice"] = $v["rej_price"];
			$result[$i]["rejMoney"] = $v["rej_money"];
		}
		
		return $result;
	}

	/**
	 * 删除采购退货出库单
	 */
	public function deletePRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_pr_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的采购退货出库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("采购退货出库单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_pr_bill_detail where prbill_id = '%s'";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_pr_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$bs = new BizlogService();
		$log = "删除采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购退货出库单
	 */
	public function commitPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bs = new BizConfigService();
		$fifo = $bs->getInventoryMethod() == 1;
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status, warehouse_id, bizdt, biz_user_id, rejection_money,
						supplier_id, receiving_type, company_id
					from t_pr_bill 
					where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的采购退货出库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		$warehouseId = $data[0]["warehouse_id"];
		$bizDT = $this->toYMD($data[0]["bizdt"]);
		$bizUserId = $data[0]["biz_user_id"];
		$allRejMoney = $data[0]["rejection_money"];
		$supplierId = $data[0]["supplier_id"];
		$receivingType = $data[0]["receiving_type"];
		$companyId = $data[0]["company_id"];
		
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("采购退货出库单(单号：$ref)已经提交，不能再次提交");
		}
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("要出库的仓库不存在");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]还没有完成库存建账，不能进行出库操作");
		}
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法完成提交操作");
		}
		$sql = "select name from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		if (! $data) {
			$db->rollback();
			return $this->bad("供应商不存在，无法完成提交操作");
		}
		
		$allReceivingType = array(
				0,
				1
		);
		if (! in_array($receivingType, $allReceivingType)) {
			$db->rollback();
			return $this->bad("收款方式不正确，无法完成提交操作");
		}
		
		$sql = "select goods_id, rejection_goods_count as rej_count,
						rejection_money as rej_money,
						goods_count, goods_price, pwbilldetail_id
					from t_pr_bill_detail
					where prbill_id = '%s'
					order by show_order";
		$items = $db->query($sql, $id);
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goods_id"];
			$rejCount = $v["rej_count"];
			$rejMoney = $v["rej_money"];
			$goodsCount = $v["goods_count"];
			$goodsPricePurchase = $v["goods_price"];
			
			$pwbillDetailId = $v["pwbilldetail_id"];
			
			if ($rejCount == 0) {
				continue;
			}
			
			if ($rejCount < 0) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的退货数量不能为负数");
			}
			if ($rejCount > $goodsCount) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的退货数量不能大于采购数量");
			}
			
			if ($fifo) {
				// 先进先出
				
				$sql = "select balance_count, balance_price, balance_money,
								out_count, out_money, date_created
							from t_inventory_fifo
							where pwbilldetail_id = '%s' ";
				$data = $db->query($sql, $pwbillDetailId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$fifoDateCreated = $data[0]["date_created"];
				$fifoOutCount = $data[0]["out_count"];
				if (! $fifoOutCount) {
					$fifoOutCount = 0;
				}
				$fifoOutMoney = $data[0]["out_money"];
				if (! $fifoOutMoney) {
					$fifoOutMoney = 0;
				}
				$fifoBalanceCount = $data[0]["balance_count"];
				if ($fifoBalanceCount < $rejCount) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$fifoBalancePrice = $data[0]["balance_price"];
				$fifoBalanceMoney = $data[0]["balance_money"];
				$outMoney = 0;
				if ($rejCount == $fifoBalanceCount) {
					$outMoney = $fifoBalanceMoney;
				} else {
					$outMoney = $fifoBalancePrice * $rejCount;
				}
				
				// 库存总账
				$sql = "select balance_count, balance_price, balance_money,
							out_count, out_money
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$balanceCount = $data[0]["balance_count"];
				$balancePrice = $data[0]["balance_price"];
				$balanceMoney = $data[0]["balance_money"];
				
				$totalOutCount = $data[0]["out_count"];
				$totalOutMoney = $data[0]["out_money"];
				
				$outCount = $rejCount;
				$outPrice = $outMoney / $rejCount;
				
				$totalOutCount += $outCount;
				$totalOutMoney += $outMoney;
				$totalOutPrice = $totalOutMoney / $totalOutCount;
				$balanceCount -= $outCount;
				if ($balanceCount == 0) {
					$balanceMoney -= $outMoney;
					$balancePrice = 0;
				} else {
					$balanceMoney -= $outMoney;
					$balancePrice = $balanceMoney / $balanceCount;
				}
				
				$sql = "update t_inventory
						set out_count = %d, out_price = %f, out_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, 
						$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, biz_date, biz_user_id,
							date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购退货出库')";
				$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
						$ref);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// fifo
				$fvOutCount = $outCount + $fifoOutCount;
				$fvOutMoney = $outMoney + $fifoOutMoney;
				$fvBalanceCount = $fifoBalanceCount - $outCount;
				$fvBalanceMoney = 0;
				if ($fvBalanceCount > 0) {
					$fvBalanceMoney = $fifoBalanceMoney - $outMoney;
				}
				$sql = "update t_inventory_fifo
							set out_count = %d, out_price = %f, out_money = %f, balance_count = %d,
								balance_money = %f
							where pwbilldetail_id = '%s' ";
				$rc = $db->execute($sql, $fvOutCount, $fvOutMoney / $fvOutCount, $fvOutMoney, 
						$fvBalanceCount, $fvBalanceMoney, $pwbillDetailId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// fifo的明细记录
				$sql = "insert into t_inventory_fifo_detail(date_created, 
								out_count, out_price, out_money, balance_count, balance_price, balance_money,
								warehouse_id, goods_id)
							values ('%s', %d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $fifoDateCreated, $outCount, $outPrice, $outMoney, 
						$fvBalanceCount, $outPrice, $fvBalanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				// 移动平均法
				
				// 库存总账
				$sql = "select balance_count, balance_price, balance_money,
							out_count, out_money
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$balanceCount = $data[0]["balance_count"];
				$balancePrice = $data[0]["balance_price"];
				$balanceMoney = $data[0]["balance_money"];
				if ($rejCount > $balanceCount) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$totalOutCount = $data[0]["out_count"];
				$totalOutMoney = $data[0]["out_money"];
				
				$outCount = $rejCount;
				$outMoney = $goodsPricePurchase * $outCount;
				$outPrice = $goodsPricePurchase;
				
				$totalOutCount += $outCount;
				$totalOutMoney += $outMoney;
				$totalOutPrice = $totalOutMoney / $totalOutCount;
				$balanceCount -= $outCount;
				if ($balanceCount == 0) {
					$balanceMoney -= $outMoney;
					$balancePrice = 0;
				} else {
					$balanceMoney -= $outMoney;
					$balancePrice = $balanceMoney / $balanceCount;
				}
				
				$sql = "update t_inventory
						set out_count = %d, out_price = %f, out_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, 
						$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, biz_date, biz_user_id,
							date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购退货出库')";
				$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
						$ref);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
			}
		}
		
		$idGen = new IdGenService();
		
		if ($receivingType == 0) {
			// 记应收账款
			// 应收总账
			$sql = "select rv_money, balance_money
					from t_receivables
					where ca_id = '%s' and ca_type = 'supplier'
						and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			if (! $data) {
				$sql = "insert into t_receivables(id, rv_money, act_money, balance_money, ca_id, ca_type,
							company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $allRejMoney, $allRejMoney, $supplierId, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$rvMoney = $data[0]["rv_money"];
				$balanceMoney = $data[0]["balance_money"];
				$rvMoney += $allRejMoney;
				$balanceMoney += $allRejMoney;
				$sql = "update t_receivables
						set rv_money = %f, balance_money = %f
						where ca_id = '%s' and ca_type = 'supplier'
							and company_id = '%s' ";
				$rc = $db->execute($sql, $rvMoney, $balanceMoney, $supplierId, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 应收明细账
			$sql = "insert into t_receivables_detail(id, rv_money, act_money, balance_money, ca_id, ca_type,
						biz_date, date_created, ref_number, ref_type, company_id)
					values ('%s', %f, 0, %f, '%s', 'supplier', '%s', now(), '%s', '采购退货出库', '%s')";
			$rc = $db->execute($sql, $idGen->newId(), $allRejMoney, $allRejMoney, $supplierId, 
					$bizDT, $ref, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		} else if ($receivingType == 1) {
			// 现金收款
			$inCash = $allRejMoney;
			
			$sql = "select in_money, out_money, balance_money 
					from t_cash 
					where biz_date = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $bizDT, $companyId);
			if (! $data) {
				// 当天首次发生现金业务
				$sql = "select sum(in_money) as sum_in_money, sum(out_money) as sum_out_money
							from t_cash
							where biz_date <= '%s' and company_id = '%s' ";
				$data = $db->query($sql, $bizDT, $companyId);
				$sumInMoney = $data[0]["sum_in_money"];
				$sumOutMoney = $data[0]["sum_out_money"];
				if (! $sumInMoney) {
					$sumInMoney = 0;
				}
				if (! $sumOutMoney) {
					$sumOutMoney = 0;
				}
				
				$balanceCash = $sumInMoney - $sumOutMoney + $inCash;
				$sql = "insert into t_cash(in_money, balance_money, biz_date, company_id)
							values (%f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(in_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购退货出库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCash = $data[0]["balance_money"] + $inCash;
				$sumInMoney = $data[0]["in_money"] + $inCash;
				$sql = "update t_cash
							set in_money = %f, balance_money = %f
							where biz_date = '%s' and company_id = '%s' ";
				$db->execute($sql, $sumInMoney, $balanceCash, $bizDT, $companyId);
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(in_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购退货出库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 调整业务日期之后的现金总账和明细账的余额
			$sql = "update t_cash
							set balance_money = balance_money + %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $inCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "update t_cash_detail
							set balance_money = balance_money + %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $inCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 修改单据本身的状态
		$sql = "update t_pr_bill
					set bill_status = 1000
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$log = "提交采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}