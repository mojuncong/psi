/**
 * 销售出库 - 新建或编辑界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Sale.WSEditForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null,
		entity : null,
		genBill : false,
		sobillRef : null
	},

	initComponent : function() {
		var me = this;
		me.__readonly = false;
		var entity = me.getEntity();
		this.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建销售出库单" : "编辑销售出库单",
			modal : true,
			onEsc : Ext.emptyFn,
			maximized : true,
			width : 1000,
			height : 600,
			layout : "border",
			defaultFocus : "editCustomer",
			tbar : [{
						id : "buttonToolbox",
						text : "工具",
						iconCls : "PSI-button-toolbox",
						menu : [{
									text : "临时保存销售出库单",
									scope : me,
									handler : me.onExportBill
								}, "-", {
									text : "导入临时保存的销售出库单",
									scope : me,
									handler : me.onImportBill
								}]
					}, "-", {
						id : "displayFieldBarcode",
						value : "条码录入",
						xtype : "displayfield"
					}, {
						xtype : "textfield",
						id : "editBarcode",
						listeners : {
							specialkey : {
								fn : me.onEditBarcodeKeydown,
								scope : me
							}
						}
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
									id : "editCustomer",
									xtype : "psi_customerfield",
									fieldLabel : "客户",
									showAddButton : true,
									allowBlank : false,
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									colspan : 2,
									width : 430,
									blankText : "没有输入客户",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditCustomerSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editWarehouse",
									fieldLabel : "出库仓库",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_warehousefield",
									fid : "2002",
									allowBlank : false,
									blankText : "没有输入出库仓库",
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
														["1", "现金收款"],
														["2", "用预收款支付"]]
											}),
									value : "0",
									listeners : {
										specialkey : {
											fn : me.onEditReceivingTypeSpecialKey,
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
									listeners : {
										specialkey : {
											fn : me.onEditBillMemoSpecialKey,
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
		me.__canEditGoodsPrice = false;
		var el = me.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Sale/wsBillInfo",
					params : {
						id : Ext.getCmp("hiddenId").getValue(),
						sobillRef : me.getSobillRef()
					},
					method : "POST",
					callback : function(options, success, response) {
						el.unmask();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);

							if (data.canEditGoodsPrice) {
								me.__canEditGoodsPrice = true;
								Ext.getCmp("columnGoodsPrice").setEditor({
											xtype : "numberfield",
											allowDecimals : true,
											hideTrigger : true
										});
								Ext.getCmp("columnGoodsMoney").setEditor({
											xtype : "numberfield",
											allowDecimals : true,
											hideTrigger : true
										});
							}

							if (me.getGenBill()) {
								// 从销售订单生成销售出库单
								Ext.getCmp("editCustomer")
										.setIdValue(data.customerId);
								Ext.getCmp("editCustomer")
										.setValue(data.customerName);
								Ext.getCmp("editBizUser")
										.setIdValue(data.bizUserId);
								Ext.getCmp("editBizUser")
										.setValue(data.bizUserName);
								Ext.getCmp("editBizDT").setValue(data.dealDate);
								Ext.getCmp("editReceivingType")
										.setValue(data.receivingType);
								var store = me.getGoodsGrid().getStore();
								store.removeAll();
								store.add(data.items);

								Ext.getCmp("editCustomer").setReadOnly(true);
								Ext.getCmp("columnActionDelete").hide();
								Ext.getCmp("columnActionAdd").hide();
								Ext.getCmp("columnActionAppend").hide();

								Ext.getCmp("buttonToolbox").setDisabled(true);
								Ext.getCmp("editBarcode").setDisabled(true);
							} else {

								if (data.ref) {
									Ext.getCmp("editRef").setValue(data.ref);
								}

								Ext.getCmp("editCustomer")
										.setIdValue(data.customerId);
								Ext.getCmp("editCustomer")
										.setValue(data.customerName);
								Ext
										.getCmp("editCustomer")
										.setShowAddButton(data.showAddCustomerButton);

								Ext.getCmp("editWarehouse")
										.setIdValue(data.warehouseId);
								Ext.getCmp("editWarehouse")
										.setValue(data.warehouseName);

								Ext.getCmp("editBizUser")
										.setIdValue(data.bizUserId);
								Ext.getCmp("editBizUser")
										.setValue(data.bizUserName);
								if (data.bizDT) {
									Ext.getCmp("editBizDT")
											.setValue(data.bizDT);
								}
								if (data.receivingType) {
									Ext.getCmp("editReceivingType")
											.setValue(data.receivingType);
								}
								if (data.memo) {
									Ext.getCmp("editBillMemo")
											.setValue(data.memo);
								}

								var store = me.getGoodsGrid().getStore();
								store.removeAll();
								if (data.items) {
									store.add(data.items);
								}
								if (store.getCount() == 0) {
									store.add({});
								}

								if (data.billStatus && data.billStatus != 0) {
									me.setBillReadonly();
								}
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
			url : PSI.Const.BASE_URL + "Home/Sale/editWSBill",
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
			Ext.getCmp("editCustomer").focus();
		}
	},
	onEditCustomerSpecialKey : function(field, e) {
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
			Ext.getCmp("editReceivingType").focus();
		}
	},

	onEditReceivingTypeSpecialKey : function(field, e) {
		if (this.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBillMemo").focus();
		}
	},

	onEditBillMemoSpecialKey : function(field, e) {
		if (this.__readonly) {
			return;
		}

		if (e.getKey() == e.ENTER) {
			var me = this;
			var store = me.getGoodsGrid().getStore();
			if (store.getCount() == 0) {
				store.add({});
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
		Ext.define("PSIWSBillDetail_EditForm", {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount", {
								name : "goodsMoney",
								type : "float"
							}, "goodsPrice", "sn", "memo"]
				});
		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : "PSIWSBillDetail_EditForm",
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
								header : "销售单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100,
								id : "columnGoodsPrice",
								summaryRenderer : function() {
									return "销售金额合计";
								}
							}, {
								header : "销售金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								draggable : false,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120,
								id : "columnGoodsMoney",
								summaryType : "sum"
							}, {
								header : "序列号",
								dataIndex : "sn",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								editor : {
									xtype : "textfield"
								}
							}, {
								header : "备注",
								dataIndex : "memo",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								editor : {
									xtype : "textfield"
								}
							}, {
								header : "",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 50,
								xtype : "actioncolumn",
								id : "columnActionDelete",
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
								width : 50,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/add.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row, [{}]);
									},
									scope : me
								}]
							}, {
								header : "",
								id : "columnActionAppend",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 50,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/add_detail.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.insert(row + 1, [{}]);
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

	cellEditingAfterEdit : function(editor, e) {
		var me = this;

		var fieldName = e.field;
		var goods = e.record;
		var oldValue = e.originalValue;
		if (fieldName == "goodsCount") {
			if (goods.get(fieldName) != oldValue) {
				me.calcMoney(goods);
			}
		} else if (fieldName == "goodsPrice") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcMoney(goods);
			}
		} else if (fieldName == "goodsMoney") {
			if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
				me.calcPrice(goods);
			}
		} else if (fieldName == "memo") {
			var store = me.getGoodsGrid().getStore();
			if (e.rowIdx == store.getCount() - 1) {
				store.add({});
			}

			me.getGoodsGrid().getSelectionModel().select(e.rowIdx + 1);
			me.__cellEditing.startEdit(e.rowIdx + 1, 1);
		}
	},

	calcMoney : function(goods) {
		if (!goods) {
			return;
		}
		goods.set("goodsMoney", goods.get("goodsCount")
						* goods.get("goodsPrice"));
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
		goods.set("goodsPrice", data.salePrice);

		me.calcMoney(goods);
	},

	getSaveData : function() {
		var me = this;

		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			bizDT : Ext.Date
					.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			customerId : Ext.getCmp("editCustomer").getIdValue(),
			warehouseId : Ext.getCmp("editWarehouse").getIdValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			receivingType : Ext.getCmp("editReceivingType").getValue(),
			billMemo : Ext.getCmp("editBillMemo").getValue(),
			sobillRef : me.getSobillRef(),
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
						sn : item.get("sn"),
						memo : item.get("memo")
					});
		}

		return Ext.JSON.encode(result);
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("查看销售出库单");
		Ext.getCmp("buttonToolbox").setDisabled(true);
		Ext.getCmp("displayFieldBarcode").setDisabled(true);
		Ext.getCmp("editBarcode").setDisabled(true);
		Ext.getCmp("buttonSave").setDisabled(true);
		Ext.getCmp("buttonCancel").setText("关闭");
		Ext.getCmp("editBizDT").setReadOnly(true);
		Ext.getCmp("editCustomer").setReadOnly(true);
		Ext.getCmp("editWarehouse").setReadOnly(true);
		Ext.getCmp("editBizUser").setReadOnly(true);
		Ext.getCmp("columnActionDelete").hide();
		Ext.getCmp("columnActionAdd").hide();
		Ext.getCmp("columnActionAppend").hide();
		Ext.getCmp("editReceivingType").setReadOnly(true);
		Ext.getCmp("editBillMemo").setReadOnly(true);
	},

	onBarCode : function() {
		var form = Ext.create("PSI.Sale.WSBarcodeEditForm", {
					parentForm : this
				});
		form.show();
	},

	addGoodsByBarCode : function(goods) {
		if (!goods) {
			return;
		}

		var me = this;
		var store = me.getGoodsGrid().getStore();

		if (store.getCount() == 1) {
			var r = store.getAt(0);
			var id = r.get("goodsId");
			if (id == null || id == "") {
				store.removeAll();
			}
		}

		store.add(goods);
	},

	getExportData : function() {
		var result = {
			bizDT : Ext.Date
					.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			customerId : Ext.getCmp("editCustomer").getIdValue(),
			customerName : Ext.getCmp("editCustomer").getValue(),
			warehouseId : Ext.getCmp("editWarehouse").getIdValue(),
			warehouseName : Ext.getCmp("editWarehouse").getValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			bizUserName : Ext.getCmp("editBizUser").getValue(),
			billMemo : Ext.getCmp("editBillMemo").getValue(),
			items : []
		};

		var store = this.getGoodsGrid().getStore();
		for (var i = 0; i < store.getCount(); i++) {
			var item = store.getAt(i);
			result.items.push({
						id : item.get("id"),
						goodsId : item.get("goodsId"),
						goodsCode : item.get("goodsCode"),
						goodsName : item.get("goodsName"),
						goodsSpec : item.get("goodsSpec"),
						unitName : item.get("unitName"),
						goodsCount : item.get("goodsCount"),
						goodsPrice : item.get("goodsPrice"),
						goodsMoney : item.get("goodsMoney"),
						sn : item.get("sn"),
						memo : item.get("memo")
					});
		}

		return Ext.JSON.encode(result);
	},

	onExportBill : function() {
		var form = Ext.create("PSI.Sale.WSExportForm", {
					billData : this.getExportData()
				});
		form.show();
	},

	onImportBill : function() {
		var form = Ext.create("PSI.Sale.WSImportForm", {
					parentForm : this
				});
		form.show();
	},

	importBill : function(bill) {
		if (!bill) {
			PSI.MsgBox.showInfo("没有输入数据");
			return false;
		}

		var me = this;
		// 主表
		Ext.getCmp("editCustomer").setIdValue(bill.customerId);
		Ext.getCmp("editCustomer").setValue(bill.customerName);

		Ext.getCmp("editWarehouse").setIdValue(bill.warehouseId);
		Ext.getCmp("editWarehouse").setValue(bill.warehouseName);

		Ext.getCmp("editBizUser").setIdValue(bill.bizUserId);
		Ext.getCmp("editBizUser").setValue(bill.bizUserName);
		Ext.getCmp("editBizDT").setValue(bill.bizDT);
		Ext.getCmp("editBillMemo").setValue(bill.billMemo);

		// 明细表
		var store = me.getGoodsGrid().getStore();
		store.removeAll();
		store.add(bill.items);

		return true;
	},

	onEditBarcodeKeydown : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var me = this;

			var el = Ext.getBody();
			el.mask("查询中...");
			Ext.Ajax.request({
				url : PSI.Const.BASE_URL + "Home/Goods/queryGoodsInfoByBarcode",
				method : "POST",
				params : {
					barcode : field.getValue()
				},
				callback : function(options, success, response) {
					el.unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							var goods = {
								goodsId : data.id,
								goodsCode : data.code,
								goodsName : data.name,
								goodsSpec : data.spec,
								unitName : data.unitName,
								goodsCount : 1,
								goodsPrice : data.salePrice,
								goodsMoney : data.salePrice
							};
							me.addGoodsByBarCode(goods);
							var edit = Ext.getCmp("editBarcode");
							edit.setValue(null);
							edit.focus();
						} else {
							var edit = Ext.getCmp("editBarcode");
							edit.setValue(null);
							PSI.MsgBox.showInfo(data.msg, function() {
										edit.focus();
									});
						}
					} else {
						PSI.MsgBox.showInfo("网络错误");
					}
				}

			});
		}
	}
});