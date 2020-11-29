<?php
namespace SouthCoast\Tools\Acquiring;

class Sberbank
{
    /**
     * URL регистрации заказа (тест)
     */
    const URL_REGISTER_ORDER_TEST = 'https://3dsec.sberbank.ru/payment/rest/register.do';

    /**
     * URL получения статуса заказа (тест)
     */
    const URL_ORDER_STATUS_TEST = 'https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do';

    /**
     * URL регистрации заказа (рабочий)
     */
    const URL_REGISTER_ORDER_REAL = 'https://3dsec.sberbank.ru/payment/rest/register.do';

    /**
     * URL получения статуса заказа (рабочий)
     */
    const URL_ORDER_STATUS_REAL = 'https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do';

    /**
     * Режим работы: false - тест, true - рабочий
     * @var bool
     */
    protected $bMode = false;

    protected $userName;
    protected $password;
    protected $urlRegisterOrder;
    protected $urlOrderStatus;

    public function __construct($bMode = false, $userName = 'poehali-na-more-api', $password = 'poehali-na-more')
    {
        $this->userName = $userName;
        $this->password = $password;
        $this->bMode = $bMode;

        if ($bMode) {
            $this->urlRegisterOrder = self::URL_REGISTER_ORDER_REAL;
            $this->urlOrderStatus = self::URL_ORDER_STATUS_REAL;
        }
        else {
            $this->urlRegisterOrder = self::URL_REGISTER_ORDER_TEST;
            $this->urlOrderStatus = self::URL_ORDER_STATUS_TEST;
        }
    }

    /**
     * Метод отправляет запрос на регистрацию заказа
     *
     * @param $arParams
     * @return array
     */
    public function registerOrder($arParams)
    {
        $arErrors = [];
        $arRequiredFields = ['orderNumber', 'amount', 'returnUrl'];
        foreach ($arRequiredFields as $nameField)
            if (!array_key_exists($nameField, $arParams))
                $arErrors[] = $nameField;

        if (count($arErrors) > 0)
            return [
                'status' => false,
                'data' => 'Не заданы обязательные параметры: ' . implode(' ,', $arErrors)
            ];

        return self::sendRequest($arParams, $this->urlRegisterOrder);
    }

    /**
     * Метод получает состояние заказа
     *
     * @param $idOrder
     * @return array
     */
    public function getOrderStatus($idOrder)
    {
        $arParamsRequest = ['orderId' => $idOrder];
        return self::sendRequest($arParamsRequest, $this->urlOrderStatus);
    }

    /**
     * Метод отправляет запрос на URL = $urlRequest с параметрами $arParamsRequest
     *
     * @param $arParamsRequest
     * @param $urlRequest
     * @return array
     */
    public function sendRequest($arParamsRequest, $urlRequest)
    {
        try
        {
            $curl = curl_init();
            if($curl)
            {
                $arParamsRequest['userName'] = $this->userName;
                $arParamsRequest['password'] = $this->password;

                curl_setopt($curl, CURLOPT_URL, $urlRequest);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($arParamsRequest));
                curl_setopt($curl, CURLOPT_POST, true);

                $jsonResponse = curl_exec($curl);
                $arResponse = json_decode($jsonResponse, true);
                curl_close($curl);

                $arResult = [
                    'status' => true,
                    'data' => $arResponse
                ];
            }
            else
            {
                $arResult = [
                    'status' => false,
                    'data' => 'Ошибка инициализации cURL!'
                ];
            }
        }
        catch (\Exception $ex)
        {
            $arResult = [
                'status' => false,
                'data' => $ex
            ];
        }
        return $arResult;
    }
}