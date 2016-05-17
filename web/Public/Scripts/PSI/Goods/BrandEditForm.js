/**
 * 新增或编辑商品品牌
 */
Ext.define("PSI.Goods.BrandEditForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null,
		entity : null
	},

	getBaseURL : function() {
		return PSI.Const.BASE_URL;
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		Ext.apply(me, {
			title : entity === null ? "新增商品品牌" : "编辑商品品牌",
			modal : true,
			resizable : false,
			onEsc : Ext.emptyFn,
			width : 400,
			height : 140,
			layout : "fit",
			defaultFocus : "editName",
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
					labelWidth : 50,
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side'
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity === null ? null : entity.get("id")
						}, {
							id : "editName",
							fieldLabel : "品牌",
							allowBlank : false,
							blankText : "没有输入品牌",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "name",
							value : entity === null ? null : entity.get("text"),
							listeners : {
								specialkey : {
									fn : me.onEditNameSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							id : "editParentBrand",
							xtype : "PSI_parent_brand_editor",
							parentItem : me,
							fieldLabel : "上级品牌",
							listeners : {
								specialkey : {
									fn : me.onEditParentOrgSpecialKey,
									scope : me
								}
							},
							width : 370
						}, {
							id : "editParentBrandId",
							xtype : "hidden",
							name : "parentId",
							value : entity === null ? null : entity
									.get("parentId")
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
			}],
			listeners : {
				show : {
					fn : me.onEditFormShow,
					scope : me
				}
			}
		});

		me.callParent(arguments);
	},

	onEditFormShow : function() {
		var me = this;

		var entity = this.getEntity();
		if (entity === null) {
			return;
		}

		me.getEl().mask("数据加载中...");
		Ext.Ajax.request({
					url : me.getBaseURL() + "Home/Goods/brandParentName",
					method : "POST",
					params : {
						id : entity.get("id")
					},
					callback : function(options, success, response) {
						me.getEl().unmask();
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							Ext.getCmp("editParentBrand")
									.setValue(data.parentBrandName);
							Ext.getCmp("editParentBrandId")
									.setValue(data.parentBrandId);
							Ext.getCmp("editName").setValue(data.name);
						}
					}
				});
	},

	setParentBrand : function(data) {
		var editParentBrand = Ext.getCmp("editParentBrand");
		editParentBrand.setValue(data.fullName);
		var editParentBrandId = Ext.getCmp("editParentBrandId");
		editParentBrandId.setValue(data.id);
	},

	onOK : function() {
		var me = this;
		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask("数据保存中...");
		f.submit({
					url : me.getBaseURL() + "Home/Goods/editBrand",
					method : "POST",
					success : function(form, action) {
						el.unmask();
						me.close();
						me.getParentForm().refreshGrid();
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									Ext.getCmp("editName").focus();
								});
					}
				});
	},

	onEditNameSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editParentBrand").focus();
		}
	},

	onEditParentBrandSpecialKey : function(field, e) {
		var me = this;
		if (e.getKey() == e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				me.onOK();
			}
		}
	}
});