<?php
namespace Eagle\Helper;

use Eagle\Helper;

class TextHelper extends Helper
{
    public function excerpt(string $text, int $limit = 60, string $ellipsis = '...'): string
    {
        $content = wordwrap($text, $limit);
        $content = explode("\n", $content);
        $content = $content[0] . $ellipsis;

        return $content;
    }
}