<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 15.01.2020 22:08
 */

namespace Src\Parser;

use Src\Services\Link\URL;
use Src\Services\Storage\File;
use Src\Services\Storage\Search\Image;
use Src\Services\Storage\Search\Link;

class Parse extends ParseBase
{
    private $fileSystem;

    private $URL;

    private $collectionLinks = [], $checkedLink = [], $rmLinks = [], $imageLinks = [];

    /**
     * Parse constructor.
     * @param string $link
     */
    public function __construct(string $link)
    {
        $this->URL = new URL($link);
        if (!$this->URL->host()) exit('Некорректная ссылка. Нужна правильная ссылка (http(s)://example.com');

        $this->createBasePath($this->URL->host());
        $this->fileSystem = new File($this->basePath);# Указал, что нужно создать папку и она будет базовой
    }


    /**
     * Create Base path:
     * @var string basePath
     */
    protected function createDownloadPath(): void
    {
        $downloadPath = realpath(getcwd() . '/download/');# роверка папки Download
        $parse_url = $this->URL->host();# Парсим страницу. Хочу, стобы Хост был названием папки
        $dirname = str_replace(['www'], '', $parse_url);# Убираю лишнее

        $this->basePath = $downloadPath . '\\' . $dirname;# Условный путь к базовой папке
    }

    /**
     * Поиск всех ссылок
     * Рекурсивная работа с методом recursiveLinks
     * @var array collectionLinks
     */
    public function setAllLinks(): void
    {
        $links = $this->getLinks();

        print PHP_EOL . "1) Сканирую все ссылки (" . date('H:i:s') . ")" . PHP_EOL;

        /**
         * Обрабатывает links.
         * links - получает список ссылок, которые не входят в $this->>collectionLinks
         */
        $this->collectionLinks[] = $this->URL->link();
        while ($links) $links = $this->recursiveLinks($links);


        $this->collectionLinks = array_diff($this->collectionLinks, $this->rmLinks);
    }

    /**
     * Поиск по HTML ссылки
     * @param array $links
     * @return array|null
     */
    protected function recursiveLinks(array $links)
    {
        /**
         * Возможна ошибка. Скорее всего будет ругаться HTTP, но в ТЗ написано, что нужно все страницы.
         * По этому эти страницы будут проиндексированы. Но ошибка может выскочить на экран.
         */

        foreach ($links as $link):
            $this->checkedLink[] = $link;


            if (URL::checkURL($link) != 200) {
                $this->rmLinks[] = $link;
                continue;
            } else $this->saveHTML($link);
            print ' ---  Link: ' . $link . PHP_EOL;

            $newLinks = $this->getLinks($link);# Изза этого нигодяя (P.S Пробовал @get_header() - фейл
            $this->collectionLinks = array_unique(array_merge($newLinks, $this->collectionLinks));
        endforeach;

        return array_diff($this->collectionLinks, $this->checkedLink);
    }


    # HTML(LINKS)

    /**
     * Сбор коллекции. Можно объединить с saveHTML
     */
    public function saveHTMLPages(): void
    {
        print PHP_EOL . "2) Сохраняю все(" . count($this->collectionLinks) . ") страницы (" . date('H:i:s') . ")" . PHP_EOL;

        $this->saveHTML($this->URL->link());

        foreach ($this->collectionLinks as $link) {
            $this->saveHTML($link);
        }
    }

    /**
     * @param $link
     * create HTML
     */
    protected function saveHTML($link): void
    {
        $html = $this->fileSystem->get($link); # $this->URL->link()
        $this->fileSystem->setContent($html);
        $path = parse_url($link, PHP_URL_PATH);# Обычно ссылки работают уже без filename.(HTML|PHP|etc) по этому index.html
        $this->fileSystem->write($path, 'index.html');
    }

    /**
     * @param null $link
     * @return array
     */
    protected function getLinks($link = null): array
    {
        if (!$link) $link = $this->URL->link();
        $html = $this->fileSystem->get($link);
        $this->fileSystem->setContent($html);

        return $this->fileSystem->getCollectionURL(new Link, $this->URL->host());
    }


    # IMAGE

    /**
     * Сбор коллекции. Можно объединить с saveImage
     */
    public function saveImages(): void
    {
        print PHP_EOL . "3) Сканирую все изображения (" . date('H:i:s') . ")" . PHP_EOL;
        $this->getSRC();
        print PHP_EOL . "2) Сохраняю все(" . count($this->imageLinks) . ") изображения (" . date('H:i:s') . ")" . PHP_EOL;

        foreach ($this->imageLinks as $link):
            $this->saveImage($link);
        endforeach;
    }

    /**
     * @param $link
     */
    public function saveImage($link): void
    {
        $html = $this->fileSystem->get($link); # $this->URL->link()
        $this->fileSystem->setContent($html);
        $fileData = $this->getFilenameByPath(parse_url($link, PHP_URL_PATH));

        $path = $fileData['path'];
        $filename = $fileData['filename'];

        print ' ---  Image(' . $filename . '): ' . $link . PHP_EOL;
        $this->fileSystem->write($path, $filename);
    }

    /**
     * Разбиваю на путь(часть) и название файла.
     * Если есть точка значит название файла существует.
     * @param $path
     * @return array
     */
    private function getFilenameByPath($path): array
    {
        $ex_path = explode('/', $path);
        $filename = end($ex_path);# Считаю, что последний сфлеш имеет название файла типа Image.png
        $key = array_search($filename, $ex_path);
        unset($ex_path[$key]);

        $path = implode('/', $ex_path);
        return compact('path', 'filename');
    }

    /**
     * Поиск по Ссылке SRC
     */
    protected function getSRC(): void
    {
        $images[] = $this->searchSRC($this->URL->link());

        foreach ($this->collectionLinks as $link) {
            $images = array_merge($this->searchSRC($link), $images);
        }
        $this->imageLinks = arraY_filter($images);
    }

    /**
     * @param $link
     * @return array
     */
    protected function searchSRC($link)
    {
        if (!$link) $link = $this->URL->link();
        $html = $this->fileSystem->get($link); # $this->URL->link()
        $this->fileSystem->setContent($html);
        return $this->fileSystem->getCollectionURL(new Image);
    }


    # DOCUMENT

    /**
     * Создание документов
     */
    public function createDocument()
    {
        print PHP_EOL . "4) Создаю CSV файл с данными (" . date('H:i:s') . ")" . PHP_EOL;

        $pathPages = $this->createCSVDocument('Список ссылок: ', $this->collectionLinks, 'pages.csv');
        $pathImages = $this->createCSVDocument('Список изображений', $this->imageLinks, 'images.csv');

        $reportData = [
            'Введенная ссылка: ' . $this->URL->link(),
            'Найдено ' . count($this->collectionLinks) . ' ссылок на сайте. Подробнее: ' . $pathPages,
            'Количество изображений: ' . count($this->imageLinks) . '. Подробнее: ' . $pathImages,
            'Базовый путь для данного проекта: ' . $this->basePath,
        ];
        $pathReport = $this->createCSVDocument('Информация: ', $reportData, 'report.csv');

        print PHP_EOL . "5) Ссылка на CSV: IUI" . PHP_EOL . $pathReport . PHP_EOL;
    }

    /**
     * @param $title
     * @param array $collection
     * @param $filename
     * @return string
     */
    protected function createCSVDocument($title, array $collection, $filename)
    {
        $content =
            $title . PHP_EOL .
            implode(PHP_EOL, $collection);

        $this->fileSystem->setContent($content);
        return $this->fileSystem->write('', $filename);
    }
}