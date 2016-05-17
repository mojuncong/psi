	/**
	 * 用户登录
	 **/

	  function login(loginInfo) {
		
		loginInfo = loginInfo || {};
		loginInfo.account = loginInfo.account || '';
		loginInfo.password = loginInfo.password || '';
		
		
		$.post(window.uri+"Home/User/loginPOST",
                 {
                   loginName:loginInfo.account,
                    password:loginInfo.password
                  },
                  function(data){
					  var myData=[];
					  $.each(data,function(key,value){
						 myData[key]=value;
						 
									
					   });
						if(myData["success"]==true){
							createState(loginInfo.account);
							location.replace(window.uri+"mobile/index/index");
						}    
					   else{
						    alert(myData["msg"]);
						   
					   }
				   }
				   
         );
		
	   }
	 
	 function createState(name){
		var state = getState();
		state.account = name;
		state.token = "token123456789";
		setState(state);
		sessionStorage.setItem('$session','ok');
	 }
	
	 /**
	 * 获取session状态
	 **/
	function getSession(){
		if(sessionStorage.getItem('$session')=='ok')
			return true;
		return false;
	
	}
	 /**
	 * 获取当前状态
	 **/
	function getState() {
		var stateText = localStorage.getItem('$state') || "{}";
		return JSON.parse(stateText);
	}

	/**
	 * 设置当前状态
	 **/
	function setState(state) {
		state = state || {};
		localStorage.setItem('$state', JSON.stringify(state));
	}
	    
     /**
	 * 获取应用本地配置
	 **/
	 function setSettings(settings) {
		settings = settings || {};
		localStorage.setItem('$settings', JSON.stringify(settings));
	}

	/**
	 * 设置应用本地配置
	 **/
	function getSettings() {
		var settingsText = localStorage.getItem('$settings') || "{}";
		return JSON.parse(settingsText);
	}
	
	/**
	 * 转向主页面
	 **/
	 function toMain(){
		location.replace(window.uri+"mobile/index/index");
	 }
	


