<?php
namespace rc4;
/**
 * RC4加密解密 + 16进制
 * 户连超
 * 2016.12.16
 * hu_lianchao@outlook.com 
 */
class rc4
{
    /**
    * 加密
    * @param string $key 私匙
    * @param mix $data 需要加密的数据
    * @param boolean $decrypted 是否解密
    * @return 16进制字符串
    */
    public function Encrypted($key, $data, $decrypted=false)
    {
        $keyLength = strlen($key);
        $S = array();
        for($i = 0; $i < 256; $i++) 
        $S[$i] = $i;

        $j = 0;
        for ($i = 0; $i < 256; $i++)
        {
            $j = ($j + $S[$i] + ord($key[$i % $keyLength])) % 256;
            $this->swap($S[$i], $S[$j]);
        }

        $dataLength = strlen($data);
        $output ="";
        for ($a = $j = $i = 0; $i < $dataLength; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $S[$a]) % 256;

            $this->swap($S[$a], $S[$j]);

            $k = $S[(($S[$a] + $S[$j]) % 256)];

            $output .= chr(ord($data[$i]) ^ $k);

        }
        return ($decrypted) ? $output : bin2hex($output);
    }
    /**
    * 解密
    * @param string $a 私匙
    * @param mix $b 需要解密的数据
    * @return 字符串
    */
    public function Decrypted($a, $b)
    {
        return $this->Encrypted($a, $this->hex2bin($b), true);
    }

    public function swap(&$a, &$b)
    {
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }
    public function hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data); 
    }
}