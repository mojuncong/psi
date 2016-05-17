/**
 * 销售出库 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Sale.WSMainForm", {
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
					text : "新建销售出库单",
					iconCls : "PSI-button-add",
					scope : me,
					handler : me.onAddBill
				}, "-", {
					text : "编辑销售出库单",
					id : "buttonEdit",
					iconCls : "PSI-button-edit",
					scope : me,
					handler : me.onEditBill
				}, "-", {
					text : "删除销售出库单",
					id : "buttonDelete",
					iconCls : "PSI-button-delete",
					scope : me,
					handler : me.onDeleteBill
				}, "-", {
					text : "提交出库",
					id : "buttonCommit",
					iconCls : "PSI-button-commit",
					scope : me,
					handler : me.onCommit
				}, "-", {
					text : "单据生成pdf",
					id : "buttonPDF",
					iconCls : "PSI-button-pdf",
					scope : me,
					handler : me.onPDF
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
								data : [[-1, "全部"], [0, "待出库"], [1000, "已出库"]]
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
					fieldLabel : "序列号",
					margin : "5, 0, 0, 0",
					labelWidth : 60,
					xtype : "textfield"
				}, {
					id : "editQueryReceivingType",
					margin : "5, 0, 0, 0",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "收款方式",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					store : Ext.create("Ext.data.ArrayStore", {
								fields : ["id", "text"],
								data : [[-1, "全部"], [0, "记应收账款"], [1, "现金收款"],
										[2, "用预收款支付"]]
							}),
					value : -1
				}, {
					xtype : "container",
					items : [{
								xtype : "button",
								text : "查询",
								width : 90,
								margin : "5 0 0 10",
								iconCls : "PSI-button-refresh",
								handler : me.onQuery,
								scope : me
							}, {
								xtype : "button",
								text : "清空查询条件",
								width : 90,
								margin : "5, 0, 0, 10",
								handler : me.onClearQuery,
								scope : me
							}]
				}];
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIWSBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "customerName",
							"warehouseName", "inputUserName", "bizUserName",
							"billStatus", "amount", "dateCreated",
							"receivingType", "memo"]
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
						url : PSI.Const.BASE_URL + "Home/Sale/wsbillList",
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
									return value == "待出库"
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
								width : 300,
								menuDisabled : true,
								sortable : false
							}, {
								header : "收款方式",
								dataIndex : "receivingType",
								menuDisabled : true,
								sortable : false,
								width : 100,
								renderer : function(value) {
									if (value == 0) {
										return "记应收账款";
									} else if (value == 1) {
										return "现金收款";
									} else if (value == 2) {
										return "用预收款支付";
									} else {
										return "";
									}
								}
							}, {
								header : "销售金额",
								dataIndex : "amount",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
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
							}, {
								header : "备注",
								dataIndex : "memo",
								width : 200,
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
											store.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											store.currentPage = 1;
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

		var modelName = "PSIWSBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney",
							"goodsPrice", "sn", "memo"]
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
					title : "销售出库单明细",
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
								header : "数量",
								dataIndex : "goodsCount",
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
								header : "单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
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
							}, {
								header : "备注",
								dataIndex : "memo",
								width : 200,
								menuDisabled : true,
								sortable : false
							}],
					store : store
				});

		return me.__detailGrid;
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("buttonEdit").setDisabled(true);
		Ext.getCmp("buttonDelete").setDisabled(true);
		Ext.getCmp("buttonCommit").setDisabled(true);
		Ext.getCmp("buttonPDF").setDisabled(true);

		var gridDetail = this.getDetailGrid();
		gridDetail.setTitle("销售出库单明细");
		gridDetail.getStore().removeAll();
		Ext.getCmp("pagingToobar").doRefresh();
		this.__lastId = id;
	},

	onAddBill : function() {
		var form = Ext.create("PSI.Sale.WSEditForm", {
					parentForm : this
				});
		form.show();
	},

	onEditBill : function() {
		var item = this.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的销售出库单");
			return;
		}
		var bill = item[0];

		var form = Ext.create("PSI.Sale.WSEditForm", {
					parentForm : this,
					entity : bill
				});
		form.show();
	},

	onDeleteBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的销售出库单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == "已出库") {
			PSI.MsgBox.showInfo("当前销售出库单已经提交出库，不能删除");
			return;
		}

		var info = "请确认是否删除销售出库单: <span style='color:red'>" + bill.get("ref")
				+ "</span>";
		var id = bill.get("id");

		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Sale/deleteWSBill",
								method : "POST",
								params : {
									id : id
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

	onMainGridSelect : function() {
		var me = this;
		me.getDetailGrid().setTitle("销售出库单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);
			Ext.getCmp("buttonPDF").setDisabled(true);
			return;
		}
		var bill = item[0];
		var commited = bill.get("billStatus") == "已出库";

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (commited) {
			buttonEdit.setText("查看销售出库单");
		} else {
			buttonEdit.setText("编辑销售出库单");
		}

		Ext.getCmp("buttonDelete").setDisabled(commited);
		Ext.getCmp("buttonCommit").setDisabled(commited);
		Ext.getCmp("buttonPDF").setDisabled(false);

		me.refreshDetailGrid();
	},

	refreshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("销售出库单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 客户: "
				+ bill.get("customerName") + " 出库仓库: "
				+ bill.get("warehouseName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Sale/wsBillDetailList",
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

	refreshWSBillInfo : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Sale/refreshWSBillInfo",
					method : "POST",
					params : {
						id : bill.get("id")
					},
					callback : function(options, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							bill.set("amount", data.amount);
							me.getMainGrid().getStore().commitChanges();
						}
					}
				});
	},

	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要提交的销售出库单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == "已出库") {
			PSI.MsgBox.showInfo("当前销售出库单已经提交出库，不能再次提交");
			return;
		}

		var detailCount = me.getDetailGrid().getStore().getCount();
		if (detailCount == 0) {
			PSI.MsgBox.showInfo("当前销售出库单没有录入商品明细，不能提交");
			return;
		}

		var info = "请确认是否提交单号: <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售出库单?";
		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在提交中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Sale/commitWSBill",
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
		Ext.getCmp("editQueryReceivingType").setValue(-1);

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

		var receivingType = Ext.getCmp("editQueryReceivingType").getValue();
		result.receivingType = receivingType;

		return result;
	},

	onPDF : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要生成pdf文件的销售出库单");
			return;
		}
		var bill = item[0];

		var url = PSI.Const.BASE_URL + "Home/Sale/pdf?ref=" + bill.get("ref");
		window.open(url);
	}
});