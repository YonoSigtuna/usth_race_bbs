<?php
	// 连接数据库
	require_once dirname(__FILE__).'/conn.php';

	// 连接外部函数
	require_once dirname(__FILE__).'/functions.php';





	if($_POST['cmd']) {
		switch ($_POST['cmd']) {


			// 删除评论
			case 'remove_reply':
				$uid = get_uid();
				$tid = $_POST['tid'];
				$tid_last_char = substr($tid, -1);
				$rid = $_POST['rid'];

				// 如果是管理员
				if (administrator($uid) == 1) {

					// 获取回复内容，日志记录用
					$content = get_value("replies_$tid_last_char", "content", "rid=$rid");

					// 根据tid分表删除
					mysqli_query($link, "DELETE FROM `replies_$tid_last_char` WHERE rid=$rid LIMIT 1; ");

					// 记录日志
					log_add($uid, "\$user 在帖子 \$title 删除了回复：$content", $tid);

					// BUG未修复
					echo "succ";
				} else {
					echo "refuse";
				}
				break;



			// 删除帖子
			case 'remove_topic':
				$uid = get_uid();
				$tid = $_POST['tid'];
				$tid_last_char = substr($tid, -1);

				// 如果是管理员
				if (administrator($uid) == 1) {
					
					// 获取帖子标题，记录日志用
					$title = get_topic($tid, "title");

					// 获取tid对应的fid
					$fid = get_fid($tid);

					// 根据分表删除帖子索引和数据
					mysqli_query($link, "DELETE FROM `topics_index` WHERE tid=$tid LIMIT 1; ");
					mysqli_query($link, "DELETE FROM `topics_${fid}_$tid_last_char` WHERE tid=$tid LIMIT 1; ");

					// 如果每日推荐中包含，则清除每日推荐
					// 每日推荐格式：0||1||2
					$recommend = get_value("sys_auto_increment_value", "value", "variable='recommend'");

					// 格式清除：0||，||1||，||2
					$recommend = str_replace("$tid||", "84||", $recommend);
					$recommend = str_replace("||$tid||", "||84||", $recommend);
					$recommend = str_replace("||$tid", "||84", $recommend);
				
					// 更新每日推荐
					mysqli_query($link, "UPDATE sys_auto_increment_value SET value='$recommend' WHERE variable='recommend' LIMIT 1");

					// 记录日志
					log_add($uid, "\$user 在版块${fid}删除了帖子：$title");
					echo 'succ';

				} else {
					echo "refuse";
				}
				break;






			// 更新评分
			case 'update_rating':
				$tid = $_POST['tid'];
				$tid_last_char = substr($tid, -1);
				$score = $_POST['score'];
				$state = $_POST['state'];
				$uid = get_uid();
				$date = get_time();

				// 先前评分过，更新评分
				if(mysql_exist("rating_$tid_last_char", "uid", "$uid", "AND tid=$tid") == 1) {
					mysqli_query($link, "UPDATE rating_$tid_last_char SET date = '$date', score = '$score', state = '$state' WHERE tid=$tid AND uid=$uid; ");

				// 第一次评分
				} else {
					mysqli_query($link, "INSERT INTO `rating_$tid_last_char` (tid, uid, date, score, state) VALUE ('$tid', '$uid', '$date', '$score', '$state'); ");
				}

				log_add($uid, "\$user 在帖子 \$title 更新了评分：${score} | ${state}", $tid);
				break;










			// 保存百度网盘账号信息
			case 'save_bdwp_account':
				$cookie = $_POST['cookie'];
				$uid = get_uid();
				
				// cookie未空，删除账号
				if (!$cookie) {
					mysqli_query($link, "DELETE FROM pan_account WHERE uid=$uid; ");
					exit;
				}

				// 判断之前填写过cookie
				if (mysql_exist("pan_account", "uid", $uid) == 1) {

					// 更新cookie
					mysqli_query($link, "UPDATE pan_account SET cookie='$cookie' WHERE uid=$uid; ");

				// 新填入，储存进pan_account
				} else {
					mysqli_query($link, "INSERT INTO `pan_account` (uid, cookie) VALUES ($uid, '$cookie');");
				}
				break;







			// 封禁用户
			case 'ban':
				// 判定管理员权限组
				if (!get_uid() == 1) {
					exit("你当前没有权限封禁任何人");
				}

				// 获取前端信息
				$uid = $_POST['uid'];
				$uid_last_char = substr($uid, -1);
				$ban_reason = $_POST['ban_reason'];

				// 根据目标uid查找QQ
				$qq = get_value("users_info_$uid_last_char", "qq", "uid=$uid");
				
				// 将QQ记录banlist中
				mysqli_query($link, "INSERT INTO qq_banlist (qq) VALUE ('$qq'); ");

				// 将用户密码抹除，同时删除sessionID
				mysqli_query($link, "UPDATE users_info_$uid_last_char SET psw='' WHERE uid=$uid; ");
				mysqli_query($link, "DELETE FROM users_sessions WHERE uid=$uid; ");

				echo "风纪执行完成";
				// 
				// 日志记录
				// 

				$uid_ = get_uid();	// 执行者uid
				$uname = get_uname($uid);	// 被ban的用户名
				$uname_ = get_uname($uid_);	// 执行者用户名

				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '$uname_($uid_) 对 $uname($uid) 执行了风纪行动，执行理由：$ban_reason', 0);", date('Y'),  date('Y-m-d H:i')));
				break;



			// 请求旧站的老链接（临时）
			case 'request_old_download':
				$tid = $_POST['tid'];
				$uid = get_uid();

				// 根据tid查找百度网盘链接
				$pan = mysqli_query($link, "SELECT value FROM pre_forum_typeoptionvar WHERE tid=$tid AND optionid=14 LIMIT 1");
				$pan = $pan->fetch_assoc()['value'];

				// 查询今日是否下载过，下载过返回1，未下载返回0
				$today = date('Y-m-d');
				$result = mysql_exist('logs_download', 'uid', $uid, "AND date='$today'");
				if ($result == 1) {

					// 判断下载次数是否小于3
					$download_count = get_value("logs_download", "count", "date='$today' AND uid=$uid");
					if ($download_count < 3) {

						// 下载次数 + 1
						mysqli_query($link, "UPDATE `logs_download` SET count = count + 1 WHERE date='$today' AND uid=$uid LIMIT 1;");

						// 返回给前端URL
						echo $pan;

						// 
						// 日志记录
						// 
						// 获取帖子标题
						$title = get_value("pre_forum_post", "subject", "position=1 AND tid=$tid");

						// 获取前端用户名
						$uname = get_uname($uid);

						mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname}(${uid}) 在旧站帖子 <a href=\"./index.php\" target=\"_blank\">$title</a>请求了一次下载链接。', 0);", date('Y'),  date('Y-m-d H:i')));


					// 下载次数超过3次，禁止下载
					} else {
						echo 'refuse';
					}




				// 今日未下载
				} else {

					// 记录今日第一次下载链接
					mysqli_query($link, "INSERT INTO `logs_download` (date, uid, count) VALUE ('$today', $uid, 1)");

					echo $pan;

					// 
					// 日志记录
					// 
					// 获取帖子标题
					$title = get_value("pre_forum_post", "subject", "position=1 AND tid=$tid");

					// 获取前端用户名
					$uname = get_uname($uid);

					mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname}(${uid}) 在旧站帖子 <a href=\"./index.php\" target=\"_blank\">$title</a>请求了一次下载链接。', 0);", date('Y'),  date('Y-m-d H:i')));

				}



				break;




			// 填充下载链接
			case 'fill_url':
				// 获取链接和tid
				$url = $_POST['url'];
				$tid = $_POST['tid'];

				// 填入新链接
				mysqli_query($link, "INSERT INTO wangpan_urls (tid, url) VALUES ($tid, '$url')");

				// 
				// 日志记录
				// 
				// 获取前端uid
				$uid = get_uid();

				// 获取uid对应的uname
				$uname = get_uname($uid);

				// 根据tid获取帖子标题
				$title = get_topic($tid, "title");
				
				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname}(${uid}) 为帖子<a href=\"./view_topic.php?tid=$tid&page=0\" target=\"_blank\">$title</a>填入了一个新链接', 0);", date('Y'),  date('Y-m-d H:i')));
				break;












			// 请求下载
			case '':
				break;







			// 请求下载
			case 'download':
				$tid = $_POST['tid'];
				$uid = get_uid();
				$url = get_value("wangpan_urls", "url", "tid=$tid");

				// // 判断请求是管理员
				// if (administrator($uid) == 1) {
				// 	exit($url);
				// }

				// 判断请求是作者
				if ($uid == get_topic($tid, 'uid')) {
					exit($url);
				}

				// 今日下载达到3次，禁止下载
				$today = get_time("Y-m-d");
				if (get_value("logs_download", "count", "date='$today' AND uid=$uid") == 3) {
					exit("limit");
				}

				// 链接存在，这个if判断不能删，前端要空值判断链接填入
				if ($url) {

					// 判断用户今日未下载，记录第一次下载
					if (mysql_exist("logs_download", "uid", "$uid", "AND date='$today'") == 0) {
						mysqli_query($link, "INSERT INTO `logs_download` (date, uid, count) VALUE ('$today', $uid, 1)");

					// 下载次数追加
					} else {
						mysqli_query($link, "UPDATE `logs_download` SET count = count + 1 WHERE date='$today' AND uid=$uid LIMIT 1;");
					}

					// 跳转链接整合
					$encode = encode($tid);
					echo "./download.php?tid=$tid&encode=$encode";

					// 
					// 日志记录
					// 
					// 获取帖子标题
					$title = get_topic($tid, 'title');

					// 获取前端用户名
					$uname = get_uname($uid);

					mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname}(${uid}) 在帖子<a href=\"./view_topic.php?tid=$tid&page=0\" target=\"_blank\">$title</a>请求了一次下载链接。', 0);", date('Y'),  date('Y-m-d H:i')));
				}
				break;




















			// 系统信息已读
			case 'finish_read_system_msgs':
				mysqli_query($link, sprintf("UPDATE logs_%s SET `read` = 1 WHERE uid=0 ORDER by date DESC LIMIT 100", date('Y')));
				break;





			// 请求系统信息
			case 'request_system_msgs':
				// 查询uid=0的近100条信息
				$data = mysqli_query($link, sprintf("SELECT * FROM logs_%s WHERE uid=0 ORDER by date DESC LIMIT 100", date('Y')));

				$n = 0;
				// 循环每个msg
				while ($row = $data->fetch_assoc()) {
					$msgs[$n]['date'] = $row['date'];
					$msgs[$n]['content'] = $row['content'];
					$msgs[$n]['read'] = $row['read'];
					$n++;
				}

				// 返回前端
				echo json_encode($msgs);
				break;












			// 标记已读
			case 'finish_read':
				// 获取前端sessionID查找uid
				$uid = get_uid();

				mysqli_query($link, sprintf("UPDATE logs_%s SET `read` = 1 WHERE uid=$uid ORDER by date DESC LIMIT 50", date('Y')));
				break;




			// 请求个人信息
			case 'request_msgs':
				// 获取前端sessionID查找uid
				$uid = get_uid();

				// 根据uid查找50条最新信息
				$data = mysqli_query($link, sprintf("SELECT * FROM logs_%s WHERE uid=$uid LIMIT 50", date('Y')));

				$n = 0;
				// 循环每个msg
				while ($row = $data->fetch_assoc()) {
					$msgs[$n]['date'] = $row['date'];
					$msgs[$n]['content'] = $row['content'];
					$msgs[$n]['read'] = $row['read'];
					$n++;
				}

				// 返回前端
				echo json_encode($msgs);
				break;



			// 请求帖子数据
			case 'request_topics':
				$fid = $_POST['fid'];
				$page = $_POST['page'];

				// 偏移量 = page * 20，page初始为0
				$offset_value = $page * 20;
		
				// 获取需要的20个tid
				$result = mysqli_query($link, "SELECT tid FROM `topics_index` WHERE fid='$fid' ORDER BY tid DESC LIMIT 20 OFFSET $offset_value; ");
				$n = 0;

				// 循环每个tid找到对应的帖子数据
				while ($row = $result->fetch_assoc()) {
					// 获取tid最后一位
					$tid = $row['tid'];
					$tid_last_char = substr($tid, -1);

					// 列表式请求
					if ($fid != "1-1" && $fid != "1-2" && $fid != "1-3" && $fid != "1-4" && $fid != "2-1" && $fid != "2-2") {
						file_put_contents("./ces.txt", "SELECT * FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1");
						$data = mysqli_query($link, "SELECT * FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1");
						$data = $data->fetch_assoc();
						// ['tid']
						// ['title']
						// ['content']
						// ['uid']
						// ['date']
						// ['tags']
						// ['preview']
						

						// 获取该tid的最新回复数据
						$reply = mysqli_query($link, "SELECT uid, content, date FROM replies_${tid_last_char} WHERE tid=$tid ORDER BY rid DESC LIMIT 1");
						$reply = $reply->fetch_assoc();

						// 如果最新回复存在
						if ($reply) {

							// 获取最新回复内容
							$reply_content = $reply['content'];

							// 获取最新回复用户名
							$reply_uname = get_uname($reply['uid']);

							// 整合
							$data['newest_reply_date'] = $reply['date'];
							$data['newest_reply'] = "${reply_uname}：$reply_content";
						}

					// 卡片式请求
					} else {
						$data = mysqli_query($link, "SELECT tid, title, uid, date, preview FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1");
						$data = $data->fetch_assoc();
						// ['tid']
						// ['title']
						// ['uid']
						// ['date']
						// ['preview']

						// 请求作者头像
						$data['avatar'] = get_avatar($data['uid']);
					}

					// 获取uid对应的用户名
					$data['auther'] = get_uname($data['uid']);

					// 整合数据
					$forums[$n]  = $data;
					$n++;
				}

				// 将所有数据返回前端
				echo json_encode($forums);
				break;


		


			// 回复别人的回复
			case 'reply_reply':
				// 根据sessionID查找uid
				$uid = get_uid();

				// 获取数据
				$tgt_rid = $_POST['rid'];
				$content = $_POST['content'];
				$tid = $_POST['tid'];
				
				// 获取当前时间
				$date = date("Y-m-d H:i");

				// 取tid最后一位做分表
				$tid_last_char = substr($tid, -1);

				// 根据uid查找用户名
				$uname = get_uname($uid);

				// 获取最新rid
				$rid = mysqli_query($link, "SELECT value FROM sys_auto_increment_value WHERE variable='rid' LIMIT 1");
				$rid = $rid->fetch_assoc()['value'];

				// rid + 1
				mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='rid'");

				// 回复信息储存进数据库
				mysqli_query($link, "INSERT INTO replies_$tid_last_char (tid, rid, uid, content, date, reply_rid) VALUES ($tid, $rid, $uid, '$content', '$date', $tgt_rid)");


				// 根据tid找fid
				$fid = get_fid($tid);

				// 指定tid回复量 + 1
				mysqli_query($link, "UPDATE `topics_${fid}_${tid_last_char}` SET reply_count = reply_count + 1 WHERE tid=$tid");

				// 
				// 日志
				// 
				// 获取目标rid的用户名
				$tgt_uid = mysqli_query($link, "SELECT uid FROM replies_${tid_last_char} WHERE rid=$tgt_rid LIMIT 1");
				$tgt_uid = $tgt_uid->fetch_assoc()['uid'];
				$tgt_uname = get_uname($tgt_uid);
				
				// 根据tid获取帖子标题
				$title = get_topic($tid, "title");

				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES ($tgt_uid, '%s', '${tgt_uname} 在帖子<a href=\"./view_topic.php?tid=$tid&page=0&rid=$rid\" target=\"_blank\">$title</a>回复了你：$content', 0);", date('Y'),  date('Y-m-d H:i')));
				break;










			// 回复二次审核通过
			case 'allow_reply':
				break;


			// 回复二次审核拒绝
			case 'refuse_reply':
				break;


			// 帖子回复
			case 'reply_topic':
				// 获取回复内容
				$tid = $_POST['tid'];
				$content = $_POST['content'];

				// 拆分tid最后一位做分表
				$tid_last_char = substr($tid, -1);

				// 根据sessionID查找uid
				$uid = get_uid();

				// 获取当前时间
				$time = date("Y-m-d H:i");

				// 根据uid查找用户名
				$uname = get_uname($uid);

				// 分配一个rid
				$rid = mysqli_query($link, "SELECT value FROM sys_auto_increment_value WHERE variable='rid' LIMIT 1");
				$rid = $rid->fetch_assoc()['value'];

				// rid + 1
				mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='rid'");

				// 将回复信息储存进数据库
				mysqli_query($link, "INSERT INTO replies_$tid_last_char (tid, rid, uid, content, date) VALUES ($tid, $rid, $uid, '$content', '$time')");

				// 将回复信息储存进审核表
				mysqli_query($link, "INSERT INTO check_replies (tid, uid, content) VALUES ($tid, $uid, '${uname} ($time)：${content}')");

				// 根据tid找fid
				$fid = mysqli_query($link, "SELECT fid FROM topics_index WHERE tid=$tid LIMIT 1");
				$fid = $fid->fetch_assoc()['fid'];

				// 指定tid回复量 + 1
				mysqli_query($link, "UPDATE `topics_${fid}_${tid_last_char}` SET reply_count = reply_count + 1 WHERE tid=$tid");

				// 
				// 日志
				// 
				// 获取帖子title和uid数据
				$data = mysqli_query($link, "SELECT title, uid FROM `topics_${fid}_${tid_last_char}` WHERE tid=$tid LIMIT 1");
				$data = $data->fetch_assoc();

				// 获取帖子title
				$title = $data['title'];

				// 获取帖子作者uid
				$auther_uid = $data['uid'];

				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES ($auther_uid, '%s', '${uname}($uid) 回复了你的帖子<a href=\"./view_topic.php?tid=$tid&page=0\" target=\"_blank\">$title</a>：${content}', 0);", date('Y'),  date('Y-m-d H:i')));
				break;



			// 发布帖子
			case 'send_topic':
				// 获取数据
				$title = $_POST['title'];
				$content = $_POST['content'];
				$date = $_POST['date'];
				$tags = $_POST['tags'];
				$cover = $_POST['cover'];
				$fid = $_POST['fid'];
				$cid = $_POST['cid'];

				// 根据sessionID查找uid
				$uid = get_uid();

				// 如果cid不存在
				if (!$cid) {
					break;
				}

				// cid全为数字，即为tid，为修改帖子
				if (is_numeric($cid)) {
					$tid = $cid;
					$tid_last_char = substr($tid, -1);

					// 获取旧的fid
					$old_fid = get_fid($tid);
					
					// 旧fid == 新fid，更新帖子内容
					if ($old_fid == $fid) {
						mysqli_query($link, "UPDATE `topics_${fid}_${tid_last_char}` SET title='$title', content='$content', tags='$tags', preview='$cover' WHERE tid=${tid} LIMIT 1");

						// 对tags格式化	
						format_tags_to_id($tid);

						// 记录日志
						log_add($uid, "\$user 在版块${fid}审核并通过了重新编辑的帖子：\$title", $tid);
						break;

					// fid有变更
					} else {

						// 获取帖子的发帖的 日期 浏览量 回复量
						$data = get_topic($tid);
						// ['date']
						// ['view_count']
						// ['reply_count']

						// 更新topics_index索引列表
						mysqli_query($link, "UPDATE topics_index SET fid='$fid' WHERE tid=$tid");

						// 删除旧贴分表占位
						mysqli_query($link, "DELETE FROM `topics_${old_fid}_${tid_last_char}` WHERE tid=$tid LIMIT 1");

						// 插入新的fid分表
						mysqli_query($link, "INSERT INTO `topics_${fid}_${tid_last_char}` (tid, title, content, uid, date, tags, preview, view_count, reply_count) VALUES ('$tid', '$title', '$content', '$uid', '{$data['date']}', '$tags', '$cover', '{$data['view_count']}', '{$data['reply_count']}')");

						// 对tags格式化	
						format_tags_to_id($tid);

						// 记录日志
						log_add($uid, "\$user 在版块${old_fid}迁移至${fid}审核并通过了重新编辑的帖子：\$title", $tid);
						break;
					}

				// 新发帖审核
				} else {
					// 获取最新tid
					$tid = get_value("sys_auto_increment_value", "value", "variable='tid'");
					$tid_last_char = substr($tid, -1);

					// tid自增值+1
					mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='tid'");

					// 分配入表
					mysqli_query($link, "INSERT INTO `topics_${fid}_${tid_last_char}` (tid, title, content, uid, date, tags, preview, view_count, reply_count) VALUES ('$tid', '$title', '$content', '$uid', '$date', '$tags', '$cover', 0, 0)");

					// 记录至索引
					mysqli_query($link, "INSERT INTO `topics_index` (fid, tid) VALUES ('$fid', $tid)");

					// 更新tags
					format_tags_to_id($tid);

					// 对帖子作者猫罐头 + 1
					$uid_last_char = substr($uid, -1);
					mysqli_query($link, "UPDATE `users_data_$uid_last_char` SET canned_count = canned_count + 1 WHERE uid=$uid LIMIT 1;");

					// 图片文件夹名字存在，进行tid命名
					if (file_exists("./data/forums/${cid}")) {
						rename("./data/forums/${cid}", "./data/forums/${tid}");
					}

					// 记录日志
					log_add($uid, "\$user 在版块${fid}发布了帖子：\$title", $tid);
				}
				break;



			// 发帖时加载帖子图片
			case 'load_topic_imgs':
				$cid = $_POST['cid'];
				
				// 判断cid或tid是否有对应的文件夹
				if (file_exists("./data/forums/${cid}")) {
					// 获取文件夹下的所有图像文件
					$imgs = scandir(dirname(__FILE__)."/data/forums/${cid}");

					// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
					unset($imgs[0]);
					unset($imgs[1]);
					unset($imgs[array_search('Thumbs.db', $imgs)]);

					// 利用正则表达式过滤掉非jpg文件
					$imgs = array_filter($imgs, function($value) {
						return preg_match('/\.jpg$/', $value);
					});

					// 去除键名
					$imgs = array_values($imgs);

					// 返回前端
					echo json_encode($imgs);
				}
				break;




			// 帖子数据请求
			case 'request_list_topics':
				// 参数获取
				$fid = $_POST['fid'];
				$page = $_POST['page'];

				// 偏移量 = page * 20，page初始为0
				$offset_value = $page * 20;

				// 获取需要的20个tid
				$result = mysqli_query($link, "SELECT tid FROM topics_index WHERE fid='{$fid}' and tid <= {$max} ORDER BY tid DESC LIMIT 20");
				$n = 0;

				// 循环每个tid找到对应的帖子数据
				while ($row = $result->fetch_assoc()) {
					// 获取tid最后一位
					$tid = $row['tid'];
					$tid_last_char = substr($tid, -1);

					// 根据tid最后一位查表
					$data = mysqli_query($link, "SELECT * FROM `topics_${fid}_{$tid_last_char}` WHERE tid={$tid} LIMIT 1");
					$data = $data->fetch_assoc();
					// ['tid']
					// ['title']
					// ['content']
					// ['uid']
					// ['date']
					// ['tags']
					// ['preview']


					// 获取uid对应的用户名
					$uid = $data['uid'];
					$uid_last_char = substr($uid, -1);
					$uname = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char}` WHERE uid={$uid}");
					$uname = $uname->fetch_assoc()['uname'];

					// 获取给定tid的最新回复数据
					$reply = mysqli_query($link, "SELECT uid, content, date FROM replies_${tid_last_char} WHERE tid=$tid ORDER BY rid DESC LIMIT 1");
					$reply = $reply->fetch_assoc();

					// 如果最新回复存在，整合并输出
					if ($reply) {
						$uname = get_uname($reply['uid']);
						$content = $reply['content'];

						$data['newest_reply_date'] = $reply['date'];
						$data['newest_reply'] = "${uname}：$content";
					}

					// 赋值给总数据
					$data['auther'] = $uname;

					// 整合数据
					$forums[$n]  = $data;
					$n++;
				}

				// 将所有数据返回前端
				echo json_encode($forums);
				break;








			// 在线时间增加
			case 'add_online_time':
				// 根据sessionID查找uid
				$uid = get_uid();

				// 取uid最后一位字符分表
				$uid_last_char = substr($uid, -1);

				// 时间 + 0.05h
				mysqli_query($link, "UPDATE users_data_${uid_last_char} SET online_time = online_time + 3 WHERE uid=$uid");

				// 更新最后在线时间
				$time = date("Y-m-d H:i");
				mysqli_query($link, "UPDATE users_data_${uid_last_char} SET last_login_time = '$time' WHERE uid=$uid");
				break;









			// 头像审核拒绝
			case 'refuse_avatar':
				$uid = $_POST['uid'];

				// 删除头像审核数据
				mysqli_query($link, "DELETE FROM check_avatars WHERE uid=$uid LIMIT 1");

				// 删除头像文件
				unlink("./data/_checking/avatars/${uid}.jpg");
				unlink("./data/_checking/avatars/${uid}_small.jpg");

				// 获取uid最后一位做分表（头像主人）
				$uid_last_char = substr($uid, -1);

				// 根据uid查找用户名（头像主人）
				$uname = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char}` WHERE uid={$uid}");
				$uname = $uname->fetch_assoc()['uname'];

				// 根据sessionID查找uid（审核用户）
				$uid_ = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid_ = $uid_->fetch_assoc()['uid'];

				
				// 拆分uid最后一位做分表（审核用户）
				$uid_last_char_ = substr($uid_, -1);

				// 根据uid查找用户名（审核用户）
				$uname_ = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char_}` WHERE uid={$uid_}");
				$uname_ = $uname_->fetch_assoc()['uname'];

				// 记录日志
				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname_}(${uid_}) 审核并拒绝了 ${uname}(${uid}) 的头像。', 0)", date('Y'),  date('Y-m-d H:i')));
				break;


			// 头像审核通过
			case 'allow_avatar':
				$uid = $_POST['uid'];

				// 移动头像文件
				rename("./data/_checking/avatars/{$uid}.jpg", "./data/avatars/{$uid}.jpg");
				rename("./data/_checking/avatars/{$uid}_small.jpg", "./data/avatars/{$uid}_small.jpg");

				// 删除头像审核数据
				mysqli_query($link, "DELETE FROM check_avatars WHERE uid=$uid LIMIT 1");

				// 删除头像文件
				unlink("./data/_checking/avatars/${uid}.jpg");
				unlink("./data/_checking/avatars/${uid}_small.jpg");

				// 获取uid最后一位做分表（头像主人）
				$uid_last_char = substr($uid, -1);

				// 根据uid查找用户名（头像主人）
				$uname = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char}` WHERE uid={$uid}");
				$uname = $uname->fetch_assoc()['uname'];

				// 根据sessionID查找uid（审核用户）
				$uid_ = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid_ = $uid_->fetch_assoc()['uid'];

				
				// 拆分uid最后一位做分表（审核用户）
				$uid_last_char_ = substr($uid_, -1);

				// 根据uid查找用户名（审核用户）
				$uname_ = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char_}` WHERE uid={$uid_}");
				$uname_ = $uname_->fetch_assoc()['uname'];

				// 记录日志
				mysqli_query($link, sprintf("INSERT INTO logs_%s (uid, date, content, `read`) VALUES (0, '%s', '${uname_}(${uid_}) 审核并通过了 ${uname}(${uid}) 的头像：<a href=\"./data/avatars/${uid}_small.jpg\" target=\"_blank\">${uid}_small.jpg</a>', 0)", date('Y'),  date('Y-m-d H:i')));
				break;



			// 更改密码
			case 'replace_psw':
				$new_psw = $_POST['psw'];

				// 根据sessionID查找uid
				$uid = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid = $uid->fetch_assoc()['uid'];

				// 取uid最后一位字符分表
				$uid_last_char = substr($uid, -1);

				// 更新密码
				mysqli_query($link, "UPDATE users_info_${uid_last_char} SET psw = '$new_psw' WHERE uid=$uid LIMIT 1");
				break;



				




			// 更新个人信息
			case 'user_data_update':
				$info = explode('||', $_POST['info']);
				// [0]	->	sign
				// [1]	->	sign_img
				// [2]	->	best_love_story
				// [3]	->	playing_story
				// [4]	->	recommend_stories

				// 根据sessionID查找uid
				$uid = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid = $uid->fetch_assoc()['uid'];

				// 取uid最后一位字符分表
				$uid_last_char = substr($uid, -1);

				// 更新数据
				mysqli_query($link, "UPDATE users_data_${uid_last_char} SET sign = '{$info[0]}', sign_img = '{$info[1]}', best_love_story = '{$info[2]}', playing_story = '{$info[3]}', recommend_stories = '{$info[4]}' WHERE uid = $uid;");
				break;









				




			// 登录
			case 'login':
				// 获取信息
				$uname = $_POST['uname'];
				$psw = $_POST['psw'];

				// 查询用户名
				for ($n = 0; $n < 10; $n++) {
					$uid = mysqli_query($link, "SELECT uid FROM users_info_${n} WHERE uname='$uname' and psw='$psw'; ");
					$uid = $uid->fetch_assoc()['uid'];

					// 登录成功
					if ($uid > 0) {
						// 开启会话
						session_start();

						// 生成新的sessionID
						session_regenerate_id(true);

						$sessionID = session_id();

						// 设置前端cookie
						setcookie('sessionID', $sessionID, time() + 2592000, '/');

						// 删除旧的sessionID和对应的uid
						mysqli_query($link, "DELETE FROM users_sessions WHERE uid=${uid}");

						// 记录新的sessionID对应的uid 
						mysqli_query($link, "INSERT INTO users_sessions(sessionID, uid) VALUES ('{$sessionID}', ${uid})");		

						// 登录成功
						echo "登录成功";
						break;
					}
				}

				// 登录失败
				if ($uid == 0) {
					echo '登录失败，请仔细确认 用户名 / 密码 是否存在错误。';
				}
				break;













			// 注册
			case 'register':
				// 获取注册信息
				$uname = $_POST['uname'];
				$psw = $_POST['psw'];
				$email = $_POST['email'];

				// 判断用户名是否重复
				for ($n = 0; $n < 10; $n++) {
					$result = mysql_exist("users_info_$n", "uname", $uname);

					// 存在返回1，不存在返回0
					if ($result == 1) {
						exit('用户名已被注册');
					}
				}

				// 判断邮箱是否重复
				for ($n = 0; $n < 10; $n++) {
					$result = mysql_exist("users_info_$n", "email", "$email");

					// 存在返回1，不存在返回0
					if ($result == 1) {
						exit('当前邮箱已被注册');
					}
				}

				// 提取最新uid
				$uid = get_value("sys_auto_increment_value", "value", "variable='uid'");

				// 取uid最后一位字符分表
				$uid_last_char = substr($uid, -1);

				// 分表储存注册信息uid uname psw email
				mysqli_query($link, "INSERT INTO users_info_$uid_last_char (uid, uname, psw, email) VALUE ($uid, '$uname', '$psw', '$email')");

				// 获取当前时间
				$time = get_time();

				// 储存用户默认数据
				mysqli_query($link, "INSERT INTO users_data_${uid_last_char} (uid, online_time, identity, credit, academic_year, schoolship, judment_count, canned_count, register_time, last_login_time) VALUES (${uid}, 0, '普通用户', 0, '编程一年生', 0, 0, 0, '${time}', '${time}')");

				// 自增表uid+1
				mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='uid'; ");

				// 数据库记录注册信息
				echo '注册成功，请手动进行登录';
				break;
		}
	}
