<?php

declare(strict_types=1);

const FIXED_ZIP_TIMESTAMP = 315532800; // 1980-01-01T00:00:00Z, the ZIP epoch.

function fail(string $message): never
{
    fwrite(STDERR, "Release build failed: {$message}\n");
    exit(1);
}

function run(array $command, string $cwd): string
{
    $pipes = [];
    $process = proc_open($command, [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, $cwd);

    if (!is_resource($process)) {
        fail('Unable to start: ' . implode(' ', $command));
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);

    if ($status !== 0) {
        fail(trim($stderr) ?: 'Command failed: ' . implode(' ', $command));
    }

    return $stdout;
}

function json_file(string $path): array
{
    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        fail("Invalid JSON: {$path}");
    }

    return $data;
}

function write_json(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false || file_put_contents($path, $json . PHP_EOL) === false) {
        fail("Unable to write {$path}");
    }
}

function git_blob(string $root, string $path): string
{
    return run(['git', 'show', 'HEAD:' . $path], $root);
}

function header_version(string $contents, string $label): string
{
    if (!preg_match('/^[ \t*#@\/]*' . preg_quote($label, '/') . '\s*:\s*(\S+)/mi', $contents, $matches)) {
        fail("Missing {$label} header");
    }

    return trim($matches[1]);
}

function resolve_output(string $root, ?string $requested): string
{
    if ($requested === null || $requested === '') {
        return $root . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'release';
    }

    if (preg_match('~^(?:/|[A-Za-z]:[\\\\/]|[\\\\/]{2})~', $requested) === 1) {
        return rtrim($requested, "\\/");
    }

    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $requested);
}

if (PHP_VERSION_ID < 80100) {
    fail('PHP 8.1 or newer is required');
}
if (!class_exists(ZipArchive::class)) {
    fail('The PHP Zip extension is required');
}

$options = getopt('', ['output:', 'allow-untagged', 'signing-key-file:']);
$root = dirname(__DIR__, 2);
$config = json_file($root . DIRECTORY_SEPARATOR . 'release-config.json');
$version = trim((string) file_get_contents($root . DIRECTORY_SEPARATOR . 'VERSION'));

if (preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $version) !== 1) {
    fail("VERSION is not valid SemVer: {$version}");
}

$dirty = trim(run(['git', 'status', '--porcelain', '--untracked-files=all'], $root));
if ($dirty !== '') {
    fail("Repository must be clean before packaging:\n{$dirty}");
}

$commit = trim(run(['git', 'rev-parse', 'HEAD'], $root));
$commitTime = trim(run(['git', 'show', '-s', '--format=%cI', 'HEAD'], $root));
$expectedTag = str_replace('{version}', $version, (string) $config['tag_pattern']);
$exactTags = preg_split('/\R+/', trim(run(['git', 'tag', '--points-at', 'HEAD'], $root))) ?: [];
$tagVerified = in_array($expectedTag, $exactTags, true);
$allowUntagged = array_key_exists('allow-untagged', $options);
if (!$tagVerified && !$allowUntagged) {
    fail("HEAD must carry the exact tag {$expectedTag}; use --allow-untagged only for local verification");
}

$tracked = preg_split('/\R+/', trim(run(['git', 'ls-tree', '-r', '--name-only', 'HEAD'], $root))) ?: [];
$includeFiles = array_fill_keys($config['include_files'] ?? [], true);
$includeRoots = array_map(static fn (string $path): string => rtrim($path, '/') . '/', $config['include_roots'] ?? []);
$files = [];
foreach ($tracked as $path) {
    if ($path === '') {
        continue;
    }
    $included = isset($includeFiles[$path]);
    foreach ($includeRoots as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $included = true;
            break;
        }
    }
    if ($included) {
        $files[] = $path;
    }
}
sort($files, SORT_STRING);

foreach ($config['required_files'] ?? [] as $required) {
    if (!in_array($required, $files, true)) {
        fail("Required runtime file is missing from HEAD or the package allowlist: {$required}");
    }
}

