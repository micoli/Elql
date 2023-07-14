#!/usr/bin/env php
<?php

declare(strict_types=1);

include 'vendor/autoload.php';

use phpDocumentor\Reflection\DocBlockFactory;

class MarkdownUpdater
{
    private DocBlockFactory $docblockFactory;

    public function __construct()
    {
        $this->docblockFactory = DocBlockFactory::createInstance();
    }

    public function parse(string $filename): void
    {
        $content = file_get_contents($filename);

        $content = $this->parseTags($content, 'command', $this->getCommandResult(...));
        $content = $this->parseTags($content, 'class-method-summary', $this->getClassMethodSummary(...));
        $content = $this->parseTags($content, 'class-method-code', $this->getClassMethodCode(...));
        $content = $this->parseTags($content, 'class-method-documentation', $this->getClassMethodAnnotations(...));
        $content = $this->parseTags($content, 'classes-methods-comparator', $this->getClassesMethodsComparator(...));
        $content = $this->parseTags($content, 'include', $this->getInclude(...));

        file_put_contents($filename, $content);
    }

    public function parseTags(string $content, string $tag, callable $executor): string
    {
        return preg_replace_callback(
            sprintf('!\[//]: <> \(%s-placeholder-start "(.*?)" "(.*?)"\)(.*?)\[//]: <> \(%s-placeholder-end\)!sim', $tag, $tag),
            fn(array $match) => implode(PHP_EOL, [
                sprintf('[//]: <> (%s-placeholder-start "%s" "%s")', $tag, $match[1], $match[2]),
                $executor($match[1], $match[2]),
                '',
                sprintf('[//]: <> (%s-placeholder-end)', $tag),
            ]),
            $content,
        );
    }

    private function getClassMethodAnnotations(string $className, string $prefix): string
    {
        $blocks = [];
        $reflectionClass = new ReflectionClass($className);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $content = explode(PHP_EOL, file_get_contents($method->getFileName()));
            $body = implode(PHP_EOL, array_slice($content, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
            $comment = $method->getDocComment() ?: '';
            $comment = preg_replace('!^\s*\* @.*$!sim', '', $comment);
            $docBlock = $comment === '' ? '' : $this->docblockFactory->create($comment)->getSummary();
            $blocks[$method->getName()] = [
                'name' => $method->getName(),
                'doc' => str_replace("\n", "\n\n", $docBlock),
                'declaration' => trim(preg_replace('!(.*?)\{(.*)!sim', '\1', $body)),
            ];
        }
        ksort($blocks);
        $result = '';
        foreach ($blocks as $methodName => $block) {
            $result .= <<<BLOCK

                {$prefix}`{$reflectionClass->getShortName()}::{$methodName}` <a id="{$reflectionClass->getShortName()}__{$methodName}"></a>

                `{$block['declaration']}`
                
                {$block['doc']}
                BLOCK;
        }

        return $result;
    }

    private function getClassMethodSummary(string $className, string $prefix): string
    {
        $blocks = [];
        $reflectionClass = new ReflectionClass($className);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $blocks[$method->getName()] = $method->getName();
        }
        ksort($blocks);
        $result = '';
        foreach ($blocks as $block) {
            $result .= <<<BLOCK

                {$prefix} [$block](#user-content-{$reflectionClass->getShortName()}__{$block})
                BLOCK;
        }

        return $result;
    }

    private function getClassMethodCode(string $classNameAndMethod, string $prefix): string
    {
        $parts = explode('::', $classNameAndMethod);
        $reflectionClass = new ReflectionClass($parts[0]);
        $method = $reflectionClass->getMethod($parts[1]);
        $content = explode(PHP_EOL, file_get_contents($method->getFileName()));
        $body = implode(PHP_EOL, array_slice($content, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

        return <<<BLOCK
            ```php
            {$body}
            ```
            BLOCK;
    }

    private function getClassesMethodsComparator(string $classNames, string $prefix): string
    {
        $methodNames = [];
        $classNames = explode(',', $classNames);
        $classShortNames = [];
        foreach ($classNames as $className) {
            $reflectionClass = new ReflectionClass($className);
            $classShortNames[] = $reflectionClass->getShortName();
        }
        foreach ($classNames as $className) {
            $reflectionClass = new ReflectionClass($className);
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $shortName = $reflectionClass->getShortName();
                $methodName = $method->getName();
                if (!array_key_exists($methodName, $methodNames)) {
                    $methodNames[$methodName] = array_fill_keys($classShortNames, ' ');
                }
                $methodNames[$methodName][$shortName] = '[x]';
            }
        }
        ksort($methodNames);
        $line = 0;
        $result = '';
        foreach ($methodNames as $methodName => $availibility) {
            if ($line % 20 === 0) {
                $result .= '|  | **' . implode('** | **', array_keys($availibility)) . '** |' . PHP_EOL;
            }
            if ($line === 0) {
                $result .= '|---| ' . implode(' | ', array_map(fn($tmp) => '--', $availibility)) . ' |' . PHP_EOL;
            }
            $result .= '| ' . $methodName . ' | ' . implode(' | ', $availibility) . ' |' . PHP_EOL;
            $line++;
        }

        return PHP_EOL . $result . PHP_EOL;
    }

    private function getInclude(string $filename, string $prefix): string
    {
        $content = file_get_contents($filename);
        $file = new SplFileInfo($filename);

        return <<<BLOCK

            File: `{$prefix}`
            ```{$file->getExtension()}
            {$content}
            ```
            BLOCK;
    }

    private function getCommandResult(string $command, string $prefix): string
    {
        $result = trim(shell_exec($command));

        return <<<BLOCK
            {$prefix}`{$command}`
            ```
            {$result}
            ```
            BLOCK;
    }
}

$parser = new MarkdownUpdater();
$parser->parse($argv[1]);
