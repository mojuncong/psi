<?php

namespace Home\Common;

/**
 * FId常数值
 *
 * @author 李静波
 */
class FIdConst {
	
	/**
	 * 首页
	 */
	const HOME = "-9997";
	
	/**
	 * 重新登录
	 */
	const RELOGIN = "-9999";
	
	/**
	 * 修改我的密码
	 */
	const CHANGE_MY_PASSWORD = "-9996";
	
	/**
	 * 使用帮助
	 */
	const HELP = "-9995";
	
	/**
	 * 关于
	 */
	const ABOUT = "-9994";
	
	/**
	 * 购买商业服务
	 */
	const PSI_SERVICE = "-9993";
	
	/**
	 * 用户管理
	 */
	const USR_MANAGEMENT = "-8999";
	
	/**
	 * 用户管理 - 新增组织机构
	 */
	const USER_MANAGEMENT_ADD_ORG = "-8999-03";
	
	/**
	 * 用户管理 - 编辑组织机构
	 */
	const USER_MANAGEMENT_EDIT_ORG = "-8999-04";
	
	/**
	 * 用户管理 - 删除组织机构
	 */
	const USER_MANAGEMENT_DELETE_ORG = "-8999-05";
	
	/**
	 * 用户管理 - 新增用户
	 */
	const USER_MANAGEMENT_ADD_USER = "-8999-06";
	
	/**
	 * 用户管理 - 编辑用户
	 */
	const USER_MANAGEMENT_EDIT_USER = "-8999-07";
	
	/**
	 * 用户管理 - 删除用户
	 */
	const USER_MANAGEMENT_DELETE_USER = "-8999-08";
	
	/**
	 * 用户管理 - 修改用户密码
	 */
	const USER_MANAGEMENT_CHANGE_USER_PASSWORD = "-8999-09";
	
	/**
	 * 权限管理
	 */
	const PERMISSION_MANAGEMENT = "-8996";
	
	/**
	 * 权限管理 - 新增角色 - 按钮权限
	 */
	const PERMISSION_MANAGEMENT_ADD = "-8996-01";
	
	/**
	 * 权限管理 - 编辑角色 - 按钮权限
	 */
	const PERMISSION_MANAGEMENT_EDIT = "-8996-02";
	
	/**
	 * 权限管理 - 删除角色 - 按钮权限
	 */
	const PERMISSION_MANAGEMENT_DELETE = "-8996-03";
	
	/**
	 * 业务日志
	 */
	const BIZ_LOG = "-8997";
	
	/**
	 * 基础数据-仓库
	 */
	const WAREHOUSE = "1003";
	
	/**
	 * 新增仓库
	 */
	const WAREHOUSE_ADD = "1003-02";
	
	/**
	 * 编辑仓库
	 */
	const WAREHOUSE_EDIT = "1003-03";
	
	/**
	 * 删除仓库
	 */
	const WAREHOUSE_DELETE = "1003-04";
	
	/**
	 * 修改仓库数据域
	 */
	const WAREHOUSE_EDIT_DATAORG = "1003-05";
	
	/**
	 * 基础数据-供应商档案
	 */
	const SUPPLIER = "1004";
	
	/**
	 * 供应商分类
	 */
	const SUPPLIER_CATEGORY = "1004-02";
	
	/**
	 * 新增供应商分类
	 */
	const SUPPLIER_CATEGORY_ADD = "1004-03";
	
	/**
	 * 编辑供应商分类
	 */
	const SUPPLIER_CATEGORY_EDIT = "1004-04";
	
	/**
	 * 删除供应商分类
	 */
	const SUPPLIER_CATEGORY_DELETE = "1004-05";
	
	/**
	 * 新增供应商
	 */
	const SUPPLIER_ADD = "1004-06";
	
	/**
	 * 编辑供应商
	 */
	const SUPPLIER_EDIT = "1004-07";
	
	/**
	 * 删除供应商
	 */
	const SUPPLIER_DELETE = "1004-08";
	
	/**
	 * 基础数据-商品
	 */
	const GOODS = "1001";
	
	/**
	 * 商品分类
	 */
	const GOODS_CATEGORY = "1001-02";
	
	/**
	 * 新增商品分类
	 */
	const GOODS_CATEGORY_ADD = "1001-03";
	
	/**
	 * 编辑商品分类
	 */
	const GOODS_CATEGORY_EDIT = "1001-04";
	
	/**
	 * 删除商品分类
	 */
	const GOODS_CATEGORY_DELETE = "1001-05";
	
	/**
	 * 新增商品
	 */
	const GOODS_ADD = "1001-06";
	
	/**
	 * 编辑商品
	 */
	const GOODS_EDIT = "1001-07";
	
	/**
	 * 删除商品
	 */
	const GOODS_DELETE = "1001-08";
	
	/**
	 * 导入商品
	 */
	const GOODS_IMPORT = "1001-09";
	
	/**
	 * 设置商品安全库存
	 */
	const GOODS_SI = "1001-10";
	
	/**
	 * 基础数据-商品计量单位
	 */
	const GOODS_UNIT = "1002";
	
	/**
	 * 客户资料
	 */
	const CUSTOMER = "1007";
	
