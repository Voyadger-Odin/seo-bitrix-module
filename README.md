SEO модуль для bitrix 1.0.0
===========

SEO модуль для CMS "1С-Битрикс" - настраиваемые редиректы и подмена метатегов

## Возможности

* Подмены метатегов на странице - подменяются метатеги: title, keywords, description, h1, text
* Настраиваемые редиректы - можно указать с какой на какую страницу произойдёт редирект, а также тип редиректа: 301, 302

## Настройка

Скачать модуль в папку `voyadger.seo` и установить из административного раздела сайта

Для корректной замены метатега title не использовать `$APPLICATION->ShowTitle(false)`. 
Необходимо использовать `$APPLICATION->ShowTitle()` (без параметра false) 

После установки, модуль появляется в `Сервисы / SEO автоматическая подмена`

Модуль работает не на инфоблоках, а создаёт собственные таблицы в базе данных, что упрощает его установку

## Метатеги

Подмена метатегов происходит до отрисовки страницы, сохранением в PageProperty соответствующих параметров

Для корректной работы модуля, необходимо запускать `ShowTitle` без параметра `false`

ShowTitle в стандартном шаблоне bitrix
![img_2.png](docs%2Fimg%2Fimg_2.png)

Как должно быть
![img_3.png](docs%2Fimg%2Fimg_3.png)

Пример создания метатегов
![img_1.png](docs%2Fimg%2Fimg_1.png)

Подмена метатегов будет происходить, даже если в коде страницы задан `SetTitle`

## Редиректы

Настраиваемые редиректы с одных страниц на другие с указанием типа редиректа

Пример заполнения редиректов
![img.png](docs%2Fimg%2Fimg.png)