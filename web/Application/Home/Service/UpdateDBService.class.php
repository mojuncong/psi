<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 数据库升级Service
 *
 * @author 李静波
 */
class UpdateDBService extends PSIBaseService {

	public function updateDatabase() {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		// 检查t_psi_db_version是否存在
		if (! $this->tableExists($db, "t_psi_db_version")) {
			return $this->bad("表t_psi_db_db_version不存在，数据库结构实在是太久远了，无法升级");
		}
		
		// 检查t_psi_db_version中的版本号
		$sql = "select db_version from t_psi_db_version";
		$data = $db->query($sql);
		$dbVersion = $data[0]["db_version"];
		if ($dbVersion == $this->CURRENT_DB_VERSION) {
			return $this->bad("当前数据库是最新版本，不用升级");
		}
		
		$this->t_cash($db);
		$this->t_cash_detail($db);
		$this->t_config($db);
		$this->t_customer($db);
		$this->t_fid($db);
		$this->t_goods($db);
		$this->t_goods_category($db);
		$this->t_goods_si($db);
		$this->t_menu_item($db);
		$this->t_permission($db);
		$this->t_po_bill($db);
		$this->t_po_bill_detail($db);
		$this->t_po_pw($db);
		$this->t_pr_bill($db);
		$this->t_pre_payment($db);
		$this->t_pre_payment_detail($db);
		$this->t_pre_receiving($db);
		$this->t_pre_receiving_detail($db);
		$this->t_pw_bill($db);
		$this->t_role_permission($db);
		$this->t_supplier($db);
		$this->t_supplier_category($db);
		$this->t_sr_bill($db);
		$this->t_sr_bill_detail($db);
		$this->t_ws_bill($db);
		$this->t_ws_bill_detail($db);
		
		$this->update_20151016_01($db);
		$this->update_20151031_01($db);
		$this->update_20151102_01($db);
		$this->update_20151105_01($db);
		$this->update_20151106_01($db);
		$this->update_20151106_02($db);
		$this->update_20151108_01($db);
		$this->update_20151110_01($db);
		$this->update_20151110_02($db);
		$this->update_20151111_01($db);
		$this->update_20151112_01($db);
		$this->update_20151113_01($db);
		$this->update_20151119_01($db);
		$this->update_20151119_03($db);
		$this->update_20151121_01($db);
		$this->update_20151123_01($db);
		$this->update_20151123_02($db);
		$this->update_20151123_03($db);
		$this->update_20151124_01($db);
		$this->update_20151126_01($db);
		$this->update_20151127_01($db);
		$this->update_20151128_01($db);
		$this->update_20151128_02($db);
		$this->update_20151128_03($db);
		$this->update_20151210_01($db);
		$this->update_20160105_01($db);
		$this->update_20160105_02($db);
		$this->update_20160108_01($db);
		$this->update_20160112_01($db);
		$this->update_20160116_01($db);
		$this->update_20160116_02($db);
		$this->update_20160118_01($db);
		$this->update_20160119_01($db);
		$this->update_20160120_01($db);
		$this->update_20160219_01($db);
		$this->update_20160301_01($db);
		$this->update_20160303_01($db);
		$this->update_20160314_01($db);
		
		$sql = "delete from t_psi_db_version";
		$db->execute($sql);
		$sql = "insert into t_psi_db_version (db_version, update_dt) 
				values ('%s', now())";
		$db->execute($sql, $this->CURRENT_DB_VERSION);
		
		$bl = new BizlogService();
		$bl->insertBizlog("升级数据库，数据库版本 = " . $this->CURRENT_DB_VERSION);
		
		return $this->ok();
	}

