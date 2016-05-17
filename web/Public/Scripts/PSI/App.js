/**
 * PSI的应用容器：承载主菜单、其他模块的UI
 */
Ext.define("PSI.App", {
	config : {
		userName : "",
		productionName : "开源进销存PSI"
	},

	constructor : function(config) {
		this.initConfig(config);

		this.createMainUI();
	},

	createMainUI : function() {
		var me = this;

		me.mainPanel = Ext.create("Ext.panel.Panel", {
					border : 0,
					layout : "fit"
				});

		Ext.define("PSIFId", {
					extend : "Ext.data.Model",
					fields : ["fid", "name"]
				});

		var storeRecentFid = Ext.create("Ext.data.Store", {
					autoLoad : false,
					model : "PSIFId",
					data : []
				});

		me.gridRecentFid = Ext.create("Ext.grid.Panel", {
					title : "常用功能",
					forceFit : true,
					hideHeaders : true,
					columns : [{
						dataIndex : "name",
						menuDisabled : true,
						menuDisabled : true,
						sortable : false,
						renderer : function(value, metaData, record) {
							var fid = record.get("fid");
							var fileName = PSI.Const.BASE_URL
									+ "Public/Images/fid/fid" + fid + ".png";
							return "<img src='"
									+ fileName
									+ "'><a href='#' style='text-decoration:none'>"
									+ value + "</a></img>";
						}
					}],
					store : storeRecentFid
				});

		me.gridRecentFid.on("itemclick", function(v, r) {
					var fid = r.get("fid");

					if (fid === "-9999") {
						PSI.MsgBox.confirm("请确认是否重新登录", function() {
									location
											.replace(PSI.Const.BASE_URL
													+ "Home/MainMenu/navigateTo/fid/-9999");
								});
					} else {
						location.replace(PSI.Const.BASE_URL
								+ "Home/MainMenu/navigateTo/fid/" + fid);
					}
				}, me);

		me.vp = Ext.create("Ext.container.Viewport", {
			layout : "fit",
			items : [{
						id : "__PSITopPanel",
						xtype : "panel",
						border : 0,
						layout : "border",
						bbar : ["当前用户：" + me.getUserName()],
						items : [{
									region : "center",
									layout : "fit",
									xtype : "panel",
									items : [me.mainPanel]
								}, {
									xtype : "panel",
									region : "east",
									width : 250,
									maxWidth : 250,
									split : true,
									collapsible : true,
									collapsed : me.getRecentFidPanelCollapsed(),
									header : false,
									layout : "fit",
									items : [me.gridRecentFid],
									listeners : {
										collapse : {
											fn : me.onRecentFidPanelCollapse,
											scope : me
										},
										expand : {
											fn : me.onRecentFidPanelExpand,
											scope : me
										}
									}
								}]
					}]
		});

		var el = Ext.getBody();
		el.mask("系统正在加载中...");

		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/MainMenu/mainMenuItems",
					method : "POST",
					callback : function(opt, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							me.createMainMenu(data);
							me.refreshRectFidGrid();
						}

						el.unmask();
					},
					scope : me
				});
	},

	refreshRectFidGrid : function() {
		var me = this;

		var el = me.gridRecentFid.getEl() || Ext.getBody();
		el.mask("系统正在加载中...");
		var store = me.gridRecentFid.getStore();
		store.removeAll();

		Ext.Ajax.request({
					url : PSI.Const.BASE_URL + "Home/MainMenu/recentFid",
					method : "POST",
					callback : function(opt, success, response) {
						if (success) {
							var data = Ext.JSON.decode(response.responseText);
							store.add(data);
						}
						el.unmask();
					},
					scope : me
				});
	},

	createMainMenu : function(root) {
		var me = this;

		var menuItemClick = function() {
			var fid = this.fid;

			if (fid == "-9995") {
				window.open("http://psi.mydoc.io/");
			} else if (fid == "-9993") {
				window.open("https://zb.oschina.net/market/opus/134395_122");
			} else if (fid === "-9999") {
				PSI.MsgBox.confirm("请确认是否重新登录", function() {
							location.replace(PSI.Const.BASE_URL
									+ "Home/MainMenu/navigateTo/fid/-9999");
						});
			} else {
				location.replace(PSI.Const.BASE_URL
						+ "Home/MainMenu/navigateTo/fid/" + fid);
			}
		};

		var mainMenu = [];
		for (var i = 0; i < root.length; i++) {
			var m1 = root[i];

			var menuItem = Ext.create("Ext.menu.Menu");
			for (var j = 0; j < m1.children.length; j++) {
				var m2 = m1.children[j];

				if (m2.children.length === 0) {
					// 只有二级菜单
					if (m2.fid) {
						menuItem.add({
									text : m2.caption,
									fid : m2.fid,
									handler : menuItemClick,
									iconCls : "PSI-fid" + m2.fid
								});
					}
				} else {
					var menuItem2 = Ext.create("Ext.menu.Menu");

					menuItem.add({
								text : m2.caption,
								menu : menuItem2
							});

					// 三级菜单
					for (var k = 0; k < m2.children.length; k++) {
						var m3 = m2.children[k];
						menuItem2.add({
									text : m3.caption,
									fid : m3.fid,
									handler : menuItemClick,
									iconCls : "PSI-fid" + m3.fid
								});
					}
				}
			}

			if (m1.children.length > 0) {
				mainMenu.push({
							text : m1.caption,
							menu : menuItem
						});
			}
		}

		var mainToolbar = Ext.create("Ext.toolbar.Toolbar", {
					dock : "top"
				});
		mainToolbar.add(mainMenu);

		me.vp.getComponent(0).addDocked(mainToolbar);
	},

	// 设置模块的标题
	setAppHeader : function(header) {
		if (!header) {
			return;
		}
		var panel = Ext.getCmp("__PSITopPanel");
		panel.setTitle(header.title + " - " + this.getProductionName());
		panel.setIconCls(header.iconCls);
	},

	add : function(comp) {
		this.mainPanel.add(comp);
	},

	onRecentFidPanelCollapse : function() {
		Ext.util.Cookies.set("PSI_RECENT_FID", "1", Ext.Date.add(new Date(),
						Ext.Date.YEAR, 1));
	},

	onRecentFidPanelExpand : function() {
		Ext.util.Cookies.clear("PSI_RECENT_FID");
	},

	getRecentFidPanelCollapsed : function() {
		var v = Ext.util.Cookies.get("PSI_RECENT_FID");
		return v === "1";
	}
});