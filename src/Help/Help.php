<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 16.01.2020 17:22
 */
namespace Src\Help;

class Help extends BaseHelp
{
    protected $mask = "|%-10s |%-100s \n";

    public function printHelp(){
        printf($this->mask, 'Команда', 'Описание');
        printf($this->mask, 'parse', 'Запустить парсер по сылке');
        printf($this->mask, 'report', 'Результат анализа для домена');
        printf($this->mask, 'help', 'Помощь');
    }
}