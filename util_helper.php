<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 타입별 목록개수 세팅 & 가져오기
function listitem($type,$s_rows)
{
    $CI =& get_instance();
    if($s_rows){
        if(!is_numeric($s_rows)) {
            $s_rows = 10;
        }else{
            if($s_rows > 100){
                $s_rows = 100;
            }
        }

        $CI->load->model('dbu_config');
        $CI->dbu_config->update_config($type, "listItem", $s_rows,"");
    }else{
        $s_rows = get_code_name("user_config",$type."_listItem");
        if(!$s_rows) $s_rows = 20;
    }
    return $s_rows;
}

function makedir($path, $is_recursive=false)
{
    if(!is_dir($path)){
        @mkdir($path, 0755, $is_recursive);
        @chown($path, "nobody");
        @chgrp($path, "nobody");
    }
}

function chk_trans_utf8($string)
{
    //$string = mb_check_encoding($string, 'UTF-8') ? $string : utf8_encode($string);
    $current_encoding = mb_detect_encoding($string, 'auto');
    $string = iconv($current_encoding, 'UTF-8//IGNORE', $string);
    return $string;
}

function cut_str($msg,$cut_size,$tail="...")
{
    if($cut_size <= 0) return $msg;
    $msg = strip_tags($msg);
    $msg = str_replace("&mp;quot;","\"",$msg);
    if(strlen($msg) <= $cut_size) return $msg;

    for($i=0;$i<$cut_size;++$i) if(ord($msg[$i])>127) $han++;
        else $eng++;
        if($han%2) $han--;

        $cut_size = $han + $eng;

    $tmp = substr($msg,0,$cut_size);
    $tmp .= $tail;
    return $tmp;
}

function get_email($str)
{
    if(strstr($str, "<")) {
        $name = getName_extract($str);
        $email = getEmail_extract($str);
        if(!trim($name)){
            $var['name'] = "";
            $var['email'] = trim(str_replace(">","",$email));
        }else{
            $var['name'] = trim($name);
            $var['email'] = trim(str_replace(">","",$email));
        }
    }else if(strstr($str, "&lt;")) {
        $name = getName_extract($str);
        $email = getEmail_extract($str);
        if(!trim($name)){
            $var['name'] = "";
            $var['email'] = trim(str_replace("&gt;","",$email));
        }else{
            $var['name'] = trim($name);
            $var['email'] = trim(str_replace("&gt;","",$email));
        }
    }else{
        $var['email'] = trim($r_name);
        $var['name'] = trim($r_name);
    }

    if(!trim($var['email'])){
        $var['email'] = $str;
        $var['name'] = $str;
    }
    return $var;
}

function get_emailaddr($full_addr)
{
    if (strstr($full_addr, "<"))
    {
        $temp = explode ("<", $full_addr);
        $r_name = preg_replace("/[\'|\"|,|<|>|\;]/","",$temp[0]);
        $r_name = trim($r_name);
        $temp = explode (">", $temp[1]); //email
        $r_email = trim($temp[0]);
    }
    else if(strstr($full_addr, "&lt;"))
    {
        $temp = explode ("&lt;", $full_addr);
        $r_name = preg_replace("/[\'|\"|,|&lt;|&gt;|\;]/","",$temp[0]);
        $r_name = trim($r_name);
        $temp = explode ("&gt;", $temp[1]); //email
        $r_email = trim($temp[0]);
    }
    else
    {
        $r_email=trim($full_addr);
    }

    return $r_email;
}

function save_gmt_change($org_timestamp)
{
    $gmt_gap = get_code_name("user_config","MAIL_timegmt") - 9;
    $gmt_gap = (int)($gmt_gap * 60 * 60 * -1);
    $return_timestamp = $org_timestamp+$gmt_gap;
    return $return_timestamp;
}

function cut_date($msg,$type="1",$gmt_gap='config')
{
    $msg = trim($msg);
    // 시스템 타임
    if(strlen($msg)== 10 && $msg > 1000000000 ){
        $msg_time = $msg;
        $msg = date('Y-m-d H:i:s',$msg);
    }else{
        $msg_time = strtotime($msg);
    }

    $msg = strip_tags($msg);
    $msg = str_replace("&mp;quot;","\"",$msg);
    $han = 0;
    $eng = 0;
    $cut_size = strlen($msg);
    for($i=0;$i<$cut_size;++$i)
    {
        if(ord($msg[$i])>127) $han++;
        else $eng++;
        if($han%2) $han--;
    }

    $cut_size = $han + $eng;
    if($msg){
        if($gmt_gap){

            if($gmt_gap == 'none') $gmt_gap = 'config';

            if($gmt_gap=='config'){
                $gmt_gap = get_code_name("user_config","MAIL_timegmt") - 9;
            }
            $gmt_gap = (int)($gmt_gap * 60);
            $msg_time = strtotime("{$gmt_gap} minutes", $msg_time);
        }

        // example $msg: 2016-05-04 12:34:56 //

        if($type=="2"){
            // 16050412 //
            $tmp = date("ymdH",$msg_time);

        }else if($type=="3"){
            // 2016년 05월 04일 //
            $tmp = substr($msg,0,4).langs('year')." ".substr($msg,5,2).langs('month')." ".substr($msg,8,2).langs('day');

        }else if($type=="4"){
            // 2016-05-04 12:34:56 //
            $tmp = date("Y-m-d H:i:s",$msg_time);

        }else if($type=="5"){
            // 2016-05-04 //
            $tmp = date("Y-m-d",$msg_time);

        }else if($type=="6"){
            // 20160504 //
            $tmp = date("Ymd",$msg_time);

        }else if($type=="7"){
            // unix timestamp //
            $tmp = $msg_time;

        }else if($type=="8"){
            // 12:34 (24시간제) //
            $tmp = date("H:i",$msg_time);

        }else if($type=="9"){
            // 05월 04일 //
            $tmp = date("m".langs('month')." d".langs('day'),$msg_time);

        }else if($type=="10"){
            // 2016-05-04 12:34 //
            $tmp = date("Y-m-d H:i",$msg_time);

        }else if($type=="11"){
            // 20160504123456 //
            $tmp = date("YmdHis",$msg_time);

        }else if($type=="12"){
            // 16-05-04 12:34:56 //
            $tmp = date("y-m-d H:i:s",$msg_time);

        }else if($type=="13"){
            // 16.05.04 12:34 //
            $tmp = date("y.m.d H:i",$msg_time);

        }else if($type=="14"){
            // 16.05.04 12:34:56 //
            $tmp = date("y.m.d H:i:s",$msg_time);

        }else if($type=="15"){
            // 16.05.04 //
            $tmp = date("y.m.d",$msg_time);

        }else if($type=="16"){
            // May 04 //
            $tmp = date("M d",$msg_time);

        }else if($type=="17"){
            // 2016.05.04 12:34 //
            $tmp = date("Y.m.d H:i",$msg_time);

        }else if($type=="18"){
            // 2016.05.04 //
            $tmp = date("Y.m.d",$msg_time);

        }else if($type=="19"){
            // AM/PM //
            $tmp = date("A",$msg_time);

        }else if($type=="20"){
            // 12:34 (12시간제) //
            $tmp = date("h:i",$msg_time);

        }else if($type=="21"){
            // 05-04 12:34 //
            $tmp = date("m-d H:i",$msg_time);

        }else if($type=="22"){
            // 16. 05. 04 //
            $tmp = date("y. m. d",$msg_time);

        }else{
            // 16-05-04 12:34:56 //
            $tmp = substr($msg,2,$cut_size); // (type == 12)일 때와 같은 format

        }
    }else{
        $tmp = "";
    }
    return $tmp;
}

function go_back($msg = "")
{
    echo vars('ALERT_HEADER_SKIN11');
    echo "<script>";
    if( $msg ){
        if(_MOBILE == "mobile") {
            echo "$.cookie('toast_cookie', '{$msg}', { path: '/' }); history.back();";
        }else{
            echo "dialog_alert('Message','{$msg}',function(){ history.back(); });";
        }
    }else{
        echo "history.back();";
    }
    echo "</script>";
}

function close_win($msg = "", $refresh = 0)
{
    echo vars('ALERT_HEADER_SKIN11');
    echo "<script>";
    if( $msg ){
        echo "dialog_alert('Message','{$msg}');";
    }
    if( $refresh ){
        echo "opener.location.reload();";
    }
    echo "top.close();";
    echo "</script>";
}

function go_to($url, $msg = "", $target = "", $openerReload = false)
{
    echo vars('ALERT_HEADER_SKIN11');

    echo "<script>";
    if( $msg ){
        echo "dialog_alert('Message','{$msg}');";
    }
    if( $target ){
        echo $target . ".";
    }
    echo "location.replace('{$url}');";
    if($openerReload){
        echo "opener.location.reload();";
    }
    echo "</script>";
}

function go_to_callback($url, $msg = "", $target = "", $openerReload = false)
{
    echo vars('ALERT_HEADER_SKIN11');

    echo "<script>";
    if( $msg ){
        echo "dialog_alert('Message','{$msg}',function(){ location.replace('{$url}') });";
    }
    if($openerReload){
        echo "opener.location.reload();";
    }
    echo "</script>";
}

function alert($msg,$reload = false)
{
    echo vars('ALERT_HEADER_SKIN11');

    echo "<script>";
    echo "dialog_alert('Message','{$msg}');";
    if($reload){
        echo "location.reload();";
    }
    echo "</script>";
}

if ( ! function_exists('go_mobile_hash'))
{
    /**
     * PHP :: function go_mobile_hash / script 해시 변경
     */
    function go_mobile_hash($param, $base_url=null)
    {
        $script = array();
        $json_param = json_encode((array)$param);

        $script[] = vars('ALERT_HEADER_MOBILE');
        $script[] = "<script>";
        if ( ! $base_url) {
            $script[] = "window.pub.hashChange(";
            $script[] = $json_param;
            $script[] = ");";
        }
        else {
            $script[] = "var hash = window.pub.param2hash(";
            $script[] = $json_param;
            $script[] = ");";
            $script[] = "location.href = \"" . $base_url . "\" + \"#\" + hash;";
        }
        $script[] = "</script>";

        echo implode("", $script);
    }
}

if ( ! function_exists('equal2print'))
{
    /**
     * PHP :: function equal2print / 문자열 비교 후 일치시 원하는 문자열 반환
     *
     * @param   String  $base_value         기준 문자열
     * @param   String  $compare_value      비교 대상 문자열
     * @param   String  $equal_value        기준과 비교 대상이 같을 경우 출력할 문자열
     * @param   String  $not_equal_value    기준과 비교 대상이 다를 경우 출력할 문자열
     */
    function equal2print($base_value = '', $compare_value = '', $equal_value = '', $not_equal_value = '', $is_test = '')
    {
        $base_str = (string)$base_value;
        $compare = (gettype($compare_value) === 'array') ? $compare_value : (string)$compare_value;
        $is_equal = false;

        if ((gettype($compare) === 'string') && ($base_str === $compare))
        {
            $is_equal = true;
        }

        if (gettype($compare) === 'array')
        {
            foreach((array)$compare as $com_val)
            {
                if ($base_str === (string)$com_val)
                {
                    $is_equal = true;
                }
            }
        }

        if (!!$is_test) {
            p_r(array(
                'is_test' => $is_test,
                'is_equal' => $is_equal,
                'base' => $base_str,
                'compare' => $compare,
                'equal' => $equal_value,
                'not_equal' => $not_equal_value,
            ));
        }

        return ($is_equal) ? $equal_value : $not_equal_value;
    }
}

if ( ! function_exists('equal2print_request'))
{
    /**
     * PHP :: function equal2print_request / $_REQUEST 비교 후 일치시 원하는 문자열 반환
     *
     * @param   String  $field              GET param의 필드값
     * @param   String  $compare_value      비교 대상 문자열
     * @param   String  $equal_value        기준과 비교 대상이 같을 경우 출력할 문자열
     * @param   String  $not_equal_value    기준과 비교 대상이 다를 경우 출력할 문자열
     */
    function equal2print_request($field = '', $compare_value = '', $equal_value = '', $not_equal_value = '')
    {
        $CI =& get_instance();
        $return_value;

        if (gettype($CI->input->get($field)) === 'string')
        {
            $base_str = (gettype($CI->input->get($field)) === 'string') ? $CI->input->get($field) : (string) $CI->input->get($field);
            $compare_str = (gettype($compare_value) === 'string') ? $compare_value : (string)$compare_value;

            $return_value = $not_equal_value;
            if ($CI->input->get($field) && ($base_str === $compare_str))
            {
                $return_value = $equal_value;
            }
        }
        else if (is_array($CI->input->get($field)))
        {

            $return_value = $not_equal_value;

            if (in_array($compare_value, $CI->input->get($field)))
            {
                $return_value = $equal_value;
            }
        }

        return $return_value;
    }
}

if ( ! function_exists('exist2print_request'))
{
    /**
     * PHP :: function exist2print_request / $_REQUEST 존재하지는 검사 후 일치시 원하는 문자열 반환
     *
     * @param   String  $field              GET param의 필드값
     * @param   String  $exist_value        GET param의 필드값이 존재할 경우 출력할 문자열
     * @param   String  $not_exist_value    GET param의 필드값이 존재하지 않을 경우 출력할 문자열
     */
    function exist2print_request($field = '', $exist_value = '', $not_exist_value = '')
    {
        $CI =& get_instance();
        $return_value = $not_exist_value;

        if ($CI->input->get($field))
        {
            $return_value = $exist_value;
        }

        return $return_value;
    }
}

function query_string($exclude = "", $query_string = "", $add = "")
{
    if(!$query_string)
    {
        $query_string = $_SERVER['QUERY_STRING'];
    }
    parse_str($query_string, $string_arr);

    if($exclude)
    {
        if(is_array($exclude))
        {
            foreach((array)$exclude as $out)
            {
                foreach($string_arr as $key => $val)
                {
                    if($out == $key)
                        unset($string_arr[$key]);
                }
            }
        }
        else
        {
            foreach($string_arr as $key => $val)
            {
                if($exclude == $key)
                    unset($string_arr[$key]);
            }
        }
    }
    $query_string = http_build_query($string_arr, '=', '&');
    $query_string = preg_replace('/^(\&|\?)?/i', '', $query_string);

    $query_string = str_replace("/index.php","",$query_string);

    if($add)
    {
        return $query_string ? $query_string."&".$add : $add;
    }
    else
    {
        return $query_string;
    }
}

function concat(/*arg0=[glue], ..., argN*/)
{
    if(func_num_args() < 2)return "";
    $args = func_get_args();
    $glue = array_shift($args);

    return implode($glue, $args);
}

function get_code_list($cd_field, $sst="")
{
    $CI =& get_instance();
    return $CI->property->get_index($cd_field, $sst);
}

function get_code_name($index, $key, $default="")
{

    if(isset($_COOKIE[$key]) && $index == 'user_config'){
        $return = $_COOKIE[$key];
    }else if(isset($_SESSION[$key]) && $index == 'user_config'){
        $return = $_SESSION[$key];
    }else{
        $CI =& get_instance();
        if(!$CI->session->userdata('sess_cid') && ($index == 'user_config')){
            return ;
        }
        $return = $CI->property->get($index, $key);
        if(isset($_SESSION['sess_cid'])){
            if($index == 'user_config' || $index == 'config' || $index == 'server'){
                $CI->session->set_userdata(array($key => $return));
            }
        }
    }
    if(!is_array($return)){
        if(strlen($return) == 0) $return = $default;
    }
    return $return;
}

function get_user_code_list($us_id, $index, $cf_name)
{
    $CI =& get_instance();
    $return = $CI->property->get_user_index($us_id, $index, $cf_name);
    if(!is_array($return)){
        if(strlen($return) == 0) $return = $default;
    }
    return $return;
}

function langs($key)
{
    $CI =& get_instance();
    if(_DOMAIN == 'mx16.wiro.kr')
        return $key;
    else
        return $CI->property->get_lang($key);
}

function cr_code($key, $type="code")
{
    $CI =& get_instance();
    if(_DOMAIN == 'mx16.wiro.kr')
        return $key;
    else if ($type === "code")
        return $CI->property->get_cr_code($key);
    else if ($type === "country")
        return $CI->property->get_cr_name($key);
}

function vars($key)
{
    $CI =& get_instance();
    return $CI->config->item($key, 'vars');
}

function get_vars($key, $index)
{
    $CI =& get_instance();
    $tmp = $CI->config->item($key, 'vars');
    return $tmp[$index];
}

function is_serialized($val)
{
    if (!is_string($val))return false;
    if (trim($val) == "")return false;
    if (preg_match("/^(i|s|a|o|d|b):(.*);/si",$val))return true;
    return false;
}

