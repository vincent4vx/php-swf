<?php

namespace Swf\Cli;

/**
 * A jar file executor
 *
 * This class is immutable, every call to modifier will creates a new instance.
 */
final class Jar
{
    /**
     * Path to the java executable
     *
     * @var string
     */
    private $java;

    /**
     * Path to the JAR file
     *
     * @var string
     */
    private $jar;

    /**
     * List of options, as key value
     *
     * @var array
     */
    private $options = [];

    /**
     * List of arguments
     *
     * @var string[]
     */
    private $arguments = [];


    /**
     * Jar constructor.
     *
     * @param string $jar The jar file name
     * @param string $java The java command
     */
    public function __construct(string $jar, string $java = 'java')
    {
        $this->jar = $jar;
        $this->java = $java;
    }

    /**
     * Add an argument
     *
     * @param string $argument The argument
     *
     * @return static The new Jar instance with the added argument
     */
    public function argument(string $argument): self
    {
        $jar = clone $this;
        $jar->arguments[] = $argument;

        return $jar;
    }

    /**
     * Set an option value
     *
     * @param string $option The option name (without "-")
     * @param mixed $value The option value
     *
     * @return static The new Jar instance with the added argument
     */
    public function option(string $option, $value): self
    {
        $jar = clone $this;
        $jar->options[$option] = $value;

        return $jar;
    }

    /**
     * Execute the JAR command
     *
     * @return string The standard output
     *
     * @throws JarException
     */
    public function execute(): string
    {
        exec($this . ' 2>&1', $output, $code);

        if ($code !== 0) {
            throw new JarException(implode(PHP_EOL, $output));
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Render the command
     *
     * @return string
     */
    public function __toString(): string
    {
        $cmd = $this->java.' -jar '.escapeshellarg($this->jar);

        foreach ($this->options as $name => $value) {
            $cmd .= ' -'.$name.' '.escapeshellarg($value);
        }

        foreach ($this->arguments as $argument) {
            $cmd .= ' '.escapeshellarg($argument);
        }

        return $cmd;
    }
}
