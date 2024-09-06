<?php

declare(strict_types=1);

namespace Nighten\Bc\Service;

use Nighten\Bc\Validator\NumberValidator;

readonly class SourceListProvider
{
    private const string FILE_NAME = 'list.dump';

    public function __construct(
        private NumberValidator $numberValidator,
    ) {
    }

    /**
     * @return string[]
     */
    public function getSourceList(): array
    {
        $list = $this->loadFromCache();
        if (null !== $list) {
            return $list;
        }
        $result = [];
        for ($i = 0; $i <= 9; $i++) {
            for ($j = 0; $j <= 9; $j++) {
                for ($k = 0; $k <= 9; $k++) {
                    for ($m = 0; $m <= 9; $m++) {
                        $s = $i . $j . $k . $m;
                        $validateResult = $this->numberValidator->validate($s);
                        if ($validateResult->success) {
                            $result[] = $s;
                        }
                    }
                }
            }
        }
        $this->saveToCache($result);
        return $result;
    }

    /**
     * @return string[]|null
     */
    private function loadFromCache(): ?array
    {
        if (file_exists(self::FILE_NAME)) {
            $content = file_get_contents(self::FILE_NAME);
            if (is_string($content)) {
                $result = json_decode($content);
                if (!is_array($result)) {
                    return null;
                }
                foreach ($result as $value) {
                    if (!is_string($value)) {
                        return null;
                    }
                }
                return $result;
            }
        }
        return null;
    }

    /**
     * @param string[] $list
     */
    private function saveToCache(array $list): void
    {
        file_put_contents(self::FILE_NAME, json_encode($list));
    }
}