function callback_base64_encode(&$item, $key)
{
    switch($key)
    {
    case "org_name":
        $item = base64_encode($item);
        break;
    case "arc":
        $item = str_replace("arc", "", $item);
        $item = str_replace("N", "", $item);
        break;
    case "arc_quota":
        $item = $item*1024;
        if($item == 0) $item = "";
        break;
    default:
        $item = $item;
        break;
    }
}

function s_cookie($name,$value)
{

    $CI =& get_instance();

    $CI->input->set_cookie(array(
        'name'   => $name,
        'value'  => $value,
        'expire' => strtotime("2038-01-01")-time(),
        'domain' => '.'._DOMAIN,
        'path'   => '/',
        'prefix' => ''
    ));
}

function d_cookie($name)
{
    setcookie($name, null, -1, '/');
}

function g_cookie($name)
{
    $CI =& get_instance();
    return $CI->input->cookie($name);
}

function callback_base64_decode(&$item, $key)
{
    switch($key)
    {
    case "org_name":
        $item = addslashes(base64_decode($item));
        break;
    case "arc":
        $item = str_replace("N", "", $item);
        if($item != "")
            $item = "arc".$item;
        else
            $item = "";
        break;
    case "arc_quota":
        $item = $item/1024;
        if($item == 0) $item = "";
        break;
    default:
        $item = $item;
        break;
    }
}

function check_encoding($str,$type="UTF-8")
{
    //$arrEncode = array("UTF-8", "EUC-KR", "JIS", "SHIFT-JIS", "BIG5", "GB2312");
    $arrEncode = array("EUC-KR");
    $chk =  mb_detect_encoding($str, $arrEncode, true);
    if ((!! $chk) && ( $chk != $type )) { // case by 반응있고, check 된 type 다를때
        $new_str = iconv($chk, $type."//IGNORE" , $str);
        if(trim($new_str) == '') return $str;
        else return $new_str;
    }
    else{
        return $str;
    }
}

function _decode($encoding,$user_id,$num)
{

    $CI =& get_instance();
    $CI->load->model('dbu_index');
    $mail_info = $CI->dbu_index-mail_info(array(
        "select" => "file_id",
        "num" => $num
    ));
    $msg_fname = $mail_info["file_id"];
    $home_root = $_DOMAIN."/".$user_id;
}

function callback_check(&$item, $key)
{
    $item = str_replace("&","＆", $item);
    $item = str_replace("'","’", $item);
    $item = str_replace(","," ", $item);
    $item = str_replace('"',' ', $item);
    $item = str_replace('}',' ', $item);
    $item = str_replace('{',' ', $item);
    if($key!='receiver'){
        $item = str_replace(';',' ', $item);
    }
    $item = str_replace(':',' ', $item);
}

function file_size($size)
{
    $filesizename = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    return $size ? @round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0B';
}

function webhard_file_size($size, $digit = 2, $num = 4, $number_format = true)
{
    //if(!is_numeric($size)) return FALSE;
    if(!$size) return '0 B';

    $filesizename = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    $i = floor(log($size, 1024));
    if($i > 3) $i = 3;
    $size_tmp = explode(".", round($size/pow(1024, $i), $digit));
    $result = $size_tmp[0];
    $tail_size = $size_tmp[1];

    if($tail_size > 0) {
        $num = abs($num - strlen($result));
        if($num > 0) {
            $result .=  '.' . substr($tail_size, 0, $num);
        }
    }else{
        if(!$num) $result .= '.00';
    }
    if($number_format) {
        $result = number_format($result);
    }
    return $result . ' ' . $filesizename[$i];

}

// SMS 발송
function sms_send($data)
{
    $CI =& get_instance();
    $CI->load->library('api_sms');
    if($CI->api_sms->sms($data))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function p_r($array="")
{
    if($array=="") $array = $_REQUEST;
    echo '<div style="
    position: absolute;
    z-index: 100000;
    top: 0;
    left: 0;
    opacity: 0.95;
    -webkit-box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);
    box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);
    padding: 1.5rem;
    background-color: white;
"><pre>';
    if(is_array($array))
        echo htmlspecialchars(print_r($array, true));
    else
        echo htmlspecialchars($array);
    echo '</pre></div>';
}

function getFileSize($size,$unit="",$out="Y")
{
    $tmpvalue = $size;
    $ncount = 0;
    $ncount_limit = 3;
    if($unit=="KB") $ncount_limit = 1;
    if($unit=="MB") $ncount_limit = 2;
    if($unit=="GB") $ncount_limit = 3;

    while($tmpvalue){
        $tmpvalue = $tmpvalue / 1024;
        $ncount++;
        if($ncount >= $ncount_limit)
            break;
        if($unit=="" && $tmpvalue < 1024)
            break;
    }
    $addment = "B";
    if($ncount == 1) $addment = "KB";
    if($ncount == 2) $addment = "MB";
    if($ncount == 3) $addment = "GB";

    if($out == "Y")
        return number_format(round($tmpvalue, 2)).$addment;
    else
        return round($tmpvalue, 2);
}

/*
 * PHP::split_index_get($string,$index,$standard = ".") 스트링을 분할하여 받은 키의 값을 리턴
 * @param $string string 분할이 될 문자열
 * @param $index string|number 리턴 받을 키 값
 * @param $standard string 분할을 할 문자열
 * @return string
 */
function split_index_get($string,$index,$standard = ".")
{

    $st_array = explode($standard,$string);
    if($index=='last'){
        $index = count($st_array)-1;
    }

    return $st_array[$index];
}

/*
 * PHP::array_key_change($array, $field) 키를 서브배열의 값으로 변경
 * @param $array array 서브배열필드랑 교체될 배열
 * @param $field string 키값이 될 필드값
 * @return array 변경된 배열 리턴
 */
function array_key_change($array,$field)
{
    $array_remake = array();
    if(is_array($array) && count($array)>0){
        foreach($array as $key => $value){
            $array_remake[$value[$field]] = $value;
        }
    }
    return $array_remake;
}

// http://kr1.php.net/manual/en/function.array-column.php
if (!function_exists('array_column'))
{
    function array_column($input, $column_key, $index_key = null)
    {
        if ($index_key !== null) {
            // Collect the keys
            $keys = array();
            $i = 0; // Counter for numerical keys when key does not exist

            foreach ((array)$input as $row) {
                if (array_key_exists($index_key, (array)$row)) {
                    // Update counter for numerical keys
                    if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
                        $i = max($i, (int) $row[$index_key] + 1);
                    }

                    // Get the key from a single column of the array
                    $keys[] = $row[$index_key];
                } else {
                    // The key does not exist, use numerical indexing
                    $keys[] = $i++;
                }
            }
        }

        if ($column_key !== null) {
            // Collect the values
            $values = array();
            $i = 0; // Counter for removing keys

            foreach ((array)$input as $row) {
                if (array_key_exists($column_key, (array)$row)) {
                    // Get the values from a single column of the input array
                    $values[] = $row[$column_key];
                    $i++;
                } elseif (isset($keys)) {
                    // Values does not exist, also drop the key for it
                    array_splice($keys, $i, 1);
                }
            }
        } else {
            // Get the full arrays
            $values = array_values($input);
        }

        if ($index_key !== null) {
            return array_combine($keys, $values);
        }

        return $values;
    }
}

// uri체크 (My_Controller.php에서 사용)
function chk_page_auth($uri,$chk_array)
{
    $return = false;
    foreach((array)$chk_array as $key => $chk_uri){
        $chk_uri = str_replace("/","\\/",$chk_uri);
        if(preg_match("/{$chk_uri}/i", $uri)){
            $return = true;
        }
    }
    return $return;
}

function make_log_kind($repl_uri, $data)
{
    if(isset($data['mode'])){
        $kind = "{$repl_uri}_{$data['mode']}";
    }else if(isset($data['kind'])){
        if($data['kind'] == 'auto_save_chk') return false;
        $kind = "{$repl_uri}_{$data['kind']}";
    }else if(isset($data['d_kind'])){
        $kind = "{$repl_uri}_{$data['d_kind']}";
    }else if(isset($data['operation'])){
        $kind = "{$repl_uri}_{$data['operation']}";
    }else{
        $kind = $repl_uri;
    }
    return strtoupper($kind);
}

function array_diff_chk($array1,$array2)
{
    $result1 = array_diff((array)$array1, (array)$array2);
    $result2 = array_diff((array)$array2, (array)$array1);
    if(count($result1) + count($result2) > 0){
        return true;
    }
    else{
        return false;
    }

}

function get_user_agent()
{

    $CI =& get_instance();
    $CI->load->library('user_agent');
    if($CI->agent->is_browser()){
        $agent = $CI->agent->browser().' '.$CI->agent->version();
    }
    elseif($CI->agent->is_robot()){
        $agent = $CI->agent->robot();
    }
    elseif($CI->agent->is_mobile()){
        $agent = $CI->agent->mobile();
    }
    else{
        $agent = 'Unidentified User Agent';
    }
    return $agent;
}

function get_ext($file,$LOWER="LOWER")
{
    $file=preg_replace("/^.+\.([^\.]{1,})$/","$1",$file);
    if($LOWER=="LOWER"){
        $file = strtolower($file);
    }
    return $file;
}

// 실제경로 가져오기(/home/mail/h0X/domain)
function getRealPath($domain)
{
    $realPath = readlink($this->homePath.$domain);
    $realPath = str_replace("../", $this->mailPath, $realPath);

    return $realPath;
}

// 주도메인인지, 멀티도메인인지
function getDomainKind($domain)
{
    $realPath = $this->getRealPath($domain);
    if(is_link($realPath)){
        return false;
    }else{
        return true;
    }
}

// 멀티도메인의 주도메인 가져오기
function getMainDomain($domain)
{
    if($this->getDomainKind($domain)){
        return $domain;
    }else{
        $hx = str_replace($domain,"",$this->getRealPath($domain));
        return str_replace($hx,"",readlink($this->getRealPath($domain)));
    }
}

function editor_font_info($font_key,$return_type)
{

    $FONT_STRING = array(
        "dotum"          => array(
            "name"  => "돋움",
            "style" => "돋움,dotum"
        ),
        "dotumche"       => array(
            "name"  => "돋움체",
            "style" => "돋움체,dotumche,applegothic"
        ),
        "gulim"          => array(
            "name"  => "굴림",
            "style" => "굴림,gulim"
        ),
        "gulimche"       => array(
            "name"  => "굴림체",
            "style" => "굴림체,gulimche"
        ),
        "batang"         => array(
            "name"  => "바탕",
            "style" => "바탕,batang,applemyungjo"
        ),
        "batangche"      => array(
            "name"  => "바탕체",
            "style" => "바탕체,batangche"
        ),
        "gungsuh"        => array(
            "name"  => "궁서",
            "style" => "궁서,gungsuh,gungseo"
        ),
        "arial"          => array(
            "name"  => "arial",
            "style" => "arial"
        ),
        "tahoma"         => array(
            "name"  => "tahoma",
            "style" => "tahoma"
        ),
        "timesnewroman"  => array(
            "name"  => "times new roman",
            "style" => "times new roman"
        ),
        "verdana"        => array(
            "name"  => "verdana",
            "style" => "verdana"
        ),
        "couriernew"     => array(
            "name"  => "courier new",
            "style" => "courier new"
        ),
        "mspgothic"      => array(
            "name"  => "Pゴシック",
            "style" => "ms pgothic, sans-serif"
        ),
        "mspmincho"      => array(
            "name"  => "P明朝",
            "style" => "ms pmincho, serif"
        ),
        "msgothic"       => array(
            "name"  => "ゴシック",
            "style" => "ms gothic, monospace"
        ),
        "nsimsun"        => array(
            "name"  => "宋体",
            "style" => "nsimsun, monospace"
        ),
        "fangsong"       => array(
            "name"  => "仿宋",
            "style" => "fangsong, monospace"
        ),
        "microsoftyahei" => array(
            "name"  => "微软雅黑",
            "style" => "microsoft yahei, monospace"
        ),
        "malgungothic" => array(
            "name"  => "맑은 고딕",
            "style" => "맑은 고딕, Malgun Gothic"
        ),
        "nanumgothic" => array(
            "name"  => "나눔고딕",
            "style" => "나눔고딕, NanumGothic"
        )
    );

    if(!array_key_exists($font_key, $FONT_STRING)){
        $font_key = "dotum";
    }

    if($return_type=="name"){
        return $FONT_STRING[$font_key]["name"];
    }else if($return_type=="style"){
        return $FONT_STRING[$font_key]["style"];
    }
}

function cheditor1($id, $width='100%', $height='250')
{
    if(_SERVER_TYPE == 'PRU'){
        return "
        <script type='text/javascript'>
        var ed_{$id} = new cheditor('ed_{$id}');
        ed_{$id}.config.editorHeight = '{$height}';
        ed_{$id}.config.editorWidth  = '{$width}';
        ed_{$id}.config.editorFontName = '굴림';
        ed_{$id}.config.editorFontSize = '10pt';
        ed_{$id}.config.useMap = false;
        ed_{$id}.inputForm = 'tx_{$id}';
        ed_{$id}.editorPath = '../cheditor';
        </script>";
    }else{
        return "
        <script type='text/javascript'>
        var ed_{$id} = new cheditor('ed_{$id}');
        ed_{$id}.config.editorHeight = '{$height}';
        ed_{$id}.config.editorWidth  = '{$width}';
        ed_{$id}.config.editorFontName = '굴림';
        ed_{$id}.config.editorFontSize = '10pt';
        ed_{$id}.inputForm = 'tx_{$id}';
        </script>";
    }

    //ed_{$id}.config.editorFontName = '궁서';
}

function cheditor2($id, $content='')
{
    return "
    <textarea name='{$id}' id='tx_{$id}' style='display:none;'>{$content}</textarea>
    <script type='text/javascript'>
    ed_{$id}.run();
    </script>";
}

function cheditor3($id)
{
    return "document.getElementById('tx_{$id}').value = ed_{$id}.outputBodyHTML().replace(/\uFEFF/g,'');";
}

function mobile_editor($id, $content = '')
{

    $font = get_code_name('user_config','MAIL_editor_font');
    $font_size = get_code_name('user_config','MAIL_editor_fontsize').'pt';
    if($font != '' && $font_size != '')
    {
        $font_arr = array(
            'dotum'          => "돋움,Dotum",
            'dotumche'       => "돋움체,DotumChe,AppleGothic",
            'gulim'          => "굴림,Gulim",
            'gulimche'       => "굴림체,GulimChe",
            'batang'         => "바탕,Batang,AppleMyungjo",
            'batangche'      => "바탕체,BatangChe",
            'gungsuh'        => "궁서,Gungsuh,GungSeo",
            'malgungothic'   => "맑은 고딕,Malgun Gothic",
            'arial'          => 'Arial',
            'tahoma'         => 'Tahoma',
            'timesnewroman'  => 'Times New Roman',
            'verdana'        => 'Verdana',
            'couriernew'     => 'Courier New',
            'mspgothic'      => 'ms pgothic,sans-serif',
            'mspmincho'      => 'ms pmincho,serif',
            'msgothic'       => 'ms gothic,monospace',
            'nsimsun'        => 'nsimsun,monospace',
            'fangsong'       => 'fangsong,monospace',
            'microsoftyahei' => 'microsoft yahei,monospace',
            'nanumgothic'    => '나눔고딕, NanumGothic, Sans-serif'
        );
    }
    $res = "
        <style> div[contenteditable] p, .div[contenteditable] br { margin: 0; padding: 0; float: none; } </style>
        <div style='padding:20px 15px 0px 15px;box-shadow:none;font-size: 16px;position:relative;min-height:56px;color: #222;background:#fff;'>
            <div id='ed_{$id}' style='position: relative; overflow-y: hidden; display: block;font-family:Arial, Noto sans;font-size:16px;height: auto; min-height: 205px; -webkit-overflow-scrolling: touch; color: #000; white-space: normal; outline: none;' contenteditable='true' placeholder='" . langs('nodatacomment') . "'>{$content}</div>
        </div>
    ";

    if ($id == 'wr_content') { // case by board
        $res = "
            <style> div[contenteditable] p, .div[contenteditable] br { margin: 0; padding: 0; float: none; } </style>
            <div style='padding:20px 15px 0px 15px;box-shadow:none;font-size: 16px;position:relative;min-height:56px;color: #222;background:#fff;'>
            <div id='ed_{$id}' style='position: relative; overflow-y: hidden; display: block; height: auto; min-height: 205px; max-width: 100%; -webkit-overflow-scrolling: touch; color: #000; white-space: normal;font-family:Arial, Noto Sans,Malgun Gothic;font-size:16px; outline: none;' contenteditable='true' placeholder='" . langs('mobile_board_write_input') . "'>{$content}</div>
            </div>
        ";
    }

    return $res;
}

