/**
 * 修改用户密码
 */
Ext.define("PSI.User.ChangeUserPasswordForm", {
	extend : "Ext.window.Window",

	config : {
		entity : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		Ext.apply(me, {
			title : "修改密码",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 400,
			height : 200,
			layout : "fit",
			defaultFocus : "editPassword",
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
							width : 370
						},
						items : [{
									xtype : "hidden",
									name : "id",
									value : entity.id
								}, {
									id : "editLoginName",
									fieldLabel : "登录名",
									value : entity.loginName,
									xtype : "displayfield"
								}, {
									id : "editName",
									fieldLabel : "姓名",
									value : entity.name,
									xtype : "displayfield"
								}, {
									id : "editPassword",
									fieldLabel : "密码",
									allowBlank : false,
									blankText : "没有输入密码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									inputType : "password",
									name : "password",
									listeners : {
										specialkey : {
											fn : me.onEditPasswordSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editConfirmPassword",
									fieldLabel : "确认密码",
									allowBlank : false,
									blankText : "没有输入确认密码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									inputType : "password",
									listeners : {
										specialkey : {
											fn : me.onEditConfirmPasswordSpecialKey,
											scope : me
										}
									}
								}],
						buttons : [{
									text : "确定",
									formBind : true,
									iconCls : "PSI-button-ok",
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

		me.editPassword = Ext.getCmp("editPassword");
		me.editConfirmPassword = Ext.getCmp("editConfirmPassword");
	},

	/**
	 * 修改密码
	 */
	onOK : function() {
		var me = this;
		var pass = me.editPassword.getValue();
		var pass2 = me.editConfirmPassword.getValue();
		if (pass != pass2) {
			PSI.MsgBox.showInfo("输入的密码和确认密码不一致，请重新输入", function() {
						me.editPassword.focus();
					});

			return;
		}

		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : PSI.Const.BASE_URL + "Home/User/changePassword",
					method : "POST",
					success : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo("成功修改密码", function() {
									me.close();
								});
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg);
					}
				});
	},

	onEditPasswordSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editConfirmPassword").focus();
		}
	},

	onEditConfirmPasswordSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				this.onOK();
			}
		}
	}
});