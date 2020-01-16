<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 16.01.2020 16:43
 */
namespace Src;

abstract class Base
{
    protected $basePath;

    /**
     * Create Base path:
     * @var string basePath
     */
    protected function createBasePath($link): void
    {
        $downloadPath = realpath(getcwd() . '/download/');# роверка папки Download
        $parse_url = $link;# Парсим страницу. Хочу, стобы Хост был названием папки
        $dirname = str_replace(['www'], '', $parse_url);# Убираю лишнее

        $this->basePath = $downloadPath . '\\' . $dirname;# Условный путь к базовой папке
    }

}