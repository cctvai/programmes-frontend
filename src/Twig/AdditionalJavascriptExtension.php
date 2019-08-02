<?php
declare(strict_types = 1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdditionalJavascriptExtension extends AbstractExtension
{
    private $buttons = [];
    private $showPopup = false;

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_button', [$this, 'addButton']),
            new TwigFunction('get_buttons', [$this, 'getButtons']),
            new TwigFunction('add_popup', [$this, 'addPopup']),
            new TwigFunction('show_popup', [$this, 'showPopup']),
        ];
    }

    public function addButton(string $elementId, string $id, string $type, string $contextId, string $title, ?string $profile = null)
    {
        $button = [
            'element_id' => $elementId,
            'id' => $id,
            'type' => $type,
            'context_id' => $contextId,
            'title' => $title,
        ];
        if ($profile) {
            $button['profile'] = $profile;
        }
        $this->buttons[] = $button;
    }

    public function getButtons(): ?array
    {
        return $this->buttons;
    }

    public function addPopup()
    {
        $this->showPopup = true;
    }

    public function showPopup()
    {
        return $this->showPopup;
    }
}
