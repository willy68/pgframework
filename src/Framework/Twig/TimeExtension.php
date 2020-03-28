<?php

namespace Framework\Twig;

use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{

  /**
   * Undocumented function
   *
   * @return array
   */
    public function getFilters(): array
    {
        return [
        new TwigFilter('ago', [$this, 'ago'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param DateTime $date
     * @param string $format
     * @return string
     */
    public function ago(DateTime $date, string $format = 'd/m/Y H:i')
    {
        return '<time class="timeago" datetime="'  .
        $date->format(DateTime::ISO8601) .
        '">' .
        $date->format($format) .
        '</time>';
    }
}
