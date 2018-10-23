<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 第二个参数 E加密 D解密
function encryption($string, $operation)
{
    $key = md5(config('encryption_key'));
    $key_length = strlen($key);
    $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
    $string_length = strlen($string);
    $rndkey = $box = array();
    $result = '';
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($key[$i % $key_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'D') {
        if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
            return substr($result, 8);
        } else {
            return '';
        }
    } else {
        return str_replace('=', '', base64_encode($result));
    }
}

//获取用户真实IP
function getIp()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                $ip = getenv("REMOTE_ADDR");
            else
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else
                    $ip = "unknown";
    return ($ip);
}

//Remove the exploer'bug XSS
function RemoveXSS($val)
{
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

        // @ @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // @ @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}

//防注入
function abacaAddslashes($var)
{
    if (!get_magic_quotes_gpc()) {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $var [$key] = abacaAddslashes($val);
            }
        } else {
            $var = addslashes($var);
        }
    }
    return $var;
}