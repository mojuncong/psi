<?php

namespace Home\Service;

/**
 * FId Service
 *
 * @author 李静波
 */
class FIdService {

	/**
	 * 记录刚刚操作过的FId值
	 */
	public function insertRecentFid($fid) {
		if ($fid == null) {
			return;
		}
		
		$us = new UserService();
		$userId = $us->getLoginUserId();
		if (! $userId) {
			return;
		}
		
		$db = M();
		
		$sql = "select click_count from t_recent_fid where user_id = '%s' and fid = '%s' ";
		$data = $db->query($sql, $userId, $fid);
		
		if ($data) {
			$clickCount = $data[0]["click_count"];
			$clickCount ++;
			
			$sql = "update t_recent_fid 
					set click_count = %d 
					where user_id = '%s' and fid = '%s' ";
			$db->execute($sql, $clickCount, $userId, $fid);
		} else {
			$sql = "insert into t_recent_fid(fid, user_id, click_count) values ('%s', '%s',  1)";
			
			$db->execute($sql, $fid, $userId);
		}
	}

	public function recentFid() {
		$us = new UserService();
		$userId = $us->getLoginUserId();
		
		$sql = " select distinct f.fid, f.name 
				from t_recent_fid r,  t_fid f, t_permission p, t_role_permission rp, t_role_user ru
				where r.fid = f.fid and r.user_id = '%s' and r.fid = p.fid 
				and p.id = rp.permission_id and rp.role_id = ru.role_id 
				and ru.user_id = '%s' 
				order by r.click_count desc
				limit 10";
		
		$data = M()->query($sql, $userId, $userId);
		
		return $data;
	}

	public function getFIdName($fid) {
		$sql = "select name from t_fid where fid = '%s' ";
		$data = M()->query($sql, $fid);
		if ($data) {
			return $data[0]["name"];
		} else {
			return null;
		}
	}
}