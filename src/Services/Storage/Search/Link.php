<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 16.01.2020 14:37
 */


namespace Src\Services\Storage\Search;


class Link implements iSearch
{

    public function tag()
    {
        return 'a';
    }

    public function attribute()
    {
        return 'href';
    }
}