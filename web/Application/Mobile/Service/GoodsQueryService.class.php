<?php
	namespace Mobile\Service;
	 class GoodsQueryService {
		public function warehouse(){
			$sql="select code,name from t_warehouse";
			$warehouse=M()->query($sql);
			return $warehouse;
			
				
		}
	}