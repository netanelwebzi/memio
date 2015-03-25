<?php

/*
 * This file is part of the Medio project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\Medio;

use Gnugat\Medio\Validator\ModelValidator;
use Gnugat\Medio\Validator\ModelValidator\CollectionValidator;
use Gnugat\Medio\Validator\ModelValidator\ContractValidator;
use Gnugat\Medio\Validator\ModelValidator\MethodValidator;
use Gnugat\Medio\Validator\ModelValidator\ObjectValidator;
use Gnugat\Medio\Validator\ViolationCollection;

class Validator
{
    /**
     * @var array
     */
    private $modelValidators = array();

    public function __construct()
    {
        $collectionValidator = new CollectionValidator();
        $methodValidator = new MethodValidator($collectionValidator);
        $contractValidator = new ContractValidator($collectionValidator, $methodValidator);
        $objectValidator = new ObjectValidator($collectionValidator, $methodValidator);

        $this->modelValidators[] = $collectionValidator;
        $this->modelValidators[] = $contractValidator;
        $this->modelValidators[] = $methodValidator;
        $this->modelValidators[] = $objectValidator;
    }

    /**
     * @param ModelValidator $modelValidator
     */
    public function add(ModelValidator $modelValidator)
    {
        $this->modelValidators[] = $modelValidator;
    }

    /**
     * @param mixed $model
     *
     * @throws \Gnugat\Medio\Exception\InvalidModelException If model is invalid
     */
    public function validate($model)
    {
        $violations = new ViolationCollection();
        foreach ($this->modelValidators as $modelValidator) {
            if ($modelValidator->supports($model)) {
                $violations->merge($modelValidator->validate($model));
            }
        }
        $violations->raise();
    }
}
