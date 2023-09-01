<?php

namespace LogicLeap\PhpServerCore;

use Error;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class TemplateEngine
{
    public const TEMPLATE_ACCOUNTING = 0;
    public const TEMPLATE_MAIL = 1;
    public const TEMPLATE_PDF = 2;

    /**
     * Get rendered template.
     * @param string $templateName File name of the template
     * @param int $templateType Type of the template. Must be one of TEMPLATE_... constants defined in the class
     * @param array $contextArray An associative array of placeholders and their values.
     * @return string|null Return rendered template. null if any error.
     */
    public static function generateTemplate(string $templateName, int $templateType, array $contextArray): string|null
    {
        $loader = new FilesystemLoader(self::getTemplateBasePath($templateType));
        $twig = new Environment($loader, [
            'cache' => FileHandler::getBaseFolder(true) . 'cache/twig_cache',
        ]);
        try {
            $template = $twig->load($templateName);
        } catch (LoaderError|SyntaxError) {
            return null;
        }catch (RuntimeError){
            rmdir(FileHandler::getBaseFolder(true) . 'cache/twig_cache');
            return null;
        }
        return $template->render($contextArray);
    }

    private static function getTemplateBasePath(int $templateType): string
    {
        return match ($templateType) {
            self::TEMPLATE_ACCOUNTING => FileHandler::getBaseFolder(true) . 'templates/accounting/',
            self::TEMPLATE_MAIL => FileHandler::getBaseFolder(true) . 'templates/mail/',
            self::TEMPLATE_PDF => FileHandler::getBaseFolder(true) . 'templates/pdf/',
            default => throw new Error('invalid template type.')
        };
    }
}