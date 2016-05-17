/**
 * 采购退货出库单-选择采购入库单界面
 */
Ext.define("PSI.PurchaseRej.PRSelectPWBillForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null
	},
	initComponent : function() {
		var me = this;
		Ext.apply(me, {
					title : "选择采购入库单",
					modal : true,
					onEsc : Ext.emptyFn,
					width : 800,
					height : 500,
					layout : "border",
					items : [{
								region : "center",
								border : 0,
								bodyPadding : 10,
								layout : "fit",
								items : [me.getPWBillGrid()]
							}, {
								region : "north",
								border : 0,
								layout : {
									type : "table",
									columns : 2
								},
								height : 150,
								bodyPadding : 10,
								items : [{
											html : "<h1>选择采购入库单</h1>",
											border : 0,
											colspan : 2
										}, {
											id : "editPWRef",
											xtype : "textfield",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "采购入库单单号"
										}, {
											id : "editPWSupplier",
											xtype : "psi_supplierfield",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "供应商"
										}, {
											id : "editPWFromDT",
											xtype : "datefield",
											format : "Y-m-d",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "业务日期（起）"
										}, {
											id : "editPWToDT",
											xtype : "datefield",
											format : "Y-m-d",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "业务日期（止）"
										}, {
											id : "editPWWarehouse",
											xtype : "psi_warehousefield",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "仓库"
										}, {
											xtype : "container",
											items : [{
														xtype : "button",
														text : "查询",
														width : 100,
														margin : "0 0 0 10",
														iconCls : "PSI-button-refresh",
														handler : me.onQuery,
														scope : me
													}, {
														xtype : "button",
														text : "清空查询条件",
														width : 100,
														margin : "0, 0, 0, 10",
														handler : me.onClearQuery,
														scope : me
													}]
										}]
							}],
					listeners : {
						show : {
							fn : me.onWndShow,
							scope : me
						}
					},
					buttons : [{
								text : "选择",
								iconCls : "PSI-button-ok",
								formBind : true,
								handler : me.onOK,
								scope : me
							}, {
								text : "取消",
								handler : function() {
									me.close();
								},
								scope : me
							}]
				});

		me.callParent(arguments);
	},
	onWndShow : function() {
		var me = this;
	},

	onOK : function() {
		var me = this;

		var item = me.getPWBillGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择采购入库单");
			return;
		}
		var bill = item[0];
		me.close();
		me.getParentForm().getPWBillInfo(bill.get("id"));
	},
	getPWBillGrid : function() {
		var me = this;

		if (me.__billGrid) {
			return me.__billGrid;
		}

		var modelName = "PSIPRBill_PWSelectForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "supplierName",
							"warehouseName", "inputUserName", "bizUserName",
							"amount"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL
								+ "Home/PurchaseRej/selectPWBillList",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});

		me.__billGrid = Ext.create("Ext.grid.Panel", {
			columnLines : true,
			columns : [Ext.create("Ext.grid.RowNumberer", {
								text : "序号",
								width : 50
							}), {
						header : "单号",
						dataIndex : "ref",
						width : 110,
						menuDisabled : true,
						sortable : false
					}, {
						header : "业务日期",
						dataIndex : "bizDate",
						menuDisabled : true,
						sortable : false
					}, {
						header : "供应商",
						dataIndex : "supplierName",
						width : 200,
						menuDisabled : true,
						sortable : false
					}, {
						header : "采购金额",
						dataIndex : "amount",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 80
					}, {
						header : "入库仓库",
						dataIndex : "warehouseName",
						menuDisabled : true,
						sortable : false
					}, {
						header : "业务员",
						dataIndex : "bizUserName",
						menuDisabled : true,
						sortable : false
					}, {
						header : "录单人",
						dataIndex : "inputUserName",
						menuDisabled : true,
						sortable : false
					}],
			listeners : {
				itemdblclick : {
					fn : me.onOK,
					scope : me
				}
			},
			store : store,
			bbar : [{
						id : "prbill_selectform_pagingToobar",
						xtype : "pagingtoolbar",
						border : 0,
						store : store
					}, "-", {
						xtype : "displayfield",
						value : "每页显示"
					}, {
						id : "prbill_selectform_comboCountPerPage",
						xtype : "combobox",
						editable : false,
						width : 60,
						store : Ext.create("Ext.data.ArrayStore", {
									fields : ["text"],
									data : [["20"], ["50"], ["100"], ["300"],
											["1000"]]
								}),
						value : 20,
						listeners : {
							change : {
								fn : function() {
									store.pageSize = Ext
											.getCmp("prbill_selectform_comboCountPerPage")
											.getValue();
									store.currentPage = 1;
									Ext
											.getCmp("prbill_selectform_pagingToobar")
											.doRefresh();
								},
								scope : me
							}
						}
					}, {
						xtype : "displayfield",
						value : "条记录"
					}]
		});

		return me.__billGrid;
	},

	onQuery : function() {
		Ext.getCmp("prbill_selectform_pagingToobar").doRefresh();
	},

	getQueryParam : function() {
		var result = {};

		var ref = Ext.getCmp("editPWRef").getValue();
		if (ref) {
			result.ref = ref;
		}

		var supplierId = Ext.getCmp("editPWSupplier").getIdValue();
		if (supplierId) {
			result.supplierId = supplierId;
		}

		var warehouseId = Ext.getCmp("editPWWarehouse").getIdValue();
		if (warehouseId) {
			result.warehouseId = warehouseId;
		}

		var fromDT = Ext.getCmp("editPWFromDT").getValue();
		if (fromDT) {
			result.fromDT = Ext.Date.format(fromDT, "Y-m-d");
		}

		var toDT = Ext.getCmp("editPWToDT").getValue();
		if (toDT) {
			result.toDT = Ext.Date.format(toDT, "Y-m-d");
		}

		return result;
	},

	onClearQuery : function() {
		Ext.getCmp("editPWRef").setValue(null);
		Ext.getCmp("editPWSupplier").clearIdValue();
		Ext.getCmp("editPWWarehouse").clearIdValue();
		Ext.getCmp("editPWFromDT").setValue(null);
		Ext.getCmp("editPWToDT").setValue(null);

		this.onQuery();
	}
});