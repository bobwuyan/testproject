<?php
   /*   ��վ��֤�����
    *   ���л����� PHP5.0.18 �µ���ͨ��
    *   ��Ҫ gd2 ͼ�ο�֧�֣�PHP.INI�� php_gd2.dll������
    *   �ļ���: showimg.php
    *   ���ߣ�  17php.com
    *   Date:   2007.03
    *   ����֧�֣� www.17php.com
    */

   //�������һ��4λ����������֤��
    $num="";
    for($i=0;$i<4;$i++){
    $num .= rand(0,9);
    }
   //4λ��֤��Ҳ������rand(1000,9999)ֱ������
   //�����ɵ���֤��д��session������֤ҳ��ʹ��
    Session_start();
    $_SESSION["Checknum"] = $num;
   //����ͼƬ��������ɫֵ
    Header("Content-type: image/PNG");
    srand((double)microtime()*1000000);
    $im = imagecreate(78,50);
    $black = ImageColorAllocate($im, 0,0,0);
    $gray = ImageColorAllocate($im, 217,217,217);
    imagefill($im,0,0,$gray);

    //��������������ߣ����������
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
    //�ڻ�����������ɴ����ڵ㣬���������;
    for($i=0;$i<50;$i++)
    {
		$black = ImageColorAllocate($im, rand(0,255),rand(0,255),rand(0,255));
		imagesetpixel($im, rand(0,78), rand(0,50), $black);
    }
	
	$font_color_arr=array(array(0,0,253),array(0,0,0),array(102,0,0),array(102,0,102),array(255,20,147),array(255,0,0),array(238,0,238),array(148,0,211),array(160,82,45),array(34, 139, 34),array(65, 105, 225),array(77, 77, 77),array(105, 139, 34),array(139, 62, 47),array(139, 105, 105),array(141, 182, 205),array(143, 188, 143),array(205, 133, 0));
    //���ĸ����������ʾ�ڻ�����,�ַ���ˮƽ����λ�ö���һ��������Χ�������
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