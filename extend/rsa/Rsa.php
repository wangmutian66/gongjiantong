<?php
namespace rsa;
/**
 * 
 * 
 */
class Rsa
{
    private $public_key = ''; //公密钥
    private $private_key = ''; //私密钥
    private $public_key_resource = ''; //公密钥资源
    private $private_key_resource = ''; //私密钥资源
    /**
     * 架构函数
     * @param [string] $public_key_file  [公密钥文件地址]
     * @param [string] $private_key_file [私密钥文件地址]
     */
    public function __construct($public_key_file, $private_key_file)
    {
        try {
            if (!file_exists($public_key_file) || !file_exists($private_key_file)) {
                throw new Exception('key file no exists');
            }
            if (false == ($this->public_key = file_get_contents($public_key_file)) || false == ($this->private_key = file_get_contents($private_key_file))) {
                throw new Exception('read key file fail');
            }
            if (false == ($this->public_key_resource = $this->is_bad_public_key($this->public_key)) || false == ($this->private_key_resource = $this->is_bad_private_key($this->private_key))) {
                throw new Exception('public key or private key no usable');
            }

        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    private function is_bad_public_key($public_key)
    {
        return openssl_pkey_get_public($public_key);
    }
    private function is_bad_private_key($private_key)
    {
        return openssl_pkey_get_private($private_key);
    }
    /**
     * 生成一对公私密钥 成功返回 公私密钥数组 失败 返回 false
     */
    public function create_key()
    {
        $res = openssl_pkey_new();
        if ($res == false) {
            return false;
        }

        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        return array('public_key' => $public_key["key"], 'private_key' => $private_key);
    }
    /**
     * 用私密钥加密
     */
    public function private_encrypt($input)
    {
        openssl_private_encrypt($input, $output, $this->private_key_resource);
        return base64_encode($output);
    }
    /**
     * 解密 私密钥加密后的密文
     */
    public function public_decrypt($input)
    {
        openssl_public_decrypt(base64_decode($input), $output, $this->public_key_resource);
        return $output;
    }
    /**
     * 用公密钥加密
     */
    public function public_encrypt($input)
    {
        openssl_public_encrypt($input, $output, $this->public_key_resource);
        return base64_encode($output);
    }
    /**
     * 解密 公密钥加密后的密文
     */
    public function private_decrypt($input)
    {
        openssl_private_decrypt(base64_decode($input), $output, $this->private_key_resource);
        return $output;
    }
}
