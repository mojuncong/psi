<?php

namespace Home\DAO;

/**
 * 商品构成DAO
 *
 * @author 李静波
 */
class GoodsBomDAO extends PSIBaseDAO {

	/**
	 * 获得某个商品的商品构成
	 */
	public function goodsBOMList($params) {
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		
		$sql = "select b.id, b.sub_goods_count, g.code, g.name, g.spec, u.name as unit_name
				from t_goods_bom b, t_goods g, t_goods_unit u
				where b.goods_id = '%s' and b.sub_goods_id = g.id and g.unit_id = u.id
				order by g.code";
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["sub_goods_count"];
		}
		
		return $result;
	}
}