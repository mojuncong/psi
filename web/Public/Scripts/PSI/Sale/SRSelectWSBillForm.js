/**
 * 销售退货入库单-选择销售出库单界面
 */
Ext.define("PSI.Sale.SRSelectWSBillForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null
	},

	initComponent : function() {
		var me = this;
		Ext.apply(me, {
					title : "选择销售出库单",
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
								items : [me.getWSBillGrid()]
							}, {
								region : "north",
								border : 0,
								layout : {
									type : "table",
									columns : 2
								},
								height : 180,
								bodyPadding : 10,
								items : [{
											html : "<h1>选择销售出库单</h1>",
											border : 0,
											colspan : 2
										}, {
											id : "editWSRef",
											xtype : "textfield",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "销售出库单单号"
										}, {
											xtype : "psi_customerfield",
											id : "editWSCustomer",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "客户"
										}, {
											id : "editFromDT",
											xtype : "datefield",
											format : "Y-m-d",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "业务日期（起）"
										}, {
											id : "editToDT",
											xtype : "datefield",
											format : "Y-m-d",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "业务日期（止）"
										}, {
											xtype : "psi_warehousefield",
											id : "editWSWarehouse",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "仓库"
										}, {
											id : "editWSSN",
											xtype : "textfield",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "序列号"
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

		var item = me.getWSBillGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择销售出库单");
			return;
		}
		var wsBill = item[0];
		me.close();
		me.getParentForm().getWSBillInfo(wsBill.get("id"));
	},

	getWSBillGrid : function() {
		var me = this;

		if (me.__wsBillGrid) {
			return me.__wsBillGrid;
		}

		var modelName = "PSIWSBill_SRSelectForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "customerName",
							"warehouseName", "inputUserName", "bizUserName",
							"amount"]
				});
		var storeWSBill = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL + "Home/Sale/selectWSBillList",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		storeWSBill.on("beforeload", function() {
					storeWSBill.proxy.extraParams = me.getQueryParam();
				});

		me.__wsBillGrid = Ext.create("Ext.grid.Panel", {
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
						header : "客户",
						dataIndex : "customerName",
						width : 200,
						menuDisabled : true,
						sortable : false
					}, {
						header : "销售金额",
						dataIndex : "amount",
						menuDisabled : true,
						sortable : false,
						align : "right",
						xtype : "numbercolumn",
						width : 80
					}, {
						header : "出库仓库",
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
			store : storeWSBill,
			bbar : [{
						id : "srbill_selectform_pagingToobar",
						xtype : "pagingtoolbar",
						border : 0,
						store : storeWSBill
					}, "-", {
						xtype : "displayfield",
						value : "每页显示"
					}, {
						id : "srbill_selectform_comboCountPerPage",
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
									storeWSBill.pageSize = Ext
											.getCmp("srbill_selectform_comboCountPerPage")
											.getValue();
									storeWSBill.currentPage = 1;
									Ext
											.getCmp("srbill_selectform_pagingToobar")
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

		return me.__wsBillGrid;
	},

	onQuery : function() {
		Ext.getCmp("srbill_selectform_pagingToobar").doRefresh();
	},

	getQueryParam : function() {
		var result = {};

		var ref = Ext.getCmp("editWSRef").getValue();
		if (ref) {
			result.ref = ref;
		}

		var customerId = Ext.getCmp("editWSCustomer").getIdValue();
		if (customerId) {
			result.customerId = customerId;
		}

		var warehouseId = Ext.getCmp("editWSWarehouse").getIdValue();
		if (warehouseId) {
			result.warehouseId = warehouseId;
		}

		var fromDT = Ext.getCmp("editFromDT").getValue();
		if (fromDT) {
			result.fromDT = Ext.Date.format(fromDT, "Y-m-d");
		}

		var toDT = Ext.getCmp("editToDT").getValue();
		if (toDT) {
			result.toDT = Ext.Date.format(toDT, "Y-m-d");
		}

		var sn = Ext.getCmp("editWSSN").getValue();
		if (sn) {
			result.sn = sn;
		}

		return result;
	},

	onClearQuery : function() {
		Ext.getCmp("editWSRef").setValue(null);
		Ext.getCmp("editWSCustomer").clearIdValue();
		Ext.getCmp("editWSWarehouse").clearIdValue();
		Ext.getCmp("editFromDT").setValue(null);
		Ext.getCmp("editToDT").setValue(null);
		Ext.getCmp("editWSSN").setValue(null);

		this.onQuery();
	}
});