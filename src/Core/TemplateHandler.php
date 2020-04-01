<?php


namespace FahrradKrucken\YAAE\Core;


class TemplateHandler
{
    private static $templatePath = '';

    private static function normalizePath(string $path): string
    {
        return str_replace(['\\\\', '//', '\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    public static function getTemplatePath(): string
    {
        return self::$templatePath;
    }

    public static function setTemplatePath(string $templatePath): void
    {
        $templatePath = self::normalizePath($templatePath);
        self::$templatePath = is_dir($templatePath) ? $templatePath : '';
    }

    public static function render(string $templateName, array $templateVars = []): string
    {
        $templateName = self::normalizePath(self::$templatePath . '/' . $templateName . '.php');
        $templateContent = '';
        if (is_file($templateName)) {
            ob_start();
            if (!empty($templateVars)) extract($templateVars, EXTR_OVERWRITE);
            include($templateName);
            $templateContent = ob_get_clean();
        }
        return $templateContent;
    }
}