<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 应收账款Service
 *
 * @author 李静波
 */
class ReceivablesService extends PSIBaseService {
	private $LOG_CATEGORY = "应收账款管理";

	/**
	 * 往来单位分类
	 */
	public function rvCategoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		$result = array();
		$result[0]["id"] = "";
		$result[0]["name"] = "[全部]";
		
		$id = $params["id"];
		if ($id == "customer") {
			$sql = "select id, name from t_customer_category ";
			
			$queryParams = array();
			
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_customer_category");
			if ($rs) {
				$sql .= " where " . $rs[0];
				$queryParams = $rs[1];
			}
			
			$sql .= " order by code";
			$data = $db->query($sql, $queryParams);
			foreach ( $data as $i => $v ) {
				$result[$i + 1]["id"] = $v["id"];
				$result[$i + 1]["name"] = $v["name"];
			}
		} else {
			$sql = "select id, name from t_supplier_category ";
			$queryParams = array();
			
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_supplier_category");
			if ($rs) {
				$sql .= " where " . $rs[0];
				$queryParams = $rs[1];
			}
			$sql .= " order by code";
			
			$data = $db->query($sql, $queryParams);
			foreach ( $data as $i => $v ) {
				$result[$i + 1]["id"] = $v["id"];
				$result[$i + 1]["name"] = $v["name"];
			}
		}
		
