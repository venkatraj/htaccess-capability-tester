<?php

namespace HtaccessCapabilityTester;

use \HtaccessCapabilityTester\Testers\AbstractTester;
use \HtaccessCapabilityTester\Testers\AddTypeTester;
use \HtaccessCapabilityTester\Testers\ContentDigestTester;
use \HtaccessCapabilityTester\Testers\CrashTester;
use \HtaccessCapabilityTester\Testers\DirectoryIndexTester;
use \HtaccessCapabilityTester\Testers\HtaccessEnabledTester;
use \HtaccessCapabilityTester\Testers\CustomTester;
use \HtaccessCapabilityTester\Testers\ModLoadedTester;
use \HtaccessCapabilityTester\Testers\PassEnvThroughRequestHeaderTester;
use \HtaccessCapabilityTester\Testers\PassEnvThroughRewriteTester;
use \HtaccessCapabilityTester\Testers\RewriteTester;
use \HtaccessCapabilityTester\Testers\ServerSignatureTester;
use \HtaccessCapabilityTester\Testers\SetRequestHeaderTester;
use \HtaccessCapabilityTester\Testers\SetResponseHeaderTester;

/**
 * Main entrance.
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class HtaccessCapabilityTester
{

    /** @var string  The dir where the test files should be put */
    protected $baseDir;

    /** @var string  The base url that the tests can be run from (corresponds to $baseDir) */
    protected $baseUrl;

    /** @var string  Additional info regarding last test (often empty) */
    public $infoFromLastTest;

    /** @var HttpRequesterInterface  The object used to make the HTTP request */
    private $requester;

    /**
     * Constructor.
     *
     * @param  string  $baseDir  Directory on the server where the test files can be put
     * @param  string  $baseUrl  The base URL of the test files
     *
     * @return void
     */
    public function __construct($baseDir, $baseUrl)
    {
        $this->baseDir = $baseDir;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Run a test, store the info and return the status.
     *
     * @param  AbstractTester  $tester
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    private function runTest($tester)
    {
        if (isset($this->requester)) {
            $tester->setHTTPRequester($this->requester);
        }
        if (TestResultCache::isCached($tester)) {
            $testResult = TestResultCache::getCached($tester);
        } else {
            $testResult = $tester->run();
            TestResultCache::cache($tester, $testResult);
        }

        $this->infoFromLastTest = $testResult->info;
        return $testResult->status;
    }

    /**
     * Run a test, store the info and return the status.
     *
     * @param  HttpRequesterInterface  $requester
     *
     * @return void
     */
    public function setHttpRequester($requester)
    {
        $this->requester = $requester;
    }

    /**
     * Test if .htaccess files are enabled
     *
     * Apache can be configured to completely ignore .htaccess files. This test examines
     * if .htaccess files are proccesed.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function htaccessEnabled()
    {
        return $this->runTest(new HtaccessEnabledTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if a module is loaded.
     *
     * This test detects if directives inside a "IfModule" is run for a given module
     *
     * @param string       $moduleName  A valid Apache module name (ie "rewrite")
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function moduleLoaded($moduleName)
    {
        return $this->runTest(new ModLoadedTester($this->baseDir, $this->baseUrl, $moduleName));
    }

    /**
     * Test if rewriting works.
     *
     * The .htaccess in this test uses the following directives:
     * - IfModule
     * - RewriteEngine
     * - Rewrite
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canRewrite()
    {
        return $this->runTest(new RewriteTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if AddType works.
     *
     * The .htaccess in this test uses the following directives:
     * - IfModule (core)
     * - AddType  (mod_mime, FileInfo)
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canAddType()
    {
        return $this->runTest(new AddTypeTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if setting a Response Header with the Header directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canSetResponseHeader()
    {
        return $this->runTest(new SetResponseHeaderTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if setting a Request Header with the RequestHeader directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canSetRequestHeader()
    {
        return $this->runTest(new SetRequestHeaderTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if ContentDigest directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canContentDigest()
    {
        return $this->runTest(new ContentDigestTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if ServerSignature directive works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canSetServerSignature()
    {
        return $this->runTest(new ServerSignatureTester($this->baseDir, $this->baseUrl));
    }


    /**
     * Test if DirectoryIndex works.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canSetDirectoryIndex()
    {
        return $this->runTest(new DirectoryIndexTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Test if an environment variable can be passed through RequestHeader and received in PHP.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canPassEnvThroughRequestHeader()
    {
        return $this->runTest(new PassEnvThroughRequestHeaderTester($this->baseDir, $this->baseUrl));
    }


    /**
     * Test if an environment variable can be set in a rewrite rule  and received in PHP.
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function canPassEnvThroughRewrite()
    {
        return $this->runTest(new PassEnvThroughRewriteTester($this->baseDir, $this->baseUrl));
    }

    /**
     * Call one of the methods of this class (not all allowed).
     *
     * @param string  $functionCall  ie "canRewrite()"
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function callMethod($functionCall)
    {
        switch ($functionCall) {
            case 'htaccessEnabled()':
                return $this->htaccessEnabled();
            case 'canRewrite()':
                return $this->canRewrite();
            case 'canAddType()':
                return $this->canAddType();
            case 'canSetResponseHeader()':
                return $this->canSetResponseHeader();
            case 'canSetRequestHeader()':
                return $this->canSetRequestHeader();
            case 'canContentDigest()':
                return $this->canContentDigest();
            case 'canSetDirectoryIndex()':
                return $this->canSetDirectoryIndex();
            case 'canPassEnvThroughRequestHeader()':
                return $this->canPassEnvThroughRequestHeader();
            case 'canPassEnvThroughRewrite()':
                return $this->canPassEnvThroughRewrite();
            default:
                throw new \Exception('The method is not callable');
        }

        // TODO:             moduleLoaded($moduleName)
    }

    /**
     * Crash-test some .htaccess.
     *
     * This test detects if directives inside a "IfModule" is run for a given module
     *
     * @param string       $rules   Rules to crash-test
     * @param string       $subDir  (optional) Subdir for the .htaccess to reside.
     *                              if left out, a unique string will be generated
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function crashTest($rules, $subDir = null)
    {
        return $this->runTest(new CrashTester($this->baseDir, $this->baseUrl, $rules, $subDir));
    }

    /**
     * Run a custom test.
     *
     * @param array       $definition
     *
     * @return bool|null   true=success, false=failure, null=inconclusive
     */
    public function customTest($definition)
    {
        return $this->runTest(new CustomTester($this->baseDir, $this->baseUrl, $definition));
    }
}
