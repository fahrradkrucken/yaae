<?php


namespace FahrradKrucken\YAAE\Core;

/**
 * Class TemplateHandler
 * @package FahrradKrucken\YAAE\Core
 */
class TemplateHandler
{
    /**
     * @var string - PAth to all templates
     */
    private static $templatePath = '';

    /**
     * @param string $path
     *
     * @return string - $path with DIRECTORY_SEPARATOR
     */
    private static function normalizePath(string $path): string
    {
        return str_replace(['\\\\', '//', '\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return string
     */
    public static function getTemplatePath(): string
    {
        return self::$templatePath;
    }

    /**
     * @param string $templatePath
     */
    public static function setTemplatePath(string $templatePath): void
    {
        $templatePath = self::normalizePath($templatePath);
        self::$templatePath = is_dir($templatePath) ? $templatePath : '';
    }

    /**
     * @param string $templateName - name of the template, relative to $templatePath, without ".php" extension
     * @param array  $templateVars
     *
     * @return string - rendered ".php" template
     */
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