<?php

namespace Babel;

use \V8JsException;
use \ErrorException;
use \V8Js;

class Transpiler {
    public static $v8;
    public static $babel;

    /**
     * Transform the source code
     * @param  string $sourceCode Source code to transform
     * @param array $options Associative array of options that will be passed to Babel
     * @return string             Transformed source code
     * @throws V8JsException
     */
    public static function transform($sourceCode, $options = []) {
        $options = array_merge($options, [ 'ast' => false ]);
        // The compiled bundle will use this attributes as
        // `PHP.sourceCode` and `PHP.babelOptions`.
        // Check `src/executor.js`.
        self::$v8->sourceCode = $sourceCode;
        self::$v8->babelOptions = $options;
        $transpiled_str = false;
	    ob_start();
        try {
            self::$v8->executeString(self::$babel);
            $transpiled_str = ob_get_contents();
        }
        catch( V8JsException $e ) {
           throw $e;
        } finally {
            ob_end_clean();
        }
        return $transpiled_str;
    }

    /**
     * Transform the content of a file
     * @param  string $filePath Absolute path of the file
     * @param array $options Associative array of options that will be passed to Babel
     * @return string           Transformed content of the file
     * @throws ErrorException
     */
    public static function transformFile($filePath, $options = []) {

       if ( !($fileContent = file_get_contents($filePath)) ) {
           throw new ErrorException("Can't read File {$filePath}",error_get_last());
       }

        return self::transform($fileContent, $options);
    }
}

Transpiler::$v8 = new V8Js();
Transpiler::$babel = file_get_contents(realpath(dirname(__FILE__) . '/../assets/executor.bundle.js'));
