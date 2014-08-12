<?php

namespace Acme\DemoBundle\Model;

class Todo
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string The todo title
     */
    public $title;

    /**
     * @var boolean Whether the todo is marked as completed or not
     */
    public $completed;

    /**
     * String representation for a todo
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}
