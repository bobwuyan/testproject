<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);



error_reporting(0);
$oldname=$_SERVER["REQUEST_URI"];
$oldname=substr($oldname,1);
$array=explode("?", $oldname);
$oldname=$array[0];
//  /0ca0b92f26e8050aac2b54d00f96259fa.jpg.80x80.jpg
$showarray=array('990x1760','768x1558','768x1500','638x850','600x800','480x640','400x533','350x240','338x600','300x615','240x360','240x320','220x392','201x134','187x250','164x336','130x232','130x130','110x150','106x190','84x150','80x80','70x124','71x94','67x120','50x90','14x14','10x10','170x170');
		
//如果有三个点表示是准备裁剪文件名
if(substr_count($oldname,'.')>=3)
{

	$pos1 = strpos($oldname, '.');
	$pos2 = strpos($oldname, '.',$pos1+1);
	$pos3 = strpos($oldname, '.',$pos2+1);
	$pos4 = strpos($oldname, '.',$pos3+1);
	$pos5 = strpos($oldname, '.',$pos4+1);
	$posx = strpos($oldname, 'x',$pos2+1);
	if(substr_count($oldname, 'lz.png')>0 || substr_count($oldname, 'hb.png')>0 || substr_count($oldname, 'kb.png')>0 || substr_count($oldname, 'hat.png')>0 || substr_count($oldname, 'mask.png')>0 || substr_count($oldname, 'square.jpg')>0)
	{
		$posx = strpos($oldname, 'x',$pos4+1);
		$sourcename=substr($oldname,0,$pos4);//源文件名
		$desttype=substr($oldname,$pos5+1);//新文件类型
		$destwidth=substr($oldname,$pos4+1,$posx-$pos4-1);//目标宽
		$destheight=substr($oldname,$posx+1,$pos5-$posx-1);//目标高
	}
	else if(substr_count($oldname, '.png.png')>0)//不带模特的搭配图
	{
		$posx = strpos($oldname, 'x',$pos3+1);//从第3个点的位置开始找X的位置
		$sourcename=substr($oldname,0,$pos3);//源文件名
		$desttype=substr($oldname,$pos4+1);//新文件类型
		$destwidth=substr($oldname,$pos3+1,$posx-$pos3-1);//目标宽
		$destheight=substr($oldname,$posx+1,$pos4-$posx-1);//目标高
	}
	else{
		$sourcename=substr($oldname,0,$pos2);//源文件名
		$sourcetype=substr($oldname,$pos1+1,$pos2-$pos1-1);//源文件类型
		$desttype=substr($oldname,$pos3+1);//新文件类型
		$destwidth=substr($oldname,$pos2+1,$posx-$pos2-1);//目标宽
		$destheight=substr($oldname,$posx+1,$pos3-$posx-1);//目标高
	}
	$size="$destwidth"."x"."$destheight";
		
	if (!in_array($size,$showarray))//不在规定的尺寸里，不生成
		{
			$res="http://".$_SERVER['HTTP_HOST']."/".$sourcename;
			if(substr_count($res,'modelimg')>0 || substr_count($res,'modelheadimg')>0 || substr_count($res,'cls')>0 || substr_count($res,'modelmaskimg')>0 || substr_count($res,'colorimage')>0)
			{
					$image_content = file_get_contents($res); 
					$image = imagecreatefromstring($image_content); 
					$width = imagesx($image); 
					$height = imagesy($image); 
					$width2b=$width/2;
					$height2b=$height/2;
					$num=20;
					if($destwidth< $width2b-$num || $destwidth>$width2b+$num)
					{
						exit();	
					}
					if($destheight< $height2b-$num || $destheight>$height2b+$num)
					{
						exit();	
					}
				}else
				{
					exit();
				}
		}
	/*
	echo $oldname."\r\n";
	echo $pos1."\r\n";
	echo $pos2."\r\n";
	echo $pos3."\r\n";
	echo $sourcename."\r\n";
	echo $sourcetype."\r\n";
	echo $destwidth."\r\n";
	echo $destheight."\r\n";
	echo $desttype."\r\n";
	exit();*/
	
	$canvas = new gmagick(getcwd()."\\". $sourcename);
	$canvas->setImageFormat ( $desttype );
	//$canvas->setImageCompressionQuality ( 95 );	
	$canvas->resizeimage ($destwidth,$destheight,gmagick::FILTER_LANCZOS,1,true);//裁剪最清楚
	//$canvas->thumbnailImage ($destwidth,$destheight,true);//最小，比较模糊
	//$canvas->scaleimage ($destwidth,$destheight,true);			
	$canvas->writeImage ( getcwd()."\\".$oldname );
	$canvas->clear ();
	$canvas->destroy();
	header('Location: '.$_SERVER["REQUEST_URI"]."?");exit();
	header("Content-type: image/JPEG",TRUE);

	$fp=fopen(getcwd()."\\".$oldname,"rb");
	$content = fread($fp,filesize(getcwd()."\\".$oldname));
	fclose($fp);
	echo $content;

}

Yii::createWebApplication($config)->run();