function mobile_editor_sync($id)
{
    return "document.getElementById('tx_{$id}').value = document.getElementById('ed_{$id}').innerHTML.replace(/\uFEFF/g,'');";
}

// 배열 초기값 설정
function array_setting($Array )
{
    array_walk_recursive($Array,'array_setting2');
    return $Array;
}

function array_setting2(&$item)
{
    if(!is_array($item)){
        if($item == ''){
            $item = "-";
        }
    }
}

/**
 * function array_setting3 / 배열 초기값 설정 3
 *
 * @TODO    - 2depth 이상
 * @desc    - array에 원하는 값으로 replace all
 */
if ( ! function_exists('array_setting3'))
{
    function array_setting3($arr, $replace_value="")
    {
        if ( ! is_array($arr)) return $arr;

        foreach((array)$arr as $key => $value)
        {
            $arr[$key] = $replace_value;
        }

        return $arr;
    }
}

// 다운로드 파일명 변경
function down_file_name($filename, $zip="")
{
    $USER_AGENT = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(preg_match('/iPhone/',$USER_AGENT)){
        $equipment = "m";
    }else if(preg_match('/Android|Mobile|samsung/',$USER_AGENT)){
        $equipment = "a";
    }
    $urlencode = "";
    $nclang = "";
    switch($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    {
        case "ja":
            $nclang = "shift-jis//IGNORE";
            break;
        case "zh-TW":
            $nclang = "big5//IGNORE";
            break;
        case "zh-CN":
            $nclang = "gb2312//IGNORE";
            break;
        case "zh-tw":
            $nclang = "big5//IGNORE";
            break;
        case "zh-cn":
            $nclang = "gb2312//IGNORE";
            break;
        default: //"ko"
            $nclang = "cp949//IGNORE";
    }
    if($zip){
        //IE
        /*
         * mac 에서 보낸 파일명 처리를 위한 함수
         * if (!normalizer_is_normalized($filename)) {
         *  $filename = normalizer_normalize($filename);
         * }
         *
         * php-intl library 를 서버에 설치 해야하고 고객문의가 적어 일단 보류
         * */
        if(preg_match('/trident/',$USER_AGENT) || preg_match('/msie/',$USER_AGENT)){
            $tmp_filename =  iconv("UTF-8", $nclang, $filename);
            $str = "1. {$nclang} // {$filename} // {$tmp_filename} ";
        }else{
            $tmp_filename = iconv("UTF-8", $nclang, $filename);
            $str = "2. {$nclang} // {$filename} // {$tmp_filename} ";
        }
    }else{
        //IE
        if(preg_match('/trident/',$USER_AGENT) || preg_match('/msie/',$USER_AGENT)){
            $tmp_filename = rawurlencode($filename);
            //$tmp_filename = iconv("UTF-8", $nclang, $filename);
            //$tmp_filename = $filename;
            $str = "3. {$nclang} // {$filename} // {$tmp_filename} ";
        }else{
            // Edge 예외 처리
            if (strpos($_SERVER['HTTP_USER_AGENT'], "Edge") == true)
            {
                $tmp_filename = iconv("UTF-8", $nclang, $filename);
            }else{
                $tmp_filename = $filename;
            }
            $str = "4. {$nclang} // {$filename} // {$tmp_filename} ";
        }
    }
    if (strpos($_SERVER['HTTP_USER_AGENT'], "Alamofire") == true)
    {
        $tmp_filename = rawurlencode($tmp_filename);
    }
    //echo $str;
    return $tmp_filename;
}

function gmtPrint($gmt,$lang)
{
    if(!$lang) $lang = "kr";
    if($lang == "kr"){
        switch($gmt){
            case "-12" : $gmtMent = "GMT -12:00 (날짜 변경선 서쪽)";break;
            case "-11" : $gmtMent = "GMT -11:00 (미드웨이 아일랜드, 사모아)";break;
            case "-10" : $gmtMent = "GMT -10:00 (하와이)";break;
            case "-9.5" : $gmtMent = "GMT -09:30 (마퀘사스)";break;
            case "-9" : $gmtMent = "GMT -09:00 (알래스카)";break;
            case "-8.5" : $gmtMent = "GMT -08:30 (피칸)";break;
            case "-8" : $gmtMent = "GMT -08:00 (태평양 표준시)";break;
            case "-7" : $gmtMent = "GMT -07:00 (산지 표준시_미국/캐나다)";break;
            case "-6" : $gmtMent = "GMT -06:00 (중부 표준시_미국/캐나다)";break;
            case "-5" : $gmtMent = "GMT -05:00 (동부 표준시_미국/캐나다)";break;
            case "-4.5" : $gmtMent = "GMT -04:30 (카라카스)";break;
            case "-4" : $gmtMent = "GMT -04:00 (대서양 표준시)";break;
            case "-3.5" : $gmtMent = "GMT -03:30 (뉴펀들랜드)";break;
            case "-3" : $gmtMent = "GMT -03:00 (부에노스아이레스)";break;
            case "-2" : $gmtMent = "GMT -02:00 (중앙 대서양)";break;
            case "-1" : $gmtMent = "GMT -01:00 (아조레스)";break;
            case "0" : $gmtMent = "GMT +00:00 (그리니치 표준시_런던)";break;
            case "1" : $gmtMent = "GMT +01:00 (암스테르담, 베를린, 빈)";break;
            case "2" : $gmtMent = "GMT +02:00 (아테네, 카이로)";break;
            case "3" : $gmtMent = "GMT +03:00 (모스크바, 바그다드)";break;
            case "3.5" : $gmtMent = "GMT +03:30 (테헤란)";break;
            case "4" : $gmtMent = "GMT +04:00 (바쿠, 무스카트)";break;
            case "4.5" : $gmtMent = "GMT +04:30 (카불)";break;
            case "5" : $gmtMent = "GMT +05:00 (카라치)";break;
            case "5.5" : $gmtMent = "GMT +05:30 (뉴델리, 뭄바이)";break;
            case "5.75" : $gmtMent = "GMT +05:45 (카트만두)";break;
            case "6" : $gmtMent = "GMT +06:00 (아스타나, 다카)";break;
            case "6.5" : $gmtMent = "GMT +06:30 (양곤)";break;
            case "7" : $gmtMent = "GMT +07:00 (방콕, 하노이, 자카르타)";break;
            case "8" : $gmtMent = "GMT +08:00 (베이징, 싱가폴, 타이베이)";break;
            case "9" : $gmtMent = "GMT +09:00 (서울, 도쿄)";break;
            case "9.5" : $gmtMent = "GMT +09:30 (다윈, 아델라이드)";break;
            case "10" : $gmtMent = "GMT +10:00 (괌, 캔버라, 시드니)";break;
            case "11" : $gmtMent = "GMT +11:00 (뉴 칼레도니아)";break;
            case "11.5" : $gmtMent = "GMT +11:30 (노포크 아일랜드)";break;
            case "12" : $gmtMent = "GMT +12:00 (오클랜드,피지)";break;
            case "-12" : $gmtMent = "GMT -12:00 (International Date Line West)";break;
        }
    }else{
        switch($gmt){
            case "-11" : $gmtMent = "GMT -11:00 (Midway Island, American Samoa)";break;
            case "-10" : $gmtMent = "GMT -10:00 (Hawaii)";break;
            case "-9.5" : $gmtMent = "GMT -09:30 (Marquesas)";break;
            case "-9" : $gmtMent = "GMT -09:00 (Alaska)";break;
            case "-8.5" : $gmtMent = "GMT -08:30 (Pecans)";break;
            case "-8" : $gmtMent = "GMT -08:00 (PT)";break;
            case "-7" : $gmtMent = "GMT -07:00 (Mountain Time US / Canada)";break;
            case "-6" : $gmtMent = "GMT -06:00 (CT USA / Canada)";break;
            case "-5" : $gmtMent = "GMT -05:00 (ET US / Canada)";break;
            case "-4.5" : $gmtMent = "GMT -04:30 (Caracas)";break;
            case "-4" : $gmtMent = "GMT -04:00 (AST)";break;
            case "-3.5" : $gmtMent = "GMT -03:30 (Newfoundland)";break;
            case "-3" : $gmtMent = "GMT -03:00 (Buenos Aires)";break;
            case "-2" : $gmtMent = "GMT -02:00 (Mid-Atlantic)";break;
            case "-1" : $gmtMent = "GMT -01:00 (Azores)";break;
            case "0" : $gmtMent = "GMT +00:00 (London GMT)";break;
            case "1" : $gmtMent = "GMT +01:00 (Amsterdam, Berlin, Vienna)";break;
            case "2" : $gmtMent = "GMT +02:00 (Athens, Cairo)";break;
            case "3" : $gmtMent = "GMT +03:00 (Moscow, Baghdad)";break;
            case "3.5" : $gmtMent = "GMT +03:30 (Tehran)";break;
            case "4" : $gmtMent = "GMT +04:00 (Baku, Muscat)";break;
            case "4.5" : $gmtMent = "GMT +04:30 (Kabul)";break;
            case "5" : $gmtMent = "GMT +05:00 (Karachi)";break;
            case "5.5" : $gmtMent = "GMT +05:30 (New Delhi, Mumbai)";break;
            case "5.75" : $gmtMent = "GMT +05:45 (Kathmandu)";break;
            case "6" : $gmtMent = "GMT +06:00 (Astana, Dhaka)";break;
            case "6.5" : $gmtMent = "GMT +06:30 (Yangon)";break;
            case "7" : $gmtMent = "GMT +07:00 (Bangkok, Hanoi, Jakarta)";break;
            case "8" : $gmtMent = "GMT +08:00 (Beijing, Singapore, Chinese Taipei)";break;
            case "9" : $gmtMent = "GMT +09:00 (Seoul, Tokyo)";break;
            case "9.5" : $gmtMent = "GMT +09:30 (Darwin, Adelaide)";break;
            case "10" : $gmtMent = "GMT +10:00 (Guam, Canberra, Sydney)";break;
            case "11" : $gmtMent = "GMT +11:00 (New Caledonia)";break;
            case "11.5" : $gmtMent = "GMT +11:30 (Norfolk Island)";break;
            case "12" : $gmtMent = "GMT +12:00 (Auckland, Fiji)";break;
        }
    }

    return $gmtMent;
}

// 사용자 이미지 경로 찾기
function user_photo($id,$photo)
{
    $no_img = "/asset/wm60/images/myphoto_default.gif";
    $tmp = explode("|", $photo);
    // 유저이미지 파일이동
    $ext = get_ext(_HOMEROOT.$tmp[0],"NO");
    $photo_url = _HOMEROOT."userImg/"._DOMAIN."/avatar/".$id.".".$ext;
    if(file_exists($photo_url))
        $photo_url = _USERIMGPATH."avatar/".$id.".".$ext;
    else
        $photo_url = $no_img;

    return $photo_url;
}

/**
 * 인사 이미지 변환
 *
 * 캐시 경로 -> 실경로 변경
 *
 * @param   dummy_file_name      string     - cache된 파일 이름
 * @return  rename               string     - 실제 저장된 파일 이름
 */
if(!function_exists('cache2hr_photo'))
{
    function cache2hr_photo($hr_id, $dummy_file_name)
    {
        $ext = get_ext(_HOMEROOT.$dummy_file_name);

        $cache_file = _HOMEROOT."userImg".str_replace("/tmp/app_tmp/cache/", "/app_tmp/", _TMPUSER).$dummy_file_name;
        $rename = $hr_id.".".$ext;
        $save_file = _HOMEROOT."userImg/"._DOMAIN."/img/".$rename;

        if (file_exists($save_file)) // case by overwrite
        {
            unlink($save_file);
        }

        rename($cache_file, $save_file);

        return $rename;
    }
}

// get header function
if(!function_exists('getallheaders'))
{
    function getallheaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) != 'HTTP_') { continue; }
            $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = trim($value);
        }
        return $headers;
    }
}

if (!function_exists('sqlite_escape_string'))
{
    function sqlite_escape_string($string)
    {
        return SQLite3::escapeString($string);
    }
}

function tidy_conv($string, $doc_trim = 'Y')
{
    if(extension_loaded('tidy')) {
        //$string = preg_replace("/<!--.*(mso|vml).*-->/si", "", $string);
        if($doc_trim == 'Y'){
            $tidy_config = array(
                        'indent'           => 'auto',
                        'clean'            => false,
                        'show-body-only'   => false,
                        'input-encoding'   => 'utf8',
                        'output-encoding'  => 'utf8',
                        'char-encoding'    => 'utf8',
                        'language'         => 'kr',
                        'wrap'             => 200);
            // Tidy
            $tidy = new tidy;
            $tidy->parseString($string, $tidy_config, 'utf8');
            $tidy->cleanRepair();
            $string = $tidy;

            $pattern = array(
                '/<!DOCTYPE[^\>]*>/i',
                '/<html>/i',
                '/<head>/i',
                '/<title><\/title>/i',
                '/<\/html>/i',
                '/<\/head>/i',
                '/<body>/i',
                '/<\/body>/i',
                '/<\/?textarea[^>]*>/i',
                '/<!--[^>]*-->/i'
                //'/<style>.*<\/style>/i'
            );
            $replace = array_fill(0, sizeof($pattern) - 1, "");
            $string = preg_replace($pattern, $replace, $string);
        }else{
            $tidy_config = array(
                        'indent'           => 'auto',
                        'clean'            => true,
                        'show-body-only'   => false,
                        'input-encoding'   => 'utf8',
                        'output-encoding'  => 'utf8',
                        'char-encoding'    => 'utf8',
                        'language'         => 'kr',
                        'wrap'             => 200);
            // Tidy
            $tidy = new tidy;
            $tidy->parseString($string, $tidy_config, 'utf8');
            $tidy->cleanRepair();
            $string = $tidy;

            $pattern = array(
                '/<!DOCTYPE[^\>]*>/i',
                '/<html>/i',
                '/<head>/i',
                '/<title><\/title>/i',
                '/<\/html>/i',
                '/<\/head>/i',
                '/<body>/i',
                '/<\/body>/i',
                '/<\/?textarea[^>]*>/i'
                //'/<style>.*<\/style>/i'
            );
            $replace = array_fill(0, sizeof($pattern) - 1, "");
            $string = preg_replace($pattern, $replace, $string);

        }
    }
    return $string;
}

// 메일 주소 형식 체크
function email_chk($emailaddress)
{
    //일반적 공백이 아닌 잘못된 공백 제거
    $emailaddress = urldecode(str_replace("%C2%A0", "", urlencode($emailaddress)));
    $emailaddress = str_replace("\\u00a0", "", $emailaddress);
    //여러명에게 보낼 때 공백이 포함된 주소가 있음
    $emailaddress = trim($emailaddress);
    // 이메일 형식일때
    if(preg_match("/^[-A-Za-z0-9_!]+[-A-Za-z0-9_.#&]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,20}$/", $emailaddress)){
        return true;
    }
    // 이메일 형식이 아닐때
    else{
        return false;
    }
}

function email_check($email)
{
    return filter_var($email , FILTER_VALIDATE_EMAIL);
}

// 메신저 알림
function messenger_notify($data, $type = 'notify')
{
    $url = 'http://'.vars('MSG_NOTIFY').'/'.$type;

    $cmd = "curl -s --connect-timeout 1 --max-time 1 -X POST {$url} -d '".json_encode($data, true)."' -H \"Content-Type: application/json\"> /dev/null &";

    if ( ! file_exists(_SCHEMA.'etc/NOMSG'))
    {
        // exec_helper() 태우면 response 오는 시간이 길어짐
        exec($cmd);
    }
}

