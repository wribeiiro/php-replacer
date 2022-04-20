<?php

$keywords = [
    '$__halt_compilerName', '$abstract', '$and', '$array', '$as', '$break', '$callable', '$case', '$catch', '$class', '$clone', '$const', '$continue', '$declare', '$default', '$die', '$do', '$echo', '$else', '$elseif', '$empty', '$enddeclare', '$endfor', '$endforeach', '$endif', '$endswitch', '$endwhile', '$eval', '$exit', '$extends', '$final', '$for', '$foreach', '$function', '$global', '$goto', '$if', '$implements', '$include', '$include_once', '$instanceof', '$insteadof', '$interface', '$isset', '$list', '$namespace', '$new', '$or', '$print', '$private', '$protected', '$public', '$require', '$require_once', '$return', '$static', '$switch', '$throw', '$trait', '$try', '$unset', '$use', '$var', '$while', '$xor', '$HTTP_RAW_POST_DATA', '$resource', '$object', '$mixed', '$numeric', '$variavel',
];

define("KEYWORDS", $keywords);

checkFolder($argv[1]);
//checkFolder("C:\\xampp\\htdocs\\scripts-php\\teste-renamed");

/**
 * Loop in all folders and files to check wanted variables.
 *
 * @param string $target
 * @return void
 */
function checkFolder(string $target): void
{
    if (is_dir($target) && $dir = opendir($target)) {
        while (($file = readdir($dir)) !== false) {

            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullFile = $target . '/' . $file;

            if (isPiEidipiFile($fullFile)) {
                checkTokensInFile($fullFile);
                continue;
            }

            checkFolder($fullFile);
        }

        closedir($dir);
        return;
    }
}

/**
 * Verify all variables in pi eidi pi file
 *
 * @param string $file
 * @return void
 */
function checkTokensInFile(string $file): void
{
    $fileContents = file_get_contents($file);
    $tokensFromFile = tokenizerWords($fileContents);
    $fileTokens = [
        'hasReplaced' => [],
        'totalReplaced' => 0
    ];

    foreach ($tokensFromFile as $contentToken) {
        if (!isReservedWord($contentToken)) {
            continue;
        }

        $fileTokens['totalReplaced']++;

        $newToken = $contentToken."Name";

        if (in_array($newToken, $fileTokens['hasReplaced'])) {
            continue;
        }

        writeLog($contentToken . ' será renomeada para => ' . $newToken . PHP_EOL);

        $fileContents = preg_replace(
            '/\$\b'.str_replace('$', '', $contentToken).'\b/',
            $contentToken.'Name',
            $fileContents
        );

        $fileTokens['hasReplaced'][] = $newToken;
    }

    file_put_contents($file, $fileContents);

    writeLog(
        "Arquivo {$file}" . PHP_EOL .
        "Total de variáveis renomeadas: " . $fileTokens['totalReplaced'] . PHP_EOL .
        "--------------------------------" . PHP_EOL
    );
}

/**
 * Check file is pi eidi pi extension
 *
 * @param string $file
 * @return boolean
 */
function isPiEidipiFile(string $file): bool
{
    $file = strtolower($file);
    $allowedExtension = pathinfo($file, PATHINFO_EXTENSION);

    return is_file($file) && in_array($allowedExtension, ['php', 'inc']);
}

/**
 * Check if token is a reserved variable of according
 * with the constant defined on top of this file because yes
 *
 * @param string $token
 * @return boolean
 */
function isReservedWord(string $token): bool
{
    foreach (KEYWORDS as $key) {
        if (trim($token) == $key) {
            return true;
        }
    }

    return false;
}

/**
 * Write a log anywhere and fuck all
 * solid sent you huggies
 *
 * @param string $data
 * @return void
 */
function writeLog(string $data): void
{
    $filename = '/log_' . date("Y-m-d") . '.log';
    file_put_contents(__DIR__ . $filename, $data, FILE_APPEND);
    echo $data;
}

/**
 * Get all possible variable from a fucked file
 *
 * @param string $content
 * @return array
 */
function tokenizerWords(string $content): array
{
    preg_match_all('/\$[A-Za-z0-9-_]+/', $content, $tokens);
    return $tokens[0];
}