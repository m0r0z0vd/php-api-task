<?php

namespace App\Tests\Unit\Service;

use App\Service\PutFormDataParser;
use PHPUnit\Framework\TestCase;

class PutFormDataParserTest extends TestCase
{
    public function testGet(): void
    {
        $validFormData = $this->getValidFormData();
        $value = PutFormDataParser::get('data', $validFormData);
        $this->assertEquals('new secret', $value);
        $value = PutFormDataParser::get('id', $validFormData);
        $this->assertEquals('1', $value);
        $value = PutFormDataParser::get('non-existing-property', $validFormData);
        $this->assertEquals('', $value);
    }

    public function testGetIfInvalidData(): void
    {
        $data = 'name="data"' . PHP_EOL;
        $data .= 'abc';
        $value = PutFormDataParser::get('data', $data);
        $this->assertEquals('', $value);
    }

    private function getValidFormData(): string
    {
        $validData = '----------------------------511717019598830426511917' . PHP_EOL;
        $validData .= 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL;
        $validData .= '1' . PHP_EOL;
        $validData .= '----------------------------511717019598830426511917' . PHP_EOL;
        $validData .= 'Content-Disposition: form-data; name="data"' . PHP_EOL . PHP_EOL;
        $validData .= 'new secret' . PHP_EOL;

        return $validData;
    }
}
