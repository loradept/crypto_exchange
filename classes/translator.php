<?php
class Translator {
    private $lang;

    private $dictionary = [
        'ua' => [
            'balance' => 'Баланс',
            'trade' => 'Угода',
            'language' => 'Мова',
            'price' => 'Цiна',
            'update' => 'оновлення',
            'chart' => 'Графiк',
            'History' => 'Iсторiя',
            'amount' => 'Сума',
'back' => 'Назад',
'history' => 'Історія',
'type' => 'Тип',
'coin' => 'Монета',
'amount' => 'Сума',
'price' => 'Ціна',
'total' => 'Всього',
'buy' => 'Купити',
'sell' => 'Продати',
'Back' => 'Назад',
'Portfolio' => 'Портфолiо',
'Asset'   => 'Монета',
'Amount'  => 'Кiлькiсть монет',
'Value'   => 'Вартiсть',
'loading' => 'Загрузка',
'All'    => 'Всi',
'Buy'    => 'Покупка',
'Sell'   => 'Продажа',
'Newest' => 'Спочатку новi',
'Oldest' => 'Спочатку старi',
'Apply'  => 'Застсувати',
'Type'   => 'Тип',
    'Coin'   => 'Монета',
    'Amount' => 'Кiлькiсть',
    'Price'  => 'Цiна',
    'Total'  => 'Усього',
    'logout'  => 'Вийти',
    'buy'  => 'купив',
    'sell' => 'продав',
'success_trade' => 'Транзакція успішно виконана'
        ],
        'en' => [
            'balance' => 'Balance',
            'trade' => 'Trade',
            'language' => 'Language',
            'price' => 'Price',
            'update' => 'Update',
            'chart' => 'Chart',
            'history' => 'History',
            'amount' => 'Amount'
        ]
    ];

    public function __construct($lang) {
        $this->lang = $lang;
    }

    public function get($key) {
        return $this->dictionary[$this->lang][$key] ?? $key;
    }
}