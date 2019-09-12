<?php !defined('YOUNG_TEAM') AND exit('Access Denied!');

class Lib_Yunpian_Sms
{
    public function sock_post($url,$query)
    {
        $data = "";
        $info=parse_url($url);
        $fp=fsockopen($info["host"],80,$errno,$errstr,30);
        if(!$fp){
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp,$head);
        $header = "";
        while ($str = trim(fgets($fp,4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp,4096);
        }

        return $data;
    }


   /**
    * 普通接口发短信
    * apikey 为云片分配的apikey
    * text 为短信内容
    * mobile 为接受短信的手机号
    */
    public function send($mobile, $msg)
    {
        try
        {
            $numberProto   = Loader_Phone::factory()->parse($mobile, 'CN');

            $isValidNumber = Loader_Phone::factory()->isValidNumber($numberProto);
            
            if(!$isValidNumber)
            {
                return FALSE;
            }    

            $mobile = Loader_Phone::factory()->getNationalSignificantNumber($numberProto);
        }
        catch(\libphonenumber\NumberParseException $e)
        {
            return FALSE;
        }
        $apikey = Config::$app['sms_yunpian_api_key'];
        
        $url="http://yunpian.com/v1/sms/send.json";
        $encoded_msg = urlencode("{$msg}");
        $post_string="apikey={$apikey}&text={$encoded_msg}&mobile={$mobile}";

        return $this->sock_post($url, $post_string);
    }
}