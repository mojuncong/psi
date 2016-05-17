/**
 * 业务设置 - 主窗体
 */
Ext.define("PSI.BizConfig.MainForm", {
	extend : "Ext.panel.Panel",

	initComponent : function() {
		var me = this;

		var modelName = "PSICompany";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "name"]
				});

		Ext.apply(me, {
			border : 0,
			layout : "border",
			tbar : [{
						xtype : "displayfield",
						value : "公司 "
					}, {
						xtype : "combobox",
						id : "comboCompany",
						queryMode : "local",
						editable : false,
						valueField : "id",
						displayField : "name",
						store : Ext.create("Ext.data.Store", {
									model : modelName,
									autoLoad : false,
									data : []
								}),
						width : 400,
						listeners : {
							select : {
								fn : me.onComboCompanySelect,
								scope : me
							}
						}
					}, {
						text : "设置",
						iconCls : "PSI-button-edit",
						handler : me.onEdit,
						scope : me
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							var url = "http://my.oschina.net/u/134395/blog/378538";
							window.open(url);
						}
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
						xtype : "panel",
						border : 0,
						items : [me.getGrid()]
					}]
		});

		me.callParent(arguments);

		me.queryCompany();
	},

	getGrid : function() {
		var me = this;
		if (me.__grid) {
			return me.__grid;
		}

		var modelName = "PSIBizConfig";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "name", "value", "displayValue", "note"],
					idProperty : "id"
				});
		var store = Ext.create("Ext.data.Store", {
					model : modelName,
					data : [],
					autoLoad : false
				});

		me.__grid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					loadMask : true,
					border : 0,
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 40
									}), {
								text : "设置项",
								dataIndex : "name",
								width : 250,
								menuDisabled : true
							}, {
								text : "值",
								dataIndex : "displayValue",
								width : 500,
								menuDisabled : true
							}, {
								text : "备注",
								dataIndex : "note",
								width : 500,
								menuDisabled : true
							}],
					store : store,
					listeners : {
						itemdblclick : {
							fn : me.onEdit,
							scope : me
						}
					}
				});

		return me.__grid;
	},

	/**
	 * 刷新Grid数据
	 */
	refreshGrid : function(id) {
		var me = this;
		var grid = me.getGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/BizConfig/allConfigs",
					params : {
						companyId : Ext.getCmp("comboCompany").getValue()
					},
					method : "POST",
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);

							if (id) {
								var r = store.findExact("id", id);
								if (r != -1) {
									grid.getSelectionModel().select(r);
								}
							} else {
								grid.getSelectionModel().select(0);
							}
						}

						el.unmask();
					}
				});
	},

	/**
	 * 设置按钮被单击
	 */
	onEdit : function() {
		var companyId = Ext.getCmp("comboCompany").getValue();
		if (!companyId) {
			PSI.MsgBos.showInfo("没有选择要设置的公司");
			return;
		}

		var form = Ext.create("PSI.BizConfig.EditForm", {
					parentForm : this,
					companyId : companyId
				});
		form.show();
	},

	/**
	 * 查询公司信息
	 */
	queryCompany : function() {
		var me = this;
		var el = Ext.getBody();
		var comboCompany = Ext.getCmp("comboCompany");
		var store = comboCompany.getStore();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/BizConfig/getCompany",
					method : "POST",
					callback : function(options, success, response) {
						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
							if (data.length > 0) {
								comboCompany.setValue(data[0]["id"]);
								me.refreshGrid();
							}
						}

						el.unmask();
					}
				});
	},

	onComboCompanySelect : function() {
		this.refreshGrid();
	}
});