$versionSources = ['VERSION' => $version];
if (($config['type'] ?? '') === 'theme') {
    $versionSources['style.css'] = header_version(git_blob($root, 'style.css'), 'Version');
    $package = json_decode(git_blob($root, 'package.json'), true);
    $lock = json_decode(git_blob($root, 'package-lock.json'), true);
    $versionSources['package.json'] = (string) ($package['version'] ?? '');
    $versionSources['package-lock.json'] = (string) ($lock['version'] ?? '');
    $versionSources['package-lock root'] = (string) ($lock['packages']['']['version'] ?? '');
} elseif (($config['type'] ?? '') === 'child-theme') {
    $style = git_blob($root, 'style.css');
    $versionSources['style.css'] = header_version($style, 'Version');
    if (header_version($style, 'Update URI') !== 'https://updates.dokanelbanat.com/' . $config['slug']) {
        fail('style.css Update URI must point at the update gateway for this slug');
    }
    if (header_version($style, 'Egstore Update Key') !== (string) ($config['signing']['public_key'] ?? '')) {
        fail('style.css Egstore Update Key must match the signing public key');
    }
} elseif (($config['type'] ?? '') === 'plugin') {
    $bootstrap = git_blob($root, (string) $config['include_files'][0]);
    $versionSources['plugin header'] = header_version($bootstrap, 'Version');
    if (!preg_match("/define\\(\\s*['\"]EGSTORE_COMMERCE_VERSION['\"]\\s*,\\s*['\"]([^'\"]+)['\"]\\s*\\)/", $bootstrap, $matches)) {
        fail('Missing EGSTORE_COMMERCE_VERSION constant');
    }
    $versionSources['runtime constant'] = $matches[1];
} else {
    fail('release-config.json contains an unsupported package type');
}

foreach ($versionSources as $source => $sourceVersion) {
    if ($sourceVersion !== $version) {
        fail("Version mismatch in {$source}: expected {$version}, found {$sourceVersion}");
    }
}

$output = resolve_output($root, isset($options['output']) ? (string) $options['output'] : null);
if (!is_dir($output) && !mkdir($output, 0775, true) && !is_dir($output)) {
    fail("Unable to create output directory: {$output}");
}

$slug = (string) $config['slug'];
$base = $slug . '-' . $version;
$zipPath = $output . DIRECTORY_SEPARATOR . $base . '.zip';
$manifestPath = $output . DIRECTORY_SEPARATOR . $base . '.manifest.json';
$changelogPath = $output . DIRECTORY_SEPARATOR . $base . '-CHANGELOG.md';
$auditPath = $output . DIRECTORY_SEPARATOR . $base . '.audit.json';
$checksumsPath = $output . DIRECTORY_SEPARATOR . $base . '.SHA256SUMS';
foreach ([$zipPath, $manifestPath, $changelogPath, $auditPath, $checksumsPath] as $ownedPath) {
    if (is_file($ownedPath) && !unlink($ownedPath)) {
        fail("Unable to replace owned output: {$ownedPath}");
    }
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
    fail("Unable to create ZIP: {$zipPath}");
}
foreach ($files as $path) {
    $entry = $slug . '/' . str_replace('\\', '/', $path);
    if (!$zip->addFromString($entry, git_blob($root, $path))) {
        $zip->close();
        fail("Unable to add {$path} to the ZIP");
    }
    $zip->setMtimeName($entry, FIXED_ZIP_TIMESTAMP);
    $zip->setCompressionName($entry, ZipArchive::CM_DEFLATE, 9);
    $zip->setExternalAttributesName($entry, ZipArchive::OPSYS_UNIX, 0100644 << 16);
}
if (!$zip->close()) {
    fail('Unable to finalize ZIP');
}

$zipHash = hash_file('sha256', $zipPath);
$zipSize = filesize($zipPath);
if ($zipHash === false || $zipSize === false) {
    fail('Unable to inspect the generated ZIP');
}

