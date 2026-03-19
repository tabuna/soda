<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\EfferentCoupling;

use PhpParser\Node;

/**
 * Delegates member-level Ce collection to focused scanners (keeps efferent Ce per class low).
 *
 * @internal
 */
final readonly class EfferentCouplingMemberScanner
{
    public function __construct(
        private EfferentCouplingSignatureTypesScanner $signatureTypes,
        private EfferentCouplingInvokeShapeScanner $invokeShapes,
        private EfferentCouplingExceptionTypesScanner $exceptionTypes,
    ) {}

    public static function wiredToTypeSink(EfferentCouplingTypeSink $types): self
    {
        return new self(
            new EfferentCouplingSignatureTypesScanner($types),
            new EfferentCouplingInvokeShapeScanner($types),
            new EfferentCouplingExceptionTypesScanner($types),
        );
    }

    public function enter(Node $node): void
    {
        $this->signatureTypes->onNode($node);
        $this->invokeShapes->onNode($node);
        $this->exceptionTypes->onNode($node);
    }
}
