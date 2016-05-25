<?php
	namespace Mobile\Service;
	 use Home\Service\PinyinService;
		class GoodsQueryService {
		public function warehouse(){
			$sql="select code,name from t_warehouse";
			$warehouse=M()->query($sql);
			return $warehouse;
			
				
		}
		public function goodlist($goodsname,$warehouse){
			$goodsname=$goodsname;
			$warehouse=$warehouse;
			$sql="select v.code,v.name,v.spec from t_goods v where v.data_org=(select t.data_org from t_warehouse t where t.code='%s') and (v.py like  '%s' or v.code = '%s') limit 10";
							
			$param1=$warehouse;
			$pys= new PinyinService();
			$py = $pys->toPY($goodsname);
			$param2="%{$py}%";
			$result=M()->query($sql,$param1,$param2,$goodsname);
			
			
			return $result;
		}
		
		public  function goodinfos($gcode,$warehouse){
			$param1=$gcode;
			$param2=$warehouse;
			$sq1="select v.code,v.name,v.spec from t_goods v where v.data_org=(select t.data_org from t_warehouse t where t.code='%s') and v.code='%s' order by v.name desc limit 1";
			$sq2="select v.name from t_goods_unit v where v.id=(select t.id from t_goods t where t.code='%s') limit 1";
			$sq3="select name from t_warehouse where code='%s'limit 1";
			$sq4="select v.balance_count from t_inventory_detail v where  v.goods_id=(select t.id from t_goods t where t.code='%s') order by v.date_created desc limit 1";
			$sq5="select d.goods_price from t_ws_bill w, t_ws_bill_detail d where w.id = d.wsbill_id and d.goods_id = (select t.id from t_goods t where t.code='%s') order by w.bizdt desc limit 1";
			$sq6="select d.goods_price from t_pw_bill p, t_pw_bill_detail d where p.id = d.pwbill_id and d.goods_id = (select t.id from t_goods t where t.code='%s') order by p.biz_dt desc limit 1";
			$r1=M()->query($sq1,$param2,$param1);
		    $r2=M()->query($sq2,$param1);
 			$r3=M()->query($sq3,$param2);
			$r4=M()->query($sq4,$param1);
			$r5=M()->query($sq5,$param1);
			$r6=M()->query($sq6,$param1);
		
			foreach ($r1 as $i=>$v){
				$result["name"]=$v["name"];
				$result["code"]=$v["code"];
				$result["spec"]=$v["spec"];
			}
			foreach ($r2 as $i=>$v){
				$result["unit"]=$v["name"];
			}
			foreach ($r3 as $i=>$v){
				$result["warehouse"]=$v["name"];
			}
			foreach ($r4 as $i=>$v){
				$result["count"]=$v["balance_count"];
			}
			foreach ($r5 as $i=>$v){
				$result["sale"]=$v["goods_price"];
			}
			foreach ($r6 as $i=>$v){
				$result["purchase"]=$v["goods_price"];
			}
				
//  			$result["name"]=$r1["name"];
//  			$result["code"]=$r1["code"];
// 			$result["spec"]=$r1["spec"];
// 			$result["unit"]=$r2["name"];
// 			$result["warehouse"]=$r3["name"];
// 			$result["count"]=$r4["balance_count"];
// 			$result["sale"]=$r5["goods_price"];
// 			$result["purchase"]=$r6["goods_price"];
			return [$result];
			
			
			
		}
	}