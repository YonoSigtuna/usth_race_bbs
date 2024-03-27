<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>头像审核</title>
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





	

	<!-- TITLE分割 -->
	<div class="title_split_line" style="width: 35%;">
		<span>头像审核</span>
	</div>
 




	
	<?php
		// 获取最新头像信息
		$data = mysqli_query($link, "SELECT uid, file_size FROM check_avatars LIMIT 1");
		$data = $data->fetch_assoc();
		// ['uid']
		// ['file_size']
		$file_size = $data['file_size'] / 1024;

		if (!$data) {
			exit('当前没有需要审核的头像');
		}


		// 根据uid查找uname
		$uid_last_char = substr($data['uid'], -1);
		$uname_ = mysqli_query($link, "SELECT uname FROM users_info_${uid_last_char} WHERE uid={$data['uid']} LIMIT 1;");
		$uname_ = $uname_->fetch_assoc()['uname'];
	?>



	<img class="avatar_big" src="./data/_checking/avatars/<?php echo $data['uid']; ?>.jpg" alt="头像加载失败"><br><br><br>
	<div class="board" id="checking_topic_content">上传UID：<?php echo $data['uid']; ?>（<?php echo $uname_; ?>），文件大小<?php echo $file_size; ?>KB</div>

	<div style="float: right;">
		<button onclick="allow_avatar()">通过并审核下一个</button>
		<button class="button2" onclick="refuse_avatar()">拒绝并审核下一个</button>
	</div>





<script>
	// 头像审核拒绝
	const refuse_avatar = () => {
		// 通过xhr提交信息
		var data = new FormData()
		data.append("cmd", "refuse_avatar")
		data.append("uid", <?php echo $data['uid']; ?>)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				try {
					window.location.href = './check_avatar.php'
				} catch (error) {
					console.error('xhr请求失败，失败code：' + error)
				}
			}
		}

		// xhr请求超时
		xhr.timeout = 5000	// ms
		xhr.ontimeout = () => alert("请求超时")
	}


	// 头像审核通过
	const allow_avatar = () => {
		// 通过xhr提交信息
		var data = new FormData()
		data.append("cmd", "allow_avatar")
		data.append("uid", <?php echo $data['uid']; ?>)

		// 发送xhr
		var xhr = new XMLHttpRequest()
		xhr.open("POST", "./server.php", true)
		xhr.send(data)

		// xhr处理
		xhr.onreadystatechange = () => {
			if(xhr.readyState == 4 && xhr.status == 200){
				try {
					window.location.href = './check_avatar.php'
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