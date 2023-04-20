<?php

namespace Ctexthuang\AliSdk\Alipay\Tools;

use Ctexthuang\AliSdk\Exceptions\ctexthuangException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait RequestTools
{
    /**
     * post请求
     * @return mixed
     * @throws GuzzleException
     * @throws ctexthuangException
     */
    protected function post()
    {
        $client = new Client([
            'timeout' => 10,
        ]);

        $response = $client->post($this->url, ['form_params' => $this->options->get()]);

        if ($response->getStatusCode() != 200) {
            throw new ctexthuangException('请求失败: ' . $response->getStatusCode());
        }

        return json_decode($response->getBody(), true);

    }
}