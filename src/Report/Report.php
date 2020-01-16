<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 16.01.2020 16:40
 */

namespace Src\Report;

class Report extends ReportBase
{

    private $files = ['report.csv', 'pages.csv', 'images.csv'];

    protected $content;

    public function __construct($domain)
    {
        $this->createBasePath($domain);
        $this->createContent();
    }

    public function createContent(): void
    {
        foreach($this->files as $file):
            $path = realpath($this->basePath.'\\'.$file);
            $content[$file] = file_get_contents($path);
        endforeach;

        $this->content = implode(PHP_EOL, $content);
    }

    public function printReport()
    {
        print $this->content.PHP_EOL;
    }
}