$authentication = [
    'method' => 'none',
    'status' => 'unsigned-development',
];
$signingKeyFile = isset($options['signing-key-file']) ? (string) $options['signing-key-file'] : '';
$manifestCore = [
    'schema_version' => 1,
    'package' => [
        'slug' => $slug,
        'type' => (string) $config['type'],
        'version' => $version,
    ],
    'source' => [
        'commit' => $commit,
        'commit_time' => $commitTime,
        'tag' => $tagVerified ? $expectedTag : null,
    ],
    'artifact' => [
        'filename' => basename($zipPath),
        'sha256' => $zipHash,
        'size_bytes' => $zipSize,
    ],
    'compatibility' => $config['compatibility'] ?? new stdClass(),
    'rollback' => $config['rollback'] ?? null,
];
if ($signingKeyFile !== '') {
    if (!extension_loaded('sodium')) {
        fail('The Sodium extension is required for Ed25519 signing');
    }
    if (!is_file($signingKeyFile) || !is_readable($signingKeyFile)) {
        fail('The Ed25519 signing key file is not readable');
    }
    $encodedSecretKey = trim((string) file_get_contents($signingKeyFile));
    $secretKey = base64_decode($encodedSecretKey, true);
    if ($secretKey === false || strlen($secretKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
        fail('The Ed25519 signing key must be a base64-encoded 64-byte secret key');
    }
    $encodedPublicKey = (string) ($config['signing']['public_key'] ?? '');
    $publicKey = base64_decode($encodedPublicKey, true);
    if (($config['signing']['algorithm'] ?? '') !== 'ed25519'
        || $publicKey === false
        || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
        sodium_memzero($secretKey);
        fail('release-config.json must contain a valid Ed25519 public key');
    }
    if (!hash_equals($publicKey, sodium_crypto_sign_publickey_from_secretkey($secretKey))) {
        sodium_memzero($secretKey);
        fail('The Ed25519 signing key does not match the configured public key');
    }
    $payload = json_encode($manifestCore, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($payload === false) {
        sodium_memzero($secretKey);
        fail('Unable to serialize the manifest for signing');
    }
    $signatureBytes = sodium_crypto_sign_detached($payload, $secretKey);
    sodium_memzero($secretKey);
    if (!sodium_crypto_sign_verify_detached($signatureBytes, $payload, $publicKey)) {
        fail('Ed25519 signature self-verification failed');
    }
    $authentication = [
        'method' => 'ed25519',
        'status' => 'signed',
        'key_id' => substr(hash('sha256', $publicKey), 0, 16),
        'signature' => base64_encode($signatureBytes),
    ];
}
$manifest = $manifestCore;
$manifest['authentication'] = $authentication;
write_json($manifestPath, $manifest);

$changelog = git_blob($root, 'CHANGELOG.md');
if (file_put_contents($changelogPath, str_replace(["\r\n", "\r"], "\n", $changelog)) === false) {
    fail('Unable to write the release changelog');
}

$auditZip = new ZipArchive();
if ($auditZip->open($zipPath) !== true) {
    fail('Unable to reopen the ZIP for audit');
}
$zipEntries = [];
for ($index = 0; $index < $auditZip->numFiles; $index++) {
    $name = $auditZip->getNameIndex($index);
    if ($name !== false) {
        $zipEntries[] = $name;
    }
}
$auditZip->close();
$expectedEntries = array_map(static fn (string $path): string => $slug . '/' . $path, $files);
$forbiddenPatterns = ['~/(?:\.git|node_modules|tests|artifacts|demo-home|screenshots?|reports?|\.idea|\.vscode)/~'];
$forbiddenFound = [];
foreach ($zipEntries as $entry) {
    foreach ($forbiddenPatterns as $pattern) {
        if (preg_match($pattern, '/' . $entry) === 1) {
            $forbiddenFound[] = $entry;
            break;
        }
    }
}
$contentMatches = $zipEntries === $expectedEntries;
$rollback = is_array($config['rollback'] ?? null) ? $config['rollback'] : [];
$baselineRelease = ($rollback['strategy'] ?? '') === 'first-release-baseline'
    && ($rollback['baseline_version'] ?? '') === $version
    && ($rollback['previous_version'] ?? null) === null
    && ($rollback['artifact_sha256'] ?? null) === null;
$rollbackConfigured = ($rollback['strategy'] ?? '') === 'previous-approved-release'
    && !empty($rollback['previous_version'])
    && !empty($rollback['artifact_sha256']);
$rollbackPolicyValid = $baselineRelease || $rollbackConfigured;
$signed = $authentication['status'] === 'signed';
$releaseReady = $contentMatches && $forbiddenFound === [] && $tagVerified && $signed && $rollbackPolicyValid;
$audit = [
    'status' => ($contentMatches && $forbiddenFound === []) ? 'pass' : 'fail',
    'package' => $slug,
    'version' => $version,
    'version_sources' => $versionSources,
    'source_commit' => $commit,
    'expected_tag' => $expectedTag,
    'tag_verified' => $tagVerified,
    'signed' => $signed,
    'baseline_release' => $baselineRelease,
    'rollback_configured' => $rollbackConfigured,
    'rollback_policy_valid' => $rollbackPolicyValid,
    'release_ready' => $releaseReady,
    'zip_entry_count' => count($zipEntries),
    'content_matches_allowlist' => $contentMatches,
    'forbidden_entries' => array_values(array_unique($forbiddenFound)),
];
write_json($auditPath, $audit);
if ($audit['status'] !== 'pass') {
    fail('Package content audit failed');
}

$checksumLines = [];
foreach ([$zipPath, $manifestPath, $changelogPath, $auditPath] as $artifactPath) {
    $checksumLines[] = hash_file('sha256', $artifactPath) . '  ' . basename($artifactPath);
}
if (file_put_contents($checksumsPath, implode(PHP_EOL, $checksumLines) . PHP_EOL) === false) {
    fail('Unable to write checksums');
}

write_json('php://stdout', [
    'status' => 'pass',
    'package' => $slug,
    'version' => $version,
    'zip' => $zipPath,
    'zip_sha256' => $zipHash,
    'audit' => $auditPath,
    'release_ready' => $releaseReady,
]);
