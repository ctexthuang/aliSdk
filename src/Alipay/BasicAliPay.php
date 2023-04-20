<?php

namespace Ctexthuang\AliSdk\Alipay;

use Ctexthuang\AliSdk\Alipay\Tools\GeneralTools;
use Ctexthuang\AliSdk\Alipay\Tools\HandleArray;
use Ctexthuang\AliSdk\Alipay\Tools\RequestTools;
use Ctexthuang\AliSdk\Exceptions\ctexthuangException;
use Ctexthuang\AliSdk\Exceptions\InvalidArgumentException;
use Ctexthuang\AliSdk\Exceptions\InvalidResponseException;
use GuzzleHttp\Exception\GuzzleException;

abstract class BasicAliPay
{
    use RequestTools, GeneralTools;

    /**
     * 请求的地址
     * @var string
     */
    protected string $url = 'https://openapi.alipay.com/gateway.do?charset=utf-8';

    /**
     * params参数
     * @var HandleArray
     */
    protected HandleArray $params;

    /**
     * 支付参数
     * @var HandleArray
     */
    protected HandleArray $config;

    /**
     * 请求参数-content
     * @var HandleArray
     */
    protected HandleArray $options;

    /**
     * 请求方法
     * @var string
     */
    protected string $method;

    /**
     * 参数设置
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->params = new HandleArray([]);
        $this->config = new HandleArray($options);

        if (empty($options['appid']))
        {
            throw new InvalidArgumentException("Config Not Found -> appid");
        }

        if (empty($options['public_key']))
        {
            throw new InvalidArgumentException("Config Not Found -> public_key");
        }

        if (empty($options['private_key']))
        {
            throw new InvalidArgumentException("Config Not Found -> private_key");
        }

        if (!empty($options['debug']))
        {
            $this->url = 'https://openapi.alipaydev.com/gateway.do?charset=utf-8';
        }

        $this->options = new HandleArray([
            'app_id' => $this->config->offsetGet('appid'),
            'charset' => empty($options['charset']) ? 'utf-8' : $options['charset'],
            'format' => 'JSON',
            'version' => '1.0',
            'sign_type' => empty($options['sign_type']) ? 'RSA2' : $options['sign_type'],
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        if (isset($options['notify_url']) && $options['notify_url'] !== '')
        {
            $this->options->offsetSet('notify_url', $options['notify_url']);
        }

        if (isset($options['return_url']) && $options['return_url'] !== '')
        {
            $this->options->offsetSet('return_url', $options['return_url']);
        }

        if (isset($options['app_auth_token']) && $options['app_auth_token'] !== '')
        {
            $this->options->offsetSet('app_auth_token', $options['app_auth_token']);
        }
    }

    /**
     * 获取回调数据
     * @param bool $needSignType
     * @return array
     * @throws InvalidResponseException
     */
    public function getNotify(bool $needSignType = false) : array
    {
        $data = $_POST;

        if (empty($data) || empty($data['sign']))
        {
            throw new InvalidResponseException('Illegal push request.', 0, $data);
        }

        $string = $this->alipaySignatureHandle($data, $needSignType);

        $content = wordwrap($this->config->offsetGet('public_key'), 64, "\n", true);

        $res = "-----BEGIN PUBLIC KEY-----\n{$content}\n-----END PUBLIC KEY-----";

        if (openssl_verify($string, base64_decode($data['sign']), $res, OPENSSL_ALGO_SHA256) !== 1)
        {
            throw new InvalidArgumentException('Data signature verification failed.', 0, $data);
        }

        return $data;
    }

    /**
     * 阿里支付签名处理
     * @param array $data 需要进行签名数据
     * @param boolean $needSignType 是否需要sign_type字段
     * @return string
     */
    protected function alipaySignatureHandle(array $data, bool $needSignType = false): string
    {
        [$attrs,] = [[], ksort($data)];

        if (isset($data['sign']))
        {
            unset($data['sign']);
        }

        if (empty($needSignType))
        {
            unset($data['sign_type']);
        }

        foreach ($data as $key => $value) {
            if ($value === '' || is_null($value))
            {
                continue;
            }

            $attrs[] = "$key=$value";
        }

        return join('&', $attrs);
    }

    /**
     * 获取数据签名
     * 默认签名请求参数，传入str则签名str
     * @param string $str
     * @return string
     */
    protected function getAlipaySign(string $str = ''): string
    {
        if (empty($str))
        {
            $str = $this->alipaySignatureHandle($this->options->offsetGet(), true);
        }

        $content = wordwrap($this->handleCert($this->config->offsetGet('private_key')), 64, "\n", true);

        $string = "-----BEGIN RSA PRIVATE KEY-----\n$content\n-----END RSA PRIVATE KEY-----";

        if ($this->options->offsetGet('sign_type') === 'RSA2') {
            openssl_sign($str, $sign, $string, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($str, $sign, $string, OPENSSL_ALGO_SHA1);
        }

        return base64_encode($sign);
    }

    /**
     * 发送支付请求并且获取结果
     * @param string $key
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidResponseException
     * @throws ctexthuangException
     */
    protected function postPay(string $key = '')
    {
        $data = $this->post();

        if (empty($key))
        {
            $key = $this->method;
        }

        if (isset($data[$key]['code']) && $data[$key]['code'] !== '10000')
        {
            throw new InvalidResponseException(
                "Error: " .
                (empty($data[$key]['code']) ? '' : "{$data[$key]['msg']} [{$data[$key]['code']}]\r\n") .
                (empty($data[$key]['sub_code']) ? '' : "{$data[$key]['sub_msg']} [{$data[$key]['sub_code']}]\r\n"),
                $data[$key]['code'], $data
            );
        }

        return $data[$key];
    }

    /**
     * 发起支付的逻辑
     * @param array $options
     * @return mixed
     */
    abstract public function apply(array $options): mixed;
}