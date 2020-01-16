<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 15.01.2020 22:31
 */


namespace Src\Services\Storage;


use DOMDocument;
use Src\Services\Storage\Search\iSearch;

class File
{
    private $basePath;

    private $content;

    private $status;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->checkBasePath();

        if (!$this->status) $this->createDirs($this->basePath);
    }

    private function checkBasePath(): void
    {
        $this->status = file_exists(realpath($this->basePath));
    }

    private function createDirs($path): void
    {
        /**
         * STR_REPLACE потому что не понятно, если Linux: "/", если Windows: "\".
         * Windows работает как с /, так и с \. Но по дефолту \
         * Это все меняем на "__:__" - маловероятный delimite
         */
        $replacePath = str_replace(['/', '\\'], '__:__', $path);

        $dirs = array_filter(explode('__:__', $replacePath));# Раззбиваем путь, для проверки каждой папки
        $impPath = null; # Исзначаьно пусто. Но можно влепить половину пути(с ядра)
        foreach ($dirs as $path):
            $impPath .= $path . "/";# Добавляю папку для проверки и создания
            if (!file_exists($impPath)) mkdir($impPath);
        endforeach;
    }

    public function get(string $link)
    {
        return file_get_contents($link);// # Выбрал простой путь для получения исходника. Можно через CURL.
    }

    public function write($path, $filename): string
    {
        $this->createDirs($this->basePath . "/" . $path);
        $realPath = realpath($this->basePath . "/" . $path);
        if (!$realPath) return null;

        $path = $realPath . '/' . $filename;
        $exists = file_exists($path);

        if (!$exists) $file = fopen($path, 'w'); # Создаем если нужно

        file_put_contents($path, $this->content);# Записываю, всегда.

        if (!$exists) fclose($file);# Закрываем для буф.

        return realpath($path);
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }


    public function getCollectionURL(iSearch $search, $host = null): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML($this->content);
        $imageTags = $dom->getElementsByTagName($search->tag());
        $collection = [];
        foreach ($imageTags as $tag) {
            $href = $tag->getAttribute($search->attribute());
            $parse_host = parse_url($href, PHP_URL_HOST);
            if (!$host or $host == $parse_host)
                $collection[] = urldecode($href);
        }

        return $collection;
    }


}
