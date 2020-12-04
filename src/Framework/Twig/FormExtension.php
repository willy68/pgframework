<?php

namespace Framework\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class FormExtension extends AbstractExtension
{
    /**
   * Undocumented function
   *
   * @return array
   */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('field', [$this, 'field'], [
                'is_safe' => ['html'],
                'needs_context' => true
            ])
        ];
    }

    /**
     * Undocumented function
     *
     * @param array $context
     * @param string $key
     * @param mixed $value
     * @param string|null $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, $value, ?string $label = null, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $error = $this->getErrorHTML($context, $key);
        $value = $this->convertValue($value);
        $attributes = [
            'class' => ($options['class'] ?? '') . 'form-control',
            'name'  => $key,
            'id'    => $key
        ];
        if ($error) {
            $attributes['class'] .= ' is-invalid';
        } elseif (isset($context['submited']) && $context['submited']) {
            $attributes['class'] .= ' is-valid';
        }
        if ($type === 'textarea') {
            $input = $this->textarea($value, $attributes);
        } elseif ($type === 'file') {
            $input = $this->file($attributes);
        } elseif ($type === 'checkbox') {
            $input = $this->checkbox($value, $attributes);
        } elseif (array_key_exists('options', $options)) {
            $input = $this->select($value, $options['options'], $attributes);
        } else {
            $input = $this->input($value, $attributes);
        }
        return "<div class=\"form-group\">
        <label for=\"{$key}\">{$label}</label>
        {$input}
        {$error}
      </div>";
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return string
     */
    private function convertValue($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }

    /**
     * Undocumented function
     *
     * @param array $context
     * @param string $key
     * @return string
     */
    private function getErrorHTML(array $context, string $key): string
    {
        $error = $context['errors'][$key] ?? false;
        if ($error) {
            return "<div class=\"invalid-feedback\">{$error}</div>";
        }
        return "";
    }

    /**
     * Undocumented function
     *
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function textarea(?string $value, array $attributes): string
    {
        return "<textarea " .
            $this->getHtmlFromArray($attributes) .
            " rows=\"10\">{$value}</textarea>";
    }


    /**
     * Undocumented function
     *
     * @param string|null $value
     * @param array $options
     * @param array $attributes
     * @return string
     */
    public function select(?string $value, array $options, array $attributes): string
    {
        
        $htmlOptions = array_reduce(
            array_keys($options),
            function (string $html, string $key) use ($options, $value) {
                $params = ['value' => $key, 'selected' => $key === $value];
                return $html .
                "<option " . $this->getHtmlFromArray($params) . " > {$options[$key]} </option>";
            },
            ""
        );
        return "<select " .
        $this->getHtmlFromArray($attributes) .
        ">{$htmlOptions}</select>";
    }

    /**
     * Undocumented function
     *
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function input(?string $value, array $attributes): string
    {
        return "<input type=\"text\"" .
            $this->getHtmlFromArray($attributes) .
            " value=\"{$value}\"/>";
    }

    /**
     * Undocumented function
     *
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function checkbox(?string $value, array $attributes): string
    {
        $html = "<input type=\"hidden\"" .
        " name=\"" . $attributes['name'] . "\"" .
        " value=\"0\"/>";
        if ($value) {
            $attributes['checked'] = true;
        }
        return $html .
            "<input type=\"checkbox\" " .
            $this->getHtmlFromArray($attributes) .
            " value=\"1\"/>";
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     * @return void
     */
    public function file(array $attributes)
    {
        return "<input type=\"file\"" .
            $this->getHtmlFromArray($attributes) .
            " />";
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes): string
    {
        $htmlParts = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlParts[] = "$key";
            } elseif ($value !== false) {
                $htmlParts[] = "$key=\"$value\"";
            }
        }

        return implode(' ', $htmlParts);
    }
}
