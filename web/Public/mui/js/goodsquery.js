/**
 * 商品查询
 */

	function selects(wareHouseIdSelect){
		wareHouseIdSelect=wareHouseIdSelect;
		
		$.post(window.uri+"Mobile/GoodsQuery/select",
				{
			
				},
	             function(data){
					  $.each(data,function(key,value){
						  $(wareHouseIdSelectd).append("<option value="+value["code"]+">"+value["name"]+"</option>");
						 
									
					   }); 
					  
				   }
				   
	    );
	}
	
	function goodsQuery(goodsname,warehouse){
		goodsname=goodsname;
		warehouse=warehouse;
		sessionStorage.setItem('goodsname',goodsname);
		sessionStorage.setItem('warehouse',warehouse);
		
		location.replace(window.uri+"mobile/GoodsQuery/goodspricelist");
	}
	function goodslist(){
		var goodsname=sessionStorage.getItem('goodsname');
		var warehouse=sessionStorage.getItem('warehouse');
		
		$.post(window.uri+"Mobile/GoodsQuery/querylist",
				{
					goodsname:goodsname,
					warehouse:warehouse
				},
	             function(data){
					if(data!=null){
						 $.each(data,function(key,value){
							  a=window.uri+"Mobile/GoodsQuery/goodsinfo";
							  
							  $(listtable).append("<tr><td><a href="+a+"?gcode="+value["code"]+">"+value["code"]+"</a></td>"+"<td>"+value["name"]+"</td><td>"+value["spec"]+"</td></tr>");                   ;
							 
										
						   }); 
					}
					  
					else{
						 $(tb).append("<ul>查询不到结果<ol>");
					}
						
					  
					  
				   }
				   
	    );
	}
	
	function displayinfo(gcode){
		var gcode=gcode;
		var warehouse=sessionStorage.getItem('warehouse');
		$.post(window.uri+"Mobile/GoodsQuery/queryinfo",
				{
					gcode:gcode,
					warehouse:warehouse
				},
	             function(data){
					
						 $.each(data,function(key,value){
							
							  if(value["count"]==null){
								  $(goodsinfo).append("<li class='mui-table-view-cell'>本商品还没入过库</li>");
								  }else{
								  $(goodsinfo).append("<li class='mui-table-view-cell'>商品编码：&nbsp;&nbsp;&nbsp;&nbsp"+value["code"] +"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>商品名称：&nbsp;&nbsp;"+value["name"] +"</span></li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>规格型号："+value["spec"] +"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>计量单位：&nbsp;&nbsp;"+value["unit"]+"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>仓库：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+value["warehouse"] +"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>库存数量：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+value["count"] +"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>最近销售价格："+value["sale"] +"</li>");
								  $(goodsinfo).append("<li class='mui-table-view-cell'>最近采购价格："+value["purchase"] +"</li>");
							  }
								
						   }); 
				}
				   
	    );
	}
		
		function getUrlParam(name){  
		    //构造一个含有目标参数的正则表达式对象  
		    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");  
		    //匹配目标参数  
		    var r = window.location.search.substr(1).match(reg);  
		    //返回参数值  
		    if (r!=null) return unescape(r[2]);  
		    return null;  
		    
		} 
	