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
        // On Windows, dirname('/index.php') is '\\', not '/' — normalize before comparing.
        $dir = str_replace("\\", "/", dirname($scriptName));
        if ($dir !== "/" && $dir !== "." && $dir !== "") {
            return rtrim($dir, "/");
        }

        $docRoot = self::realOrRaw((string) ($_SERVER["DOCUMENT_ROOT"] ?? ""));
        $scriptFile = self::realOrRaw((string) ($_SERVER["SCRIPT_FILENAME"] ?? ""));
        $relOpt = self::relativePathUnderDocRoot($docRoot, $scriptFile);
        if ($relOpt !== null) {
            $rel = "/" . ltrim(str_replace("\\", "/", $relOpt), "/");
            $parent = dirname($rel);
            $parent = str_replace("\\", "/", $parent);
            if ($parent !== "/" && $parent !== "." && $parent !== "\\") {
                return rtrim($parent, "/");
            }
        }

        $phpSelf = str_replace("\\", "/", (string) ($_SERVER["PHP_SELF"] ?? ""));
        if ($phpSelf !== "") {
            $dirPs = str_replace("\\", "/", dirname($phpSelf));
            if ($dirPs !== "/" && $dirPs !== "." && $dirPs !== "") {
                return rtrim($dirPs, "/");
            }
        }

        return "";
    }

    /**
     * Strip docroot prefix case-insensitively (Windows drive letter / path casing).
     *
     * @return non-empty-string|null Relative path including leading slash segments, no leading slash in return
     */
    private static function relativePathUnderDocRoot(string $docRoot, string $scriptFile): ?string
    {
        if ($docRoot === "" || $scriptFile === "") {
            return null;
        }

        $docRoot = str_replace("\\", "/", $docRoot);
        $scriptFile = str_replace("\\", "/", $scriptFile);

        $docLen = strlen($docRoot);
        $scrLen = strlen($scriptFile);
        if ($scrLen < $docLen || $docLen === 0) {
            return null;
        }

        if (strcasecmp(substr($scriptFile, 0, $docLen), $docRoot) !== 0) {
            return null;
        }

        $tail = substr($scriptFile, $docLen);

        return ltrim($tail, "/");
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
