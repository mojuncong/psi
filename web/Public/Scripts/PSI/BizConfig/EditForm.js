/**
 * 业务设置 - 编辑设置项目
 */
Ext.define("PSI.BizConfig.EditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		companyId : null
	},

	initComponent : function() {
		var me = this;

		var buttons = [];

		buttons.push({
					text : "保存",
					formBind : true,
					iconCls : "PSI-button-ok",
					handler : function() {
						me.onOK();
					},
					scope : me
				}, {
					text : "取消",
					handler : function() {
						me.close();
					},
					scope : me
				}, {
					text : "帮助",
					iconCls : "PSI-help",
					handler : function() {
						var url = "http://my.oschina.net/u/134395/blog/378538";
						window.open(url);
					}
				});

		var modelName = "PSIWarehouse";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "name"]
				});

		var storePW = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					fields : ["id", "name"],
					data : []
				});
		me.__storePW = storePW;
		var storeWS = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					fields : ["id", "name"],
					data : []
				});
		me.__storeWS = storeWS;

		Ext.apply(me, {
					title : "业务设置",
					modal : true,
					onEsc : Ext.emptyFn,
					width : 500,
					height : 490,
					layout : "fit",
					items : [{
						xtype : "tabpanel",
						bodyPadding : 5,
						items : [{
									title : "公司",
									layout : "form",
									iconCls : "PSI-fid2008",
									items : [{
												id : "editName9000-01",
												xtype : "displayfield"
											}, {
												id : "editValue9000-01",
												xtype : "textfield"
											}, {
												id : "editName9000-02",
												xtype : "displayfield"
											}, {
												id : "editValue9000-02",
												xtype : "textfield"
											}, {
												id : "editName9000-03",
												xtype : "displayfield"
											}, {
												id : "editValue9000-03",
												xtype : "textfield"
											}, {
												id : "editName9000-04",
												xtype : "displayfield"
											}, {
												id : "editValue9000-04",
												xtype : "textfield"
											}, {
												id : "editName9000-05",
												xtype : "displayfield"
											}, {
												id : "editValue9000-05",
												xtype : "textfield"
											}]
								}, {
									title : "采购",
									layout : "form",
									iconCls : "PSI-fid2001",
									items : [{
												id : "editName2001-01",
												xtype : "displayfield"
											}, {
												id : "editValue2001-01",
												xtype : "combo",
												queryMode : "local",
												editable : false,
												valueField : "id",
												displayField : "name",
												store : storePW,
												name : "value2001-01"
											}]
								}, {
									title : "销售",
									layout : "form",
									iconCls : "PSI-fid2002",
									items : [{
												id : "editName2002-02",
												xtype : "displayfield"
											}, {
												id : "editValue2002-02",
												xtype : "combo",
												queryMode : "local",
												editable : false,
												valueField : "id",
												displayField : "name",
												store : storeWS,
												name : "value2002-02"
											}, {
												id : "editName2002-01",
												xtype : "displayfield"
											}, {
												id : "editValue2002-01",
												xtype : "combo",
												queryMode : "local",
												editable : false,
												valueField : "id",
												store : Ext.create(
														"Ext.data.ArrayStore",
														{
															fields : ["id",
																	"text"],
															data : [
																	["0",
																			"不允许编辑销售单价"],
																	["1",
																			"允许编辑销售单价"]]
														}),
												name : "value2002-01"
											}]
								}, {
									title : "存货",
									layout : "form",
									iconCls : "PSI-fid1003",
									items : [{
												id : "editName1003-02",
												xtype : "displayfield"
											}, {
												id : "editValue1003-02",
												xtype : "combo",
												queryMode : "local",
												editable : false,
												valueField : "id",
												store : Ext.create(
														"Ext.data.ArrayStore",
														{
															fields : ["id",
																	"text"],
															data : [
																	["0",
																			"移动平均法"],
																	["1",
																			"先进先出法"]]
														}),
												name : "value1003-01"
											}]
								}, {
									title : "财务",
									iconCls : "PSI-fid2024",
									layout : "form",
									items : [{
												id : "editName9001-01",
												xtype : "displayfield"
											}, {
												id : "editValue9001-01",
												xtype : "numberfield",
												hideTrigger : true,
												allowDecimals : false
											}]
								}, {
									title : "单号前缀",
									layout : "form",
									items : [{
												id : "editName9003-01",
												xtype : "displayfield"
											}, {
												id : "editValue9003-01",
												xtype : "textfield"
											}, {
												id : "editName9003-02",
												xtype : "displayfield"
											}, {
												id : "editValue9003-02",
												xtype : "textfield"
											}, {
												id : "editName9003-03",
												xtype : "displayfield"
											}, {
												id : "editValue9003-03",
												xtype : "textfield"
											}, {
												id : "editName9003-04",
												xtype : "displayfield"
											}, {
												id : "editValue9003-04",
												xtype : "textfield"
											}, {
												id : "editName9003-05",
												xtype : "displayfield"
											}, {
												id : "editValue9003-05",
												xtype : "textfield"
											}, {
												id : "editName9003-06",
												xtype : "displayfield"
											}, {
												id : "editValue9003-06",
												xtype : "textfield"
											}, {
												id : "editName9003-07",
												xtype : "displayfield"
											}, {
												id : "editValue9003-07",
												xtype : "textfield"
											}, {
												id : "editName9003-08",
												xtype : "displayfield"
											}, {
												id : "editValue9003-08",
												xtype : "textfield"
											}]
								}, {
									title : "系统",
									iconCls : "PSI-fid-9994",
									layout : "form",
									items : [{
												id : "editName9002-01",
												xtype : "displayfield"
											}, {
												id : "editValue9002-01",
												xtype : "textfield"
											}]
								}],
						buttons : buttons
					}],
					listeners : {
						close : {
							fn : me.onWndClose,
							scope : me
						},
						show : {
							fn : me.onWndShow,
							scope : me
						}
					}
				});

		me.callParent(arguments);
	},

	getSaveData : function() {
		var me = this;

		var result = {
			companyId : me.getCompanyId(),
			'value9000-01' : Ext.getCmp("editValue9000-01").getValue(),
			'value9000-02' : Ext.getCmp("editValue9000-02").getValue(),
			'value9000-03' : Ext.getCmp("editValue9000-03").getValue(),
			'value9000-04' : Ext.getCmp("editValue9000-04").getValue(),
			'value9000-05' : Ext.getCmp("editValue9000-05").getValue(),
			'value1003-02' : Ext.getCmp("editValue1003-02").getValue(),
			'value2001-01' : Ext.getCmp("editValue2001-01").getValue(),
			'value2002-01' : Ext.getCmp("editValue2002-01").getValue(),
			'value2002-02' : Ext.getCmp("editValue2002-02").getValue(),
			'value9001-01' : Ext.getCmp("editValue9001-01").getValue(),
			'value9002-01' : Ext.getCmp("editValue9002-01").getValue(),
			'value9003-01' : Ext.getCmp("editValue9003-01").getValue(),
			'value9003-02' : Ext.getCmp("editValue9003-02").getValue(),
			'value9003-03' : Ext.getCmp("editValue9003-03").getValue(),
			'value9003-04' : Ext.getCmp("editValue9003-04").getValue(),
			'value9003-05' : Ext.getCmp("editValue9003-05").getValue(),
			'value9003-06' : Ext.getCmp("editValue9003-06").getValue(),
			'value9003-07' : Ext.getCmp("editValue9003-07").getValue(),
			'value9003-08' : Ext.getCmp("editValue9003-08").getValue()
		};

		return result;
	},

	onOK : function(thenAdd) {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/BizConfig/edit",
					method : "POST",
					params : me.getSaveData(),
					callback : function(options, success, response) {
						Ext.getBody().unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							if (data.success) {
								me.__saved = true;
								PSI.MsgBox.showInfo("成功保存数据", function() {
											me.close();
										});
							} else {
								PSI.MsgBox.showInfo(data.msg);
							}
						}
					}
				});
	},

	onWndClose : function() {
		var me = this;
		if (me.__saved) {
			me.getParentForm().refreshGrid();
		}
	},

	onWndShow : function() {
		var me = this;
		me.__saved = false;

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/BizConfig/allConfigsWithExtData",
					params : {
						companyId : me.getCompanyId()
					},
					method : "POST",
					callback : function(options, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							me.__storePW.add(data.extData.warehouse);
							me.__storeWS.add(data.extData.warehouse);

							for (var i = 0; i < data.dataList.length; i++) {
								var item = data.dataList[i];
								var editName = Ext.getCmp("editName" + item.id);
								if (editName) {
									editName.setValue(item.name);
								}
								var editValue = Ext.getCmp("editValue"
										+ item.id);
								if (editValue) {
									editValue.setValue(item.value);
								}
							}
						} else {
							PSI.MsgBox.showInfo("网络错误");
						}

						el.unmask();
					}
				});
	}
});