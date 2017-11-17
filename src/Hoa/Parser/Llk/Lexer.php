<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Hoa\Compiler\Llk;

use Hoa\Compiler;

/**
 * Class \Hoa\Compiler\Llk\Lexer.
 *
 * Lexical analyser, i.e. split a string into a set of lexeme, i.e. tokens.
 *
 * @copyright  Copyright © 2007-2017 Hoa community
 * @license    New BSD License
 */
class Lexer
{
    /**
     * Lexer state.
     *
     * @var array
     */
    protected $_lexerState  = null;

    /**
     * Text.
     *
     * @var string
     */
    protected $_text        = null;

    /**
     * Tokens.
     *
     * @var array
     */
    protected $_tokens      = [];

    /**
     * Namespace stacks.
     *
     * @var \SplStack
     */
    protected $_nsStack     = null;

    /**
     * PCRE options.
     *
     * @var string
     */
    protected $_pcreOptions = null;

    /**
     * Constructor.
     *
     * @param   array  $pragmas    Pragmas.
     */
    public function __construct(array $pragmas = [])
    {
        if (!isset($pragmas['lexer.unicode']) || true === $pragmas['lexer.unicode']) {
            $this->_pcreOptions .= 'u';
        }

        return;
    }

    /**
     * Text tokenizer: splits the text in parameter in an ordered array of
     * tokens.
     *
     * @param   string  $text      Text to tokenize.
     * @param   array   $tokens    Tokens to be returned.
     * @return  \Generator
     * @throws  \Hoa\Compiler\Exception\UnrecognizedToken
     */
    public function lexMe($text, array $tokens)
    {
        $this->_text       = $text;
        $this->_tokens     = $tokens;
        $this->_nsStack    = null;
        $offset            = 0;
        $maxOffset         = strlen($this->_text);
        $this->_lexerState = 'default';
        $stack             = false;

        foreach ($this->_tokens as &$tokens) {
            $_tokens = [];

            foreach ($tokens as $fullLexeme => $regex) {
                if (false === strpos($fullLexeme, ':')) {
                    $_tokens[$fullLexeme] = [$regex, null];

                    continue;
                }

                list($lexeme, $namespace) = explode(':', $fullLexeme, 2);

                $stack |= ('__shift__' === substr($namespace, 0, 9));

                unset($tokens[$fullLexeme]);
                $_tokens[$lexeme] = [$regex, $namespace];
            }

            $tokens = $_tokens;
        }

        if (true == $stack) {
            $this->_nsStack = new \SplStack();
        }

        while ($offset < $maxOffset) {
            $nextToken = $this->nextToken($offset);

            if (null === $nextToken) {
                $error = \sprintf('Unrecognized token "%s"', \mb_substr(\substr($text, $offset), 0, 1));

                throw Compiler\Exception\UnrecognizedToken::fromOffset($error, $text, $offset);
            }

            if (true === $nextToken['keep']) {
                $nextToken['offset'] = $offset;
                yield $nextToken;
            }

            $offset += strlen($nextToken['value']);
        }

        yield [
            'token'     => 'EOF',
            'value'     => 'EOF',
            'length'    => 0,
            'namespace' => 'default',
            'keep'      => true,
            'offset'    => $offset
        ];
    }

    /**
     * Compute the next token recognized at the beginning of the string.
     *
     * @param   int  $offset    Offset.
     * @return  array
     * @throws  \Hoa\Compiler\Exception\Lexer
     */
    protected function nextToken($offset)
    {
        $tokenArray = &$this->_tokens[$this->_lexerState];

        foreach ($tokenArray as $lexeme => $bucket) {
            list($regex, $nextState) = $bucket;

            if (null === $nextState) {
                $nextState = $this->_lexerState;
            }

            $out = $this->matchLexeme($lexeme, $regex, $offset);

            if (null !== $out) {
                $out['namespace'] = $this->_lexerState;
                $out['keep']      = 'skip' !== $lexeme;

                if ($nextState !== $this->_lexerState) {
                    $shift = false;

                    if (null !== $this->_nsStack &&
                        0 !== preg_match('#^__shift__(?:\s*\*\s*(\d+))?$#', $nextState, $matches)) {
                        $i = isset($matches[1]) ? intval($matches[1]) : 1;

                        if ($i > ($c = count($this->_nsStack))) {
                            throw new Compiler\Exception\Lexer(
                                'Cannot shift namespace %d-times, from token ' .
                                '%s in namespace %s, because the stack ' .
                                'contains only %d namespaces.',
                                1,
                                [
                                    $i,
                                    $lexeme,
                                    $this->_lexerState,
                                    $c
                                ]
                            );
                        }

                        while (1 <=  $i--) {
                            $previousNamespace = $this->_nsStack->pop();
                        }

                        $nextState = $previousNamespace;
                        $shift     = true;
                    }

                    if (!isset($this->_tokens[$nextState])) {
                        throw new Compiler\Exception\Lexer(
                            'Namespace %s does not exist, called by token %s ' .
                            'in namespace %s.',
                            2,
                            [
                                $nextState,
                                $lexeme,
                                $this->_lexerState
                            ]
                        );
                    }

                    if (null !== $this->_nsStack && false === $shift) {
                        $this->_nsStack[] = $this->_lexerState;
                    }

                    $this->_lexerState = $nextState;
                }

                return $out;
            }
        }

        return null;
    }

    /**
     * Check if a given lexeme is matched at the beginning of the text.
     *
     * @param   string  $lexeme    Name of the lexeme.
     * @param   string  $regex     Regular expression describing the lexeme.
     * @param   int     $offset    Offset.
     * @return  array
     * @throws  \Hoa\Compiler\Exception\Lexer
     */
    protected function matchLexeme($lexeme, $regex, $offset)
    {
        $_regex = str_replace('#', '\#', $regex);
        $preg   = preg_match(
            '#\G(?|' . $_regex . ')#' . $this->_pcreOptions,
            $this->_text,
            $matches,
            0,
            $offset
        );

        if (0 === $preg) {
            return null;
        }

        if ('' === $matches[0]) {
            throw new Compiler\Exception\Lexer(
                'A lexeme must not match an empty value, which is the ' .
                'case of "%s" (%s).',
                3,
                [$lexeme, $regex]
            );
        }

        return [
            'token'  => $lexeme,
            'value'  => $matches[0],
            'length' => mb_strlen($matches[0])
        ];
    }
}