<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\WarehouseService;
use Home\Common\FIdConst;

/**
 * 仓库Controller
 *
 * @author 李静波
 *        
 */
class WarehouseController extends PSIBaseController {

	/**
	 * 仓库 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::WAREHOUSE)) {
			$this->initVar();
			
			$this->assign("title", "仓库");
			
			$this->assign("pAdd", $us->hasPermission(FIdConst::WAREHOUSE_ADD) ? 1 : 0);
			$this->assign("pEdit", $us->hasPermission(FIdConst::WAREHOUSE_EDIT) ? 1 : 0);
			$this->assign("pDelete", $us->hasPermission(FIdConst::WAREHOUSE_DELETE) ? 1 : 0);
			$this->assign("pEditDataOrg", 
					$us->hasPermission(FIdConst::WAREHOUSE_EDIT_DATAORG) ? 1 : 0);
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Warehouse/index");
		}
	}

	/**
	 * 仓库列表
	 */
	public function warehouseList() {
		if (IS_POST) {
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->warehouseList());
		}
	}

	/**
	 * 新增或编辑仓库
	 */
	public function editWarehouse() {
		if (IS_POST) {
			$us = new UserService();
			if (I("post.id")) {
				// 编辑仓库
				if (! $us->hasPermission(FIdConst::WAREHOUSE_EDIT)) {
					$this->ajaxReturn($this->noPermission("编辑仓库"));
					return;
				}
			} else {
				// 新增仓库
				if (! $us->hasPermission(FIdConst::WAREHOUSE_ADD)) {
					$this->ajaxReturn($this->noPermission("新增仓库"));
					return;
				}
			}
			
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->editWarehouse($params));
		}
	}

	/**
	 * 删除仓库
	 */
	public function deleteWarehouse() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::WAREHOUSE_DELETE)) {
				$this->ajaxReturn($this->noPermission("删除仓库"));
				return;
			}
			
			$params = array(
					"id" => I("post.id")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->deleteWarehouse($params));
		}
	}

	/**
	 * 仓库自定义字段，查询数据
	 */
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$fid = I("post.fid");
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->queryData($queryKey, $fid));
		}
	}

	/**
	 * 修改数据域
	 */
	public function editDataOrg() {
		if (IS_POST) {
			$us = new UserService();
			if (! $us->hasPermission(FIdConst::WAREHOUSE_EDIT_DATAORG)) {
				$this->ajaxReturn($this->noPermission("修改数据域"));
				return;
			}
			
			$params = array(
					"id" => I("post.id"),
					"dataOrg" => I("post.dataOrg")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->editDataOrg($params));
		}
	}
}