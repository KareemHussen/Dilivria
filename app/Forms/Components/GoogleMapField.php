<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class GoogleMapField extends Field
{
    protected string $view = 'forms.components.google-map-field';

    public function apiKey(string $apiKey): static
    {
        return $this->extraAttributes(['data-api-key' => $apiKey]);
    }

    public function latField(string $statePath): static
    {
        return $this->extraAttributes(array_merge($this->getExtraAttributes(), ['lat-field' => $statePath]));
    }

    public function lngField(string $statePath): static
    {
        return $this->extraAttributes(array_merge($this->getExtraAttributes(), ['lng-field' => $statePath]));
    }
}
