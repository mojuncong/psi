<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 采购入库Service
 *
 * @author 李静波
 */
class PWBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购入库";

	/**
	 * 获得采购入库单主表列表
	 */
	public function pwbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		$supplierId = $params["supplierId"];
		$paymentType = $params["paymentType"];
		
		$db = M();
		
		$queryParams = array();
		$sql = "select p.id, p.bill_status, p.ref, p.biz_dt, u1.name as biz_user_name, u2.name as input_user_name, 
					p.goods_money, w.name as warehouse_name, s.name as supplier_name,
					p.date_created, p.payment_type
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2 
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id) 
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) ";
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_WAREHOUSE, "p");
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
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
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
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$sql .= " order by p.biz_dt desc, p.ref desc 
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = $this->toYMD($v["biz_dt"]);
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待入库" : "已入库";
			$result[$i]["amount"] = $v["goods_money"];
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["paymentType"] = $v["payment_type"];
		}
		
		$sql = "select count(*) as cnt 
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2 
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id) 
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PURCHASE_WAREHOUSE, "p");
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
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
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
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 获得采购入库单商品明细记录列表
	 */
	public function pwBillDetailList($pwbillId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select p.id, g.code, g.name, g.spec, u.name as unit_name, p.goods_count, p.goods_price, 
					p.goods_money, p.memo 
				from t_pw_bill_detail p, t_goods g, t_goods_unit u 
				where p.pwbill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id 
				order by p.show_order ";
		$data = M()->query($sql, $pwbillId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["goodsPrice"] = $v["goods_price"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		return $result;
	}

	/**
	 * 新建或编辑采购入库单
	 */
	public function editPWBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$supplierId = $bill["supplierId"];
		$bizUserId = $bill["bizUserId"];
		$paymentType = $bill["paymentType"];
		
		$pobillRef = $bill["pobillRef"];
		
		$db = M();
		
		$sql = "select count(*) as cnt from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("入库仓库不存在");
		}
		
		$sql = "select count(*) as cnt from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("供应商不存在");
		}
		
		$sql = "select count(*) as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("业务人员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$db->startTrans();
		
		$idGen = new IdGenService();
		$us = new UserService();
		$dataOrg = $us->getLoginUserDataOrg();
		
		$log = null;
		
		if ($id) {
			// 编辑采购入库单
			$sql = "select ref, bill_status, data_org, company_id from t_pw_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的采购入库单不存在");
			}
			$dataOrg = $data[0]["data_org"];
			$billStatus = $data[0]["bill_status"];
			$companyId = $data[0]["company_id"];
			$ref = $data[0]["ref"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("当前采购入库单已经提交入库，不能再编辑");
			}
			
			$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细记录
			$items = $bill["items"];
			foreach ( $items as $i => $item ) {
				$goodsId = $item["goodsId"];
				$goodsCount = intval($item["goodsCount"]);
				$memo = $item["memo"];
				if ($goodsId != null && $goodsCount != 0) {
					// 检查商品是否存在
					$sql = "select count(*) as cnt from t_goods where id = '%s' ";
					$data = $db->query($sql, $goodsId);
					$cnt = $data[0]["cnt"];
					if ($cnt == 1) {
						$goodsPrice = $item["goodsPrice"];
						$goodsMoney = $item["goodsMoney"];
						
						$sql = "insert into t_pw_bill_detail (id, date_created, goods_id, goods_count, goods_price,
									goods_money,  pwbill_id, show_order, data_org, memo, company_id)
									values ('%s', now(), '%s', %d, %f, %f, '%s', %d, '%s', '%s', '%s')";
						$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, 
								$goodsPrice, $goodsMoney, $id, $i, $dataOrg, $memo, $companyId);
						if ($rc === false) {
							$db->rollback();
							return $this->sqlError(__LINE__);
						}
					}
				}
			}
			
			$sql = "select sum(goods_money) as goods_money from t_pw_bill_detail 
						where pwbill_id = '%s' ";
			$data = $db->query($sql, $id);
			$totalMoney = $data[0]["goods_money"];
			if (! $totalMoney) {
				$totalMoney = 0;
			}
			$sql = "update t_pw_bill 
						set goods_money = %f, warehouse_id = '%s', 
							supplier_id = '%s', biz_dt = '%s',
							biz_user_id = '%s', payment_type = %d
						where id = '%s' ";
			$rc = $db->execute($sql, $totalMoney, $warehouseId, $supplierId, $bizDT, $bizUserId, 
					$paymentType, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑采购入库单: 单号 = {$ref}";
		} else {
			// 新建采购入库单
			
			$companyId = $us->getCompanyId();
			
			$id = $idGen->newId();
			
			$sql = "insert into t_pw_bill (id, ref, supplier_id, warehouse_id, biz_dt, 
						biz_user_id, bill_status, date_created, goods_money, input_user_id, payment_type,
						data_org, company_id) 
						values ('%s', '%s', '%s', '%s', '%s', '%s', 0, now(), 0, '%s', %d, '%s', '%s')";
			
			$ref = $this->genNewBillRef();
			
			$rc = $db->execute($sql, $id, $ref, $supplierId, $warehouseId, $bizDT, $bizUserId, 
					$us->getLoginUserId(), $paymentType, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细记录
			$items = $bill["items"];
			foreach ( $items as $i => $item ) {
				$goodsId = $item["goodsId"];
				$goodsCount = intval($item["goodsCount"]);
				$memo = $item["memo"];
				if ($goodsId != null && $goodsCount != 0) {
					// 检查商品是否存在
					$sql = "select count(*) as cnt from t_goods where id = '%s' ";
					$data = $db->query($sql, $goodsId);
					$cnt = $data[0]["cnt"];
					if ($cnt == 1) {
						
						$goodsPrice = $item["goodsPrice"];
						$goodsMoney = $item["goodsMoney"];
						
						$sql = "insert into t_pw_bill_detail 
									(id, date_created, goods_id, goods_count, goods_price,
									goods_money,  pwbill_id, show_order, data_org, memo, company_id)
								values ('%s', now(), '%s', %d, %f, %f, '%s', %d, '%s', '%s', '%s')";
						$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, 
								$goodsPrice, $goodsMoney, $id, $i, $dataOrg, $memo, $companyId);
						if ($rc === false) {
							$db->rollback();
							return $this->sqlError(__LINE__);
						}
					}
				}
			}
			
			$sql = "select sum(goods_money) as goods_money from t_pw_bill_detail 
						where pwbill_id = '%s' ";
			$data = $db->query($sql, $id);
			$totalMoney = $data[0]["goods_money"];
			if (! $totalMoney) {
				$totalMoney = 0;
			}
			$sql = "update t_pw_bill
						set goods_money = %f 
						where id = '%s' ";
			$rc = $db->execute($sql, $totalMoney, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			if ($pobillRef) {
				// 从采购订单生成采购入库单
				$sql = "select id, company_id from t_po_bill where ref = '%s' ";
				$data = $db->query($sql, $pobillRef);
				if (! $data) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				$pobillId = $data[0]["id"];
				$companyId = $data[0]["company_id"];
				
				$sql = "update t_pw_bill
							set company_id = '%s'
							where id = '%s' ";
				$rc = $db->execute($sql, $companyId, $id);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				$sql = "insert into t_po_pw(po_id, pw_id) values('%s', '%s')";
				$rc = $db->execute($sql, $pobillId, $id);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				$log = "从采购订单(单号：{$pobillRef})生成采购入库单: 单号 = {$ref}";
			} else {
				// 手工新建采购入库单
				$log = "新建采购入库单: 单号 = {$ref}";
			}
		}
		
		// 同步库存账中的在途库存
		$sql = "select goods_id
				from t_pw_bill_detail
				where pwbill_id = '%s' 
				order by show_order";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$goodsId = $v["goods_id"];
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
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
	 * 生成新的采购入库单单号
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getPWBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_pw_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$suf = "001";
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	/**
	 * 获得某个采购入库单的信息
	 */
	public function pwBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		$pobillRef = $params["pobillRef"];
		
		$result["id"] = $id;
		
		$db = M();
		$sql = "select p.ref, p.bill_status, p.supplier_id, s.name as supplier_name, 
				p.warehouse_id, w.name as  warehouse_name, 
				p.biz_user_id, u.name as biz_user_name, p.biz_dt, p.payment_type 
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u 
				where p.id = '%s' and p.supplier_id = s.id and p.warehouse_id = w.id 
				  and p.biz_user_id = u.id";
		$data = $db->query($sql, $id);
		if ($data) {
			$v = $data[0];
			$result["ref"] = $v["ref"];
			$result["billStatus"] = $v["bill_status"];
			$result["supplierId"] = $v["supplier_id"];
			$result["supplierName"] = $v["supplier_name"];
			$result["warehouseId"] = $v["warehouse_id"];
			$result["warehouseName"] = $v["warehouse_name"];
			$result["bizUserId"] = $v["biz_user_id"];
			$result["bizUserName"] = $v["biz_user_name"];
			$result["bizDT"] = date("Y-m-d", strtotime($v["biz_dt"]));
			$result["paymentType"] = $v["payment_type"];
			
			// 采购的商品明细
			$items = array();
			$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name, 
					p.goods_count, p.goods_price, p.goods_money, p.memo 
					from t_pw_bill_detail p, t_goods g, t_goods_unit u 
					where p.goods_Id = g.id and g.unit_id = u.id and p.pwbill_id = '%s' 
					order by p.show_order";
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["memo"] = $v["memo"];
			}
			
			$result["items"] = $items;
			
			// 查询该单据是否是由采购订单生成的
			$sql = "select po_id from t_po_pw where pw_id = '%s' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$result["genBill"] = true;
			} else {
				$result["genBill"] = false;
			}
		} else {
			// 新建采购入库单
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			
			$ts = new BizConfigService();
			$sql = "select value from t_config where id = '2001-01' ";
			$data = $db->query($sql);
			if ($data) {
				$warehouseId = $data[0]["value"];
				$sql = "select id, name from t_warehouse where id = '%s' ";
				$data = $db->query($sql, $warehouseId);
				if ($data) {
					$result["warehouseId"] = $data[0]["id"];
					$result["warehouseName"] = $data[0]["name"];
				}
			}
			
			if ($pobillRef) {
				// 由采购订单生成采购入库单
				$sql = "select p.id, p.supplier_id, s.name as supplier_name, p.deal_date,
							p.payment_type
						from t_po_bill p, t_supplier s
						where p.ref = '%s' and p.supplier_id = s.id ";
				$data = $db->query($sql, $pobillRef);
				if ($data) {
					$v = $data[0];
					$result["supplierId"] = $v["supplier_id"];
					$result["supplierName"] = $v["supplier_name"];
					$result["dealDate"] = $this->toYMD($v["deal_date"]);
					$result["paymentType"] = $v["payment_type"];
					
					$pobillId = $v["id"];
					// 采购的明细
					$items = array();
					$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name,
								p.goods_count, p.goods_price, p.goods_money
							from t_po_bill_detail p, t_goods g, t_goods_unit u
							where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
							order by p.show_order ";
					$data = $db->query($sql, $pobillId);
					foreach ( $data as $i => $v ) {
						$items[$i]["id"] = $v["id"];
						$items[$i]["goodsId"] = $v["goods_id"];
						$items[$i]["goodsCode"] = $v["code"];
						$items[$i]["goodsName"] = $v["name"];
						$items[$i]["goodsSpec"] = $v["spec"];
						$items[$i]["unitName"] = $v["unit_name"];
						$items[$i]["goodsCount"] = $v["goods_count"];
						$items[$i]["goodsPrice"] = $v["goods_price"];
						$items[$i]["goodsMoney"] = $v["goods_money"];
					}
					
					$result["items"] = $items;
				}
			}
		}
		
		return $result;
	}

	/**
	 * 删除采购入库单
	 */
	public function deletePWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status, warehouse_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的采购入库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("当前采购入库单已经提交入库，不能删除");
		}
		$warehouseId = $data[0]["warehouse_id"];
		
		$sql = "select goods_id
				from t_pw_bill_detail
				where pwbill_id = '%s'
				order by show_order";
		$data = $db->query($sql, $id);
		$goodsIdList = array();
		foreach ( $data as $v ) {
			$goodsIdList[] = $v["goods_id"];
		}
		
		$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_pw_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 删除从采购订单生成的记录
		$sql = "delete from t_po_pw where pw_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 同步库存账中的在途库存
		foreach ( $goodsIdList as $v ) {
			$goodsId = $v;
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 记录业务日志
		$log = "删除采购入库单: 单号 = {$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购入库单
	 */
	public function commitPWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bs = new BizConfigService();
		
		// true: 先进先出法
		$fifo = $bs->getInventoryMethod() == 1;
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, warehouse_id, bill_status, biz_dt, biz_user_id,  goods_money, supplier_id,
					payment_type, company_id
				from t_pw_bill 
				where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的采购入库单不存在");
		}
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("采购入库单已经提交入库，不能再次提交");
		}
		
		$ref = $data[0]["ref"];
		$bizDT = $data[0]["biz_dt"];
		$bizUserId = $data[0]["biz_user_id"];
		$billPayables = floatval($data[0]["goods_money"]);
		$supplierId = $data[0]["supplier_id"];
		$warehouseId = $data[0]["warehouse_id"];
		$paymentType = $data[0]["payment_type"];
		$companyId = $data[0]["company_id"];
		
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("要入库的仓库不存在");
		}
		$inited = $data[0]["inited"];
		if ($inited == 0) {
			$db->rollback();
			return $this->bad("仓库 [{$data[0]['name']}] 还没有完成建账，不能做采购入库的操作");
		}
		
		// 检查供应商是否存在
		$ss = new SupplierService();
		if (! $ss->supplierExists($supplierId, $db)) {
			$db->rollback();
			return $this->bad("供应商不存在");
		}
		
		// 检查业务员是否存在
		$us = new UserService();
		if (! $us->userExists($bizUserId, $db)) {
			$db->rollback();
			return $this->bad("业务员不存在");
		}
		
		$sql = "select goods_id, goods_count, goods_price, goods_money, id 
				from t_pw_bill_detail 
				where pwbill_id = '%s' order by show_order";
		$items = $db->query($sql, $id);
		if (! $items) {
			$db->rollback();
			return $this->bad("采购入库单没有采购明细记录，不能入库");
		}
		
		// 检查入库数量、单价、金额不能为负数
		foreach ( $items as $v ) {
			$goodsCount = intval($v["goods_count"]);
			if ($goodsCount <= 0) {
				$db->rollback();
				return $this->bad("采购数量不能小于0");
			}
			$goodsPrice = floatval($v["goods_price"]);
			if ($goodsPrice < 0) {
				$db->rollback();
				return $this->bad("采购单价不能为负数");
			}
			$goodsMoney = floatval($v["goods_money"]);
			if ($goodsMoney < 0) {
				$db->rollback();
				return $this->bad("采购金额不能为负数");
			}
		}
		
		$allPaymentType = array(
				0,
				1,
				2
		);
		if (! in_array($paymentType, $allPaymentType)) {
			$db->rollback();
			return $this->bad("付款方式填写不正确，无法提交");
		}
		
		foreach ( $items as $v ) {
			$pwbilldetailId = $v["id"];
			
			$goodsCount = intval($v["goods_count"]);
			$goodsPrice = floatval($v["goods_price"]);
			$goodsMoney = floatval($v["goods_money"]);
			if ($goodsCount != 0) {
				$goodsPrice = $goodsMoney / $goodsCount;
			}
			
			$goodsId = $v["goods_id"];
			
			$balanceCount = 0;
			$balanceMoney = 0;
			$balancePrice = (float)0;
			// 库存总账
			$sql = "select in_count, in_money, balance_count, balance_money 
						from t_inventory 
						where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $warehouseId, $goodsId);
			if ($data) {
				$inCount = intval($data[0]["in_count"]);
				$inMoney = floatval($data[0]["in_money"]);
				$balanceCount = intval($data[0]["balance_count"]);
				$balanceMoney = floatval($data[0]["balance_money"]);
				
				$inCount += $goodsCount;
				$inMoney += $goodsMoney;
				$inPrice = $inMoney / $inCount;
				
				$balanceCount += $goodsCount;
				$balanceMoney += $goodsMoney;
				$balancePrice = $balanceMoney / $balanceCount;
				
				$sql = "update t_inventory 
							set in_count = %d, in_price = %f, in_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f 
							where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
						$balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$inCount = $goodsCount;
				$inMoney = $goodsMoney;
				$inPrice = $inMoney / $inCount;
				$balanceCount += $goodsCount;
				$balanceMoney += $goodsMoney;
				$balancePrice = $balanceMoney / $balanceCount;
				
				$sql = "insert into t_inventory (in_count, in_price, in_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
						$balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 库存明细账
			$sql = "insert into t_inventory_detail (in_count, in_price, in_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id, biz_date,
						biz_user_id, date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购入库')";
			$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $balanceCount, 
					$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, $ref);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 先进先出
			if ($fifo) {
				$dt = date("Y-m-d H:i:s");
				$sql = "insert into t_inventory_fifo (in_count, in_price, in_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, date_created, in_ref,
							in_ref_type, pwbilldetail_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', '采购入库', '%s')";
				$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $goodsCount, 
						$goodsPrice, $goodsMoney, $warehouseId, $goodsId, $dt, $ref, $pwbilldetailId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// fifo 明细记录
				$sql = "insert into t_inventory_fifo_detail(in_count, in_price, in_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, date_created, pwbilldetail_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s')";
				$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $goodsCount, 
						$goodsPrice, $goodsMoney, $warehouseId, $goodsId, $dt, $pwbilldetailId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
		}
		
		$sql = "update t_pw_bill set bill_status = 1000 where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		if ($paymentType == 0) {
			// 记应付账款
			// 应付明细账
			$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money,
					ca_id, ca_type, date_created, ref_number, ref_type, biz_date, company_id)
					values ('%s', %f, 0, %f, '%s', 'supplier', now(), '%s', '采购入库', '%s', '%s')";
			$idGen = new IdGenService();
			$rc = $db->execute($sql, $idGen->newId(), $billPayables, $billPayables, $supplierId, 
					$ref, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 应付总账
			$sql = "select id, pay_money 
					from t_payables 
					where ca_id = '%s' and ca_type = 'supplier' and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			if ($data) {
				$pId = $data[0]["id"];
				$payMoney = floatval($data[0]["pay_money"]);
				$payMoney += $billPayables;
				
				$sql = "update t_payables 
						set pay_money = %f, balance_money = %f 
						where id = '%s' ";
				$rc = $db->execute($sql, $payMoney, $payMoney, $pId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$payMoney = $billPayables;
				
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, 
						ca_id, ca_type, company_id) 
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $payMoney, $payMoney, $supplierId, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
		} else if ($paymentType == 1) {
			// 现金付款
			
			$outCash = $billPayables;
			
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
				
				$balanceCash = $sumInMoney - $sumOutMoney - $outCash;
				$sql = "insert into t_cash(out_money, balance_money, biz_date, company_id)
							values (%f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type, 
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCash = $data[0]["balance_money"] - $outCash;
				$sumOutMoney = $data[0]["out_money"] + $outCash;
				$sql = "update t_cash
							set out_money = %f, balance_money = %f
							where biz_date = '%s' and company_id = '%s' ";
				$rc = $db->execute($sql, $sumOutMoney, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type, 
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 调整业务日期之后的现金总账和明细账的余额
			$sql = "update t_cash
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "update t_cash_detail
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		} else if ($paymentType == 2) {
			// 2: 预付款
			
			$outMoney = $billPayables;
			
			$sql = "select out_money, balance_money from t_pre_payment
						where supplier_id = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			$totalOutMoney = $data[0]["out_money"];
			$totalBalanceMoney = $data[0]["balance_money"];
			if (! $totalOutMoney) {
				$totalOutMoney = 0;
			}
			if (! $totalBalanceMoney) {
				$totalBalanceMoney = 0;
			}
			if ($outMoney > $totalBalanceMoney) {
				$db->rollback();
				$ss = new SupplierService();
				$supplierName = $ss->getSupplierNameById($supplierId, $db);
				return $this->bad(
						"供应商[{$supplierName}]预付款余额不足，无法完成支付<br/><br/>余额:{$totalBalanceMoney}元，付款金额:{$outMoney}元");
			}
			
			// 预付款总账
			$sql = "update t_pre_payment
						set out_money = %f, balance_money = %f
						where supplier_id = '%s' and company_id = '%s' ";
			$totalOutMoney += $outMoney;
			$totalBalanceMoney -= $outMoney;
			$rc = $db->execute($sql, $totalOutMoney, $totalBalanceMoney, $supplierId, $companyId);
			if (! $rc) {
				$db->rollback();
				return $this->sqlError();
			}
			
			// 预付款明细账
			$sql = "insert into t_pre_payment_detail(id, supplier_id, out_money, balance_money,
						biz_date, date_created, ref_number, ref_type, biz_user_id, input_user_id,
						company_id)
						values ('%s', '%s', %f, %f, '%s', now(), '%s', '采购入库', '%s', '%s', '%s')";
			$idGen = new IdGenService();
			$us = new UserService();
			$rc = $db->execute($sql, $idGen->newId(), $supplierId, $outMoney, $totalBalanceMoney, 
					$bizDT, $ref, $bizUserId, $us->getLoginUserId(), $companyId);
			if (! $rc) {
				$db->rollback();
				return $this->sqlError();
			}
		}
		
		// 同步库存账中的在途库存
		$sql = "select goods_id
				from t_pw_bill_detail
				where pwbill_id = '%s' 
				order by show_order";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$goodsId = $v["goods_id"];
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 业务日志
		$log = "提交采购入库单: 单号 = {$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 同步在途库存
	 */
	private function updateAfloatInventory($db, $warehouseId, $goodsId) {
		$sql = "select sum(pd.goods_count) as goods_count, sum(pd.goods_money) as goods_money
				from t_pw_bill p, t_pw_bill_detail pd
				where p.id = pd.pwbill_id 
					and p.warehouse_id = '%s' 
					and pd.goods_id = '%s'
					and p.bill_status = 0 ";
		
		$data = $db->query($sql, $warehouseId, $goodsId);
		$count = 0;
		$price = 0;
		$money = 0;
		if ($data) {
			$count = $data[0]["goods_count"];
			if (! $count) {
				$count = 0;
			}
			$money = $data[0]["goods_money"];
			if (! $money) {
				$money = 0;
			}
			
			if ($count !== 0) {
				$price = $money / $count;
			}
		}
		
		$sql = "select id from t_inventory where warehouse_id = '%s' and goods_id = '%s' ";
		$data = $db->query($sql, $warehouseId, $goodsId);
		if (! $data) {
			// 首次有库存记录
			$sql = "insert into t_inventory (warehouse_id, goods_id, afloat_count, afloat_price,
						afloat_money, balance_count, balance_price, balance_money)
					values ('%s', '%s', %d, %f, %f, 0, 0, 0)";
			return $db->execute($sql, $warehouseId, $goodsId, $count, $price, $money);
		} else {
			$sql = "update t_inventory
					set afloat_count = %d, afloat_price = %f, afloat_money = %f
					where warehouse_id = '%s' and goods_id = '%s' ";
			return $db->execute($sql, $count, $price, $money, $warehouseId, $goodsId);
		}
		
		return true;
	}
}