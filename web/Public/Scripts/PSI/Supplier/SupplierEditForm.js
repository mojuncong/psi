/**
 * 供应商档案 - 新建或编辑界面
 */
Ext.define("PSI.Supplier.SupplierEditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		entity : null
	},

	initComponent : function() {
		var me = this;
		var entity = me.getEntity();
		this.adding = entity == null;

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

		var categoryStore = me.getParentForm().categoryGrid.getStore();

		Ext.apply(me, {
			title : entity == null ? "新增供应商" : "编辑供应商",
			modal : true,
			resizable : false,
			onEsc : Ext.emptyFn,
			width : 550,
			height : 400,
			layout : "fit",
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
					labelWidth : 90,
					labelAlign : "right",
					labelSeparator : "",
					msgTarget : 'side'
				},
				items : [{
							xtype : "hidden",
							name : "id",
							value : entity == null ? null : entity.get("id")
						}, {
							id : "editCategory",
							xtype : "combo",
							fieldLabel : "分类",
							allowBlank : false,
							blankText : "没有输入供应商分类",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							valueField : "id",
							displayField : "name",
							store : categoryStore,
							queryMode : "local",
							editable : false,
							value : categoryStore.getAt(0).get("id"),
							name : "categoryId",
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editCode",
							fieldLabel : "编码",
							allowBlank : false,
							blankText : "没有输入供应商编码",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "code",
							value : entity == null ? null : entity.get("code"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editName",
							fieldLabel : "供应商",
							allowBlank : false,
							blankText : "没有输入供应商",
							beforeLabelTextTpl : PSI.Const.REQUIRED,
							name : "name",
							value : entity == null ? null : entity.get("name"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							},
							colspan : 2,
							width : 490
						}, {
							id : "editAddress",
							fieldLabel : "地址",
							name : "address",
							value : entity == null ? null : entity
									.get("address"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							},
							colspan : 2,
							width : 490
						}, {
							id : "editContact01",
							fieldLabel : "联系人",
							name : "contact01",
							value : entity == null ? null : entity
									.get("contact01"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editMobile01",
							fieldLabel : "手机",
							name : "mobile01",
							value : entity == null ? null : entity
									.get("mobile01"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editTel01",
							fieldLabel : "固话",
							name : "tel01",
							value : entity == null ? null : entity.get("tel01"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editQQ01",
							fieldLabel : "QQ",
							name : "qq01",
							value : entity == null ? null : entity.get("qq01"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editContact02",
							fieldLabel : "备用联系人",
							name : "contact02",
							value : entity == null ? null : entity
									.get("contact02"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editMobile02",
							fieldLabel : "备用联系人手机",
							name : "mobile02",
							value : entity == null ? null : entity
									.get("mobile02"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editTel02",
							fieldLabel : "备用联系人固话",
							name : "tel02",
							value : entity == null ? null : entity.get("tel02"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editQQ02",
							fieldLabel : "备用联系人QQ",
							name : "qq02",
							value : entity == null ? null : entity.get("qq02"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editAddressShipping",
							fieldLabel : "发货地址",
							name : "addressShipping",
							value : entity == null ? null : entity
									.get("addressShipping"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							},
							colspan : 2,
							width : 490
						}, {
							id : "editBankName",
							fieldLabel : "开户行",
							name : "bankName",
							value : entity == null ? null : entity
									.get("bankName"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editBankAccount",
							fieldLabel : "开户行账号",
							name : "bankAccount",
							value : entity == null ? null : entity
									.get("bankAccount"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editTax",
							fieldLabel : "税号",
							name : "tax",
							value : entity == null ? null : entity.get("tax"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editFax",
							fieldLabel : "传真",
							name : "fax",
							value : entity == null ? null : entity.get("fax"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editInitPayables",
							fieldLabel : "应付期初余额",
							name : "initPayables",
							xtype : "numberfield",
							hideTrigger : true,
							value : entity == null ? null : entity
									.get("initPayables"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editInitPayablesDT",
							fieldLabel : "余额日期",
							name : "initPayablesDT",
							xtype : "datefield",
							format : "Y-m-d",
							value : entity == null ? null : entity
									.get("initPayablesDT"),
							listeners : {
								specialkey : {
									fn : me.onEditSpecialKey,
									scope : me
								}
							}
						}, {
							id : "editNote",
							fieldLabel : "备注",
							name : "note",
							value : entity == null ? null : entity.get("note"),
							listeners : {
								specialkey : {
									fn : me.onEditLastSpecialKey,
									scope : me
								}
							},
							width : 490,
							colspan : 2
						}],
				buttons : buttons
			}],
			listeners : {
				show : {
					fn : me.onWndShow,
					scope : me
				},
				close : {
					fn : me.onWndClose,
					scope : me
				}
			}
		});

		me.callParent(arguments);

		me.__editorList = ["editCategory", "editCode", "editName",
				"editAddress", "editContact01", "editMobile01", "editTel01",
				"editQQ01", "editContact02", "editMobile02", "editTel02",
				"editQQ02", "editAddressShipping", "editBankName",
				"editBankAccount", "editTax", "editFax", "editInitPayables",
				"editInitPayablesDT", "editNote"];
	},
	onWndShow : function() {
		var me = this;
		if (me.adding) {
			// 新建
			var grid = me.getParentForm().categoryGrid;
			var item = grid.getSelectionModel().getSelection();
			if (item == null || item.length != 1) {
				return;
			}

			Ext.getCmp("editCategory").setValue(item[0].get("id"));
		} else {
			// 编辑
			var el = me.getEl();
			el.mask(PSI.Const.LOADING);
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL + "Home/Supplier/supplierInfo",
						params : {
							id : me.getEntity().get("id")
						},
						method : "POST",
						callback : function(options, success, response) {
							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								Ext.getCmp("editCategory")
										.setValue(data.categoryId);
								Ext.getCmp("editCode").setValue(data.code);
								Ext.getCmp("editName").setValue(data.name);
								Ext.getCmp("editAddress")
										.setValue(data.address);
								Ext.getCmp("editContact01")
										.setValue(data.contact01);
								Ext.getCmp("editMobile01")
										.setValue(data.mobile01);
								Ext.getCmp("editTel01").setValue(data.tel01);
								Ext.getCmp("editQQ01").setValue(data.qq01);
								Ext.getCmp("editContact02")
										.setValue(data.contact02);
								Ext.getCmp("editMobile02")
										.setValue(data.mobile02);
								Ext.getCmp("editTel02").setValue(data.tel02);
								Ext.getCmp("editQQ02").setValue(data.qq02);
								Ext.getCmp("editAddressShipping")
										.setValue(data.addressShipping);
								Ext.getCmp("editInitPayables")
										.setValue(data.initPayables);
								Ext.getCmp("editInitPayablesDT")
										.setValue(data.initPayablesDT);
								Ext.getCmp("editBankName")
										.setValue(data.bankName);
								Ext.getCmp("editBankAccount")
										.setValue(data.bankAccount);
								Ext.getCmp("editTax").setValue(data.tax);
								Ext.getCmp("editFax").setValue(data.fax);
								Ext.getCmp("editNote").setValue(data.note);
							}

							el.unmask();
						}
					});
		}

		var editCode = Ext.getCmp("editCode");
		editCode.focus();
		editCode.setValue(editCode.getValue());
	},
	// private
	onOK : function(thenAdd) {
		var me = this;
		var f = Ext.getCmp("editForm");
		var el = f.getEl();
		el.mask(PSI.Const.SAVING);
		f.submit({
					url : PSI.Const.BASE_URL + "Home/Supplier/editSupplier",
					method : "POST",
					success : function(form, action) {
						el.unmask();
						PSI.MsgBox.tip("数据保存成功");
						me.focus();
						me.__lastId = action.result.id;
						if (thenAdd) {
							me.clearEdit();
						} else {
							me.close();
						}
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									Ext.getCmp("editCode").focus();
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
	onEditLastSpecialKey : function(field, e) {
		if (e.getKey() === e.ENTER) {
			var f = Ext.getCmp("editForm");
			if (f.getForm().isValid()) {
				var me = this;
				me.onOK(me.adding);
			}
		}
	},
	clearEdit : function() {
		Ext.getCmp("editCode").focus();

		var editors = ["editCode", "editName", "editAddress",
				"editAddressShipping", "editContact01", "editMobile01",
				"editTel01", "editQQ01", "editContact02", "editMobile02",
				"editTel02", "editQQ02", "editInitPayables",
				"editInitPayablesDT", "editBankName", "editBankAccount",
				"editTax", "editFax", "editNote"];
		for (var i = 0; i < editors.length; i++) {
			var edit = Ext.getCmp(editors[i]);
			if (edit) {
				edit.setValue(null);
				edit.clearInvalid();
			}
		}
	},
	onWndClose : function() {
		var me = this;
		if (me.__lastId) {
			me.getParentForm().freshSupplierGrid(me.__lastId);
		}
	}
});