<?php
// 这是系统自动生成的公共文件
if (!function_exists('toUnderline')) {
    /**
     * 驼峰命名转下划线命名
     *
     * @param string $str
     * @return string
     */
    function toUnderline($str) {
        $dstr = preg_replace_callback('/([A-Z]+)/', function($matchs) {
            return '_'.strtolower($matchs[0]);
        }, $str);

        return trim(preg_replace('/_{2,}/','_',$dstr), '_');
    }
}

if (!function_exists('toCamelCase')) {
    /**
     * 下划线转驼峰
     *
     * @param string $str
     * @return void
     */
    function toCamelCase($str = '') {
        $array = explode('_', $str);
        $result = $array[0];
        $len=count($array);
        if($len>1) {
            for($i=1;$i<$len;$i++) {
                $result.= ucfirst($array[$i]);
            }
        }

        return $result;
    }
}

function getVipRule(){
    $vip_rule = conf('vip.vip_rule');
    $rule_list = explode('<br />',nl2br($vip_rule));
    foreach ($rule_list as $key => $item){
        $keys = ['name', 'money', 'day','day_name', 'discount', 'discount_msg', 'desc','is_top'];
        $rule = array_combine($keys,explode('|',trim($item)));
        $rule['id'] = $key;
        $rule_list[$key] = $rule;
    }

    return $rule_list;
}