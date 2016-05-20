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