<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 库间调拨Service
 *
 * @author 李静波
 */
class ITBillService extends PSIBaseService {
	private $LOG_CATEGORY = "库间调拨";

	/**
	 * 生成新的调拨单单号
	 *
	 * @return string
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getITBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_it_bill where ref like '%s' order by ref desc limit 1";
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
	 * 调拨单主表列表信息
	 */
	public function itbillList($params) {
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
		$fromWarehouseId = $params["fromWarehouseId"];
		$toWarehouseId = $params["toWarehouseId"];
		
		$db = M();
		
		$sql = "select t.id, t.ref, t.bizdt, t.bill_status,
					fw.name as from_warehouse_name,
					tw.name as to_warehouse_name,
					u.name as biz_user_name,
					u1.name as input_user_name,
					t.date_created
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where (t.from_warehouse_id = fw.id) 
				  and (t.to_warehouse_id = tw.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id) ";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::INVENTORY_TRANSFER, "t");
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
		if ($fromWarehouseId) {
			$sql .= " and (t.from_warehouse_id = '%s') ";
			$queryParams[] = $fromWarehouseId;
		}
		if ($toWarehouseId) {
			$sql .= " and (t.to_warehouse_id = '%s') ";
			$queryParams[] = $toWarehouseId;
		}
		
		$sql .= " order by t.bizdt desc, t.ref desc
				limit %d , %d
				";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待调拨" : "已调拨";
			$result[$i]["fromWarehouseName"] = $v["from_warehouse_name"];
			$result[$i]["toWarehouseName"] = $v["to_warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
		}
		
		$sql = "select count(*) as cnt
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where (t.from_warehouse_id = fw.id) 
				  and (t.to_warehouse_id = tw.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id)
				";
		$queryParams = array();
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::INVENTORY_TRANSFER, "t");
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
		if ($fromWarehouseId) {
			$sql .= " and (t.from_warehouse_id = '%s') ";
			$queryParams[] = $fromWarehouseId;
		}
		if ($toWarehouseId) {
			$sql .= " and (t.to_warehouse_id = '%s') ";
			$queryParams[] = $toWarehouseId;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 新建或编辑调拨单
	 */
	public function editITBill($params) {
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
		$fromWarehouseId = $bill["fromWarehouseId"];
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $fromWarehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("调出仓库不存在，无法保存");
		}
		
		$toWarehouseId = $bill["toWarehouseId"];
		$sql = "select name from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $toWarehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("调入仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法保存");
		}
		
		if ($fromWarehouseId == $toWarehouseId) {
			$db->rollback();
			return $this->bad("调出仓库和调入仓库不能是同一个仓库");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			$db->rollback();
			return $this->bad("业务日期不正确");
		}
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		$us = new UserService();
		
		$log = null;
		
