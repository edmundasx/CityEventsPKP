<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Web path prefix for links and assets (e.g. /CityEventsPKP/public).
 * dirname(SCRIPT_NAME) fails when SCRIPT_NAME is /index.php — derive from filesystem vs DOCUMENT_ROOT.
 */
final class AppBasePath
{
    public static function fromServer(): string
    {
        $scriptName = str_replace("\\", "/", (string) ($_SERVER["SCRIPT_NAME"] ?? ""));
        $dir = dirname($scriptName);
        if ($dir !== "/" && $dir !== "." && $dir !== "") {
            return rtrim($dir, "/");
        }

        $docRoot = self::realOrRaw((string) ($_SERVER["DOCUMENT_ROOT"] ?? ""));
        $scriptFile = self::realOrRaw((string) ($_SERVER["SCRIPT_FILENAME"] ?? ""));
        if ($docRoot !== "" && $scriptFile !== "" && str_starts_with($scriptFile, $docRoot)) {
            $rel = substr($scriptFile, strlen($docRoot));
            $rel = "/" . ltrim(str_replace("\\", "/", $rel), "/");
            $parent = dirname($rel);
            if ($parent !== "/" && $parent !== "." && $parent !== "\\") {
                return rtrim(str_replace("\\", "/", $parent), "/");
            }
        }

        return "";
    }

    private static function realOrRaw(string $path): string
    {
        $path = str_replace("\\", "/", $path);
        $path = rtrim($path, "/");
        $real = realpath($path);
        if ($real !== false) {
            return str_replace("\\", "/", $real);
        }
        return $path;
    }
}
