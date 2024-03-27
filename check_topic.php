<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>帖子审核</title>
	<!-- css -->
	<link rel="stylesheet" href="./css/header.css">
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/home_page.css">

	<style>
		/* 清除内外边距 */
		* {
			margin: 0;
			padding: 0;
		}
	</style>

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
		// 获取最新需要审核的cid
		$result = mysqli_query($link, "SELECT title, content, uid, fid, cid FROM check_topics LIMIT 1");

		// 如果不需要审核返回前端
		if (!$result) {
			exit('当前不存在需要审核的帖子');
		}

		// 切片
		$result = $result->fetch_assoc();
		// ['title']
		// ['content']
		// ['uid']
		// ['fid']
		// ['cid']
	?>


	

	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>标题审核</span>
	</div>
	<div class="board">
		<?php echo $result['title']; ?>
	</div>



	<div class="title_split_line" style="width: 35%;">
		<span>内容审核</span>
	</div>
	<div class="board">
		<?php echo $result['content']; ?>
	</div>



	<div class="title_split_line" style="width: 35%;">
		<span>发帖用户</span>
	</div>
	<div class="board">
		<?php 
			if ($result) {
				// 获取uid最后一位查表
				$uid_last_char_ = substr($result['uid'], -1);

				// 查表uname
				$uname_ = mysqli_query($link, "SELECT uname FROM `users_info_{$uid_last_char_}` WHERE uid={$result['uid']}");
				$uname_ = $uname_->fetch_assoc()['uname'];

				// 整合数据回显
				echo "UID：{$result['uid']}（${uname_}）";
			}
		?>
	</div>



	<div class="title_split_line" style="width: 35%;">
		<span>目标fid审核</span>
	</div>
	<div class="board">
		<?php echo $result['fid']; ?>
	</div>



	<div class="title_split_line" style="width: 35%;">
		<span>cid审核</span>
	</div>
	<div class="board">
		<?php echo $result['cid']; ?>
	</div>




	<div style="float: right;">
		<button onclick="allow_topic()">通过并审核下一个</button>
		<button class="button2" onclick="refuse_topic()">拒绝并审核下一个</button>
	</div>


	<script>
		// 
		// 
		//	审核通过
		// 
		// 
		const allow_topic = () => {
			// 构建xhr请求
			var data = new FormData()
			data.append("cmd", "allow_topic")
			data.append("cid", "<?php echo $result['cid']; ?>")

			// 发送xhr
			var xhr = new XMLHttpRequest()
			xhr.open("POST", './server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					try {
						// 自动跳转回主页
						window.location.href = `./check_topic.php`
					} catch (error) {
						console.error('xhr请求失败，失败code：' + error)
					}
				}
			}

			// xhr请求超时
			xhr.timeout = 5000	// ms
			xhr.ontimeout = () => alert("请求超时")
		}




		// 
		// 
		//	审核拒绝
		// 
		// 
		const refuse_topic = () => {
			// 构建xhr请求
			var data = new FormData()
			data.append("cmd", "refuse_topic")
			data.append("cid", "<?php echo $result['cid']; ?>")

			// 发送xhr
			var xhr = new XMLHttpRequest()
			xhr.open("POST", './server.php', true)
			xhr.send(data)

			// xhr处理
			xhr.onreadystatechange = () => {
				if(xhr.readyState == 4 && xhr.status == 200){
					try {
						// 自动跳转回主页
						window.location.href = `./check_topic.php`
					} catch (error) {
						console.error('xhr请求失败，失败code：' + error)
					}
				}
			}

			// xhr请求超时
			xhr.timeout = 5000	// ms
			xhr.ontimeout = () => alert("请求超时")
		}
	</script>































<!-- 引入底部模块 -->
<?php require_once dirname(__FILE__).'/footer.php'; ?>







<!-- 关闭数据库 -->
<?php mysqli_close($link); ?>