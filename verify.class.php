<?php
/**
*author : auto15
*email : wyPHP520@163.com
*build :2017.3.30
* 
*/
class Verify
{
	//字体文件
	private $_fontfile = '';
	//画布宽度
	private $_width = 120;
	//画布高度
	private $_height = 40;
	//字体大小
	private $_fontsize = 20;
	//验证码长度
	private $_length = 4;
	//画布资源
	private $_image = null;
	//干扰元素
	private $_pixel =true;
	//干扰线段
	private $_line = 3;
	
	/**
	 * 初始化配置数据
	 * @param array $config 
	 */
	function __construct($config = array())
	{
		if (is_array($config) && count($config)>0) {
			//判断配置文件里的fontfile是否存在，是否是文件，是否可读
			if (isset($config['fontfile']) && is_file($config['fontfile']) && is_readable($config['fontfile'])) {
				$this->_fontfile = $config['fontfile'];
			}else{
				return false;
			}
			//判断是否设置了宽 .高
			if (isset($config['width']) && $config['width']>0) {
				$this->_width = (int)$config['width'];
			}
			if (isset($config['height']) && $config['height']>0) {
				$this->_width = (int)$config['height'];
			}
			if (isset($config['fontsize']) && $config['fontsize']>0) {
				$this->_fontsize = (int)$config['fontsize'];
			}
			if (isset($config['length']) && $config['length']>0) {
				$this->_length = (int)$config['length'];
			}
			if (isset($config['pixel']) && $config['pixel']>0) {
				$this->_pixel = (int)$config['pixel'];
			}
			if (isset($config['line']) && $config['line']>0) {
				$this->_line = (int)$config['line'];
			}

			$this->_image = imagecreatetruecolor($this->_width, $this->_height);
			return $this->_image;
		}else{
			return false;
		}
	}

	/**
	 * 生成验证码
	 * @param  string $value 
	 * @return 
	 */
	public function getVerify()
	{
		$white = imagecolorallocate($this->_image, 255, 255, 255);
		//填充画布
		imagefill($this->_image, 0, 0, $white);
		//生成验证码内容
		$str = $this->_getStr($this->_length);
		if (false===$str) {
			return false;
		}
		//把验证码信息写入session
		session_start();
		$_SESSION['verify'] = md5(strtolower($str));
		//绘制验证码
		$fontsize = $this->_fontsize;
		$fontfile = $this->_fontfile;
		for ($i=0; $i < $this->_length; $i++) { 
			$angle = mt_rand(-30,30);
			$x = ceil($this->_width/$this->_length)*$i + mt_rand(5,10);
			$y = $fontsize + mt_rand(10, 20);
			$color = imagecolorallocate($this->_image,mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
			$text = substr($str,$i,1);
			imagettftext($this->_image, $fontsize, $angle, $x, $y, $color, $fontfile, $text);
		}
		//增加干扰元素
		if ($this->_pixel) {
			$this->getPixel();
		}
		if($this->_line){
			$this->getLine();
		}
		//输出图像
		header('content-type:image/png');
		imagepng($this->_image);
		imagedestroy($this->_image);
		return strtolower($str);
	}

	/**
	 * 产生一个随机字符串
	 * @param  integer $length [验证码长度]
	 * @return [string]          [返回一个随机字符]
	 */
	private function _getStr($length = 4)
	{
		if ($length<1 || $length>30) {
			return false;
		}
		$chars = array(
			'a','b','c','d','e','f','g','h','k','m','n','p','x','y','z',
			'A','B','C','D','E','F','G','H','K','M','N','P','X','Y','Z',
			2,3,4,5,6,7,8,9
			);
		$str = implode('', array_rand(array_flip($chars),$length));
		return $str;
	}

	/**
	 * 绘制干扰小字
	 * @return 
	 */
	private function getPixel()
	{
		for ($i=0; $i < 10; $i++) { 
			$fontSize = 5;
			//外层循环每次取出一位数
			$noiseText = substr('abcdefghjkmnpqrstuvwxy',$i,1);
			$noiseColor = imagecolorallocate($this->_image,mt_rand(150,230),mt_rand(150,230),mt_rand(150,230));
			for ($j=0; $j < 5; $j++) { 
				imagettftext($this->_image, $fontSize, mt_rand(-40,40),rand(0,$this->_width-1),rand(0,$this->_height-1), $noiseColor, $this->_fontfile, $noiseText);
			}
		}
		
		
	}

	/**
	 * 绘制干扰线段
	 * @return
	 */
	private function getLine()
	{
		for ($i=0; $i < $this->_line ; $i++) { 
			$linecolor = imagecolorallocate($this->_image,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imageline($this->_image,rand(1,$this->_width),rand(1,$this->_height),rand(1,$this->_width-1),rand(1,$this->_height-1),$linecolor);
		}
		
	}
}

?>
