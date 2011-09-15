<?php
namespace Kitpages\CmsBundle\Twig\Extension;

use Symfony\Component\Locale\Locale;

class DateExtension extends \Twig_Extension
{

    public static function kitStrftime($datetime, $formatdate, $locale = null)
    {
            $timestamp = $datetime->getTimestamp();
            return strftime($formatdate, $timestamp);

    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'kit_strftime' => new \Twig_Filter_Function('Kitpages\CmsBundle\Twig\Extension\DateExtension::kitStrftime'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'kit_date';
    }
}
