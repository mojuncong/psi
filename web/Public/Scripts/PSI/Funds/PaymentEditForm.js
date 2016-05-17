/**
 * 应付账款 - 付款记录
 */
Ext.define("PSI.Funds.PaymentEditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		payDetail : null
	},

	initComponent : function() {
		var me = this;
		Ext.apply(me, {
					title : "录入付款记录",
					modal : true,
					resizable : false,
					onEsc : Ext.emptyFn,
					width : 400,
					height : 230,
					layout : "fit",
					defaultFocus : "editActMoney",
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
											xtype : "hidden",
											name : "refNumber",
											value : me.getPayDetail()
													.get("refNumber")
										}, {
											xtype : "hidden",
											name : "refType",
											value : me.getPayDetail()
													.get("refType")
										}, {
											fieldLabel : "单号",
											xtype : "displayfield",
											value : me.getPayDetail()
													.get("refNumber")
										}, {
											id : "editBizDT",
											fieldLabel : "付款日期",
											allowBlank : false,
											blankText : "没有输入付款日期",
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
											fieldLabel : "付款金额",
											allowBlank : false,
											blankText : "没有输入付款金额",
											beforeLabelTextTpl : PSI.Const.REQUIRED,
											xtype : "numberfield",
											hideTrigger : true,
											name : "actMoney",
											id : "editActMoney",
											listeners : {
												specialkey : {
													fn : me.onEditActMoneySpecialKey,
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
										}, {
											fieldLabel : "备注",
											name : "remark",
											id : "editRemark",
											listeners : {
												specialkey : {
													fn : me.onEditRemarkSpecialKey,
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
					url : PSI.Const.BASE_URL + "Home/Funds/payRecInfo",
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

		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		f.submit({
					url : PSI.Const.BASE_URL + "Home/Funds/addPayment",
					method : "POST",
					success : function(form, action) {
						el.unmask();

						me.close();
						var pf = me.getParentForm();
						pf.refreshPayInfo();
						pf.refreshPayDetailInfo();
						pf.getPayRecordGrid().getStore().loadPage(1);
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									Ext.getCmp("editBizDT").focus();
								});
					}
				});
	},

	onEditBizDTSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editActMoney").focus();
		}
	},

	onEditActMoneySpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizUser").focus();
		}
	},

	onEditBizUserSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editRemark").focus();
		}
	},

	onEditRemarkSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				var me = this;
				PSI.MsgBox.confirm("请确认是否录入收款记录?", function() {
							me.onOK();
						});
			}
		}
	}
});