<?php

namespace PhpConsole\Command;

use DomainException;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractCommand
{
    /**
     *  Help annotations.
     *
     * @var array
     */
    protected $annotations;

    const COLOR_FAILURE = 31; //red
    const COLOR_SUCCESS = 32; //green
    const COLOR_WARNING = 36; //yellow

    /**
     * @param array|null $arguments
     */
    public function __construct($arguments = null)
    {
        if (PHP_SAPI != 'cli' || !is_array($arguments)) {
            throw new DomainException('Please run script from cli');
        }

        $this->getAnnotations();

        parse_str(implode('&', array_slice($arguments, 1)), $options);

        if (empty($options) || isset($options['--help']) || isset($options['?'])) {
            $this->displayHelp();

            return;
        }

        foreach ($this->annotations['methods'] as $methodName => $methodProperties) {
            if (isset($options[$methodName])) {
                $methodOptions = [];
                foreach ($methodProperties['parameters'] as $parameterName => $parameterProperties) {
                    $parameterName = "-{$parameterName}";
                    if (!isset($options[$parameterName]) && !$parameterProperties['optional']) {
                        $this->error("Missing parameter '{$parameterName}' for method {$methodName} ({$parameterProperties['description']})");

                        return;
                    }
                    $parameterValue = $this->castParameter($options[$parameterName], $parameterProperties['type']);
                    $methodOptions[] = (!$parameterProperties['optional'] || $parameterValue !== null) ? $parameterValue : null;
                }
                echo $this->stringColor("{$methodName}: ".call_user_func_array([$this, $methodName],
                            $methodOptions)).PHP_EOL;

                return;
            }
        }
        $this->error('Invalid Method specified');
    }

    /**
     * Extract class annotations.
     *
     * @return array
     */
    protected function getAnnotations()
    {
        if (!$this->annotations) {
            $reflection = new ReflectionClass($this);
            //parsing class comment
            $programDocComment = $reflection->getDocComment();
            $this->annotations['programTitle'] = $this->parseAnnotationBlock('title', $programDocComment);
            $this->annotations['programVersion'] = $this->parseAnnotationBlock('version', $programDocComment);
            $this->annotations['programUsage'] = $this->parseAnnotationBlock('usage', $programDocComment);
            $this->annotations['methods'] = [];
            //parsing methods' comments
            foreach ($reflection->getMethods(ReflectionMethod::IS_FINAL) as $method) {
                $methodName = $method->getName();
                //cleaning doc comment
                $methodDocComment = str_replace(['/**', ' */', '*'], '', $method->getDocComment());
                //getting method description
                $description = trim(substr($methodDocComment, 0, strpos($methodDocComment, '@')));
                //getting method parameters
                $methodParameters = $method->getParameters();
                $parameters = [];
                //parsing parameters
                foreach ($methodParameters as $parameter) {
                    $parameterName = $parameter->getName();
                    preg_match_all(
                        '`@param\s+(string|int|array|bool)\s+\$'.$parameterName.'\s*(.*)`',
                        $methodDocComment,
                        $matches
                    );
                    $parameters[$parameterName] = [
                        'optional'    => $parameter->isOptional(),
                        'type'        => $matches[1][0],
                        'description' => $matches[2][0],
                    ];
                }

                $this->annotations['methods'][$methodName] = [
                    'description' => $description,
                    'parameters'  => $parameters,
                ];
            }
        }

        return $this->annotations;
    }

    /**
     * Extract annotation block.
     *
     * @param string $type
     * @param string $docCommentBlock
     *
     * @return string
     */
    private function parseAnnotationBlock($type, $docCommentBlock)
    {
        $match = [];
        preg_match(
            '`@'.$type.'\s+(.*)`',
            $docCommentBlock,
            $match
        );

        return isset($match[1]) ? $match[1] : '';
    }

    protected function displayHelp()
    {
        $help = $this->stringColor(
                "{$this->annotations['programTitle']} {$this->annotations['programVersion']}",
                self::COLOR_SUCCESS,
                true
            ).PHP_EOL;
        $help .= "Usage: {$this->annotations['programUsage']}".PHP_EOL;
        $help .= 'Methods :'.PHP_EOL;
        foreach ($this->annotations['methods'] as $methodName => $methodProperties) {
            $help .= " * {$methodName}: {$methodProperties['description']}".PHP_EOL;
            $help .= '    Options:'.PHP_EOL;
            foreach ($methodProperties['parameters'] as $parameterName => $parameterProperties) {
                $help .= "     -{$parameterName}: ({$parameterProperties['type']}) {$parameterProperties['description']}".($parameterProperties['optional'] ? ' (optional)' : '').PHP_EOL;
            }
            $help .= PHP_EOL;
        }
        echo $help.PHP_EOL;
    }

    /**
     * @param string $text
     * @param int    $color
     * @param bool   $bold
     *
     * @return string
     */
    protected function stringColor($text, $color = self::COLOR_SUCCESS, $bold = false)
    {
        return chr(27).'['.($bold ? '1' : '0').';'.$color.'m'.$text.chr(27).'[0m';
    }

    /**
     * @param string $error
     */
    protected function error($error)
    {
        echo $this->stringColor(
                "Error! {$error}",
                self::COLOR_FAILURE,
                true
            ).PHP_EOL;
        $this->displayHelp();
    }

    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return array|bool|int|mixed
     */
    protected function castParameter($value, $type)
    {
        switch ($type) {
            case 'int':
                return (int) $value;
            case 'array':
                return json_decode($value);
            case 'bool':
                return (bool) $value;
            default:
                return $value;
        }
    }
}
