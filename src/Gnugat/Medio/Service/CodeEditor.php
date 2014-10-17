<?php

/*
 * This file is part of the Medio project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\Medio\Service;

use Gnugat\Redaktilo\Editor;
use Gnugat\Redaktilo\Text;
use Gnugat\Redaktilo\Search\PatternNotFoundException;

class CodeEditor
{
    const EMPTY_LINE = '';
    const METHOD_OPENING = '    {';
    const METHOD_CLOSING = '    }';

    /**
     * @var CodeDetector
     */
    private $codeDetector;

    /**
     * @var CodeNavigator
     */
    private $codeNavigator;

    /**
     * @var Editor
     */
    private $editor;

    /**
     * @var MultilineEditor
     */
    private $multilineEditor;

    /**
     * @param CodeDetector    $codeDetector
     * @param CodeNavigator   $codeNavigator
     * @param Editor          $editor
     * @param MultilineEditor $multilineEditor
     */
    public function __construct(
        CodeDetector $codeDetector,
        CodeNavigator $codeNavigator,
        Editor $editor,
        MultilineEditor $multilineEditor
    )
    {
        $this->codeDetector = $codeDetector;
        $this->codeNavigator = $codeNavigator;
        $this->editor = $editor;
        $this->multilineEditor = $multilineEditor;
    }

    /**
     * @param Text   $text
     * @param string $fullyQualifiedClassname
     */
    public function addUse(Text $text, $fullyQualifiedClassname)
    {
        $useStatement = sprintf('use %s;', $fullyQualifiedClassname);

        $this->codeNavigator->goToNamespace($text);
        $this->codeNavigator->goOneLineBelow($text);
        $this->editor->insertBelow($text, $useStatement);
        if (!$this->codeDetector->hasOneUseBelow($text)) {
            $this->editor->insertBelow($text, self::EMPTY_LINE);
        }
    }

    /**
     * @param Text   $text
     * @param string $variableName
     */
    public function addProperty(Text $text, $variableName)
    {
        $property = sprintf('    private $%s;', $variableName);

        $this->codeNavigator->goToClassOpening($text);
        while ($this->codeDetector->hasOneConstantBelow($text)) {
            $this->codeNavigator->goOneConstantBelow($text);
        }
        while ($this->codeDetector->hasOnePropertyBelow($text)) {
            $this->codeNavigator->goOnePropertyBelow($text);
        }
        $this->editor->insertBelow($text, $property);
        if ($this->codeDetector->hasOnePropertyAbove($text) || $this->codeDetector->hasOneConstantAbove($text)) {
            $this->editor->insertAbove($text, self::EMPTY_LINE);

            return;
        }
        $this->editor->insertBelow($text, self::EMPTY_LINE);
    }

    /**
     * @param Text   $text
     * @param string $methodName
     * @param string $argumentName
     * @param string $argumentType
     */
    public function addArgument(Text $text, $methodName, $argumentName, $argumentType = null)
    {
        if ($this->codeDetector->hasMultilineArguments($text, $methodName)) {
            $this->multilineEditor->addArgument($text, $methodName, $argumentName, $argumentType);

            return;
        }
        $this->codeNavigator->goToMethod($text, $methodName);
        $currentConstructor = $text->getLine();
        $constructorArgument = '';
        if ($this->codeDetector->hasAnyArguments($text, $methodName)) {
            $constructorArgument .= ', ';
        }
        $constructorArgument .= $argumentType ? $argumentType.' ' : '';
        $constructorArgument .= '$'.$argumentName;
        $newConstructor = str_replace(')', $constructorArgument.')', $currentConstructor);
        $this->editor->replace($text, $newConstructor);
    }

    /**
     * @param Text   $text
     * @param string $methodName
     * @param string $variableName
     */
    public function addPropertyInitialization(Text $text, $methodName, $variableName)
    {
        $propertyInitialization = sprintf('        $this->%s = $%s;', $variableName, $variableName);
        $this->codeNavigator->goToMethodClosing($text, $methodName);
        $this->editor->insertAbove($text, $propertyInitialization);
    }

    /**
     * @param Text   $text
     * @param string $methodName
     */
    public function addMethod(Text $text, $methodName)
    {
        $method = sprintf('    public function %s()', $methodName);

        $this->codeNavigator->goToClassEnding($text);
        $wasClassEmpty = $this->codeDetector->isClassEmpty($text);
        $this->editor->insertAbove($text, self::METHOD_CLOSING);
        $this->editor->insertAbove($text, self::METHOD_OPENING);
        $this->editor->insertAbove($text, $method);
        if (!$wasClassEmpty) {
            $this->editor->insertAbove($text, self::EMPTY_LINE);
        }
    }
}