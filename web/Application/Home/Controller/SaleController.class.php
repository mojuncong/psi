<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\WSBillService;
use Home\Service\SRBillService;
use Home\Service\SOBillService;

/**
 * 销售Controller
 *
 * @author 李静波
 *        
 */
class SaleController extends PSIBaseController {

	/**
	 * 销售订单 - 主页面
	 */
	public function soIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::SALE_ORDER)) {
			$this->initVar();
			
			$this->assign("title", "销售订单");
			
			$this->assign("pConfirm", $us->hasPermission(FIdConst::SALE_ORDER_CONFIRM) ? "1" : "0");
			$this->assign("pGenWSBill", 
					$us->hasPermission(FIdConst::SALE_ORDER_GEN_WSBILL) ? "1" : "0");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Sale/soIndex");
		}
	}

	/**
	 * 销售出库 - 主页面
	 */
	public function wsIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
			$this->initVar();
			
			$this->assign("title", "销售出库");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Sale/wsIndex");
		}
	}

	/**
	 * 获得销售出库单的信息
	 */
	public function wsBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"sobillRef" => I("post.sobillRef")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsBillInfo($params));
		}
	}

	/**
	 * 新建或编辑销售出库单
	 */
	public function editWSBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->editWSBill($params));
		}
	}

	/**
	 * 销售出库单主表信息列表
	 */
	public function wsbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"customerId" => I("post.customerId"),
					"receivingType" => I("post.receivingType"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsbillList($params));
		}
	}

	/**
	 * 销售出库单明细信息列表
	 */
	public function wsBillDetailList() {
		if (IS_POST) {
			$params = array(
					"billId" => I("post.billId")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsBillDetailList($params));
		}
	}

	/**
	 * 删除销售出库单
	 */
	public function deleteWSBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->deleteWSBill($params));
		}
	}

	/**
	 * 提交销售出库单
	 */
	public function commitWSBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->commitWSBill($params));
		}
	}

	/**
	 * 销售退货入库 - 主界面
	 */
	public function srIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::SALE_REJECTION)) {
			$this->initVar();
			
			$this->assign("title", "销售退货入库");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Sale/srIndex");
		}
	}

	/**
	 * 销售退货入库单主表信息列表
	 */
	public function srbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"customerId" => I("post.customerId"),
					"paymentType" => I("post.paymentType"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$sr = new SRBillService();
			$this->ajaxReturn($sr->srbillList($params));
		}
	}

	/**
	 * 销售退货入库单明细信息列表
	 */
	public function srBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.billId")
			);
			
			$sr = new SRBillService();
			$this->ajaxReturn($sr->srBillDetailList($params));
		}
	}

	/**
	 * 获得销售退货入库单的信息
	 */
	public function srBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->srBillInfo($params));
		}
	}

	/**
	 * 选择销售出库单
	 */
	public function selectWSBillList() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref"),
					"customerId" => I("post.customerId"),
					"warehouseId" => I("post.warehouseId"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->selectWSBillList($params));
		}
	}

	/**
	 * 新增或者编辑销售退货入库单
	 */
	public function editSRBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->editSRBill($params));
		}
	}

	/**
	 * 查询要退货的销售出库单信息
	 */
	public function getWSBillInfoForSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->getWSBillInfoForSRBill($params));
		}
	}

	/**
	 * 删除销售退货入库单
	 */
	public function deleteSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->deleteSRBill($params));
		}
	}

	/**
	 * 提交销售退货入库单
	 */
	public function commitSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->commitSRBill($params));
		}
	}

	/**
	 * 生成pdf文件
	 */
	public function pdf() {
		$params = array(
				"ref" => I("get.ref")
		);
		
		$ws = new WSBillService();
		$ws->pdf($params);
	}

	/**
	 * 获得销售订单主表信息列表
	 */
	public function sobillList() {
		if (IS_POST) {
			$ps = new SOBillService();
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"customerId" => I("post.customerId"),
					"receivingType" => I("post.receivingType"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$this->ajaxReturn($ps->sobillList($params));
		}
	}

	/**
	 * 获得销售订单的信息
	 */
	public function soBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ps = new SOBillService();
			$this->ajaxReturn($ps->soBillInfo($params));
		}
	}

	/**
	 * 新增或编辑销售订单
	 */
	public function editSOBill() {
		if (IS_POST) {
			$json = I("post.jsonStr");
			$ps = new SOBillService();
			$this->ajaxReturn($ps->editSOBill($json));
		}
	}

	/**
	 * 获得销售订单的明细信息
	 */
	public function soBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ps = new SOBillService();
			$this->ajaxReturn($ps->soBillDetailList($params));
		}
	}

	/**
	 * 删除销售订单
	 */
	public function deleteSOBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ps = new SOBillService();
			$this->ajaxReturn($ps->deleteSOBill($params));
		}
	}

	/**
	 * 审核销售订单
	 */
	public function commitSOBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ps = new SOBillService();
			$this->ajaxReturn($ps->commitSOBill($params));
		}
	}

	/**
	 * 取消销售订单审核
	 */
	public function cancelConfirmSOBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ps = new SOBillService();
			$this->ajaxReturn($ps->cancelConfirmSOBill($params));
		}
	}
}