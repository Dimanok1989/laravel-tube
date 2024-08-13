<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Режим работы
    |--------------------------------------------------------------------------
    |
    | Эта настройка определяет режим работы загрузчика видео. Поддерживается
    | несколько режимов, которые позволят запустить процесс загрузки видео
    | с использованием прокси сервера или при помощи http запросов. Для
    | использоваания http запроов на другой машине должен быть установлен
    | laravel с пакетом kolgaev/laravel-tube
    | 
    | Поддерживается: "self", "http", "proxy"
    |   "self" - Все процессы происходят на Вашем сервере
    |   "http" - Процесс скачивания происходит на другом сервере через http запросы
    |   "proxy" - Все процессы таже происходят на Вашем сервере но через прокси
    |
    */

    'mode' => env('TUBE_MODE', "self"),

    /*
    |--------------------------------------------------------------------------
    | Базовая сслыка для режима http
    |--------------------------------------------------------------------------
    */

    'base_url' => env('TUBE_BASE_URL', "http://localhost:8000"),

];
