/**
 * 新增或编辑用户界面
 */
Ext.define("PSI.User.UserEditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		entity : null,
		defaultOrg : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		var entity = me.getEntity();

		Ext.apply(me, {
			title : entity === null ? "新增用户" : "编辑用户",
			modal : true,
			onEsc : Ext.emptyFn,
			width : 470,
			height : 260,
			layout : "fit",
			defaultFocus : "editLoginName",
			items : [{
				id : "editForm",
				xtype : "form",
				layout : {
					type : "table",
					columns : 2
				},
				height : "100%",
				bodyPadding : 5,
				defaultType : 'textfield',
				fieldDefaults : {
					labelWidth : 60,
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side'
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity === null ? null : entity.id
						}, {
							id : "editLoginName",
							fieldLabel : "登录名",
							allowBlank : false,
							blankText : "没有输入登录名",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "loginName",
							value : entity === null ? null : entity.loginName,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editName",
							fieldLabel : "姓名",
							allowBlank : false,
							blankText : "没有输入姓名",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "name",
							value : entity === null ? null : entity.name,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editOrgCode",
							fieldLabel : "编码",
							allowBlank : false,
							blankText : "没有输入编码",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "orgCode",
							value : entity === null ? null : entity.orgCode,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							},
							colspan : 2
						}, {
							id : "editOrgName",
							xtype : "PSI_org_editor",
							fieldLabel : "所属组织",
							allowBlank : false,
							blankText : "没有选择组织机构",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							parentItem : this,
							value : entity === null ? null : entity.orgName,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							},
							colspan : 2,
							width : 430
						}, {
							id : "editOrgId",
							xtype : "hidden",
							name : "orgId",
							value : entity === null ? null : entity.orgId
						}, {
							id : "editBirthday",
							fieldLabel : "生日",
							xtype : "datefield",
							format : "Y-m-d",
							name : "birthday",
							value : entity === null ? null : entity.birthday,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editIdCardNumber",
							fieldLabel : "身份证号",
							name : "idCardNumber",
							value : entity === null
									? null
									: entity.idCardNumber,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editTel",
							fieldLabel : "联系电话",
							name : "tel",
							value : entity === null ? null : entity.tel,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editTel02",
							fieldLabel : "备用电话",
							name : "tel02",
							value : entity === null ? null : entity.tel02,
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editAddress",
							fieldLabel : "家庭住址",
							name : "address",
							value : entity === null ? null : entity.address,
							listeners : {
								specialkey : {
									fn : me.onLastEditSpecialKey,
									scope : me
								}
							},
							colspan : 2,
							width : 430
						}, {
							xtype : "radiogroup",
							fieldLabel : "性别",
							columns : 2,
							items : [{
								boxLabel : "男 ",
								name : "gender",
								inputValue : "男",
								checked : entity === null
										? true
										: entity.gender == "男"
							}, {
								boxLabel : "女 ",
								name : "gender",
								inputValue : "女",
								checked : entity === null
										? false
										: entity.gender == "女"
							}],
							width : 200
						}, {
							xtype : "radiogroup",
							fieldLabel : "能否登录",
							columns : 2,
							items : [{
								boxLabel : "允许登录",
								name : "enabled",
								inputValue : true,
								checked : entity === null
										? true
										: entity.enabled == 1
							}, {
								boxLabel : "<span style='color:red'>禁止登录</span>",
								name : "enabled",
								inputValue : false,
								checked : entity === null
										? false
										: entity.enabled != 1
							}],
							width : 240
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
								PSI.MsgBox.confirm("请确认是否取消操作?", function() {
											me.close();
										});
							},
							scope : me
						}]
			}]
		});

		me.callParent(arguments);

		me.__editorList = ["editLoginName", "editName", "editOrgCode",
				"editOrgName", "editBirthday", "editIdCardNumber", "editTel",
				"editTel02", "editAddress"];

		if (me.getDefaultOrg()) {
			var org = me.getDefaultOrg();
			me.setOrg({
						id : org.get("id"),
						fullName : org.get("fullName")
					});
		}
	},

	setOrg : function(data) {
		var editOrgName = Ext.getCmp("editOrgName");
		editOrgName.setValue(data.fullName);

		var editOrgId = Ext.getCmp("editOrgId");
		editOrgId.setValue(data.id);
	},

	onOK : function() {
		var me = this;
		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : PSI.Const.BASE_URL + "Home/User/editUser",
					method : "POST",
					success : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo("数据保存成功", function() {
									me.close();
									me.getParentForm().freshUserGrid();
								});
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									Ext.getCmp("editName").focus();
								});
					}
				});
	},

	onEditSpecialKey : function(field, e) {
		if (e.getKey() === e.ENTER) {
			var me = this;
			var id = field.getId();
			for (var i = 0; i < me.__editorList.length; i++) {
				var editorId = me.__editorList[i];
				if (id === editorId) {
					var edit = Ext.getCmp(me.__editorList[i + 1]);
					edit.focus();
					edit.setValue(edit.getValue());
				}
			}
		}
	},

	onLastEditSpecialKey : function(field, e) {
		if (e.getKey() === e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				this.onOK();
			}
		}
	}
});