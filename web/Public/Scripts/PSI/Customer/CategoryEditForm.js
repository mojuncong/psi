/**
 * 客户分类 - 新增或编辑界面
 */
Ext.define("PSI.Customer.CategoryEditForm", {
			extend : "Ext.window.Window",
			config : {
				parentForm : null,
				entity : null
			},

			initComponent : function() {
				var me = this;
				var entity = me.getEntity();
				me.adding = entity == null;

				var buttons = [];
				if (!entity) {
					buttons.push({
								text : "保存并继续新增",
								formBind : true,
								handler : function() {
									me.onOK(true);
								},
								scope : me
							});
				}

				buttons.push({
							text : "保存",
							formBind : true,
							iconCls : "PSI-button-ok",
							handler : function() {
								me.onOK(false);
							},
							scope : me
						}, {
							text : entity == null ? "关闭" : "取消",
							handler : function() {
								me.close();
							},
							scope : me
						});

				Ext.apply(me, {
							title : entity == null ? "新增客户分类" : "编辑客户分类",
							modal : true,
							resizable : false,
							onEsc : Ext.emptyFn,
							width : 400,
							height : 140,
							layout : "fit",
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
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "editCode",
									fieldLabel : "分类编码",
									allowBlank : false,
									blankText : "没有输入分类编码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "code",
									value : entity == null ? null : entity
											.get("code"),
									listeners : {
										specialkey : {
											fn : me.onEditCodeSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editName",
									fieldLabel : "分类名称",
									allowBlank : false,
									blankText : "没有输入分类名称",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "name",
									value : entity == null ? null : entity
											.get("name"),
									listeners : {
										specialkey : {
											fn : me.onEditNameSpecialKey,
											scope : me
										}
									}
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

			onOK : function(thenAdd) {
				var me = this;
				var f = Ext.getCmp("editForm");
				var el = f.getEl();
				el.mask(PSI.Const.SAVING);
				f.submit({
							url : PSI.Const.BASE_URL
									+ "Home/Customer/editCategory",
							method : "POST",
							success : function(form, action) {
								el.unmask();
								PSI.MsgBox.tip("数据保存成功");
								me.focus();
								me.__lastId = action.result.id;
								if (thenAdd) {
									var editCode = Ext.getCmp("editCode");
									editCode.setValue(null);
									editCode.clearInvalid();
									editCode.focus();

									var editName = Ext.getCmp("editName");
									editName.setValue(null);
									editName.clearInvalid();
								} else {
									me.close();
								}
							},
							failure : function(form, action) {
								el.unmask();
								PSI.MsgBox.showInfo(action.result.msg,
										function() {
											Ext.getCmp("editCode").focus();
										});
							}
						});
			},
			
			onEditCodeSpecialKey : function(field, e) {
				if (e.getKey() == e.ENTER) {
					var editName = Ext.getCmp("editName");
					editName.focus();
					editName.setValue(editName.getValue());
				}
			},
			
			onEditNameSpecialKey : function(field, e) {
				if (e.getKey() == e.ENTER) {
					var f = Ext.getCmp("editForm");
					if (f.getForm().isValid()) {
						this.onOK(this.adding);
					}
				}
			},
			
			onWndClose : function() {
				var me = this;
				if (me.__lastId) {
					me.getParentForm().freshCategoryGrid(me.__lastId);
				}
			},
			
			onWndShow : function() {
				var editCode = Ext.getCmp("editCode");
				editCode.focus();
				editCode.setValue(editCode.getValue());
			}
		});