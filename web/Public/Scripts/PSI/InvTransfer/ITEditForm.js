/**
 * 调拨单
 */
Ext.define("PSI.InvTransfer.ITEditForm", {
	extend : "Ext.window.Window",

	config : {
		parentForm : null,
		entity : null
	},

	initComponent : function() {
		var me = this;
		me.__readonly = false;
		var entity = me.getEntity();
		me.adding = entity == null;

		Ext.apply(me, {
			title : entity == null ? "新建调拨单" : "编辑调拨单",
			modal : true,
			onEsc : Ext.emptyFn,
			maximized : true,
			width : 1000,
			height : 600,
			layout : "border",
			defaultFocus : "editFromWarehouse",
			tbar : ["-", {
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
							PSI.MsgBox.confirm("请确认是否取消当前操作?", function() {
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
						height : 100,
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
									id : "editFromWarehouse",
									fieldLabel : "调出仓库",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_warehousefield",
									fid : "2009",
									allowBlank : false,
									blankText : "没有输入调出仓库",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditFromWarehouseSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editToWarehouse",
									fieldLabel : "调入仓库",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									xtype : "psi_warehousefield",
									fid : "2009",
									allowBlank : false,
									blankText : "没有输入调入仓库",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditToWarehouseSpecialKey,
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
					url : PSI.Const.BASE_URL + "Home/InvTransfer/itBillInfo",
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
							}

							Ext.getCmp("editBizUser")
									.setIdValue(data.bizUserId);
							Ext.getCmp("editBizUser")
									.setValue(data.bizUserName);
							if (data.bizDT) {
								Ext.getCmp("editBizDT").setValue(data.bizDT);
							}
							if (data.fromWarehouseId) {
								Ext.getCmp("editFromWarehouse")
										.setIdValue(data.fromWarehouseId);
								Ext.getCmp("editFromWarehouse")
										.setValue(data.fromWarehouseName);
							}
							if (data.toWarehouseId) {
								Ext.getCmp("editToWarehouse")
										.setIdValue(data.toWarehouseId);
								Ext.getCmp("editToWarehouse")
										.setValue(data.toWarehouseName);
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
			url : PSI.Const.BASE_URL + "Home/InvTransfer/editITBill",
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
			Ext.getCmp("editFromWarehouse").focus();
		}
	},

	onEditFromWarehouseSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editToWarehouse").focus();
		}
	},
	onEditToWarehouseSpecialKey : function(field, e) {
		if (e.getKey() == e.ENTER) {
			Ext.getCmp("editBizUser").focus();
		}
	},
	onEditBizUserSpecialKey : function(field, e) {
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
		var modelName = "PSIITBillDetail_EditForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsId", "goodsCode", "goodsName",
							"goodsSpec", "unitName", "goodsCount"]
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
								sortable : false,
								editor : {
									xtype : "psi_goodsfield",
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
								header : "调拨数量",
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
		if (e.colIdx == 4) {
			if (!me.__canEditGoodsPrice) {
				var store = me.getGoodsGrid().getStore();
				if (e.rowIdx == store.getCount() - 1) {
					store.add({});
				}
				e.rowIdx += 1;
				me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
				me.__cellEditing.startEdit(e.rowIdx, 1);
			}
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
	},

	getSaveData : function() {
		var result = {
			id : Ext.getCmp("hiddenId").getValue(),
			bizDT : Ext.Date
					.format(Ext.getCmp("editBizDT").getValue(), "Y-m-d"),
			fromWarehouseId : Ext.getCmp("editFromWarehouse").getIdValue(),
			toWarehouseId : Ext.getCmp("editToWarehouse").getIdValue(),
			bizUserId : Ext.getCmp("editBizUser").getIdValue(),
			items : []
		};

		var store = this.getGoodsGrid().getStore();
		for (var i = 0; i < store.getCount(); i++) {
			var item = store.getAt(i);
			result.items.push({
						id : item.get("id"),
						goodsId : item.get("goodsId"),
						goodsCount : item.get("goodsCount")
					});
		}

		return Ext.JSON.encode(result);
	},

	setBillReadonly : function() {
		var me = this;
		me.__readonly = true;
		me.setTitle("查看调拨单");
		Ext.getCmp("buttonSave").setDisabled(true);
		Ext.getCmp("buttonCancel").setText("关闭");
		Ext.getCmp("editBizDT").setReadOnly(true);
		Ext.getCmp("editFromWarehouse").setReadOnly(true);
		Ext.getCmp("editToWarehouse").setReadOnly(true);
		Ext.getCmp("editBizUser").setReadOnly(true);
		Ext.getCmp("columnActionDelete").hide();
		Ext.getCmp("columnActionAdd").hide();
		Ext.getCmp("columnActionAppend").hide();
	}
});