//scav 변경하기
function scanv_check($file_path,$fwdsave="")
{
    $CI =& get_instance();
    $CI->load->helper('file');

    if(file_exists($file_path)){

        define("QMAIL_PATH", $file_path);
        $config_path = str_replace(".qmail", "config.dbs", QMAIL_PATH);

        $dbconn = new SQLite3($config_path);
        $dbconn->busyTimeout(1000);
        $userQuery = $dbconn->query("select * from config where name in ('offsend_target','offsend_setting','forward_copy','offsend_start','offsend_end','forward_email','spam_level','spam_save_due')");
        if($userQuery){
            $data = array();
            while($row = $userQuery->fetchArray()){
                $data[$row['name']] = $row['value'];
            }
        }
        $dbconn->close();


        $offsend_target  = $data['offsend_target'];
        $offsend_setting = $data['offsend_setting'];
        $forward_copy    = $data['forward_copy'];
        $offsend_start   = $data['offsend_start'];
        $offsend_end     = $data['offsend_end'];
        $forward_email   = $data['forward_email'];
        $spam_level      = $data['spam_level'];
        $spam_save_due   = $data['spam_save_due'];

        if(!$spam_level) $spam_level = 'medium';
        if(!$spam_save_due) $spam_save_due = '7';
        if($spam_save_due == '0') $spam_save_due = 'delete';

        if(!$forward_copy) $forward_copy = 'N';

        $forward_email2 = str_replace("|","\n",$forward_email);

        $current = file_get_contents(QMAIL_PATH);
        $tmp = explode("\n",$current);

        // |/usr/local/bin/php /usr/local/bin/scanv.php
        //$tmp2 = explode(" ",$tmp[0]);
        $tmp2 = array();
        $tmp2[0] = "|/usr/local/bin/php";
        $tmp2[1] = "/usr/local/bin/scanv.php";
        $tmp2[2] = $spam_level;
        $tmp2[3] = $spam_save_due;


        if(trim($forward_email2) != '' && $forward_copy == 'N'){
            $tmp2[4] = "fwd";
        }
        else{
            $tmp2[4] = "no";
        }

        $tmp2[5] = $offsend_start;
        $tmp2[6] = $offsend_end;
        if($offsend_start)
            $tmp2[7] = ".vbody";

        $scanv = implode(" ",$tmp2);
        write_file(QMAIL_PATH, $scanv."\n".$forward_email2, 'w');

        $exec_cmd = "chown -R nobody:nobody ".QMAIL_PATH;
        exec_helper($exec_cmd);

        $exec_cmd = "chmod 0600 ".QMAIL_PATH;
        exec_helper($exec_cmd);
    }
}

function gzipencode($str)
{
    return '!Z!' . base64_encode(gzcompress($str));
}

function gzipdecode($str)
{
    if (substr($str, 0, 3) == '!Z!')
        return gzuncompress(base64_decode(substr($str, 3)));
    return $str;
}

// 메일 백업 User-Agent
function file_get($url)
{
    $opts = array(
        'http'   => array(
            'header' => "User_Agent: Mailplug_mailbackup \r\n"
        )
    );
    $context = stream_context_create($opts);
    $lists = @file_get_contents($url, false, $context);
    return $lists;
}

if ( ! function_exists('get_real_IP'))
{
    /**
     * PHP :: function get_real_IP / 실제 IP 찾기
     */
    function get_real_IP()
    {
        $ip = null;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])){ // check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}

if ( ! function_exists('get_IP'))
{
    /**
     * PHP :: function get_IP / IP 정보 얻어오기
     */
    function get_IP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $ip2 = null;
        $p_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];   // 방화벽 + 사설 IP
        $r_ip = $_SERVER['HTTP_CLIENT_IP'];         // 방화벽 + 공인 IP

        if ( !! $p_ip)
        {
            $ip2 = $p_ip; // private IP
        }
        else if ( !! $r_ip)
        {
            $ip2 = $r_ip; // registered IP
        }

        return array(
            'ip' => $ip,
            'ip2' => $ip2,
        );
    }
}

function token_get($cid,$domain)
{
    if(ENVIRONMENT != 'production' && substr($_SERVER['REMOTE_ADDR'],0,10) == "192.168.10"){
        $token = md5($cid.OFFICE_IP.time().$domain);
    }else{
        $token = md5($cid.$_SERVER['REMOTE_ADDR'].time().$domain);
    }
    foreach(range(1,3) as $k) {
        $token = hash("sha256", $token."#!@##");
    }
    return $token;
}

function token_set($cid, $domain, $time_salt)
{
    if(ENVIRONMENT != 'production' && substr($_SERVER['REMOTE_ADDR'],0,10) == "192.168.10"){
        $token = md5($cid.OFFICE_IP.$time_salt.$domain);
    }else{
        $token = md5($cid.$_SERVER['REMOTE_ADDR'].$time_salt.$domain);
    }
    foreach(range(1,3) as $k) {
        $token = hash("sha256", $token."#!@##");
    }
    return $token;
}

// 10 동안만 유효성 체크
function token_check($cid, $domain, $token)
{
    $time_salt = time()+10;
    $chk = false;
    foreach(range(0,20) as $k) {
        if(token_set($cid, $domain, ($time_salt-$k)) == $token){
            $chk = true;
            break;
        }
    }
    return $chk;
}

function salt_add()
{
    $intermediateSalt = md5(uniqid(rand()));
    $salt = substr($intermediateSalt, 0, 25);
    return $salt;
}

function get_usersalt($id)
{
    $saltpath = _DOMAINPATH."/".$id."/_CONTROL/USERSALT";
    if(file_exists($saltpath)) {
        return trim(file_get_contents($saltpath));
    }else{
        return make_usersalt($id);
    }
}

function make_usersalt($id)
{
    $CI =& get_instance();
    $CI->load->helper('file');

    $u_salt = hash('sha512', salt_add().$id);
    write_file(_DOMAINPATH."/".$id."/_CONTROL/USERSALT", $u_salt, 'w');
    return $u_salt;
}

/*
function pwd_change($id, $pwd, $mode='', $salt="")
{
    if(!trim($id)){
        return;
        exit;
    }
    if(!trim($pwd)){
        return;
        exit;
    }

    if($mode != 'md5') {
        $pwd = md5($pwd);
    }
    $u_salt = get_usersalt($id);
    if($salt != ''){
        $u_salt = $salt;
    }
    $passwd = $id.$pwd;

    foreach(range(1,3) as $k) {
        $passwd = hash("sha512", $passwd.$u_salt);
    }
    return $passwd;
}
*/

function pwd_change($id, $pwd, $mode='', $salt="")
{
    if(!trim($id)){
        return;
    }
    if(!trim($pwd)){
        return;
    }
    if($salt == "")
    {
        $salt = config_item('encryption_key');
    }
    $u_salt = get_usersalt($id);

    if($mode != 'md5') {
        $pwd = md5($pwd);
    }

    foreach(range(1,3) as $k) {
        $pwd = hash("sha512", $pwd.$salt."%$#@!");
    }
    return $pwd;
}

function pwd_check($pwd = '')
{
    if(!trim($pwd)){
        return;
    }
    $salt = config_item('encryption_key');

    foreach(range(1,3) as $k) {
        $pwd = hash("sha512", $salt.$pwd);
    }
    return $pwd;
}

function get_campaign($type="banner", $lang = "kr", $cache_name="", $bn_code="")
{
    $CI =& get_instance();
    $CI->load->helper('file');

    if(_SERVER_TYPE == 'ASP' || _SERVER_TYPE == 'PRU') return;
    if (ENVIRONMENT == 'development') {
        $expire_timestamp = '60';
        return '';
    } else {
        $expire_timestamp = '3600';
    }

    if($lang != "kr")
        $lang = "en";

    if($type == "banner"){
        if($bn_code == "")
            return '';
        $url = "_".$bn_code;
    }else{
        $url = "/".$CI->uri->segment('1')."/".$CI->uri->segment('2');
    }
    if(!$cache_name)
        $cache_name = $url;
    $cache_file = _TMPDOMAIN.$type.str_replace("/","_",$cache_name)."_".$lang;
    if(!file_exists($cache_file)) {
        $cache_fwrite = true;
    } else {
        $filetime = filemtime($cache_file);
        if($filetime && $filetime < (time() - $expire_timestamp)) {
            @unlink($cache_file);
            $cache_fwrite = true;
        }
    }
    if(is_dir(_DOMAINPATH)){
        $goods = get_code_name('server','goods');
        $users = get_code_name('server','users');
        $user_quota = get_code_name('server','user_quota');
    }else{
        $goods = "MAX2009";
        $users = "5";
        $user_quota = "1024";
    }

    if($cache_fwrite){
        $params = array(
            'type' => $type,
            'bn_code' => $bn_code,
            'host' => _HOST,
            'lang' => $lang,
            'domain' => _DOMAIN,
            'goods' => $goods,
            'usrs' => $users,
            'user_quota' => $user_quota,
            'referer' => $url,
        );
        define('CAMPAIGN_URL', vars('campaign') . '?' . http_build_query($params));
        $campaign_data = file_get_contents(CAMPAIGN_URL);
        if(is_dir(_DOMAINPATH)){
            write_file($cache_file, $campaign_data, 'w');
        }
    }else{
        $campaign_data = file_get_contents($cache_file);
    }
    return $campaign_data;
}

// 악성태그 변환
function bad_tag_convert($code)
{
    return preg_replace("/\<([\/]?)(script|iframe)([^\>]*)\>/i", "&lt;$1$2$3&gt;", $code);
}

//배포된 고객과 배포되지 않은 고객 company 캘린더에 추가된 사용자가 없으면 보여주지 않음
function company_cal_unset($cal_array)
{
    $CI =& get_instance();
    $CI->load->model('db_calendars');
    $comp_cal_auth = $CI->db_calendars->calendar_auth_list(array(
        "calendar_seq"  => '1'
    ));
    if(!$comp_cal_auth){
        $get_schedule = $CI->db_calendars->get_schedule(array(
            "where" => array("calendar_seq"  => "1")
        ));
        if(count($get_schedule)){
            $CI->db_calendars->calendar_auth_insert(array(
                'calendar_seq' => '1',
                'auth_type' => 'all'
            ));
        }else{
            foreach((array)$cal_array as $key => $val){
                if($val['owner_seq'] == 1 && $val['owner_user_id'] == 'postmaster'){
                    unset($cal_array[$key]);
                }
            }

            // Left Menu 배열 숫자 0부터 따지므로 추가
            $temp_array = array();
            foreach((array)$cal_array as $key => $val){
                $temp_array[] = $val;
            }
            $cal_array = $temp_array;
        }
    }
    return $cal_array;
}

// 첨부 파일 저장 하기
function attach_sha1_link($tmp_file)
{
    $sha1_name = sha1_file($tmp_file);
    $sub1 = substr($sha1_name,0,2);
    //$sub2 = substr($sha1_name,2,2);
    //sha1 값 못 구함
    if(!trim($sha1_name) || !file_exists($tmp_file)){
        return false;
    }
    /* 단독이면서 이중화 쓰지 않을 경우 ( 로그인 페이지에 이미 있음 )
    if(trim(file_get_contents('/data/files/glusterattach')) != 1 || file_exists(_SCHEMA.'etc/noattach'))
    {
        if(in_array(substr(_HOST,0,2), array('ma', 'mb', 'mc')))
        {
            $sid = get_code_name('config','CUSTOM_sid');
            if(!$sid){
                $sid = file_get_contents(vars('sid_url')._HOST.'/'._DOMAIN);
                if($sid > 0){
                    $this->load->model('db_domain');
                    $this->db_domain->update_config('CUSTOM', 'sid', $sid,'');
                }
            }
            $_attachtmp = '/home/mail/_ATTACHTMP/'.$sid;
            if(!is_dir($_attachtmp)){
                @exec('mkdir -p '.$_attachtmp);
                @chown($_attachtmp, 'nobody');
                @chgrp($_attachtmp, 'nobody');
            }
            if(!is_link('/home/mail/domaindir/'._DOMAIN.'/_ATTACHTMP')){
                symlink($_attachtmp, '/home/mail/domaindir/'._DOMAIN.'/_ATTACHTMP');
            }
        }
    }
    */
    //디렉토리 생성
    if(!is_dir(_ATTACHPATH.$sub1.'/')){
        // @exec('mkdir -p '._ATTACHPATH.$sub1.'/');
        $exec_cmd = 'mkdir -p '._ATTACHPATH.$sub1.'/';
        exec_helper($exec_cmd);
    }
    //파일 복사
    if(!file_exists(_ATTACHPATH.$sub1.'/'.$sha1_name)){
        @rename($tmp_file, _ATTACHPATH.$sub1.'/'.$sha1_name);
    }else{
        @unlink($tmp_file);
    }
    if(file_exists(_ATTACHPATH.$sub1.'/'.$sha1_name))
        return $sha1_name;
    else
        return false;
}

//이중화 관련 모든 파일 읽을때 경로 만들기
function make_file_path($filename)
{
    $sub1 = substr($filename,0,2);
    $sub2 = substr($filename,2,2);
    $tmp_name = explode("-", $filename);
    $path[0] = "/:Attach:/{$sub1}/{$tmp_name[0]}";
    $path[1] = "/_ATTACHTMP/{$sub1}/{$tmp_name[0]}";
    $path[2] = "/_ATTACHTMP/{$sub1}/{$sub2}/hashes/{$tmp_name[0]}";
    $path[3] = "/_ATTACH/{$sub1}/{$tmp_name[0]}";
    $path[4] = "/_ATTACH/{$sub1}/{$sub2}/hashes/{$tmp_name[0]}";
    $path[5] = "/_COLDATTACH/{$sub1}/{$sub2}/{$tmp_name[0]}";

    foreach((array)$path as $val){
        if(file_exists(_DOMAINPATH.$val)) {
            $filepath = _DOMAINPATH.$val;
            break;
        }
    }

    //if(!file_exists($filepath)) {
    //  $sid = get_code_name('config','CUSTOM_sid');
    //  if(!$sid){
    //      $sid = file_get_contents(vars('sid_url')._HOST."/"._DOMAIN);
    //      if($sid > 0){
    //          $CI =& get_instance();
    //          $CI->load->model('db_domain');
    //          $CI->db_domain->update_config("CUSTOM", "sid", $sid,"");
    //      }else{
    //          return "sid error";
    //      }
    //  }
    //  $filepath = _DOMAINPATH."/_COLDATTACH/{$sub1}/{$sub2}/{$tmp_name[0]}.{$sid}";
    //}

    return $filepath;
}

function file_upload_html5_agent($type="")
{
    $user_agent = get_user_agent();
    if(strstr($user_agent,"Internet Explorer")){
        //$user_agent = str_replace("Internet Explorer","",$user_agent);
        $user_agent = trident_check();
        $variable_num = ($type == 'skin'? 8 : 9);
        if(trim($user_agent) <= $variable_num){
            return true;
        }
    }
    return false;
}

function file_upload_html5_select()
{
    if(file_upload_html5_agent()){
        $file_upload = "../skin11_common/file_upload.html";
    }else{
        if(_MOBILE == "mobile") {
            $file_upload = "../skin11_common/file_upload_html5.html";
        } else{
            $file_upload = "../skin11_common/file_upload_html5_new.html";
        }
    }

    return $file_upload;
}

function trident_check()
{
    preg_match('/Trident\/\d{1,2}.\d{1,2};?/', $_SERVER['HTTP_USER_AGENT'], $matches);
    $trident = preg_replace("@Trident\/|;@","",$matches[0]);
    switch($trident){
        case '4.0':
            $user_agent = '8.0';
            break;
        case '5.0':
            $user_agent = '8.0';
            break;
        case '6.0':
            $user_agent = '10.0';
            break;
        case '7.0':
            $user_agent = '11.0';
            break;
        default:
            $user_agent = '7.0';
            break;
    }
    return $user_agent;
}

function get_auth_type()
{
    $goods = get_code_name('server', 'goods');
    switch($goods){
        case "TYPE1_DEDI":
        case "TYPE2_DEDI":
        case "TYPE3_DEDI":
            $type = "auth";
            break;
        default:
            $type = "normal";
            break;
    }
    return $type;
}

// 전체 권한 가져오기
function get_auth_user($mode, $user_seq="", $type="user")
{
    $CI =& get_instance();

    if($user_seq == '')
        $user_seq = $CI->session->userdata('sess_id');

    if($user_seq == '')
    {
        return FALSE;
    }

    $CI->load->model('ea_auth');
    $auth = $CI->ea_auth->get_auth($user_seq, $type);

    // postmaster
    if ($CI->session->userdata('sess_priv') == 'postmaster')
    {
        $auth['au_master'] = '1';
    }

    // DEV-1536 숨기기
//  if($mode == 'au_master')
//    {
//        $get_ip = function($arr)
//        {
//            return $arr['value'];
//        };
//        $CI->load->model('db_domain');
//        $allow_ip_list = $CI->db_domain->get_admin_addr_all();
//        $allowed_ip = array_map($get_ip, $allow_ip_list);
//        if(!empty($allowed_ip))
//        {
//            $ip = get_real_IP();
//            if(in_array($ip, $allowed_ip) || $ip == OFFICE_IP)
//            {
//                $auth['au_master'] = 1;
//            }
//            else
//            {
//                $auth['au_master'] = 0;
//            }
//        }
//    }
    return (isset($auth[$mode]) ? $auth[$mode] : 0);
}

