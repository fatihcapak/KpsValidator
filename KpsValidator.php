<?php

/**
 * Kps Validator Class
 *
 * @author      Fatih ÇAPAK
 */
class KpsValidator
{
    const KPS_SOAP_REQUEST_URL = 'https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx';

    const KPS_SOAP_DATA = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ws="http://tckimlik.nvi.gov.tr/WS">
   <soap:Header/>
   <soap:Body>
      <ws:TCKimlikNoDogrula>
         <ws:TCKimlikNo>%s</ws:TCKimlikNo>
         <ws:Ad>%s</ws:Ad>
         <ws:Soyad>%s</ws:Soyad>
         <ws:DogumYili>%s</ws:DogumYili>
      </ws:TCKimlikNoDogrula>
   </soap:Body>
</soap:Envelope>';

    protected static $_instance;

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function check($ssn, $name, $lastName, $birthYear)
    {
        $soapData = sprintf(self::KPS_SOAP_DATA, $ssn, $name, $lastName, $birthYear);

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($soapData),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, self::KPS_SOAP_REQUEST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soapData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $curlError = curl_errno($ch);

        if ($curlError !== 0) {
            throw new Exception('Curl Error Code : ' . $curlError);
        }

        curl_close($ch);

        preg_match('@<TCKimlikNoDogrulaResult>(.*?)</TCKimlikNoDogrulaResult>@si', $response, $result);

        return (isset($result[1]) && $result[1] == 'true') ? true : false;
    }
}
