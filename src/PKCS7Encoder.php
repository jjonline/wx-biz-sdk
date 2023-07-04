<?php
/**
 * PKCS7填充算法实现
 */

namespace jjonline\WxBizSdk;

class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param string $text 需要进行填充补位操作的明文
     * @return string 补齐明文字符串
     */
    public static function encode(string $text): string
    {
        $text_length   = strlen($text);
        $amount_to_pad = self::$block_size - ($text_length % self::$block_size); // 计算需要填充的位数
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::$block_size;
        }
        $pad_chr = chr($amount_to_pad);
        $tmp     = str_repeat($pad_chr, $amount_to_pad); // 获得补位所用的字符
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param string $text 解密后的明文
     * @return string 删除填充补位后的明文
     */
    public static function decode(string $text): string
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > self::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}
