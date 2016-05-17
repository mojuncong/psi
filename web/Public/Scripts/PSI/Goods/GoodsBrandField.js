/**
 * 自定义字段 - 商品品牌字段
 */
Ext.define("PSI.Goods.GoodsBrandField", {
			extend : "Ext.form.field.Trigger",
			alias : "widget.PSI_goods_brand_field",

			initComponent : function() {
				var me = this;
				me.__idValue = null;

				me.enableKeyEvents = true;

				me.callParent(arguments);

				me.on("keydown", function(field, e) {
							if (e.getKey() === e.BACKSPACE) {
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
				var me = this;

				var modelName = "PSIModel_GoodsBrandEditor";
				Ext.define(modelName, {
							extend : "Ext.data.Model",
							fields : ["id", "text", "fullName", "leaf",
									"children"]
						});

				var store = Ext.create("Ext.data.TreeStore", {
							model : modelName,
							proxy : {
								type : "ajax",
								actionMethods : {
									read : "POST"
								},
								url : PSI.Const.BASE_URL
										+ "Home/Goods/allBrands"
							}
						});

				var tree = Ext.create("Ext.tree.Panel", {
							store : store,
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
											text : "品牌",
											dataIndex : "text"
										}]
							}
						});
				tree.on("itemdblclick", me.onOK, me);
				me.tree = tree;

				var wnd = Ext.create("Ext.window.Window", {
							title : "选择商品品牌",
							modal : true,
							width : 400,
							height : 300,
							layout : "fit",
							items : [tree],
							buttons : [{
										text : "确定",
										handler : me.onOK,
										scope : me
									}, {
										text : "取消",
										handler : function() {
											wnd.close();
										}
									}]
						});
				me.wnd = wnd;
				wnd.show();
			},

			onOK : function() {
				var me = this;
				var tree = me.tree;
				var item = tree.getSelectionModel().getSelection();

				if (item === null || item.length !== 1) {
					PSI.MsgBox.showInfo("没有选择品牌");

					return;
				}

				var data = item[0].data;
				me.setValue(data.fullName);
				me.setIdValue(data.id);
				me.wnd.close();
				me.focus();
			},

			setIdValue : function(id) {
				this.__idValue = id;
			},

			getIdValue : function() {
				return this.__idValue;
			},

			clearIdValue : function() {
				this.setValue(null);
				this.__idValue = null;
			}
		});