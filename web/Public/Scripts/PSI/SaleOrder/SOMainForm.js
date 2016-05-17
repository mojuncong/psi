/**
 * 销售订单 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.SaleOrder.SOMainForm", {
	extend : "Ext.panel.Panel",

	config : {
		permission : null
	},

	/**
	 * 初始化组件
	 */
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
									columns : 4
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

		var bConfirm = me.getPermission().confirm == "1";
		var tb1 = Ext.getCmp("tbseparator1");
		if (tb1) {
			tb1.setVisible(bConfirm);
		}

		var buttonCommit = Ext.getCmp("buttonCommit");
		if (buttonCommit) {
			buttonCommit.setVisible(bConfirm);
		}

		var buttonCancelConfirm = Ext.getCmp("buttonCancelConfirm");
		if (buttonCancelConfirm) {
			buttonCancelConfirm.setVisible(bConfirm);
		}

		var bGenWSBill = me.getPermission().genWSBill == "1";
		var buttonGenWSBill = Ext.getCmp("buttonGenWSBill");
		if (buttonGenWSBill) {
			buttonGenWSBill.setVisible(bGenWSBill);
		}

		var tb2 = Ext.getCmp("tbseparator2");
		if (tb2) {
			tb2.setVisible(bGenWSBill);
		}

		me.refreshMainGrid();
	},

	/**
	 * 工具栏
	 */
	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "新建销售订单",
					iconCls : "PSI-button-add",
					scope : me,
					handler : me.onAddBill
				}, "-", {
					text : "编辑销售订单",
					iconCls : "PSI-button-edit",
					scope : me,
					handler : me.onEditBill,
					id : "buttonEdit"
				}, "-", {
					text : "删除销售订单",
					iconCls : "PSI-button-delete",
					scope : me,
					handler : me.onDeleteBill,
					id : "buttonDelete"
				}, {
					xtype : "tbseparator",
					id : "tbseparator1"
				}, {
					text : "审核",
					iconCls : "PSI-button-commit",
					scope : me,
					handler : me.onCommit,
					id : "buttonCommit"
				}, {
					text : "取消审核",
					iconCls : "PSI-button-cancelconfirm",
					scope : me,
					handler : me.onCancelConfirm,
					id : "buttonCancelConfirm"
				}, {
					xtype : "tbseparator",
					id : "tbseparator2"
				}, {
					text : "生成销售出库单",
					iconCls : "PSI-button-genbill",
					scope : me,
					handler : me.onGenWSBill,
					id : "buttonGenWSBill"
				}, "-", {
					text : "关闭",
					iconCls : "PSI-button-exit",
					handler : function() {
						location.replace(PSI.Const.BASE_URL);
					}
				}];
	},

	/**
	 * 查询条件
	 */
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
								data : [[-1, "全部"], [0, "待审核"], [1000, "已审核"]]
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
					fieldLabel : "交货日期（起）"
				}, {
					id : "editQueryToDT",
					xtype : "datefield",
					margin : "5, 0, 0, 0",
					format : "Y-m-d",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "交货日期（止）"
				}, {
					id : "editQueryCustomer",
					xtype : "psi_customerfield",
					labelAlign : "right",
					labelSeparator : "",
					labelWidth : 60,
					margin : "5, 0, 0, 0",
					fieldLabel : "客户"
				}, {
					id : "editQueryReceivingType",
					labelAlign : "right",
					labelSeparator : "",
					fieldLabel : "收款方式",
					labelWidth : 60,
					margin : "5, 0, 0, 0",
					xtype : "combo",
					queryMode : "local",
					editable : false,
					valueField : "id",
					store : Ext.create("Ext.data.ArrayStore", {
								fields : ["id", "text"],
								data : [[-1, "全部"], [0, "记应收账款"], [1, "现金收款"]]
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

	/**
	 * 销售订单主表
	 */
	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSISOBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "customerName", "contact", "tel",
							"fax", "inputUserName", "bizUserName",
							"billStatus", "goodsMoney", "dateCreated",
							"receivingType", "tax", "moneyWithTax", "dealDate",
							"dealAddress", "orgName", "confirmUserName",
							"confirmDate", "billMemo"]
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
						url : PSI.Const.BASE_URL + "Home/Sale/sobillList",
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
									if (value == 0) {
										return "<span style='color:red'>待审核</span>";
									} else if (value == 1000) {
										return "已审核";
									} else {
										return "";
									}
								}
							}, {
								header : "销售订单号",
								dataIndex : "ref",
								width : 110,
								menuDisabled : true,
								sortable : false
							}, {
								header : "交货日期",
								dataIndex : "dealDate",
								menuDisabled : true,
								sortable : false
							}, {
								header : "交货地址",
								dataIndex : "dealAddress",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户",
								dataIndex : "customerName",
								width : 300,
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户联系人",
								dataIndex : "contact",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户电话",
								dataIndex : "tel",
								menuDisabled : true,
								sortable : false
							}, {
								header : "客户传真",
								dataIndex : "fax",
								menuDisabled : true,
								sortable : false
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "税金",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "价税合计",
								dataIndex : "moneyWithTax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
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
									} else {
										return "";
									}
								}
							}, {
								header : "业务员",
								dataIndex : "bizUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "组织机构",
								dataIndex : "orgName",
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
								menuDisabled : true,
								sortable : false,
								width : 140
							}, {
								header : "审核人",
								dataIndex : "confirmUserName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "审核时间",
								dataIndex : "confirmDate",
								menuDisabled : true,
								sortable : false,
								width : 140
							}, {
								header : "备注",
								dataIndex : "billMemo",
								menuDisabled : true,
								sortable : false
							}],
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
					}
				});

		return me.__mainGrid;
	},

	/**
	 * 销售订单明细记录
	 */
	getDetailGrid : function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		var modelName = "PSISOBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney",
							"goodsPrice", "taxRate", "tax", "moneyWithTax"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__detailGrid = Ext.create("Ext.grid.Panel", {
					title : "销售订单明细",
					viewConfig : {
						enableTextSelection : true
					},
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
								header : "销售数量",
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
								header : "销售单价",
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
								header : "税率",
								dataIndex : "taxRate",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}, {
								header : "税金",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}, {
								header : "价税合计",
								dataIndex : "moneyWithTax",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 150
							}],
					store : store
				});

		return me.__detailGrid;
	},

	/**
	 * 刷新销售订单主表记录
	 */
	refreshMainGrid : function(id) {
		var me = this;

		Ext.getCmp("buttonEdit").setDisabled(true);
		Ext.getCmp("buttonDelete").setDisabled(true);
		Ext.getCmp("buttonCommit").setDisabled(true);
		Ext.getCmp("buttonCancelConfirm").setDisabled(true);
		Ext.getCmp("buttonGenWSBill").setDisabled(true);

		var gridDetail = me.getDetailGrid();
		gridDetail.setTitle("销售订单明细");
		gridDetail.getStore().removeAll();

		Ext.getCmp("pagingToobar").doRefresh();
		me.__lastId = id;
	},

	/**
	 * 新增销售订单
	 */
	onAddBill : function() {
		var form = Ext.create("PSI.SaleOrder.SOEditForm", {
					parentForm : this
				});
		form.show();
	},

	/**
	 * 编辑销售订单
	 */
	onEditBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要编辑的销售订单");
			return;
		}
		var bill = item[0];

		var form = Ext.create("PSI.SaleOrder.SOEditForm", {
					parentForm : me,
					entity : bill
				});
		form.show();
	},

	/**
	 * 删除销售订单
	 */
	onDeleteBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的销售订单");
			return;
		}

		var bill = item[0];

		if (bill.get("billStatus") > 0) {
			PSI.MsgBox.showInfo("当前销售订单已经审核，不能删除");
			return;
		}

		var store = me.getMainGrid().getStore();
		var index = store.findExact("id", bill.get("id"));
		index--;
		var preIndex = null;
		var preItem = store.getAt(index);
		if (preItem) {
			preIndex = preItem.get("id");
		}

		var info = "请确认是否删除销售订单: <span style='color:red'>" + bill.get("ref")
				+ "</span>";
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在删除中...");
			var r = {
				url : PSI.Const.BASE_URL + "Home/Sale/deleteSOBill",
				method : "POST",
				params : {
					id : bill.get("id")
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成删除操作", function() {
										me.refreshMainGrid(preIndex);
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					} else {
						PSI.MsgBox.showInfo("网络错误");
					}
				}
			};
			Ext.Ajax.request(r);
		};

		PSI.MsgBox.confirm(info, funcConfirm);
	},

	onMainGridSelect : function() {
		var me = this;
		me.getDetailGrid().setTitle("销售订单明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);
			Ext.getCmp("buttonCancelConfirm").setDisabled(true);
			Ext.getCmp("buttonGenWSBill").setDisabled(true);

			return;
		}
		var bill = item[0];
		var commited = bill.get("billStatus") >= 1000;

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (commited) {
			buttonEdit.setText("查看销售订单");
		} else {
			buttonEdit.setText("编辑销售订单");
		}

		Ext.getCmp("buttonDelete").setDisabled(commited);
		Ext.getCmp("buttonCommit").setDisabled(commited);
		Ext.getCmp("buttonCancelConfirm").setDisabled(!commited);
		Ext.getCmp("buttonGenWSBill").setDisabled(!commited);

		me.refreshDetailGrid();
	},

	/**
	 * 刷新销售订单明细记录
	 */
	refreshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("销售订单明细");
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		var grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 客户: "
				+ bill.get("customerName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);

		var r = {
			url : PSI.Const.BASE_URL + "Home/Sale/soBillDetailList",
			params : {
				id : bill.get("id")
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
		};
		Ext.Ajax.request(r);
	},

	/**
	 * 审核销售订单
	 */
	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要审核的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") > 0) {
			PSI.MsgBox.showInfo("当前销售订单已经审核，不能再次审核");
			return;
		}

		var detailCount = me.getDetailGrid().getStore().getCount();
		if (detailCount == 0) {
			PSI.MsgBox.showInfo("当前销售订单没有录入商品明细，不能审核");
			return;
		}

		var info = "请确认是否审核单号: <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售订单?";
		var id = bill.get("id");

		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : PSI.Const.BASE_URL + "Home/Sale/commitSOBill",
				method : "POST",
				params : {
					id : id
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成审核操作", function() {
										me.refreshMainGrid(id);
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					} else {
						PSI.MsgBox.showInfo("网络错误");
					}
				}
			};
			Ext.Ajax.request(r);
		};
		PSI.MsgBox.confirm(info, funcConfirm);
	},

	/**
	 * 取消审核
	 */
	onCancelConfirm : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要取消审核的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") == 0) {
			PSI.MsgBox.showInfo("当前销售订单还没有审核，无法取消审核");
			return;
		}

		var info = "请确认是否取消审核单号为 <span style='color:red'>" + bill.get("ref")
				+ "</span> 的销售订单?";
		var id = bill.get("id");
		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			var r = {
				url : PSI.Const.BASE_URL + "Home/Sale/cancelConfirmSOBill",
				method : "POST",
				params : {
					id : id
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成取消审核操作", function() {
										me.refreshMainGrid(id);
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					} else {
						PSI.MsgBox.showInfo("网络错误");
					}
				}
			};
			Ext.Ajax.request(r);
		};
		PSI.MsgBox.confirm(info, funcConfirm);
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

	/**
	 * 查询
	 */
	onQuery : function() {
		this.refreshMainGrid();
	},

	/**
	 * 清除查询条件
	 */
	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editQueryBillStatus").setValue(-1);
		Ext.getCmp("editQueryRef").setValue(null);
		Ext.getCmp("editQueryFromDT").setValue(null);
		Ext.getCmp("editQueryToDT").setValue(null);
		Ext.getCmp("editQueryCustomer").clearIdValue();
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

		var fromDT = Ext.getCmp("editQueryFromDT").getValue();
		if (fromDT) {
			result.fromDT = Ext.Date.format(fromDT, "Y-m-d");
		}

		var toDT = Ext.getCmp("editQueryToDT").getValue();
		if (toDT) {
			result.toDT = Ext.Date.format(toDT, "Y-m-d");
		}

		var receivingType = Ext.getCmp("editQueryReceivingType").getValue();
		result.receivingType = receivingType;

		return result;
	},

	/**
	 * 生成销售出库单
	 */
	onGenWSBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要生成出库单的销售订单");
			return;
		}
		var bill = item[0];

		if (bill.get("billStatus") != 1000) {
			PSI.MsgBox.showInfo("当前销售订单还没有审核，无法生成销售出库单");
			return;
		}

		var form = Ext.create("PSI.Sale.WSEditForm", {
					genBill : true,
					sobillRef : bill.get("ref")
				});
		form.show();
	}
});