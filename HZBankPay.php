<?php

class HZBankPay {

//    private $account;
        //字符集编码
    private $postCharset = "UTF-8";
        //文件字符集编码
    private $fileCharset = "UTF-8";

    private $apiUrl = '';

    public $certKey = '';
    public $merchantPrivatekey = '';
    public $merId = '';
    public $bankDn = '';

    private $defaultParam = [
        'encoding' => 'UTF-8',
        'sdkAppId' => '201701',
        'certId' => '17010100001',
        'merId' => '',
//        'phoneNo' => '13788624582',
//        'customerNM' => '102916028 ',
//        'certifId' => '362329198603050613',
//        'certifType' => '00',
//        'cardNo' => '603367100119193957',
    ];

    private $params = [];

    public function __construct($config)
    {
        $this->certKey = $config['certKey'];
        $this->merchantPrivatekey = $config['merchantPrivatekey'];
        $this->merId = $config['merId'];
        $this->defaultParam['merId'] = $this->merId;
        $this->apiUrl = $config['apiUrl'];
        $this->bankDn = $config['bankDn'];
    }

    private function setParam($params){
        $this->params = $this->defaultParam + $params;
        $this->setSign();
    }

    public function post($params){
        $this->setParam($params);
        return HttpRequest::post(
            $this->apiUrl,
            json_encode($this->params),
            [
                'Content-Type:application/json;charset=UTF-8',
            ]
        );
    }

    /**
     *  Sign 加签
     * @param array $param
     * @return false|mixed
     */
    public function setSign()
    {
        $param = $this->params;
        $signAture = $this->createSign($this->getSignContent($param));
        $this->params['signAture'] = $signAture;
    }

    function checkSign($data){
        $signData = $data['signAture'];
        unset($data['signAture']);
        $unsignData = $this->getSignContent($data);
        $sourceFile = tempnam('/tmp', md5(uniqid() . 'source'));
        $targetFile = tempnam('/tmp', md5(uniqid() . 'target'));
        file_put_contents($sourceFile,$this->formatSmimeSignData($signData,$unsignData));
        $ret = [
            'error' => 0
        ];
        try{
            if(openssl_pkcs7_verify($sourceFile,PKCS7_BINARY|PKCS7_NOVERIFY,$targetFile) === true){
                $dnData = openssl_x509_parse("file://".$targetFile);
                if(!$dnData){
                    throw new \Exception('验签失败:'.openssl_error_string());
                }
                if(time()<intval($dnData['validFrom_time_t']) || time()>intval($dnData['validTo_time_t'])){
                    throw new \Exception('验签失效');
                }
                if(strpos($dnData['subject']['CN'],"[{$this->bankDn}]") === false){
                    throw new \Exception('验签失败1');
                }
            }else{
                throw new \Exception('验签失败:'.openssl_error_string());
            }
        }catch (\Exception $e){
            //logger
            $ret =  [
                'error' => 1,
                'msg' => $e->getMessage(),
            ];
        }
        @unlink($sourceFile);
        @unlink($targetFile);
        return $ret;
    }

    /**
     * 加签
     * @param string $data
     */
    private function createSign(string $data)
    {
        $pkcs12 = file_get_contents($this->certKey);
        openssl_pkcs12_read($pkcs12, $certs, $this->merchantPrivatekey);
        if (empty($certs) || empty($certs['pkey']) || empty($certs['cert'])) {
            throw new \RuntimeException('支付订单处理失败了');
        }
        $sourceFile = tempnam('/tmp', md5(uniqid() . 'source'));
        $targetFile = tempnam('/tmp', md5(uniqid() . 'target'));
        file_put_contents($sourceFile, $data);
        // 对数据签名
        try {
            openssl_pkcs7_sign($sourceFile, $targetFile, $certs['cert'], $certs['pkey'], [], PKCS7_DETACHED | PKCS7_BINARY | PKCS7_NOATTR);
        } catch (\Exception $e) {
//            throw new \Exception('hzb_pay api response:'.$e->getMessage());
//            Logger::info(sprintf('hzb_pay api response: %s', $e->getMessage()));
        }
        // 输出部分签名
        $signText = file_get_contents($targetFile);
        @unlink($sourceFile);
        @unlink($targetFile);
        // 正则匹配base64编码的签名
        $signText = str_replace("\n", '', $signText);
        preg_match('/MII[\w\d\+\/\=)]+/', $signText, $matches);
        // 返回签名
        return $matches ? $matches[0] : false;
    }

    /**
     * @param $params
     * @return string
     */
    private function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->charCet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    private function charCet($data, $targetCharset): string
    {
        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

    private function formatSmimeSignData($signData,$unsignData){
        $signData = chunk_split($signData,76,"\n");
        $boundary = "----".md5($signData);

        $signData = <<<EOD
MIME-Version: 1.0
Content-Type: multipart/signed; protocol="application/x-pkcs7-signature"; micalg=sha1; boundary="$boundary"

This is an S/MIME signed message

--$boundary
$unsignData
--$boundary
Content-Type: application/x-pkcs7-signature; name="smime.p7s"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="smime.p7s"

$signData

--$boundary--


EOD;

        return $signData;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    private function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }
}