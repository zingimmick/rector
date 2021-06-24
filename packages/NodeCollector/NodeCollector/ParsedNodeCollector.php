<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * All parsed nodes grouped type
 * @see https://phpstan.org/blog/generics-in-php-using-phpdocs
 *
 * @internal To be used only in NodeRepository
 */
final class ParsedNodeCollector
{
    /**
     * @var array<class-string<Node>>
     */
    private const COLLECTABLE_NODE_TYPES = [
        \PhpParser\Node\Stmt\Class_::class,
        \PhpParser\Node\Stmt\Interface_::class,
        \PhpParser\Node\Stmt\ClassConst::class,
        \PhpParser\Node\Expr\ClassConstFetch::class,
        \PhpParser\Node\Stmt\Trait_::class,
        \PhpParser\Node\Stmt\ClassMethod::class,
        // simply collected
        \PhpParser\Node\Expr\MethodCall::class,
        // for array callable - [$this, 'someCall']
        \PhpParser\Node\Expr\Array_::class,
    ];
    /**
     * @var Class_[]
     */
    private $classes = [];
    /**
     * @var Interface_[]
     */
    private $interfaces = [];
    /**
     * @var Trait_[]
     */
    private $traits = [];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @return Interface_[]
     */
    public function getInterfaces() : array
    {
        return $this->interfaces;
    }
    /**
     * @return Class_[]
     */
    public function getClasses() : array
    {
        return $this->classes;
    }
    public function findClass(string $name) : ?\PhpParser\Node\Stmt\Class_
    {
        return $this->classes[$name] ?? null;
    }
    public function findInterface(string $name) : ?\PhpParser\Node\Stmt\Interface_
    {
        return $this->interfaces[$name] ?? null;
    }
    public function findTrait(string $name) : ?\PhpParser\Node\Stmt\Trait_
    {
        return $this->traits[$name] ?? null;
    }
    public function isCollectableNode(\PhpParser\Node $node) : bool
    {
        foreach (self::COLLECTABLE_NODE_TYPES as $collectableNodeType) {
            /** @var class-string<Node> $collectableNodeType */
            if (\is_a($node, $collectableNodeType, \true)) {
                return \true;
            }
        }
        return \false;
    }
    public function collect(\PhpParser\Node $node) : void
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $this->addClass($node);
            return;
        }
        if ($node instanceof \PhpParser\Node\Stmt\Interface_ || $node instanceof \PhpParser\Node\Stmt\Trait_) {
            $this->collectInterfaceOrTrait($node);
            return;
        }
    }
    private function addClass(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return;
        }
        $className = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->classes[$className] = $class;
    }
    /**
     * @param Interface_|Trait_ $classLike
     */
    private function collectInterfaceOrTrait(\PhpParser\Node\Stmt\ClassLike $classLike) : void
    {
        $name = $this->nodeNameResolver->getName($classLike);
        if ($name === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            $this->interfaces[$name] = $classLike;
        } elseif ($classLike instanceof \PhpParser\Node\Stmt\Trait_) {
            $this->traits[$name] = $classLike;
        }
    }
}
