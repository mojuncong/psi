/**
 * 商品 - 新建或编辑界面
 */
Ext.define("PSI.Goods.GoodsEditForm", {
			extend : "Ext.window.Window",

			config : {
				parentForm : null,
				entity : null
			},

			/**
			 * 初始化组件
			 */
			initComponent : function() {
				var me = this;
				var entity = me.getEntity();

				Ext.define("PSIGoodsUnit", {
							extend : "Ext.data.Model",
							fields : ["id", "name"]
						});

				var unitStore = Ext.create("Ext.data.Store", {
							model : "PSIGoodsUnit",
							autoLoad : false,
							data : []
						});
				me.unitStore = unitStore;

				me.adding = entity == null;

				var buttons = [];
				if (!entity) {
					buttons.push({
								text : "保存并继续新增",
								formBind : true,
								handler : function() {
									me.onOK(true);
								},
								scope : me
							});
				}

				buttons.push({
							text : "保存",
							formBind : true,
							iconCls : "PSI-button-ok",
							handler : function() {
								me.onOK(false);
							},
							scope : me
						}, {
							text : entity == null ? "关闭" : "取消",
							handler : function() {
								me.close();
							},
							scope : me
						});

				var selectedCategory = me.getParentForm().getCategoryGrid()
						.getSelectionModel().getSelection();
				var defaultCategoryId = null;
				if (selectedCategory != null && selectedCategory.length > 0) {
					defaultCategoryId = selectedCategory[0].get("id");
				}

				Ext.apply(me, {
							title : entity == null ? "新增商品" : "编辑商品",
							modal : true,
							resizable : false,
							onEsc : Ext.emptyFn,
							width : 460,
							height : 260,
							layout : "fit",
							items : [{
								id : "editForm",
								xtype : "form",
								layout : {
									type : "table",
									columns : 2
								},
								height : "100%",
								bodyPadding : 5,
								defaultType : 'textfield',
								fieldDefaults : {
									labelWidth : 60,
									labelAlign : "right",
									labelSeparator : "",
									msgTarget : 'side'
								},
								items : [{
									xtype : "hidden",
									name : "id",
									value : entity == null ? null : entity
											.get("id")
								}, {
									id : "editCategory",
									xtype : "psi_goodscategoryfield",
									fieldLabel : "商品分类",
									allowBlank : false,
									blankText : "没有输入商品分类",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editCategoryId",
									name : "categoryId",
									xtype : "hidden",
									value : defaultCategoryId
								}, {
									id : "editCode",
									fieldLabel : "商品编码",
									allowBlank : false,
									blankText : "没有输入商品编码",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "code",
									value : entity == null ? null : entity
											.get("code"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editName",
									fieldLabel : "品名",
									colspan : 2,
									width : 430,
									allowBlank : false,
									blankText : "没有输入品名",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									name : "name",
									value : entity == null ? null : entity
											.get("name"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editSpec",
									fieldLabel : "规格型号",
									colspan : 2,
									width : 430,
									name : "spec",
									value : entity == null ? null : entity
											.get("spec"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editUnit",
									xtype : "combo",
									fieldLabel : "计量单位",
									allowBlank : false,
									blankText : "没有输入计量单位",
									beforeLabelTextTpl : PSI.Const.REQUIRED,
									valueField : "id",
									displayField : "name",
									store : unitStore,
									queryMode : "local",
									editable : false,
									name : "unitId",
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBarCode",
									fieldLabel : "条形码",
									name : "barCode",
									value : entity == null ? null : entity
											.get("barCode"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									id : "editBrandId",
									xtype : "hidden",
									name : "brandId"
								}, {
									id : "editBrand",
									fieldLabel : "品牌",
									name : "brandName",
									xtype : "PSI_goods_brand_field",
									colspan : 2,
									width : 430,
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									fieldLabel : "销售价",
									xtype : "numberfield",
									hideTrigger : true,
									name : "salePrice",
									id : "editSalePrice",
									value : entity == null ? null : entity
											.get("salePrice"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									fieldLabel : "建议采购价",
									xtype : "numberfield",
									hideTrigger : true,
									name : "purchasePrice",
									id : "editPurchasePrice",
									value : entity == null ? null : entity
											.get("purchasePrice"),
									listeners : {
										specialkey : {
											fn : me.onEditSpecialKey,
											scope : me
										}
									}
								}, {
									fieldLabel : "备注",
									name : "memo",
									id : "editMemo",
									value : entity == null ? null : entity
											.get("memo"),
									listeners : {
										specialkey : {
											fn : me.onLastEditSpecialKey,
											scope : me
										}
									},
									colspan : 2,
									width : 430
								}],
								buttons : buttons
							}],
							listeners : {
								show : {
									fn : me.onWndShow,
									scope : me
								},
								close : {
									fn : me.onWndClose,
									scope : me
								}
							}
						});

				me.callParent(arguments);

				me.__editorList = ["editCategory", "editCode", "editName",
						"editSpec", "editUnit", "editBarCode", "editBrand",
						"editSalePrice", "editPurchasePrice", "editMemo"];
			},

			onWndShow : function() {
				var me = this;
				var editCode = Ext.getCmp("editCode");
				editCode.focus();
				editCode.setValue(editCode.getValue());

				var categoryId = Ext.getCmp("editCategoryId").getValue();
				var el = me.getEl();
				var unitStore = me.unitStore;
				el.mask(PSI.Const.LOADING);
				Ext.Ajax.request({
							url : PSI.Const.BASE_URL + "Home/Goods/goodsInfo",
							params : {
								id : me.adding ? null : me.getEntity()
										.get("id"),
								categoryId : categoryId
							},
							method : "POST",
							callback : function(options, success, response) {
								unitStore.removeAll();

								if (success) {
									var data = Ext.JSON
											.decode(response.responseText);
									if (data.units) {
										unitStore.add(data.units);
									}

									if (!me.adding) {
										// 编辑商品信息
										Ext.getCmp("editCategory")
												.setIdValue(data.categoryId);
										Ext.getCmp("editCategory")
												.setValue(data.categoryName);
										Ext.getCmp("editCode")
												.setValue(data.code);
										Ext.getCmp("editName")
												.setValue(data.name);
										Ext.getCmp("editSpec")
												.setValue(data.spec);
										Ext.getCmp("editUnit")
												.setValue(data.unitId);
										Ext.getCmp("editSalePrice")
												.setValue(data.salePrice);
										Ext.getCmp("editPurchasePrice")
												.setValue(data.purchasePrice);
										Ext.getCmp("editBarCode")
												.setValue(data.barCode);
										Ext.getCmp("editMemo")
												.setValue(data.memo);
										var brandId = data.brandId;
										if (brandId) {
											var editBrand = Ext
													.getCmp("editBrand");
											editBrand.setIdValue(brandId);
											editBrand
													.setValue(data.brandFullName);
										}
									} else {
										// 新增商品
										if (unitStore.getCount() > 0) {
											var unitId = unitStore.getAt(0)
													.get("id");
											Ext.getCmp("editUnit")
													.setValue(unitId);
										}
										if (data.categoryId) {
											Ext
													.getCmp("editCategory")
													.setIdValue(data.categoryId);
											Ext
													.getCmp("editCategory")
													.setValue(data.categoryName);
										}
									}
								}

								el.unmask();
							}
						});
			},

			onOK : function(thenAdd) {
				var me = this;

				var categoryId = Ext.getCmp("editCategory").getIdValue();
				Ext.getCmp("editCategoryId").setValue(categoryId);

				var brandId = Ext.getCmp("editBrand").getIdValue();
				Ext.getCmp("editBrandId").setValue(brandId);

				var f = Ext.getCmp("editForm");
				var el = f.getEl();
				el.mask(PSI.Const.SAVING);
				f.submit({
							url : PSI.Const.BASE_URL + "Home/Goods/editGoods",
							method : "POST",
							success : function(form, action) {
								el.unmask();
								me.__lastId = action.result.id;
								me.getParentForm().__lastId = me.__lastId;

								PSI.MsgBox.tip("数据保存成功");
								me.focus();

								if (thenAdd) {
									me.clearEdit();
								} else {
									me.close();
									me.getParentForm().freshGoodsGrid();
								}
							},
							failure : function(form, action) {
								el.unmask();
								PSI.MsgBox.showInfo(action.result.msg,
										function() {
											Ext.getCmp("editCode").focus();
										});
							}
						});
			},

			onEditSpecialKey : function(field, e) {
				if (e.getKey() === e.ENTER) {
					var me = this;
					var id = field.getId();
					for (var i = 0; i < me.__editorList.length; i++) {
						var editorId = me.__editorList[i];
						if (id === editorId) {
							var edit = Ext.getCmp(me.__editorList[i + 1]);
							edit.focus();
							edit.setValue(edit.getValue());
						}
					}
				}
			},

			onLastEditSpecialKey : function(field, e) {
				if (e.getKey() == e.ENTER) {
					var f = Ext.getCmp("editForm");
					if (f.getForm().isValid()) {
						var me = this;
						me.onOK(me.adding);
					}
				}
			},

			clearEdit : function() {
				Ext.getCmp("editCode").focus();

				var editors = [Ext.getCmp("editCode"), Ext.getCmp("editName"),
						Ext.getCmp("editSpec"), Ext.getCmp("editSalePrice"),
						Ext.getCmp("editPurchasePrice"),
						Ext.getCmp("editBarCode")];
				for (var i = 0; i < editors.length; i++) {
					var edit = editors[i];
					edit.setValue(null);
					edit.clearInvalid();
				}
			},

			onWndClose : function() {
				var me = this;
				me.getParentForm().__lastId = me.__lastId;
				me.getParentForm().freshGoodsGrid();
			}
		});