<?php

namespace Home\DAO;

/**
 * 用户 DAO
 *
 * @author 李静波
 */
class UserDAO extends PSIBaseDAO {

	/**
	 * 判断某个用户是否被禁用
	 *
	 * true: 被禁用
	 */
	public function isDisabled($userId) {
		$db = M();
		
		$sql = "select enabled from t_user where id = '%s' ";
		$data = $db->query($sql, $userId);
		if ($data) {
			return $data[0]["enabled"] == 0;
		} else {
			// $userId的用户不存在，也视为被禁用了
			return true;
		}
	}
}