/**
 * 销售订单 - 新增或编辑界面
 * 
 * @author 李静波
 */
Ext.define("PSI.SaleOrder.SOEditForm", {
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
		me.__readOnly = false;
		var entity = me.getEntity();
		this.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建销售订单" : "编辑销售订单",
			modal : true,
			onEsc : Ext.emptyFn,
			defaultFocus : "editCustomer",
			maximized : true,
			width : 1000,
			height : 600,
			layout : "border",
			tbar : ["-", {
						text : "保存",
						id : "buttonSave",
						iconCls : "PSI-button-ok",
						handler : me.onOK,
						scope : me
					}, "-", {
						text : "取消",
						id : "buttonCancel",
						iconCls : "PSI-button-cancel",
						handler : function() {
							if (me.__readonly) {
								me.close();
								return;
							}

							PSI.MsgBox.confirm("请确认是否取消当前操作？", function() {
										me.close();
									});
						},
						scope : me
					}],
			items : [{
						region : "center",
						layout : "fit",
						border : 0,
						bodyPadding : 10,
						items : [me.getGoodsGrid()]
					}, {
						region : "north",
						id : "editForm",
						layout : {
							type : "table",
							columns : 4
						},
						height : 120,
						bodyPadding : 10,
						border : 0,
						items : [{
									xtype : "hidden",
									id : "hiddenId",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "editRef",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "单号",
									xtype : "displayfield",
									value : "<span style='color:red'>保存后自动生成</span>"
								}, {
									id : "editDealDate",
									fieldLabel : "交货日期",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									allowBlank : false,
									blankText : "没有输入交货日期",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									xtype : "datefield",
									format : "Y-m-d",
									value : new Date(),
									name : "bizDT",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editCustomer",
									colspan : 2,
									width : 430,
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_customerfield",
									fieldLabel : "客户",
									allowBlank : false,
									blankText : "没有输入客户",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									},
									callbackFunc : me.__setCustomerExtData
								}, {
									id : "editDealAddress",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "交货地址",
									colspan : 2,
									width : 430,
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editContact",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "联系人",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editTel",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "电话",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editFax",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "传真",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editOrg",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "组织机构",
									xtype : "psi_orgwithdataorgfield",
									colspan : 2,
									width : 430,
									allowBlank : false,
									blankText : "没有输入组织机构",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBizUser",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "业务员",
									xtype : "psi_userfield",
									allowBlank : false,
									blankText : "没有输入业务员",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editReceivingType",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "收款方式",
									xtype : "combo",
									queryMode : "local",
									editable : false,
									valueField : "id",
									store : Ext.create("Ext.data.ArrayStore", {
												fields : ["id", "text"],
												data : [["0", "记应收账款"],
														["1", "现金收款"]]
											}),
									value : "0",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBillMemo",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "备注",
									xtype : "textfield",
									colspan : 3,
									width : 645,
									listeners : {
										specialkey : {
											fn : me.onLastEditSpecialKey,
											scope : me
										}
									}
								}]
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

		me.__editorList = ["editDealDate", "editCustomer", "editDealAddress",
				"editContact", "editTel", "editFax", "editOrg", "editBizUser",
				"editReceivingType", "editBillMemo"];
	},

	onWindowBeforeUnload : function(e) {
		return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
	},

	onWndClose : function() {
		Ext.get(window).un('beforeunload', this.onWindowBeforeUnload);
	},

	onWndShow : function() {
		Ext.get(window).on('beforeunload', this.onWindowBeforeUnload);
		
		var me = this;

		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Sale/soBillInfo",
					params : {
						id : Ext.getCmp("hiddenId").getValue()
					},
					method : "POST",
					callback : function(options, success, response) {
						el.unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);

							if (data.ref) {
								Ext.getCmp("editRef").setValue(data.ref);
								var editCustomer = Ext.getCmp("editCustomer");
								editCustomer.setIdValue(data.customerId);
								editCustomer.setValue(data.customerName);
								Ext.getCmp("editBillMemo")
										.setValue(data.billMemo);
								Ext.getCmp("editDealDate")
										.setValue(data.dealDate);
								Ext.getCmp("editDealAddress")
										.setValue(data.dealAddress);
								Ext.getCmp("editContact")
										.setValue(data.contact);
								Ext.getCmp("editTel").setValue(data.tel);
								Ext.getCmp("editFax").setValue(data.fax);
							}
							me.__taxRate = data.taxRate;

							Ext.getCmp("editBizUser")
									.setIdValue(data.bizUserId);
							Ext.getCmp("editBizUser")
									.setValue(data.bizUserName);
							if (data.orgId) {
								Ext.getCmp("editOrg").setIdValue(data.orgId);
								Ext.getCmp("editOrg")
										.setValue(data.orgFullName);
							}

							if (data.receivingType) {
								Ext.getCmp("editReceivingType")
										.setValue(data.receivingType);
							}

							var store = me.getGoodsGrid().getStore();
							store.removeAll();
							if (data.items) {
								store.add(data.items);
							}
							if (store.getCount() == 0) {
								store.add({
											taxRate : me.__taxRate
										});
							}

							if (data.billStatus && data.billStatus != 0) {
								me.setBillReadonly();
							}
						}
					}
				});
	},

	onOK : function() {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Sale/editSOBill",
			method : "POST",
			params : {
				jsonStr : me.getSaveData()
			},
			callback : function(options, success, response) {
				Ext.getBody().unmask();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);
					if (data.success) {
						PSI.MsgBox.showInfo("成功保存数据", function() {
									me.close();
									me.getParentForm().refreshMainGrid(data.id);
								});
					} else {
						PSI.MsgBox.showInfo(data.msg);
					}
				}
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
		if (this.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			var me = this;
			var store = me.getGoodsGrid().getStore();
			if (store.getCount() == 0) {
				store.add({
							taxRate : me.__taxRate
						});
			}
			me.getGoodsGrid().focus();
			me.__cellEditing.startEdit(0, 1);
		}
	},

	getGoodsGrid : function() {
		var me = this;
		if (me.__goodsGrid) {
			return me.__goodsGrid;
		}
		var modelName = "PSISOBillDetail_EditForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount", {
								name : "goodsMoney",
								type : "float"
							}, "goodsPrice", "taxRate", {
								name : "tax",
								type : "float"
							}, {
								name : "moneyWithTax",
								type : "float"
							}]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : []
				});

		me.__cellEditing = Ext.create("PSI.UX.CellEditing", {
					clicksToEdit : 1,
					listeners : {
						edit : {
							fn : me.cellEditingAfterEdit,
							scope : me
						}
					}
				});

		me.__goodsGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					features : [{
								ftype : "summary"
							}],
					plugins : [me.__cellEditing],
					columnLines : true,
					columns : [{
								xtype : "rownumberer"
							}, {
								header : "商品编码",
								dataIndex : "goodsCode",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								editor : {
									xtype : "psi_goods_with_saleprice_field",
									parentCmp : me
								}
							}, {
								header : "商品名称",
								dataIndex : "goodsName",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								width : 200
							}, {
								header : "规格型号",
								dataIndex : "goodsSpec",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								width : 200
							}, {
								header : "销售数量",
								dataIndex : "goodsCount",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								width : 100,
								editor : {
									xtype : "numberfield",
									allowDecimals : false,
									hideTrigger : true
								}
							}, {
								header : "单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								width : 60
							}, {
								header : "销售单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100,
								editor : {
									xtype : "numberfield",
									hideTrigger : true
								},
								summaryRenderer : function() {
									return "销售金额合计";
								}
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120,
								editor : {
									xtype : "numberfield",
									hideTrigger : true
								},
								summaryType : "sum"
							}, {
								header : "税率(%)",
								dataIndex : "taxRate",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								width : 60
							}, {
								header : "税金",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100,
								editor : {
									xtype : "numberfield",
									hideTrigger : true
								},
								summaryType : "sum"
							}, {
								header : "价税合计",
								dataIndex : "moneyWithTax",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120,
								editor : {
									xtype : "numberfield",
									hideTrigger : true
								},
								summaryType : "sum"
							}, {
								header : "",
								id : "columnActionDelete",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 40,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/delete.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.remove(store.getAt(row));
										if (store.getCount() == 0) {
											store.add({});
										}
									},
									scope : me
								}]
							}, {
								header : "",
								id : "columnActionAdd",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 40,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/add.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row, [{
															taxRate : me.__taxRate
														}]);
									},
									scope : me
								}]
							}, {
								header : "",
								id : "columnActionAppend",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 40,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/add_detail.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row + 1, [{
															taxRate : me.__taxRate
														}]);
									},
									scope : me
								}]
							}],
					store : store,
					listeners : {
						cellclick : function() {
							return !me.__readonly;
						}
					}
				});

		return me.__goodsGrid;
	},
	
	__setGoodsInfo : function(data) {
		var me = this;
		var item = me.getGoodsGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
		var goods = item[0];

		goods.set("goodsId", data.id);
		goods.set("goodsCode", data.code);
		goods.set("goodsName", data.name);
		goods.set("unitName", data.unitName);
		goods.set("goodsSpec", data.spec);

		me.calcMoney(goods);
	},

	cellEditingAfterEdit : function(editor, e) {
		var me = this;

		if (me.__readonly) {
			return;
		}

		var fieldName = e.field;
		var goods = e.record;
		var oldValue = e.originalValue;
		if (fieldName == "moneyWithTax") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcTax(goods);
			}
			var store = me.getGoodsGrid().getStore();
			if (e.rowIdx == store.getCount() - 1) {
				store.add({
							taxRate : me.__taxRate
						});
			}
			e.rowIdx += 1;
			me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
			me.__cellEditing.startEdit(e.rowIdx, 1);
		} else if (fieldName == "tax") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcMoneyWithTax(goods);
			}
		} else if (fieldName == "goodsMoney") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcPrice(goods);
			}
		} else if (fieldName == "goodsCount") {
			if (goods.get(fieldName) != oldValue) {
				me.calcMoney(goods);
			}
		} else if (fieldName == "goodsPrice") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcMoney(goods);
			}
		}
	},

	calcTax : function(goods) {
		if (!goods) {
			return;
		}
		var taxRate = goods.get("taxRate") / 100;
		var tax = goods.get("moneyWithTax") * taxRate / (1 + taxRate);
		goods.set("tax", tax);
		goods.set("goodsMoney", goods.get("moneyWithTax") - tax);
	},

	calcMoneyWithTax : function(goods) {
		if (!goods) {
			return;
		}
		goods.set("moneyWithTax", goods.get("goodsMoney") + goods.get("tax"));
	},

	calcMoney : function(goods) {
		if (!goods) {
			return;
		}

		goods.set("goodsMoney", goods.get("goodsCount")
						* goods.get("goodsPrice"));
		goods.set("tax", goods.get("goodsMoney") * goods.get("taxRate") / 100);
		goods.set("moneyWithTax", goods.get("goodsMoney") + goods.get("tax"));
	},

	calcPrice : function(goods) {
		if (!goods) {
			return;
		}

		var goodsCount = goods.get("goodsCount");
		if (goodsCount && goodsCount != 0) {
			goods.set("goodsPrice", goods.get("goodsMoney")
							/ goods.get("goodsCount"));
		}
	},
	
	getSaveData : function() {
		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			dealDate : Ext.Date.format(Ext.getCmp("editDealDate").getValue(),
					"Y-m-d"),
			customerId : Ext.getCmp("editCustomer").getIdValue(),
			dealAddress : Ext.getCmp("editDealAddress").getValue(),
			contact : Ext.getCmp("editContact").getValue(),
			tel : Ext.getCmp("editTel").getValue(),
			fax : Ext.getCmp("editFax").getValue(),
			orgId : Ext.getCmp("editOrg").getIdValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			receivingType : Ext.getCmp("editReceivingType").getValue(),
			billMemo : Ext.getCmp("editBillMemo").getValue(),
			items : []
		};

		var store = this.getGoodsGrid().getStore();
		for (var i = 0; i < store.getCount(); i++) {
			var item = store.getAt(i);
			result.items.push({
						id : item.get("id"),
						goodsId : item.get("goodsId"),
						goodsCount : item.get("goodsCount"),
						goodsPrice : item.get("goodsPrice"),
						goodsMoney : item.get("goodsMoney"),
						tax : item.get("tax"),
						taxRate : item.get("taxRate"),
						moneyWithTax : item.get("moneyWithTax")
					});
		}

		return Ext.JSON.encode(result);
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("查看采购订单");
		Ext.getCmp("buttonSave").setDisabled(true);
		Ext.getCmp("buttonCancel").setText("关闭");
		Ext.getCmp("editDealDate").setReadOnly(true);
		Ext.getCmp("editCustomer").setReadOnly(true);
		Ext.getCmp("editDealAddress").setReadOnly(true);
		Ext.getCmp("editContact").setReadOnly(true);
		Ext.getCmp("editTel").setReadOnly(true);
		Ext.getCmp("editFax").setReadOnly(true);
		Ext.getCmp("editOrg").setReadOnly(true);
		Ext.getCmp("editBizUser").setReadOnly(true);
		Ext.getCmp("editReceivingType").setReadOnly(true);
		Ext.getCmp("editBillMemo").setReadOnly(true);

		Ext.getCmp("columnActionDelete").hide();
		Ext.getCmp("columnActionAdd").hide();
		Ext.getCmp("columnActionAppend").hide();
	},

	__setCustomerExtData : function(data) {
		Ext.getCmp("editDealAddress").setValue(data.address_receipt);
		Ext.getCmp("editTel").setValue(data.tel01);
		Ext.getCmp("editFax").setValue(data.fax);
		Ext.getCmp("editContact").setValue(data.contact01);
	}
});