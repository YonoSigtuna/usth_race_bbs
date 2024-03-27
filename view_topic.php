<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/home_page.css">
	<link rel="stylesheet" href="./css/forum.css">

</head>
<body>



	<!-- 引入顶部导航栏 -->
	<?php require_once dirname(__FILE__).'/header.php'; ?>
	<?php
		// 未登录处理
		if (!$uid) {
			exit("<script>alert('当前页面需要登录才能查看内容')</script>");
		}
	?>



	<?php
		// 获取tid最后一位做分表查询
		$tid = $_GET['tid'];
		$tid_last_char = substr($tid, -1);

		// 找到tid对应的fid
		$result = mysqli_query($link, "SELECT fid FROM topics_index WHERE tid=${tid} LIMIT 1;");
		$fid = $result->fetch_assoc()['fid'];

		// tid找fid无效
		if (!$fid) {
			exit("<script>alert('当前tid无对应的帖子内容')</script>");

		// tid找fid有效，帖子浏览量 + 1
		} else {
			mysqli_query($link, "UPDATE `topics_${fid}_${tid_last_char}` SET view_count = view_count + 1 WHERE tid=$tid");
		}

		// 根据fid和tid查表找帖子数据
		$data = mysqli_query($link, "SELECT * FROM `topics_${fid}_${tid_last_char}`  WHERE tid=${tid} LIMIT 1;");
		$data = $data->fetch_assoc();

		// 对data里的tags进行格式化
		$data['tags'] = format_tags_to_str($tid);

		// 获取帖子发布者uid对应的用户名，这里的uid_是作者uid
		$uid_ = $data['uid'];
		$uid_last_char_ = substr($uid_, -1);

		// 获取uid对应的用户数据
		$auther = mysqli_query($link, "SELECT * FROM users_data_${uid_last_char_} WHERE uid=$uid_ LIMIT 1");
		$auther = $auther->fetch_assoc();

		// 获取uid对应的用户名uname_
		$uname_ = mysqli_query($link, "SELECT uname FROM users_info_${uid_last_char_} WHERE uid=${uid_} LIMIT 1");
		$auther['uname'] = $uname_->fetch_assoc()['uname'];
	?>




	

	<!-- TITLE分割 -->
	<div class="title_split_line">
		<span>&emsp;<?php echo $data['title']; ?>&emsp;</span>
	</div>


	<div class="forum">
		<header>
			<!-- 右上角功能按钮 -->
			<button class="forum_bt button2" onclick="remove_topic()">删除帖子</button>
			<button class="forum_bt">提升帖子</button>
			<button class="forum_bt" onclick="window.location.href = './send_topic.php?mod=replace&tid=<?php echo $data['tid'];?>'">修改帖子</button>
			<!-- 帖子数据 -->
			<ul class="data">
				<li>浏览数: <?php echo $data['view_count']; ?></li>
				<li>回复数: <?php echo $data['reply_count']; ?></li>
				<li id="time_diff"></li>
				<li id="tags"></li>
			</ul>
		</header>
	</div>




	<div class="forum">
		<main class="board">
			<!-- 作者数据 -->
			<div class="auther_info">
				<span class="name">
					<?php 
						echo $auther['uname'];
					?>
				</span>
				<img class="avatar" src="./data/avatars/<?php echo get_avatar($auther['uid']); ?>" onclick="fullscreen_avatar()" loading="lazy" alt="图片加载失败">
				<span class="auther_sign"><?php echo $auther['sign']; ?></span>
				<ul class="auther_data">
					<li><img src="./data/imgs/pannya.png" loading="lazy" alt="图片加载失败"></li>
					<li>UID: <?php echo $auther['uid']; ?></li>
					<li>在线时间: ><?php echo $auther['online_time'] / 60 ?>小时</li>
					<li>身份: <?php echo $auther['identity']; ?></li>
					<li>学分: <?php echo $auther['credit']; ?>点</li>
					<li>等级: <?php echo $auther['academic_year']; ?></li>
					<li>奖学金: <?php echo $auther['schoolship']; ?>呜溜</li>
					<li>被处罚: <?php echo $auther['judment_count']; ?>次</li>
					<li>发帖数: <?php echo $auther['canned_count']; ?>罐</li>
					<li>注册时间: <?php echo $auther['register_time']; ?></li>
					<li>最后登录: <?php echo $auther['last_login_time']; ?></li>
				</ul>

				<!-- 分割线 -->
				<br><img class="split_line" src="
					<?php
						if (strlen($auther['sign_img']) > 0) {

							// 修改sign_img中的格式
							$auther['sign_img'] = str_replace("|", "/", $auther['sign_img']);
							echo "./data/forums/{$auther['sign_img']}.jpg"; 
							
						} else {
							echo "./data/imgs/yingmei_small.jpg";
						}
					?>
				" onclick="fullscreen(this)" loading="lazy" alt="图片加载失败">



				<!-- 作者喜欢的作品 -->
				<ul class="auther_loves_story">
					<li class="box">此生挚爱</li>
					<li>
						<?php
							// 不存在
							if (!$auther['best_love_story']) {
								echo '无';

							// 存在
							} else {
								echo $auther['best_love_story'];
							}
						?>
					</li>
					<li></li>
					<li class="box">正在推进</li>
					<li>
						<?php
							// 不存在
							if (!$auther['playing_story']) {
								echo '无';
							// 存在
							} else {
								echo $auther['playing_story'];
							}
						?>
					</li>
					<li></li>
					<li class="box">强烈推荐</li>
					<li>
						<div class="recommend_">
							<!-- 作品集由js添加 -->
						</div>
						<br>
					</li>
				</ul>
			</div>

			<!-- 帖子内容 -->
			<div class="content">
			</div>

			<div class="bottom">
				<input type="text" class="reply_content" placeholder="请输入你要回复的内容">
				<button class="reply" onclick="reply_topic()">回复</button>
			</div>
		</main>
	</div>




	<!-- 总回复浏览 -->
	<br>
	<div class="forum">
		<main class="board" id="replies"></main>
	</div>




	<!-- 未来开发瀑布流请求需要的DOM -->
	<div class="forums_loading">
		<button class="last_page">上一页</button>
		<span> 当前页数：<?php echo $_GET['page']; ?> </span>
		<button class="next_page" style="margin-left: 5px;">下一页</button>
		
		<!-- 自定义行数跳转 -->
		<br><br>
		<input type="text" id="page_number" placeholder="请输入你需要跳转的页数">
		<button onclick="goto_page()">跳转</button>
	</div>

	<!-- 底部顶行 -->
	<div>
		
		<br>
	</div>



	







