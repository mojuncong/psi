/**
 * 销售日报表(按仓库汇总)
 */
Ext.define("PSI.Report.SaleDayByWarehouseForm", {
	extend : "Ext.panel.Panel",

	border : 0,

	layout : "border",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					tbar : [{
								text : "关闭",
								iconCls : "PSI-button-exit",
								handler : function() {
									location.replace(PSI.Const.BASE_URL);
								}
							}],
					items : [{
								region : "north",
								height : 60,
								border : 0,
								layout : "fit",
								border : 1,
								title : "查询条件",
								collapsible : true,
								layout : {
									type : "table",
									columns : 4
								},
								items : [{
											id : "editQueryDT",
											xtype : "datefield",
											margin : "5, 0, 0, 0",
											format : "Y-m-d",
											labelAlign : "right",
											labelSeparator : "",
											fieldLabel : "业务日期",
											value : new Date()
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
														text : "重置查询条件",
														width : 100,
														margin : "5, 0, 0, 10",
														handler : me.onClearQuery,
														scope : me
													}]
										}]
							}, {
								region : "center",
								layout : "border",
								border : 0,
								items : [{
											region : "center",
											layout : "fit",
											border : 0,
											items : [me.getMainGrid()]
										}, {
											region : "south",
											layout : "fit",
											height : 100,
											items : [me.getSummaryGrid()]
										}]
							}]
				});

		me.callParent(arguments);
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIReportSaleDayByWarehouse";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["bizDT", "warehouseCode", "warehouseName",
							"saleMoney", "rejMoney", "m", "profit", "rate"]
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
								+ "Home/Report/saleDayByWarehouseQueryData",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								xtype : "rownumberer"
							}, {
								header : "业务日期",
								dataIndex : "bizDT",
								menuDisabled : true,
								sortable : false,
								width : 80
							}, {
								header : "仓库编码",
								dataIndex : "warehouseCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "仓库名称",
								dataIndex : "warehouseName",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "销售出库金额",
								dataIndex : "saleMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "退货入库金额",
								dataIndex : "rejMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "净销售金额",
								dataIndex : "m",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利",
								dataIndex : "profit",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利率",
								dataIndex : "rate",
								menuDisabled : true,
								sortable : false,
								align : "right"
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
					listeners : {}
				});

		return me.__mainGrid;
	},

	getSummaryGrid : function() {
		var me = this;
		if (me.__summaryGrid) {
			return me.__summaryGrid;
		}

		var modelName = "PSIReportSaleDayByWarehouseSummary";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["bizDT", "saleMoney", "rejMoney", "m", "profit",
							"rate"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__summaryGrid = Ext.create("Ext.grid.Panel", {
					title : "日销售汇总",
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								header : "业务日期",
								dataIndex : "bizDT",
								menuDisabled : true,
								sortable : false,
								width : 80
							}, {
								header : "销售出库金额",
								dataIndex : "saleMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "退货入库金额",
								dataIndex : "rejMoney",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "净销售金额",
								dataIndex : "m",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利",
								dataIndex : "profit",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "毛利率",
								dataIndex : "rate",
								menuDisabled : true,
								sortable : false,
								align : "right"
							}],
					store : store
				});

		return me.__summaryGrid;
	},

	onQuery : function() {
		this.refreshMainGrid();
		this.refreshSummaryGrid();
	},

	refreshSummaryGrid : function() {
		var me = this;
		var grid = me.getSummaryGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/Report/saleDayByWarehouseSummaryQueryData",
					params : me.getQueryParam(),
					method : "POST",
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}

						el.unmask();
					}
				});
	},

	onClearQuery : function() {
		var me = this;

		Ext.getCmp("editQueryDT").setValue(new Date());

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {};

		var dt = Ext.getCmp("editQueryDT").getValue();
		if (dt) {
			result.dt = Ext.Date.format(dt, "Y-m-d");
		}

		return result;
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("pagingToobar").doRefresh();
	}
});