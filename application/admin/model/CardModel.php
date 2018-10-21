<?php
/**
 * Created by PhpStorm.
 * User: antonymorales
 * Date: 2018/10/3
 * Time: 15:58
 */

namespace app\admin\model;

use think\Model;

class CardModel extends Model
{
    protected $table = 'ca_cards';

    static function addCards($cash, $credit, $num, $time)
    {
        $expiretime = date('Y-m-d H:i:s', time() + $time * 24 * 3600);
//        var_dump($expiretime);
        $cards = self::generateCode($num, null, 20);
        foreach ($cards as $val) {
            $c = new CardModel();
            $c->card = $val;
            $c->cash = $cash;
            $c->credit = $credit;
            $c->expiretime = $expiretime;
            $c->isUpdate(false)->save();
        }
        return array('status' => true);
    }

    static function getAllCards()
    {
        $c = CardModel::paginate(10);
        return array('status' => true, 'cObj' => $c);
    }

    /**
     * 生成随机激活码
     * @param int $nums 个数
     * @param array $exist_array 排除指定数组
     * @param int $code_length 长度
     * @param int $prefix 前缀
     * @return array                 返回激活码数组
     */
    static function generateCode($nums, $exist_array = '', $code_length = 6, $prefix = '')
    {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array();//这个数组用来接收生成的优惠码

        for ($j = 0; $j < $nums; $j++) {

            $code = '';

            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters) - 1)];

            }

            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if (!in_array($code, $promotion_codes)) {

                if (is_array($exist_array)) {

                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码

                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组

                    } else {

                        $j--;

                    }

                } else {

                    $promotion_codes[$j] = $prefix . $code;//将优惠码赋值给数组

                }

            } else {
                $j--;
            }
        }

        return $promotion_codes;
    }

}