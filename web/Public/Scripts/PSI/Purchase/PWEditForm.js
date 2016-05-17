/**
 * 采购入库单 - 新增或编辑界面
 */
Ext.define("PSI.Purchase.PWEditForm", {
	extend : "Ext.window.Window",
	config : {
		parentForm : null,
		entity : null,
		genBill : false,
		pobillRef : null
	},

	initComponent : function() {
		var me = this;
		me.__readOnly = false;
		var entity = me.getEntity();
		this.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建采购入库单" : "编辑采购入库单",
			modal : true,
			onEsc : Ext.emptyFn,
			maximized : true,
			width : 1000,
			height : 600,
			layout : "border",
			defaultFocus : "editSupplier",
			tbar : [{
						xtype : "displayfield",
						value : "条码录入",
						id : "displayFieldBarcode"
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
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							window
									.open("http://my.oschina.net/u/134395/blog/379622");
						}
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
							columns : 2
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
									id : "editBizDT",
									fieldLabel : "业务日期",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									allowBlank : false,
									blankText : "没有输入业务日期",
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
									id : "editSupplier",
									colspan : 2,
									width : 430,
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_supplierfield",
									fieldLabel : "供应商",
									allowBlank : false,
									blankText : "没有输入供应商",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSupplierSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editWarehouse",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "入库仓库",
									xtype : "psi_warehousefield",
									fid : "2001",
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
														["2", "预付款"]]
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
			url : PSI.Const.BASE_URL + "Home/Purchase/pwBillInfo",
			params : {
				id : Ext.getCmp("hiddenId").getValue(),
				pobillRef : me.getPobillRef()
			},
			method : "POST",
			callback : function(options, success, response) {
				el.unmask();

				if (success) {
					var data = Ext.JSON.decode(response.responseText);

					if (me.getGenBill()) {
						// 从采购订单生成采购入库单
						Ext.getCmp("editSupplier").setIdValue(data.supplierId);
						Ext.getCmp("editSupplier").setValue(data.supplierName);
						Ext.getCmp("editBizUser").setIdValue(data.bizUserId);
						Ext.getCmp("editBizUser").setValue(data.bizUserName);
						Ext.getCmp("editBizDT").setValue(data.dealDate);
						Ext.getCmp("editPaymentType")
								.setValue(data.paymentType);
						var store = me.getGoodsGrid().getStore();
						store.removeAll();
						store.add(data.items);

						Ext.getCmp("editSupplier").setReadOnly(true);
						Ext.getCmp("columnActionDelete").hide();
						Ext.getCmp("columnActionAdd").hide();
						Ext.getCmp("columnActionAppend").hide();

						Ext.getCmp("editBarcode").setDisabled(true);
					} else {
						if (!data.genBill) {
							Ext.getCmp("columnGoodsCode").setEditor({
										xtype : "psi_goods_with_purchaseprice_field",
										parentCmp : me
									});
							Ext.getCmp("columnGoodsPrice").setEditor({
										xtype : "numberfield",
										hideTrigger : true
									});
							Ext.getCmp("columnGoodsMoney").setEditor({
										xtype : "numberfield",
										hideTrigger : true
									});
						} else {
							Ext.getCmp("editSupplier").setReadOnly(true);
							Ext.getCmp("columnActionDelete").hide();
							Ext.getCmp("columnActionAdd").hide();
							Ext.getCmp("columnActionAppend").hide();
						}

						if (data.ref) {
							Ext.getCmp("editRef").setValue(data.ref);
						}

						Ext.getCmp("editSupplier").setIdValue(data.supplierId);
						Ext.getCmp("editSupplier").setValue(data.supplierName);

						Ext.getCmp("editWarehouse")
								.setIdValue(data.warehouseId);
						Ext.getCmp("editWarehouse")
								.setValue(data.warehouseName);

						Ext.getCmp("editBizUser").setIdValue(data.bizUserId);
						Ext.getCmp("editBizUser").setValue(data.bizUserName);
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
						if (store.getCount() == 0) {
							store.add({});
						}

						if (data.billStatus && data.billStatus != 0) {
							me.setBillReadonly();
						}
					}
				}
			}
		});
	},

	onOK : function() {
		var me = this;
		Ext.getBody().mask("正在保存中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Purchase/editPWBill",
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
											var pf = me.getParentForm();
											if (pf) {
												pf.refreshMainGrid(data.id);
											}
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
			Ext.getCmp("editSupplier").focus();
		}
	},
	onEditSupplierSpecialKey : function(field, e) {
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
		var modelName = "PSIPWBillDetail_EditForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount", {
								name : "goodsMoney",
								type : "float"
							}, "goodsPrice", "memo"]
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
								id : "columnGoodsCode"
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
								header : "采购数量",
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
								header : "采购单价",
								dataIndex : "goodsPrice",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 100,
								id : "columnGoodsPrice",
								summaryRenderer : function() {
									return "采购金额合计";
								}
							}, {
								header : "采购金额",
								dataIndex : "goodsMoney",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								align : "right",
								xtype : "numbercolumn",
								width : 120,
								id : "columnGoodsMoney",
								summaryType : "sum"
							}, {
								header : "备注",
								dataIndex : "memo",
								menuDisabled : true,
								sortable : false,
								draggable : false,
								width : 200,
								editor : {
									xtype : "textfield"
								}
							}, {
								header : "",
								id : "columnActionDelete",
								align : "center",
								menuDisabled : true,
								draggable : false,
								width : 50,
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

		// 设置建议采购价
		goods.set("goodsPrice", data.purchasePrice);

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
		if (fieldName == "memo") {
			var store = me.getGoodsGrid().getStore();
			if (e.rowIdx == store.getCount() - 1) {
				store.add({});
			}
			e.rowIdx += 1;
			me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
			me.__cellEditing.startEdit(e.rowIdx, 1);
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
	getSaveData : function() {
		var me = this;

		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			bizDT : Ext.Date
					.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			supplierId : Ext.getCmp("editSupplier").getIdValue(),
			warehouseId : Ext.getCmp("editWarehouse").getIdValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			paymentType : Ext.getCmp("editPaymentType").getValue(),
			pobillRef : me.getPobillRef(),
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
						memo : item.get("memo")
					});
		}

		return Ext.JSON.encode(result);
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("查看采购入库单");
		Ext.getCmp("buttonSave").setDisabled(true);
		Ext.getCmp("buttonCancel").setText("关闭");
		Ext.getCmp("editBizDT").setReadOnly(true);
		Ext.getCmp("editSupplier").setReadOnly(true);
		Ext.getCmp("editWarehouse").setReadOnly(true);
		Ext.getCmp("editBizUser").setReadOnly(true);
		Ext.getCmp("editPaymentType").setReadOnly(true);
		Ext.getCmp("columnActionDelete").hide();
		Ext.getCmp("columnActionAdd").hide();
		Ext.getCmp("columnActionAppend").hide();
		Ext.getCmp("displayFieldBarcode").setDisabled(true);
		Ext.getCmp("editBarcode").setDisabled(true);
	},

	onEditBarcodeKeydown : function(field, e) {
		if (e.getKey() == e.ENTER) {
			var me = this;

			var el = Ext.getBody();
			el.mask("查询中...");
			Ext.Ajax.request({
						url : PSI.Const.BASE_URL
								+ "Home/Goods/queryGoodsInfoByBarcodeForPW",
						method : "POST",
						params : {
							barcode : field.getValue()
						},
						callback : function(options, success, response) {
							el.unmask();

							if (success) {
								var data = Ext.JSON
										.decode(response.responseText);
								if (data.success) {
									var goods = {
										goodsId : data.id,
										goodsCode : data.code,
										goodsName : data.name,
										goodsSpec : data.spec,
										unitName : data.unitName,
										goodsCount : 1,
										goodsPrice : data.purchasePrice,
										goodsMoney : data.purchasePrice
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
	}
});