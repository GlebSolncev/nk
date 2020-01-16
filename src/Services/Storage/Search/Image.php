<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 16.01.2020 14:36
 */
namespace Src\Services\Storage\Search;


class Image implements iSearch
{

    public function tag()
    {
        return 'img';
    }

    public function attribute()
    {
        return 'src';
    }
}