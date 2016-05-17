/**
 * 商品 - 主界面
 * 
 * @author 李静波
 */
Ext.define("PSI.Goods.MainForm", {
	extend : "Ext.panel.Panel",

	config : {
		pAddCategory : null,
		pEditCategory : null,
		pDeleteCategory : null,
		pAddGoods : null,
		pEditGoods : null,
		pDeleteGoods : null,
		pImportGoods : null,
		pGoodsSI : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		var modelName = "PSIGoods";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "code", "name", "spec", "unitId",
							"unitName", "categoryId", "salePrice",
							"purchasePrice", "barCode", "memo", "dataOrg",
							"brandFullName"]
				});

		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : modelName,
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL + "Home/Goods/goodsList",
						reader : {
							root : 'goodsList',
							totalProperty : 'totalCount'
						}
					}
				});

		store.on("beforeload", function() {
					store.proxy.extraParams = me.getQueryParam();
				});
		store.on("load", function(e, records, successful) {
					if (successful) {
						me.refreshCategoryCount();
						me.gotoGoodsGridRecord(me.__lastId);
					}
				});

		var goodsGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "商品列表",
					bbar : [{
								id : "pagingToolbar",
								border : 0,
								xtype : "pagingtoolbar",
								store : store
							}, "-", {
								xtype : "displayfield",
								value : "每页显示"
							}, {
								id : "comboCountPerPage",
								xtype : "combobox",
								editable : false,
								width : 60,
								store : Ext.create("Ext.data.ArrayStore", {
											fields : ["text"],
											data : [["20"], ["50"], ["100"],
													["300"], ["1000"]]
										}),
								value : 20,
								listeners : {
									change : {
										fn : function() {
											store.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											store.currentPage = 1;
											Ext.getCmp("pagingToolbar")
													.doRefresh();
										},
										scope : me
									}
								}
							}, {
								xtype : "displayfield",
								value : "条记录"
							}],
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 30
									}), {
								header : "商品编码",
								dataIndex : "code",
								menuDisabled : true,
								sortable : false
							}, {
								header : "品名",
								dataIndex : "name",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "规格型号",
								dataIndex : "spec",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "计量单位",
								dataIndex : "unitName",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "品牌",
								dataIndex : "brandFullName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "销售价",
								dataIndex : "salePrice",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "建议采购价",
								dataIndex : "purchasePrice",
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn"
							}, {
								header : "条形码",
								dataIndex : "barCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备注",
								dataIndex : "memo",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
								menuDisabled : true,
								sortable : false
							}],
					store : store,
					listeners : {
						itemdblclick : {
							fn : me.onEditGoods,
							scope : me
						},
						select : {
							fn : me.onGoodsSelect,
							scope : me
						}
					}
				});

		me.goodsGrid = goodsGrid;

		Ext.apply(me, {
			border : 0,
			layout : "border",
			tbar : [{
						text : "新增商品分类",
						disabled : me.getPAddCategory() == "0",
						iconCls : "PSI-button-add",
						handler : me.onAddCategory,
						scope : me
					}, {
						text : "编辑商品分类",
						disabled : me.getPEditCategory() == "0",
						iconCls : "PSI-button-edit",
						handler : me.onEditCategory,
						scope : me
					}, {
						text : "删除商品分类",
						disabled : me.getPDeleteCategory() == "0",
						iconCls : "PSI-button-delete",
						handler : me.onDeleteCategory,
						scope : me
					}, "-", {
						text : "新增商品",
						disabled : me.getPAddGoods() == "0",
						iconCls : "PSI-button-add-detail",
						handler : me.onAddGoods,
						scope : me
					}, {
						text : "导入商品",
						disabled : me.getPImportGoods() == "0",
						iconCls : "PSI-button-excelimport",
						handler : me.onImportGoods,
						scope : me
					}, "-", {
						text : "修改商品",
						disabled : me.getPEditGoods() == "0",
						iconCls : "PSI-button-edit-detail",
						handler : me.onEditGoods,
						scope : me
					}, {
						text : "删除商品",
						disabled : me.getPDeleteGoods() == "0",
						iconCls : "PSI-button-delete-detail",
						handler : me.onDeleteGoods,
						scope : me
					}, "-", {
						text : "设置商品安全库存",
						disabled : me.getPGoodsSI() == "0",
						iconCls : "PSI-button-view",
						handler : me.onSafetyInventory,
						scope : me
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							window
									.open("http://my.oschina.net/u/134395/blog/374778");
						}
					}, "-", {
						text : "关闭",
						iconCls : "PSI-button-exit",
						handler : function() {
							location.replace(PSI.Const.BASE_URL);
						}
					}],
			items : [{
						region : "north",
						border : 0,
						height : 60,
						title : "查询条件",
						collapsible : true,
						layout : {
							type : "table",
							columns : 5
						},
						items : [{
									id : "editQueryCode",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "商品编码",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryName",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "品名",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQuerySpec",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "规格型号",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryBarCode",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "条形码",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onLastQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									xtype : "container",
									items : [{
												xtype : "button",
												text : "查询",
												width : 100,
												iconCls : "PSI-button-refresh",
												margin : "5, 0, 0, 20",
												handler : me.onQuery,
												scope : me
											}, {
												xtype : "button",
												text : "清空查询条件",
												width : 100,
												iconCls : "PSI-button-cancel",
												margin : "5, 0, 0, 5",
												handler : me.onClearQuery,
												scope : me
											}]
								}]
					}, {
						region : "center",
						layout : "border",
						items : [{
							region : "center",
							xtype : "panel",
							layout : "border",
							border : 0,
							items : [{
										region : "center",
										layout : "fit",
										border : 0,
										items : [goodsGrid]
									}, {
										region : "south",
										layout : "fit",
										border : 0,
										height : 200,
										split : true,
										xtype : "tabpanel",
										items : [me.getSIGrid(),
												me.getGoodsBOMGrid()]
									}]
						}, {
							xtype : "panel",
							region : "west",
							layout : "fit",
							width : 430,
							split : true,
							collapsible : true,
							border : 0,
							items : [me.getCategoryGrid()]
						}]
					}]
		});

		me.callParent(arguments);

		me.queryTotalGoodsCount();

		me.__queryEditNameList = ["editQueryCode", "editQueryName",
				"editQuerySpec", "editQueryBarCode"];
	},

	/**
	 * 新增商品分类
	 */
	onAddCategory : function() {
		var form = Ext.create("PSI.Goods.CategoryEditForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 编辑商品分类
	 */
	onEditCategory : function() {
		var item = this.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的商品分类");
			return;
		}

		var category = item[0];

		var form = Ext.create("PSI.Goods.CategoryEditForm", {
					parentForm : this,
					entity : category
				});

		form.show();
	},

	/**
	 * 删除商品分类
	 */
	onDeleteCategory : function() {
		var me = this;
		var item = me.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的商品分类");
			return;
		}

		var category = item[0];

		var store = me.categoryGrid.getStore();

		var info = "请确认是否删除商品分类: <span style='color:red'>"
				+ category.get("text") + "</span>";
		var me = this;
		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Goods/deleteCategory",
								method : "POST",
								params : {
									id : category.get("id")
								},
								callback : function(options, success, response) {
									el.unmask();

									if (success) {
										var data = Ext.JSON
												.decode(response.responseText);
										if (data.success) {
											PSI.MsgBox.tip("成功完成删除操作")
											me.freshCategoryGrid();
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									} else {
										PSI.MsgBox.showInfo("网络错误", function() {
													window.location.reload();
												});
									}
								}

							});
				});
	},

	/**
	 * 刷新商品分类Grid
	 */
	freshCategoryGrid : function(id) {
		var me = this;
		var store = me.getCategoryGrid().getStore();
		store.load();
	},

	/**
	 * 刷新商品Grid
	 */
	freshGoodsGrid : function() {
		var me = this;
		var item = me.getCategoryGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			var grid = me.goodsGrid;
			grid.setTitle("商品列表");
			return;
		}

		Ext.getCmp("pagingToolbar").doRefresh()
	},

	onCategoryGridSelect : function() {
		var me = this;
		me.getSIGrid().setTitle("商品安全库存");
		me.getSIGrid().getStore().removeAll();

		me.goodsGrid.getStore().currentPage = 1;

		me.freshGoodsGrid();
	},

	/**
	 * 新增商品
	 */
	onAddGoods : function() {
		if (this.getCategoryGrid().getStore().getCount() == 0) {
			PSI.MsgBox.showInfo("没有商品分类，请先新增商品分类");
			return;
		}

		var form = Ext.create("PSI.Goods.GoodsEditForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 编辑商品
	 */
	onEditGoods : function() {
		var me = this;
		if (me.getPEditGoods() == "0") {
			return;
		}

		var item = this.getCategoryGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择商品分类");
			return;
		}

		var category = item[0];

		var item = this.goodsGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的商品");
			return;
		}

		var goods = item[0];
		goods.set("categoryId", category.get("id"));
		var form = Ext.create("PSI.Goods.GoodsEditForm", {
					parentForm : this,
					entity : goods
				});

		form.show();
	},

	/**
	 * 删除商品
	 */
	onDeleteGoods : function() {
		var me = this;
		var item = me.goodsGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的商品");
			return;
		}

		var goods = item[0];

		var store = me.goodsGrid.getStore();
		var index = store.findExact("id", goods.get("id"));
		index--;
		var preItem = store.getAt(index);
		if (preItem) {
			me.__lastId = preItem.get("id");
		}

		var info = "请确认是否删除商品: <span style='color:red'>" + goods.get("name")
				+ " " + goods.get("spec") + "</span>";

		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Goods/deleteGoods",
								method : "POST",
								params : {
									id : goods.get("id")
								},
								callback : function(options, success, response) {
									el.unmask();

									if (success) {
										var data = Ext.JSON
												.decode(response.responseText);
										if (data.success) {
											PSI.MsgBox.tip("成功完成删除操作");
											me.freshGoodsGrid();
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									} else {
										PSI.MsgBox.showInfo("网络错误", function() {
													window.location.reload();
												});
									}
								}

							});
				});
	},

	gotoCategoryGridRecord : function(id) {
		var me = this;
		var grid = me.getCategoryGrid();
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		}
	},

	gotoGoodsGridRecord : function(id) {
		var me = this;
		var grid = me.goodsGrid;
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		}
	},

	refreshCategoryCount : function() {
		var me = this;
		var item = me.getCategoryGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}
	},

	onQueryEditSpecialKey : function(field, e) {
		if (e.getKey() === e.ENTER) {
			var me = this;
			var id = field.getId();
			for (var i = 0; i < me.__queryEditNameList.length - 1; i++) {
				var editorId = me.__queryEditNameList[i];
				if (id === editorId) {
					var edit = Ext.getCmp(me.__queryEditNameList[i + 1]);
					edit.focus();
					edit.setValue(edit.getValue());
				}
			}
		}
	},

	onLastQueryEditSpecialKey : function(field, e) {
		if (e.getKey() === e.ENTER) {
			this.onQuery();
		}
	},

	getQueryParamForCategory : function() {
		var me = this;
		var result = {};

		if (Ext.getCmp("editQueryCode") == null) {
			return result;
		}

		var code = Ext.getCmp("editQueryCode").getValue();
		if (code) {
			result.code = code;
		}

		var name = Ext.getCmp("editQueryName").getValue();
		if (name) {
			result.name = name;
		}

		var spec = Ext.getCmp("editQuerySpec").getValue();
		if (spec) {
			result.spec = spec;
		}

		var barCode = Ext.getCmp("editQueryBarCode").getValue();
		if (barCode) {
			result.barCode = barCode;
		}

		return result;
	},

	getQueryParam : function() {
		var me = this;
		var item = me.getCategoryGrid().getSelectionModel().getSelection();
		var categoryId;
		if (item == null || item.length != 1) {
			categoryId = null;
		} else {
			categoryId = item[0].get("id");
		}

		var result = {
			categoryId : categoryId
		};

		var code = Ext.getCmp("editQueryCode").getValue();
		if (code) {
			result.code = code;
		}

		var name = Ext.getCmp("editQueryName").getValue();
		if (name) {
			result.name = name;
		}

		var spec = Ext.getCmp("editQuerySpec").getValue();
		if (spec) {
			result.spec = spec;
		}

		var barCode = Ext.getCmp("editQueryBarCode").getValue();
		if (barCode) {
			result.barCode = barCode;
		}

		return result;
	},

	/**
	 * 查询
	 */
	onQuery : function() {
		var me = this;

		me.goodsGrid.getStore().removeAll();
		me.getSIGrid().getStore().removeAll();

		me.queryTotalGoodsCount();

		me.freshCategoryGrid();
	},

	/**
	 * 清除查询条件
	 */
	onClearQuery : function() {
		var me = this;
		var nameList = me.__queryEditNameList;
		for (var i = 0; i < nameList.length; i++) {
			var name = nameList[i];
			var edit = Ext.getCmp(name);
			if (edit) {
				edit.setValue(null);
			}
		}

		me.onQuery();
	},

	/**
	 * 安全库存Grid
	 */
	getSIGrid : function() {
		var me = this;
		if (me.__siGrid) {
			return me.__siGrid;
		}

		var modelName = "PSIGoodsSafetyInventory";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "warehouseCode", "warehouseName",
							"safetyInventory", "inventoryCount", "unitName",
							"inventoryUpper"]
				});

		me.__siGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "商品安全库存",
					columnLines : true,
					columns : [{
								header : "仓库编码",
								dataIndex : "warehouseCode",
								width : 80,
								menuDisabled : true,
								sortable : false
							}, {
								header : "仓库名称",
								dataIndex : "warehouseName",
								width : 100,
								menuDisabled : true,
								sortable : false
							}, {
								header : "库存上限",
								dataIndex : "inventoryUpper",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								format : "0"
							}, {
								header : "安全库存量",
								dataIndex : "safetyInventory",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								format : "0"
							}, {
								header : "当前库存",
								dataIndex : "inventoryCount",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								format : "0"
							}, {
								header : "计量单位",
								dataIndex : "unitName",
								width : 80,
								menuDisabled : true,
								sortable : false
							}],
					store : Ext.create("Ext.data.Store", {
								model : modelName,
								autoLoad : false,
								data : []
							}),
					listeners : {
						itemdblclick : {
							fn : me.onSafetyInventory,
							scope : me
						}
					}
				});

		return me.__siGrid;
	},

	onGoodsSelect : function() {
		var me = this;
		var item = me.goodsGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			me.getSIGrid().setTitle("商品安全库存");
			me.getGoodsBOMGrid().setTitle("商品构成");
			return;
		}

		var goods = item[0];
		var info = goods.get("code") + " " + goods.get("name") + " "
				+ goods.get("spec");

		var grid = me.getSIGrid();
		grid.setTitle("商品[" + info + "]的安全库存");

		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL
							+ "Home/Goods/goodsSafetyInventoryList",
					method : "POST",
					params : {
						id : goods.get("id")
					},
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}

						el.unmask();
					}
				});

		var gridBOM = me.getGoodsBOMGrid();
		var elBOM = gridBOM.getEl() || Ext.getBody();
		elBOM.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Goods/goodsBOMList",
					method : "POST",
					params : {
						id : goods.get("id")
					},
					callback : function(options, success, response) {
						var store = gridBOM.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}

						elBOM.unmask();
					}
				});
	},

	/**
	 * 设置安全库存
	 */
	onSafetyInventory : function() {
		var me = this;
		if (me.getPGoodsSI() == "0") {
			return;
		}

		var item = me.goodsGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要设置安全库存的商品");
			return;
		}

		var goods = item[0];

		var form = Ext.create("PSI.Goods.SafetyInventoryEditForm", {
					parentForm : me,
					entity : goods
				});

		form.show();
	},

	/**
	 * 导入商品资料
	 */
	onImportGoods : function() {
		var form = Ext.create("PSI.Goods.GoodsImportForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 商品分类Grid
	 */
	getCategoryGrid : function() {
		var me = this;
		if (me.__categoryGrid) {
			return me.__categoryGrid;
		}

		var modelName = "PSIGoodsCategory";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "text", "fullName", "code", "cnt", "leaf",
							"children"]
				});

		var store = Ext.create("Ext.data.TreeStore", {
			model : modelName,
			proxy : {
				type : "ajax",
				actionMethods : {
					read : "POST"
				},
				url : PSI.Const.BASE_URL + "Home/Goods/allCategories"
			},
			listeners : {
				beforeload : {
					fn : function() {
						store.proxy.extraParams = me.getQueryParamForCategory();
					},
					scope : me
				}
			}

		});

		store.on("load", me.onCategoryStoreLoad, me);

		me.__categoryGrid = Ext.create("Ext.tree.Panel", {
					title : "商品分类",
					store : store,
					rootVisible : false,
					useArrows : true,
					viewConfig : {
						loadMask : true
					},
					bbar : [{
								id : "fieldTotalGoodsCount",
								xtype : "displayfield",
								value : "共用商品0种"
							}],
					columns : {
						defaults : {
							sortable : false,
							menuDisabled : true,
							draggable : false
						},
						items : [{
									xtype : "treecolumn",
									text : "分类",
									dataIndex : "text",
									width : 220
								}, {
									text : "编码",
									dataIndex : "code",
									width : 100
								}, {
									text : "商品种类数",
									dataIndex : "cnt",
									align : "right",
									width : 80,
									renderer : function(value) {
										return value == 0 ? "" : value;
									}
								}]
					},
					listeners : {
						select : {
							fn : function(rowModel, record) {
								me.onCategoryTreeNodeSelect(record);
							},
							scope : me
						}
					}
				});

		me.categoryGrid = me.__categoryGrid;

		return me.__categoryGrid;
	},

	onCategoryStoreLoad : function() {
		var me = this;
		var tree = me.getCategoryGrid();
		var root = tree.getRootNode();
		if (root) {
			var node = root.firstChild;
			if (node) {
				// me.onOrgTreeNodeSelect(node);
			}
		}
	},

	onCategoryTreeNodeSelect : function(record) {
		if (!record) {
			return;
		}

		this.onCategoryGridSelect();
	},

	queryTotalGoodsCount : function() {
		var me = this;
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Goods/getTotalGoodsCount",
					method : "POST",
					params : me.getQueryParamForCategory(),
					callback : function(options, success, response) {

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							Ext.getCmp("fieldTotalGoodsCount").setValue("共有商品"
									+ data.cnt + "种");
						}
					}
				});
	},

	/**
	 * 商品构成Grid
	 */
	getGoodsBOMGrid : function() {
		var me = this;
		if (me.__bomGrid) {
			return me.__bomGrid;
		}

		var modelName = "PSIGoodsBOM";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "goodsCode", "goodsName", "goodsCount",
							"goodsSpec", "unitName"]
				});

		me.__bomGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "商品构成",
					columnLines : true,
					columns : [{
								header : "子商品编码",
								dataIndex : "goodsCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "子商品名称",
								dataIndex : "goodsName",
								width : 300,
								menuDisabled : true,
								sortable : false
							}, {
								header : "子商品规格型号",
								dataIndex : "goodsSpec",
								width : 200,
								menuDisabled : true,
								sortable : false
							}, {
								header : "子商品数量",
								dataIndex : "goodsCount",
								width : 120,
								menuDisabled : true,
								sortable : false,
								align : "right",
								xtype : "numbercolumn",
								format : "0"
							}, {
								header : "计量单位",
								dataIndex : "unitName",
								width : 80,
								menuDisabled : true,
								sortable : false
							}],
					store : Ext.create("Ext.data.Store", {
								model : modelName,
								autoLoad : false,
								data : []
							})
				});

		return me.__bomGrid;
	}
});