/**
 * 销售退货入库 - 主界面
 */
Ext.define("PSI.Sale.SRMainForm", {
	extend : "Ext.panel.Panel",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					border : 0,
					layout : "border",
					tbar : me.getToolbarCmp(),
					items : [{
								region : "north",
								height : 90,
								layout : "fit",
								border : 0,
								title : "查询条件",
								collapsible : true,
								layout : {
									type : "table",
									columns : 5
								},
								items : me.getQueryCmp()
							}, {
								region : "center",
								layout : "border",
								border : 0,
								items : [{
											region : "north",
											height : "40%",
											split : true,
											layout : "fit",
											border : 0,
											items : [me.getMainGrid()]
										}, {
											region : "center",
											layout : "fit",
											border : 0,
											items : [me.getDetailGrid()]
										}]
							}]
				});

		me.callParent(arguments);

		me.refreshMainGrid();
	},

	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "新建销售退货入库单",
					iconCls : "PSI-button-add",
					scope : me,
					handler : me.onAddBill
				}, "-", {
					text : "编辑销售退货入库单",
					id : "buttonEdit",
					iconCls : "PSI-button-edit",
					scope : me,
					handler : me.onEditBill
				}, "-", {
					text : "删除销售退货入库单",
					id : "buttonDelete",
					iconCls : "PSI-button-delete",
					scope : me,
					handler : me.onDeleteBill
				}, "-", {
					text : "提交入库",
					id : "buttonCommit",
					iconCls : "PSI-button-commit",
					scope : me,
					handler : me.onCommit
				}, "-", {
					text : "关闭",
					iconCls : "PSI-button-exit",
					handler : function() {
						location.replace(PSI.Const.BASE_URL);
					}
				}];
	},

	getQueryCmp : function() {
		var me = this;
		return [{
					id : "editQueryBillStatus",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "状态",
					margin : "5, 0, 0, 0",
					store : Ext.create("Ext.data.ArrayStore", {
								fields : ["id", "text"],
								data : [[-1, "全部"], [0, "待入库"], [1000, "已入库"]]
							}),
					value : -1
				}, {
					id : "editQueryRef",
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "单号",
					margin : "5, 0, 0, 0",
					xtype : "textfield"
				}, {
					id : "editQueryFromDT",
					xtype : "datefield",
					margin : "5, 0, 0, 0",
					format : "Y-m-d",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "业务日期（起）"
				}, {
					id : "editQueryToDT",
					xtype : "datefield",
					margin : "5, 0, 0, 0",
					format : "Y-m-d",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "业务日期（止）"
				}, {
					id : "editQueryCustomer",
					xtype : "psi_customerfield",
					labelAlign : "right",
					labelSeparator : "",
					labelWidth : 60,
					margin : "5, 0, 0, 0",
					fieldLabel : "客户"
				}, {
					id : "editQueryWarehouse",
					xtype : "psi_warehousefield",
					labelAlign : "right",
					labelSeparator : "",
					labelWidth : 60,
					margin : "5, 0, 0, 0",
					fieldLabel : "仓库"
				}, {
					id : "editQuerySN",
					labelAlign : "right",
					labelSeparator : "",
					labelWidth : 60,
					fieldLabel : "序列号",
					margin : "5, 0, 0, 0",
					xtype : "textfield"
				}, {
					id : "editQueryPaymentType",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "付款方式",
					margin : "5, 0, 0, 0",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					store : Ext.create("Ext.data.ArrayStore", {
								fields : ["id", "text"],
								data : [[-1, "全部"], [0, "记应付账款"], [1, "现金付款"],
										[2, "退款转入预收款"]]
							}),
					value : -1
				}, {
					xtype : "container",
					items : [{
								xtype : "button",
								text : "查询",
								width : 100,
								margin : "5 0 0 10",
								iconCls : "PSI-button-refresh",
								handler : me.onQuery,
								scope : me
							}, {
								xtype : "button",
								text : "清空查询条件",
								width : 100,
								margin : "5, 0, 0, 10",
								handler : me.onClearQuery,
								scope : me
							}]
				}];
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("buttonEdit").setDisabled(true);
		Ext.getCmp("buttonDelete").setDisabled(true);
		Ext.getCmp("buttonCommit").setDisabled(true);

		var gridDetail = this.getDetailGrid();
		gridDetail.setTitle("销售退货入库单明细");
		gridDetail.getStore().removeAll();
		Ext.getCmp("pagingToobar").doRefresh();
		this.__lastId = id;
	},

	// 新增销售退货入库单
	onAddBill : function() {
		var form = Ext.create("PSI.Sale.SREditForm", {
					parentForm : this
				});
		form.show();
	},

	// 编辑销售退货入库单
	onEditBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的销售退货入库单");
			return;
		}
		var bill = item[0];

		var form = Ext.create("PSI.Sale.SREditForm", {
					parentForm : me,
					entity : bill
				});
		form.show();
	},

	// 删除销售退货入库单
	onDeleteBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的销售退货入库单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == "已入库") {
			PSI.MsgBox.showInfo("当前销售退货入库单已经提交入库，不能删除");
			return;
		}

		var info = "请确认是否删除销售退货入库单: <span style='color:red'>" + bill.get("ref")
				+ "</span>";
		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Sale/deleteSRBill",
								method : "POST",
								params : {
									id : bill.get("id")
								},
								callback : function(options, success, response) {
									el.unmask();

									if (success) {
										var data = Ext.JSON
												.decode(response.responseText);
										if (data.success) {
											PSI.MsgBox.showInfo("成功完成删除操作",
													function() {
														me.refreshMainGrid();
													});
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									} else {
										PSI.MsgBox.showInfo("网络错误", function() {
													window.location.reload();
												});
									}
								}
							});
				});
	},

	// 提交销售退货入库单
	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要提交的销售退货入库单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == "已入库") {
			PSI.MsgBox.showInfo("当前销售退货入库单已经提交入库，不能再次提交");
			return;
		}

		var info = "请确认是否提交单号: <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售退货入库单?";
		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在提交中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Sale/commitSRBill",
								method : "POST",
								params : {
									id : bill.get("id")
								},
								callback : function(options, success, response) {
									el.unmask();

									if (success) {
										var data = Ext.JSON
												.decode(response.responseText);
										if (data.success) {
											PSI.MsgBox.showInfo("成功完成提交操作",
													function() {
														me
																.refreshMainGrid(data.id);
													});
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									} else {
										PSI.MsgBox.showInfo("网络错误", function() {
													window.location.reload();
												});
									}
								}
							});
				});
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSISRBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "customerName",
							"warehouseName", "inputUserName", "bizUserName",
							"billStatus", "amount", "dateCreated",
							"paymentType"]
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
						url : PSI.Const.BASE_URL + "Home/Sale/srbillList",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});
		store.on("load", function(e, records, successful) {
					if (successful) {
						me.gotoMainGridRecord(me.__lastId);
					}
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								xtype : "rownumberer",
								width : 50
							}, {
								header : "状态",
								dataIndex : "billStatus",
								menuDisabled : true,
								sortable : false,
								width : 60,
								renderer : function(value) {
									return value == "待入库"
											? "<span style='color:red'>"
													+ value + "</span>"
											: value;
								}
							}, {
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
								header : "退货金额",
								dataIndex : "amount",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 80
							}, {
								header : "付款方式",
								dataIndex : "paymentType",
								menuDisabled : true,
								sortable : false,
								width : 100,
								renderer : function(value) {
									if (value == 0) {
										return "记应付账款";
									} else if (value == 1) {
										return "现金付款";
									} else if (value == 2) {
										return "退款转入预收款";
									} else {
										return "";
									}
								}
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
								header : "制单人",
								dataIndex : "inputUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "制单时间",
								dataIndex : "dateCreated",
								width : 140,
								menuDisabled : true,
								sortable : false
							}],
					listeners : {
						select : {
							fn : me.onMainGridSelect,
							scope : me
						},
						itemdblclick : {
							fn : me.onEditBill,
							scope : me
						}
					},
					store : store,
					tbar : [{
								id : "pagingToobar",
								xtype : "pagingtoolbar",
								border : 0,
								store : store
							}, "-", {
								xtype : "displayfield",
								value : "每页显示"
							}, {
								id : "comboCountPerPage",
								xtype : "combobox",
								editable : false,
								width : 60,
								store : Ext.create("Ext.data.ArrayStore", {
											fields : ["text"],
											data : [["20"], ["50"], ["100"],
													["300"], ["1000"]]
										}),
								value : 20,
								listeners : {
									change : {
										fn : function() {
											storeWSBill.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											storeWSBill.currentPage = 1;
											Ext.getCmp("pagingToobar")
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

		return me.__mainGrid;
	},

	getDetailGrid : function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		var modelName = "PSISRBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney",
							"goodsPrice", "rejCount", "rejPrice",
							"rejSaleMoney", "sn"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__detailGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "销售退货入库单明细",
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 30
									}), {
								header : "商品编码",
								dataIndex : "goodsCode",
								menuDisabled : true,
								sortable : false,
								width : 120
							}, {
								header : "商品名称",
								dataIndex : "goodsName",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "规格型号",
								dataIndex : "goodsSpec",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "退货数量",
								dataIndex : "rejCount",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "退货单价",
								dataIndex : "rejPrice",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "退货金额",
								dataIndex : "rejSaleMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "序列号",
								dataIndex : "sn",
								menuDisabled : true,
								sortable : false
							}],
					store : store
				});

		return me.__detailGrid;
	},

	gotoMainGridRecord : function(id) {
		var me = this;
		var grid = me.getMainGrid();
		grid.getSelectionModel().deselectAll();
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		} else {
			grid.getSelectionModel().select(0);
		}
	},

	onMainGridSelect : function() {
		var me = this;
		me.getDetailGrid().setTitle("销售退货入库单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);
			return;
		}
		var bill = item[0];

		var commited = bill.get("billStatus") == "已入库";

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (commited) {
			buttonEdit.setText("查看销售退货入库单");
		} else {
			buttonEdit.setText("编辑销售退货入库单");
		}

		Ext.getCmp("buttonDelete").setDisabled(commited);
		Ext.getCmp("buttonCommit").setDisabled(commited);

		me.freshDetailGrid();
	},

	freshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("销售退货入库单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 客户: "
				+ bill.get("customerName") + " 入库仓库: "
				+ bill.get("warehouseName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Sale/srBillDetailList",
					params : {
						billId : bill.get("id")
					},
					method : "POST",
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);

							if (store.getCount() > 0) {
								if (id) {
									var r = store.findExact("id", id);
									if (r != -1) {
										grid.getSelectionModel().select(r);
									}
								}
							}
						}

						el.unmask();
					}
				});
	},

	onQuery : function() {
		this.refreshMainGrid();
	},

	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editQueryBillStatus").setValue(-1);
		Ext.getCmp("editQueryRef").setValue(null);
		Ext.getCmp("editQueryFromDT").setValue(null);
		Ext.getCmp("editQueryToDT").setValue(null);
		Ext.getCmp("editQueryCustomer").clearIdValue();
		Ext.getCmp("editQueryWarehouse").clearIdValue();
		Ext.getCmp("editQuerySN").setValue(null);
		Ext.getCmp("editQueryPaymentType").setValue(-1);

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {
			billStatus : Ext.getCmp("editQueryBillStatus").getValue()
		};

		var ref = Ext.getCmp("editQueryRef").getValue();
		if (ref) {
			result.ref = ref;
		}

		var customerId = Ext.getCmp("editQueryCustomer").getIdValue();
		if (customerId) {
			result.customerId = customerId;
		}

		var warehouseId = Ext.getCmp("editQueryWarehouse").getIdValue();
		if (warehouseId) {
			result.warehouseId = warehouseId;
		}

		var fromDT = Ext.getCmp("editQueryFromDT").getValue();
		if (fromDT) {
			result.fromDT = Ext.Date.format(fromDT, "Y-m-d");
		}

		var toDT = Ext.getCmp("editQueryToDT").getValue();
		if (toDT) {
			result.toDT = Ext.Date.format(toDT, "Y-m-d");
		}

		var sn = Ext.getCmp("editQuerySN").getValue();
		if (sn) {
			result.sn = sn;
		}

		var paymentType = Ext.getCmp("editQueryPaymentType").getValue();
		result.paymentType = paymentType;

		return result;
	}
});