// 사용자의 timezone이 GMT와 얼마나 차이나는지를 return
function get_gmt_diff()
{
    $CI =& get_instance();

    $res = array();

    // mktime과 gmmktime에 사용되는 날짜는 아무 값이나 상관없다 (단, 동일한 날짜를 입력해야 함)
    $res['server_offset'] = (gmmktime(0, 0, 0, 9, 9, 2016) - mktime(0, 0, 0, 9, 9, 2016)) / 60;

    $uid = $CI->session->userdata('sess_cid');
    if($uid == "" or $uid == "postmaster")
    {
        // local timezone 사용

        $gmt = new DateTimeZone("GMT");
        $default_tz = ini_get("date.timezone");
        if ( $default_tz == "" ) {
            $default_tz = "Asia/Seoul";
        }
        // $tz_local = new DateTimeZone(ini_get("date.timezone"));
        $tz_local = new DateTimeZone($default_tz);
        $offset = $tz_local->getOffset(new DateTime("now", $gmt));

        $res['client_offset'] = $offset / 60;
    }
    else
    {
        // 사용자설정 화면에서 저장한 timezone 사용

        $CI->load->model('dbu_config');

        $tz_config = $CI->dbu_config->get_config("timegmt", "CONFIG");
        $offset = $tz_config['value'];

        $res['client_offset'] = $offset * 60;
    }

    return $res;
}

/**
 * unix timestamp를 datetime 형태로 변환
 *
 * @param   string  $date_format    datetime의 format
 * @param   int $unix_timestamp 변환하려는 timestamp 값; 생략하면 현재 시각의 timestamp를 사용함
 * @return  datetime
 */
function time2date($date_format, $unix_timestamp = false)
{
    if ($unix_timestamp === false)
    {
        $unix_timestamp = time();
    }

    $gmt_diff = get_gmt_diff();

    $offset_diff = $gmt_diff['client_offset'] - $gmt_diff['server_offset'];
    $tz_correction = "0 minute";

    if ($offset_diff < 0)
    {
        $tz_correction = "{$offset_diff} minute";
    }
    else
    {
        $tz_correction = "+{$offset_diff} minute";
    }

    return date($date_format, strtotime($tz_correction, $unix_timestamp));
}

/**
 * datetime을 unix timestamp 형태로 변환
 *
 * @param   datetime    $datetime
 * @return  unix timestamp
 */
function date2time($datetime)
{
    // strtotime 함수에서 [Y.m.d] 형식 날짜를 인식하지 못해서, 구분자를 '-'로 수정
    $datetime = preg_replace("/\./", "-", $datetime);

    $gmt_diff = get_gmt_diff();

    $offset_diff = $gmt_diff['server_offset'] - $gmt_diff['client_offset'];
    $tz_correction = "0 minute";

    if ($offset_diff < 0)
    {
        $tz_correction = "{$offset_diff} minute";
    }
    else
    {
        $tz_correction = "+{$offset_diff} minute";
    }

    return strtotime($tz_correction, strtotime($datetime));
}

//메일 보낼때 이미지 있는지 체크
function image_check($filename)
{
    $filename = basename($filename);

    if(strstr($filename, "|")) {
        $tmpImagePath = explode("|", $filename);
        // 메일 : ucgkorea.com|monicahan|1404|0818332001397465473 (구)
        $tmpImagePath = explode("|",str_replace("//","/",$filename));
        $tmp[1] = _DOMAINPATH."/".strtolower($tmpImagePath[1])."/:Attach:/{$tmpImagePath[2]}/{$tmpImagePath[3]}";

        if($tmp[1] && file_exists($tmp[1])){
            $imagePath = $tmp[1];
        }else{
            //(구) 위 1차 확인 후에 없으면 디비 셀렉트
            $nName = basename($tmp[1]);
            $attachDbPath = _DOMAINPATH."/".strtolower($tmpImagePath[1])."/_DBS/attach.dbs";

            if(file_exists($attachDbPath)){
                $value = "";
                $sql = new SQLite3($attachDbPath);
                $sql->busyTimeout(10000);
                $userQuery = $sql->query("select foldername from attachinfo where newfilename = '".$nName."';");
                if($userQuery){
                    $data = $userQuery->fetchArray();
                    if($data[0]){
                        $value = $data[0];
                    }
                }

                $sql->close();
                $imgName = $value;

                if($imgName){
                    $filename = $imgName;
                    $imagePath = _DOMAINPATH."/:Attach:/{$imgName[0]}{$imgName[1]}/{$imgName}";
                }
            }
        }
    }

    // 93f6af9d2fcad76214737023e605e576d93551ef (신)
    // 93f6af9d2fcad76214737023e605e576d93551ef-1234567890 (신)
    if($filename != '' && (!isset($imagePath) || !file_exists($imagePath))){
        $imagePath = make_file_path($filename);
    }
    return $imagePath;
}

//유저별 complete 정보 캐시 통일
function auto_complete_cache()
{
    $CI =& get_instance();
    $CI->load->helper('file');
    ## 자동완성 파일캐시갱신
    $cache_file = _TMPUSER.'complete';
    $cache_tmp  = _TMPUSER.'auto_complete';
    $cache_fwrite = false;
    if(!file_exists($cache_file)){
        $cache_fwrite = true;
    }else{
        $filetime = filemtime($cache_file);
        if($filetime && $filetime < (time() - 600)) {
            @unlink($cache_file);
            $cache_fwrite = true;
        }
    }
    if($cache_fwrite){
        $CI->load->library('user');
        $ac_array = $CI->user->auto_complete_user_input();
        $cache_list = "<?php\n\$ac_array=".var_export($ac_array, true).'?'.'>';
        write_file($cache_tmp, $cache_list, 'w');
        if(file_exists($cache_tmp)){
            rename($cache_tmp, $cache_file);
        }
    }
    include($cache_file);
    return $ac_array;
}

// base64 encode, decode
function get_base64_convert($data, $flag, $key = "")
{
    $CI =& get_instance();
    if(!$key)
        $key = $CI->config->item('encryption_key');

    if ($flag == "encode")
    {
        $res = base64_encode(openssl_encrypt($data, "aes-256-cbc", md5($key), true, str_repeat(chr(0), 16)));
    }
    else
    {
        $res = openssl_decrypt(base64_decode($data), "aes-256-cbc", md5($key), true, str_repeat(chr(0), 16));
    }

    return $res;
}
//웹메일 레프트 카운트 캐시
function left_folder_cache()
{
    $CI =& get_instance();
    $CI->load->helper('file');

    // $id = rawurlencode(get_code_name('user', 'id'));
    $id = rawurlencode($CI->session->userdata('sess_cid')); // CWE-862

    //$userindexdbs = filemtime(_DOMAINPATH.'/'.$id.'/_DBS/index.dbs');
    $path = _DOMAINPATH.'/'.$id.'/_DBS/index.dbs';
    clearstatcache($path);
    // $dateUnix = shell_exec('stat --format "%y" '.$path);
    $exec_cmd = 'stat --format "%y" ' . $path;
    $dateUnix = shell_exec_helper($exec_cmd);

    $date = explode(".", $dateUnix);
    $userindexdbs = filemtime($path).".".substr($date[1], 0, 4);

    $left_cache_file = _TMPUSER.'mail_left_count';
    $left_cache_html = _TMPUSER.'mail_left_html';
    if($CI->folder_count_time != $userindexdbs || !file_exists($left_cache_file) || !file_exists($left_cache_html)){
        # 폴더 리스트 가져오기
        $CI->load->model('dbu_index');
        $folder_list = $CI->dbu_index->folder_list(array(
            'sst' => 'orderno',
            'sod' => 'asc'
        ));

        # 폴더별 언리드 카운트 가져오기
        $folder_num_array = array_column($folder_list, 'num');
        $left_cache_tmp  = _TMPDOMAIN . basename('mail_count_' . $id);
        $folder_count = $CI->dbu_index->mail_all_count($folder_num_array);
        $folder_count = array_key_change($folder_count, 'folder_num');

        $total_unread_count = 0; // 전체 언리드 카운트 구하기
        foreach ($folder_count as $key => $value)
        {
            if (!$value['unread'])
            {
                $folder_count[$key]['unread'] = 0;
            }
            if (!in_array($value['folder_num'], array(2, 3, 4, 5)))
            {
                $total_unread_count += $value['unread'];
            }
        }
        $folder_count['all_mail']['unread'] = $total_unread_count;
        $folder_count['my'] = (isset($folder_count[0]) ? $folder_count[0] : null);
        $other = $CI->dbu_index->mail_folder_count(array(
            "select" => "count(a.mail_read) as mail_count, a.mail_read",
            "__not_folder_num" => 3,
            "open" => 'Z',
            "group" => 'mail_read'
        ));
        foreach($other as $v){
            if(!is_numeric($v['mail_count'])) $v['mail_count'] = 0;
            if($v['mail_read'] == 'N') {
                $folder_count['other']['unread'] = $v['mail_count'];
            }else{
                $folder_count['other']['etc'] = $v['mail_count'];
            }
        }

        $star = $CI->dbu_index->mail_folder_count(array(
            "select" => "count(a.mail_read) as mail_count, a.mail_read",
            "category" => 'STAR',
            "group" => 'mail_read'
        ));
        foreach($star as $v){
            if(!is_numeric($v['mail_count'])) $v['mail_count'] = 0;
            if($v['mail_read'] == 'N') {
                $folder_count['star']['unread'] = $v['mail_count'];
            }else{
                $folder_count['star']['etc'] = $v['mail_count'];
            }
        }

        # 내 폴더 리스트만 가지고 있는 배열 만들기
        $my_folder_list = array();
        if (count($folder_list) > 5)
        {
            foreach ($folder_list as $key => $value)
            {
                if ($value['num'] >= 6)
                {
                    $value['folder_name'] = htmlspecialchars($value['folder_name']);
                    $value['dir_name'] = $value['dir_name'];
                    $my_folder_list[$key] = $value;
                }
            }

            $myfolder_unread_cnt = 0;
            foreach (array_column($my_folder_list, 'num') as $key)
            {
                if(isset($folder_count[$key]))
                {
                    if(!is_numeric($folder_count[$key]['unread']))  {
                        $folder_count[$key]['unread'] = 0;
                    }
                    $myfolder_unread_cnt += $folder_count[$key]['unread'];
                }
            }
            $folder_count['my_folder']['unread'] = $myfolder_unread_cnt;
        }
        $folder_count['my_folder_list'] = $my_folder_list;

        //unread 카운트가 하나도 없을 경우 스크립트 에러 방지
        foreach (array('0', '1', '3', '4', '5', 'my_folder', 'all_mail') as $val)
        {
            if(!isset($folder_count[$val])) $folder_count[$val]['unread'] = 0;
        }

        $cache_list = "<?php\n\$folder_count=".var_export($folder_count, true).'?'.'>';
        write_file($left_cache_tmp, $cache_list, 'w');
        if (file_exists($left_cache_tmp))
            rename($left_cache_tmp, $left_cache_file);

        # 개인파일함 권한
        $userpds_auth = false;
        $userpds = get_code_name('config', 'CUSTOM_UserPDSUse');
        $person_file_manager = get_code_name('server', 'personFileManager');
        if($userpds === 'Y' || $person_file_manager === 'Y')
            $userpds_auth = true;
        if($userpds === 'N')
            $userpds_auth = false;

        setcookie('folder_count_time', $userindexdbs, vars('COOKIE_EXPIRE'), '/webmail', "", "", true);
        if(_MOBILE !== 'mobile')
        {
            $CI->load->model('db_approval_mail');
            // 승인대기 메일 리스트
            $approval_mail_list = (array) $CI->db_approval_mail->mailindex_list(array(
                'am_count' => $CI->session->userdata('sess_id'),
                'am_status' => 'W',
            ));
            $CI->tpl->assign(array(
                'folder_count'    => $folder_count,
                'my_folder_list'  => $folder_count['my_folder_list'],
                'userpds_auth'    => $userpds_auth,
                'approval_mail_count' => count($approval_mail_list)
            ));
            $CI->tpl->define(array(
                'folder_count_cache'  =>  'webmail/left_ajax.html',
            ));
            $CI->tpl->template_dir = FCPATH . 'views/wm60/skin11/';
            $_left = $CI->tpl->fetch('folder_count_cache');

            //left html cache
            write_file(_TMPUSER . 'mail_left_html', $_left, 'w');

            return true;
        }
    }
}

/**
 * get_token
 */
if ( ! function_exists('get_token'))
{
    function get_token()
    {
        $CI =& get_instance();

        $token_name = $CI->security->get_csrf_token_name();
        $token = $CI->input->cookie($token_name);

        if ( ! $token)
        {
            $token = $CI->security->get_csrf_hash();
        }

        return $token;
    }
}

if ( ! function_exists('get_elapsed'))
{
    /**
     * 경과일 구하기
     *
     * @param   reminder    - 나머지 처리 (floor, ceil, round)
     */
    function get_elapsed($after_time = false, $before_time = false, $unit = 'day', $reminder = 'floor')
    {
        $unit_range = array(
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1,
        );

        $reminder_range = array('floor', 'ceil', 'round'); // 버림, 올림, 반올림

        // check require param
        if (( ! array_key_exists($unit, $unit_range)) || ( ! in_array($reminder, $reminder_range)) || ( ! $after_time) || ( ! $before_time) || ($after_time < $before_time))
        {
            return false;
        }

        $time_sub = $after_time - $before_time;
        $time_interval = ($time_sub < 1) ? 1 : $time_sub;

        $interval = floor($time_interval / $unit_range[$unit]);
        if ($reminder === 'ceil')
        {
            $interval = ceil($time_interval / $unit_range[$unit]);
        }
        else if ($reminder === 'round')
        {
            $interval = round($time_interval / $unit_range[$unit]);
        }

        return $interval;
    }
}

/**
 * fpc : file_put_contents의 첫 글자 조합
 *
 * ajax로 데이터를 주고받을 때
 * json으로 데이터를 넘겨야 하는 경우 p_r() 함수로 디버그할 수 없어서,
 * 같은 용도로 사용하기 위해서 만듦
 */
if ( ! function_exists('fpc'))
{
    function fpc($content, $label = '')
    {
        $CI =& get_instance();
        $CI->load->helper('file');

        ob_start();
        if (is_array($content))
        {
            print_r($content);
        }
        else
        {
            echo $content;
        }
        $obc = ob_get_contents();
        ob_end_clean();

        if ($label === '')
        {
            $label = 'CONTENT';
        }
        write_file('/tmp/log/dev_log', "{$label} = [{$obc}]".PHP_EOL, 'a');
    }
}

/**
 * disconnected_user_refresh
 *
 * jstree와 연결이 끊어진 사용자를 처리한다
 *
 * 조직도/트리 개선 이슈(wm60-182)로 인해 jstree가 바뀌면서,
 * 트리의 노드 삭제 시, 해당 노드(와 그 자손 노드들)에 연결된 사용자들을 업데이트(또는 삭제) 해주기 위해 만듦
 */
if ( ! function_exists('disconnected_user_refresh'))
{
    function disconnected_user_refresh($tree_type)
    {
        $CI =& get_instance();

        $CI->load->library('user');
        $CI->load->model('db_address');
        $CI->load->model('db_domain');
        $CI->load->model('db_users');

        switch ($tree_type)
        {
            case 'personal_address':
            {
                // 개인 주소록 : 삭제

                $query_data = array(
                    'groupname__array' => array('__SELFADDRESS__'),
                    'grouptype' => 'self',
                    'masterids' => $CI->session->userdata('sess_cid'),
                );
                $root_info = (array) $CI->db_address->groups_new_list($query_data);
                $personal_root_seq = $root_info[0]['seq'];

                $disc_users = (array) $CI->db_address->disconnected_users('self', $CI->session->userdata('sess_cid'));

                foreach ($disc_users as $idx => $row)
                {
                    $query_data = array(
                        'seq' => $row['seq'],
                    );
                    $CI->db_address->relaction_delete($query_data);
                }
                break;
            }
            case 'public_address':
            {
                // 공용 주소록 : 삭제

                $query_data = array(
                    'groupname__array' => array('__COMMADDRESS__'),
                    'grouptype' => 'comm',
                );
                $root_info = (array) $CI->db_address->groups_new_list($query_data);
                $public_root_seq = $root_info[0]['seq'];

                $disc_users = (array) $CI->db_address->disconnected_users('comm');

                foreach ($disc_users as $idx => $row)
                {
                    $query_data = array(
                        'seq' => $row['seq'],
                    );
                    $CI->db_address->relaction_delete($query_data);
                }
                break;
            }
            case 'organ':
            {
                // 조직도 : '소속없음'으로 이동

                $query_data = array(
                    'sosokname' => '__NOSOSOK__',
                );
                $nososok_info = (array) $CI->db_users->sosok_new_list($query_data, true);
                $nososok_seq = $nososok_info[0]['seq'];

                $disc_users = (array) $CI->db_users->disconnected_users();

                foreach ($disc_users as $idx => $row)
                {
                    $query_data = array(
                        'sosok' => $nososok_seq,
                        'where' => array(
                            'seq' => $row['seq'],
                            'u_priv' => 'user',
                        ),
                    );
                    $CI->db_users->users_update($query_data);

                    // eas & hrm sync
                    $query_data = array(
                        'id' => $row['id'],
                    );
                    $CI->user->user_update($query_data, _DOMAIN);
                }

                // 대표메일 계정은 삭제 처리

                $query_data = array(
                    'select' => 'a.seq as u_seq,a.id, a.name , a.sosok, b.seq, b.sosokname',
                    'active' => 'Y',
                    'mbox_host' => _DOMAIN,
                    'u_priv' => 'mailer',
                );
                $represent_list = (array) $CI->db_users->find($query_data);
                $represent_list = $represent_list['res'];

                foreach ($represent_list as $row)
                {
                    if ($row['sosok'] > 0 && $row['seq'] == '')
                    {
                        $CI->user->user_delete($row['id']);
                    }
                }
                break;
            }
            case 'public_group':
            {
                // 공용그룹 : 삭제

                $disc_users = (array) $CI->db_domain->disconnected_users();

                foreach ($disc_users as $idx => $row)
                {
                    $query_data = array(
                        'organ_seq' => $row['organ_seq'],
                        'user_id' => $row['user_id'],
                    );
                    $CI->db_domain->oju_delete($query_data);
                }
                break;
            }
            default:
            {
                break;
            }
        }
    }
}

