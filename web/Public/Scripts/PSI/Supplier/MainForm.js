/**
 * 供应商档案 - 主界面
 */
Ext.define("PSI.Supplier.MainForm", {
	extend : "Ext.panel.Panel",

	config : {
		pAddCategory : null,
		pEditCategory : null,
		pDeleteCategory : null,
		pAddSupplier : null,
		pEditSupplier : null,
		pDeleteSupplier : null
	},

	initComponent : function() {
		var me = this;

		Ext.define("PSISupplierCategory", {
					extend : "Ext.data.Model",
					fields : ["id", "code", "name", {
								name : "cnt",
								type : "int"
							}]
				});

		var categoryGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "供应商分类",
					features : [{
								ftype : "summary"
							}],
					forceFit : true,
					columnLines : true,
					columns : [{
								header : "分类编码",
								dataIndex : "code",
								width : 60,
								menuDisabled : true,
								sortable : false
							}, {
								header : "供应商分类",
								dataIndex : "name",
								flex : 1,
								menuDisabled : true,
								sortable : false,
								summaryRenderer : function() {
									return "供应商个数合计";
								}
							}, {
								header : "供应商个数",
								dataIndex : "cnt",
								width : 80,
								menuDisabled : true,
								sortable : false,
								summaryType : "sum",
								align : "right"
							}],
					store : Ext.create("Ext.data.Store", {
								model : "PSISupplierCategory",
								autoLoad : false,
								data : []
							}),
					listeners : {
						select : {
							fn : me.onCategoryGridSelect,
							scope : me
						},
						itemdblclick : {
							fn : me.onEditCategory,
							scope : me
						}
					}
				});
		me.categoryGrid = categoryGrid;

		Ext.define("PSISupplier", {
					extend : "Ext.data.Model",
					fields : ["id", "code", "name", "contact01", "tel01",
							"mobile01", "qq01", "contact02", "tel02",
							"mobile02", "qq02", "categoryId", "initPayables",
							"initPayablesDT", "address", "addressShipping",
							"bankName", "bankAccount", "tax", "fax", "note",
							"dataOrg"]
				});

		var store = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : "PSISupplier",
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL + "Home/Supplier/supplierList",
						reader : {
							root : 'supplierList',
							totalProperty : 'totalCount'
						}
					},
					listeners : {
						beforeload : {
							fn : function() {
								store.proxy.extraParams = me.getQueryParam();
							},
							scope : me
						},
						load : {
							fn : function(e, records, successful) {
								if (successful) {
									me.refreshCategoryCount();
									me.gotoSupplierGridRecord(me.__lastId);
								}
							},
							scope : me
						}
					}
				});

		var supplierGrid = Ext.create("Ext.grid.Panel", {
					viewConfig : {
						enableTextSelection : true
					},
					title : "供应商列表",
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 30
									}), {
								header : "供应商编码",
								dataIndex : "code",
								menuDisabled : true,
								sortable : false
							}, {
								header : "供应商名称",
								dataIndex : "name",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "地址",
								dataIndex : "address",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "联系人",
								dataIndex : "contact01",
								menuDisabled : true,
								sortable : false
							}, {
								header : "手机",
								dataIndex : "mobile01",
								menuDisabled : true,
								sortable : false
							}, {
								header : "固话",
								dataIndex : "tel01",
								menuDisabled : true,
								sortable : false
							}, {
								header : "QQ",
								dataIndex : "qq01",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备用联系人",
								dataIndex : "contact02",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备用联系人手机",
								dataIndex : "mobile02",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备用联系人固话",
								dataIndex : "tel02",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备用联系人QQ",
								dataIndex : "qq02",
								menuDisabled : true,
								sortable : false
							}, {
								header : "发货地址",
								dataIndex : "addressShipping",
								menuDisabled : true,
								sortable : false,
								width : 300
							}, {
								header : "开户行",
								dataIndex : "bankName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "开户行账号",
								dataIndex : "bankAccount",
								menuDisabled : true,
								sortable : false
							}, {
								header : "税号",
								dataIndex : "tax",
								menuDisabled : true,
								sortable : false
							}, {
								header : "传真",
								dataIndex : "fax",
								menuDisabled : true,
								sortable : false
							}, {
								header : "应付期初余额",
								dataIndex : "initPayables",
								align : "right",
								xtype : "numbercolumn",
								menuDisabled : true,
								sortable : false
							}, {
								header : "应付期初余额日期",
								dataIndex : "initPayablesDT",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备注",
								dataIndex : "note",
								menuDisabled : true,
								sortable : false,
								width : 400
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
								menuDisabled : true,
								sortable : false
							}],
					store : store,
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
					listeners : {
						itemdblclick : {
							fn : me.onEditSupplier,
							scope : me
						}
					}
				});

		me.supplierGrid = supplierGrid;

		Ext.apply(me, {
			border : 0,
			layout : "border",
			tbar : [{
						text : "新增供应商分类",
						disabled : me.getPAddCategory() == "0",
						iconCls : "PSI-button-add",
						handler : this.onAddCategory,
						scope : this
					}, {
						text : "编辑供应商分类",
						disabled : me.getPEditCategory() == "0",
						iconCls : "PSI-button-edit",
						handler : this.onEditCategory,
						scope : this
					}, {
						text : "删除供应商分类",
						disabled : me.getPDeleteCategory() == "0",
						iconCls : "PSI-button-delete",
						handler : this.onDeleteCategory,
						scope : this
					}, "-", {
						text : "新增供应商",
						disabled : me.getPAddSupplier() == "0",
						iconCls : "PSI-button-add-detail",
						handler : this.onAddSupplier,
						scope : this
					}, {
						text : "修改供应商",
						disabled : me.getPEditSupplier() == "0",
						iconCls : "PSI-button-edit-detail",
						handler : this.onEditSupplier,
						scope : this
					}, {
						text : "删除供应商",
						disabled : me.getPDeleteSupplier() == "0",
						iconCls : "PSI-button-delete-detail",
						handler : this.onDeleteSupplier,
						scope : this
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							window
									.open("http://my.oschina.net/u/134395/blog/374838");
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
						height : 90,
						border : 0,
						collapsible : true,
						title : "查询条件",
						layout : {
							type : "table",
							columns : 4
						},
						items : [{
									id : "editQueryCode",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "供应商编码",
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
									fieldLabel : "供应商名称",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryAddress",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "地址",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryContact",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "联系人",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryMobile",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "手机",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryTel",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "固话",
									margin : "5, 0, 0, 0",
									xtype : "textfield",
									listeners : {
										specialkey : {
											fn : me.onQueryEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editQueryQQ",
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									fieldLabel : "QQ",
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
						xtype : "container",
						layout : "border",
						border : 0,
						items : [{
									region : "center",
									xtype : "panel",
									layout : "fit",
									border : 0,
									items : [supplierGrid]
								}, {
									xtype : "panel",
									region : "west",
									layout : "fit",
									width : 300,
									minWidth : 200,
									maxWidth : 350,
									split : true,
									border : 0,
									items : [categoryGrid]
								}]
					}]
		});

		me.callParent(arguments);

		me.__queryEditNameList = ["editQueryCode", "editQueryName",
				"editQueryAddress", "editQueryContact", "editQueryMobile",
				"editQueryTel", "editQueryQQ"];

		me.freshCategoryGrid();
	},

	/**
	 * 新增供应商分类
	 */
	onAddCategory : function() {
		var form = Ext.create("PSI.Supplier.CategoryEditForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 编辑供应商分类
	 */
	onEditCategory : function() {
		var me = this;
		if (me.getPEditCategory() == "0") {
			return;
		}

		var item = this.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的供应商分类");
			return;
		}

		var category = item[0];

		var form = Ext.create("PSI.Supplier.CategoryEditForm", {
					parentForm : this,
					entity : category
				});

		form.show();
	},

	/**
	 * 删除供应商分类
	 */
	onDeleteCategory : function() {
		var item = this.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的供应商分类");
			return;
		}

		var category = item[0];
		var info = "请确认是否删除供应商分类: <span style='color:red'>"
				+ category.get("name") + "</span>";
		var me = this;

		var store = me.categoryGrid.getStore();
		var index = store.findExact("id", category.get("id"));
		index--;
		var preIndex = null;
		var preItem = store.getAt(index);
		if (preItem) {
			preIndex = preItem.get("id");
		}

		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Supplier/deleteCategory",
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
											PSI.MsgBox.tip("成功完成删除操作");
											me.freshCategoryGrid(preIndex);
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									}
								}
							});
				});
	},

	freshCategoryGrid : function(id) {
		var me = this;
		var grid = me.categoryGrid;
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Supplier/categoryList",
					method : "POST",
					params : me.getQueryParam(),
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);

							if (id) {
								var r = store.findExact("id", id);
								if (r != -1) {
									grid.getSelectionModel().select(r);
								}
							} else {
								grid.getSelectionModel().select(0);
							}
						}

						el.unmask();
					}
				});
	},
	freshSupplierGrid : function(id) {
		var item = this.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			var grid = this.supplierGrid;
			grid.setTitle("供应商档案");
			return;
		}

		var category = item[0];

		var grid = this.supplierGrid;
		grid.setTitle("属于分类 [" + category.get("name") + "] 的供应商");

		this.__lastId = id;
		Ext.getCmp("pagingToolbar").doRefresh()
	},

	onCategoryGridSelect : function() {
		var me = this;
		me.supplierGrid.getStore().currentPage = 1;
		me.freshSupplierGrid();
	},
	onAddSupplier : function() {
		if (this.categoryGrid.getStore().getCount() == 0) {
			PSI.MsgBox.showInfo("没有供应商分类，请先新增供应商分类");
			return;
		}

		var form = Ext.create("PSI.Supplier.SupplierEditForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 编辑供应商档案
	 */
	onEditSupplier : function() {
		var me = this;
		if (me.getPEditSupplier() == "0") {
			return;
		}

		var item = this.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("没有选择供应商分类");
			return;
		}
		var category = item[0];

		var item = this.supplierGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的供应商");
			return;
		}

		var supplier = item[0];
		supplier.set("categoryId", category.get("id"));
		var form = Ext.create("PSI.Supplier.SupplierEditForm", {
					parentForm : this,
					entity : supplier
				});

		form.show();
	},
	onDeleteSupplier : function() {
		var me = this;
		var item = me.supplierGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的供应商");
			return;
		}

		var supplier = item[0];

		var store = me.supplierGrid.getStore();
		var index = store.findExact("id", supplier.get("id"));
		index--;
		var preIndex = null;
		var preItem = store.getAt(index);
		if (preItem) {
			preIndex = preItem.get("id");
		}

		var info = "请确认是否删除供应商: <span style='color:red'>"
				+ supplier.get("name") + "</span>";
		var me = this;
		PSI.MsgBox.confirm(info, function() {
					var el = Ext.getBody();
					el.mask("正在删除中...");
					Ext.Ajax.request({
								url : PSI.Const.BASE_URL
										+ "Home/Supplier/deleteSupplier",
								method : "POST",
								params : {
									id : supplier.get("id")
								},
								callback : function(options, success, response) {
									el.unmask();

									if (success) {
										var data = Ext.JSON
												.decode(response.responseText);
										if (data.success) {
											PSI.MsgBox.tip("成功完成删除操作");
											me.freshSupplierGrid(preIndex);
										} else {
											PSI.MsgBox.showInfo(data.msg);
										}
									}
								}

							});
				});
	},
	gotoCategoryGridRecord : function(id) {
		var me = this;
		var grid = me.categoryGrid;
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
	gotoSupplierGridRecord : function(id) {
		var me = this;
		var grid = me.supplierGrid;
		var store = grid.getStore();
		if (id) {
			var r = store.findExact("id", id);
			if (r != -1) {
				grid.getSelectionModel().select(r);
			} else {
				grid.getSelectionModel().select(0);
			}
		} else {
			grid.getSelectionModel().select(0);
		}
	},
	refreshCategoryCount : function() {
		var me = this;
		var item = me.categoryGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}

		var category = item[0];
		category.set("cnt", me.supplierGrid.getStore().getTotalCount());
		me.categoryGrid.getStore().commitChanges();
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

	getQueryParam : function() {
		var me = this;
		var item = me.categoryGrid.getSelectionModel().getSelection();
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

		var address = Ext.getCmp("editQueryAddress").getValue();
		if (address) {
			result.address = address;
		}

		var name = Ext.getCmp("editQueryName").getValue();
		if (name) {
			result.name = name;
		}

		var contact = Ext.getCmp("editQueryContact").getValue();
		if (contact) {
			result.contact = contact;
		}

		var mobile = Ext.getCmp("editQueryMobile").getValue();
		if (mobile) {
			result.mobile = mobile;
		}

		var tel = Ext.getCmp("editQueryTel").getValue();
		if (tel) {
			result.tel = tel;
		}

		var qq = Ext.getCmp("editQueryQQ").getValue();
		if (qq) {
			result.qq = qq;
		}

		return result;
	},

	onQuery : function() {
		this.freshCategoryGrid();
	},

	onClearQuery : function() {
		var nameList = this.__queryEditNameList;
		for (var i = 0; i < nameList.length; i++) {
			var name = nameList[i];
			var edit = Ext.getCmp(name);
			if (edit) {
				edit.setValue(null);
			}
		}

		this.onQuery();
	}
});