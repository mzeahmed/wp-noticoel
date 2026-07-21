<?php

declare(strict_types=1);

namespace WpNoticoel\CsFixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
 * Puts one argument per line on method attributes with 2 arguments or more
 * (e.g. #[RestRoute(path: ..., methods: ..., permission: ...)]).
 *
 * Intentionally limited scope: a single attribute per #[...] group with
 * arguments (not grouped #[Foo(...), Bar(...)]); groups already split across
 * several lines are left untouched (idempotent).
 */
final class SplitMethodAttributeArgsFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    private const int MIN_ARGS_TO_SPLIT = 2;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Puts one argument per line on method attributes with 2 arguments or more.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<?php
class SomeController
{
    #[RestRoute(path: 'broadcast/events', methods: 'GET', permission: [self::class, 'adminOrAssistant'])]
    public function events(\WP_REST_Request $request): \WP_REST_Response
    {
    }
}

CODE_SAMPLE
                ),
            ]
        );
    }

    public function getName(): string
    {
        return 'WpNoticoel/split_method_attribute_args';
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(FCT::T_ATTRIBUTE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $groups = [];
        $index = 0;

        while (null !== $index = $tokens->getNextTokenOfKind($index, [[FCT::T_ATTRIBUTE]])) {
            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
            $groups[] = [$index, $endIndex];
            $index = $endIndex + 1;
        }

        // Processed right to left: insertions within a group only shift indexes
        // located after it, so previous (unprocessed) groups stay valid.
        foreach (array_reverse($groups) as [$startIndex, $endIndex]) {
            if (!$this->isMethodAttribute($tokens, $endIndex)) {
                continue;
            }

            $this->fixGroup($tokens, $startIndex, $endIndex);
        }
    }

    private function isMethodAttribute(Tokens $tokens, int $attributeCloseIndex): bool
    {
        $index = $attributeCloseIndex;

        while (true) {
            $index = $tokens->getNextMeaningfulToken($index);

            if (null === $index) {
                return false;
            }

            $token = $tokens[$index];

            if ($token->isGivenKind(\T_FUNCTION)) {
                return true;
            }

            if ($token->isGivenKind(FCT::T_ATTRIBUTE)) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
                continue;
            }

            if ($token->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_STATIC, \T_FINAL, \T_ABSTRACT, \T_READONLY])) {
                continue;
            }

            return false;
        }
    }

    private function fixGroup(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $openParenIndex = null;

        for ($i = $startIndex + 1; $i < $endIndex; $i++) {
            $content = $tokens[$i]->getContent();

            if ('(' === $content) {
                $openParenIndex = $i;
                break;
            }

            // Comma at top level before any '(' => several bare attributes grouped
            // together (#[Foo, Bar(...)]): out of scope, left as-is.
            if (',' === $content) {
                return;
            }
        }

        if (null === $openParenIndex) {
            return;
        }

        $closeParenIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenIndex);

        // #[Foo(...), Bar(...)]: several attributes in the same group, out of scope.
        $afterClose = $tokens->getNextMeaningfulToken($closeParenIndex);
        if (null !== $afterClose && $afterClose < $endIndex && $tokens[$afterClose]->equals(',')) {
            return;
        }

        $commaIndexes = $this->findTopLevelCommas($tokens, $openParenIndex, $closeParenIndex);

        if (\count($commaIndexes) < self::MIN_ARGS_TO_SPLIT - 1) {
            return;
        }

        if ($this->isAlreadyMultiline($tokens, $openParenIndex, $closeParenIndex)) {
            return;
        }

        $baseIndent = $this->getLineIndent($tokens, $startIndex);
        $argIndent = $baseIndent . $this->whitespacesConfig->getIndent();
        $lineEnding = $this->whitespacesConfig->getLineEnding();

        // Still right to left, for the same reason as above.
        $this->setWhitespaceBefore($tokens, $closeParenIndex, $lineEnding . $baseIndent);

        foreach (array_reverse($commaIndexes) as $commaIndex) {
            $this->setWhitespaceAfter($tokens, $commaIndex, $lineEnding . $argIndent);
        }

        $this->setWhitespaceAfter($tokens, $openParenIndex, $lineEnding . $argIndent);
    }

    /**
     * @return int[]
     */
    private function findTopLevelCommas(Tokens $tokens, int $openIndex, int $closeIndex): array
    {
        $commas = [];
        $depth = 0;

        for ($i = $openIndex + 1; $i < $closeIndex; $i++) {
            $content = $tokens[$i]->getContent();

            // Comparing raw content, not Token::equals(): php-cs-fixer reassigns a
            // custom kind to array literal '['/']' (CT::T_ARRAY_BRACKET_*), so
            // equals('[') wouldn't match them even though they do delimit a level.
            if (\in_array($content, ['(', '[', '{'], true)) {
                $depth++;
                continue;
            }

            if (\in_array($content, [')', ']', '}'], true)) {
                $depth--;
                continue;
            }

            if (0 === $depth && ',' === $content) {
                $commas[] = $i;
            }
        }

        return $commas;
    }

    private function isAlreadyMultiline(Tokens $tokens, int $openIndex, int $closeIndex): bool
    {
        for ($i = $openIndex; $i <= $closeIndex; $i++) {
            if (str_contains($tokens[$i]->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }

    private function getLineIndent(Tokens $tokens, int $attributeStartIndex): string
    {
        $prevIndex = $attributeStartIndex - 1;

        if ($prevIndex < 0 || !$tokens[$prevIndex]->isWhitespace()) {
            return '';
        }

        $content = $tokens[$prevIndex]->getContent();
        $lastNewlinePos = strrpos($content, "\n");

        if (false === $lastNewlinePos) {
            return '';
        }

        return substr($content, $lastNewlinePos + 1);
    }

    private function setWhitespaceAfter(Tokens $tokens, int $index, string $whitespace): void
    {
        $nextIndex = $index + 1;

        if (isset($tokens[$nextIndex]) && $tokens[$nextIndex]->isWhitespace()) {
            $tokens[$nextIndex] = new Token([\T_WHITESPACE, $whitespace]);

            return;
        }

        $tokens->insertAt($nextIndex, new Token([\T_WHITESPACE, $whitespace]));
    }

    private function setWhitespaceBefore(Tokens $tokens, int $index, string $whitespace): void
    {
        $prevIndex = $index - 1;

        if (isset($tokens[$prevIndex]) && $tokens[$prevIndex]->isWhitespace()) {
            $tokens[$prevIndex] = new Token([\T_WHITESPACE, $whitespace]);

            return;
        }

        $tokens->insertAt($index, new Token([\T_WHITESPACE, $whitespace]));
    }
}
