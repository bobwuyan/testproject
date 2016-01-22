<?php
/**
 * 触摸屏类
 */
class PhpPost{
	
	
	public static function curl_post($url, $post,$Authorization='') {
        $header = array ();
        $header [] = 'Content-Type:application/x-www-form-urlencoded';
        if(!empty($Authorization))
        {
        	$header [] ='Authorization:'.$Authorization;
        }
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER    => $header,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST=>false
           // CURLOPT_HTTPHEADER    => array('Expect:')
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
	
	
} 