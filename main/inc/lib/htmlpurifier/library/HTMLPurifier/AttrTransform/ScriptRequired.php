<?php

/**
 * Implements required attribute stipulation for <script type="text/javascript">
 */
class HTMLPurifier_AttrTransform_ScriptRequired extends HTMLPurifier_AttrTransform
{
    public function transform($attr, $config, $context) {
        if (!isset($attr['type'])) {
            $attr['type'] = 'text/javascript';
        }
        return $attr;
    }
}

// vim: et sw=4 sts=4
