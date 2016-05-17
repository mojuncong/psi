/**
 * 首页
 */
Ext.define("PSI.Home.MainForm", {
			extend : "Ext.panel.Panel",

			config : {
				pSale : "",
				pInventory : "",
				pPurchase : "",
				pMoney : ""
			},

			border : 0,
			bodyPadding : 5,

			getPortal : function(index) {
				var me = this;
				if (!me.__portalList) {
					me.__portalList = [];
					var pSale = me.getPSale() == "1";
					if (pSale) {
						me.__portalList.push(me.getSalePortal());
					}

					var pInventory = me.getPInventory() == "1";
					if (pInventory) {
						me.__portalList.push(me.getInventoryPortal());
					}

					var pPurchase = me.getPPurchase() == "1";
					if (pPurchase) {
						me.__portalList.push(me.getPurchasePortal());
					}

					var pMoney = me.getPMoney() == "1";
					if (pMoney) {
						me.__portalList.push(me.getMoneyPortal());
					}
				}

				if (index == 0 && me.__portalList.length == 0) {
					return me.getInfoPortal();
				}

				if (index >= me.__portalList.length || index < 0) {
					return {
						border : 0
					};
				}

				return me.__portalList[index];
			},

			initComponent : function() {
				var me = this;

				Ext.apply(me, {
							layout : "hbox",
							items : [{
										region : "west",
										flex : 1,
										layout : "vbox",
										border : 0,
										items : [me.getPortal(0),
												me.getPortal(2)]
									}, {
										flex : 1,
										layout : "vbox",
										border : 0,
										items : [me.getPortal(1),
												me.getPortal(3)]
									}]
						});

				me.callParent(arguments);

				me.querySaleData();
				me.queryInventoryData();
				me.queryPurchaseData();
				me.queryMoneyData();
			},

			getSaleGrid : function() {
				var me = this;
				if (me.__saleGrid) {
					return me.__saleGrid;
				}

				var modelName = "PSIPortalSale";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["month", "saleMoney", "profit", "rate"]
						});

				me.__saleGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							columnLines : true,
							border : 0,
							columns : [{
										header : "月份",
										dataIndex : "month",
										width : 80,
										menuDisabled : true,
										sortable : false
									}, {
										header : "销售额",
										dataIndex : "saleMoney",
										width : 120,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "毛利",
										dataIndex : "profit",
										width : 120,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "毛利率",
										dataIndex : "rate",
										menuDisabled : true,
										sortable : false,
										align : "right"
									}],
							store : Ext.create("Ext.data.Store", {
										model : modelName,
										autoLoad : false,
										data : []
									})
						});

				return me.__saleGrid;
			},

			getPurchaseGrid : function() {
				var me = this;
				if (me.__purchaseGrid) {
					return me.__purchaseGrid;
				}

				var modelName = "PSIPortalPurchase";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["month", "purchaseMoney"]
						});

				me.__purchaseGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							columnLines : true,
							border : 0,
							columns : [{
										header : "月份",
										dataIndex : "month",
										width : 80,
										menuDisabled : true,
										sortable : false
									}, {
										header : "采购额",
										dataIndex : "purchaseMoney",
										width : 120,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}],
							store : Ext.create("Ext.data.Store", {
										model : modelName,
										autoLoad : false,
										data : []
									})
						});

				return me.__purchaseGrid;
			},

			getInventoryGrid : function() {
				var me = this;
				if (me.__inventoryGrid) {
					return me.__inventoryGrid;
				}

				var modelName = "PSIPortalInventory";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["warehouseName", "inventoryMoney",
									"siCount", "iuCount"]
						});

				me.__inventoryGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							columnLines : true,
							border : 0,
							columns : [{
										header : "仓库",
										dataIndex : "warehouseName",
										width : 160,
										menuDisabled : true,
										sortable : false
									}, {
										header : "存货金额",
										dataIndex : "inventoryMoney",
										width : 140,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "低于安全库存商品种类数",
										dataIndex : "siCount",
										width : 160,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										format : "0",
										renderer : function(value) {
											return value > 0
													? "<span style='color:red'>"
															+ value + "</span>"
													: value;
										}
									}, {
										header : "超过库存上限的商品种类数",
										dataIndex : "iuCount",
										width : 160,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn",
										format : "0",
										renderer : function(value) {
											return value > 0
													? "<span style='color:red'>"
															+ value + "</span>"
													: value;
										}
									}],
							store : Ext.create("Ext.data.Store", {
										model : modelName,
										autoLoad : false,
										data : []
									})
						});

				return me.__inventoryGrid;
			},

			getMoneyGrid : function() {
				var me = this;
				if (me.__moneyGrid) {
					return me.__moneyGrid;
				}

				var modelName = "PSIPortalMoney";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["item", "balanceMoney", "money30",
									"money30to60", "money60to90", "money90"]
						});

				me.__moneyGrid = Ext.create("Ext.grid.Panel", {
							viewConfig : {
								enableTextSelection : true
							},
							columnLines : true,
							border : 0,
							columns : [{
										header : "款项",
										dataIndex : "item",
										width : 80,
										menuDisabled : true,
										sortable : false
									}, {
										header : "当期余额",
										dataIndex : "balanceMoney",
										width : 120,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "账龄30天内",
										dataIndex : "money30",
										width : 120,
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "账龄30-60天",
										dataIndex : "money30to60",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "账龄60-90天",
										dataIndex : "money60to90",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}, {
										header : "账龄大于90天",
										dataIndex : "money90",
										menuDisabled : true,
										sortable : false,
										align : "right",
										xtype : "numbercolumn"
									}],
							store : Ext.create("Ext.data.Store", {
										model : modelName,
										autoLoad : false,
										data : []
									})
						});

				return me.__moneyGrid;
			},

			getSalePortal : function() {
				var me = this;
				return {
					flex : 1,
					width : "100%",
					height : 240,
					margin : "5",
					header : {
						title : "<span style='font-size:120%'>销售看板</span>",
						iconCls : "PSI-portal-sale",
						height : 40
					},
					layout : "fit",
					items : [me.getSaleGrid()]
				};
			},

			getPurchasePortal : function() {
				var me = this;
				return {
					header : {
						title : "<span style='font-size:120%'>采购看板</span>",
						iconCls : "PSI-portal-purchase",
						height : 40
					},
					flex : 1,
					width : "100%",
					height : 240,
					margin : "5",
					layout : "fit",
					items : [me.getPurchaseGrid()]
				};
			},

			getInventoryPortal : function() {
				var me = this;
				return {
					header : {
						title : "<span style='font-size:120%'>库存看板</span>",
						iconCls : "PSI-portal-inventory",
						height : 40
					},
					flex : 1,
					width : "100%",
					height : 240,
					margin : "5",
					layout : "fit",
					items : [me.getInventoryGrid()]
				};
			},

			getMoneyPortal : function() {
				var me = this;
				return {
					header : {
						title : "<span style='font-size:120%'>资金看板</span>",
						iconCls : "PSI-portal-money",
						height : 40
					},
					flex : 1,
					width : "100%",
					height : 240,
					margin : "5",
					layout : "fit",
					items : [me.getMoneyGrid()]
				};
			},

			queryInventoryData : function() {
				var me = this;
				var grid = me.getInventoryGrid();
				var el = grid.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL
									+ "Home/Portal/inventoryPortal",
							method : "POST",
							callback : function(options, success, response) {
								var store = grid.getStore();
								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);
								}

								el.unmask();
							}
						});
			},

			querySaleData : function() {
				var me = this;
				var grid = me.getSaleGrid();
				var el = grid.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL + "Home/Portal/salePortal",
							method : "POST",
							callback : function(options, success, response) {
								var store = grid.getStore();
								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);
								}

								el.unmask();
							}
						});
			},

			queryPurchaseData : function() {
				var me = this;
				var grid = me.getPurchaseGrid();
				var el = grid.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL
									+ "Home/Portal/purchasePortal",
							method : "POST",
							callback : function(options, success, response) {
								var store = grid.getStore();
								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);
								}

								el.unmask();
							}
						});
			},

			queryMoneyData : function() {
				var me = this;
				var grid = me.getMoneyGrid();
				var el = grid.getEl() || Ext.getBody();
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL
									+ "Home/Portal/moneyPortal",
							method : "POST",
							callback : function(options, success, response) {
								var store = grid.getStore();
								store.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									store.add(data);
								}

								el.unmask();
							}
						});
			},

			getInfoPortal : function() {
				return {
					border : 0,
					html : "<h1>欢迎使用开源进销存PSI</h1>"
				}
			}
		});