	/**
	 * 客户分类
	 */
	const CUSTOMER_CATEGORY = "1007-02";
	
	/**
	 * 新增客户分类
	 */
	const CUSTOMER_CATEGORY_ADD = "1007-03";
	
	/**
	 * 编辑客户分类
	 */
	const CUSTOMER_CATEGORY_EDIT = "1007-04";
	
	/**
	 * 删除客户分类
	 */
	const CUSTOMER_CATEGORY_DELETE = "1007-05";
	
	/**
	 * 新增客户
	 */
	const CUSTOMER_ADD = "1007-06";
	
	/**
	 * 编辑客户
	 */
	const CUSTOMER_EDIT = "1007-07";
	
	/**
	 * 删除客户
	 */
	const CUSTOMER_DELETE = "1007-08";
	
	/**
	 * 导入客户
	 */
	const CUSTOMER_IMPORT = "1007-09";
	
	/**
	 * 库存建账
	 */
	const INVENTORY_INIT = "2000";
	
	/**
	 * 采购入库
	 */
	const PURCHASE_WAREHOUSE = "2001";
	
	/**
	 * 库存账查询
	 */
	const INVENTORY_QUERY = "2003";
	
	/**
	 * 应付账款管理
	 */
	const PAYABLES = "2005";
	
	/**
	 * 应收账款管理
	 */
	const RECEIVING = "2004";
	
	/**
	 * 销售出库
	 */
	const WAREHOUSING_SALE = "2002";
	
	/**
	 * 销售退货入库
	 */
	const SALE_REJECTION = "2006";
	
	/**
	 * 业务设置
	 */
	const BIZ_CONFIG = "2008";
	
	/**
	 * 库间调拨
	 */
	const INVENTORY_TRANSFER = "2009";
	
	/**
	 * 库存盘点
	 */
	const INVENTORY_CHECK = "2010";
	
	/**
	 * 采购退货出库
	 */
	const PURCHASE_REJECTION = "2007";
	
	/**
	 * 首页-销售看板
	 */
	const PORTAL_SALE = "2011-01";
	
	/**
	 * 首页-库存看板
	 */
	const PORTAL_INVENTORY = "2011-02";
	
	/**
	 * 首页-采购看板
	 */
	const PORTAL_PURCHASE = "2011-03";
	
	/**
	 * 首页-资金看板
	 */
	const PORTAL_MONEY = "2011-04";
	
	/**
	 * 销售日报表(按商品汇总)
	 */
	const REPORT_SALE_DAY_BY_GOODS = "2012";
	
	/**
	 * 销售日报表(按客户汇总)
	 */
	const REPORT_SALE_DAY_BY_CUSTOMER = "2013";
	
	/**
	 * 销售日报表(按仓库汇总)
	 */
	const REPORT_SALE_DAY_BY_WAREHOUSE = "2014";
	
	/**
	 * 销售日报表(按业务员汇总)
	 */
	const REPORT_SALE_DAY_BY_BIZUSER = "2015";
	
	/**
	 * 销售月报表(按商品汇总)
	 */
	const REPORT_SALE_MONTH_BY_GOODS = "2016";
	
	/**
	 * 销售月报表(按客户汇总)
	 */
	const REPORT_SALE_MONTH_BY_CUSTOMER = "2017";
	
	/**
	 * 销售月报表(按仓库汇总)
	 */
	const REPORT_SALE_MONTH_BY_WAREHOUSE = "2018";
	
	/**
	 * 销售月报表(按业务员汇总)
	 */
	const REPORT_SALE_MONTH_BY_BIZUSER = "2019";
	
	/**
	 * 安全库存明细表
	 */
	const REPORT_SAFETY_INVENTORY = "2020";
	
	/**
	 * 应收账款账龄分析表
	 */
	const REPORT_RECEIVABLES_AGE = "2021";
	
	/**
	 * 应付账款账龄分析表
	 */
	const REPORT_PAYABLES_AGE = "2022";
	
	/**
	 * 库存超上限明细表
	 */
	const REPORT_INVENTORY_UPPER = "2023";
	
	/**
	 * 现金收支查询
	 */
	const CASH_INDEX = "2024";
	
	/**
	 * 预收款管理
	 */
	const PRE_RECEIVING = "2025";
	
	/**
	 * 预付款管理
	 */
	const PRE_PAYMENT = "2026";
	
	/**
	 * 采购订单
	 */
	const PURCHASE_ORDER = "2027";
	
	/**
	 * 采购订单 - 审核
	 */
	const PURCHASE_ORDER_CONFIRM = "2027-01";
	
	/**
	 * 采购订单 - 生成采购入库单
	 */
	const PURCHASE_ORDER_GEN_PWBILL = "2027-02";
	
	/**
	 * 销售订单
	 */
	const SALE_ORDER = "2028";
	
	/**
	 * 销售订单 - 审核
	 */
	const SALE_ORDER_CONFIRM = "2028-01";
	
	/**
	 * 销售订单 - 生成销售出库单
	 */
	const SALE_ORDER_GEN_WSBILL = "2028-02";
	
	/**
	 * 基础数据 - 商品品牌
	 */
	const GOODS_BRAND = "2029";
}