<!-- 加载需要的js -->
<?php require_once dirname(__FILE__)."/js/view_topic.php"; ?>

<script>
	


	// 
	// 
	// 网页title变更
	// 
	// 
	var title = document.querySelector(".title_split_line span").textContent
	document.title = title

	



	// 
	// 
	// ？
	// 
	// 
	const create_post_card = (uid, uname, avatar, content) => {
		// 前端DOM元素显示
		document.querySelector("#target_rid_br").style.display = 'block'
		document.querySelector("#target_rid_card").style.display = 'block'

		// 用户名 rid 内容 头像 赋值到指定DOM
		document.querySelector("#target_rid_card .poster_info .name").textContent = `${uname}`
		document.querySelector("#target_rid_card .poster_info .rid").textContent = `rid:<?php echo $_GET['rid']; ?>`
		document.querySelector("#target_rid_card .post_content").innerHTML = `${content}`
		document.querySelector("#target_rid_card .poster_info .avatar").src = `./data/avatars/${avatar}`

		// 窗口下滑到特定元素
		document.querySelector("#target_rid_card").scrollIntoView({ behavior: 'smooth' })

		// 追加回复onclick更新
		document.querySelector("#target_rid_card .bottom_ button").setAttribute("onclick", `reply(<?php echo $_GET['rid']; ?>)`)

		// 回复框DOM id更新
		document.querySelector("#target_rid_card .bottom_ input").id = "rid_<?php echo $_GET['rid']; ?>"
	}


	// 
	// 
	//	URL中包含rid索引到指定回复数据
	// 
	// 
	<?php
		if ($_GET['rid']) {
			// 从URL中获取rid
			$rid = $_GET['rid'];

			// 获取rid对应的回复数据
			$rid_data = mysqli_query($link, "SELECT uid, content FROM replies_${tid_last_char} WHERE rid=$rid LIMIT 1");
			$rid_data = $rid_data->fetch_assoc();

			// 获取uid对应的用户名
			$uid_ = $rid_data['uid'];
			$uid_last_char = substr($uid_, -1);

			// 根据uid查找用户名
			$uname = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char}` WHERE uid={$uid_}");
			$uname = $uname->fetch_assoc()['uname'];

			// 判断头像存在
			if (file_exists("./data/avatars/{$uid_}.jpg")) {
				$avatar = "${uid_}_small.jpg";
			} else {
				// 取所有头像文件
				$imgs = scandir(dirname(__FILE__).'/data/avatars/random');

				// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
				unset($imgs[0]);
				unset($imgs[1]);
				unset($imgs[array_search('Thumbs.db', $imgs)]);

				// 随机取一个头像
				$avatar = 'random/' . $imgs[array_rand($imgs, 1)];
			}
			// 跳转到create_post_card
			echo "create_post_card($uid_, '$uname', '$avatar','{$rid_data['content']}')";
		}
	?>













	// 
	// 
	//	帖子作者头像全屏
	// 
	// 
	const fullscreen_avatar = () => {
		// 获取头像URL
		var avatar = document.querySelector(".auther_info .avatar")
		var url = avatar.src.replace("_small", "")
		console.log(url);
		avatar.src = url
		fullscreen(avatar)
	}



	// 
	// 
	//	帖内图片全屏
	// 
	// 
	function fullscreen(element) {
		// 全屏模式未启用
		if (!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
			// 不同浏览器的兼容性处理
			if (element.requestFullscreen) {
				element.requestFullscreen();
			} else if (element.mozRequestFullScreen) {
				element.mozRequestFullScreen();
			} else if (element.webkitRequestFullscreen) {
				element.webkitRequestFullscreen();
			} else if (element.msRequestFullscreen) {
				element.msRequestFullscreen();
			}
		} else {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
		}
	}











	// 
	// 
	// 上一页
	// 
	// 
	var last_page_button = document.querySelector('.forums_loading .last_page')
	last_page_button.addEventListener('click', function() {
		// 获取fid和page - 1
		var tid = "<?php echo $_GET['tid']; ?>"
		var target_page = "<?php echo $_GET['page']; ?>" - 1

		if (target_page < 0) {
			alert('无上一页')
		} else {

			// 从URL进行跳转
			window.location.href = `./view_topic.php?tid=${tid}&page=${target_page}`
		}
	})




	// 
	// 
	// 下一页
	// 
	// 
	var next_page_button = document.querySelector('.forums_loading .next_page')
	next_page_button.addEventListener('click', function() {
		// 获取fid和page + 1
		var tid = "<?php echo $_GET['tid']; ?>"
		var target_page = <?php echo $_GET['page']; ?> + 1

		// 从URL进行跳转
		window.location.href = `./view_topic.php?tid=${tid}&page=${target_page}`
	})



	// 
	// 
	// 指定页数跳转
	// 
	// 
	const goto_page = () => {
		// 获取需要跳转的page
		var tid = "<?php echo $_GET['tid']; ?>"
		var page = document.querySelector('#page_number').value
		
		// 未填入需要跳转的page
		if (!page) {
			alert('请输入你要跳转的网页')

		// 执行跳转
		} else {
			// 从URL进行跳转
			window.location.href = `./view_topic.php?tid=${tid}&page=${page}`
		}
	}















	// 
	// 
	//	回复区补充card
	// 
	// 
	<?php
		// 获取最新rid
		$newest_rid = mysqli_query($link, "SELECT rid FROM replies_$tid_last_char WHERE tid=$tid ORDER BY rid DESC LIMIT 1;");
		$newest_rid = $newest_rid->fetch_assoc()['rid'];

		// 获取最大请求值和最新请求值
		// 计算公式:
		// 		min = $page * 20
		// 		max = ($page + 1) *20
		$min = $_GET['page'] * 20;
		$max = ($_GET['page'] + 1) * 20;

		// 按目标max请求指定数量rid数据（最多请求20个
		$result = mysqli_query($link, "SELECT * FROM replies_$tid_last_char WHERE tid='{$tid}' ORDER BY rid DESC LIMIT $min, $max;");

		// 循环每个reply数据
		$n = 0;
		while ($row = $result->fetch_assoc()) {
			$replies[$n]['rid'] = $row['rid'];
			$replies[$n]['uid'] = $row['uid'];
			$replies[$n]['content'] = $row['content'];
			
			// 获取回复日期和reply_rid
			$replies[$n]['date'] = $row['date'];
			$replies[$n]['reply_rid'] = $row['reply_rid'];

			// 存在reply_rid
			if ($replies[$n]['reply_rid']) {
				
				// 获取reply_rid对应的uid和内容
				$reply_rid = $replies[$n]['reply_rid'];
				$reply_data = mysqli_query($link, "SELECT uid, content FROM replies_$tid_last_char WHERE rid=$reply_rid ;");
				$reply_data = $reply_data->fetch_assoc();

				// 获取uid对应的uname
				$replies[$n]['reply_uid'] = $reply_data['uid'];
				$replies[$n]['reply_uname'] = get_uname($reply_data['uid']);
				$replies[$n]['reply_content'] = $reply_data['content'];
			}
			
			// 判断uid有没有对应的头像
			if (file_exists("./data/avatars/{$replies[$n]['uid']}_small.jpg")) {
				$replies[$n]['avatar'] = "{$replies[$n]['uid']}_small.jpg";
			
			// 头像不存在
			} else {
				// 取所有头像文件
				$imgs = scandir(dirname(__FILE__).'/data/avatars/random');

				// 删去0键和1键，0为"."本级目录，1为".."上级目录，和缩略图缓存db
				unset($imgs[0]);
				unset($imgs[1]);
				unset($imgs[array_search('Thumbs.db', $imgs)]);

				// 随机取一个头像
				$replies[$n]['avatar'] = 'random/' . $imgs[array_rand($imgs, 1)];
			}


			// 根据uid查找用户名
			$uid_last_char__ = substr($replies[$n]['uid'], -1);
			$uname__ = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char__}` WHERE uid={$replies[$n]['uid']};");
			$uname__ = $uname__->fetch_assoc()['uname'];
			$replies[$n]['uname'] = $uname__;

			$n++;
		}
	?>

	// 存在回复信息
	if ("<?php echo $replies; ?>") {
		var replies_div = document.querySelector('#replies')
		var replies = <?php echo json_encode($replies); ?>
		
		// 循环每个回复
		for (n = 0; n < replies.length; n++) {
			// 回复别人的对象存在
			if (replies[n]['reply_uid']) {
				var add = `<a href="./space.php?uid=${replies[n]['reply_uid']}" target="_blank">@${replies[n]['reply_uname']}</a>：<a href="./view_topic.php?tid=<?php echo $_GET['tid']; ?>&page=0&rid=${replies[n]['reply_rid']}" target="_blank">${replies[n]['reply_content']}</a><br>`
			} else {
				var add = ''
			}

			var html = `
				<div class="post_card_">
					<div class="poster_info">
						<img class="avatar" src="./data/avatars/${replies[n]['avatar']}" alt="图片加载失败" loading="lazy">
						<span class="name">${replies[n]['uname']}</span>
						<span class="rid">${replies[n]['date']} || rid:${replies[n]['rid']}</span>
					</div>
					<div class="post_content">
						${add}${replies[n]['content']}
					</div>
					<div class="bottom_">
						<input type="text" id="rid_${replies[n]['rid']}" class="reply_content" placeholder="请输入需要回复的内容">
						<button onclick="reply_topic(${replies[n]['rid']})">参与回复</button>
						<button class="button2" onclick="remove_reply(${replies[n]['rid']})">风纪执行</button>
					</div>
				</div>
				<br>
			`
			replies_div.insertAdjacentHTML("beforeend", html)
		}
	}











	













	// 
	// 
	//	时间差计算
	// 
	// 
	// 获取当前时间
	var current_time = new Date()

	// 获取给定时间字符串的时间
	var send_time = new Date('<?php echo $data['date']; ?>')

	// 计算时间差（单位为毫秒）
	var time_diff = send_time.getTime() - current_time.getTime()

	// 将时间差转换为天数
	var time_diff = Math.ceil(time_diff / (1000 * 60 * 60 * 24))

	// 取绝对值
	var time_diff = Math.abs(time_diff)

	// DOM回显
	document.querySelector('#time_diff').textContent = `发布于: <?php echo $data['date']; ?>（${time_diff}天前）`
















	// 
	// 
	//	作品集计算
	// 
	// 
	// 获取DOM元素
	var recommends_div = document.querySelector('.forum main .auther_info .auther_loves_story .recommend_')

	// 从php中获取作品集
	var recommends = '<?php echo $auther['recommend_stories']; ?>';
	
	// 作品集不存在
	if (!recommends) {
		recommends_div.insertAdjacentHTML("beforeend", `<span>无</span>`)
		
	// 作品集存在
	} else {

		// 拆分作品集
		var recommends = recommends.split('|')

		// 循环添加作品名字
		for (n = 0; n < recommends.length; n++) {
			recommends_div.insertAdjacentHTML("beforeend", `<span>${recommends[n]}</span>`)
		}
	}












	// 
	// 
	//  帖子内容标签解析
	// 
	//
	var html = `<?php echo $data['content']; ?>`
	
	// 判断fid
	var fid = `<?php echo $fid; ?>`

	// 生成防剧透class，匹配[_d *]的正则表达式
	var regex = /\[_d\s+([^\[\]]+)\]/g

	// 生成div结构包被{_i23}
	var html = html.replace(regex, function(match, p1) {
		return '<div class="defense_img">' + p1 + '</div>';
	})

	// 
	// 对图片code进行修饰，匹配 {_i*} 的正则表达式
	// 
	var regex = /{(_i(\d+))}/g

	// 替换匹配的内容
	var html = html.replace(regex, `<img src="./data/forums/<?php echo $data['tid']; ?>/$2.jpg" id="_$2" onclick="fullscreen(this)" loading="lazy">`)

	// 
	// 对视频code进行修饰，匹配 {_v*} 的正则表达式
	// 
	var regex = /{(_v(\d+))}/g

	// 替换匹配的内容
	var	html_code = `
		<div class="player_window">
			<video class="player" src="./data/forums/<?php echo $data['tid']; ?>/$2.mp4" preload="auto" allow="autoplay; encrypted-media" allowfullscreen="true" controls controlsList="nodownload"></video>
		</div>
	`
	var html = html.replace(regex, html_code)

	// 
	// 对跳转{_gototid}进行修饰，匹配 {_goto*} 的正则表达式
	// 
	var regex = /{(_goto(\d+))}/g
	var html = html.replace(regex, `<a href="./view_topic.php?tid=$2&page=0" target="_blank">跳转</a>`)

	// 
	// 对{?pre *?}进行修饰，匹配 {?pre *?} 的正则表达式
	// 

	// 匹配 {?pre ... ?} 中的内容并替换为 <pre>...</pre>
	var regex = /{\?pre\s*([\s\S]*?)\s*\?}/g;
	var html = html.replace(regex, "<br><pre>$1</pre><br>");

	// 添加帖子内容
	document.querySelector('.forum main .content').innerHTML = html







</script>



















<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>