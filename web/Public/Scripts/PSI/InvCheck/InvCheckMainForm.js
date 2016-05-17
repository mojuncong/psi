/**
 * 库存盘点 - 主界面
 */
Ext.define("PSI.InvCheck.InvCheckMainForm", {
	extend : "Ext.panel.Panel",
	border : 0,
	layout : "border",

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					tbar : me.getToolbarCmp(),
					items : [{
								region : "north",
								height : 90,
								layout : "fit",
								border : 1,
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

		me.refreshMainGrid();
	},

	getToolbarCmp : function() {
		var me = this;
		return [{
					text : "新建盘点单",
					iconCls : "PSI-button-add",
					scope : me,
					handler : me.onAddBill
				}, "-", {
					text : "编辑盘点单",
					id : "buttonEdit",
					iconCls : "PSI-button-edit",
					scope : me,
					handler : me.onEditBill
				}, "-", {
					text : "删除盘点单",
					id : "buttonDelete",
					iconCls : "PSI-button-delete",
					scope : me,
					handler : me.onDeleteBill
				}, "-", {
					text : "提交盘点单",
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
								data : [[-1, "全部"], [0, "待盘点"], [1000, "已盘点"]]
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
					id : "editQueryWarehouse",
					xtype : "psi_warehousefield",
					labelAlign : "right",
					labelSeparator : "",
					labelWidth : 60,
					margin : "5, 0, 0, 0",
					fieldLabel : "仓库"
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

		var me = this;
		var gridDetail = me.getDetailGrid();
		gridDetail.setTitle("盘点单明细");
		gridDetail.getStore().removeAll();
		Ext.getCmp("pagingToobar").doRefresh();
		me.__lastId = id;
	},

	/**
	 * 新增盘点单
	 */
	onAddBill : function() {
		var form = Ext.create("PSI.InvCheck.ICEditForm", {
					parentForm : this
				});
		form.show();
	},

	/**
	 * 编辑盘点单
	 */
	onEditBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的盘点单");
			return;
		}
		var bill = item[0];

		var form = Ext.create("PSI.InvCheck.ICEditForm", {
					parentForm : me,
					entity : bill
				});
		form.show();
	},

	/**
	 * 删除盘点单
	 */
	onDeleteBill : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的盘点单");
			return;
		}
		var bill = item[0];

		var info = "请确认是否删除盘点单: <span style='color:red'>" + bill.get("ref")
				+ "</span>";

		PSI.MsgBox.confirm(info, function() {
			var el = Ext.getBody();
			el.mask("正在删除中...");
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/InvCheck/deleteICBill",
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
									PSI.MsgBox.showInfo("成功完成删除操作", function() {
												me.refreshMainGrid();
											});
								} else {
									PSI.MsgBox.showInfo(data.msg);
								}
							} else {
								PSI.MsgBox.showInfo("网络错误");
							}
						}

					});
		});
	},

	/**
	 * 提交盘点单
	 */
	onCommit : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择要提交的盘点单");
			return;
		}
		var bill = item[0];

		var detailCount = me.getDetailGrid().getStore().getCount();
		if (detailCount == 0) {
			PSI.MsgBox.showInfo("当前盘点单没有录入明细，不能提交");
			return;
		}

		var info = "请确认是否提交单号: <span style='color:red'>" + bill.get("ref")
				+ "</span> 的盘点单?";
		PSI.MsgBox.confirm(info, function() {
			var el = Ext.getBody();
			el.mask("正在提交中...");
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/InvCheck/commitICBill",
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
									PSI.MsgBox.showInfo("成功完成提交操作", function() {
												me.refreshMainGrid(data.id);
											});
								} else {
									PSI.MsgBox.showInfo(data.msg);
								}
							} else {
								PSI.MsgBox.showInfo("网络错误");
							}
						}
					});
		});
	},

	/**
	 * 盘点单主表
	 */
	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIICBill";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "ref", "bizDate", "warehouseName",
							"inputUserName", "bizUserName", "billStatus",
							"dateCreated"]
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
						url : PSI.Const.BASE_URL + "Home/InvCheck/icbillList",
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
									return value == "待盘点"
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
								header : "盘点仓库",
								dataIndex : "warehouseName",
								menuDisabled : true,
								sortable : false,
								width : 150
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

	/**
	 * 盘点单明细记录Grid
	 */
	getDetailGrid : function() {
		var me = this;
		if (me.__detailGrid) {
			return me.__detailGrid;
		}

		var modelName = "PSIICBillDetail";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsSpec",
							"unitName", "goodsCount", "goodsMoney"]
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
					title : "盘点单明细",
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
								header : "盘点后库存数量",
								dataIndex : "goodsCount",
								menuDisabled : true,
								sortable : false,
								align : "right",
								width : 150
							}, {
								header : "单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "盘点后库存金额",
								dataIndex : "goodsMoney",
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
		me.getDetailGrid().setTitle("盘点单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			Ext.getCmp("buttonEdit").setDisabled(true);
			Ext.getCmp("buttonDelete").setDisabled(true);
			Ext.getCmp("buttonCommit").setDisabled(true);
			return;
		}
		var bill = item[0];
		var commited = bill.get("billStatus") == "已盘点";

		var buttonEdit = Ext.getCmp("buttonEdit");
		buttonEdit.setDisabled(false);
		if (commited) {
			buttonEdit.setText("查看盘点单");
		} else {
			buttonEdit.setText("编辑盘点单");
		}

		Ext.getCmp("buttonDelete").setDisabled(commited);
		Ext.getCmp("buttonCommit").setDisabled(commited);

		me.refreshDetailGrid();
	},

	refreshDetailGrid : function(id) {
		var me = this;
		me.getDetailGrid().setTitle("盘点单明细");
		var grid = me.getMainGrid();
		var item = grid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var bill = item[0];

		grid = me.getDetailGrid();
		grid.setTitle("单号: " + bill.get("ref") + " 盘点仓库: "
				+ bill.get("warehouseName"));
		var el = grid.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/InvCheck/icBillDetailList",
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
				});
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
		Ext.getCmp("editQueryWarehouse").clearIdValue();

		me.onQuery();
	},

	/**
	 * 查询参数
	 */
	getQueryParam : function() {
		var me = this;

		var result = {
			billStatus : Ext.getCmp("editQueryBillStatus").getValue()
		};

		var ref = Ext.getCmp("editQueryRef").getValue();
		if (ref) {
			result.ref = ref;
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

		return result;
	}
});