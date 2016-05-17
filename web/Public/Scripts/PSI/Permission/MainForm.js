/**
 * 权限管理 - 主界面
 */
Ext.define("PSI.Permission.MainForm", {
	extend : "Ext.panel.Panel",

	config : {
		pAdd : "",
		pEdit : "",
		pDelete : ""
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.define("PSIRole", {
					extend : "Ext.data.Model",
					fields : ["id", "name"]
				});

		var roleStore = Ext.create("Ext.data.Store", {
					model : "PSIRole",
					autoLoad : false,
					data : []
				});

		var roleGrid = Ext.create("Ext.grid.Panel", {
					title : "角色",
					store : roleStore,
					columns : [{
								header : "角色名称",
								dataIndex : "name",
								flex : 1,
								menuDisabled : true
							}]
				});

		roleGrid.on("itemclick", me.onRoleGridItemClick, me);

		Ext.define("PSIPermission", {
					extend : "Ext.data.Model",
					fields : ["id", "name", "dataOrg"]
				});

		var permissionStore = Ext.create("Ext.data.Store", {
					model : "PSIPermission",
					autoLoad : false,
					data : []
				});

		var permissionGrid = Ext.create("Ext.grid.Panel", {
					store : permissionStore,
					columnLines : true,
					columns : [{
								header : "权限名称",
								dataIndex : "name",
								flex : 2,
								menuDisabled : true
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
								flex : 1,
								menuDisabled : true
							}],
					listeners : {
						itemclick : {
							fn : me.onPermissionGridItemClick,
							scope : me
						}
					}
				});

		Ext.define("PSIUser", {
					extend : "Ext.data.Model",
					fields : ["id", "loginName", "name", "orgFullName",
							"enabled"]
				});

		var userStore = Ext.create("Ext.data.Store", {
					model : "PSIUser",
					autoLoad : false,
					data : []
				});

		var userGrid = Ext.create("Ext.grid.Panel", {
					store : userStore,
					columns : [{
								header : "用户姓名",
								dataIndex : "name",
								flex : 1
							}, {
								header : "登录名",
								dataIndex : "loginName",
								flex : 1
							}, {
								header : "所属组织",
								dataIndex : "orgFullName",
								flex : 1
							}]
				});

		me.roleGrid = roleGrid;
		me.permissionGrid = permissionGrid;
		me.userGrid = userGrid;

		Ext.apply(me, {
			border : 0,
			layout : "border",
			tbar : [{
						text : "新增角色",
						handler : me.onAddRole,
						scope : me,
						disabled : me.getPAdd() == "0",
						iconCls : "PSI-button-add"
					}, {
						text : "编辑角色",
						handler : me.onEditRole,
						scope : me,
						disabled : me.getPEdit() == "0",
						iconCls : "PSI-button-edit"
					}, {
						text : "删除角色",
						handler : me.onDeleteRole,
						scope : me,
						disabled : me.getPDelete() == "0",
						iconCls : "PSI-button-delete"
					}, "-", {
						text : "帮助",
						iconCls : "PSI-help",
						handler : function() {
							window
									.open("http://my.oschina.net/u/134395/blog/374337");
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
				items : [{
							xtype : "panel",
							layout : "border",
							items : [{
										xtype : "panel",
										region : "north",
										height : "50%",
										border : 0,
										split : true,
										layout : "border",
										items : [{
													region : "center",
													layout : "fit",
													border : 0,
													items : [permissionGrid]
												}, {
													region : "east",
													layout : "fit",
													width : "50%",
													border : 0,
													items : [me
															.getDataOrgGrid()]
												}]
									}, {
										xtype : "panel",
										region : "center",
										border : 0,
										layout : "fit",
										items : [userGrid]
									}]
						}]
			}, {
				xtype : "panel",
				region : "west",
				layout : "fit",
				width : 300,
				minWidth : 200,
				maxWidth : 350,
				split : true,
				border : 0,
				items : [roleGrid]
			}]
		});

		me.callParent(arguments);

		me.refreshRoleGrid();
	},

	/**
	 * 刷新角色Grid
	 */
	refreshRoleGrid : function(id) {
		var grid = this.roleGrid;
		var store = grid.getStore();
		var me = this;
		Ext.getBody().mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/roleList",
					method : "POST",
					callback : function(options, success, response) {
						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);

							if (data.length > 0) {
								if (id) {
									var r = store.findExact("id", id);
									if (r != -1) {
										grid.getSelectionModel().select(r);
									}
								} else {
									grid.getSelectionModel().select(0);
								}
								me.onRoleGridItemClick();
							}
						}

						Ext.getBody().unmask();
					}
				});
	},

	onRoleGridItemClick : function() {
		var me = this;
		me.getDataOrgGrid().getStore().removeAll();
		me.getDataOrgGrid().setTitle("数据域");

		var grid = this.permissionGrid;

		var item = this.roleGrid.getSelectionModel().getSelection();
		if (item == null || item.length != 1) {
			return;
		}

		var role = item[0].data;
		var store = grid.getStore();
		grid.setTitle("角色 [" + role.name + "] 的权限列表");

		var el = grid.getEl() || Ext.getBody();

		el.mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/permissionList",
					params : {
						roleId : role.id
					},
					method : "POST",
					callback : function(options, success, response) {
						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}

						el.unmask();
					}
				});

		var userGrid = this.userGrid;
		var userStore = userGrid.getStore();
		var userEl = userGrid.getEl() || Ext.getBody();
		userGrid.setTitle("属于角色 [" + role.name + "] 的人员列表");
		userEl.mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/userList",
					params : {
						roleId : role.id
					},
					method : "POST",
					callback : function(options, success, response) {
						userStore.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							userStore.add(data);
						}

						userEl.unmask();
					}
				});
	},

	/**
	 * 新增角色
	 */
	onAddRole : function() {
		var editForm = Ext.create("PSI.Permission.EditForm", {
					parentForm : this
				});

		editForm.show();
	},

	/**
	 * 编辑角色
	 */
	onEditRole : function() {
		var grid = this.roleGrid;
		var items = grid.getSelectionModel().getSelection();

		if (items == null || items.length != 1) {
			PSI.MsgBox.showInfo("请选择要编辑的角色");
			return;
		}

		var role = items[0].data;

		var editForm = Ext.create("PSI.Permission.EditForm", {
					entity : role,
					parentForm : this
				});

		editForm.show();
	},

	/**
	 * 删除角色
	 */
	onDeleteRole : function() {
		var me = this;
		var grid = me.roleGrid;
		var items = grid.getSelectionModel().getSelection();

		if (items == null || items.length != 1) {
			PSI.MsgBox.showInfo("请选择要删除的角色");
			return;
		}

		var role = items[0].data;

		var info = "请确认是否删除角色 <span style='color:red'>" + role.name
				+ "</span> ?";
		var funcConfirm = function() {
			Ext.getBody().mask("正在删除中...");
			var r = {
				url : PSI.Const.BASE_URL + "Home/Permission/deleteRole",
				method : "POST",
				params : {
					id : role.id
				},
				callback : function(options, success, response) {
					Ext.getBody().unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成删除操作", function() {
										me.refreshRoleGrid();
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					}
				}
			};

			Ext.Ajax.request(r);
		};

		PSI.MsgBox.confirm(info, funcConfirm);
	},

	getDataOrgGrid : function() {
		var me = this;
		if (me.__dataOrgGrid) {
			return me.__dataOrgGrid;
		}

		var modelName = "PSIPermissionDataOrg_MainForm";
		Ext.define(modelName, {
					extend : "Ext.data.Model",
					fields : ["dataOrg", "fullName"]
				});

		var store = Ext.create("Ext.data.Store", {
					model : modelName,
					autoLoad : false,
					data : []
				});

		me.__dataOrgGrid = Ext.create("Ext.grid.Panel", {
					title : "数据域",
					store : store,
					columns : [{
								header : "数据域",
								dataIndex : "dataOrg",
								flex : 1,
								menuDisabled : true
							}, {
								header : "组织机构/人",
								dataIndex : "fullName",
								flex : 1,
								menuDisabled : true
							}]
				});

		return me.__dataOrgGrid;
	},

	onPermissionGridItemClick : function() {
		var me = this;
		var grid = me.roleGrid;
		var items = grid.getSelectionModel().getSelection();

		if (items == null || items.length != 1) {
			return;
		}

		var role = items[0];

		var grid = me.permissionGrid;
		var items = grid.getSelectionModel().getSelection();

		if (items == null || items.length != 1) {
			return;
		}
		var permission = items[0];

		var grid = me.getDataOrgGrid();
		grid.setTitle("角色 [" + role.get("name") + "] - 权限 ["
				+ permission.get("name") + "] - 数据域");

		var el = grid.getEl() || Ext.getBody();
		var store = grid.getStore();

		el.mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/dataOrgList",
					params : {
						roleId : role.get("id"),
						permissionId : permission.get("id")
					},
					method : "POST",
					callback : function(options, success, response) {
						store.removeAll();

						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}

						el.unmask();
					}
				});
	}
});