<?php

namespace Home\Service;

/**
 * 安装Service
 *
 * @author 李静波
 */
class InstallService extends PSIBaseService {

	/**
	 * 首次运行PSI的时候，自动初始化数据库(创建表和往表里面插入初始化数据)
	 */
	public function autoInstallWhenFirstRun() {
		$db = M();
		$tableName = "t_biz_log";
		
		// 用 t_biz_log 这个表是否存在 来判断是否已经初始化了数据库
		if ($this->tableExists($db, $tableName)) {
			return;
		}
		
		$this->createTables();
		$this->insertInitData();
	}

	/**
	 * 创建表
	 */
	private function createTables() {
		$db = M();
		
		// t_biz_log
		$sql = "CREATE TABLE IF NOT EXISTS `t_biz_log` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `date_created` datetime DEFAULT NULL,
				  `info` varchar(1000) NOT NULL,
				  `ip` varchar(255) NOT NULL,
				  `user_id` varchar(255) NOT NULL,
				  `log_category` varchar(50) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `ip_from` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_fid
		$sql = "CREATE TABLE IF NOT EXISTS `t_fid` (
				  `fid` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				";
		$db->execute($sql);
		
		// t_menu_item
		$sql = "CREATE TABLE IF NOT EXISTS `t_menu_item` (
				  `id` varchar(255) NOT NULL,
				  `caption` varchar(255) NOT NULL,
				  `fid` varchar(255) DEFAULT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `show_order` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_org
		$sql = "CREATE TABLE IF NOT EXISTS `t_org` (
				  `id` varchar(255) NOT NULL,
				  `full_name` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `org_code` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_permission
		$sql = "CREATE TABLE IF NOT EXISTS `t_permission` (
				  `id` varchar(255) NOT NULL,
				  `fid` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `note` varchar(255) DEFAULT NULL,
				  `category` varchar(255) DEFAULT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_recent_fid
		$sql = "CREATE TABLE IF NOT EXISTS `t_recent_fid` (
				  `fid` varchar(255) NOT NULL,
				  `user_id` varchar(255) NOT NULL,
				  `click_count` int(11) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role
		$sql = "CREATE TABLE IF NOT EXISTS `t_role` (
				  `id` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role_permission
		$sql = "CREATE TABLE IF NOT EXISTS `t_role_permission` (
				  `role_id` varchar(255) DEFAULT NULL,
				  `permission_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role_user
		$sql = "CREATE TABLE IF NOT EXISTS `t_role_user` (
				  `role_id` varchar(255) DEFAULT NULL,
				  `user_id` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_user
		$sql = "CREATE TABLE IF NOT EXISTS `t_user` (
				  `id` varchar(255) NOT NULL,
				  `enabled` int(11) NOT NULL,
				  `login_name` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `org_id` varchar(255) NOT NULL,
				  `org_code` varchar(255) NOT NULL,
				  `password` varchar(255) NOT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `gender` varchar(255) DEFAULT NULL,
				  `birthday` varchar(255) DEFAULT NULL,
				  `id_card_number` varchar(255) DEFAULT NULL,
				  `tel` varchar(255) DEFAULT NULL,
				  `tel02` varchar(255) DEFAULT NULL,
				  `address` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_warehouse
		$sql = "CREATE TABLE IF NOT EXISTS `t_warehouse` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `inited` int(11) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_supplier
		$sql = "CREATE TABLE IF NOT EXISTS `t_supplier` (
				  `id` varchar(255) NOT NULL,
				  `category_id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `contact01` varchar(255) DEFAULT NULL,
				  `qq01` varchar(255) DEFAULT NULL,
				  `tel01` varchar(255) DEFAULT NULL,
				  `mobile01` varchar(255) DEFAULT NULL,
				  `contact02` varchar(255) DEFAULT NULL,
				  `qq02` varchar(255) DEFAULT NULL,
				  `tel02` varchar(255) DEFAULT NULL,
				  `mobile02` varchar(255) DEFAULT NULL,
				  `address` varchar(255) DEFAULT NULL,
				  `address_shipping` varchar(255) DEFAULT NULL,
				  `address_receipt` varchar(255) DEFAULT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `init_receivables` decimal(19,2) DEFAULT NULL, 
				  `init_receivables_dt` datetime DEFAULT NULL, 
				  `init_payables` decimal(19,2) DEFAULT NULL, 
				  `init_payables_dt` datetime DEFAULT NULL, 
				  `bank_name` varchar(255) DEFAULT NULL,
				  `bank_account` varchar(255) DEFAULT NULL,
				  `tax_number` varchar(255) DEFAULT NULL,
				  `fax` varchar(255) DEFAULT NULL,
				  `note` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_supplier_category
		$sql = "CREATE TABLE IF NOT EXISTS `t_supplier_category` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_goods
		$sql = "CREATE TABLE IF NOT EXISTS `t_goods` (
				  `id` varchar(255) NOT NULL,
				  `category_id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `sale_price` decimal(19,2) NOT NULL,
				  `spec` varchar(255) NOT NULL,
				  `unit_id` varchar(255) NOT NULL,
				  `purchase_price` decimal(19, 2) DEFAULT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `spec_py` varchar(255) DEFAULT NULL,
				  `bar_code` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `memo` varchar(500) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  `brand_id` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_goods_category
		$sql = "CREATE TABLE IF NOT EXISTS `t_goods_category` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `full_name` varchar(1000) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_goods_unit
		$sql = "CREATE TABLE IF NOT EXISTS `t_goods_unit` (
			  `id` varchar(255) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `data_org` varchar(255) DEFAULT NULL,
			  `company_id` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_customer
		$sql = "CREATE TABLE IF NOT EXISTS `t_customer` (
				  `id` varchar(255) NOT NULL,
				  `category_id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `contact01` varchar(255) DEFAULT NULL,
				  `qq01` varchar(255) DEFAULT NULL,
				  `tel01` varchar(255) DEFAULT NULL,
				  `mobile01` varchar(255) DEFAULT NULL,
				  `contact02` varchar(255) DEFAULT NULL,
				  `qq02` varchar(255) DEFAULT NULL,
				  `tel02` varchar(255) DEFAULT NULL,
				  `mobile02` varchar(255) DEFAULT NULL,
				  `address` varchar(255) DEFAULT NULL,
				  `address_shipping` varchar(255) DEFAULT NULL,
				  `address_receipt` varchar(255) DEFAULT NULL,
				  `py` varchar(255) DEFAULT NULL,
				  `init_receivables` decimal(19,2) DEFAULT NULL, 
				  `init_receivables_dt` datetime DEFAULT NULL, 
				  `init_payables` decimal(19,2) DEFAULT NULL, 
				  `init_payables_dt` datetime DEFAULT NULL, 
				  `bank_name` varchar(255) DEFAULT NULL,
				  `bank_account` varchar(255) DEFAULT NULL,
				  `tax_number` varchar(255) DEFAULT NULL,
				  `fax` varchar(255) DEFAULT NULL,
				  `note` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_customer_category
		$sql = "CREATE TABLE IF NOT EXISTS `t_customer_category` (
				  `id` varchar(255) NOT NULL,
				  `code` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `parent_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_inventory
		$sql = "CREATE TABLE IF NOT EXISTS `t_inventory` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `balance_count` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `balance_price` decimal(19,2) NOT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `in_count` decimal(19,2) DEFAULT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `in_price` decimal(19,2) DEFAULT NULL,
				  `out_count` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `out_price` decimal(19,2) DEFAULT NULL,
				  `afloat_count` decimal(19,2) DEFAULT NULL,
				  `afloat_money` decimal(19,2) DEFAULT NULL,
				  `afloat_price` decimal(19,2) DEFAULT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_inventory_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_inventory_detail` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `balance_count` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `balance_price` decimal(19,2) NOT NULL,
				  `biz_date` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `in_count` decimal(19,2) DEFAULT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `in_price` decimal(19,2) DEFAULT NULL,
				  `out_count` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `out_price` decimal(19,2) DEFAULT NULL,
				  `ref_number` varchar(255) DEFAULT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_pw_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_pw_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `biz_dt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_money` decimal(19,2) NOT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `ref` varchar(255) NOT NULL,
				  `supplier_id` varchar(255) NOT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `payment_type` int(11) NOT NULL DEFAULT 0,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pw_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_pw_bill_detail` (
				  `id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `goods_count` int(11) NOT NULL,
				  `goods_money` decimal(19,2) NOT NULL,
				  `goods_price` decimal(19,2) NOT NULL,
				  `pwbill_id` varchar(255) NOT NULL,
				  `show_order` int(11) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `memo` varchar(1000) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_payables
		$sql = "CREATE TABLE IF NOT EXISTS `t_payables` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `ca_id` varchar(255) NOT NULL,
				  `ca_type` varchar(255) NOT NULL,
				  `pay_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_payables_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_payables_detail` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `ca_id` varchar(255) NOT NULL,
				  `ca_type` varchar(255) NOT NULL,
				  `biz_date` datetime DEFAULT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `pay_money` decimal(19,2) NOT NULL,
				  `ref_number` varchar(255) NOT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_receivables
		$sql = "CREATE TABLE IF NOT EXISTS `t_receivables` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `ca_id` varchar(255) NOT NULL,
				  `ca_type` varchar(255) NOT NULL,
				  `rv_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_receivables_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_receivables_detail` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `ca_id` varchar(255) NOT NULL,
				  `ca_type` varchar(255) NOT NULL,
				  `biz_date` datetime DEFAULT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `ref_number` varchar(255) NOT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `rv_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_payment
		$sql = "CREATE TABLE IF NOT EXISTS `t_payment` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `biz_date` datetime NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `pay_user_id` varchar(255) NOT NULL,
				  `bill_id` varchar(255) NOT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `ref_number` varchar(255) NOT NULL,
				  `remark` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_ws_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_ws_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `bizdt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `customer_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `inventory_money` decimal(19,2) DEFAULT NULL,
				  `profit` decimal(19,2) DEFAULT NULL,
				  `ref` varchar(255) NOT NULL,
				  `sale_money` decimal(19,2) DEFAULT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `receiving_type` int(11) NOT NULL DEFAULT 0,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  `memo` varchar(1000) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_ws_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_ws_bill_detail` (
			  `id` varchar(255) NOT NULL,
			  `date_created` datetime DEFAULT NULL,
			  `goods_id` varchar(255) NOT NULL,
			  `goods_count` int(11) NOT NULL,
			  `goods_money` decimal(19,2) NOT NULL,
			  `goods_price` decimal(19,2) NOT NULL,
			  `inventory_money` decimal(19,2) DEFAULT NULL,
			  `inventory_price` decimal(19,2) DEFAULT NULL,
			  `show_order` int(11) NOT NULL,
			  `wsbill_id` varchar(255) NOT NULL,
			  `sn_note` varchar(255) DEFAULT NULL,
			  `data_org` varchar(255) DEFAULT NULL,
			  `memo` varchar(1000) DEFAULT NULL,
			  `company_id` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_receiving
		$sql = "CREATE TABLE IF NOT EXISTS `t_receiving` (
				  `id` varchar(255) NOT NULL,
				  `act_money` decimal(19,2) NOT NULL,
				  `biz_date` datetime NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `remark` varchar(255) NOT NULL,
				  `rv_user_id` varchar(255) NOT NULL,
				  `bill_id` varchar(255) NOT NULL,
				  `ref_number` varchar(255) NOT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_sr_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_sr_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `bizdt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `customer_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `inventory_money` decimal(19,2) DEFAULT NULL,
				  `profit` decimal(19,2) DEFAULT NULL,
				  `ref` varchar(255) NOT NULL,
				  `rejection_sale_money` decimal(19,2) DEFAULT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `ws_bill_id` varchar(255) NOT NULL,
				  `payment_type` int(11) NOT NULL DEFAULT 0,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_sr_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_sr_bill_detail` (
				  `id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `goods_count` int(11) NOT NULL,
				  `goods_money` decimal(19,2) NOT NULL,
				  `goods_price` decimal(19,2) NOT NULL,
				  `inventory_money` decimal(19,2) NOT NULL,
				  `inventory_price` decimal(19,2) NOT NULL,
				  `rejection_goods_count` int(11) NOT NULL,
				  `rejection_goods_price` decimal(19,2) NOT NULL,
				  `rejection_sale_money` decimal(19,2) NOT NULL,
				  `show_order` int(11) NOT NULL,
				  `srbill_id` varchar(255) NOT NULL,
				  `wsbilldetail_id` varchar(255) NOT NULL,
				  `sn_note` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_it_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_it_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `bizdt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `ref` varchar(255) NOT NULL,
				  `from_warehouse_id` varchar(255) NOT NULL,
				  `to_warehouse_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_it_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_it_bill_detail` (
				  `id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `goods_count` int(11) NOT NULL,
				  `show_order` int(11) NOT NULL,
				  `itbill_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_ic_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_ic_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `bizdt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `ref` varchar(255) NOT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_ic_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_ic_bill_detail` (
				  `id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `goods_count` int(11) NOT NULL,
				  `goods_money` decimal(19,2) NOT NULL,
				  `show_order` int(11) NOT NULL,
				  `icbill_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pr_bill
		$sql = "CREATE TABLE IF NOT EXISTS `t_pr_bill` (
				  `id` varchar(255) NOT NULL,
				  `bill_status` int(11) NOT NULL,
				  `bizdt` datetime NOT NULL,
				  `biz_user_id` varchar(255) NOT NULL,
				  `supplier_id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `inventory_money` decimal(19,2) DEFAULT NULL,
				  `ref` varchar(255) NOT NULL,
				  `rejection_money` decimal(19,2) DEFAULT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `pw_bill_id` varchar(255) NOT NULL,
				  `receiving_type` int(11) NOT NULL DEFAULT 0,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pr_bill_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_pr_bill_detail` (
				  `id` varchar(255) NOT NULL,
				  `date_created` datetime DEFAULT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `goods_count` int(11) NOT NULL,
				  `goods_money` decimal(19,2) NOT NULL,
				  `goods_price` decimal(19,2) NOT NULL,
				  `inventory_money` decimal(19,2) NOT NULL,
				  `inventory_price` decimal(19,2) NOT NULL,
				  `rejection_goods_count` int(11) NOT NULL,
				  `rejection_goods_price` decimal(19,2) NOT NULL,
				  `rejection_money` decimal(19,2) NOT NULL,
				  `show_order` int(11) NOT NULL,
				  `prbill_id` varchar(255) NOT NULL,
				  `pwbilldetail_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_config
		$sql = "CREATE TABLE IF NOT EXISTS `t_config` (
				  `id` varchar(255) NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `value` varchar(255) NOT NULL,
				  `note` varchar(255) NOT NULL,
				  `show_order` int(11) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_psi_db_version
		$sql = "CREATE TABLE IF NOT EXISTS `t_psi_db_version` (
				  `db_version` varchar(255) NOT NULL,
				  `update_dt` datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_goods_si
		$sql = "CREATE TABLE IF NOT EXISTS `t_goods_si` (
				  `id` varchar(255) NOT NULL,
				  `goods_id` varchar(255) NOT NULL,
				  `warehouse_id` varchar(255) NOT NULL,
				  `safety_inventory` decimal(19,2) NOT NULL,
				  `inventory_upper` decimal(19,2) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_cash
		$sql = "CREATE TABLE IF NOT EXISTS `t_cash` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `biz_date` datetime NOT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_cash_detail
		$sql = "CREATE TABLE IF NOT EXISTS `t_cash_detail` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `biz_date` datetime NOT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `ref_number` varchar(255) NOT NULL,
				  `ref_type` varchar(255) NOT NULL,
				  `date_created` datetime NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pre_receiving
		$sql = "CREATE TABLE IF NOT EXISTS `t_pre_receiving` (
				  `id` varchar(255) NOT NULL,
				  `customer_id` varchar(255) NOT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pre_receiving_detail
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
				  `biz_user_id` varchar(255) NOT NULL,
				  `input_user_id` varchar(255) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pre_payment
		$sql = "CREATE TABLE IF NOT EXISTS `t_pre_payment` (
				  `id` varchar(255) NOT NULL,
				  `supplier_id` varchar(255) NOT NULL,
				  `in_money` decimal(19,2) DEFAULT NULL,
				  `out_money` decimal(19,2) DEFAULT NULL,
				  `balance_money` decimal(19,2) NOT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_pre_payment_detail
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
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_po_bill
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
				  `confirm_user_id` varchar(255) DEFAULT NULL,
				  `confirm_date` datetime DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_po_bill_detail
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
				  `data_org` varchar(255) DEFAULT NULL,
				  `company_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_po_pw
		$sql = "CREATE TABLE IF NOT EXISTS `t_po_pw` (
				  `po_id` varchar(255) NOT NULL,
				  `pw_id` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_role_permission_dataorg
		$sql = "CREATE TABLE IF NOT EXISTS `t_role_permission_dataorg` (
				  `role_id` varchar(255) DEFAULT NULL,
				  `permission_id` varchar(255) DEFAULT NULL,
				  `data_org` varchar(255) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		$db->execute($sql);
		
		// t_inventory_fifo
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
				  `pwbilldetail_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_inventory_fifo_detail
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
				  `pwbilldetail_id` varchar(255) DEFAULT NULL,
				  `wsbilldetail_id` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$db->execute($sql);
		
		// t_so_bill
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
		
		// t_so_bill_detail
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
		
		// t_so_ws
		$sql = "CREATE TABLE IF NOT EXISTS `t_so_ws` (
				  `so_id` varchar(255) NOT NULL,
				  `ws_id` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				";
		$db->execute($sql);
		
		// t_goods_brand
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
		
		// t_goods_bom
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

	/**
	 * 插入初始化数据
	 */
	private function insertInitData() {
		$db = M();
		$db->startTrans();
		
		// t_fid
		$sql = "INSERT INTO `t_fid` (`fid`, `name`) VALUES
					('-9999', '重新登录'),
					('-9997', '首页'),
					('-9996', '修改我的密码'),
					('-9995', '帮助'),
					('-9994', '关于'),
					('-9993', '购买商业服务'),
					('-8999', '用户管理'),
					('-8999-01', '组织机构在业务单据中的使用权限'),
					('-8999-02', '业务员在业务单据中的使用权限'),
					('-8997', '业务日志'),
					('-8996', '权限管理'),
					('1001', '商品'),
					('1001-01', '商品在业务单据中的使用权限'),
					('1001-02', '商品分类'),
					('1002', '商品计量单位'),
					('1003', '仓库'),
					('1003-01', '仓库在业务单据中的使用权限'),
					('1004', '供应商档案'),
					('1004-01', '供应商档案在业务单据中的使用权限'),
					('1004-02', '供应商分类'),
					('1007', '客户资料'),
					('1007-01', '客户资料在业务单据中的使用权限'),
					('1007-02', '客户分类'),
					('2000', '库存建账'),
					('2001', '采购入库'),
					('2002', '销售出库'),
					('2002-01', '销售出库'),
					('2003', '库存账查询'),
					('2004', '应收账款管理'),
					('2005', '应付账款管理'),
					('2006', '销售退货入库'),
					('2007', '采购退货出库'),
					('2008', '业务设置'),
					('2009', '库间调拨'),
					('2010', '库存盘点'),
					('2011-01', '首页-销售看板'),
					('2011-02', '首页-库存看板'),
					('2011-03', '首页-采购看板'),
					('2011-04', '首页-资金看板'),
					('2012', '报表-销售日报表(按商品汇总)'),
					('2013', '报表-销售日报表(按客户汇总)'),
					('2014', '报表-销售日报表(按仓库汇总)'),
					('2015', '报表-销售日报表(按业务员汇总)'),
					('2016', '报表-销售月报表(按商品汇总)'),
					('2017', '报表-销售月报表(按客户汇总)'),
					('2018', '报表-销售月报表(按仓库汇总)'),
					('2019', '报表-销售月报表(按业务员汇总)'),
					('2020', '报表-安全库存明细表'),
					('2021', '报表-应收账款账龄分析表'),
					('2022', '报表-应付账款账龄分析表'),
					('2023', '报表-库存超上限明细表'),
					('2024', '现金收支查询'),
					('2025', '预收款管理'),
					('2026', '预付款管理'),
					('2027', '采购订单'),
					('2027-01', '采购订单 - 审核/取消审核'),
					('2027-02', '采购订单 - 生成采购入库单'),
					('2028-02', '销售订单 - 生成销售出库单'),
					('2029', '商品品牌');
				";
		$db->execute($sql);
		
		// t_menu_item
		$sql = "INSERT INTO `t_menu_item` (`id`, `caption`, `fid`, `parent_id`, `show_order`) VALUES
					('01', '文件', NULL, NULL, 1),
					('0101', '首页', '-9997', '01', 1),
					('0102', '重新登录', '-9999', '01', 2),
					('0103', '修改我的密码', '-9996', '01', 3),
					('02', '采购', NULL, NULL, 2),
					('0200', '采购订单', '2027', '02', 0),
					('0201', '采购入库', '2001', '02', 1),
					('0202', '采购退货出库', '2007', '02', 2),
					('03', '库存', NULL, NULL, 3),
					('0301', '库存账查询', '2003', '03', 1),
					('0302', '库存建账', '2000', '03', 2),
					('0303', '库间调拨', '2009', '03', 3),
					('0304', '库存盘点', '2010', '03', 4),
					('04', '销售', NULL, NULL, 4),
					('0400', '销售订单', '2028', '04', 0),
					('0401', '销售出库', '2002', '04', 1),
					('0402', '销售退货入库', '2006', '04', 2),
					('05', '客户关系', NULL, NULL, 5),
					('0501', '客户资料', '1007', '05', 1),
					('06', '资金', NULL, NULL, 6),
					('0601', '应收账款管理', '2004', '06', 1),
					('0602', '应付账款管理', '2005', '06', 2),
					('0603', '现金收支查询', '2024', '06', 3),
					('0604', '预收款管理', '2025', '06', 4),
					('0605', '预付款管理', '2026', '06', 5),
					('07', '报表', NULL, NULL, 7),
					('0701', '销售日报表', NULL, '07', 1),
					('070101', '销售日报表(按商品汇总)', '2012', '0701', 1),
					('070102', '销售日报表(按客户汇总)', '2013', '0701', 2),
					('070103', '销售日报表(按仓库汇总)', '2014', '0701', 3),
					('070104', '销售日报表(按业务员汇总)', '2015', '0701', 4),
					('0702', '销售月报表', NULL, '07', 2),
					('070201', '销售月报表(按商品汇总)', '2016', '0702', 1),
					('070202', '销售月报表(按客户汇总)', '2017', '0702', 2),
					('070203', '销售月报表(按仓库汇总)', '2018', '0702', 3),
					('070204', '销售月报表(按业务员汇总)', '2019', '0702', 4),
					('0703', '库存报表', NULL, '07', 3),
					('070301', '安全库存明细表', '2020', '0703', 1),
					('070302', '库存超上限明细表', '2023', '0703', 2),
					('0706', '资金报表', NULL, '07', 6),
					('070601', '应收账款账龄分析表', '2021', '0706', 1),
					('070602', '应付账款账龄分析表', '2022', '0706', 2),
					('08', '基础数据', NULL, NULL, 8),
					('0801', '商品', NULL, '08', 1),
					('080101', '商品', '1001', '0801', 1),
					('080102', '商品计量单位', '1002', '0801', 2),
					('080103', '商品品牌', '2029', '0801', 3),
					('0803', '仓库', '1003', '08', 3),
					('0804', '供应商档案', '1004', '08', 4),
					('09', '系统管理', NULL, NULL, 9),
					('0901', '用户管理', '-8999', '09', 1),
					('0902', '权限管理', '-8996', '09', 2),
					('0903', '业务日志', '-8997', '09', 3),
					('0904', '业务设置', '2008', '09', 4),
					('10', '帮助', NULL, NULL, 10),
					('1001', '使用帮助', '-9995', '10', 1),
					('1002', '购买商业服务', '-9993', '10', 2),
					('1003', '关于PSI', '-9994', '10', 3);
		";
		$db->execute($sql);
		
		// t_org
		$sql = "INSERT INTO `t_org` (`id`, `full_name`, `name`, `org_code`, `data_org`, `parent_id`) VALUES
					('4D74E1E4-A129-11E4-9B6A-782BCBD7746B', '公司', '公司', '01', '01', NULL),
					('5EBDBE11-A129-11E4-9B6A-782BCBD7746B', '公司\\\\信息部', '信息部', '0199', '0101', '4D74E1E4-A129-11E4-9B6A-782BCBD7746B');
		";
		$db->execute($sql);
		
		// t_permission
		$sql = "INSERT INTO `t_permission` (`id`, `fid`, `name`, `note`, `category`, `py`) VALUES
				('-8996', '-8996', '权限管理', '通过菜单进入权限管理模块的权限', '权限管理', 'QXGL'),
				('-8996-01', '-8996-01', '权限管理-新增角色', '权限管理模块[新增角色]按钮的权限', '权限管理', 'QXGL_XZJS'),
				('-8996-02', '-8996-02', '权限管理-编辑角色', '权限管理模块[编辑角色]按钮的权限', '权限管理', 'QXGL_BJJS'),
				('-8996-03', '-8996-03', '权限管理-删除角色', '权限管理模块[删除角色]按钮的权限', '权限管理', 'QXGL_SCJS'),
				('-8997', '-8997', '业务日志', '业务日志', '系统管理', 'YWRZ'),
				('-8999', '-8999', '用户管理', '通过菜单进入用户管理模块的权限', '用户管理', 'YHGL'),
				('-8999-01', '-8999-01', '组织机构在业务单据中的使用权限', '组织机构在业务单据中的使用权限', '用户管理', 'ZZJGZYWDJZDSYQX'),
				('-8999-02', '-8999-02', '业务员在业务单据中的使用权限', '业务员在业务单据中的使用权限', '用户管理', 'YWYZYWDJZDSYQX'),
				('-8999-03', '-8999-03', '用户管理-新增组织机构', '用户管理模块[新增组织机构]按钮的权限', '用户管理', 'YHGL_XZZZJG'),
				('-8999-04', '-8999-04', '用户管理-编辑组织机构', '用户管理模块[编辑组织机构]按钮的权限', '用户管理', 'YHGL_BJZZJG'),
				('-8999-05', '-8999-05', '用户管理-删除组织机构', '用户管理模块[删除组织机构]按钮的权限', '用户管理', 'YHGL_SCZZJG'),
				('-8999-06', '-8999-06', '用户管理-新增用户', '用户管理模块[新增用户]按钮的权限', '用户管理', 'YHGL_XZYH'),
				('-8999-07', '-8999-07', '用户管理-编辑用户', '用户管理模块[编辑用户]按钮的权限', '用户管理', 'YHGL_BJYH'),
				('-8999-08', '-8999-08', '用户管理-删除用户', '用户管理模块[删除用户]按钮的权限', '用户管理', 'YHGL_SCYH'),
				('-8999-09', '-8999-09', '用户管理-修改用户密码', '用户管理模块[修改用户密码]按钮的权限', '用户管理', 'YHGL_XGYHMM'),
				('1001', '1001', '商品', '通过菜单进入基础数据商品模块的权限', '商品', 'SP'),
				('1001-01', '1001-01', '商品在业务单据中的使用权限', '商品在业务单据中的使用权限', '商品', 'SPZYWDJZDSYQX'),
				('1001-02', '1001-02', '商品分类', '商品分类', '商品', 'SPFL'),
				('1001-03', '1001-03', '新增商品分类', '基础数据商品模块[新增商品分类]按钮的权限', '商品', 'XZSPFL'),
				('1001-04', '1001-04', '编辑商品分类', '基础数据商品模块[编辑商品分类]按钮的权限', '商品', 'BJSPFL'),
				('1001-05', '1001-05', '删除商品分类', '基础数据商品模块[删除商品分类]按钮的权限', '商品', 'SCSPFL'),
				('1001-06', '1001-06', '新增商品', '基础数据商品模块[新增商品]按钮的权限', '商品', 'XZSP'),
				('1001-07', '1001-07', '编辑商品', '基础数据商品模块[编辑商品]按钮的权限', '商品', 'BJSP'),
				('1001-08', '1001-08', '删除商品', '基础数据商品模块[删除商品]按钮的权限', '商品', 'SCSP'),
				('1001-09', '1001-09', '导入商品', '基础数据商品模块[导入商品]按钮的权限', '商品', 'DRSP'),
				('1001-10', '1001-10', '设置商品安全库存', '基础数据商品模块[设置商品安全库存]按钮的权限', '商品', 'SZSPAQKC'),
				('1002', '1002', '商品计量单位', '商品计量单位', '商品', 'SPJLDW'),
				('1003', '1003', '仓库', '通过菜单进入基础数据仓库模块的权限', '仓库', 'CK'),
				('1003-01', '1003-01', '仓库在业务单据中的使用权限', '仓库在业务单据中的使用权限', '仓库', 'CKZYWDJZDSYQX'),
				('1003-02', '1003-02', '新增仓库', '基础数据仓库模块[新增仓库]按钮的权限', '仓库', 'XZCK'),
				('1003-03', '1003-03', '编辑仓库', '基础数据仓库模块[编辑仓库]按钮的权限', '仓库', 'BJCK'),
				('1003-04', '1003-04', '删除仓库', '基础数据仓库模块[删除仓库]按钮的权限', '仓库', 'SCCK'),
				('1003-05', '1003-05', '修改仓库数据域', '基础数据仓库模块[修改仓库数据域]按钮的权限', '仓库', 'XGCKSJY'),
				('1004', '1004', '供应商档案', '通过菜单进入基础数据供应商档案模块的权限', '供应商管理', 'GYSDA'),
				('1004-01', '1004-01', '供应商档案在业务单据中的使用权限', '供应商档案在业务单据中的使用权限', '供应商管理', 'GYSDAZYWDJZDSYQX'),
				('1004-02', '1004-02', '供应商分类', '供应商分类', '供应商管理', 'GYSFL'),
				('1004-03', '1004-03', '新增供应商分类', '基础数据供应商档案模块[新增供应商分类]按钮的权限', '供应商管理', 'XZGYSFL'),
				('1004-04', '1004-04', '编辑供应商分类', '基础数据供应商档案模块[编辑供应商分类]按钮的权限', '供应商管理', 'BJGYSFL'),
				('1004-05', '1004-05', '删除供应商分类', '基础数据供应商档案模块[删除供应商分类]按钮的权限', '供应商管理', 'SCGYSFL'),
				('1004-06', '1004-06', '新增供应商', '基础数据供应商档案模块[新增供应商]按钮的权限', '供应商管理', 'XZGYS'),
				('1004-07', '1004-07', '编辑供应商', '基础数据供应商档案模块[编辑供应商]按钮的权限', '供应商管理', 'BJGYS'),
				('1004-08', '1004-08', '删除供应商', '基础数据供应商档案模块[删除供应商]按钮的权限', '供应商管理', 'SCGYS'),
				('1007', '1007', '客户资料', '通过菜单进入客户资料模块的权限', '客户管理', 'KHZL'),
				('1007-01', '1007-01', '客户资料在业务单据中的使用权限', '客户资料在业务单据中的使用权限', '客户管理', 'KHZLZYWDJZDSYQX'),
				('1007-02', '1007-02', '客户分类', '客户分类', '客户管理', 'KHFL'),
				('1007-03', '1007-03', '新增客户分类', '客户资料模块[新增客户分类]按钮的权限', '客户管理', 'XZKHFL'),
				('1007-04', '1007-04', '编辑客户分类', '客户资料模块[编辑客户分类]按钮的权限', '客户管理', 'BJKHFL'),
				('1007-05', '1007-05', '删除客户分类', '客户资料模块[删除客户分类]按钮的权限', '客户管理', 'SCKHFL'),
				('1007-06', '1007-06', '新增客户', '客户资料模块[新增客户]按钮的权限', '客户管理', 'XZKH'),
				('1007-07', '1007-07', '编辑客户', '客户资料模块[编辑客户]按钮的权限', '客户管理', 'BJKH'),
				('1007-08', '1007-08', '删除客户', '客户资料模块[删除客户]按钮的权限', '客户管理', 'SCKH'),
				('1007-09', '1007-09', '导入客户', '客户资料模块[导入客户]按钮的权限', '客户管理', 'DRKH'),
				('2000', '2000', '库存建账', '库存建账', '库存建账', 'KCJZ'),
				('2001', '2001', '采购入库', '采购入库', '采购入库', 'CGRK'),
				('2002', '2002', '销售出库', '销售出库', '销售出库', 'XSCK'),
				('2002-01', '2002-01', '销售出库单允许编辑销售单价', '销售出库单允许编辑销售单价', '销售出库', 'XSCKDYXBJXSDJ'),
				('2003', '2003', '库存账查询', '库存账查询', '库存账查询', 'KCZCX'),
				('2004', '2004', '应收账款管理', '应收账款管理', '应收账款管理', 'YSZKGL'),
				('2005', '2005', '应付账款管理', '应付账款管理', '应付账款管理', 'YFZKGL'),
				('2006', '2006', '销售退货入库', '销售退货入库', '销售退货入库', 'XSTHRK'),
				('2007', '2007', '采购退货出库', '采购退货出库', '采购退货出库', 'CGTHCK'),
				('2008', '2008', '业务设置', '业务设置', '系统管理', 'YWSZ'),
				('2009', '2009', '库间调拨', '库间调拨', '库间调拨', 'KJDB'),
				('2010', '2010', '库存盘点', '库存盘点', '库存盘点', 'KCPD'),
				('2011-01', '2011-01', '首页-销售看板', '首页-销售看板', '首页看板', 'SY_XSKB'),
				('2011-02', '2011-02', '首页-库存看板', '首页-库存看板', '首页看板', 'SY_KCKB'),
				('2011-03', '2011-03', '首页-采购看板', '首页-采购看板', '首页看板', 'SY_CGKB'),
				('2011-04', '2011-04', '首页-资金看板', '首页-资金看板', '首页看板', 'SY_ZJKB'),
				('2012', '2012', '报表-销售日报表(按商品汇总)', '报表-销售日报表(按商品汇总)', '销售日报表', 'BB_XSRBB_ASPHZ_'),
				('2013', '2013', '报表-销售日报表(按客户汇总)', '报表-销售日报表(按客户汇总)', '销售日报表', 'BB_XSRBB_AKHHZ_'),
				('2014', '2014', '报表-销售日报表(按仓库汇总)', '报表-销售日报表(按仓库汇总)', '销售日报表', 'BB_XSRBB_ACKHZ_'),
				('2015', '2015', '报表-销售日报表(按业务员汇总)', '报表-销售日报表(按业务员汇总)', '销售日报表', 'BB_XSRBB_AYWYHZ_'),
				('2016', '2016', '报表-销售月报表(按商品汇总)', '报表-销售月报表(按商品汇总)', '销售月报表', 'BB_XSYBB_ASPHZ_'),
				('2017', '2017', '报表-销售月报表(按客户汇总)', '报表-销售月报表(按客户汇总)', '销售月报表', 'BB_XSYBB_AKHHZ_'),
				('2018', '2018', '报表-销售月报表(按仓库汇总)', '报表-销售月报表(按仓库汇总)', '销售月报表', 'BB_XSYBB_ACKHZ_'),
				('2019', '2019', '报表-销售月报表(按业务员汇总)', '报表-销售月报表(按业务员汇总)', '销售月报表', 'BB_XSYBB_AYWYHZ_'),
				('2020', '2020', '报表-安全库存明细表', '报表-安全库存明细表', '库存报表', 'BB_AQKCMXB'),
				('2021', '2021', '报表-应收账款账龄分析表', '报表-应收账款账龄分析表', '资金报表', 'BB_YSZKZLFXB'),
				('2022', '2022', '报表-应付账款账龄分析表', '报表-应付账款账龄分析表', '资金报表', 'BB_YFZKZLFXB'),
				('2023', '2023', '报表-库存超上限明细表', '报表-库存超上限明细表', '库存报表', 'BB_KCCSXMXB'),
				('2024', '2024', '现金收支查询', '现金收支查询', '现金管理', 'XJSZCX'),
				('2025', '2025', '预收款管理', '预收款管理', '预收款管理', 'YSKGL'),
				('2026', '2026', '预付款管理', '预付款管理', '预付款管理', 'YFKGL'),
				('2027', '2027', '采购订单', '采购订单', '采购订单', 'CGDD'),
				('2027-01', '2027-01', '采购订单 - 审核/取消审核', '采购订单 - 审核/取消审核', '采购订单', 'CGDD _ SH_QXSH'),
				('2027-02', '2027-02', '采购订单 - 生成采购入库单', '采购订单 - 生成采购入库单', '采购订单', 'CGDD _ SCCGRKD'),
				('2028', '2028', '销售订单', '销售订单', '销售订单', 'XSDD'),
				('2028-01', '2028-01', '销售订单 - 审核/取消审核', '销售订单 - 审核/取消审核', '销售订单', 'XSDD _ SH_QXSH'),
				('2028-02', '2028-02', '销售订单 - 生成销售出库单', '销售订单 - 生成销售出库单', '销售订单', 'XSDD _ SCXSCKD'),
				('2029', '2029', '商品品牌', '通过菜单进入基础数据商品品牌模块的权限', '商品', 'SPPP');
				";
		$db->execute($sql);
		
		// t_role
		$sql = "INSERT INTO `t_role` (`id`, `name`, `data_org`, `company_id`) VALUES
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '系统管理', '01010001', '4D74E1E4-A129-11E4-9B6A-782BCBD7746B');
		";
		$db->execute($sql);
		
		// t_role_permission
		$sql = "INSERT INTO `t_role_permission` (`role_id`, `permission_id`) VALUES
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '-8999'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '-8997'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '-8996'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '1001'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '1002'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '1003'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '1004'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '1007'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2000'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2001'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2002'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2002-01'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2003'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2004'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2005'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2006'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2007'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2008'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2009'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2010'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2011-01'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2011-02'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2011-03'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2011-04'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2012'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2013'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2014'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2015'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2016'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2017'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2018'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2019'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2020'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2021'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2022'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2023'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2024'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2025'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2026'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-01'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2027-02'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2028'),
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '2029');
				";
		$db->execute($sql);
		
