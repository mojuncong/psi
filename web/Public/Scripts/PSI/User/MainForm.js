/**
 * 用户管理 - 主界面
 */
Ext.define("PSI.User.MainForm", {
	extend : "Ext.panel.Panel",

	config : {
		pAddOrg : null,
		pEditOrg : null,
		pDeleteOrg : null,
		pAddUser : null,
		pEditUser : null,
		pDeleteUser : null,
		pChangePassword : null
	},

	getBaseURL : function() {
		return PSI.Const.BASE_URL;
	},

	/**
	 * 初始化组件
	 */
	initComponent : function() {
		var me = this;

		Ext.define("PSIOrgModel", {
					extend : "Ext.data.Model",
					fields : ["id", "text", "fullName", "orgCode", "dataOrg",
							"leaf", "children"]
				});

		var orgStore = Ext.create("Ext.data.TreeStore", {
					model : "PSIOrgModel",
					proxy : {
						type : "ajax",
						url : me.getBaseURL() + "Home/User/allOrgs"
					}
				});

		orgStore.on("load", me.onOrgStoreLoad, me);

		var orgTree = Ext.create("Ext.tree.Panel", {
					title : "组织机构",
					store : orgStore,
					rootVisible : false,
					useArrows : true,
					viewConfig : {
						loadMask : true
					},
					columns : {
						defaults : {
							sortable : false,
							menuDisabled : true,
							draggable : false
						},
						items : [{
									xtype : "treecolumn",
									text : "名称",
									dataIndex : "text",
									width : 220
								}, {
									text : "编码",
									dataIndex : "orgCode",
									width : 100
								}, {
									text : "数据域",
									dataIndex : "dataOrg",
									width : 100
								}]
					}
				});
		me.orgTree = orgTree;

		orgTree.on("select", function(rowModel, record) {
					me.onOrgTreeNodeSelect(record);
				}, me);

		orgTree.on("itemdblclick", me.onEditOrg, me);

		Ext.define("PSIUser", {
					extend : "Ext.data.Model",
					fields : ["id", "loginName", "name", "enabled", "orgCode",
							"gender", "birthday", "idCardNumber", "tel",
							"tel02", "address", "dataOrg"]
				});
		var storeGrid = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : "PSIUser",
					data : [],
					pageSize : 20,
					proxy : {
						type : "ajax",
						actionMethods : {
							read : "POST"
						},
						url : PSI.Const.BASE_URL + "Home/User/users",
						reader : {
							root : 'dataList',
							totalProperty : 'totalCount'
						}
					}
				});
		storeGrid.on("beforeload", function() {
					storeGrid.proxy.extraParams = me.getUserParam();
				});

		var grid = Ext.create("Ext.grid.Panel", {
					title : "人员列表",
					viewConfig : {
						enableTextSelection : true
					},
					columnLines : true,
					columns : [Ext.create("Ext.grid.RowNumberer", {
										text : "序号",
										width : 40
									}), {
								header : "登录名",
								dataIndex : "loginName",
								menuDisabled : true,
								sortable : false
							}, {
								header : "姓名",
								dataIndex : "name",
								menuDisabled : true,
								sortable : false
							}, {
								header : "编码",
								dataIndex : "orgCode",
								menuDisabled : true,
								sortable : false
							}, {
								header : "是否允许登录",
								dataIndex : "enabled",
								menuDisabled : true,
								sortable : false,
								renderer : function(value) {
									return value == 1
											? "允许登录"
											: "<span style='color:red'>禁止登录</span>";
								}
							}, {
								header : "性别",
								dataIndex : "gender",
								menuDisabled : true,
								sortable : false,
								width : 70
							}, {
								header : "生日",
								dataIndex : "birthday",
								menuDisabled : true,
								sortable : false
							}, {
								header : "身份证号",
								dataIndex : "idCardNumber",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "联系电话",
								dataIndex : "tel",
								menuDisabled : true,
								sortable : false
							}, {
								header : "备用联系电话",
								dataIndex : "tel02",
								menuDisabled : true,
								sortable : false
							}, {
								header : "家庭住址",
								dataIndex : "address",
								menuDisabled : true,
								sortable : false,
								width : 200
							}, {
								header : "数据域",
								dataIndex : "dataOrg",
								menuDisabled : true,
								sortable : false,
								width : 100
							}],
					store : storeGrid,
					listeners : {
						itemdblclick : {
							fn : me.onEditUser,
							scope : me
						}
					},
					bbar : [{
								id : "pagingToolbar",
								border : 0,
								xtype : "pagingtoolbar",
								store : storeGrid
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
											storeGrid.pageSize = Ext
													.getCmp("comboCountPerPage")
													.getValue();
											storeGrid.currentPage = 1;
											Ext.getCmp("pagingToolbar")
													.doRefresh();
										},
										scope : me
									}
								}
							}, {
								xtype : "displayfield",
								value : "条记录"
							}]
				});

		this.grid = grid;

		Ext.apply(me, {
					border : 0,
					layout : "border",
					tbar : [{
								text : "新增组织机构",
								iconCls : "PSI-button-add",
								disabled : me.getPAddOrg() == "0",
								handler : me.onAddOrg,
								scope : me
							}, {
								text : "编辑组织机构",
								iconCls : "PSI-button-edit",
								disabled : me.getPEditOrg() == "0",
								handler : me.onEditOrg,
								scope : me
							}, {
								text : "删除组织机构",
								disabled : me.getPDeleteOrg() == "0",
								iconCls : "PSI-button-delete",
								handler : me.onDeleteOrg,
								scope : me
							}, "-", {
								text : "新增用户",
								disabled : me.getPAddUser() == "0",
								iconCls : "PSI-button-add-user",
								handler : me.onAddUser,
								scope : me
							}, {
								text : "修改用户",
								disabled : me.getPEditUser() == "0",
								iconCls : "PSI-button-edit-user",
								handler : me.onEditUser,
								scope : me
							}, {
								text : "删除用户",
								disabled : me.getPDeleteUser() == "0",
								iconCls : "PSI-button-delete-user",
								handler : me.onDeleteUser,
								scope : me
							}, "-", {
								text : "修改用户密码",
								disabled : me.getPChangePassword() == "0",
								iconCls : "PSI-button-change-password",
								handler : me.onEditUserPassword,
								scope : me
							}, "-", {
								text : "帮助",
								iconCls : "PSI-help",
								handler : function() {
									window.open(PSI.Const.BASE_URL
											+ "/Home/Help/index?t=user");
								}
							}, "-", {
								text : "关闭",
								iconCls : "PSI-button-exit",
								handler : function() {
									location.replace(me.getBaseURL());
								}
							}],
					items : [{
								region : "center",
								xtype : "panel",
								layout : "fit",
								border : 0,
								items : [grid]
							}, {
								xtype : "panel",
								region : "west",
								layout : "fit",
								width : 440,
								split : true,
								border : 0,
								items : [orgTree]
							}]
				});

		me.callParent(arguments);
	},

	getGrid : function() {
		return this.grid;
	},

	/**
	 * 新增组织机构
	 */
	onAddOrg : function() {
		var form = Ext.create("PSI.User.OrgEditForm", {
					parentForm : this
				});
		form.show();
	},

	/**
	 * 编辑组织机构
	 */
	onEditOrg : function() {
		var me = this;
		if (me.getPEditOrg() == "0") {
			return;
		}

		var tree = this.orgTree;
		var item = tree.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			PSI.MsgBox.showInfo("请选择要编辑的组织机构");
			return;
		}

		var org = item[0];

		var form = Ext.create("PSI.User.OrgEditForm", {
					parentForm : this,
					entity : org
				});
		form.show();
	},

	/**
	 * 删除组织机构
	 */
	onDeleteOrg : function() {
		var me = this;
		var tree = me.orgTree;
		var item = tree.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			PSI.MsgBox.showInfo("请选择要删除的组织机构");
			return;
		}

		var org = item[0].getData();

		var funcConfirm = function() {
			Ext.getBody().mask("正在删除中...");
			var r = {
				url : me.getBaseURL() + "Home/User/deleteOrg",
				method : "POST",
				params : {
					id : org.id
				},
				callback : function(options, success, response) {
					Ext.getBody().unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成删除操作", function() {
										me.freshOrgGrid();
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					}
				}
			};

			Ext.Ajax.request(r);
		};

		var info = "请确认是否删除组织机构 <span style='color:red'>" + org.fullName
				+ "</span> ?";
		PSI.MsgBox.confirm(info, funcConfirm);
	},

	freshOrgGrid : function() {
		this.orgTree.getStore().reload();
	},

	freshUserGrid : function() {
		var tree = this.orgTree;
		var item = tree.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			return;
		}

		this.onOrgTreeNodeSelect(item[0]);
	},

	/**
	 * 新增用户
	 */
	onAddUser : function() {
		var tree = this.orgTree;
		var item = tree.getSelectionModel().getSelection();
		var org = null;
		if (item != null && item.length > 0) {
			org = item[0];
		}

		var editFrom = Ext.create("PSI.User.UserEditForm", {
					parentForm : this,
					defaultOrg : org
				});
		editFrom.show();
	},

	/**
	 * 编辑用户
	 */
	onEditUser : function() {
		var me = this;
		if (me.getPEditUser() == "0") {
			return;
		}

		var item = this.grid.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			PSI.MsgBox.showInfo("请选择要编辑的用户");
			return;
		}

		var user = item[0].data;

		var tree = this.orgTree;
		var node = tree.getSelectionModel().getSelection();
		if (node && node.length === 1) {
			var org = node[0].data;

			user.orgId = org.id;
			user.orgName = org.fullName;
		}

		var editFrom = Ext.create("PSI.User.UserEditForm", {
					parentForm : this,
					entity : user
				});
		editFrom.show();
	},

	/**
	 * 修改用户密码
	 */
	onEditUserPassword : function() {
		var item = this.grid.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			PSI.MsgBox.showInfo("请选择要修改密码的用户");
			return;
		}

		var user = item[0].getData();
		var editFrom = Ext.create("PSI.User.ChangeUserPasswordForm", {
					entity : user
				});
		editFrom.show();
	},

	/**
	 * 删除用户
	 */
	onDeleteUser : function() {
		var me = this;
		var item = me.grid.getSelectionModel().getSelection();
		if (item === null || item.length !== 1) {
			PSI.MsgBox.showInfo("请选择要删除的用户");
			return;
		}

		var user = item[0].getData();

		var funcConfirm = function() {
			Ext.getBody().mask("正在删除中...");
			var r = {
				url : me.getBaseURL() + "Home/User/deleteUser",
				method : "POST",
				params : {
					id : user.id
				},
				callback : function(options, success, response) {
					Ext.getBody().unmask();

					if (success) {
						var data = Ext.JSON.decode(response.responseText);
						if (data.success) {
							PSI.MsgBox.showInfo("成功完成删除操作", function() {
										me.freshUserGrid();
									});
						} else {
							PSI.MsgBox.showInfo(data.msg);
						}
					}
				}
			};
			Ext.Ajax.request(r);
		};

		var info = "请确认是否删除用户 <span style='color:red'>" + user.name
				+ "</span> ?";
		PSI.MsgBox.confirm(info, funcConfirm);
	},

	onOrgTreeNodeSelect : function(rec) {
		if (!rec) {
			return;
		}

		var org = rec.data;
		if (!org) {
			return;
		}

		var me = this;
		var grid = me.getGrid();

		grid.setTitle(org.fullName + " - 人员列表");

		Ext.getCmp("pagingToolbar").doRefresh();
	},

	onOrgStoreLoad : function() {
		var tree = this.orgTree;
		var root = tree.getRootNode();
		if (root) {
			var node = root.firstChild;
			if (node) {
				this.onOrgTreeNodeSelect(node);
			}
		}
	},

	getUserParam : function() {
		var me = this;
		var item = me.orgTree.getSelectionModel().getSelection();
		if (item == null || item.length == 0) {
			return {};
		}

		var org = item[0];

		return {
			orgId : org.get("id")
		}
	}
});