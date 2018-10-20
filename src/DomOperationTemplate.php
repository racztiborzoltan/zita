<?php
declare(strict_types=1);

namespace Zita;

use DomOperationQueue\DomOperationInterface;

abstract class DomOperationTemplate implements DomOperationInterface
{

    /**
     * Template variables
     *
     * @var array
     */
    private $_template_variables = [];

    public function setTemplateVariable($name, $value): self
    {
        $this->_template_variables[$name] = $value;
        return $this;
    }

    public function hasTemplateVariable($name): bool
    {
        return isset($this->_template_variables[$name]);
    }

    public function getTemplateVariable($name, $default_value = null)
    {
        return $this->hasTemplateVariable($name) ? $this->_template_variables[$name] : $default_value;
    }

    public function removeTemplateVariable($name): self
    {
        unset($this->_template_variables[$name]);
        return $this;
    }

    public function getTemplateVariables(): array
    {
        return $this->_template_variables;
    }

    /**
     * Replace all template variables
     *
     * @param array $template_variables
     * @return self
     */
    public function setTemplateVariables(array $template_variables): self
    {
        $this->_template_variables = $template_variables;
        return $this;
    }

    abstract function executeDomOperation(\DOMDocument $dom_document): \DOMDocument;
}