		return $result;
	}

	/**
	 * 应收账款列表
	 */
	public function rvList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$caType = $params["caType"];
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		
		if ($caType == "customer") {
			$queryParams = array();
			$sql = "select r.id, r.ca_id, c.code, c.name, r.act_money, r.balance_money, r.rv_money 
					from t_receivables r, t_customer c 
					where (r.ca_type = '%s' and r.ca_id = c.id)";
			$queryParams[] = $caType;
			
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c");
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$sql .= " order by c.code 
					limit %d , %d ";
			$queryParams[] = $start;
			$queryParams[] = $limit;
			$data = $db->query($sql, $queryParams);
			$result = array();
			
			foreach ( $data as $i => $v ) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
				$result[$i]["rvMoney"] = $v["rv_money"];
			}
			
			$queryParams = array();
			$sql = "select count(*) as cnt 
					from t_receivables r, t_customer c 
					where r.ca_type = '%s'  and r.ca_id = c.id";
			$queryParams[] = $caType;
			
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c");
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		} else {
			$queryParams = array();
			$sql = "select r.id, r.ca_id, c.code, c.name, r.act_money, r.balance_money, r.rv_money 
					from t_receivables r, t_supplier c 
					where r.ca_type = '%s' and r.ca_id = c.id ";
			$queryParams[] = $caType;
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c");
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$sql .= " order by c.code 
					limit %d , %d ";
			$queryParams[] = $start;
			$queryParams[] = $limit;
			$data = $db->query($sql, $queryParams);
			$result = array();
			
			foreach ( $data as $i => $v ) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
				$result[$i]["rvMoney"] = $v["rv_money"];
			}
			
			$queryParams = array();
			$sql = "select count(*) as cnt 
					from t_receivables r, t_supplier c 
					where r.ca_type = '%s'  and r.ca_id = c.id";
			$queryParams[] = $caType;
			$ds = new DataOrgService();
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c");
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		}
	}

	/**
	 * 应收账款的明细记录
	 */
	public function rvDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$caType = $params["caType"];
		$caId = $params["caId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select id, rv_money, act_money, balance_money, ref_type, ref_number, date_created, biz_date 
				from t_receivables_detail 
				where ca_type = '%s' and ca_id = '%s'
				order by biz_date desc, date_created desc
				limit %d , %d ";
		$data = $db->query($sql, $caType, $caId, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["refType"] = $v["ref_type"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["bizDT"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["rvMoney"] = $v["rv_money"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}
		
		$sql = "select count(*) as cnt 
				from t_receivables_detail 
				where ca_type = '%s' and ca_id = '%s' ";
		$data = $db->query($sql, $caType, $caId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 应收账款的收款记录
	 */
	public function rvRecordList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$refType = $params["refType"];
		$refNumber = $params["refNumber"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select r.id, r.act_money, r.biz_date, r.date_created, r.remark, u.name as rv_user_name,
				user.name as input_user_name 
				from t_receiving r, t_user u, t_user user 
				where r.rv_user_id = u.id and r.input_user_id = user.id 
				  and r.ref_type = '%s' and r.ref_number = '%s'
				order by r.date_created desc
				limit %d , %d ";
		$data = $db->query($sql, $refType, $refNumber, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["bizUserName"] = $v["rv_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["remark"] = $v["remark"];
		}
		
		$sql = "select count(*) as cnt 
				from t_receiving 
				where ref_type = '%s' and ref_number = '%s' ";
		$data = $db->query($sql, $refType, $refNumber);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 收款记录
	 */
	public function addRvRecord($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$refType = $params["refType"];
		$refNumber = $params["refNumber"];
		$bizDT = $params["bizDT"];
		$actMoney = $params["actMoney"];
		$bizUserId = $params["bizUserId"];
		$remark = $params["remark"];
		if (! $remark) {
			$remark = "";
		}
		
		$db = M();
		$db->startTrans();
		
		$billId = "";
		if ($refType == "销售出库") {
			$sql = "select id from t_ws_bill where ref = '%s' ";
			$data = $db->query($sql, $refNumber);
			if (! $data) {
				$db->rollback();
				return $this->bad("单号为 [{$refNumber}] 的销售出库单不存在，无法录入收款记录");
			}
			
			$billId = $data[0]["id"];
		}
		
		// 检查收款人是否存在
		$sql = "select count(*) as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			$db->rollback();
			return $this->bad("收款人不存在，无法收款");
		}
		
		if (! $this->dateIsValid($bizDT)) {
			$db->rollback();
			return $this->bad("收款日期不正确");
		}
		
		$sql = "insert into t_receiving (id, act_money, biz_date, date_created, input_user_id,
					rv_user_id, remark, ref_number, ref_type, bill_id, data_org, company_id) 
					values ('%s', %f, '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
		$idGen = new IdGenService();
		$us = new UserService();
		
		$companyId = $us->getCompanyId();
		$dataOrg = $us->getLoginUserDataOrg();
		
		$rc = $db->execute($sql, $idGen->newId(), $actMoney, $bizDT, $us->getLoginUserId(), 
				$bizUserId, $remark, $refNumber, $refType, $billId, $dataOrg, $companyId);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "为 {$refType} - 单号：{$refNumber} 收款：{$actMoney}元";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		// 应收明细账
		$sql = "select ca_id, ca_type, act_money, balance_money, company_id 
					from t_receivables_detail 
					where ref_number = '%s' and ref_type = '%s' ";
		$data = $db->query($sql, $refNumber, $refType);
		if (! $data) {
			$db->rollback();
			return $this->bad("数据库错误，没有应收明细对应，无法收款");
		}
		$caId = $data[0]["ca_id"];
		$caType = $data[0]["ca_type"];
		$companyId = $data[0]["company_id"];
		$actMoneyDetail = $data[0]["act_money"];
		$balanceMoneyDetail = $data[0]["balance_money"];
		$actMoneyDetail += $actMoney;
		$balanceMoneyDetail -= $actMoney;
		$sql = "update t_receivables_detail 
					set act_money = %f, balance_money = %f 
					where ref_number = '%s' and ref_type = '%s' 
					  and ca_id = '%s' and ca_type = '%s' ";
		$rc = $db->execute($sql, $actMoneyDetail, $balanceMoneyDetail, $refNumber, $refType, $caId, 
				$caType);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 应收总账
		$sql = "select sum(rv_money) as sum_rv_money, sum(act_money) as sum_act_money
					from t_receivables_detail
					where ca_id = '%s' and ca_type = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $caId, $caType, $companyId);
		$sumRvMoney = $data[0]["sum_rv_money"];
		if (! $sumRvMoney) {
			$sumRvMoney = 0;
		}
		$sumActMoney = $data[0]["sum_act_money"];
		if (! $sumActMoney) {
			$sumActMoney = 0;
		}
		$sumBalanceMoney = $sumRvMoney - $sumActMoney;
		
		$sql = "update t_receivables 
					set act_money = %f, balance_money = %f 
					where ca_id = '%s' and ca_type = '%s' and company_id = '%s' ";
		$rc = $db->execute($sql, $sumActMoney, $sumBalanceMoney, $caId, $caType, $companyId);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$db->commit();
		
		return $this->ok();
	}

	public function refreshRvInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		$sql = "select act_money, balance_money from t_receivables where id = '%s' ";
		$data = M()->query($sql, $id);
		if (! $data) {
			return array();
		} else {
			return array(
					"actMoney" => $data[0]["act_money"],
					"balanceMoney" => $data[0]["balance_money"]
			);
		}
	}

	public function refreshRvDetailInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		$sql = "select act_money, balance_money from t_receivables_detail where id = '%s' ";
		$data = M()->query($sql, $id);
		if (! $data) {
			return array();
		} else {
			return array(
					"actMoney" => $data[0]["act_money"],
					"balanceMoney" => $data[0]["balance_money"]
			);
		}
	}
}