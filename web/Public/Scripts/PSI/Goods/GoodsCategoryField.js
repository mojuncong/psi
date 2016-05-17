/**
 * 自定义字段 - 商品分类字段
 */
Ext.define("PSI.Goods.GoodsCategoryField", {
			extend : "Ext.form.field.Trigger",
			alias : "widget.psi_goodscategoryfield",

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;

				me.__idValue = null;

				me.enableKeyEvents = true;

				me.callParent(arguments);

				me.on("keydown", function(field, e) {
							if (e.getKey() == e.BACKSPACE) {
								field.setValue(null);
								me.setIdValue(null);
								e.preventDefault();
								return false;
							}

							if (e.getKey() !== e.ENTER) {
								this.onTriggerClick(e);
							}
						});
			},

			onTriggerClick : function(e) {
				var modelName = "PSIGoodsCategoryModel_Field";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "text", "fullName", "code", "leaf",
									"children"]
						});

				var orgStore = Ext.create("Ext.data.TreeStore", {
							model : modelName,
							proxy : {
								type : "ajax",
								actionMethods : {
									read : "POST"
								},
								url : PSI.Const.BASE_URL
										+ "Home/Goods/allCategories"
							}
						});

				var orgTree = Ext.create("Ext.tree.Panel", {
							store : orgStore,
							rootVisible : false,
							useArrows : true,
							viewConfig : {
								loadMask : true
							},
							columns : {
								defaults : {
									flex : 1,
									sortable : false,
									menuDisabled : true,
									draggable : false
								},
								items : [{
											xtype : "treecolumn",
											text : "名称",
											dataIndex : "text"
										}, {
											text : "编码",
											dataIndex : "code"
										}]
							}
						});
				orgTree.on("itemdblclick", this.onOK, this);
				this.tree = orgTree;

				var wnd = Ext.create("Ext.window.Window", {
							title : "选择商品分类",
							modal : true,
							width : 400,
							height : 300,
							layout : "fit",
							items : [orgTree],
							buttons : [{
										text : "确定",
										handler : this.onOK,
										scope : this
									}, {
										text : "取消",
										handler : function() {
											wnd.close();
										}
									}]
						});
				this.wnd = wnd;
				wnd.show();
			},

			onOK : function() {
				var me = this;

				var tree = me.tree;
				var item = tree.getSelectionModel().getSelection();

				if (item === null || item.length !== 1) {
					PSI.MsgBox.showInfo("没有选择商品分类");

					return;
				}

				var data = item[0];
				me.setIdValue(data.get("id"));
				me.setValue(data.get("fullName"));
				me.wnd.close();
				me.focus();
			},

			setIdValue : function(id) {
				this.__idValue = id;
			},

			getIdValue : function() {
				return this.__idValue;
			}
		});