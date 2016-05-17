/**
 * 仓库 - 主界面
 */
Ext.define("PSI.Warehouse.MainForm", {
	extend : "Ext.panel.Panel",
	border : 0,
	layout : "border",

	config : {
		pAdd : null,
		pEdit : null,
		pDelete : null,
		pEditDataOrg : null
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.apply(me, {
			tbar : [{
						text : "新增仓库",
						disabled : me.getPAdd() == "0",
						iconCls : "PSI-button-add",
						handler : me.onAddWarehouse,
						scope : me
					}, {
						text : "编辑仓库",
						iconCls : "PSI-button-edit",
						disabled : me.getPEdit() == "0",
						handler : me.onEditWarehouse,
						scope : me
					}, {
						text : "删除仓库",
						disabled : me.getPDelete() == "0",
						iconCls : "PSI-button-delete",
						handler : me.onDeleteWarehouse,
						scope : me
					}, "-", {
						text : "修改数据域",
						disabled : me.getPEditDataOrg() == "0",
						iconCls : "PSI-button-dataorg",
						handler : me.onEditDataOrg,
						scope : me
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							window
									.open("http://my.oschina.net/u/134395/blog/374807");
						}
					}, "-", {
						text : "关闭",
						iconCls : "PSI-button-exit",
						handler : function() {
							location.replace(PSI.Const.BASE_URL);
						}
					}],
			items : [{
						region : "center",
						xtype : "panel",
						layout : "fit",
						border : 0,
						items : [me.getMainGrid()]
					}]
		});

		me.callParent(arguments);

		me.freshGrid();
	},

	/**
	 * 新增仓库
	 */
	onAddWarehouse : function() {
		var form = Ext.create("PSI.Warehouse.EditForm", {
					parentForm : this
				});

		form.show();
	},

	/**
	 * 编辑仓库
	 */
	onEditWarehouse : function() {
		var me = this;

		if (me.getPEdit() == "0") {
			return;
		}

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的仓库");
			return;
		}

		var warehouse = item[0];

		var form = Ext.create("PSI.Warehouse.EditForm", {
					parentForm : me,
					entity : warehouse
				});

		form.show();
	},

	/**
	 * 删除仓库
	 */
	onDeleteWarehouse : function() {
		var me = this;
		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的仓库");
			return;
		}

		var warehouse = item[0];
		var info = "请确认是否删除仓库 <span style='color:red'>" + warehouse.get("name")
				+ "</span> ?";

		var store = me.getMainGrid().getStore();
		var index = store.findExact("id", warehouse.get("id"));
		index--;
		var preIndex = null;
		var preWarehouse = store.getAt(index);
		if (preWarehouse) {
			preIndex = preWarehouse.get("id");
		}

		var funcConfirm = function() {
			var el = Ext.getBody();
			el.mask(PSI.Const.LOADING);
			var r = {
				url : PSI.Const.BASE_URL + "Home/Warehouse/deleteWarehouse",
				params : {
					id : warehouse.get("id")
				},
				method : "POST",
				callback : function(options, success, response) {
					el.unmask();
					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.tip("成功完成删除操作");
							me.freshGrid(preIndex);
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					} else {
						PSI.MsgBox.showInfo("网络错误");
					}
				}
			};

			Ext.Ajax.request(r);
		};

		PSI.MsgBox.confirm(info, funcConfirm);
	},

	freshGrid : function(id) {
		var me = this;
		var grid = me.getMainGrid();
		var el = grid.getEl() || Ext.getBody();
		el.mask(PSI.Const.LOADING);
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Warehouse/warehouseList",
					method : "POST",
					callback : function(options, success, response) {
						var store = grid.getStore();

						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);

							me.gotoGridRecord(id);
						}

						el.unmask();
					}
				});
	},

	gotoGridRecord : function(id) {
		var me = this;
		var grid = me.getMainGrid();
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

	getMainGrid : function() {
		var me = this;
		if (me.__mainGrid) {
			return me.__mainGrid;
		}

		var modelName = "PSIWarehouse";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["id", "code", "name", "inited", "dataOrg"]
				});

		me.__mainGrid = Ext.create("Ext.grid.Panel", {
					border : 0,
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : [{
								xtype : "rownumberer"
							}, {
								header : "仓库编码",
								dataIndex : "code",
								menuDisabled : true,
								sortable : false,
								width : 60
							}, {
								header : "仓库名称",
								dataIndex : "name",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "建账完毕",
								dataIndex : "inited",
								menuDisabled : true,
								sortable : false,
								width : 70,
								renderer : function(value) {
									return value == 1
											? "完毕"
											: "<span style='color:red'>未完</span>";
								}
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
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
							fn : me.onEditWarehouse,
							scope : me
						}
					}
				});

		return me.__mainGrid;
	},

	/**
	 * 编辑数据域
	 */
	onEditDataOrg : function() {
		var me = this;

		var item = me.getMainGrid().getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑数据域的仓库");
			return;
		}

		var warehouse = item[0];

		var form = Ext.create("PSI.Warehouse.EditDataOrgForm", {
					parentForm : me,
					entity : warehouse
				});

		form.show();
	}
});