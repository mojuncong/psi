<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\SaleReportService;
use Home\Service\InventoryReportService;
use Home\Service\ReceivablesReportService;
use Home\Service\PayablesReportService;

/**
 * 报表Controller
 *
 * @author 李静波
 *        
 */
class ReportController extends PSIBaseController {

	/**
	 * 销售日报表(按商品汇总)
	 */
	public function saleDayByGoods() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
			$this->initVar();
			
			$this->assign("title", "销售日报表(按商品汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleDayByGoods");
		}
	}

	/**
	 * 销售日报表(按商品汇总) - 查询数据
	 */
	public function saleDayByGoodsQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByGoodsQueryData($params));
		}
	}

	/**
	 * 销售日报表(按商品汇总) - 查询汇总数据
	 */
	public function saleDayByGoodsSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByGoodsSummaryQueryData($params));
		}
	}

	/**
	 * 销售日报表(按客户汇总)
	 */
	public function saleDayByCustomer() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
			$this->initVar();
			
			$this->assign("title", "销售日报表(按客户汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleDayByCustomer");
		}
	}

	/**
	 * 销售日报表(按客户汇总) - 查询数据
	 */
	public function saleDayByCustomerQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByCustomerQueryData($params));
		}
	}

	/**
	 * 销售日报表(按客户汇总) - 查询汇总数据
	 */
	public function saleDayByCustomerSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByCustomerSummaryQueryData($params));
		}
	}

	/**
	 * 销售日报表(按仓库汇总)
	 */
	public function saleDayByWarehouse() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
			$this->initVar();
			
			$this->assign("title", "销售日报表(按仓库汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleDayByWarehouse");
		}
	}

	/**
	 * 销售日报表(按仓库汇总) - 查询数据
	 */
	public function saleDayByWarehouseQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByWarehouseQueryData($params));
		}
	}

	/**
	 * 销售日报表(按仓库汇总) - 查询汇总数据
	 */
	public function saleDayByWarehouseSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByWarehouseSummaryQueryData($params));
		}
	}

	/**
	 * 销售日报表(按业务员汇总)
	 */
	public function saleDayByBizuser() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
			$this->initVar();
			
			$this->assign("title", "销售日报表(按业务员汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleDayByBizuser");
		}
	}

	/**
	 * 销售日报表(按业务员汇总) - 查询数据
	 */
	public function saleDayByBizuserQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByBizuserQueryData($params));
		}
	}

	/**
	 * 销售日报表(按业务员汇总) - 查询汇总数据
	 */
	public function saleDayByBizuserSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"dt" => I("post.dt")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleDayByBizuserSummaryQueryData($params));
		}
	}

	/**
	 * 销售月报表(按商品汇总)
	 */
	public function saleMonthByGoods() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
			$this->initVar();
			
			$this->assign("title", "销售月报表(按商品汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleMonthByGoods");
		}
	}

	/**
	 * 销售月报表(按商品汇总) - 查询数据
	 */
	public function saleMonthByGoodsQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByGoodsQueryData($params));
		}
	}

	/**
	 * 销售月报表(按商品汇总) - 查询汇总数据
	 */
	public function saleMonthByGoodsSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByGoodsSummaryQueryData($params));
		}
	}

	/**
	 * 销售月报表(按客户汇总)
	 */
	public function saleMonthByCustomer() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
			$this->initVar();
			
			$this->assign("title", "销售月报表(按客户汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleMonthByCustomer");
		}
	}

	/**
	 * 销售月报表(按客户汇总) - 查询数据
	 */
	public function saleMonthByCustomerQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByCustomerQueryData($params));
		}
	}

	/**
	 * 销售月报表(按客户汇总) - 查询汇总数据
	 */
	public function saleMonthByCustomerSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByCustomerSummaryQueryData($params));
		}
	}

	/**
	 * 销售月报表(按仓库汇总)
	 */
	public function saleMonthByWarehouse() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
			$this->initVar();
			
			$this->assign("title", "销售月报表(按仓库汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleMonthByWarehouse");
		}
	}

	/**
	 * 销售月报表(按仓库汇总) - 查询数据
	 */
	public function saleMonthByWarehouseQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByWarehouseQueryData($params));
		}
	}

	/**
	 * 销售月报表(按仓库汇总) - 查询汇总数据
	 */
	public function saleMonthByWarehouseSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByWarehouseSummaryQueryData($params));
		}
	}

	/**
	 * 销售月报表(按业务员汇总)
	 */
	public function saleMonthByBizuser() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
			$this->initVar();
			
			$this->assign("title", "销售月报表(按业务员汇总)");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/saleMonthByBizuser");
		}
	}

	/**
	 * 销售月报表(按业务员汇总) - 查询数据
	 */
	public function saleMonthByBizuserQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByBizuserQueryData($params));
		}
	}

	/**
	 * 销售月报表(按业务员汇总) - 查询汇总数据
	 */
	public function saleMonthByBizuserSummaryQueryData() {
		if (IS_POST) {
			$params = array(
					"year" => I("post.year"),
					"month" => I("post.month")
			);
			
			$rs = new SaleReportService();
			
			$this->ajaxReturn($rs->saleMonthByBizuserSummaryQueryData($params));
		}
	}

	/**
	 * 安全库存明细表
	 */
	public function safetyInventory() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
			$this->initVar();
			
			$this->assign("title", "安全库存明细表");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/safetyInventory");
		}
	}

	/**
	 * 安全库存明细表 - 查询数据
	 */
	public function safetyInventoryQueryData() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$is = new InventoryReportService();
			
			$this->ajaxReturn($is->safetyInventoryQueryData($params));
		}
	}

	/**
	 * 应收账款账龄分析表
	 */
	public function receivablesAge() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
			$this->initVar();
			
			$this->assign("title", "应收账款账龄分析表");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/receivablesAge");
		}
	}

	/**
	 * 应收账款账龄分析表 - 数据查询
	 */
	public function receivablesAgeQueryData() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new ReceivablesReportService();
			
			$this->ajaxReturn($rs->receivablesAgeQueryData($params));
		}
	}

	/**
	 * 应收账款账龄分析表 - 当期汇总数据查询
	 */
	public function receivablesSummaryQueryData() {
		if (IS_POST) {
			$rs = new ReceivablesReportService();
			
			$this->ajaxReturn($rs->receivablesSummaryQueryData());
		}
	}

	/**
	 * 应付账款账龄分析表
	 */
	public function payablesAge() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
			$this->initVar();
			
			$this->assign("title", "应付账款账龄分析表");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/payablesAge");
		}
	}

	/**
	 * 应付账款账龄分析表 - 数据查询
	 */
	public function payablesAgeQueryData() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$ps = new PayablesReportService();
			
			$this->ajaxReturn($ps->payablesAgeQueryData($params));
		}
	}

	/**
	 * 应付账款账龄分析表 - 当期汇总数据查询
	 */
	public function payablesSummaryQueryData() {
		if (IS_POST) {
			$ps = new PayablesReportService();
			
			$this->ajaxReturn($ps->payablesSummaryQueryData());
		}
	}

	/**
	 * 库存超上限明细表
	 */
	public function inventoryUpper() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
			$this->initVar();
			
			$this->assign("title", "库存超上限明细表");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Report/inventoryUpper");
		}
	}

	/**
	 * 库存超上限明细表 - 查询数据
	 */
	public function inventoryUpperQueryData() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$is = new InventoryReportService();
			
			$this->ajaxReturn($is->inventoryUpperQueryData($params));
		}
	}
}