		// t_role_user
		$sql = "INSERT INTO `t_role_user` (`role_id`, `user_id`) VALUES
					('A83F617E-A153-11E4-A9B8-782BCBD7746B', '6C2A09CD-A129-11E4-9B6A-782BCBD7746B');
		";
		$db->execute($sql);
		
		// t_user
		$sql = "INSERT INTO `t_user` (`id`, `enabled`, `login_name`, `name`, `org_id`, `org_code`, `data_org`, `password`, `py`) VALUES
					('6C2A09CD-A129-11E4-9B6A-782BCBD7746B', '1', 'admin', '系统管理员', '5EBDBE11-A129-11E4-9B6A-782BCBD7746B', '019901', '01010001', '21232f297a57a5a743894a0e4a801fc3', 'XTGLY');
		";
		$db->execute($sql);
		
		// t_config
		$sql = "INSERT INTO `t_config` (`id`, `name`, `value`, `note`, `show_order`) VALUES
					('9000-01', '公司名称', '', '', 100),
					('9000-02', '公司地址', '', '', 101),
					('9000-03', '公司电话', '', '', 102),
					('9000-04', '公司传真', '', '', 103),
					('9000-05', '公司邮编', '', '', 104),
					('2001-01', '采购入库默认仓库', '', '', 200),
					('2002-02', '销售出库默认仓库', '', '', 300),
					('2002-01', '销售出库单允许编辑销售单价', '0', '当允许编辑的时候，还需要给用户赋予权限[销售出库单允许编辑销售单价]', 301),
					('1003-02', '存货计价方法', '0', '', 401),
					('9001-01', '增值税税率', '17', '', 501),
					('9002-01', '产品名称', '开源进销存PSI', '', 0),
					('9003-01', '采购订单单号前缀', 'PO', '', 601),
					('9003-02', '采购入库单单号前缀', 'PW', '', 602),
					('9003-03', '采购退货出库单单号前缀', 'PR', '', 603),
					('9003-04', '销售出库单单号前缀', 'WS', '', 604),
					('9003-05', '销售退货入库单单号前缀', 'SR', '', 605),
					('9003-06', '调拨单单号前缀', 'IT', '', 606),
					('9003-07', '盘点单单号前缀', 'IC', '', 607),
					('9003-08', '销售订单单号前缀', 'SO', '', 608);
				";
		$db->execute($sql);
		
		$sql = "update t_config set company_id = '4D74E1E4-A129-11E4-9B6A-782BCBD7746B' ;";
		$db->execute($sql);
		
		// t_psi_db_version
		$sql = "INSERT INTO `t_psi_db_version` (`db_version`, `update_dt`) VALUES
					('%s', now());
		";
		$db->execute($sql, $this->CURRENT_DB_VERSION);
		
		$db->commit();
		
		return true;
	}
}