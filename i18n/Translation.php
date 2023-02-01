<?php

use \Symfony\Component\Translation\Loader\ArrayLoader;
use \Symfony\Component\Translation\Translator;

class Translation {

    public function create($lang = 'en') {
        $translator = new Translator($lang);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            require_once (__DIR__ . '/languages/' . $lang . '.php'),
            $lang
        );
        return $translator;
    }

}
