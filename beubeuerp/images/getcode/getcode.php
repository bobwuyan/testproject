<?php
   /*   网站验证码程序
    *   运行环境： PHP5.0.18 下调试通过
    *   需要 gd2 图形库支持（PHP.INI中 php_gd2.dll开启）
    *   文件名: showimg.php
    *   作者：  17php.com
    *   Date:   2007.03
    *   技术支持： www.17php.com
    */

   //随机生成一个4位数的数字验证码
    $num="";
    for($i=0;$i<4;$i++){
    $num .= rand(0,9);
    }
   //4位验证码也可以用rand(1000,9999)直接生成
   //将生成的验证码写入session，备验证页面使用
    Session_start();
    $_SESSION["Checknum"] = $num;
   //创建图片，定义颜色值
    Header("Content-type: image/PNG");
    srand((double)microtime()*1000000);
    $im = imagecreate(78,50);
    $black = ImageColorAllocate($im, 0,0,0);
    $gray = ImageColorAllocate($im, 217,217,217);
    imagefill($im,0,0,$gray);

    //随机绘制两条虚线，起干扰作用
    $style = array($black, $black, $black, $black, $black, $gray, $gray, $gray, $gray, $gray);
    imagesetstyle($im, $style);
    $y1=rand(0,50);
    $y2=rand(0,50);
    $y3=rand(0,50);
    $y4=rand(0,50);
    imageline($im, 0, $y1, 78, $y3, IMG_COLOR_STYLED);
    imageline($im, 0, $y2, 78, $y4, IMG_COLOR_STYLED);
	imageline($im, 0, $y3, 78, $y2, IMG_COLOR_STYLED);
	imageline($im, 0, $y4, 78, $y1, IMG_COLOR_STYLED);
    //在画布上随机生成大量黑点，起干扰作用;
    for($i=0;$i<50;$i++)
    {
		$black = ImageColorAllocate($im, rand(0,255),rand(0,255),rand(0,255));
		imagesetpixel($im, rand(0,78), rand(0,50), $black);
    }
	
	$font_color_arr=array(array(0,0,253),array(0,0,0),array(102,0,0),array(102,0,102),array(255,20,147),array(255,0,0),array(238,0,238),array(148,0,211),array(160,82,45),array(34, 139, 34),array(65, 105, 225),array(77, 77, 77),array(105, 139, 34),array(139, 62, 47),array(139, 105, 105),array(141, 182, 205),array(143, 188, 143),array(205, 133, 0));
    //将四个数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
    $strx=rand(3,8);
    for($i=0;$i<4;$i++){
    $strpos=rand(1,25);
	$color_i=rand(0,17);
	$black = ImageColorAllocate($im, $font_color_arr[$color_i][0],$font_color_arr[$color_i][1],$font_color_arr[$color_i][2]);
    imagestring($im,5,$strx,$strpos, substr($num,$i,1), $black);
    $strx+=rand(8,30);
    }
    ImagePNG($im);
    ImageDestroy($im);