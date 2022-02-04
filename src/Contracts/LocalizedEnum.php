<?php

namespace mindtwo\NativeEnum\Contracts;

interface LocalizedEnum
{
    /**
     * Get the default localization name.
     *
     * @return string
     */
    public static function getLocalizationName();
}
