<?php
defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$page = max ( $page, 1 );
$limit = max ( $limit, 5 );
$url = 'index.php?do=' . $do . '&model_id=' . $model_id . '&view=edit&task_id=' . $task_id . '&op=' . $op;
switch ($op) {
	case 'work' : 
		if ($ac && $work_id) {
			switch ($ac) {
				case 'del' : 
					db_factory::execute ("update ".TABLEPRE."witkey_task set work_num=work_num-1 where task_id =(select task_id from ".TABLEPRE."witkey_task_work where work_id=".intval($work_id).")");
					$res = db_factory::execute ( sprintf ( 'delete  from %switkey_task_work where work_id=%d', TABLEPRE, intval ( $work_id ) ) );
					if ($res) {
						keke_file_class::del_obj_file(intval ( $work_id ),'work',true);
						db_factory::execute ( sprintf ( ' delete from %switkey_comment where obj_id=%d', TABLEPRE, intval ( $work_id ) ) );
					}
					$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', 0 );
					die ();
					break;
				case 'file' : 
					$taskfilelist=db_factory::get_one("select * from ".TABLEPRE."witkey_task_work where work_id = ".intval($work_id));
					$filelist=$taskfilelist[work_file];
					$f_list=db_factory::query("select * from ".TABLEPRE."witkey_file where file_id in ($filelist)");
					break;
				case 'comm' : 
					$c_list = db_factory::query ( sprintf ( ' select a.content,a.on_time from %switkey_comment a 
						left join %switkey_task_work b on a.obj_id=b.work_id where b.work_id=%d', TABLEPRE, TABLEPRE, $work_id ) );
					break;
			}
			require keke_tpl_class::template ( 'task/' . $model_info ['model_dir'] . '/admin/tpl/task_edit_ext' );
			die ();
		} else {
			$o = keke_table_class::get_instance ( 'witkey_task_work' );
			$tmp = $o->get_grid ( 'task_id=' . $task_id, $url, $page, $limit, ' order by work_status desc,work_time desc ', 1, 'ajax_dom' );
			$list = $tmp ['data'];
			$pages = $tmp ['pages'];
			$satus_arr = sreward_task_class::get_work_status ();
		}
		break;
	case 'comm' : 
		if ($ac && $comm_id) {
			$id = intval ( $comm_id );
			switch ($ac) {
				case 'del' : 
					$sql = ' delete from %switkey_comment where comment_id=%d';
					$type == 1 and $sql .= ' or p_id=%d'; 
					$res = db_factory::execute ( sprintf ( $sql, TABLEPRE, $id, $id ) );
					$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', 0 );
					die ();
					break;
				case 'load' : 
					$list = db_factory::query ( sprintf ( ' select * from %switkey_comment where p_id=%d', TABLEPRE, $id ) );
					require keke_tpl_class::template ( 'task/' . $model_info ['model_dir'] . '/admin/tpl/task_edit_ext' );
					die ();
					break;
				case 'edit':
					CHARSET == 'gbk' and $content = kekezu::utftogbk($content);
					$sql = "update %switkey_comment set content = '%s' where comment_id=%d ";
					$res = db_factory::execute(sprintf($sql,TABLEPRE,$content,$id));
					$res and kekezu::echojson ( '', 1 ) or kekezu::echojson ( '', 0 );
					die ();
					break;
			}
		} else {
			$o = keke_table_class::get_instance ( 'witkey_comment' );
			$tmp = $o->get_grid ( 'obj_id=' . $task_id . ' and p_id=0', $url, $page, $limit, ' order by on_time desc ', 1, 'ajax_dom' );
			$list = $tmp ['data'];
			$pages = $tmp ['pages'];
		}
		break;
	case 'mark' : 
		$list = db_factory::query ( sprintf ( " select * from %switkey_mark where origin_id=%d and `mark_status`!=0 and model_code='%s'", TABLEPRE, $task_id, $model_info ['model_code'] ) );
		break;
	case 'agree' : 
		keke_lang_class::loadlang ( 'task_agreement', 'task_sreward' );
		$id = db_factory::get_count ( sprintf ( ' select agree_id from %switkey_agreement where task_id=%d', TABLEPRE, $task_id ) );
		$o = sreward_task_agreement::get_instance ( $id );
		$agree_info = $o->_agree_info; 
		$buyer_contact = $o->_buyer_contact; 
		$buyer_status_arr = $o->get_buyer_status (); 
		$seller_contact = $o->_seller_contact; 
		$seller_status_arr = $o->get_seller_status (); 
		$buyer_uid = $o->_buyer_uid; 
		$seller_uid = $o->_seller_uid; 
		$buyer_username = $o->_buyer_username; 
		$seller_username = $o->_seller_username; 
		$agree_status = $o->_agree_status; 
		$buyer_status = $o->_buyer_status; 
		$seller_status = $o->_seller_status; 
		$status_arr = $o->get_agreement_status ();
		$r = db_factory::get_count ( sprintf ( ' select report_id from %switkey_report where origin_id=%d and report_type=1 and report_status!=4 limit 0,1', TABLEPRE, $task_id ) );
		break;
}