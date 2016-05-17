/**
 * 业务日志 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Bizlog.MainForm", {
	extend : "Ext.panel.Panel",

	initComponent : function() {
		var me = this;
		Ext.define("PSILog", {
					extend : "Ext.data.Model",
					fields : ["id", "loginName", "userName", "ip", "ipFrom",
							"content", "dt", "logCategory"],
					idProperty : "id"
				});
		var store = Ext.create("Ext.data.Store", {
					model : "PSILog",
					pageSize : 20,
					proxy : {
						type : "ajax",
						extraParams : {},
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL + "Home/Bizlog/logList",
						reader : {
							root : 'logs',
							totalProperty : 'totalCount'
						}
					},
					autoLoad : true
				});

		var grid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					loadMask : true,
					border : 0,
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 50
									}), {
								text : "登录名",
								dataIndex : "loginName",
								width : 60,
								menuDisabled : true,
								sortable : false
							}, {
								text : "姓名",
								dataIndex : "userName",
								width : 80,
								menuDisabled : true,
								sortable : false
							}, {
								text : "IP",
								dataIndex : "ip",
								width : 120,
								menuDisabled : true,
								sortable : false
							}, {
								text : "IP所属地",
								dataIndex : "ipFrom",
								width : 120,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志分类",
								dataIndex : "logCategory",
								width : 150,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志内容",
								dataIndex : "content",
								flex : 1,
								menuDisabled : true,
								sortable : false
							}, {
								text : "日志记录时间",
								dataIndex : "dt",
								width : 140,
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
					bbar : {
						xtype : "pagingtoolbar",
						store : store
					}
				});

		me.__grid = grid;

		Ext.apply(me, {
					border : 0,
					layout : "border",
					tbar : [{
								text : "刷新",
								handler : me.onRefresh,
								scope : me,
								iconCls : "PSI-button-refresh"
							}, "-", {
								text : "关闭",
								iconCls : "PSI-button-exit",
								handler : function() {
									location.replace(PSI.Const.BASE_URL);
								}
							}, "->", {
								text : "一键升级数据库",
								iconCls : "PSI-button-database",
								scope : me,
								handler : me.onUpdateDatabase
							}],
					items : [{
								region : "center",
								layout : "fit",
								xtype : "panel",
								border : 0,
								items : [grid]
							}]
				});

		me.callParent(arguments);
	},

	/**
	 * 刷新
	 */
	onRefresh : function() {
		Ext.getCmp("pagingToobar").doRefresh();
	},

	/**
	 * 升级数据库
	 */
	onUpdateDatabase : function() {
		var me = this;

		PSI.MsgBox.confirm("请确认是否升级数据库？", function() {
			var el = Ext.getBody();
			el.mask("正在升级数据库，请稍等......");
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/Bizlog/updateDatabase",
						method : "POST",
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									PSI.MsgBox.showInfo("成功升级数据库", function() {
												me.onRefresh();
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
	}
});