/**
 * newnode_similar_count
 *
 * 트리에 노드 추가 시, 이름이 '새 이름'이거나 '새 이름(#)' 형태인 것의 개수를 센다
 * 다국어 별로 독립적임 (단, sess_lang 기준이 아니고, 실제 등록된 문구 기준)
 */
if ( ! function_exists('newnode_similar_count'))
{
    function newnode_similar_count($tree_type)
    {
        $CI =& get_instance();

        $CI->load->model('db_address');
        $CI->load->model('db_domain');
        $CI->load->model('db_users');

        $similar_count = 0;

        switch ($tree_type)
        {
            case 'personal_address':
            {
                $query_data = array(
                    'groupname__like' => langs('new_node').'%',
                    'grouptype' => 'self',
                    'masterids' => $CI->session->userdata('sess_cid'),
                );
                $res = (array) $CI->db_address->groups_new_list($query_data);

                $similar_names = preg_grep('/^'.langs('new_node').'(\(\d+\))?$/', array_column($res, 'groupname'));
                $similar_count = count($similar_names);

                break;
            }
            case 'public_address':
            {
                $query_data = array(
                    'groupname__like' => langs('new_node').'%',
                    'grouptype' => 'comm',
                );
                $res = (array) $CI->db_address->groups_new_list($query_data);

                $similar_names = preg_grep('/^'.langs('new_node').'(\(\d+\))?$/', array_column($res, 'groupname'));
                $similar_count = count($similar_names);

                break;
            }
            case 'organ':
            {
                $query_data = array();
                $res = (array) $CI->db_users->sosok_new_list($query_data, true);

                $similar_names = preg_grep('/^'.langs('new_node').'(\(\d+\))?$/', array_column($res, 'sosokname'));
                $similar_count = count($similar_names);

                break;
            }
            case 'public_group':
            {
                $query_data = array(
                    'setname__like' => langs('new_node').'%',
                );
                $res = (array) $CI->db_domain->setorgan_new_list($query_data);

                $similar_names = preg_grep('/^'.langs('new_node').'(\(\d+\))?$/', array_column($res, 'setname'));
                $similar_count = count($similar_names);

                break;
            }
            default:
            {
                break;
            }
        }

        return $similar_count;
    }
}

/**
 * jstree_fix_root
 *
 * 루트노드가 없는 jstree에 루트노드를 추가한다
 *
 * @param   Boolean             always_adjust
 *          FALSE (default)     루트노드가 추가된 경우에만 노드 재배치
 *          TRUE                무조건 노드 재배치
 */
if ( ! function_exists('jstree_fix_root'))
{
    function jstree_fix_root($tree_type, $always_adjust = false)
    {
        $CI =& get_instance();

        $CI->load->model('db_address');
        $CI->load->model('db_domain');
        $CI->load->model('db_users');

        switch ($tree_type)
        {
            case 'personal_address':
            {
                $user_id = $CI->session->userdata('sess_cid');

                $query_data = array(
                    'groupname__array' => array('__SELFADDRESS__'),
                    'grouptype' => 'self',
                    'parentcode' => '0',
                    'masterids' => $user_id,
                );
                $root_list = (array) $CI->db_address->groups_new_list($query_data);

                if (count($root_list) !== 1)
                {
                    $sql_list = array();

                    // 루트노드 추가
                    $sql = "
                        INSERT INTO groups_new (
                            parentcode,
                            groupname,
                            groupdepth,
                            grouptype,
                            masterids,
                            num,
                            gruopdepthcount,
                            left,
                            right
                        ) VALUES (
                            '0',
                            '__SELFADDRESS__',
                            '0',
                            'self',
                            '{$user_id}',
                            '0',
                            '0',
                            '1',
                            '2'
                        )
                    ";
                    $res = $CI->db_address->cron_executer($sql);

                    // last insert id
                    $sql = "
                        SELECT LAST_INSERT_ROWID() AS last_id
                    ";
                    $res = $CI->db_address->cron_executer($sql);
                    $last_id = $res[0]['last_id'];

                    // (parent_seq === 0)인 노드들의 parent_seq를 업데이트
                    $sql = "
                        UPDATE groups_new
                        SET parentcode = '{$last_id}'
                        WHERE grouptype = 'self' AND masterids = '{$user_id}' AND parentcode = '0' AND seq != '{$last_id}'
                    ";
                    $sql_list[] = $sql;

                    // 사용자 정보 업데이트
                    $sql = "
                        UPDATE relaction
                        SET groupSeq = '{$last_id}'
                        WHERE groupSort = 'self' AND addressid = '{$user_id}' AND groupSeq = '0'
                    ";
                    $sql_list[] = $sql;

                    $CI->db_address->cron_executer($sql_list);

                    //---------------- 루트노드 추가 끝 ----------------//

                    if ($always_adjust === false)
                    {
                        jstree_adjust($tree_type);
                    }
                }

                if ($always_adjust === true)
                {
                    jstree_adjust($tree_type);
                }
                break;
            }
            case 'public_address':
            {
                $query_data = array(
                    'groupname__array' => array('__COMMADDRESS__'),
                    'grouptype' => 'comm',
                    'parentcode' => '0',
                );
                $root_list = (array) $CI->db_address->groups_new_list($query_data);

                if (count($root_list) !== 1)
                {
                    $sql_list = array();

                    // 루트노드 추가
                    $sql = "
                        INSERT INTO groups_new (
                            parentcode,
                            groupname,
                            groupdepth,
                            grouptype,
                            masterids,
                            num,
                            gruopdepthcount,
                            left,
                            right
                        ) VALUES (
                            '0',
                            '__COMMADDRESS__',
                            '0',
                            'comm',
                            'postmaster',
                            '0',
                            '0',
                            '1',
                            '2'
                        )
                    ";
                    $res = $CI->db_address->cron_executer($sql);

                    // last insert id
                    $sql = "
                        SELECT LAST_INSERT_ROWID() AS last_id
                    ";
                    $res = $CI->db_address->cron_executer($sql);
                    $last_id = $res[0]['last_id'];

                    // (parent_seq === 0)인 노드들의 parent_seq를 업데이트
                    $sql = "
                        UPDATE groups_new
                        SET parentcode = '{$last_id}'
                        WHERE grouptype = 'comm' AND parentcode = '0' AND seq != '{$last_id}'
                    ";
                    $sql_list[] = $sql;

                    // 사용자 정보 업데이트
                    $sql = "
                        UPDATE relaction
                        SET groupSeq = '{$last_id}'
                        WHERE groupSort = 'comm' AND groupSeq = '0'
                    ";
                    $sql_list[] = $sql;

                    $CI->db_address->cron_executer($sql_list);

                    //---------------- 루트노드 추가 끝 ----------------//

                    if ($always_adjust === false)
                    {
                        jstree_adjust($tree_type);
                    }
                }

                if ($always_adjust === true)
                {
                    jstree_adjust($tree_type);
                }
                break;
            }
            case 'organ':
            {
                $query_data = array(
                    'parentcode' => '0',
                    'sosokname__not' => '__NOSOSOK__',
                );
                $root_list = (array) $CI->db_users->sosok_new_list($query_data, true);

                if (count($root_list) !== 1)
                {
                    $sql_list = array();
                    $sosok_list = (array) $CI->db_users->cron_sosok_list();

                    // seq = seq + 1 업데이트는 중복제한 조건에 걸리는 문제가 있어서,
                    // 한 번에 수행하지 못하고, foreach 돌면서 내림차순으로 쿼리 제작
                    foreach ($sosok_list as $idx => $row)
                    {
                        $old_seq = $row['seq'];
                        $new_seq = 1 + $old_seq;

                        $sql = "
                            UPDATE sosok_new
                            SET seq = '{$new_seq}'
                            WHERE seq = '{$old_seq}'
                        ";
                        $sql_list[] = $sql;
                    }

                    // (parent_seq !== 0)인 노드들의 parent_seq를 하나씩 밀어준다
                    $sql = "
                        UPDATE sosok_new
                        SET parentcode = parentcode + 1
                        WHERE parentcode != '0'
                    ";
                    $sql_list[] = $sql;

                    // (parent_seq === 0)인 노드들의 parent_seq를 1로 업데이트
                    $sql = "
                        UPDATE sosok_new
                        SET parentcode = '1'
                        WHERE parentcode = '0'
                    ";
                    $sql_list[] = $sql;

                    // 사용자 정보 업데이트
                    $sql = "
                        UPDATE users
                        SET sosok = sosok + 1
                        WHERE sosok != ''
                            AND sosok >= '1'
                    ";
                    $sql_list[] = $sql;

                    // 루트노드 추가
                    $sql = "
                        INSERT INTO sosok_new (
                            seq,
                            parentcode,
                            sosokname,
                            sosokdepth,
                            masterids,
                            num,
                            left,
                            right
                        ) VALUES (
                            '1',
                            '0',
                            '".get_code_name('server', 'org_name')."',
                            '0',
                            '',
                            '0',
                            '1',
                            '2'
                        )
                    ";
                    $sql_list[] = $sql;

                    $CI->db_users->cron_executer($sql_list);

                    //---------------- 루트노드 추가 끝 ----------------//

                    if ($always_adjust === false)
                    {
                        jstree_adjust($tree_type);
                    }
                }

                if ($always_adjust === true)
                {
                    jstree_adjust($tree_type);
                }
                break;
            }
            case 'public_group':
            {
                $query_data = array(
                    'setname' => '__COMMGROUP__',
                    'parentcode' => '0',
                );
                $root_list = (array) $CI->db_domain->setorgan_new_list($query_data);

                if (count($root_list) !== 1)
                {
                    $sql_list = array();
                    $group_list = (array) $CI->db_domain->cron_group_list();

                    // seq = seq + 1 업데이트는 중복제한 조건에 걸리는 문제가 있어서,
                    // 한 번에 수행하지 못하고, foreach 돌면서 내림차순으로 쿼리 제작
                    foreach ($group_list as $idx => $row)
                    {
                        $old_seq = $row['seq'];
                        $new_seq = 1 + $old_seq;

                        $sql = "
                            UPDATE setOrgan_new
                            SET seq = '{$new_seq}'
                            WHERE seq = '{$old_seq}'
                        ";
                        $sql_list[] = $sql;
                    }

                    // (parent_seq !== 0)인 노드들의 parent_seq를 하나씩 밀어준다
                    $sql = "
                        UPDATE setOrgan_new
                        SET parentcode = parentcode + 1
                        WHERE parentcode != '0'
                    ";
                    $sql_list[] = $sql;

                    // (parent_seq === 0)인 노드들의 parent_seq를 1로 업데이트
                    $sql = "
                        UPDATE setOrgan_new
                        SET parentcode = '1'
                        WHERE parentcode = '0'
                    ";
                    $sql_list[] = $sql;

                    // 사용자 정보 업데이트
                    $query_data = array(
                        'sst' => 'organ_seq',
                        'sod' => 'desc',
                    );
                    $user_list = $CI->db_domain->oju_list($query_data);

                    // seq = seq + 1 업데이트는 중복제한 조건에 걸리는 문제가 있어서,
                    // 한 번에 수행하지 못하고, foreach 돌면서 내림차순으로 쿼리 제작
                    foreach ($user_list as $idx => $row)
                    {
                        $old_seq = $row['organ_seq'];
                        $new_seq = 1 + $old_seq;

                        $sql = "
                            UPDATE organ_join_user
                            SET organ_seq = '{$new_seq}'
                            WHERE organ_seq = '{$old_seq}'
                        ";
                        $sql_list[] = $sql;
                    }

                    // 루트노드 추가
                    $sql = "
                        INSERT INTO setOrgan_new (
                            seq,
                            setname,
                            orderno,
                            parentcode,
                            left,
                            right,
                            level
                        ) VALUES (
                            '1',
                            '__COMMGROUP__',
                            '0',
                            '0',
                            '1',
                            '2',
                            '0'
                        )
                    ";
                    $sql_list[] = $sql;

                    $CI->db_domain->cron_executer($sql_list);

                    //---------------- 루트노드 추가 끝 ----------------//

                    if ($always_adjust === false)
                    {
                        jstree_adjust($tree_type);
                    }
                }

                if ($always_adjust === true)
                {
                    jstree_adjust($tree_type);
                }
                break;
            }
            default:
            {
                break;
            }
        }
    }
}

/**
 * jstree_adjust
 *
 * jstree 노드 재배치
 * 기존의 jstree_fix_root 함수에서 노드 재배치하는 로직만 분리
 */
if ( ! function_exists('jstree_adjust'))
{
    function jstree_adjust($tree_type)
    {
        $CI =& get_instance();

        switch ($tree_type)
        {
            case 'personal_address':
            {
                $user_id = $CI->session->userdata('sess_cid');
                $lib_name = "ps_addr_tree_{$user_id}";

                $tree_data = array(
                    'dbs' => 'db_address',
                    'structure_table' => 'groups_new',
                    'data_table' => 'groups_new',
                    'data2structure' => 'seq',
                    'structure' => array(
                        'id' => 'seq',
                        'left' => 'left',
                        'right' => 'right',
                        'level' => 'groupdepth',
                        'parent_id' => 'parentcode',
                        'position' => 'gruopdepthcount', // 'gruop'는 오타가 아님, 테이블 정의가 그렇게 되어있음
                    ),
                    'data' => array(
                        'groupname',
                        'grouptype',
                        'masterids',
                        'num',
                        'ucount',
                    ),
                    'data_filter' => array(
                        'grouptype' => 'self',
                        'masterids' => $user_id,
                    ),
                );
                $CI->load->library('tree', $tree_data, $lib_name);
                $CI->{$lib_name}->adjust_tree(false);
                break;
            }
            case 'public_address':
            {
                $lib_name = "pb_addr_tree";

                $tree_data = array(
                    'dbs' => 'db_address',
                    'structure_table' => 'groups_new',
                    'data_table' => 'groups_new',
                    'data2structure' => 'seq',
                    'structure' => array(
                        'id' => 'seq',
                        'left' => 'left',
                        'right' => 'right',
                        'level' => 'groupdepth',
                        'parent_id' => 'parentcode',
                        'position' => 'gruopdepthcount', // 'gruop'는 오타가 아님, 테이블 정의가 그렇게 되어있음
                    ),
                    'data' => array(
                        'groupname',
                        'grouptype',
                        'masterids',
                        'num',
                        'ucount',
                    ),
                    'data_filter' => array(
                        'grouptype' => 'comm',
                    ),
                );
                $CI->load->library('tree', $tree_data, $lib_name);
                $CI->{$lib_name}->adjust_tree(false);
                break;
            }
            case 'organ':
            {
                $lib_name = "org_tree";

                $tree_data = array(
                    'dbs' => 'db_users',
                    'structure_table' => 'sosok_new',
                    'data_table' => 'sosok_new',
                    'data2structure' => 'seq',
                    'structure' => array(
                        'id' => 'seq',
                        'left' => 'left',
                        'right' => 'right',
                        'level' => 'sosokdepth',
                        'parent_id' => 'parentcode',
                        'position' => 'num',
                    ),
                    'data' => array(
                        'sosokname',
                        'masterids',
                        'ucount',
                    ),
                );
                $CI->load->library('tree', $tree_data, $lib_name);
                $CI->{$lib_name}->adjust_tree(false);

                $CI->load->model('ea_db');
                $CI->ea_db->sosok_copy();
                break;
            }
            case 'public_group':
            {
                $lib_name = "pb_group_tree";

                $tree_data = array(
                    'dbs' => 'db_domain',
                    'structure_table' => 'setOrgan_new',
                    'data_table' => 'setOrgan_new',
                    'data2structure' => 'seq',
                    'structure' => array(
                        'id' => 'seq',
                        'left' => 'left',
                        'right' => 'right',
                        'level' => 'level',
                        'parent_id' => 'parentcode',
                        'position' => 'orderno',
                    ),
                    'data' => array(
                        'setname',
                        'ids',
                        'idcount',
                        'regdate',
                        'sosokSeq',
                    ),
                );
                $CI->load->library('tree', $tree_data, $lib_name);
                $CI->{$lib_name}->adjust_tree(false);
                break;
            }
            default:
            {
                break;
            }
        }
    }
}

