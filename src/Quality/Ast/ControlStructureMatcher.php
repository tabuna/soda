<?php

declare(strict_types=1);

namespace Bunnivo\Soda\Quality\Ast;

use PhpParser\Node;

/**
 * @internal
 */
final class ControlStructureMatcher
{
    private const array CONTROL_STRUCTURE_NODE_KINDS = [
        'Stmt_If'       => true,
        'Stmt_ElseIf'   => true,
        'Stmt_Else'     => true,
        'Stmt_For'      => true,
        'Stmt_Foreach'  => true,
        'Stmt_While'    => true,
        'Stmt_Do'       => true,
        'Stmt_Switch'   => true,
        'Stmt_TryCatch' => true,
        'Stmt_Catch'    => true,
        'Stmt_Finally'  => true,
    ];

    public static function isControlStructure(Node $syntaxNode): bool
    {
        return isset(self::CONTROL_STRUCTURE_NODE_KINDS[$syntaxNode->getType()]);
    }
}
