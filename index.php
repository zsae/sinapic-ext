<?php include dirname(__FILE__) . '/config.php';?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>SinaPic-Ext (屌图床-扩展版)</title>
	<link rel="stylesheet" href="static/css/style.css?v=<?php echo VERSION;?>"/>
	<script src="http://ajax.aspnetcdn.com/ajax/jquery/jquery-2.1.1.min.js"></script>
	<script src="static/js/init.js?v=<?php echo VERSION;?>"></script>
</head>
<body>
	<form action="javascript:void(0);" id="spe-fm" role="form">
		<span id="spe-upload-btn" class="btn btn-link">Select or Drag image into here</span>
		<input type="file" id="spe-file" multiple>
	</form>
	<div id="progress">
		<div id="upload-tip" class="page-tip"><div class="alert alert-info" role="alert">Loading, please wait...</div></div>
		<div id="progress-bar"></div>
	</div>
	<div id="files-container"></div>
	
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-21518172-13', 'auto');
ga('send', 'pageview');
</script>
</body>
</html>