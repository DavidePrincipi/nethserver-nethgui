<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_Hidden extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content ='';

        if (is_null($value)) {
            $value = $this->view[$name];
        }

        if ( ! is_array($value)) {
            $value = array($name => $value);
        }

        $content .= $this->hiddenArrayRecursive($value, $flags);

        return $content;
    }


    private function hiddenArrayRecursive($valueArray, $flags, $path = array())
    {
        $content = '';

        foreach ($valueArray as $name => $value) {
            $namePath = $path;
            $namePath[] = $name;

            if (is_array($value)) {
                $content .= $this->hiddenArrayRecursive($value, $flags, $namePath);
            } else {
                $attributes = array(
                    'type' => 'hidden',
                    'value' => $value,
                    'name' => $this->view->getControlName($namePath),
                );

                $content .= $this->selfClosingTag('input', $attributes);
            }
        }

        return $content;
    }

}