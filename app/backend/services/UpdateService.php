<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';

// This Service is responsible for delivering updates to this software. Tampering with this file would prevent you...
// ...from receiving future updates. Older versions of this software will not function after awhile. Hence updates are required!

class UpdateService
{
    //Fetch update methods
    private static string $repoOwner = 'joshike-code';  //Do  not change this value
    private static string $repoName = 'investocc-software';  //Do  not change this value
    private static string $folderPath = 'updates';  //Do  not change this value

    public static function fetchJson(string $url, int $cacheTtl = 600): array
    {
        $cacheDir = __DIR__ . '/../cache/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cacheKey = md5($url);
        $cacheFile = $cacheDir . $cacheKey . '.json';

        // Use cached file if it's still valid
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = file_get_contents($cacheFile);
            return json_decode($cached, true);
        }

        // Fetch fresh data
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Investocc-Updater",
            CURLOPT_FAILONERROR => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen(__DIR__.'/../update_curl.log', 'w'),
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log("CURL Error: " . curl_error($ch));
            Response::error("Failed to make update request", 500);
            // throw new \Exception("CURL Error: " . curl_error($ch));
        }

        curl_close($ch);

        // Save to cache
        file_put_contents($cacheFile, $response);

        return json_decode($response, true);
    }

    public static function getLatestUpdate(): array {
        $apiUrl = "https://api.github.com/repos/" . self::$repoOwner . "/" . self::$repoName . "/contents/" . self::$folderPath;
        $files = self::fetchJson($apiUrl);

        $latestVersion = '0.0.0';
        $zipFileData = [];

        foreach ($files as $file) {
            if ($file['type'] === 'file' && preg_match('/updatev(\d+\.\d+\.\d+)\.zip$/', $file['name'], $match)) {
                $version = $match[1];

                if (version_compare($version, $latestVersion, '>')) {
                    $latestVersion = $version;
                    $zipFileData = [
                        'version' => $version,
                        'zip' => $file['download_url'],
                        'size' => $file['size'],
                        'updated_at' => $file['git_url'] ?? '' // Temporary
                    ];
                }
            }
        }

        $changelog = self::getChangelog($files, $latestVersion);
        $zipFileData['updated_at'] = self::getCommitTime("updatev$latestVersion.zip");

        return [
            'version' => $zipFileData['version'],
            'zip' => $zipFileData['zip'],
            'size' => $zipFileData['size'],
            'updated_at' => $zipFileData['updated_at'],
            'changelog' => $changelog
        ];
    }

    private static function getChangelog(array $files, string $version): string {
        $changelogFile = "changelog-v$version.txt";

        foreach ($files as $file) {
            if ($file['name'] === $changelogFile) {
                $content = self::fetchRaw($file['download_url']);
                return $content !== false ? trim($content) : '';
            }
        }

        return '';
    }

    private static function getCommitTime(string $filename): string {
        $url = "https://api.github.com/repos/" . self::$repoOwner . "/" . self::$repoName . "/commits?path=" . self::$folderPath . "/$filename&page=1&per_page=1";

        try {
            $commits = self::fetchJson($url);
            return $commits[0]['commit']['committer']['date'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private static function fetchRaw($url)
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Investocc-Updater'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log("Curl error in fetchRaw: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("GitHub responded with status code: $httpCode for $url");
            return false;
        }

        return $response;
    }







    //Install update methods
    private static string $tempZip = __DIR__ . '/../temp_update.zip';
    private static string $extractTo = __DIR__ . '/../../../'; // root directory
    private static string $versionFile = __DIR__ . '/../version.txt';
    private static string $statusFile = __DIR__ . '/../update_status.json';

    public static function getAvailableUpdates(string $currentVersion): array {
        $apiUrl = "https://api.github.com/repos/" . self::$repoOwner . "/" . self::$repoName . "/contents/" . self::$folderPath;
        $files = self::fetchJson($apiUrl);

        $updates = [];

        foreach ($files as $file) {
            if ($file['type'] === 'file' && preg_match('/updatev(\d+\.\d+\.\d+)\.zip$/', $file['name'], $match)) {
                $version = $match[1];
                if (version_compare($version, $currentVersion, '>')) {
                    $updates[$version] = [
                        'version' => $version,
                        'zip' => $file['download_url'],
                        'size' => $file['size'],
                        'updated_at' => self::getCommitTime("updatev$version.zip"),
                        'changelog' => self::getChangelog($files, $version)
                    ];
                }
            }
        }

        uksort($updates, 'version_compare'); // Sort ascending
        return array_values($updates); // Return as indexed array
    }

    public static function applyUpdate(array $input): array {
        $currentVersionLine = file_get_contents(self::$versionFile);
        if (preg_match('/version=([\d\.]+)/', $currentVersionLine, $matches)) {
            $currentVersion = $matches[1];
        } else {
            $currentVersion = '0.0.0';
        }

        $updates = self::getAvailableUpdates($currentVersion);

        if (empty($updates)) {
            self::setStatus('idle', "Software is up-to-date.");
            Response::success("You are already on the latest version.");
        }

        foreach ($updates as $update) {
            $zipUrl = $update['zip'];
            $version = $update['version'];
            $date = $update['updated_at'] ?? date('c');

            self::setStatus('updating', "Downloading version $version...");

            // Download ZIP
            $ch = curl_init($zipUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $zipContent = curl_exec($ch);
            curl_close($ch);

            if (!$zipContent) {
                self::setStatus('error', "Failed to download update v$version.");
                Response::error("Failed to download update v$version.", 400);
            }

            if (file_put_contents(self::$tempZip, $zipContent) === false) {
                self::setStatus('error', "Failed to save update v$version.");
                Response::error("Failed to write ZIP file v$version.", 400);
            }

            self::setStatus('downloading', "Downloaded v$version. Extracting update...");

            if(!class_exists('ZipArchive')) {
                Response::error('No Zip extension.', 400);
            }

            $zip = new ZipArchive();
            if ($zip->open(self::$tempZip) !== true) {
                unlink(self::$tempZip);
                Response::error("Failed to open ZIP for v$version", 400);
            }

            $zip->extractTo(self::$extractTo);
            $zip->close();

            // Load update.json from extracted directory
            $updateJsonPath = self::$extractTo . 'update.json';
            if (file_exists($updateJsonPath)) {
                $updateMeta = json_decode(file_get_contents($updateJsonPath), true);

                // Handle renames
                if (!empty($updateMeta['rename'])) {
                    foreach ($updateMeta['rename'] as $from => $to) {
                        $fromPath = self::$extractTo . $from;
                        $toPath = self::$extractTo . $to;

                        // Create directory if needed
                        $toDir = dirname($toPath);
                        if (!is_dir($toDir)) {
                            mkdir($toDir, 0777, true);
                        }

                        if (file_exists($fromPath)) {
                            rename($fromPath, $toPath);
                        }
                    }
                }

                // Handle deletions
                if (!empty($updateMeta['delete'])) {
                    foreach ($updateMeta['delete'] as $target) {
                        $fullPath = self::$extractTo . $target;
                        if (is_file($fullPath)) {
                            unlink($fullPath);
                        } elseif (is_dir($fullPath)) {
                            self::deleteDirectory($fullPath);
                        }
                    }
                }

                unlink($updateJsonPath);
            }

            // Update index.html
            $indexPath = realpath(__DIR__ . '/../../index.html');
            if (file_exists($indexPath)) {
                $indexContent = file_get_contents($indexPath);
                $indexContent = preg_replace('/distv[\d.]+/', "distv$version", $indexContent);

                file_put_contents($indexPath, $indexContent);
            }

            self::setStatus('extracting', "Extracted v$version. Running migrations...");

            // Run migrations
            $result = include __DIR__ . '/../run-migrations.php';
            if (is_array($result)) {
                if($result['status'] === 'error') {
                    Response::error("Migration failed.", 400);
                    $e = $result['error'];
                    error_log("Update migrations error: {$e}");
                }
            } else {
                Response::error("Unknown migration error.", 400);
            }

            //Update .env from .env-example if new keys are added
            require_once __DIR__ . '/EnvSyncService.php';
            $envSyncResult = EnvSyncService::syncEnvironmentFiles();
            
            if ($envSyncResult['status'] === 'error') {
                self::setStatus('error', "Environment synchronization failed: " . $envSyncResult['message']);
                Response::error("Environment synchronization failed: " . $envSyncResult['message'], 400);
            }
            
            if (!empty($envSyncResult['added_keys'])) {
                self::setStatus('updating', 'Updated environment variables.');
            }

            // Update version.txt
            // $versionText = "version=$version\nupdated_at=$date\n";
            // file_put_contents(self::$versionFile, $versionText);
            self::setStatus('completed', "v$version applied.");
        }

        Response::success("All updates installed successfully.");
    }

    public static function getUpdateStatus(): array
    {
        if (!file_exists(self::$statusFile)) {
            return ['status' => 'idle', 'message' => 'No update in progress.'];
        }
        return json_decode(file_get_contents(self::$statusFile), true);
    }

    private static function setStatus(string $status, string $message)
    {
        $data = [
            'status' => $status,
            'message' => $message,
            'timestamp' => date('c')
        ];
        file_put_contents(self::$statusFile, json_encode($data));
    }

    private static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) return false;
        if (!is_dir($dir)) return unlink($dir);

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }
}



?>