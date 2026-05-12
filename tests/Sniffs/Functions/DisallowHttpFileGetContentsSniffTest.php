<?php

declare(strict_types=1);

namespace DevStrict\Tests\Common\Sniffs\Functions;

use DevStrict\Tests\BaseTest;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for DisallowHttpFileGetContentsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowHttpFileGetContentsSniffTest extends BaseTest
{
    /**
     * Flags direct HTTP URLs.
     */
    #[Test]
    public function httpLiteralTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents("http://example.com/api");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertContainsError($result, 'file_get_contents() for HTTP requests is forbidden');
    }

    /**
     * Flags direct HTTPS URLs.
     */
    #[Test]
    public function httpsLiteralTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents("https://example.com/api");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertContainsError($result, 'file_get_contents() for HTTP requests is forbidden');
    }

    /**
     * Flags fully-qualified built-in calls too.
     */
    #[Test]
    public function fullyQualifiedFunctionTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$response = \file_get_contents("https://example.com/api");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertContainsError($result, 'file_get_contents() for HTTP requests is forbidden');
    }

    /**
     * Flags inline HTTP stream contexts.
     */
    #[Test]
    public function httpStreamContextTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$url = $baseUrl . "/api";
$response = file_get_contents(
    $url,
    false,
    stream_context_create([
        "http" => [
            "method" => "POST",
        ],
    ]),
);', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertContainsError($result, 'file_get_contents() for HTTP requests is forbidden');
    }

    /**
     * Allows local file reads.
     */
    #[Test]
    public function localFileDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$contents = file_get_contents("/tmp/local-file.txt");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertNoViolations($result);
    }

    /**
     * Allows non-HTTP stream wrappers.
     */
    #[Test]
    public function phpInputDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$contents = file_get_contents("php://input");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertNoViolations($result);
    }

    /**
     * Avoids guessing when URL intent is not statically visible.
     */
    #[Test]
    public function variableWithoutHttpEvidenceDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$contents = file_get_contents($path);', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertNoViolations($result);
    }

    /**
     * Ignores methods with the same name.
     */
    #[Test]
    public function methodNamedFileGetContentsDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
class Reader {
    public function file_get_contents(string $path): string {
        return $path;
    }
}

$reader = new Reader();
$contents = $reader->file_get_contents("https://example.com/api");', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertNoViolations($result);
    }

    /**
     * Ignores function declarations.
     */
    #[Test]
    public function functionDeclarationDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
function file_get_contents(string $path): string {
    return $path;
}', 'DevStrict.Functions.DisallowHttpFileGetContents');

        $this->assertNoViolations($result);
    }
}
