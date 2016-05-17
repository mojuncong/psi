/**
 * 权限 - 角色新增或编辑界面
 */
Ext.define("PSI.Permission.EditForm", {
	extend : "Ext.window.Window",
	config : {
		entity : null,
		parentForm : null
	},
	initComponent : function() {
		var me = this;
		var entity = me.getEntity();

		Ext.define("PSIPermission", {
					extend : "Ext.data.Model",
					fields : ["id", "name", "dataOrg", "dataOrgFullName"]
				});

		var permissionStore = Ext.create("Ext.data.Store", {
					model : "PSIPermission",
					autoLoad : false,
					data : []
				});

		var permissionGrid = Ext.create("Ext.grid.Panel", {
					title : "角色的权限",
					padding : 5,
					selModel : {
						mode : "MULTI"
					},
					selType : "checkboxmodel",
					store : permissionStore,
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
							}, {
								header : "操作",
								align : "center",
								menuDisabled : true,
								width : 50,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/delete.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.remove(store.getAt(row));
									},
									scope : this
								}]
							}],
					tbar : [{
								text : "添加权限",
								handler : me.onAddPermission,
								scope : me,
								iconCls : "PSI-button-add"
							}, "-", {
								text : "移除权限",
								handler : me.onRemovePermission,
								scope : me,
								iconCls : "PSI-button-delete"
							}, "-", {
								text : "编辑数据域",
								handler : me.onEditDataOrg,
								scope : me,
								iconCls : "PSI-button-edit"
							}]
				});

		this.permissionGrid = permissionGrid;

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
					title : "属于当前角色的用户",
					padding : 5,
					selModel : {
						mode : "MULTI"
					},
					selType : "checkboxmodel",
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
							}, {
								header : "操作",
								align : "center",
								menuDisabled : true,
								width : 50,
								xtype : "actioncolumn",
								items : [{
									icon : PSI.Const.BASE_URL
											+ "Public/Images/icons/delete.png",
									handler : function(grid, row) {
										var store = grid.getStore();
										store.remove(store.getAt(row));
									},
									scope : this
								}]
							}

					],
					tbar : [{
								text : "添加用户",
								iconCls : "PSI-button-add",
								handler : this.onAddUser,
								scope : this
							}, "-", {
								text : "移除用户",
								iconCls : "PSI-button-delete",
								handler : this.onRemoveUser,
								scope : this
							}]
				});

		this.userGrid = userGrid;

		Ext.apply(me, {
			title : entity == null ? "新增角色" : "编辑角色",
			modal : true,
			resizable : false,
			onEsc : Ext.emptyFn,
			maximized : true,
			width : 700,
			height : 600,
			layout : "border",
			defaultFocus : "editName",
			items : [{
				xtype : "panel",
				region : "north",
				layout : "fit",
				height : 40,
				border : 0,
				items : [{
							id : "editForm",
							xtype : "form",
							layout : {
								type : "table",
								columns : 1
							},
							border : 0,
							bodyPadding : 5,
							defaultType : 'textfield',
							fieldDefaults : {
								labelWidth : 60,
								labelAlign : "right",
								labelSeparator : "",
								msgTarget : 'side',
								width : 670,
								margin : "5"
							},
							items : [{
										xtype : "hidden",
										name : "id",
										value : entity == null
												? null
												: entity.id
									}, {
										id : "editName",
										fieldLabel : "角色名称",
										allowBlank : false,
										blankText : "没有输入名称",
										beforeLabelTextTpl : PSI.Const.REQUIRED,
										name : "name",
										value : entity == null
												? null
												: entity.name
									}, {
										id : "editPermissionIdList",
										xtype : "hidden",
										name : "permissionIdList"
									}, {
										id : "editDataOrgList",
										xtype : "hidden",
										name : "dataOrgList"
									}, {
										id : "editUserIdList",
										xtype : "hidden",
										name : "userIdList"
									}]
						}]
			}, {
				xtype : "panel",
				region : "center",
				flex : 1,
				border : 0,
				layout : "border",
				items : [{
							region : "center",
							layout : "fit",
							border : 0,
							items : [permissionGrid]
						}]
			}, {
				xtype : "panel",
				region : "south",
				flex : 1,
				border : 0,
				layout : "fit",
				items : [userGrid]
			}],
			tbar : [{
						text : "确定",
						formBind : true,
						iconCls : "PSI-button-ok",
						handler : function() {
							var me = this;
							PSI.MsgBox.confirm("请确认是否保存数据?", function() {
										me.onOK();
									});
						},
						scope : this
					}, "-", {
						text : "取消",
						iconCls : "PSI-button-cancel",
						handler : function() {
							var me = this;
							PSI.MsgBox.confirm("请确认是否取消操作?", function() {
										me.close();
									});
						},
						scope : this
					}]
		});

		if (entity) {
			me.on("show", this.onWndShow, this);
		}

		me.callParent(arguments);
	},

	onWndShow : function() {
		var entity = this.getEntity();
		var store = this.permissionGrid.getStore();
		var el = this.getEl() || Ext.getBody();

		el.mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/permissionList",
					params : {
						roleId : entity.id
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
		userGrid.setTitle("属于角色 [" + entity.name + "] 的人员列表");
		userEl.mask("数据加载中...");
		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/Permission/userList",
					params : {
						roleId : entity.id
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

	setSelectedPermission : function(data, dataOrgList, fullNameList) {
		var store = this.permissionGrid.getStore();

		for (var i = 0; i < data.length; i++) {
			var item = data[i];
			store.add({
						id : item.id,
						name : item.name,
						dataOrg : dataOrgList,
						dataOrgFullName : fullNameList
					});
		}
	},

	setSelectedUsers : function(data) {
		var store = this.userGrid.getStore();

		for (var i = 0; i < data.length; i++) {
			var item = data[i];
			store.add({
						id : item.get("id"),
						name : item.get("name"),
						loginName : item.get("loginName"),
						orgFullName : item.get("orgFullName")
					});
		}
	},

	onOK : function() {
		var me = this;
		var editName = Ext.getCmp("editName");

		var name = editName.getValue();
		if (name == null || name == "") {
			PSI.MsgBox.showInfo("没有输入角色名称", function() {
						editName.focus();
					});
			return;
		}

		var store = this.permissionGrid.getStore();
		var data = store.data;
		var idList = [];
		var dataOrgList = [];
		for (var i = 0; i < data.getCount(); i++) {
			var item = data.items[i].data;
			idList.push(item.id);
			dataOrgList.push(item.dataOrg);
		}

		var editPermissionIdList = Ext.getCmp("editPermissionIdList");
		editPermissionIdList.setValue(idList.join());

		Ext.getCmp("editDataOrgList").setValue(dataOrgList.join(","));

		store = this.userGrid.getStore();
		data = store.data;
		idList = [];
		for (var i = 0; i < data.getCount(); i++) {
			var item = data.items[i].data;
			idList.push(item.id);
		}

		var editUserIdList = Ext.getCmp("editUserIdList");
		editUserIdList.setValue(idList.join());

		var editForm = Ext.getCmp("editForm");
		var el = this.getEl() || Ext.getBody();
		el.mask("数据保存中...");

		editForm.submit({
					url : PSI.Const.BASE_URL + "Home/Permission/editRole",
					method : "POST",
					success : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo("数据保存成功", function() {
									me.close();
									me.getParentForm()
											.refreshRoleGrid(action.result.id);
								});
					},
					failure : function(form, action) {
						el.unmask();
						PSI.MsgBox.showInfo(action.result.msg, function() {
									editName.focus();
								});
					}
				});
	},
	onAddPermission : function() {
		var store = this.permissionGrid.getStore();
		var data = store.data;
		var idList = [];
		for (var i = 0; i < data.getCount(); i++) {
			var item = data.items[i].data;
			idList.push(item.id);
		}

		var form = Ext.create("PSI.Permission.SelectPermissionForm", {
					idList : idList,
					parentForm : this
				});
		form.show();
	},
	onRemovePermission : function() {
		var grid = this.permissionGrid;

		var items = grid.getSelectionModel().getSelection();
		if (items == null || items.length == 0) {
			PSI.MsgBox.showInfo("请选择要移除的权限");
			return;
		}

		grid.getStore().remove(items);
	},

	onAddUser : function() {
		var store = this.userGrid.getStore();
		var data = store.data;
		var idList = [];
		for (var i = 0; i < data.getCount(); i++) {
			var item = data.items[i].data;
			idList.push(item.id);
		}

		var form = Ext.create("PSI.Permission.SelectUserForm", {
					idList : idList,
					parentForm : this
				});

		form.show();
	},

	onRemoveUser : function() {
		var grid = this.userGrid;

		var items = grid.getSelectionModel().getSelection();
		if (items == null || items.length == 0) {
			PSI.MsgBox.showInfo("请选择要移除的人员");
			return;
		}

		grid.getStore().remove(items);
	},

	getDataOrgGrid : function() {
		var me = this;
		if (me.__dataOrgGrid) {
			return me.__dataOrgGrid;
		}
		var modelName = "PSIPermissionDataOrg_EditForm";
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
					padding : 5,
					tbar : [{
								text : "设置数据域"
							}],
					columns : [{
								header : "数据域",
								dataIndex : "dataOrg",
								flex : 1,
								menuDisabled : true
							}, {
								header : "组织机构/人",
								dataIndex : "fullName",
								flex : 2,
								menuDisabled : true
							}]
				});

		return me.__dataOrgGrid;
	},

	onEditDataOrg : function() {
		var me = this;

		var grid = me.permissionGrid;

		var items = grid.getSelectionModel().getSelection();
		if (items == null || items.length == 0) {
			PSI.MsgBox.showInfo("请选择要编辑数据域的权限");
			return;
		}

		var form = Ext.create("PSI.Permission.SelectDataOrgForm", {
					editForm : me
				});
		form.show();
	},

	/**
	 * PSI.Permission.SelectDataOrgForm中回调本方法
	 */
	onEditDataOrgCallback : function(dataOrg) {
		var me = this;

		var grid = me.permissionGrid;

		var items = grid.getSelectionModel().getSelection();
		if (items == null || items.length == 0) {
			return;
		}

		for (var i = 0; i < items.length; i++) {
			items[i].set("dataOrg", dataOrg);
		}
	}
});