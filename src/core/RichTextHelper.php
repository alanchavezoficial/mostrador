<?php
declare(strict_types=1);

/**
 * RichTextHelper
 * Helper para procesar y sanitizar contenido de Rich Text Editor
 */

class RichTextHelper
{
    /**
     * Sanitiza HTML para prevenir XSS
     * Mantiene formatos seguros, elimina scripts y atributos peligrosos
     */
    public static function sanitizeHTML(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Crear DOM document
        $dom = new DOMDocument();
        $dom->encoding = 'UTF-8';
        
        // Suprimir warnings de HTML malformado
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Lista de tags permitidos
        $allowedTags = [
            'p', 'br', 'strong', 'em', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'blockquote',
            'a', 'img',
            'div', 'span',
            // Permitir <font color> para compatibilidad con execCommand('foreColor')
            'font'
        ];

        // Atributos permitidos por tag
        $allowedAttributes = [
            'a' => ['href', 'title', 'style'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'div' => ['style'],
            'span' => ['style'],
            'p' => ['style'],
            'strong' => ['style'],
            'em' => ['style'],
            'u' => ['style'],
            's' => ['style'],
            'strike' => ['style'],
            'h1' => ['style'],
            'h2' => ['style'],
            'h3' => ['style'],
            'h4' => ['style'],
            'h5' => ['style'],
            'h6' => ['style'],
            'li' => ['style'],
            'blockquote' => ['style'],
            // Atributo seguro para color en <font>
            'font' => ['color']
        ];

        self::removeScripts($dom);
        self::removeUnallowedTags($dom, $allowedTags);
        self::removeUnallowedAttributes($dom, $allowedAttributes);
        self::sanitizeURLs($dom);

        $html = $dom->saveHTML();
        
        // Limpiar tags de XML
        $html = str_replace(['<?xml encoding="UTF-8">', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">'], '', $html);
        $html = str_replace(['<html>', '</html>', '<body>', '</body>'], '', $html);

        return trim($html);
    }

    /**
     * Elimina scripts y event handlers
     */
    private static function removeScripts(\DOMDocument $dom): void
    {
        $scripts = $dom->getElementsByTagName('script');
        for ($i = $scripts->length; --$i >= 0;) {
            $scripts->item($i)->parentNode->removeChild($scripts->item($i));
        }

        // Buscar y eliminar event handlers en atributos
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*[@*[starts-with(name(), "on")]]');
        
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            foreach ($element->attributes as $attr) {
                if (strpos($attr->nodeName, 'on') === 0) {
                    $element->removeAttribute($attr->nodeName);
                }
            }
        }
    }

    /**
     * Elimina tags no permitidos pero mantiene su contenido
     */
    private static function removeUnallowedTags(\DOMDocument $dom, array $allowedTags): void
    {
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*');

        $toRemove = [];
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if (!in_array(strtolower($element->nodeName), array_merge($allowedTags, ['html', 'body', '#text']))) {
                $toRemove[] = $element;
            }
        }

        foreach ($toRemove as $element) {
            while ($element->firstChild) {
                $element->parentNode->insertBefore($element->firstChild, $element);
            }
            $element->parentNode->removeChild($element);
        }
    }

    /**
     * Elimina atributos no permitidos
     */
    private static function removeUnallowedAttributes(\DOMDocument $dom, array $allowedAttributes): void
    {
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*');

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $tag = strtolower($element->nodeName);
            $allowed = $allowedAttributes[$tag] ?? [];

            $attributesToRemove = [];
            foreach ($element->attributes as $attr) {
                if (!in_array($attr->nodeName, $allowed)) {
                    $attributesToRemove[] = $attr->nodeName;
                }
            }

            foreach ($attributesToRemove as $attrName) {
                $element->removeAttribute($attrName);
            }

            // Filtrar estilos permitidos solo a propiedades seguras
            if ($element->hasAttribute('style') && in_array('style', $allowed)) {
                $filtered = self::filterStyle($element->getAttribute('style'));
                if ($filtered !== '') {
                    $element->setAttribute('style', $filtered);
                } else {
                    $element->removeAttribute('style');
                }
            }
        }
    }

    /**
     * Filtra el contenido de style para mantener solo propiedades seguras
     */
    private static function filterStyle(string $style): string
    {
        $allowedProps = ['color', 'background-color', 'text-align'];
        $out = [];
        foreach (explode(';', $style) as $declaration) {
            $declaration = trim($declaration);
            if ($declaration === '') continue;
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) continue;
            $prop = strtolower(trim($parts[0]));
            $val = trim($parts[1]);
            if (in_array($prop, $allowedProps, true)) {
                // Saneamos valores básicos
                $val = preg_replace('/[^#a-zA-Z0-9,\.\(\)\s%-]/', '', $val);
                $out[] = $prop . ': ' . $val;
            }
        }
        return implode('; ', $out);
    }

    /**
     * Sanitiza URLs para prevenir javascript: y data:
     */
    private static function sanitizeURLs(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        
        // Links
        $links = $xpath->query('//a[@href]');
        /** @var \DOMElement $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (!self::isValidURL($href)) {
                $link->setAttribute('href', '#');
            }
        }

        // Imágenes
        $images = $xpath->query('//img[@src]');
        /** @var \DOMElement $img */
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (!self::isValidImageURL($src)) {
                $img->removeAttribute('src');
            }
        }
    }

    /**
     * Valida que una URL sea segura
     */
    private static function isValidURL(string $url): bool
    {
        // Rechazar javascript: y data:
        if (stripos($url, 'javascript:') === 0 || stripos($url, 'data:') === 0) {
            return false;
        }

        return !empty($url);
    }

    /**
     * Valida que una URL de imagen sea segura
     */
    private static function isValidImageURL(string $url): bool
    {
        // Permitir URLs relativas
        if (strpos($url, '/') === 0 || strpos($url, 'http') === 0) {
            return !empty($url);
        }

        return false;
    }

    /**
     * Genera vista previa del contenido
     */
    public static function generatePreview(?string $html, int $maxLength = 150): string
    {
        $text = strip_tags($html);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
        }

        return $text;
    }

    /**
     * Valida que el contenido tenga texto
     */
    public static function hasContent(?string $html): bool
    {
        $text = strip_tags($html);
        return !empty(trim($text));
    }
}
