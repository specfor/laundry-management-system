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

    public static function generateTemplate(string $templateName, int $templateType, array $placeholders): string|null
    {
        $loader = new FilesystemLoader(self::getTemplateBasePath($templateType));
        $twig = new Environment($loader, [
            'cache' => FileHandler::getBaseFolder(true) . 'cache/twig_cache',
        ]);
        try {
            $template = $twig->load($templateName);
        } catch (LoaderError|SyntaxError|RuntimeError) {
            return null;
        }
        return $template->render($placeholders);
    }

    private static function getTemplateBasePath(int $templateType): string
    {
        return match ($templateType) {
            self::TEMPLATE_ACCOUNTING => FileHandler::getBaseFolder(true) . 'accounting_templates/',
            self::TEMPLATE_MAIL => FileHandler::getBaseFolder(true) . 'mail_templates/',
            self::TEMPLATE_PDF => FileHandler::getBaseFolder(true) . 'pdf_templates/',
            default => throw new Error('invalid template type.')
        };
    }
}