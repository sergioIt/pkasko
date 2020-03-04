<?php
/**
 * Created by PhpStorm.
 * User: makryun
 * Date: 04/03/2020
 * Time: 00:48
 */

const BASE_URL = 'https://book24.ru';

$urlList = BASE_URL . '/catalog/programmirovanie-1361/';

$html = file_get_contents($urlList);

error_reporting(E_ERROR | E_PARSE); // для отключения warnings про невалидные/неопознанные тэги html

//var_dump($html);
//1.	Автор(ы)
//2.	Название
//3.	Цена
//4.	Год издания
//5.	Изображение

//не удалось пропарсить, потому что эти данные не загружаются в DOM:
//6.	Минимальная стоимость доставки (бесплатно не учитываем)


$dom = new DOMDocument;

$dom->loadHTML($html);

$xpath = new \DOMXpath($dom);

$divsBooks = $xpath->query('//div[@class="catalog-products__item js-catalog-products-item"]');

$data = [];

$counter = 0;

echo 'processing items: ';

foreach ($divsBooks as $bookContainer) {

    echo $counter++;

    $bookData = [];
    $bookData['authors'] = [];
    $bookData['title'] = '';
    $bookData['price'] = '';
    $bookData['year'] = '';
    $bookData['image'] = '';
    $bookData['delivery_price'] = '';


    $authorsContainer = $xpath->query('descendant::*[@class="book__author "]', $bookContainer);

    foreach ($authorsContainer as $container) {
        /**
         * @var $container DOMElement
         */
        $links = $container->getElementsByTagName('a');

        foreach ($links as $link) {
            /**
             * @var $link DOMElement
             */
            $bookData['authors'][] = $link->nodeValue;
        }

    }

    $titleContainer = $xpath->query('.//div[@class="book__title "]', $bookContainer);

    foreach ($titleContainer as $container) {

        foreach ($container->childNodes as $node) {

            /**
             * @var $node DOMElement
             */
            $bookData['title'] = trim($node->nodeValue);

            $bookData['detailsLink'] = $node->getAttribute('href');
        }

    }

    // парсинг страницы с подробностями о книге, чтобы получить год издания и стоимость доаствки

    if ($bookData['detailsLink'] !== '') {

        $htmlDetails = file_get_contents(BASE_URL . $bookData['detailsLink']);

        $domDetailed = new DOMDocument;

        $domDetailed->loadHTML($htmlDetails);

        $xpathDetailed = new \DOMXpath($domDetailed);

        $yearContainers = $xpathDetailed->query('//span[@class="item-tab__chars-key"]');

        foreach ($yearContainers as $keyNode) {

            if ($keyNode->nodeValue !== 'Год издания:') {

                continue;
            }

            $bookData['year'] = $keyNode->nextSibling->nodeValue;
        }


        /**
         * @todo здесь должен быть текст со стоимостью доставки, но он не отображается в DOM, видимо загружается позже
         */
        $deliveryContainers = $xpathDetailed->query('//div[@class="item-actions__actions-item _preloading js-product-card-actions-delivery-courier"]');

    }

    $priceContainer = $xpath->query('.//div[@class="book__price-inner"]', $bookContainer);

    foreach ($priceContainer as $container) {

        $bookData['price'] = trim($container->nodeValue);
    }

    $imageContainer = $authorsContainer = $xpath->query('descendant::*[@class="book__image-block"]', $bookContainer);


    foreach ($imageContainer as $container) {

        /**
         * @var $container DOMElement
         */
        $images = $container->getElementsByTagName('img');

        foreach ($images as $image) {
            /**
             * @var $image DOMElement
             */
            $bookData['image'] = $image->getAttribute('data-src');

        }

    }

    $data[] = $bookData;

}

var_dump($data);