	private function update_20160314_01($db) {
		// 本次更新：新增表 t_goods_bom
		$tableName = "t_goods_bom";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_bom` (
					  `id` varchar(255) NOT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `sub_goods_id` varchar(255) NOT NULL,
					  `parent_id` varchar(255) DEFAULT NULL,
					  `sub_goods_count` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20160303_01($db) {
		// 本次更新：调整菜单；新增模块：基础数据-商品品牌
		
		// 调整菜单
		$sql = "update t_menu_item
				set fid = null
				where id = '0801' ";
		$db->execute($sql);
		
		$sql = "select count(*) as cnt 
				from t_menu_item 
				where id = '080101' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item (id, caption, fid, parent_id, show_order)
				values ('080101', '商品', '1001', '0801', 1)";
			$db->execute($sql);
		}
		
		$sql = "update t_menu_item
				set parent_id = '0801', id = '080102'
				where id = '0802' ";
		$db->execute($sql);
		
		// 新增模块：基础数据-商品品牌
		$fid = FIdConst::GOODS_BRAND;
		$name = "商品品牌";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$category = "商品";
			$ps = new PinyinService();
			$py = $ps->toPY($name);
			$sql = "insert into t_permission(id, fid, name, note, category, py)
					value('%s', '%s', '%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name, $category, $py);
		}
		
		$sql = "select count(*) as cnt from t_menu_item
				where id = '080103' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('080103', '%s', '%s', '0801', 3)";
			$db->execute($sql, $name, $fid);
		}
	}

	private function update_20160301_01($db) {
		// 本次更新：新增表t_goods_brand; t_goods新增字段 brand_id
		$tableName = "t_goods_brand";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_brand` (
					  `id` varchar(255) NOT NULL,
					  `name` varchar(255) NOT NULL,
					  `parent_id` varchar(255) DEFAULT NULL,
					  `full_name` varchar(1000) DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_goods";
		$columnName = "brand_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20160219_01($db) {
		// 本次更新：销售订单新增审核和生成销售出库单的权限
		$ps = new PinyinService();
		$category = "销售订单";
		
		$fid = FIdConst::SALE_ORDER_CONFIRM;
		$name = "销售订单 - 审核/取消审核";
		$note = "销售订单 - 审核/取消审核";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		$fid = FIdConst::SALE_ORDER_GEN_WSBILL;
		$name = "销售订单 - 生成销售出库单";
		$note = "销售订单 - 生成销售出库单";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160120_01($db) {
		// 本次更新：细化客户资料的权限到按钮级别
		$fid = FIdConst::CUSTOMER;
		$category = "客户管理";
		$note = "通过菜单进入客户资料模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增客户分类";
			$note = "客户资料模块[新增客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑客户分类";
			$note = "客户资料模块[编辑客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除客户分类
		$fid = FIdConst::CUSTOMER_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除客户分类";
			$note = "客户资料模块[删除客户分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增客户
		$fid = FIdConst::CUSTOMER_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增客户";
			$note = "客户资料模块[新增客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑客户
		$fid = FIdConst::CUSTOMER_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑客户";
			$note = "客户资料模块[编辑客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除客户
		$fid = FIdConst::CUSTOMER_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除客户";
			$note = "客户资料模块[删除客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 导入客户
		$fid = FIdConst::CUSTOMER_IMPORT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "导入客户";
			$note = "客户资料模块[导入客户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160119_01($db) {
		// 本次更新：细化基础数据供应商的权限到按钮级别
		$fid = "1004";
		$category = "供应商管理";
		$note = "通过菜单进入基础数据供应商档案模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增供应商分类";
			$note = "基础数据供应商档案模块[新增供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑供应商分类";
			$note = "基础数据供应商档案模块[编辑供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除供应商分类
		$fid = FIdConst::SUPPLIER_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除供应商分类";
			$note = "基础数据供应商档案模块[删除供应商分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增供应商
		$fid = FIdConst::SUPPLIER_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增供应商";
			$note = "基础数据供应商档案模块[新增供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑供应商
		$fid = FIdConst::SUPPLIER_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑供应商";
			$note = "基础数据供应商档案模块[编辑供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除供应商
		$fid = FIdConst::SUPPLIER_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除供应商";
			$note = "基础数据供应商档案模块[删除供应商]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160118_01($db) {
		// 本次更新：细化基础数据商品的权限到按钮级别
		$fid = "1001";
		$category = "商品";
		$note = "通过菜单进入基础数据商品模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增商品分类
		$fid = FIdConst::GOODS_CATEGORY_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增商品分类";
			$note = "基础数据商品模块[新增商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑商品分类
		$fid = FIdConst::GOODS_CATEGORY_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑商品分类";
			$note = "基础数据商品模块[编辑商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除商品分类
		$fid = FIdConst::GOODS_CATEGORY_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除商品分类";
			$note = "基础数据商品模块[删除商品分类]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增商品
		$fid = FIdConst::GOODS_ADD;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增商品";
			$note = "基础数据商品模块[新增商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑商品
		$fid = FIdConst::GOODS_EDIT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑商品";
			$note = "基础数据商品模块[编辑商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除商品
		$fid = FIdConst::GOODS_DELETE;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除商品";
			$note = "基础数据商品模块[删除商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 导入商品
		$fid = FIdConst::GOODS_IMPORT;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "导入商品";
			$note = "基础数据商品模块[导入商品]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 设置商品安全库存
		$fid = FIdConst::GOODS_SI;
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "设置商品安全库存";
			$note = "基础数据商品模块[设置商品安全库存]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160116_02($db) {
		// 本次更新：细化基础数据仓库的权限到按钮级别
		$fid = "1003";
		$category = "仓库";
		$note = "通过菜单进入基础数据仓库模块的权限";
		$sql = "update t_permission
				set note = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $fid);
		
		$ps = new PinyinService();
		
		// 新增仓库
		$fid = "1003-02";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "新增仓库";
			$note = "基础数据仓库模块[新增仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑仓库
		$fid = "1003-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "编辑仓库";
			$note = "基础数据仓库模块[编辑仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除仓库
		$fid = "1003-04";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "删除仓库";
			$note = "基础数据仓库模块[删除仓库]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 修改仓库数据域
		$fid = "1003-05";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "修改仓库数据域";
			$note = "基础数据仓库模块[修改仓库数据域]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160116_01($db) {
		// 本次更新：细化用户管理模块的权限到按钮级别
		$fid = "-8999";
		$category = "用户管理";
		$note = "通过菜单进入用户管理模块的权限";
		$sql = "update t_permission
				set note = '%s',
					category = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $category, $fid);
		
		$sql = "update t_permission
				set category = '%s'
				where id in( '-8999-01', '-8999-02' ) ";
		$db->execute($sql, $category);
		
		$ps = new PinyinService();
		
		// 新增组织机构
		$fid = "-8999-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-新增组织机构";
			$note = "用户管理模块[新增组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑组织机构
		$fid = "-8999-04";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-编辑组织机构";
			$note = "用户管理模块[编辑组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除组织机构
		$fid = "-8999-05";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-删除组织机构";
			$note = "用户管理模块[删除组织机构]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 新增用户
		$fid = "-8999-06";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-新增用户";
			$note = "用户管理模块[新增用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑用户
		$fid = "-8999-07";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-编辑用户";
			$note = "用户管理模块[编辑用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除用户
		$fid = "-8999-08";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-删除用户";
			$note = "用户管理模块[删除用户]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 修改用户密码
		$fid = "-8999-09";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "用户管理-修改用户密码";
			$note = "用户管理模块[修改用户密码]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160112_01($db) {
		// 本次更新： 细化权限管理模块的权限到按钮级别
		$fid = "-8996";
		$category = "权限管理";
		$note = "通过菜单进入权限管理模块的权限";
		$sql = "update t_permission
				set note = '%s',
					category = '%s'
				where id = '%s' ";
		$db->execute($sql, $note, $category, $fid);
		
		$ps = new PinyinService();
		
		// 新增角色
		$fid = "-8996-01";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-新增角色";
			$note = "权限管理模块[新增角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 编辑角色
		$fid = "-8996-02";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-编辑角色";
			$note = "权限管理模块[编辑角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
		
		// 删除角色
		$fid = "-8996-03";
		$sql = "select count(*) as cnt from t_permission
				where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$name = "权限管理-删除角色";
			$note = "权限管理模块[删除角色]按钮的权限";
			
			$py = $ps->toPY($name);
			
			$sql = "insert into t_permission (id, fid, name, note, category, py)
				values ('%s', '%s', '%s', '%s', '%s', '%s') ";
			$db->execute($sql, $fid, $fid, $name, $note, $category, $py);
		}
	}

	private function update_20160108_01($db) {
		// 本次更新：t_permission新增字段 category、py
		$tableName = "t_permission";
		$columnName = "category";
		
		$updateData = false;
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
			
			$updateData = true;
		}
		
		$columnName = "py";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
			
			$updateData = true;
		}
		
		if (! $updateData) {
			return;
		}
		
		// 更新t_permission数据
		$ps = new PinyinService();
		$sql = "select id, name from t_permission";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$id = $v["id"];
			$name = $v["name"];
			$sql = "update t_permission
					set py = '%s'
					where id = '%s' ";
			$db->execute($sql, $ps->toPY($name), $id);
		}
		
		// 权限分类：系统管理
		$sql = "update t_permission
				set category = '系统管理' 
				where id in ('-8996', '-8997', '-8999', '-8999-01', 
					'-8999-02', '2008')";
		$db->execute($sql);
		
		// 权限分类：商品
		$sql = "update t_permission
				set category = '商品' 
				where id in ('1001', '1001-01', '1001-02', '1002')";
		$db->execute($sql);
		
		// 权限分类：仓库
		$sql = "update t_permission
				set category = '仓库' 
				where id in ('1003', '1003-01')";
		$db->execute($sql);
		
		// 权限分类： 供应商管理
		$sql = "update t_permission
				set category = '供应商管理'
				where id in ('1004', '1004-01', '1004-02')";
		$db->execute($sql);
		
		// 权限分类：客户管理
		$sql = "update t_permission
				set category = '客户管理'
				where id in ('1007', '1007-01', '1007-02')";
		$db->execute($sql);
		
		// 权限分类：库存建账
		$sql = "update t_permission
				set category = '库存建账'
				where id in ('2000')";
		$db->execute($sql);
		
		// 权限分类：采购入库
		$sql = "update t_permission
				set category = '采购入库'
				where id in ('2001')";
		$db->execute($sql);
		
		// 权限分类：销售出库
		$sql = "update t_permission
				set category = '销售出库'
				where id in ('2002', '2002-01')";
		$db->execute($sql);
		
		// 权限分类：库存账查询
		$sql = "update t_permission
				set category = '库存账查询'
				where id in ('2003')";
		$db->execute($sql);
		
		// 权限分类：应收账款管理
		$sql = "update t_permission
				set category = '应收账款管理'
				where id in ('2004')";
		$db->execute($sql);
		
		// 权限分类：应付账款管理
		$sql = "update t_permission
				set category = '应付账款管理'
				where id in ('2005')";
		$db->execute($sql);
		
		// 权限分类：销售退货入库
		$sql = "update t_permission
				set category = '销售退货入库'
				where id in ('2006')";
		$db->execute($sql);
		
		// 权限分类：采购退货出库
		$sql = "update t_permission
				set category = '采购退货出库'
				where id in ('2007')";
		$db->execute($sql);
		
		// 权限分类：库间调拨
		$sql = "update t_permission
				set category = '库间调拨'
				where id in ('2009')";
		$db->execute($sql);
		
		// 权限分类：库存盘点
		$sql = "update t_permission
				set category = '库存盘点'
				where id in ('2010')";
		$db->execute($sql);
		
		// 权限分类：首页看板
		$sql = "update t_permission
				set category = '首页看板'
				where id in ('2011-01', '2011-02', '2011-03', '2011-04')";
		$db->execute($sql);
		
		// 权限分类：销售日报表
		$sql = "update t_permission
				set category = '销售日报表'
				where id in ('2012', '2013', '2014', '2015')";
		$db->execute($sql);
		
		// 权限分类：销售月报表
		$sql = "update t_permission
				set category = '销售月报表'
				where id in ('2016', '2017', '2018', '2019')";
		$db->execute($sql);
		
		// 权限分类：库存报表
		$sql = "update t_permission
				set category = '库存报表'
				where id in ('2020', '2023')";
		$db->execute($sql);
		
		// 权限分类：资金报表
		$sql = "update t_permission
				set category = '资金报表'
				where id in ('2021', '2022')";
		$db->execute($sql);
		
		// 权限分类：现金管理
		$sql = "update t_permission
				set category = '现金管理'
				where id in ('2024')";
		$db->execute($sql);
		
		// 权限分类：预收款管理
		$sql = "update t_permission
				set category = '预收款管理'
				where id in ('2025')";
		$db->execute($sql);
		
		// 权限分类：预付款管理
		$sql = "update t_permission
				set category = '预付款管理'
				where id in ('2026')";
		$db->execute($sql);
		
		// 权限分类：采购订单
		$sql = "update t_permission
				set category = '采购订单'
				where id in ('2027', '2027-01', '2027-02')";
		$db->execute($sql);
		
		// 权限分类：销售订单
		$sql = "update t_permission
				set category = '销售订单'
				where id in ('2028')";
		$db->execute($sql);
	}

	private function update_20160105_02($db) {
		// 本次更新：新增模块销售订单
		$fid = FIdConst::SALE_ORDER;
		$name = "销售订单";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0400' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0400', '%s', '%s', '04', 0)";
			$db->execute($sql, $name, $fid);
		}
	}

	private function update_20160105_01($db) {
		// 本次更新：新增采购订单表
		$tableName = "t_so_bill";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_bill` (
					  `id` varchar(255) NOT NULL,
					  `bill_status` int(11) NOT NULL,
					  `biz_dt` datetime NOT NULL,
					  `deal_date` datetime NOT NULL,
					  `org_id` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  `ref` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `contact` varchar(255) NOT NULL,
					  `tel` varchar(255) DEFAULT NULL,
					  `fax` varchar(255) DEFAULT NULL,
					  `deal_address` varchar(255) DEFAULT NULL,
					  `bill_memo` varchar(255) DEFAULT NULL,
					  `receiving_type` int(11) NOT NULL DEFAULT 0,
					  `confirm_user_id` varchar(255) DEFAULT NULL,
					  `confirm_date` datetime DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_so_bill_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_bill_detail` (
					  `id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `goods_count` int(11) NOT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `goods_price` decimal(19,2) NOT NULL,
					  `sobill_id` varchar(255) NOT NULL,
					  `tax_rate` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `ws_count` int(11) NOT NULL,
					  `left_count` int(11) NOT NULL,
					  `show_order` int(11) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  `company_id` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_so_ws";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_so_ws` (
					  `so_id` varchar(255) NOT NULL,
					  `ws_id` varchar(255) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function update_20151210_01($db) {
		// 本次更新： t_goods新增字段spec_py
		$tableName = "t_goods";
		$columnName = "spec_py";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151128_03($db) {
		// 本次更新：表新增company_id字段
		$tables = array(
				"t_biz_log",
				"t_role",
				"t_user",
				"t_warehouse",
				"t_supplier",
				"t_supplier_category",
				"t_goods",
				"t_goods_category",
				"t_goods_unit",
				"t_customer",
				"t_customer_category",
				"t_inventory",
				"t_inventory_detail",
				"t_pw_bill_detail",
				"t_payment",
				"t_ws_bill_detail",
				"t_receiving",
				"t_sr_bill_detail",
				"t_it_bill_detail",
				"t_ic_bill_detail",
				"t_pr_bill_detail",
				"t_config",
				"t_goods_si",
				"t_po_bill_detail"
		);
		$columnName = "company_id";
		foreach ( $tables as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
	}

	private function update_20151128_02($db) {
		// 本次更新：新增商品分类权限
		$fid = "1001-02";
		$name = "商品分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151128_01($db) {
		// 本次更新：新增供应商分类权限
		$fid = "1004-02";
		$name = "供应商分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151127_01($db) {
		// 本次更新：新增客户分类权限
		$fid = "1007-02";
		$name = "客户分类";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) value('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note) 
					value('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151126_01($db) {
		// 本次更新：销售出库单新增备注字段
		$tableName = "t_ws_bill";
		$columnName = "memo";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_ws_bill_detail";
		$columnName = "memo";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151124_01($db) {
		// 本次更新：调拨单、盘点单新增company_id字段
		$tableName = "t_it_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_ic_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_03($db) {
		// 本次更新：销售退货入库单新增company_id字段
		$tableName = "t_sr_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_02($db) {
		// 本次更新：销售出库单新增company_id字段
		$tableName = "t_ws_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151123_01($db) {
		// 本次更新： 采购退货出库单新增company_id字段
		$tableName = "t_pr_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151121_01($db) {
		// 本次更新：采购入库单主表新增company_id字段
		$tableName = "t_pw_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151119_03($db) {
		// 本次更新： 采购订单主表增加 company_id 字段
		$tableName = "t_po_bill";
		$columnName = "company_id";
		
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151119_01($db) {
		// 本次更新：和资金相关的表增加 company_id 字段
		$tableList = array(
				"t_cash",
				"t_cash_detail",
				"t_payables",
				"t_payables_detail",
				"t_pre_payment",
				"t_pre_payment_detail",
				"t_pre_receiving",
				"t_pre_receiving_detail",
				"t_receivables",
				"t_receivables_detail"
		);
		
		$columnName = "company_id";
		
		foreach ( $tableList as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
	}

	private function update_20151113_01($db) {
		// 本次更新：t_pw_bill_detail表新增memo字段
		$tableName = "t_pw_bill_detail";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151112_01($db) {
		// 本次更新：t_biz_log表增加ip_from字段
		$tableName = "t_biz_log";
		$columnName = "ip_from";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151111_01($db) {
		// 本次更新：t_config表：单号前缀自定义
		$id = "9003-01";
		$name = "采购订单单号前缀";
		$value = "PO";
		$showOrder = 601;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-02";
		$name = "采购入库单单号前缀";
		$value = "PW";
		$showOrder = 602;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-03";
		$name = "采购退货出库单单号前缀";
		$value = "PR";
		$showOrder = 603;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-04";
		$name = "销售出库单单号前缀";
		$value = "WS";
		$showOrder = 604;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-05";
		$name = "销售退货入库单单号前缀";
		$value = "SR";
		$showOrder = 605;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-06";
		$name = "调拨单单号前缀";
		$value = "IT";
		$showOrder = 606;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
		
		$id = "9003-07";
		$name = "盘点单单号前缀";
		$value = "IC";
		$showOrder = 607;
		$sql = "select count(*) as cnt from t_config where id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('%s', '%s', '%s', '', %d)";
			$db->execute($sql, $id, $name, $value, $showOrder);
		}
	}

	private function update_20151110_02($db) {
		// 本次更新：t_inventory_fifo_detail表增加wsbilldetail_id字段
		$tableName = "t_inventory_fifo_detail";
		$columnName = "wsbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151110_01($db) {
		// 本次更新： t_inventory_fifo、 t_inventory_fifo_detail表增加字段 pwbilldetail_id
		$tableName = "t_inventory_fifo";
		$columnName = "pwbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_inventory_fifo_detail";
		$columnName = "pwbilldetail_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151108_01($db) {
		// 本次更新：基础数据在业务单据中的使用权限
		$fid = "-8999-01";
		$name = "组织机构在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "-8999-02";
		$name = "业务员在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1001-01";
		$name = "商品在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1003-01";
		$name = "仓库在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1004-01";
		$name = "供应商档案在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "1007-01";
		$name = "客户资料在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_fid where fid = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('%s', '%s')";
			$db->execute($sql, $fid, $name);
		}
		
		$fid = "-8999-01";
		$name = "组织机构在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "-8999-02";
		$name = "业务员在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1001-01";
		$name = "商品在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1003-01";
		$name = "仓库在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1004-01";
		$name = "供应商档案在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
		
		$fid = "1007-01";
		$name = "客户资料在业务单据中的使用权限";
		$sql = "select count(*) as cnt from t_permission where id = '%s' ";
		$data = $db->query($sql, $fid);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $fid, $fid, $name, $name);
		}
	}

	private function update_20151106_02($db) {
		// 本次更新：业务设置去掉仓库设置组织结构；增加存货计价方法
		$sql = "delete from t_config where id = '1003-01' ";
		$db->execute($sql);
		
		$sql = "select count(*) as cnt from t_config where id = '1003-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config(id, name, value, note, show_order)
					values ('1003-02', '存货计价方法', '0', '', 401)";
			$db->execute($sql);
		}
	}

	private function update_20151106_01($db) {
		// 本次更新：先进先出，新增数据库表
		$tableName = "t_inventory_fifo";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_inventory_fifo` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `balance_count` decimal(19,2) NOT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `balance_price` decimal(19,2) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `in_count` decimal(19,2) DEFAULT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `in_price` decimal(19,2) DEFAULT NULL,
					  `out_count` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `out_price` decimal(19,2) DEFAULT NULL,
					  `in_ref` varchar(255) DEFAULT NULL,
					  `in_ref_type` varchar(255) NOT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					";
			$db->execute($sql);
		}
		
		$tableName = "t_inventory_fifo_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_inventory_fifo_detail` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `balance_count` decimal(19,2) NOT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `balance_price` decimal(19,2) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `in_count` decimal(19,2) DEFAULT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `in_price` decimal(19,2) DEFAULT NULL,
					  `out_count` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `out_price` decimal(19,2) DEFAULT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `data_org` varchar(255) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
					";
			$db->execute($sql);
		}
	}

	private function update_20151105_01($db) {
		// 本次更新： 在途库存、 商品多级分类
		$tableName = "t_inventory";
		$columnName = "afloat_count";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		$columnName = "afloat_money";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		$columnName = "afloat_price";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
		
		$tableName = "t_goods_category";
		$columnName = "full_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(1000) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151102_01($db) {
		// 本次更新：新增表 t_role_permission_dataorg
		$tableName = "t_role_permission_dataorg";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_role_permission_dataorg` (
					  `role_id` varchar(255) DEFAULT NULL,
					  `permission_id` varchar(255) DEFAULT NULL,
					  `data_org` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function update_20151031_01($db) {
		// 本次更新：商品 增加备注字段
		$tableName = "t_goods";
		$columnName = "memo";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(500) default null;";
			$db->execute($sql);
		}
	}

	private function update_20151016_01($db) {
		// 本次更新：表结构增加data_org字段
		$tables = array(
				"t_biz_log",
				"t_org",
				"t_role",
				"t_role_permission",
				"t_user",
				"t_warehouse",
				"t_warehouse_org",
				"t_supplier",
				"t_supplier_category",
				"t_goods",
				"t_goods_category",
				"t_goods_unit",
				"t_customer",
				"t_customer_category",
				"t_inventory",
				"t_inventory_detail",
				"t_pw_bill",
				"t_pw_bill_detail",
				"t_payables",
				"t_payables_detail",
				"t_receivables",
				"t_receivables_detail",
				"t_payment",
				"t_ws_bill",
				"t_ws_bill_detail",
				"t_receiving",
				"t_sr_bill",
				"t_sr_bill_detail",
				"t_it_bill",
				"t_it_bill_detail",
				"t_ic_bill",
				"t_ic_bill_detail",
				"t_pr_bill",
				"t_pr_bill_detail",
				"t_goods_si",
				"t_cash",
				"t_cash_detail",
				"t_pre_receiving",
				"t_pre_receiving_detail",
				"t_pre_payment",
				"t_pre_payment_detail",
				"t_po_bill",
				"t_po_bill_detail"
		);
		
		$columnName = "data_org";
		foreach ( $tables as $tableName ) {
			if (! $this->tableExists($db, $tableName)) {
				continue;
			}
			
			if (! $this->columnExists($db, $tableName, $columnName)) {
				$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
				$db->execute($sql);
			}
		}
	}

	private function t_cash($db) {
		$tableName = "t_cash";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_cash` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `biz_date` datetime NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function t_cash_detail($db) {
		$tableName = "t_cash_detail";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_cash_detail` (
					  `id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `biz_date` datetime NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  `date_created` datetime NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
			return;
		}
	}

	private function t_config($db) {
		$tableName = "t_config";
		
		$columnName = "show_order";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) default null;";
			$db->execute($sql);
			
			$sql = "delete from t_config";
			$db->execute($sql);
		}
		
		// 移走商品双单位
		$sql = "delete from t_config where id = '1001-01'";
		$db->execute($sql);
		
		// 9000-01
		$sql = "select count(*) as cnt from t_config where id = '9000-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-01', '公司名称', '', '', 100)";
			$db->execute($sql);
		}
		
		// 9000-02
		$sql = "select count(*) as cnt from t_config where id = '9000-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-02', '公司地址', '', '', 101)";
			$db->execute($sql);
		}
		
		// 9000-03
		$sql = "select count(*) as cnt from t_config where id = '9000-03' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-03', '公司电话', '', '', 102)";
			$db->execute($sql);
		}
		
		// 9000-04
		$sql = "select count(*) as cnt from t_config where id = '9000-04' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-04', '公司传真', '', '', 103)";
			$db->execute($sql);
		}
		
		// 9000-05
		$sql = "select count(*) as cnt from t_config where id = '9000-05' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9000-05', '公司邮编', '', '', 104)";
			$db->execute($sql);
		}
		
		// 2001-01
		$sql = "select count(*) as cnt from t_config where id = '2001-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2001-01', '采购入库默认仓库', '', '', 200)";
			$db->execute($sql);
		}
		
		// 2002-02
		$sql = "select count(*) as cnt from t_config where id = '2002-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2002-02', '销售出库默认仓库', '', '', 300)";
			$db->execute($sql);
		}
		
		// 2002-01
		$sql = "select count(*) as cnt from t_config where id = '2002-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('2002-01', '销售出库单允许编辑销售单价', '0', '当允许编辑的时候，还需要给用户赋予权限[销售出库单允许编辑销售单价]', 301)";
			$db->execute($sql);
		}
		
		// 1003-01
		$sql = "select count(*) as cnt from t_config where id = '1003-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('1003-01', '仓库需指定组织机构', '0', '当仓库需要指定组织机构的时候，就意味着可以控制仓库的使用人', 401)";
			$db->execute($sql);
		}
		
		// 9001-01
		$sql = "select count(*) as cnt from t_config where id = '9001-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9001-01', '增值税税率', '17', '', 501)";
			$db->execute($sql);
		}
		
		// 9002-01
		$sql = "select count(*) as cnt from t_config where id = '9002-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_config (id, name, value, note, show_order)
					values ('9002-01', '产品名称', '开源进销存PSI', '', 0)";
			$db->execute($sql);
		}
	}

	private function t_customer($db) {
		$tableName = "t_customer";
		
		$columnName = "address";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_shipping";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_receipt";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_account";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "tax_number";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "fax";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_goods($db) {
		$tableName = "t_goods";
		
		$columnName = "bar_code";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_goods_category($db) {
		$tableName = "t_goods_category";
		
		$columnName = "parent_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_fid($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_fid where fid = '2024' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2024', '现金收支查询')";
			$db->execute($sql);
		}
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_fid where fid = '2025' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2025', '预收款管理')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_fid where fid = '2026' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2026', '预付款管理')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_fid where fid = '2027' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027', '采购订单')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核
		$sql = "select count(*) as cnt from t_fid where fid = '2027-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027-01', '采购订单 - 审核/取消审核')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_fid where fid = '2027-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_fid(fid, name) values ('2027-02', '采购订单 - 生成采购入库单')";
			$db->execute($sql);
		}
	}

	private function t_goods_si($db) {
		$tableName = "t_goods_si";
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_goods_si` (
					  `id` varchar(255) NOT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `warehouse_id` varchar(255) NOT NULL,
					  `safety_inventory` decimal(19,2) NOT NULL,
					  `inventory_upper` decimal(19,2) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->execute($sql);
			return;
		}
		
		$columnName = "inventory_upper";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} decimal(19,2) default null;";
			$db->execute($sql);
		}
	}

	private function t_menu_item($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0603' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0603', '现金收支查询', '2024', '06', 3)";
			$db->execute($sql);
		}
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0604' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0604', '预收款管理', '2025', '06', 4)";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0605' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0605', '预付款管理', '2026', '06', 5)";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_menu_item
				where id = '0200' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_menu_item(id, caption, fid, parent_id, show_order)
					values ('0200', '采购订单', '2027', '02', 0)";
			$db->execute($sql);
		}
	}

	private function t_po_bill($db) {
		$tableName = "t_po_bill";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_bill` (
					  `id` varchar(255) NOT NULL,
					  `bill_status` int(11) NOT NULL,
					  `biz_dt` datetime NOT NULL,
					  `deal_date` datetime NOT NULL,
					  `org_id` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  `ref` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `contact` varchar(255) NOT NULL,
					  `tel` varchar(255) DEFAULT NULL,
					  `fax` varchar(255) DEFAULT NULL,
					  `deal_address` varchar(255) DEFAULT NULL,
					  `bill_memo` varchar(255) DEFAULT NULL,
					  `payment_type` int(11) NOT NULL DEFAULT 0,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$columnName = "confirm_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "confirm_date";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} datetime default null;";
			$db->execute($sql);
		}
	}

	private function t_po_bill_detail($db) {
		$tableName = "t_po_bill_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_bill_detail` (
					  `id` varchar(255) NOT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `goods_id` varchar(255) NOT NULL,
					  `goods_count` int(11) NOT NULL,
					  `goods_money` decimal(19,2) NOT NULL,
					  `goods_price` decimal(19,2) NOT NULL,
					  `pobill_id` varchar(255) NOT NULL,
					  `tax_rate` decimal(19,2) NOT NULL,
					  `tax` decimal(19,2) NOT NULL,
					  `money_with_tax` decimal(19,2) NOT NULL,
					  `pw_count` int(11) NOT NULL,
					  `left_count` int(11) NOT NULL,
					  `show_order` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_po_pw($db) {
		$tableName = "t_po_pw";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_po_pw` (
					  `po_id` varchar(255) NOT NULL,
					  `pw_id` varchar(255) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pr_bill($db) {
		$tableName = "t_pr_bill";
		
		$columnName = "receiving_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_pre_payment($db) {
		$tableName = "t_pre_payment";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_payment` (
					  `id` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_payment_detail($db) {
		$tableName = "t_pre_payment_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_payment_detail` (
					  `id` varchar(255) NOT NULL,
					  `supplier_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `biz_date` datetime DEFAULT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  `biz_user_id` varchar(255) NOT NULL,
					  `input_user_id` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_receiving($db) {
		$tableName = "t_pre_receiving";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_receiving` (
					  `id` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
	}

	private function t_pre_receiving_detail($db) {
		$tableName = "t_pre_receiving_detail";
		
		if (! $this->tableExists($db, $tableName)) {
			$sql = "CREATE TABLE IF NOT EXISTS `t_pre_receiving_detail` (
					  `id` varchar(255) NOT NULL,
					  `customer_id` varchar(255) NOT NULL,
					  `in_money` decimal(19,2) DEFAULT NULL,
					  `out_money` decimal(19,2) DEFAULT NULL,
					  `balance_money` decimal(19,2) NOT NULL,
					  `biz_date` datetime DEFAULT NULL,
					  `date_created` datetime DEFAULT NULL,
					  `ref_number` varchar(255) NOT NULL,
					  `ref_type` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
					";
			$db->execute($sql);
		}
		
		$columnName = "biz_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) not null;";
			$db->execute($sql);
		}
		
		$columnName = "input_user_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) not null;";
			$db->execute($sql);
		}
	}

	private function t_permission($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_permission where id = '2024' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2024', '2024', '现金收支查询', '现金收支查询')";
			$db->execute($sql);
		}
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_permission where id = '2025' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2025', '2025', '预收款管理', '预收款管理')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_permission where id = '2026' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2026', '2026', '预付款管理', '预付款管理')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_permission where id = '2027' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027', '2027', '采购订单', '采购订单')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核/取消审核
		$sql = "select count(*) as cnt from t_permission where id = '2027-01' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027-01', '2027-01', '采购订单 - 审核/取消审核', '采购订单 - 审核/取消审核')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_permission where id = '2027-02' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_permission(id, fid, name, note)
					values ('2027-02', '2027-02', '采购订单 - 生成采购入库单', '采购订单 - 生成采购入库单')";
			$db->execute($sql);
		}
	}

	private function t_pw_bill($db) {
		$tableName = "t_pw_bill";
		
		$columnName = "payment_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_role_permission($db) {
		// fid 2024: 现金收支查询
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2024' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2024')";
			$db->execute($sql);
		}
		
		// fid 2025: 预收款管理
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2025' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2025')";
			$db->execute($sql);
		}
		
		// fid 2026: 预付款管理
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2026' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2026')";
			$db->execute($sql);
		}
		
		// fid 2027: 采购订单
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027')";
			$db->execute($sql);
		}
		
		// fid 2027-01: 采购订单 - 审核/取消审核
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027-01' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-01')";
			$db->execute($sql);
		}
		
		// fid 2027-02: 采购订单 - 生成采购入库单
		$sql = "select count(*) as cnt from t_role_permission 
				where permission_id = '2027-02' and role_id = 'A83F617E-A153-11E4-A9B8-782BCBD7746B' ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			$sql = "insert into t_role_permission(role_id, permission_id)
					values ('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-02')";
			$db->execute($sql);
		}
	}

	private function t_supplier($db) {
		$tableName = "t_supplier";
		
		$columnName = "address";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_shipping";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "address_receipt";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_name";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "bank_account";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "tax_number";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "fax";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
		
		$columnName = "note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_supplier_category($db) {
		$tableName = "t_supplier_category";
		
		$columnName = "parent_id";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_sr_bill($db) {
		$tableName = "t_sr_bill";
		
		$columnName = "payment_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_sr_bill_detail($db) {
		$tableName = "t_sr_bill_detail";
		
		$columnName = "sn_note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}

	private function t_ws_bill($db) {
		$tableName = "t_ws_bill";
		
		$columnName = "receiving_type";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} int(11) not null default 0;";
			$db->execute($sql);
		}
	}

	private function t_ws_bill_detail($db) {
		$tableName = "t_ws_bill_detail";
		
		$columnName = "sn_note";
		if (! $this->columnExists($db, $tableName, $columnName)) {
			$sql = "alter table {$tableName} add {$columnName} varchar(255) default null;";
			$db->execute($sql);
		}
	}
}