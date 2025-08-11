<?php

namespace App\Helpers;

class BreadcrumbsGenerator
{
    protected array $items = [];

    /**
     * Dodaje element do ścieżki breadcrumbs.
     *
     * @param  string  $title  Tytuł elementu breadcrumb.
     * @param  string  $href  Adres URL elementu breadcrumb.
     * @return $this
     */
    public function add(string $title, string $href): self
    {
        $this->items[] = [
            'title' => $title,
            'href' => $href,
        ];

        return $this;
    }

    /**
     * Zwraca wszystkie elementy breadcrumbs.
     */
    public function get(): array
    {
        return $this->items;
    }

    /**
     * Statyczna metoda do łatwego tworzenia instancji i dodawania elementów.
     *
     * @param  string  $title  Tytuł pierwszego elementu breadcrumb.
     * @param  string  $href  Adres URL pierwszego elementu breadcrumb.
     */
    public static function make(string $title, string $href): self
    {
        return (new self)->add($title, $href);
    }
}
