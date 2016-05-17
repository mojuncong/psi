/**
 * 选择用户
 */
Ext.define("PSI.Permission.SelectUserForm", {
			extend : "Ext.window.Window",

			config : {
				idList : null, // idList是数组
				parentForm : null
			},

			title : "选择用户",
			width : 600,
			height : 500,
			modal : true,
			layout : "fit",

			initComponent : function() {
				var me = this;
				Ext.define("PSIUser_SelectUserForm", {
							extend : "Ext.data.Model",
							fields : ["id", "loginName", "name", "orgFullName",
									"enabled"]
						});

				var userStore = Ext.create("Ext.data.Store", {
							model : "PSIUser_SelectUserForm",
							autoLoad : false,
							data : []
						});

				var grid = Ext.create("Ext.grid.Panel", {
							title : "属于当前角色的用户",
							padding : 5,
							selModel : {
								mode : "MULTI"
							},
							selType : "checkboxmodel",
							viewConfig : {
								deferEmptyText : false,
								emptyText : "所有用户都已经加入到当前角色中了"
							},
							store : userStore,
							columnLines : true,
							columns : [{
										header : "用户姓名",
										dataIndex : "name",
										width : 120,
										menuDisabled : true
									}, {
										header : "登录名",
										dataIndex : "loginName",
										width : 120,
										menuDisabled : true
									}, {
										header : "所属组织",
										dataIndex : "orgFullName",
										flex : 1,
										menuDisabled : true
									}]
						});

				me.__grid = grid;

				Ext.apply(me, {
							items : [grid],
							buttons : [{
										text : "确定",
										formBind : true,
										iconCls : "PSI-button-ok",
										handler : me.onOK,
										scope : me
									}, {
										text : "取消",
										handler : function() {
											me.close();
										},
										scope : me
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
				var idList = me.getIdList();
				var userStore = me.__grid.getStore();

				var el = me.getEl() || Ext.getBody();
				el.mask("数据加载中...");
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL
									+ "Home/Permission/selectUsers",
							params : {
								idList : idList.join()
							},
							method : "POST",
							callback : function(options, success, response) {
								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);

									for (var i = 0; i < data.length; i++) {
										var item = data[i];
										userStore.add({
													id : item.id,
													name : item.name,
													loginName : item.loginName,
													orgFullName : item.orgFullName
												});
									}
								}

								el.unmask();
							}
						});
			},

			onOK : function() {
				var grid = this.__grid;

				var items = grid.getSelectionModel().getSelection();
				if (items == null || items.length == 0) {
					PSI.MsgBox.showInfo("没有选择用户");

					return;
				}

				if (this.getParentForm()) {
					this.getParentForm().setSelectedUsers(items);
				}

				this.close();
			}
		});