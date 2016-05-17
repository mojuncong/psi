/**
 * 应收账款账龄分析表
 */
Ext.define("PSI.Report.ReceivablesAgeForm", {
	extend : "Ext.panel.Panel",

	border : 0,

	layout : "border",

	initComponent : function() {
		var me = this;

		Ext.apply(me, {
					tbar : [{
								text : "查询",
								iconCls : "PSI-button-refresh",
								handler : me.onQuery,
								scope : me
							}, "-", {
								text : "关闭",
								iconCls : "PSI-button-exit",
								handler : function() {
									location.replace(PSI.Const.BASE_URL);
								}
							}],
					items : [{
								region : "center",
								layout : "fit",
								border : 0,
								items : [me.getMainGrid()]
							}, {
								region : "south",
								layout : "fit",
								border : 0,
								height : 90,
								items : [me.getSummaryGrid()]
							}]
				});

		me.callParent(arguments);
	},

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIReportReceivablesAge";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["caType", "caCode", "caName", "balanceMoney",
							"money30", "money30to60", "money60to90", "money90"]
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
								+ "Home/Report/receivablesAgeQueryData",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
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
								header : "往来单位性质",
								dataIndex : "caType",
								menuDisabled : true,
								sortable : false
							}, {
								header : "往来单位编码",
								dataIndex : "caCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "往来单位",
								dataIndex : "caName",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "当期余额",
								dataIndex : "balanceMoney",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄30天内",
								dataIndex : "money30",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄30-60天",
								dataIndex : "money30to60",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄60-90天",
								dataIndex : "money60to90",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄大于90天",
								dataIndex : "money90",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
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

		var modelName = "PSIReceivablesSummary";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["balanceMoney", "money30", "money30to60",
							"money60to90", "money90"]
				});

		me.__summaryGrid = Ext.create("Ext.grid.Panel", {
					title : "应收账款汇总",
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					border : 0,
					columns : [{
								header : "当期余额",
								dataIndex : "balanceMoney",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄30天内",
								dataIndex : "money30",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄30-60天",
								dataIndex : "money30to60",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄60-90天",
								dataIndex : "money60to90",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "账龄大于90天",
								dataIndex : "money90",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}],
					store : Ext.create("Ext.data.Store", {
								model : modelName,
								autoLoad : false,
								data : []
							})
				});

		return me.__summaryGrid;
	},

	onQuery : function() {
		this.refreshMainGrid();
		this.querySummaryData();
	},

	refreshMainGrid : function(id) {
		Ext.getCmp("pagingToobar").doRefresh();
	},

	querySummaryData : function() {
		var me = this;
		var grid = me.getSummaryGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/Report/receivablesSummaryQueryData",
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
	}
});