/**
 * 약속된 예약어 convert
 */
if ( ! function_exists('keyword_converter'))
{
    function keyword_converter($keyword = "", $type = "DEFAULT", $reverse = false)
    {
        $convert_keyword = $keyword;

        if ( ! $reverse)
        {
            if ($type === "DEFAULT")
            {
                // '__COMMADDRESS__' -> '공용 주소록'
                $convert_keyword = str_replace("__COMMADDRESS__", langs('useinfo_common_group'), $convert_keyword);

                // '__SELFADDRESS__' -> '개인 주소록'
                $convert_keyword = str_replace("__SELFADDRESS__", langs('useinfo_self_group'), $convert_keyword);

                // '__NOSOSOK__' -> '소속없음'
                $convert_keyword = str_replace("__NOSOSOK__", langs('no_group_item'), $convert_keyword);

                // '__COMMGROUP__' -> '공용그룹'
                $convert_keyword = str_replace("__COMMGROUP__", langs('mailaddresscommgroup'), $convert_keyword);
            }
        }
        else
        {
            if ($type === "DEFAULT")
            {
                // '공용 주소록' -> '__COMMADDRESS__'
                $convert_keyword = str_replace(langs('useinfo_common_group'), "__COMMADDRESS__", $convert_keyword);

                // '개인 주소록' -> '__SELFADDRESS__'
                $convert_keyword = str_replace(langs('useinfo_self_group'), "__SELFADDRESS__", $convert_keyword);

                // '소속없음' -> '__NOSOSOK__'
                $convert_keyword = str_replace(langs('no_group_item'), "__NOSOSOK__", $convert_keyword);

                // '공용그룹' -> '__COMMGROUP__'
                $convert_keyword = str_replace(langs('mailaddresscommgroup'), "__COMMGROUP__", $convert_keyword);
            }
        }

        return $convert_keyword;
    }
}

/**
 * 사용자 상태값 convert
 */
if ( ! function_exists('active_type_converter'))
{
    function active_type_converter($active_type, $type = "DEFAULT", $reverse = false)
    {
        $code2text = array(
            'D' => langs('deny'),
            'E' => langs('used_end'),
            'H' => langs('dormancy'),
            'N' => langs('userlist_item_inactive'),
            'W' => langs('smswait'),
            'Y' => langs('normalcy'),
        );

        if ( ! $reverse)
        {
            $converter = $code2text;
        }
        else
        {
            $converter = array_flip($code2text);
        }

        return $converter[$active_type];
    }
}

//퍼들러 동기화
function puddlr_curl($cmd,$method, $data, $mode="")
{
    $CI =& get_instance();
    $CI->load->helper('file');

    if(file_exists(_SCHEMA."etc/NOMSG")){
        return false;
    }

    if(get_code_name('config', 'CUSTOM_puddlr') != '1'){
        return false;
    }
    $method_delete = "";
    $ctime_out = '300';
    if($mode == 'test')
        $puddlr_domain = "http://test.puddlr.com";
    else
        $puddlr_domain = "https://puddlr.com";
    if( _HOST == 'ma79' )
        $puddlr_domain = "http://puddlr.com";
    if( ENVIRONMENT != 'production')
        $puddlr_domain = "http://puddlr.com";
    if( _HOST == 'dev022')
        $puddlr_domain = "http://puddlr22.mailplug.co.kr:5555";
    $puddlr_url = "{$puddlr_domain}/api/v1";

    $auth_data['username'] = "linuxware-admin";
    $auth_data['password'] = "qwer1234";
    $CI =& get_instance();
    if(!$CI->session->userdata('puddlr_token')){
        $str = "curl -s --connect-timeout 5 --max-time 5 -X POST {$puddlr_domain}/api-token-auth/ -d '".json_encode($auth_data,true)."' -H \"Content-Type: application/json\" -H \"Accept: application/json\" ";

         exec($str,$output);
//      $exec_res = exec_helper($str);
//      $output = $exec_res['output'];

        $auth = json_decode( $output['0'], true);
        if(file_exists(_MAILCONFIGPATH."DEBUG")){
            write_file(_TMPDOMAIN."debug_".date('Ymd'), "\n\n".date('Y-m-d H:i:s')."\n".var_export($auth, true), 'a');
        }
        $puddlr_token = $auth['token'];
        $sess_data = array(
            "puddlr_token" => $puddlr_token,
        );
        $CI->session->set_userdata($sess_data);
    }else{
        $puddlr_token = $CI->session->userdata('puddlr_token');
    }

    if($cmd == "group"){
        if($method == "POST"){
            $url = "/groups/";
            $puddlr_data['organization_chart'] = $data['organization_chart'];
        }
        if($method == "PATCH"){
            $url = "/groups/"._DOMAIN."/";
            $puddlr_data['organization_chart'] = $data['organization_chart'];
        }
        if($method == "DELETE")
            $url = "/groups/"._DOMAIN;
        $puddlr_data['name'] = _DOMAIN;
    }

    if($cmd == "member"){
        if($data['active'] == 'W' || $data['active'] == 'D'){
            return false;
        }
        if( $data['u_priv'] != 'user' ){
            return false;
        }

        $puddlr_data['group'] = _DOMAIN;
        $puddlr_data['name'] = $data['name'];
        $puddlr_data['email'] = $data['id']."@"._DOMAIN;
        if($data['photo']){
            //https://webmail.mailplug.co.kr/userImg/mailplug.co.kr/avatar/sypark96.gif
            if(get_code_name('config', 'CUSTOM_webmail_host') != '')
                $photo_url = "http://".get_code_name('config', 'CUSTOM_webmail_host')."."._DOMAIN;
            else
                $photo_url = "http://mail."._DOMAIN;
            $puddlr_data['profile_image'] = $photo_url.user_photo($data['id'],$data['photo']);
        }else{
            $puddlr_data['profile_image'] = "";
        }

        $puddlr_data['department'] = get_code_name('sosok', $data['sosok']);
        $puddlr_data['position'] = $data['title'];
        $puddlr_data['employee_no'] = $data['sabeon'];
        $puddlr_data['mobile_phone'] = $data['hp'];
        $puddlr_data['phone'] = $data['tel'];
        if(get_auth_user('au_master', $data['seq']) || $data['u_priv'] == 'postmaster' )
            $puddlr_data['type'] = "manager";
        else
            $puddlr_data['type'] = "member";
        if($data['active'] == 'Y')
            $puddlr_data['status'] = "active";
        else
            $puddlr_data['status'] = "inactive";
        if($data['usemsg'] == 'N')
            $puddlr_data['status'] = "inactive";

        if(_SERVER_TYPE == 'KBS' && $data['groupseq'] != '5'){
            $puddlr_data['status'] = "inactive";
        }

        $url = "/groupmembers/";

        if($method == "DELETE"){
            $puddlr_data['status'] = "inactive";
            $method = "POST";
            $method_delete = "DELETE";
        }
    }

    if($cmd == "member_bulk"){
        foreach($data as $row)
        {
            if($row['active'] == 'W' || $row['active'] == 'D'){
                continue;
            }
            if( $row['u_priv'] != 'user' ){
                continue;
            }
            $bulk_data['group'] = _DOMAIN;
            $bulk_data['name'] = $row['name'];
            $bulk_data['email'] = $row['id']."@"._DOMAIN;
            if($row['photo']){
                //https://webmail.mailplug.co.kr/userImg/mailplug.co.kr/avatar/sypark96.gif
                if(get_code_name('config', 'CUSTOM_webmail_host') != '')
                    $photo_url = "http://".get_code_name('config', 'CUSTOM_webmail_host')."."._DOMAIN;
                else
                    $photo_url = "http://mail."._DOMAIN;
                $bulk_data['profile_image'] = $photo_url.user_photo($row['id'],$row['photo']);
            }else{
                $bulk_data['profile_image'] = "";
            }

            $bulk_data['department'] = get_code_name('sosok', $row['sosok']);
            $bulk_data['position'] = $row['title'];
            $bulk_data['employee_no'] = $row['sabeon'];
            $bulk_data['mobile_phone'] = $row['hp'];
            $bulk_data['phone'] = $row['tel'];
            if(get_auth_user('au_master', $row['seq']) || $row['u_priv'] == 'postmaster' )
                $bulk_data['type'] = "manager";
            else
                $bulk_data['type'] = "member";
            if($row['active'] == 'Y')
                $bulk_data['status'] = "active";
            else
                $bulk_data['status'] = "inactive";
            if($row['usemsg'] == 'N')
                $bulk_data['status'] = "inactive";

            if(_SERVER_TYPE == 'KBS' && $row['groupseq'] != '5'){
                $bulk_data['status'] = "inactive";
            }

            $url = "/groupmembers/?bulk";

            if($method == "DELETE"){
                $bulk_data['status'] = "inactive";
                $method = "POST";
                $method_delete = "DELETE";
            }
            $puddlr_data[] = $bulk_data;
        }
    }

    unset($output);
    $str = "curl -s --connect-timeout {$ctime_out} --max-time {$ctime_out} -X {$method} {$puddlr_url}{$url} -d '".json_encode($puddlr_data,true)."' -H \"Content-Type: application/json\" -H \"Accept: application/json\" -H \"Authorization: Token {$puddlr_token}\" ";
    // exec($str,$output);
    $exec_res = exec_helper($str);
    $output = $exec_res['output'];

    unset($result);
    $result = json_decode( $output['0'], true);

    if($method_delete == "DELETE" && $result['user']['id']){
        unset($output);
        $url = "/groupmembers/{$result['user']['id']}";
        $str = "curl -s --connect-timeout {$ctime_out} --max-time {$ctime_out} -X DELETE {$puddlr_url}{$url} -d '".json_encode($puddlr_data,true)."' -H \"Content-Type: application/json\" -H \"Accept: application/json\" -H \"Authorization: Token {$puddlr_token}\" ";
        // exec($str,$output);
        $exec_res = exec_helper($str);
        $output = $exec_res['output'];
    }

    if(file_exists(_MAILCONFIGPATH."DEBUG")){
        write_file(_TMPDOMAIN."debug_".date('Ymd'), "\n\n".date('Y-m-d H:i:s')."\n".var_export($str, true), 'a');
        write_file(_TMPDOMAIN."debug_".date('Ymd'), "\n\n".date('Y-m-d H:i:s')."\n".var_export($result, true), 'a');
    }

    return $output['0'];

}

/**
 * push 서버 동기화
 */
function noti_user_push_curl($data)
{
    $CI =& get_instance();
    $CI->load->model('db_app_noti');
    $noti_set['0'] = $data;
    $noti_set['0']['an_data'] = $CI->db_app_noti->get_user_noti($data['an_email']);
    $CI->load->model('db_users');
    $user_info = $CI->db_users->get(strstr($data['an_email'], '@', true));
    $eas_set = get_user_code_list($user_info['seq'], 'ea_user_config', 'notify_off');
    $eas_pc = @(array)(array_keys($eas_set));

    foreach($noti_set as $key => $val)
    {
        unset($noti_set[$key]['an_regdate']);
        unset($noti_set[$key]['an_data']['au_id']);
        unset($noti_set[$key]['an_data']['au_email']);
        unset($noti_set[$key]['an_data']['au_regdate']);
        $noti_set[$key]['an_data']['au_eas_pc'] = $eas_pc;
        $noti_set[$key]['an_data']['host'] = _HOST;
    }
    $noti_data = str_replace(":null,", ":[],", json_encode($noti_set));
    $noti_data = str_replace(':"",', ':"[]",', $noti_data);
    unset($output);
    $ctime_out = 300;
    $curl_url = "http://push.mailplug.com/put";
    $headers[] = 'Content-Type:application/json';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $curl_url);
    curl_setopt($curl, CURLOPT_POST, 1);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $noti_data);
    $responseData = curl_exec($curl);
    $responseInfo = curl_getinfo($curl);
    curl_close($curl);
    $output = $responseInfo;

    return $output;
}

// 임시
function calendar_notify($data, $type = 'notify')
{
    $noti_data = json_encode($data);
    $curl_url = "http://push.mailplug.com/{$type}";
    $headers[] = 'Content-Type:application/json';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $curl_url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $noti_data);
    $responseData = curl_exec($curl);
    $responseInfo = curl_getinfo($curl);
    curl_close($curl);
    $output = $responseInfo;

    return $output;

}

/**
 * preview_url
 *
 * 미리보기 파일 다운로드
 */
if ( ! function_exists('preview_url'))
{
    function preview_url($type,$regdate,$file_name)
    {
        $salt = config_item('encryption_key');
        $url = "/".sha1($salt.time());
        $url .= "/".$type;
        $url .= "/".$regdate;
        $url .= "/".$file_name;
        $url = "http://".$_SERVER['HTTP_HOST']."/lw_api/preview".$url;
        return $url;
    }
}

if ( ! function_exists('get_tpl_id'))
{
    /**
     * get_tpl_id
     */
    function get_tpl_id($ns=null, $prefix="page", $postfix="")
    {
        // set prefix
        $id = $prefix;

        if ( ! $ns) {
            $ns = uniqid();
        }

        // set namespace
        $id = $id . "_" . $ns;

        if ( !! $postfix) {
            $id = $id . "_" . $postfix;
        }

        return $id;
    }
}

