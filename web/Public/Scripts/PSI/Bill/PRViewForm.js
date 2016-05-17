/**
 * 采购退货出库单 - 查看界面
 */
Ext.define("PSI.Bill.PRViewForm", {
			extend : "Ext.window.Window",

			config : {
				ref : null
			},

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							title : "查看采购退货出库单",
							modal : true,
							onEsc : Ext.emptyFn,
							maximized : true,
							closable : false,
							width : 1200,
							height : 600,
							layout : "border",
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
										height : 100,
										bodyPadding : 10,
										border : 0,
										items : [{
													id : "editSupplier",
													xtype : "displayfield",
													fieldLabel : "供应商",
													labelWidth : 60,
													labelAlign : "right",
													labelSeparator : ":",
													colspan : 2,
													width : 500
												}, {
													id : "editRef",
													labelWidth : 60,
													labelAlign : "right",
													labelSeparator : ":",
													fieldLabel : "单号",
													xtype : "displayfield",
													value : me.getRef()
												}, {
													id : "editBizDT",
													fieldLabel : "业务日期",
													labelWidth : 60,
													labelAlign : "right",
													labelSeparator : ":",
													xtype : "displayfield"
												}, {
													id : "editWarehouse",
													labelWidth : 60,
													labelAlign : "right",
													labelSeparator : ":",
													fieldLabel : "出库仓库",
													xtype : "displayfield"
												}, {
													id : "editBizUser",
													labelWidth : 60,
													labelAlign : "right",
													labelSeparator : ":",
													fieldLabel : "业务员",
													xtype : "displayfield"
												}]
									}],
							listeners : {
								show : {
									fn : me.onWndShow,
									scope : me
								}
							}
						});

				me.callParent(arguments);
			},

			onWndShow : function() {
				var me = this;

				var el = me.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL + "Home/Bill/prBillInfo",
							params : {
								ref : me.getRef()
							},
							method : "POST",
							callback : function(options, success, response) {
								el.unmask();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);

									Ext.getCmp("editSupplier")
											.setValue(data.supplierName
													+ " 采购入库单单号："
													+ data.pwbillRef);

									Ext.getCmp("editWarehouse")
											.setValue(data.warehouseName);

									Ext.getCmp("editBizUser")
											.setValue(data.bizUserName);
									Ext.getCmp("editBizDT")
											.setValue(data.bizDT);

									var store = me.getGoodsGrid().getStore();
									store.removeAll();
									if (data.items) {
										store.add(data.items);
									}
								}
							}
						});
			},

			getGoodsGrid : function() {
				var me = this;
				if (me.__goodsGrid) {
					return me.__goodsGrid;
				}
				var modelName = "PSIPRBillDetail_ViewForm";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "goodsId", "goodsCode",
									"goodsName", "goodsSpec", "unitName",
									"goodsCount", "goodsMoney", "goodsPrice",
									"rejCount", "rejPrice", "rejMoney"]
						});
				var store = Ext.create("Ext.data.Store", {
							autoLoad : false,
							model : modelName,
							data : []
						});

				me.__goodsGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							columnLines : true,
							columns : [Ext.create("Ext.grid.RowNumberer", {
												text : "序号",
												width : 30
											}), {
										header : "商品编码",
										dataIndex : "goodsCode",
										menuDisabled : true,
										sortable : false
									}, {
										header : "商品名称",
										dataIndex : "goodsName",
										menuDisabled : true,
										sortable : false,
										width : 200
									}, {
										header : "规格型号",
										dataIndex : "goodsSpec",
										menuDisabled : true,
										sortable : false,
										width : 200
									}, {
										header : "退货数量",
										dataIndex : "rejCount",
										menuDisabled : true,
										sortable : false,
										align : "right",
										width : 100
									}, {
										header : "单位",
										dataIndex : "unitName",
										menuDisabled : true,
										sortable : false,
										width : 60
									}, {
										header : "退货单价",
										dataIndex : "rejPrice",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										width : 100
									}, {
										header : "退货金额",
										dataIndex : "rejMoney",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										width : 120
									}, {
										header : "原采购数量",
										dataIndex : "goodsCount",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										width : 120,
										format : "0"
									}, {
										header : "原采购单价",
										dataIndex : "goodsPrice",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										width : 120
									}, {
										header : "原采购金额",
										dataIndex : "goodsMoney",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										width : 120
									}],
							store : store
						});

				return me.__goodsGrid;
			}
		});