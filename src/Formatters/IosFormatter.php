<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Formatters;

readonly class IosFormatter implements FormatterInterface
{
    public function createFileContent(array $translates): string
    {
        $result = [];

        foreach ($translates as $path => $translate) {
            $result[] = '"' . $this->preparePath((string) $path) . '" = "'
                . $this->prepareTemplate($translate->template, $translate->variables)
                . '";';
        }

        return implode('', $result);
    }

    private function preparePath(string $path): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9]+/', '_', $path);

        return strtolower(trim($safe, '_'));
    }

    private function prepareTemplate(string $template, array $variables): string
    {
        $index = 1;
        $replacements = [];

        foreach ($variables as $varName => $type) {
            $replacements["{{{$varName}}}"] = match ($type) {
                'integer' => "%{$index}\$d",
                'double', 'float' => "%{$index}\$.2f",
                default => "%{$index}\$s",
            };
            $index++;
        }

        $replacements = array_replace($replacements, ['"' => '\\"', '\\n' => '\\\\n', '\\t' => '\\\\t']);

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}