		if ($id) {
			// 编辑
			$sql = "select ref, bill_status, data_org, company_id from t_it_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的调拨单不存在");
			}
			$ref = $data[0]["ref"];
			$dataOrg = $data[0]["data_org"];
			$companyId = $data[0]["company_id"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("调拨单(单号：$ref)已经提交，不能被编辑");
			}
			
			$sql = "update t_it_bill
					set bizdt = '%s', biz_user_id = '%s', date_created = now(),
					    input_user_id = '%s', from_warehouse_id = '%s', to_warehouse_id = '%s'
					where id = '%s' ";
			
			$rc = $db->execute($sql, $bizDT, $bizUserId, $us->getLoginUserId(), $fromWarehouseId, 
					$toWarehouseId, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细记录
			$sql = "delete from t_it_bill_detail where itbill_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count, 
						show_order, itbill_id, data_org, company_id)
					values ('%s', now(), '%s', %d, %d, '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				
				$goodsCount = $v["goodsCount"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $i, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "编辑调拨单，单号：$ref";
		} else {
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			$companyId = $us->getCompanyId();
			
			// 新增
			$sql = "insert into t_it_bill(id, bill_status, bizdt, biz_user_id,
						date_created, input_user_id, ref, from_warehouse_id, 
						to_warehouse_id, data_org, company_id)
					values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s')";
			$id = $idGen->newId();
			$ref = $this->genNewBillRef();
			
			$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $us->getLoginUserId(), $ref, 
					$fromWarehouseId, $toWarehouseId, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count, 
							show_order, itbill_id, data_org, company_id)
						values ('%s', now(), '%s', %d, %d, '%s', '%s', '%s')";
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				
				$goodsCount = $v["goodsCount"];
				
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $i, $id, $dataOrg, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			$log = "新建调拨单，单号：$ref";
		}
		
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 查询某个调拨单的详情
	 */
	public function itBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
			$db = M();
			$sql = "select t.ref, t.bill_status, t.bizdt, t.biz_user_id, u.name as biz_user_name,
						wf.id as from_warehouse_id, wf.name as from_warehouse_name,
						wt.id as to_warehouse_id, wt.name as to_warehouse_name
					from t_it_bill t, t_user u, t_warehouse wf, t_warehouse wt
					where t.id = '%s' and t.biz_user_id = u.id
					      and t.from_warehouse_id = wf.id
					      and t.to_warehouse_id = wt.id";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $result;
			}
			
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["ref"] = $data[0]["ref"];
			$result["billStatus"] = $data[0]["bill_status"];
			$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
			$result["fromWarehouseId"] = $data[0]["from_warehouse_id"];
			$result["fromWarehouseName"] = $data[0]["from_warehouse_name"];
			$result["toWarehouseId"] = $data[0]["to_warehouse_id"];
			$result["toWarehouseName"] = $data[0]["to_warehouse_name"];
			
			$items = array();
			$sql = "select t.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, t.goods_count 
				from t_it_bill_detail t, t_goods g, t_goods_unit u
				where t.itbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
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
	 * 调拨单的明细记录
	 */
	public function itBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select t.id, g.code, g.name, g.spec, u.name as unit_name, t.goods_count 
				from t_it_bill_detail t, t_goods g, t_goods_unit u
				where t.itbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
		
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
		}
		
		return $result;
	}

	/**
	 * 删除调拨单
	 */
	public function deleteITBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_it_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的调拨单不存在");
		}
		
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("调拨单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_it_bill_detail where itbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_it_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$bs = new BizlogService();
		$log = "删除调拨单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交调拨单
	 */
	public function commitITBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status, from_warehouse_id, to_warehouse_id,
					bizdt, biz_user_id
				from t_it_bill 
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的调拨单不存在，无法提交");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("调拨单(单号：$ref)已经提交，不能再次提交");
		}
		
		$bizUserId = $data[0]["biz_user_id"];
		$bizDT = date("Y-m-d", strtotime($data[0]["bizdt"]));
		
		$fromWarehouseId = $data[0]["from_warehouse_id"];
		$toWarehouseId = $data[0]["to_warehouse_id"];
		
		// 检查仓库是否存在，仓库是否已经完成建账
		$sql = "select name , inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $fromWarehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("调出仓库不存在，无法进行调拨操作");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库：$warehouseName 还没有完成建账，无法进行调拨操作");
		}
		
		$sql = "select name , inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $toWarehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("调入仓库不存在，无法进行调拨操作");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库：$warehouseName 还没有完成建账，无法进行调拨操作");
		}
		
		if ($fromWarehouseId == $toWarehouseId) {
			$db->rollback();
			return $this->bad("调出仓库和调入仓库不能是同一个仓库");
		}
		
		$sql = "select goods_id, goods_count 
					from t_it_bill_detail 
					where itbill_id = '%s' 
					order by show_order";
		$items = $db->query($sql, $id);
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goods_id"];
			$goodsCount = $v["goods_count"];
			// 检查商品Id是否存在
			$sql = "select code, name, spec from t_goods where id = '%s' ";
			$data = $db->query($sql, $goodsId);
			if (! $data) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条明细记录中的商品不存在，无法完成提交");
			}
			$goodsCode = $data[0]["code"];
			$goodsName = $data[0]["name"];
			$goodsSpec = $data[0]["spec"];
			
			// 检查调出数量是否为正数
			if ($goodsCount <= 0) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条明细记录中的调拨数量不是正数，无法完成提交");
			}
			
			// 检查调出库存是否足够
			$sql = "select balance_count, balance_price, balance_money, out_count, out_money 
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $fromWarehouseId, $goodsId);
			if (! $data) {
				$db->rollback();
				return $this->bad("商品[$goodsCode $goodsName $goodsSpec]库存不足，无法调拨");
			}
			$balanceCount = $data[0]["balance_count"];
			$balancePrice = $data[0]["balance_price"];
			$balanceMoney = $data[0]["balance_money"];
			if ($balanceCount < $goodsCount) {
				$db->rollback();
				return $this->bad("商品[$goodsCode $goodsName $goodsSpec]库存不足，无法调拨");
			}
			$totalOutCount = $data[0]["out_count"];
			$totalOutMoney = $data[0]["out_money"];
			
			// 调出库 - 明细账
			$outPrice = $balancePrice;
			$outCount = $goodsCount;
			$outMoney = $outCount * $outPrice;
			if ($outCount == $balanceCount) {
				// 全部出库，这个时候金额全部转移
				$outMoney = $balanceMoney;
				$balanceCount = 0;
				$balanceMoney = 0;
			} else {
				$balanceCount -= $outCount;
				$balanceMoney -= $outMoney;
			}
			$totalOutCount += $outCount;
			$totalOutMoney += $outMoney;
			$totalOutPrice = $totalOutMoney / $totalOutCount;
			$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id, biz_date, biz_user_id, date_created,
						ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(),
						'%s', '调拨出库')";
			$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, $balancePrice, 
					$balanceMoney, $fromWarehouseId, $goodsId, $bizDT, $bizUserId, $ref);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 调出库 - 总账
			$sql = "update t_inventory
						set out_count = %d, out_price = %f, out_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s'";
			$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, $balanceCount, 
					$balancePrice, $balanceMoney, $fromWarehouseId, $goodsId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 调入库 - 总账
			$inCount = $outCount;
			$inPrice = $outPrice;
			$inMoney = $outMoney;
			$balanceCount = 0;
			$balanceMoney = 0;
			$balancePrice = 0;
			$sql = "select balance_count, balance_money, in_count, in_money from
						t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $toWarehouseId, $goodsId);
			if (! $data) {
				// 在总账中还没有记录
				$balanceCount = $inCount;
				$balanceMoney = $inMoney;
				$balancePrice = $inPrice;
				
				$sql = "insert into t_inventory(in_count, in_price, in_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
						$balanceMoney, $toWarehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCount = $data[0]["balance_count"];
				$balanceMoney = $data[0]["balance_money"];
				$totalInCount = $data[0]["in_count"];
				$totalInMoney = $data[0]["in_money"];
				
				$balanceCount += $inCount;
				$balanceMoney += $inMoney;
				$balancePrice = $balanceMoney / $balanceCount;
				$totalInCount += $inCount;
				$totalInMoney += $inMoney;
				$totalInPrice = $totalInMoney / $totalInCount;
				
				$sql = "update t_inventory
							set in_count = %d, in_price = %f, in_money = %f,
							    balance_count = %d, balance_price = %f, balance_money = %f
							where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $totalInCount, $totalInPrice, $totalInMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $toWarehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 调入库 - 明细账
			$sql = "insert into t_inventory_detail(in_count, in_price, in_money, balance_count, 
						balance_price, balance_money, warehouse_id, goods_id, ref_number, ref_type,
						biz_date, biz_user_id, date_created)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '调拨入库', '%s', '%s', now())";
			$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
					$balanceMoney, $toWarehouseId, $goodsId, $ref, $bizDT, $bizUserId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 修改调拨单单据状态为已调拨
		$sql = "update t_it_bill
					set bill_status = 1000
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$log = "提交调拨单，单号: $ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}