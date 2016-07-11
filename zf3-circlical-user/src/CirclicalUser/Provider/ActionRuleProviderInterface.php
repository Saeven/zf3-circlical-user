<?php

namespace CirclicalUser\Provider;

interface ActionRuleProviderInterface
{

    /**
     * @param $string
     * @return ActionRuleInterface[]
     */
    public function getStringActions($string) : array;

    /**
     * @param ResourceInterface $resource
     * @return array|ActionRuleInterface[]
     */
    public function getResourceActions(ResourceInterface $resource) : array;

}