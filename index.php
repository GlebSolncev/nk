<?php
header('Content-type: text/html; charset=utf-8');

function dd()
{
    $args = func_get_args();
    print "<pre>";
    foreach ($args as $text):
        print_r($text);
    endforeach;

    die();
}

function check_http($URL)
{
    $headers = get_headers($URL);
    return substr($headers[0], 9, 3);
}

function create_folders($parent_folder, $src)
{
    $ex = explode('/', $src);
    $path = $_SERVER['DOCUMENT_ROOT'] . "/download/" . $parent_folder;

    $file = end($ex);
    $key = array_search($file, $ex);
    unset($ex[$key]);// File
    array_filter($ex);

    foreach ($ex as $key => $folder) {
        $path .= "/" . $folder;


        if (file_exists($path)) continue;
        else mkdir($path, 0700);
    }
    $path .= '/'.$file;
    return $path;
}


# STEP 1 Разбить ссылку для создания папок и дальнейшем доб в картинку
//$url = 'https://google.com';
$url = 'http://www.net-f.ru/item/php/56.html';
$dd = parse_url($url);

$URL = $dd['scheme'] . "://" . $dd['host'];
$path = $_SERVER['DOCUMENT_ROOT'] . "/download/" . $URL;
if (!file_exists($path)) mkdir($URL);
$host = $dd['host'];
$scheme = $dd['scheme'];

if (!$dd['host']) die('Ссылка не корректна. Используйте Scheme....');


$code = check_http($URL);
if ($code != 200) die('Сервер не отвечает....');

$link = $URL . $dd['path'];
$html = file_get_contents($url);
preg_match_all('/<img[^>]+>/i',$html, $result);

foreach( end($result) as $img_tag)
{
    preg_match('/<img[^>]+src="?\'?([^"\']+)"?\'?[^>]*>/i', $img_tag, $images);
    $src[] = end($images);
}

foreach($src as $url){
    $url = str_replace($URL, '', $url);
    $path = createPath($_SERVER['DOCUMENT_ROOT'].$URL.$url);
    die($path);
    $content = file_get_contents($url);

    fopen($path, 'w');
    file_put_contents($path, $content);


    print $host." - ".$path;
}


//$doc = new DOMDocument();
//@$doc->loadHTML($html);
//$tags = $doc->getElementsByTagName('img');
//dd($doc->getElementsByTagName('img')->item(1)->getAttribute('src'), '____', htmlspecialchars($html));

//foreach ($tags as $tag) {
//    $src = $tag->getAttribute('src');
//    $dd = parse_url($src);
//    if (!$dd['host']) {
//        $path = create_folders($host, $src);
//        $fp = fopen($path, 'w');
//        $img_url = $URL . $src;
//        $image = file_get_contents($img_url);
//        file_put_contents($path, $image);
//    } else{
//        $path = create_folders($host, $src);
//        $fp = fopen($path, 'w');
//
//        $img_url = $src;
//        $image = file_get_contents($img_url);
//        file_put_contents($path, $image);
//    }
//}
die('done');