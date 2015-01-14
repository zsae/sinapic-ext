<?php
include dirname(__FILE__) . '/config.php';
include dirname(__FILE__) . '/inc/saetv2.ex.class.php';

spe::init();
class spe{
	public static $cookie_expire = 604800;//3600*24*7 7days
	public static $allow_types = array('png','gif','jpg','jpeg');
	public static function init(){

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
		
		switch($action){
			//check-auth
			case 'check-auth':
				self::action_check_auth();
				break;
			//upload
			case 'upload':
				self::action_upload();
				break;
			//authorize
			case 'set-auth':
				self::action_set_auth();
				break;
			case 'donate':
				self::action_donation();
				break;
			default:
				die();
		}
	}
	private static function action_donation(){
		$item_title = mb_convert_encoding('Donate for SinaPic-Ext, thank you!','GBK','UTF-8');
		$email = 'kmvan.com@gmail.com';
		$price = 99;
		?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Redirecting...</title>
	<style>
	#fm{
		display: none;
	}
	</style>
	<script>
	window.onload = function(){
		document.getElementById('fm').submit();
	}
	</script>
</head>
<body>
	<h1>Redirecting...</h1>
	<form id="fm" action="https://shenghuo.alipay.com/send/payment/fill.htm" method="post">
		<input type="hidden" name="optEmail" value="<?php echo $email;?>">
		<input type="hidden" name="payAmount" value="<?php echo (int)$price;?>">
		<input type="hidden" name="title" value="<?php echo $item_title;?>">
		<input type="submit">
	</form>
</body>
</html>
		<?php
	}
	private static function action_set_auth(){
		$token = isset($_GET['access_token']) ? $_GET['access_token'] : null;
		if(empty($token)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_access_token';
			$output['msg'] = 'Sorry, access token is invaild';
			die(json_encode($output));
		}
		$expire = isset($_GET['expires_in']) ? $_GET['expires_in'] : null;
		if(empty($expire)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_expires_in';
			$output['msg'] = 'Sorry, expire time is invaild';
			die(json_encode($output));
		}
		self::set_token($token,time() + $expire);
		die('
		<!doctype html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title>Authorize successfully</title>
			<style>
			body{color:green;}
			</style>
		</head>
		<body>
			<h1>Congratulation! SinaPic-Ext has been authorized!</h1>
			<p><a href="javascript:window.open(false,\'_self\',false);window.close();">Close this window and reload it.</a></p>
		</body>
		</html>
		');
		
	}
	private static function action_check_auth(){
		if(!self::get_token_form_cookie()){
			$output['status'] = 'error';
			$output['code'] = 'no_auth';
			$output['msg'] = sprintf('Your are NOT authorize me yet, please click %s to athorize and %s.','<a href="' . self::get_cb_url() . '" target="_blank" class="alert-link">here</a>','<a href="javascript:location.reload();" class="alert-link">reload me</a>');
		}else{
			$output['status'] = 'success';
			$output['msg'] = 'You authorized me, have fun!';
		}
		die(json_encode($output));
	}
	private static function action_upload(){
		$file = isset($_FILES['file']) ? $_FILES['file'] : array();
		// var_dump($file);exit;
		$file_name = isset($file['name']) ? $file['name'] : null;
		$file_type = isset($file['type']) ? explode('/',$file['type']) : array(); /** fuck you php 5.3 */
		$file_type = !empty($file_type) ? $file_type[1] : null;
		$tmp_name = isset($file['tmp_name']) ? $file['tmp_name'] : null;
		/** 
		 * check upload error
		 */
		if(!isset($file['error']) || $file['error'] != 0){
			$output['status'] = 'error';
			$output['msg'] = sprintf('Upload failed, file has an error code: %s',$file['code']);
			$output['code'] = 'file_has_error_code';
			die(json_encode($output));
		}
		/** 
		 * check file params
		 */
		if(!$file_name || !$file_type || !$tmp_name){
			$output['status'] = 'error';
			$output['msg'] = 'Not enough params.';
			$output['code'] = 'not_enough_params';
			die(json_encode($output));
		}
		/** 
		 * check file type
		 */
		if(!in_array($file_type,self::$allow_types)){
			$output['status'] = 'error';
			$output['msg'] = 'Invalid file type.';
			$output['code'] = 'invalid_file_type';
			die(json_encode($output));
		}
		/** 
		 * check authorization
		 */
		if(!self::get_token_form_cookie()){
			$output['status'] = 'error';
			$output['code'] = 'no_authorize';
			$output['msg'] = 'Please use your Sina Weibo account to authorize the plugin.';
			die(json_encode($output));
		}
		include dirname(__FILE__) . '/inc/saetv2.ex.class.php';

		$c = new SaeTClientV2(AKEY,SKEY,self::get_token_form_cookie());
		$callback = $c->upload(date('Y-m-d H:i:s ' . rand(100,999)) . ' Upload by SinapicExt',$tmp_name);
		
		unlink($tmp_name);

		/** 
		 * get callback
		 */
		if(is_array($callback) && isset($callback['bmiddle_pic'])){
			$output['status'] = 'success';
			$output['img_url'] = str_ireplace('http://','https://',$callback['bmiddle_pic']);
			/** 
			 * destroy after upload 
			 */
			sleep(1);
			$c->destroy($callback['id']);
		/** 
		 * got callback error code
		 */
		}else if(is_array($callback) && isset($callback['error_code'])){
			$output['status'] = 'error';
			$output['msg'] = $callback['error'];
		/** 
		 * unknown error
		 */
		}else{
			ob_start();
			var_dump($callback);
			$detail = ob_get_contents();
			ob_end_clean();
			
			$output['status'] = 'error';
			$output['code'] = 'unknown';
			$output['detail'] = $detail;
			$output['msg'] = sprintf('Sorry, upload failed. Please try again later or contact the plugin author. The reasons for this situation maybe the Weibo server does not receive the file from your server. Weibo returns an error message: %s',json_encode($callback));
		}
		die(json_encode($output));
	}
	private static function get_cb_url(){
		$uri = HOME_URL . '/action.php?action=set-auth';
		return 'http://api.inn-studio.com/sinapicv2/?action=get_authorize&amp;uri=' . urlencode($uri);
	}
	private static function set_token($token,$expire = 604800){
		setcookie('spe-token',base64_encode($token),$expire);
	}
	private static function get_token_form_cookie(){
		return isset($_COOKIE['spe-token']) ? base64_decode($_COOKIE['spe-token']) : null;
	}
	private function get_client_ip(){
		return preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	}
}
