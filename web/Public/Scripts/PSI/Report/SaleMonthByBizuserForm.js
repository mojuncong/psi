/**
 * 销售月报表(按业务员汇总)
 */
Ext.define("PSI.Report.SaleMonthByBizuserForm", {
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
									id : "editQueryYear",
									xtype : "numberfield",
									margin : "5, 0, 0, 0",
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "年",
									labelWidth : 20,
									width : 100,
									value : (new Date()).getFullYear()
								}, {
									id : "editQueryMonth",
									xtype : "combobox",
									margin : "5, 0, 0, 0",
									labelAlign : "right",
									labelSeparator : "",
									labelWidth : 20,
									fieldLabel : " ",
									store : Ext.create("Ext.data.ArrayStore", {
												fields : ["id", "text"],
												data : [[1, "一月"], [2, "二月"],
														[3, "三月"], [4, "四月"],
														[5, "五月"], [6, "六月"],
														[7, "七月"], [8, "八月"],
														[9, "九月"], [10, "十月"],
														[11, "十一月"],
														[12, "十二月"]]
											}),
									valueField : "id",
									displayFIeld : "text",
									queryMode : "local",
									editable : false,
									value : (new Date()).getMonth() + 1,
									width : 90
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

		var modelName = "PSIReportSaleMonthByBizuser";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["bizDT", "userCode", "userName", "saleMoney",
							"rejMoney", "m", "profit", "rate"]
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
								+ "Home/Report/saleMonthByBizuserQueryData",
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
								header : "月份",
								dataIndex : "bizDT",
								menuDisabled : true,
								sortable : false,
								width : 80
							}, {
								header : "业务员编码",
								dataIndex : "userCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "业务员",
								dataIndex : "userName",
								menuDisabled : true,
								sortable : false
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

		var modelName = "PSIReportSaleMonthByBizuserSummary";
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
					title : "月销售汇总",
					viewConfig : {
						enableTextSelection : true
					},
					border : 0,
					columnLines : true,
					columns : [{
								header : "月份",
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
							+ "Home/Report/saleMonthByBizuserSummaryQueryData",
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

		Ext.getCmp("editQueryYear").setValue((new Date()).getFullYear());
		Ext.getCmp("editQueryMonth").setValue((new Date()).getMonth() + 1);

		me.onQuery();
	},

	getQueryParam : function() {
		var me = this;

		var result = {};

		var year = Ext.getCmp("editQueryYear").getValue();
		if (year) {
			result.year = year;
		} else {
			year = (new Date()).getFullYear();
			Ext.getCmp("editQueryYear").setValue(year);
			result.year = year;
		}

		var month = Ext.getCmp("editQueryMonth").getValue();
		if (month) {
			result.month = month;
		} else {
			month = (new Date()).getMonth() + 1;
			Ext.getCmp("editQueryMonth").setValue(month);
			result.month = month;
		}

		return result;
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("pagingToobar").doRefresh();
	}
});