<?php
	// 连接数据库
	require_once dirname(dirname(__FILE__)).'/conn.php';

	require_once dirname(dirname(__FILE__)).'/functions.php';

	if($_POST['cmd']){

		// 分割cmd
		switch ($_POST['cmd']) {








			// 删除视频
			case 'delete_video':
				$cid_or_tid = $_POST['cid_or_tid'];
				$vid = $_POST['vid'];

				// 删除目标视频文件
				unlink("./forums/${cid_or_tid}/$vid.mp4");
				break;


			// 检测视频切片是否存在
			case 'check_chunk_exist':
				$path = $_POST['path'];
				$index = $_POST['index'];
				$all_chunks = $_POST['all_chunks'];

				// 判断目标文件存在
				if (file_exists($path)) {

					// 最后一个切片，进行清除
					if ($index + 1 == $all_chunks) {
						unlink($path);
						echo $index;

					} else {
						echo 'exist';
					}

				// 切片不存在，返回切片序号
				} else {
					echo $index;
				}
				break;


			// 上传帖子视频
			case 'upload_video':
				// 获取参数
				$cid_or_tid = $_POST['cid'];
				$md5 = $_POST['file_md5'];
				$index = $_POST['index'];
				$all_chunks = $_POST['all_chunks'];

				// 判断cid是否有对应的储存路径。无则新建文件夹
				if (!file_exists("./forums/$cid_or_tid")) {
					mkdir("./forums/$cid_or_tid");
				}

				// 创建cache文件夹（如果不存在）
				if (!is_dir("./forums/$cid_or_tid/cache")) {
					mkdir("./forums/$cid_or_tid/cache", 0777, true);
				}

				// 判断切片是否存在，存在则不写入
				if (!file_exists("./forums/$cid_or_tid/cache/$md5.mp4.$index")) {
					// 保存切片
					move_uploaded_file($_FILES['chunk']['tmp_name'], "./forums/$cid_or_tid/cache/$md5.mp4.$index");
				} else {
					break;
				}

				// 判断切片是否为最后一片
				if ($index + 1 == $all_chunks) {
					// 获取vid
					$vid = mysqli_query($link, "SELECT value FROM sys_auto_increment_value WHERE variable='vid' LIMIT 1");
					$vid = $vid->fetch_assoc()['value'];

					// 指定输出文件
					$output_file = fopen("./forums/$cid_or_tid/$vid.mp4", 'w');

					// 将切片添加入数组
					$input_files = array();
					for ($i = 0; $i < $all_chunks; $i++) {
						array_push($input_files, "./forums/$cid_or_tid/cache/$md5.mp4.$i");
					}

					// 循环每个切片
					foreach ($input_files as $intput_file) {
						$input = fopen($intput_file, 'r');

						// 逐个读取输入文件片段内容，并写入到输出文件中
						while (!feof($input)) {
							$data = fread($input, 1048576); // 每次读取1MB = 1 * 1024 * 1024数据
							fwrite($output_file, $data);

							// 休眠0.2秒
							sleep(0.2);
						}
						fclose($input);
					}
					fclose($output_file);

					// 获取源视频大小 和 现视频大小
					$origin_size = $_POST['file_size'];
					$now_size = filesize("./forums/$cid_or_tid/$vid.mp4");

					// 如果恢复视频大小 = 源视频大小，删除所有切片文件
					if ($now_size == $origin_size) {
						// vid + 1
						mysqli_query($link, "UPDATE `sys_auto_increment_value` SET value = value + 1 WHERE variable='vid' LIMIT 1;");

						// 删除cache文件夹
						delete_folder("./forums/$cid_or_tid/cache");

						echo 'succ';

					// 如果恢复视频大小 != 源视频大小
					} else {

						// 删除最后一个切片
						$target_index = $all_chunks - 1;
						unlink("./forums/$cid_or_tid/cache/$md5.mp4.$target_index");

						// 删除恢复不成功的视频
						unlink("./forums/$cid_or_tid/$vid.mp4");
						echo 'error';
					}
				}
				break;



			// 加载帖子视频
			case 'load_topic_videos':
				// 获取cid 或 tid
				$cid_or_tid = $_POST['cid'];
				
				// 判断cid 或 tid是否有对应的文件夹
				if (file_exists("./forums/${cid_or_tid}")) {
					// 获取文件夹下的所有图像文件
					$videos = scandir(dirname(__FILE__)."/forums/${cid_or_tid}");

					// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
					unset($videos[0]);
					unset($videos[1]);
					unset($videos[array_search('Thumbs.db', $videos)]);

					// 利用正则表达式过滤掉非mp4文件
					$videos = array_filter($videos, function($value) {
						return preg_match('/\.mp4$/', $value);
					});

					// 去除键名
					$videos = array_values($videos);

					// 返回前端
					echo json_encode($videos);
				}
				break;



			// 创建缩略图
			case 'create_preview':
				$fid = $_POST['fid'];
				$folder = $_POST['folder'];
				$aids = $_POST['aids'];

				// 判断fid是否为1-1，如果是，则只创建一个preview
				if ($fid == '1-1' || $fid == '1-2' || $fid == '1-3' || $fid == '1-4' || $fid == '2-1' || $fid == '2-2') {
					// 对目标图片进行压缩
					file_put_contents("./ces.txt", "./forums/$folder/$aids.jpg");
					// echo img_zip("./forums/$folder/$aids.jpg", "./forums/$folder/preview.jpg", 75, 400);					

				// fid不为1-1，多个缩略图
				} else {

					// 对aids进行分割
					$aids = explode('|', $aids);

					// 遍历压缩所有图片
					for ($i=0; $i < count($aids); $i++) { 
						$result = img_zip("./forums/$folder/{$aids[$i]}.jpg", "./forums/$folder/preview_$i.jpg", 75, 400);
					}
					echo 'succ';
				}
				break;



			// 帖子删除图片
			case 'remove_topic_img':
				// 获取cid / tid和aid
				$cid = $_POST['cid'];
				$aid = $_POST['aid'];
				
				// 删除图片
				unlink("./forums/$cid/$aid.jpg");
				break;




			// 帖子图片上传
			case 'topic_imgs_upload':
				// cid或tid提取
				$cid = $_POST['cid'];

				// 获取最新aid
				$aid = mysqli_query($link, "SELECT value FROM sys_auto_increment_value WHERE variable='aid' LIMIT 1");
				$aid = $aid->fetch_assoc()['value'];

				// 判断cid是否有对应的储存路径。无则新建文件夹
				if (!file_exists("./forums/${cid}")) {
					mkdir("./forums/${cid}");
				}
			
				// 移入文件
				$file_cache = $_FILES['file']['tmp_name'];
				move_uploaded_file($file_cache, "./forums/${cid}/${aid}.jpg");

				// 返回前端最新显示
				echo $aid;

				// aid + 1
				mysqli_query($link, "UPDATE sys_auto_increment_value SET value = value + 1 WHERE variable='aid'");
				break;











			// 上传头像
			case 'upload_avatar':
				// 获取用户sessionID
				$uid = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid = $uid->fetch_assoc()['uid'];

				// 根据uid分配文件名
				$file_cache = $_FILES['file']['tmp_name'];

				// 记录头像进审核表
				$file_size = filesize($file_cache);
				mysqli_query($link, "INSERT INTO check_avatars (uid, file_size) VALUES ($uid, $file_size)");

				// 移动头像进缓存文件夹
				move_uploaded_file($file_cache, "./_checking/avatars/${uid}.jpg");
				break;


			// 上传小头像
			case 'upload_avatar_small':
				// 获取用户sessionID
				$uid = mysqli_query($link, "SELECT uid FROM users_sessions WHERE sessionID='{$_COOKIE['sessionID']}'");
				$uid = $uid->fetch_assoc()['uid'];

				// 根据uid分配文件名
				$file_cache = $_FILES['file']['tmp_name'];

				// 移动头像进缓存文件夹
				move_uploaded_file($file_cache, "./_checking/avatars/${uid}_small.jpg");
				break;
		}
	}