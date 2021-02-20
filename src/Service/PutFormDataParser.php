<?php

namespace App\Service;

class PutFormDataParser
{
    /**
     * @param string $key
     * @param string $content
     * @return string
     */
    public static function get(string $key, string $content): string
    {
        $arr = explode(PHP_EOL, $content);
        $data = '';

        foreach ($arr as $i => $item) {
            if (strpos($item, 'name="' . $key . '"') !== false && count($arr) > ($i + 2)) {
                $data = $arr[$i + 2];
                break;
            }
        }

        $data = str_replace("\r", '', $data);

        return $data;
    }
}
