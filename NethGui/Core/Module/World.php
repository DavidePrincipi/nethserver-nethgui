<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * World module.
 *
 * This is the root of the modules composition.
 *
 * @package NethGuiFramework
 */
final class NethGui_Core_Module_World extends NethGui_Core_Module_Composite
{

    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;
    /**
     * @var NethGui_Core_ValidationReport
     */
    private $validationReport;

    public function __construct(NethGui_Core_ModuleInterface $currentModule)
    {
        parent::__construct('');
        $this->currentModule = $currentModule;
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        $this->validationReport = $report;
        parent::validate($report);
    }

    public function process()
    {
        $validationErrors = count($this->validationReport->getErrors()) > 0;

        foreach ($this->getChildren() as $child) {
            // FIXME: skip processing on non-core modules
            if ($validationErrors
                && substr(get_class($child), 0, 20) != 'NethGui_Core_Module_') {
                continue;
            }

            $child->process();
        }
    }

    public function prepareResponse(NethGui_Core_ResponseInterface $response)
    {
        if ($response->getFormat() == NethGui_Core_ResponseInterface::HTML) {
            $response->setViewName('NethGui_Core_View_decoration');

            $this->parameters = array(
                'cssMain' => base_url() . 'css/main.css',
                'js' => array(
                    'base' => base_url() . 'js/jquery-1.5.1.min.js',
                    'ui' => base_url() . 'js/jquery-ui-1.8.10.custom.min.js',
                    'test' => base_url() . 'js/test.js',
                ),
                'currentModule' => $response->getInnerResponse($this->currentModule),
            );
        }

        parent::prepareResponse($response);
    }

}