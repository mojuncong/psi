/**
 * 预付款管理 - 付供应商预付款
 */
Ext.define("PSI.Funds.AddPrePaymentForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null
	},

	initComponent : function() {
		var me = this;
		Ext.apply(me, {
					title : "预付供应商采购货款",
					modal : true,
					resizable : false,
					onEsc : Ext.emptyFn,
					width : 400,
					height : 200,
					layout : "fit",
					defaultFocus : "editSupplier",
					listeners : {
						show : {
							fn : me.onWndShow,
							scope : me
						}
					},
					items : [{
								id : "editForm",
								xtype : "form",
								layout : {
									type : "table",
									columns : 1
								},
								height : "100%",
								bodyPadding : 5,
								defaultType : 'textfield',
								fieldDefaults : {
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									msgTarget : 'side',
									width : 370,
									margin : "5"
								},
								items : [{
											id : "editSupplierId",
											xtype : "hidden",
											name : "supplierId"
										}, {
											id : "editSupplier",
											fieldLabel : "供应商",
											xtype : "psi_supplierfield",
											allowBlank : false,
											blankText : "没有输入供应商",
											beforeLabelTextTpl : PSI.Const.REQUIRED,
											listeners : {
												specialkey : {
													fn : me.onEditSupplierSpecialKey,
													scope : me
												}
											}
										}, {
											id : "editBizDT",
											fieldLabel : "收款日期",
											allowBlank : false,
											blankText : "没有输入收款日期",
											beforeLabelTextTpl : PSI.Const.REQUIRED,
											xtype : "datefield",
											format : "Y-m-d",
											value : new Date(),
											name : "bizDT",
											listeners : {
												specialkey : {
													fn : me.onEditBizDTSpecialKey,
													scope : me
												}
											}
										}, {
											fieldLabel : "预付金额",
											allowBlank : false,
											blankText : "没有输入预付款金额",
											beforeLabelTextTpl : PSI.Const.REQUIRED,
											xtype : "numberfield",
											hideTrigger : true,
											name : "inMoney",
											id : "editInMoney",
											listeners : {
												specialkey : {
													fn : me.onEditInMoneySpecialKey,
													scope : me
												}
											}
										}, {
											id : "editBizUserId",
											xtype : "hidden",
											name : "bizUserId"
										}, {
											id : "editBizUser",
											fieldLabel : "付款人",
											xtype : "psi_userfield",
											allowBlank : false,
											blankText : "没有输入付款人",
											beforeLabelTextTpl : PSI.Const.REQUIRED,
											listeners : {
												specialkey : {
													fn : me.onEditBizUserSpecialKey,
													scope : me
												}
											}
										}],
								buttons : [{
											text : "保存",
											iconCls : "PSI-button-ok",
											formBind : true,
											handler : me.onOK,
											scope : me
										}, {
											text : "取消",
											handler : function() {
												me.close();
											},
											scope : me
										}]
							}]
				});

		me.callParent(arguments);
	},

	onWndShow : function() {
		var me = this;
		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Funds/addPrePaymentInfo",
					params : {},
					method : "POST",
					callback : function(options, success, response) {
						el.unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);

							Ext.getCmp("editBizUserId")
									.setValue(data.bizUserId);
							Ext.getCmp("editBizUser")
									.setValue(data.bizUserName);
							Ext.getCmp("editBizUser")
									.setIdValue(data.bizUserId);
						} else {
							PSI.MsgBox.showInfo("网络错误")
						}
					}
				});
	},

	// private
	onOK : function() {
		var me = this;
		Ext.getCmp("editBizUserId").setValue(Ext.getCmp("editBizUser")
				.getIdValue());
		Ext.getCmp("editSupplierId").setValue(Ext.getCmp("editSupplier")
				.getIdValue());

		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		f.submit({
					url : PSI.Const.BASE_URL + "Home/Funds/addPrePayment",
					method : "POST",
					success : function(form, action) {
						el.unmask();

						me.close();

						me.getParentForm().onQuery();
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									Ext.getCmp("editBizDT").focus();
								});
					}
				});
	},

	onEditSupplierSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizDT").focus();
		}
	},

	onEditBizDTSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editInMoney").focus();
		}
	},

	onEditInMoneySpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizUser").focus();
		}
	},

	onEditBizUserSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				var me = this;
				PSI.MsgBox.confirm("请确认是否录入付款记录?", function() {
							me.onOK();
						});
			}
		}
	}
});