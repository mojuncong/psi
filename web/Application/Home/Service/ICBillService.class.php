<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 库存盘点Service
 *
 * @author 李静波
 */
class ICBillService extends PSIBaseService {
	private $LOG_CATEGORY = "库存盘点";

	/**
	 * 生成新的盘点单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getICBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_ic_bill where ref like '%s' order by ref desc limit 1";
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

	/**
	 * 获得某个盘点单的详情
	 */
	public function icBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
			$db = M();
			$sql = "select t.ref, t.bill_status, t.bizdt, t.biz_user_id, u.name as biz_user_name,
						w.id as warehouse_id, w.name as warehouse_name
					from t_ic_bill t, t_user u, t_warehouse w
					where t.id = '%s' and t.biz_user_id = u.id
					      and t.warehouse_id = w.id";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $result;
			}
			
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["ref"] = $data[0]["ref"];
			$result["billStatus"] = $data[0]["bill_status"];
			$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
			$result["warehouseId"] = $data[0]["warehouse_id"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			
			$items = array();
			$sql = "select t.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, 
						t.goods_count, t.goods_money 
				from t_ic_bill_detail t, t_goods g, t_goods_unit u
				where t.icbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
			
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
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
	 * 新建或编辑盘点单
	 */
	public function editICBill($params) {
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
			return $this->bad("盘点仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法保存");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			$db->rollback();
			return $this->bad("业务日期不正确");
		}
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		$us = new UserService();
		$dataOrg = $us->getLoginUserDataOrg();
		
		$log = null;
		
		if ($id) {
			// 编辑单据
			$sql = "select ref, bill_status, data_org, company_id from t_ic_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的盘点点不存在，无法保存");
			}
			
			$ref = $data[0]["ref"];
			$dataOrg = $data[0]["data_org"];
			$companyId = $data[0]["company_id"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("盘点单(单号：$ref)已经提交，不能再编辑");
			}
			
			// 主表
			$sql = "update t_ic_bill
					set bizdt = '%s', biz_user_id = '%s', date_created = now(), 
						input_user_id = '%s', warehouse_id = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $bizDT, $bizUserId, $us->getLoginUserId(), $warehouseId, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细表
			$sql = "delete from t_ic_bill_detail where icbill_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "insert into t_ic_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						show_order, icbill_id, data_org, company_id)
					values ('%s', now(), '%s', %d, %f, %d, '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				$goodsCount = $v["goodsCount"];
				$goodsMoney = $v["goodsMoney"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, $i, 
						$id, $dataOrg, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "编辑盘点单，单号：$ref";
		} else {
			// 新建单据
			$id = $idGen->newId();
			$ref = $this->genNewBillRef();
			
			$companyId = $us->getCompanyId();
			
			// 主表
			$sql = "insert into t_ic_bill(id, bill_status, bizdt, biz_user_id, date_created, 
						input_user_id, ref, warehouse_id, data_org, company_id)
					values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $us->getLoginUserId(), $ref, 
					$warehouseId, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细表
			$sql = "insert into t_ic_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						show_order, icbill_id, data_org, company_id)
					values ('%s', now(), '%s', %d, %f, %d, '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				$goodsCount = $v["goodsCount"];
				$goodsMoney = $v["goodsMoney"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, $i, 
						$id, $dataOrg, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "新建盘点单，单号：$ref";
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
	 * 盘点单列表
	 */
	public function icbillList($params) {
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
		
		$db = M();
		
		$sql = "
			select t.id, t.ref, t.bizdt, t.bill_status,
				w.name as warehouse_name,
				u.name as biz_user_name,
				u1.name as input_user_name,
				t.date_created
			from t_ic_bill t, t_warehouse w, t_user u, t_user u1
			where (t.warehouse_id = w.id)
			and (t.biz_user_id = u.id)
			and (t.input_user_id = u1.id) ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::INVENTORY_CHECK, "t");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (t.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (t.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (t.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (t.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($warehouseId) {
			$sql .= " and (t.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		
		$sql .= " order by t.bizdt desc, t.ref desc
			limit %d , %d ";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待盘点" : "已盘点";
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
		}
		
		$sql = "select count(*) as cnt
				from t_ic_bill t, t_warehouse w, t_user u, t_user u1
				where (t.warehouse_id = w.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id) 
				";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::INVENTORY_CHECK, "t");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (t.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (t.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (t.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (t.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($warehouseId) {
			$sql .= " and (t.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 盘点单明细记录
	 */
	public function icBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select t.id, g.code, g.name, g.spec, u.name as unit_name, t.goods_count, t.goods_money
				from t_ic_bill_detail t, t_goods g, t_goods_unit u
				where t.icbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
		
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
		}
		
		return $result;
	}

	/**
	 * 删除盘点单
	 */
	public function deleteICBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_ic_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的盘点单不存在");
		}
		
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("盘点单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_ic_bill_detail where icbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_ic_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$bs = new BizlogService();
		$log = "删除盘点单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交盘点单
	 */
	public function commitICBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status, warehouse_id, bizdt, biz_user_id 
					from t_ic_bill 
					where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的盘点单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("盘点单(单号：$ref)已经提交，不能再次提交");
		}
		$warehouseId = $data[0]["warehouse_id"];
		$bizDT = date("Y-m-d", strtotime($data[0]["bizdt"]));
		$bizUserId = $data[0]["biz_user_id"];
		
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("要盘点的仓库不存在");
		}
		$inited = $data[0]["inited"];
		$warehouseName = $data[0]["name"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]还没有建账，无法做盘点操作");
		}
		
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法完成提交");
		}
		
		$sql = "select goods_id, goods_count, goods_money
					from t_ic_bill_detail
					where icbill_id = '%s' 
					order by show_order ";
		$items = $db->query($sql, $id);
		if (! $items) {
			$db->rollback();
			return $this->bad("盘点单没有明细信息，无法完成提交");
		}
		
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goods_id"];
			$goodsCount = $v["goods_count"];
			$goodsMoney = $v["goods_money"];
			
			// 检查商品是否存在
			$sql = "select code, name, spec from t_goods where id = '%s' ";
			$data = $db->query($sql, $goodsId);
			if (! $data) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的商品不存在，无法完成提交");
			}
			
			if ($goodsCount < 0) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的商品盘点后库存数量不能为负数");
			}
			if ($goodsMoney < 0) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的商品盘点后库存金额不能为负数");
			}
			if ($goodsCount == 0) {
				if ($goodsMoney != 0) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条记录的商品盘点后库存数量为0的时候，库存金额也必须为0");
				}
			}
			
			$sql = "select balance_count, balance_money, in_count, in_money, out_count, out_money 
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $warehouseId, $goodsId);
			if (! $data) {
				// 这种情况是：没有库存，做盘盈入库
				$inCount = $goodsCount;
				$inMoney = $goodsMoney;
				$inPrice = 0;
				if ($inCount != 0) {
					$inPrice = $inMoney / $inCount;
				}
				
				// 库存总账
				$sql = "insert into t_inventory(in_count, in_price, in_money, balance_count, balance_price,
							balance_money, warehouse_id, goods_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $inCount, $inPrice, $inMoney, 
						$warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(in_count, in_price, in_money, balance_count, balance_price,
							balance_money, warehouse_id, goods_id, biz_date, biz_user_id, date_created, ref_number,
							ref_type)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '库存盘点-盘盈入库')";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $inCount, $inPrice, $inMoney, 
						$warehouseId, $goodsId, $bizDT, $bizUserId, $ref);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCount = $data[0]["balance_count"];
				$balanceMoney = $data[0]["balance_money"];
				
				if ($goodsCount > $balanceCount) {
					// 盘盈入库
					$inCount = $goodsCount - $balanceCount;
					$inMoney = $goodsMoney - $balanceMoney;
					$inPrice = $inMoney / $inCount;
					$balanceCount = $goodsCount;
					$balanceMoney = $goodsMoney;
					$balancePrice = $balanceMoney / $balanceCount;
					$totalInCount = $data[0]["in_count"] + $inCount;
					$totalInMoney = $data[0]["in_money"] + $inMoney;
					$totalInPrice = $totalInMoney / $totalInCount;
					
					// 库存总账
					$sql = "update t_inventory
								set in_count = %d, in_price = %f, in_money = %f, 
								    balance_count = %d, balance_price = %f,
							        balance_money = %f
								where warehouse_id = '%s' and goods_id = '%s' ";
					$rc = $db->execute($sql, $totalInCount, $totalInPrice, $totalInMoney, 
							$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 库存明细账
					$sql = "insert into t_inventory_detail(in_count, in_price, in_money, balance_count, balance_price,
							balance_money, warehouse_id, goods_id, biz_date, biz_user_id, date_created, ref_number,
							ref_type)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '库存盘点-盘盈入库')";
					$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, 
							$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
							$ref);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				} else {
					// 盘亏出库
					$outCount = $balanceCount - $goodsCount;
					$outMoney = $balanceMoney - $goodsMoney;
					$outPrice = 0;
					if ($outCount != 0) {
						$outPrice = $outMoney / $outCount;
					}
					$balanceCount = $goodsCount;
					$balanceMoney = $goodsMoney;
					$balancePrice = 0;
					if ($balanceCount != 0) {
						$balancePrice = $balanceMoney / $balanceCount;
					}
					
					$totalOutCount = $data[0]["out_count"] + $outCount;
					$totalOutMoney = $data[0]["out_money"] + $outMoney;
					$totalOutPrice = $totalOutMoney / $totalOutCount;
					
					// 库存总账
					$sql = "update t_inventory
								set out_count = %d, out_price = %f, out_money = %f, 
								    balance_count = %d, balance_price = %f,
							        balance_money = %f
								where warehouse_id = '%s' and goods_id = '%s' ";
					$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, 
							$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 库存明细账
					$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count, balance_price,
							balance_money, warehouse_id, goods_id, biz_date, biz_user_id, date_created, ref_number,
							ref_type)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '库存盘点-盘亏出库')";
					$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, 
							$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
							$ref);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
		}
		
		// 修改单据本身状态
		$sql = "update t_ic_bill
				set bill_status = 1000
				where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$log = "提交盘点单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}