if ( ! function_exists('make_approval_filter'))
{
    function make_approval_filter($filter_list_tmp)
    {
        $filter_list = array();

        //mime_type 모두 삭제 후 확장자로 변경 20170823
        $filter_list['mimetype']['etc'] = array();
        foreach($filter_list_tmp as $v)
        {
            if($v['af_type'] == 'M') {
                $filter_list['mail'][] = $v['af_value'];
            }else if($v['af_type'] == 'K'){
                $tmp_etc = array();

                if(strpos($v['af_value'], 'all_') !== false){

                    if($v['af_value'] == 'all_office'){
                        $tmp_etc = array(
                            'doc',
                            'dot',
                            'docx',
                            'dotx',
                            'xls',
                            'xlm',
                            'xla',
                            'xlc',
                            'xlt',
                            'xlw',
                            'xltm',
                            'xlsx',
                            'xltx',
                            'ppt',
                            'pps',
                            'pot',
                            'pptx',
                            'ppsx',
                            'ppam',
                            'pptm',
                            'sldm',
                            'ppsm',
                            'potm',
                            'pdf',
                            'hwp',
                            'hwt'
                        );
                    }
                    else if($v['af_value'] == 'all_image'){

                        $tmp_etc = array(
                            'bmp',
                            'cgm',
                            'g3',
                            'gif',
                            'ief',
                            'jpeg',
                            'jpg',
                            'jpe',
                            'ktx',
                            'png',
                            'btif',
                            'sgi',
                            'svg',
                            'svgz',
                            'tiff',
                            'tif',
                            'psd',
                            'uvi',
                            'uvvi',
                            'uvg',
                            'uvvg',
                            'djvu',
                            'djv',
                            'sub',
                            'dwg',
                            'dxf',
                            'fbs',
                            'fpx',
                            'fst',
                            'mmr',
                            'rlc',
                            'mdi',
                            'wdp',
                            'npx',
                            'wbmp',
                            'xif',
                            'webp',
                            '3ds',
                            'ras',
                            'cmx',
                            'fh',
                            'fhc',
                            'fh4',
                            'fh5',
                            'fh7',
                            'ico',
                            'sid',
                            'pcx',
                            'pic',
                            'pct',
                            'pnm',
                            'pbm',
                            'pgm',
                            'ppm',
                            'rgb',
                            'tga',
                            'xbm',
                            'xpm',
                            'xwd',
                        );
                    }
                    else if($v['af_value'] == 'all_media'){
                        $tmp_etc = array(
                            'adpcm',
                            'adp',
                            'basic',
                            'au',
                            'snd',
                            'mid',
                            'midi',
                            'kar',
                            'rmi',
                            'm4a',
                            'mp4a',
                            'mpga',
                            'mp2',
                            'mp2a',
                            'mp3',
                            'm2a',
                            'm3a',
                            'oga',
                            'spx',
                            's3m',
                            'silk',
                            'sil',
                            'uva',
                            'uvva',
                            'eol',
                            'dra',
                            'dts',
                            'dtshd',
                            'lvp',
                            'pya',
                            'ecelp4800',
                            'ecelp7470',
                            'ecelp9600',
                            'rip',
                            'weba',
                            'aac',
                            'aif',
                            'aiff',
                            'aifc',
                            'caf',
                            'flac',
                            'mka',
                            'm3u',
                            'wax',
                            'wma',
                            'ram',
                            'ra',
                            'rmp',
                            'wav',
                            'xm',
                            '3gpp',
                            '3gp',
                            '3gpp2',
                            '3g2',
                            'h261',
                            'h263',
                            'h264',
                            'jpeg',
                            'jpgv',
                            'jpm',
                            'jpgm',
                            'mj2',
                            'mjp2',
                            'mp4',
                            'mp4v',
                            'mpg4',
                            'mpeg',
                            'mpg',
                            'mpe',
                            'm1v',
                            'm2v',
                            'ogg',
                            'ogv',
                            'quicktime',
                            'qt',
                            'mov',
                            'uvh',
                            'uvvh',
                            'uvm',
                            'uvvm',
                            'uvp',
                            'uvvp',
                            'uvs',
                            'uvvs',
                            'uvv',
                            'uvvv',
                            'dvb',
                            'fvt',
                            'mxu',
                            'm4u',
                            'pyv',
                            'uvu',
                            'uvvu',
                            'viv',
                            'webm',
                            'f4v',
                            'fli',
                            'flv',
                            'm4v',
                            'mkv',
                            'mk3d',
                            'mks',
                            'mng',
                            'asf',
                            'asx',
                            'vob',
                            'wm',
                            'wmv',
                            'wmx',
                            'wvx',
                            'avi',
                            'movie',
                            'smv',
                        );
                    }
                    else if($v['af_value'] == 'all_compress'){
                        $tmp_etc = array(
                            'zip',
                            '7z',
                            'rar',
                            'ace',
                            'tar',
                            'gz',
                            'alz'
                        );

                        // .alz 파일은 mime type이 존재하지 않아서, 확장자로 필터링함
                        //$filter_list['exe'][] = 'alz';
                    }

                }else if(strpos($v['af_value'], 'office_') !== false){

                    $type = str_replace('office_', '', $v['af_value']);

                    if($type === 'word'){
                        // doc docx dot dotx

                        $tmp_etc = array(
                            'doc',
                            'dot',
                            'docx',
                            'dotx',
                        );
                    }
                    else if($type === 'excel'){
                        // xls xlsx xltx xltm xlt

                        $tmp_etc = array(
                            'xls',
                            'xlm',
                            'xla',
                            'xlc',
                            'xlt',
                            'xlw',
                            'xltm',
                            'xlsx',
                            'xltx',
                        );
                    }
                    else if($type === 'ppt'){
                        // ppt pptx pot pps ppsx

                        $tmp_etc = array(
                            'ppt',
                            'pps',
                            'pot',
                            'pptx',
                            'ppsx',
                            'ppam',
                            'pptm',
                            'sldm',
                            'ppsm',
                            'potm',
                        );
                    }
                    else if($type === 'pdf'){
                        $tmp_etc = array(
                            'pdf'
                        );
                    }
                    else if($type === 'hwp'){
                        // hwp hwt

                        $tmp_etc = array(
                            'hwp',
                            'hwt'
                        );
                    }

                }
                else{
                    $type = split_index_get($v['af_value'], "last", "_");
                    $tmp_etc = array($type);
                }


                $filter_list['mimetype']['etc'] = array_merge($filter_list['mimetype']['etc'], $tmp_etc);
                continue;
            }else if($v['af_type'] == 'F'){
                $filter_list['exe'][] = $v['af_value'];
            }else{
                $filter_list['user'][] = $v['af_value'];
            }
        }

        return $filter_list;
    }
}

if ( ! function_exists('get_calendar_cycle_text'))
{
    function get_calendar_cycle_text($data)
    {
        $CI =& get_instance();
        $sess_lang = $CI->session->userdata('sess_lang');

        $start_day = $data['start_day'];
        $start_time = $data['start_time'];
        $repeat_kind = $data['repeat_kind'];
        $repeat_cycle = $data['repeat_cycle'];
        $repeat_week_arr = $data['repeat_week_arr'];
        $repeat_day_or_week = $data['repeat_day_or_week'];

        $txt = '';

        switch ($repeat_kind)
        {
            case '1':
                if ($repeat_cycle == '1')
                {
                    ## 매일 ##
                    $txt .= langs('txtRepeatDay');
                }
                else
                {
                    ## X일마다 ##
                    $txt .= str_replace('#', $repeat_cycle, langs('txtRepeatDayIng'));
                }

                break;
            case '2':
                if ($repeat_cycle == '1')
                {
                    ## 매주 ##
                    $txt .= langs('txtRepeatWeek');
                }
                else
                {
                    ## X주마다 ##
                    $txt .= str_replace('#', $repeat_cycle, langs('txtRepeatWeekIng'));
                }

                $temp = explode(',', $repeat_week_arr);
                foreach ($temp as $key => $val)
                {
                    ## Y요일 ##
                    $day_text = date('l', strtotime('sunday +'.(int)$val.' day'));
                    $txt .= (' '.langs(strtolower($day_text)));
                }

                break;
            case '3':
                if ($repeat_cycle == '1')
                {
                    ## 매월 ##
                    $txt .= langs('txtRepeatMonth');
                }
                else
                {
                    ## X개월마다 ##
                    $txt .= str_replace('#', $repeat_cycle, langs('txtRepeatMonthIng'));
                }

                if ($repeat_day_or_week == '0')
                {
                    ## Y일 ##
                    if ($sess_lang == 'en')
                    {
                        $txt .= (' '.date('jS', strtotime($start_day)));
                    }
                    else
                    {
                        $txt .= (' '.date('j', strtotime($start_day)).langs('day'));
                    }
                }
                else if ($repeat_day_or_week == '1')
                {
                    $stamp = strtotime($start_day.' '.$start_time);

                    // day of month: 1 ~ 31
                    $start_dom = date('j', $stamp);

                    $week_count = 1 + (int)(($start_dom - 1) / 7);

                    ## Y번째 ##
                    if ($sess_lang == 'en')
                    {
                        $ordinal_text = date('jS', strtotime('today +'.(0 - date('j') + $week_count).' day'));
                        $txt .= (' '.str_replace('#', $ordinal_text, langs('txtRepeatNumberth')));
                    }
                    else
                    {
                        $txt .= (' '.str_replace('#', $week_count, langs('txtRepeatNumberth')));
                    }

                    ## Z요일 ##
                    $day_text = date('l', $stamp);
                    $txt .= (' '.langs(strtolower($day_text)));
                }

                break;
            case '4':
                if ($repeat_cycle == '1')
                {
                    ## 매년 ##
                    $txt .= langs('txtRepeatYear');
                }
                else
                {
                    ## X년마다 ##
                    $txt .= str_replace('#', $repeat_cycle, langs('txtRepeatYearIng'));
                }

                ## Y월 Z일 ##
                if ($sess_lang == 'en')
                {
                    $txt .= (' '.date('M jS', strtotime($start_day)));
                }
                else
                {
                    $txt .= (' '.date('n', strtotime($start_day)).langs('month'));
                    $txt .= (' '.date('j', strtotime($start_day)).langs('day'));
                }

                break;
            default:
                break;
        }

        return $txt;
    }
}

if ( ! function_exists('str2date'))
{
    /**
     * PHP :: function str2date / 날짜형식 문자열값을 diff 계산 후 date함수로 반환
     * @dependency date2time, ts2date
     */
    function str2date($str="now", $format="Y.m.d", $diff="now")
    {
        $res = "";
        if (( ! $str) || ((int)$str < 0))
        {
            return $res;
        }

        // set timetamp
        $ts = time();
        $str = strtolower($str);
        if ($str !== "now") {
            $ts = date2time($str);
        }

        return ts2date($ts, $format, $diff);
    }
}

if ( ! function_exists('ts2date'))
{
    /**
     * PHP :: function ts2date / timestamp값을 diff 계산 후 date함수로 반환
     */
    function ts2date($ts, $format="Y.m.d", $diff="now")
    {
        $res = "";
        if (( ! $ts) || ((int)$ts < 0))
        {
            return $res;
        }

        // calc diff - timestamp
        $apply_ts = $ts;
        if ($diff != "now") {
            $apply_ts = strtotime($diff, $ts);
        }

        $res = date($format, $apply_ts);

        return $res;
    }
}

if ( ! function_exists('isBeforeTs'))
{
    /**
     * PHP :: function isBeforeTs / timestamp값을 diff 계산 후 이전 시간인지 체크
     */
    function isBeforeTs($ts, $diff="now")
    {
        $res = false;

        $diff_ts = cut_date(strtotime($diff), '7');
        if ($ts < $diff_ts) {
            $res = true;
        }

        return $res;
    }
}

function config_verification($config)
{
    $config = strtolower((string)$config);
    return in_array($config, array('1', 'true', 'y', 'yes'));
}

// 메일함 별 읽음/안읽음 메일 개수
function get_mailbox_info()
{
    $CI =& get_instance();
    $CI->load->model('db_approval_mail');
    $user_seq = get_code_name('user', 'seq');
    $approval_mail_list = (array) $CI->db_approval_mail->mailindex_list(array(
        'am_count' => $user_seq,
        'am_status' => 'W',
    ));
    $approval_mail_count = count($approval_mail_list);

    $mailbox_list = array(
        'all_mail' => array(
            'folder_name' => langs('mobile_all_box'),
            'total_count' => 0,
            'unread_count' => 0,
        ),
        'approval' => array(
            'folder_name' => langs('accessmailbox'),
            'total_count' => $approval_mail_count,
            'unread_count' => $approval_mail_count,
        ),
        'star' => array(
            'folder_name' => langs('mobile_mail_important'),
            'total_count' => 0,
            'unread_count' => 0,
        ),
        'notify' => array(
            'folder_name' =>  langs('mobile_mail_notify'),
            'total_count' => 0,
            'unread_count' => 0,
        ),
        'other' => array(
            'folder_name' => langs('mailboxother'),
            'total_count' => 0,
            'unread_count' => 0,
        ),
        'unread' => array(
            'folder_name' => langs('mobile_unread_mail'),
            'total_count' => 0,
            'unread_count' => 0,
        )
    );

    $default_name = array(
        'my' => langs('mailboxmysend'),
        '1' => langs('mailboxrec'),
        '2' => langs('sendmailchk'),
        '3' => langs('mailboxtrash'),
        '4' => langs('mailboxsave'),
        '5' => langs('mailboxspam'),
    );

    //개인메일함 초기 세팅
    $my_folder_list = array_key_change($CI->folder_count['my_folder_list'], "num");
    foreach($my_folder_list as $k => $v){
        $mailbox_list[$k]['folder_name'] = $v['folder_name'];
        $mailbox_list[$k]['unread_count'] = 0;
        $mailbox_list[$k]['total_count'] = 0;
    }
    //각 메일함 카운트 세팅
    unset($k);
    foreach($CI->folder_count as $k => $v) {
        if(!$v['folder_num']) continue;

        if($default_name[$v['folder_num']]) {
            $mailbox_list[$v['folder_num']]['folder_name'] = $default_name[$v['folder_num']];
        }

        $mailbox_list[$v['folder_num']]['total_count'] = $v['etc'] + $v['unread'];
        $mailbox_list[$v['folder_num']]['unread_count'] = (int)$v['unread'];
        if (!in_array($v['folder_num'], array(2, 3, 4, 5)))
        {
            $mailbox_list['all_mail']['total_count'] += $mailbox_list[$v['folder_num']]['total_count'];
        }
    }
    foreach ($default_name as $folder_num => $folder_name)
    {
        if (is_int($folder_num) && ! isset($mailbox_list[$folder_num])) {
            $mailbox_list[$folder_num]['folder_name'] = $default_name[$folder_num];
            $mailbox_list[$folder_num]['total_count'] = 0;
            $mailbox_list[$folder_num]['unread_count'] = 0;
        }
    }
    $mailbox_list['all_mail']['unread_count'] = $CI->folder_count['all_mail']['unread'];
    $mailbox_list['my']['folder_name'] = $default_name['my'];
    $mailbox_list['my']['total_count'] = $CI->folder_count['my']['unread'] + $CI->folder_count['my']['etc'];
    $mailbox_list['my']['unread_count'] = (int)$CI->folder_count['my']['unread'];
    $mailbox_list['other']['total_count'] = $CI->folder_count['other']['unread'] + $CI->folder_count['other']['etc'];
    $mailbox_list['other']['unread_count'] = (int)$CI->folder_count['other']['unread'];
    $mailbox_list['star']['total_count'] = $CI->folder_count['star']['unread'] + $CI->folder_count['star']['etc'];
    $mailbox_list['star']['unread_count'] = (int)$CI->folder_count['star']['unread'];
    $mailbox_list['all_mail']['total_count'] += $mailbox_list['my']['total_count'];
    $mailbox_list['unread']['unread_count'] = $mailbox_list['all_mail']['unread_count'];

    return $mailbox_list;
}

if ( ! function_exists('hex2rgba'))
{
    /**
     * PHP :: function hex2rgba / hex값 색상을 rgba로 변환
     */
    function hex2rgba($hex_color, $opacity = 1)
    {
        $hex = preg_replace('/[^0-9a-fA-F]/', '', $hex_color);

        if (is_nan($opacity))
        {
            $opacity = 1;
        }

        if (strlen($hex) === 3) // #ccc 형태의 표기법
        {
            $red = hexdec($hex[0].$hex[0]);
            $green = hexdec($hex[1].$hex[1]);
            $blue = hexdec($hex[2].$hex[2]);
        }
        else if (strlen($hex) === 6)
        {
            $red = hexdec(substr($hex, 0, 2));
            $green = hexdec(substr($hex, 2, 2));
            $blue = hexdec(substr($hex, 4, 2));
        }
        else
        {
            // hex값이 유효하지 않은 경우, 입력된 값을 그대로 return
            return $hex_color;
        }

        return "rgba({$red}, {$green}, {$blue}, {$opacity})";
    }
}

function get_depth_array($data=array(), $pcode = null, $column = '', $str='')
{
    if(isset($data[$pcode]) && $data[$pcode]['parentcode'] != 0)
    {
        $str = "/".$data[$pcode][$column]."^||LW||^".$pcode.$str;
        return get_depth_array($data, $data[$pcode]['parentcode'], $column, $str);
    }
    else
    {
        return "^||LW||^".$pcode.$str;
    }
}

function get_depth_count($column='', $data=array(), $pcode=null, $cdata = array())
{
    $group_list = array_key_change($data, $column);
    if($pcode != null){
        ++$cdata[$pcode];
        $pcode = $group_list[$pcode]['parentcode'];
        if($pcode > 1) {
            $cdata = get_depth_count($column, $data, $pcode, $cdata);
        }
        return $cdata;
    }

    foreach($data as $sn) {
        if($sn['parentcode'] > 1 && !$sn['NotCount'])
        {
            ++$cdata[$sn['parentcode']];
            $pcode = $group_list[$sn['parentcode']]['parentcode'];
            if($pcode > 1) {
                $cdata = get_depth_count($column, $data, $pcode, $cdata);
            }
        }
    }
    return $cdata;
}

if ( ! function_exists('shell_exec_helper'))
{
    /**
     * PHP :: function shell_exec_helper / php shell_exec 를 실행 해주는 함수
     *
     * @param  string  $str  command
     * @return string
     */
    function shell_exec_helper($cmd)
    {
        if ( isset($cmd) === false )
        {
            return "command empty";
        }

        $escape_cmd = escapeshellcmd($cmd);

        $result = shell_exec($escape_cmd);
        // p_r($result);
        return $result;
    }
}

if ( ! function_exists('exec_helper'))
{
    /**
     * PHP :: function exec_helper / php exec 를 실행 해주는 함수
     *
     * @param  string  $cmd  command
     * @param  boolean  $no_warn  warning, error display Y/N
     * @return  array  output, error
     */
    function exec_helper($cmd, $no_warn = false)
    {
        if ( isset($cmd) === false )
        {
            return "command empty";
        }

        $escape_cmd = escapeshellcmd($cmd);

        if ($no_warn === true)
        {
            @exec($escape_cmd, $output, $error);
        }
        else
        {
            exec($escape_cmd, $output, $error);
        }
        // p_r(array(
        //  'output' => $output,
        //  'error' => $error,
        // ));
        $result = array(
            'output' => $output,
            'error' => $error,
        );

        return $result;
    }
}

/* End of file MY_form_helper.php */
/* Location: ./app/helpers/My_form_helper.php */

