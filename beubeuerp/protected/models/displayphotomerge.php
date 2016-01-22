<?php
class  displayphotomerge
{
	public $im;//imagick对象。
	//缓存目录/mergecache/
	protected $mergecache;
	protected $hair=null;
	//构造函数
	function __construct()
	{
		$this->im=new Gmagick();
		$this->mergecache=$_SERVER['DOCUMENT_ROOT']."/mergecache/";
		//创建目录
		if(!is_dir($this->mergecache))
			mkdir($this->mergecache,0777,true);
	}
	//析构函数
	function __destruct()
	{	
		$this->im->clear ();	
		$this->im->destroy (); //释放资源
	}
	function close()
	{
		$this->im->clear ();
		$this->im->destroy (); //释放资源
	}
	
	//载入默认内容
	function loaddefault($path)
	{
		$this->im=new Gmagick($path);
	}
	//设置新尺寸
	function newimage($width,$height,$bgcolor)
	{
		$background = new GmagickPixel($bgcolor); // Transparent
		$this->im->newImage($width, $height, '#ffffff');
	}
	//添加内容
	function setcontent($path,$x,$y,$width,$height,$bestfit=true)
	{
		$myimage = new Gmagick ($path);
		$myimage->thumbnailImage ($width,$height,$bestfit);
		$this->im->compositeImage ( $myimage, gmagick::COMPOSITE_OVER,$x, $y ); //图片与画布合成
		$myimage->clear ();	
		$myimage->destroy (); //释放资源
	}
	//添加内容,直接传文件内容过来
	function setcontentsource($source,$x,$y,$width,$height,$bestfit=true)
	{	
		$myimage = new Gmagick ();
		$myimage->readimageblob($source);
		$myimage->thumbnailImage ($width,$height,$bestfit);
		$this->im->compositeImage ( $myimage, gmagick::COMPOSITE_OVER, $x, $y ); //图片与画布合成
		$myimage->clear ();	
		$myimage->destroy (); //释放资源
	}
	//输出
	function write($path,$type,$width=null,$height=null)
	{
		if($width!=null)
		{
			//缩小图片
			$this->im->thumbnailImage ( $width, $height, true );
		}
		$this->im->setImageFormat ( $type );
		//$this->im->setImageCompressionQuality ( 90 );	
		$this->im->writeImage ( $path ); //图片写入路径
	}
	
	//从远程读文件,如果缓存有从缓存读
	function getcacheimg($url)
	{		
		$urlmd5=$this->mergecache.md5($url);
		$temp=false;
		//如果存在 ,就判断缓存
		if(file_exists($urlmd5))
		{
			if((time()-filemtime($urlmd5))>7200)//缓存超过24小时不用
			{
				$temp=false;
			}
			else {
				$temp=true;
			}
		}
		
		if ($temp == false) {
			$temp=@file_get_contents($url);
			@file_put_contents($urlmd5,$temp);
		}
		else
		{
			$temp=@file_get_contents($urlmd5);
		}
		return $temp;
	}
}