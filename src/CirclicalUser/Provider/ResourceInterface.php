<?php

namespace CirclicalUser\Provider;

interface ResourceInterface
{
    public function getClass() : string;

    public function getId() : string;
}