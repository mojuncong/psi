/**
 * 销售退货入库单
 */
Ext.define("PSI.Sale.SREditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		entity : null
	},

	initComponent : function() {
		var me = this;
		me.__readonly = false;
		var entity = me.getEntity();
		this.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建销售退货入库单" : "编辑销售退货入库单",
			modal : true,
			onEsc : Ext.emptyFn,
			maximized : true,
			width : 900,
			height : 600,
			layout : "border",
			tbar : ["-", {
						text : "选择销售出库单",
						iconCls : "PSI-button-add",
						handler : me.onSelectWSBill,
						scope : me,
						disabled : me.entity != null
					}, "-", {
						text : "保存",
						iconCls : "PSI-button-ok",
						handler : me.onOK,
						scope : me,
						id : "buttonSave"
					}, "-", {
						text : "取消",
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
						scope : me,
						id : "buttonCancel"
					}],
			defaultFocus : "editWarehouse",
			items : [{
						region : "center",
						border : 0,
						bodyPadding : 10,
						layout : "fit",
						items : [me.getGoodsGrid()]
					}, {
						region : "north",
						border : 0,
						layout : {
							type : "table",
							columns : 2
						},
						height : 120,
						bodyPadding : 10,
						items : [{
									xtype : "hidden",
									id : "hiddenId",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "editCustomer",
									xtype : "displayfield",
									fieldLabel : "客户",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									colspan : 2,
									width : 430
								}, {
									id : "editRef",
									fieldLabel : "单号",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "displayfield",
									value : "<span style='color:red'>保存后自动生成</span>"
								}, {
									id : "editBizDT",
									fieldLabel : "业务日期",
									allowBlank : false,
									blankText : "没有输入业务日期",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
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
									xtype : "hidden",
									id : "editCustomerId",
									name : "customerId"
								}, {
									id : "editWarehouse",
									fieldLabel : "入库仓库",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_warehousefield",
									fid : "2006",
									allowBlank : false,
									blankText : "没有输入入库仓库",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditWarehouseSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBizUser",
									fieldLabel : "业务员",
									xtype : "psi_userfield",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									allowBlank : false,
									blankText : "没有输入业务员",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditBizUserSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editPaymentType",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "付款方式",
									xtype : "combo",
									queryMode : "local",
									editable : false,
									valueField : "id",
									store : Ext.create("Ext.data.ArrayStore", {
												fields : ["id", "text"],
												data : [["0", "记应付账款"],
														["1", "现金付款"],
														["2", "退款转入预收款"]]
											}),
									value : "0",
									listeners : {
										specialkey : {
											fn : me.onEditPaymentTypeSpecialKey,
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
					url : PSI.Const.BASE_URL + "Home/Sale/srBillInfo",
					params : {
						id : Ext.getCmp("hiddenId").getValue()
					},
					method : "POST",
					callback : function(options, success, response) {
						el.unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);

							if (data.ref) {
								// 编辑单据
								Ext.getCmp("editRef").setValue(data.ref);
								Ext.getCmp("editCustomer")
										.setValue(data.customerName + " 销售单号: "
												+ data.wsBillRef);
								Ext.getCmp("editCustomerId")
										.setValue(data.customerId);
							} else {
								// 这是：新建退货入库单
								// 第一步就是选中销售出库单
								me.onSelectWSBill();
							}

							Ext.getCmp("editWarehouse")
									.setIdValue(data.warehouseId);
							Ext.getCmp("editWarehouse")
									.setValue(data.warehouseName);

							Ext.getCmp("editBizUser")
									.setIdValue(data.bizUserId);
							Ext.getCmp("editBizUser")
									.setValue(data.bizUserName);
							if (data.bizDT) {
								Ext.getCmp("editBizDT").setValue(data.bizDT);
							}
							if (data.paymentType) {
								Ext.getCmp("editPaymentType")
										.setValue(data.paymentType);
							}

							var store = me.getGoodsGrid().getStore();
							store.removeAll();
							if (data.items) {
								store.add(data.items);
							}

							if (data.billStatus && data.billStatus != 0) {
								me.setBillReadonly();
							}
						} else {
							PSI.MsgBox.showInfo("网络错误")
						}
					}
				});
	},

	onOK : function() {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
			url : PSI.Const.BASE_URL + "Home/Sale/editSRBill",
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
	onEditBizDTSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editWarehouse").focus();
		}
	},
	onEditWarehouseSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizUser").focus();
		}
	},
	onEditBizUserSpecialKey : function(field, e) {
		if (this.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editPaymentType").focus();
		}
	},

	onEditPaymentTypeSpecialKey : function(field, e) {
		if (this.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			var me = this;
			me.getGoodsGrid().focus();
			me.__cellEditing.startEdit(0, 4);
		}
	},

	getGoodsGrid : function() {
		var me = this;
		if (me.__goodsGrid) {
			return me.__goodsGrid;
		}

		var modelName = "PSISRBillDetail_EditForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount",
							"goodsMoney", "goodsPrice", "rejCount", "rejPrice",
							{
								name : "rejMoney",
								type : "float"
							}, "sn"]
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
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 30
									}), {
								header : "商品编码",
								dataIndex : "goodsCode",
								menuDisabled : true,
								draggable : false,
								sortable : false
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
								header : "退货数量",
								dataIndex : "rejCount",
								menuDisabled : true,
								draggable : false,
								sortable : false,
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
								header : "退货单价",
								dataIndex : "rejPrice",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100,
								editor : {
									xtype : "numberfield",
									allowDecimals : true,
									hideTrigger : true
								},
								summaryRenderer : function() {
									return "退货金额合计";
								}
							}, {
								header : "退货金额",
								dataIndex : "rejMoney",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120,
								editor : {
									xtype : "numberfield",
									allowDecimals : true,
									hideTrigger : true
								},
								summaryType : "sum"
							}, {
								header : "销售数量",
								dataIndex : "goodsCount",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								width : 100
							}, {
								header : "销售单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120
							}, {
								header : "序列号",
								dataIndex : "sn",
								menuDisabled : true,
								draggable : false,
								sortable : false
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
	cellEditingAfterEdit : function(editor, e) {
		var me = this;
		if (me.__readonly) {
			return;
		}

		var fieldName = e.field;
		var goods = e.record;
		var oldValue = e.originalValue;
		if (fieldName == "rejMoney") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcPrice(goods);
			}
		} else if (fieldName == "rejCount") {
			if (goods.get(fieldName) != oldValue) {
				me.calcMoney(goods);
			}
		} else if (fieldName == "rejPrice") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcMoney(goods);
			}
		}
	},
	calcMoney : function(goods) {
		if (!goods) {
			return;
		}

		var rejCount = goods.get("rejCount");
		if (!rejCount) {
			rejCount = 0;
		}
		var rejPrice = goods.get("rejPrice");
		if (!rejPrice) {
			rejPrice = 0;
		}
		goods.set("rejMoney", rejCount * rejPrice);
	},

	calcPrice : function(goods) {
		if (!goods) {
			return;
		}
		var rejCount = goods.get("rejCount");
		if (rejCount && rejCount != 0) {
			goods
					.set("rejPrice", goods.get("rejMoney")
									/ goods.get("rejCount"));
		}
	},

	getSaveData : function() {
		var me = this;
		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			bizDT : Ext.Date
					.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			customerId : Ext.getCmp("editCustomerId").getValue(),
			warehouseId : Ext.getCmp("editWarehouse").getIdValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			paymentType : Ext.getCmp("editPaymentType").getValue(),
			wsBillId : me.__wsBillId,
			items : []
		};

		var store = me.getGoodsGrid().getStore();
		for (var i = 0; i < store.getCount(); i++) {
			var item = store.getAt(i);
			result.items.push({
						id : item.get("id"),
						goodsId : item.get("goodsId"),
						rejCount : item.get("rejCount"),
						rejPrice : item.get("rejPrice"),
						rejMoney : item.get("rejMoney"),
						sn : item.get("sn")
					});
		}

		return Ext.JSON.encode(result);
	},

	onSelectWSBill : function() {
		var form = Ext.create("PSI.Sale.SRSelectWSBillForm", {
					parentForm : this
				});
		form.show();
	},

	getWSBillInfo : function(id) {
		var me = this;
		me.__wsBillId = id;
		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/Sale/getWSBillInfoForSRBill",
					params : {
						id : id
					},
					method : "POST",
					callback : function(options, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							Ext.getCmp("editCustomer")
									.setValue(data.customerName + " 销售单号: "
											+ data.ref);
							Ext.getCmp("editCustomerId")
									.setValue(data.customerId);
							Ext.getCmp("editWarehouse")
									.setIdValue(data.warehouseId);
							Ext.getCmp("editWarehouse")
									.setValue(data.warehouseName);

							var store = me.getGoodsGrid().getStore();
							store.removeAll();
							store.add(data.items);
						}

						el.unmask();
					}
				});
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("查看销售退货入库单");
		Ext.getCmp("buttonSave").setDisabled(true);
		Ext.getCmp("buttonCancel").setText("关闭");
		Ext.getCmp("editBizDT").setReadOnly(true);
		Ext.getCmp("editWarehouse").setReadOnly(true);
		Ext.getCmp("editBizUser").setReadOnly(true);
		Ext.getCmp("editPaymentType").setReadOnly(true);
	}
});