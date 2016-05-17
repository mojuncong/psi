/**
 * 商品分类 - 编辑界面
 */
Ext.define("PSI.Goods.CategoryEditForm", {
			extend : "Ext.window.Window",

			config : {
				parentForm : null,
				entity : null
			},

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;
				var entity = me.getEntity();

				me.__lastId = entity == null ? null : entity.get("id");

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
							title : entity == null ? "新增商品分类" : "编辑商品分类",
							modal : true,
							resizable : false,
							onEsc : Ext.emptyFn,
							width : 400,
							height : 160,
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
									width : 370
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
								}, {
									id : "editParentCategory",
									fieldLabel : "上级分类",
									xtype : "psi_goodsparentcategoryfield",
									listeners : {
										specialkey : {
											fn : me.onEditCategorySpecialKey,
											scope : me
										}
									}
								}, {
									id : "editParentCategoryId",
									xtype : "hidden",
									name : "parentId"
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

				Ext.getCmp("editParentCategoryId").setValue(Ext
						.getCmp("editParentCategory").getIdValue());

				var f = Ext.getCmp("editForm");
				var el = f.getEl();
				el.mask(PSI.Const.SAVING);
				f.submit({
							url : PSI.Const.BASE_URL
									+ "Home/Goods/editCategory",
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
					Ext.getCmp("editName").focus();
				}
			},

			onEditNameSpecialKey : function(field, e) {
				if (e.getKey() == e.ENTER) {
					Ext.getCmp("editParentCategory").focus();
				}
			},

			onEditCategorySpecialKey : function(field, e) {
				if (e.getKey() == e.ENTER) {
					var f = Ext.getCmp("editForm");
					if (f.getForm().isValid()) {
						Ext.getCmp("editCode").focus();
						var me = this;
						me.onOK(me.adding);
					}
				}
			},

			onWndClose : function() {
				if (this.__lastId) {
					this.getParentForm().freshCategoryGrid();
				}
			},

			/**
			 * 窗体显示的时候查询数据
			 */
			onWndShow : function() {
				var me = this;
				var editCode = Ext.getCmp("editCode");
				editCode.focus();
				editCode.setValue(editCode.getValue());

				if (!me.getEntity()) {
					return;
				}

				var el = me.getEl();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL
									+ "Home/Goods/getCategoryInfo",
							params : {
								id : me.getEntity().get("id")
							},
							method : "POST",
							callback : function(options, success, response) {
								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);

									if (data.code) {
										Ext.getCmp("editCode")
												.setValue(data.code);
										Ext.getCmp("editName")
												.setValue(data.name);
										Ext.getCmp("editParentCategory")
												.setIdValue(data.parentId);
										Ext.getCmp("editParentCategory")
												.setValue(data.parentName);
									}
								}

								el.unmask();
							}
						});
			}
		});