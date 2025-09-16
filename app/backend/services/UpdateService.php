<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';

// This Service is responsible for delivering updates to this software. Altering this file would prevent you...
// ...from receiving future updates. Older versions of this software may not properly function after awhile. Hence updates are required!

class UpdateService
{
    //Fetch update methods
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
        $currentVersionLine = file_get_contents(self::$versionFile);
        if (preg_match('/version=([\d\.]+)/', $currentVersionLine, $matches)) {
            $currentVersion = $matches[1];
        } else {
            $currentVersion = '0.0.0';
        }

        $config = self::getConfig();
        $apiUrl = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/contents/" . $config['folder'];
        $files = self::fetchJson($apiUrl, 1800);

        $latestVersion = '0.0.0';
        $zipFileData = [];
        $uninstalledVersions = [];

        // Find all versions and identify uninstalled ones
        foreach ($files as $file) {
            if ($file['type'] === 'file' && preg_match('/updatev(\d+\.\d+\.\d+)\.zip$/', $file['name'], $match)) {
                $version = $match[1];

                // Track latest version info
                if (version_compare($version, $latestVersion, '>')) {
                    $latestVersion = $version;
                    $zipFileData = [
                        'version' => $version,
                        'zip' => $file['download_url'],
                        'size' => $file['size'],
                        'updated_at' => $file['git_url'] ?? '' // Temporary
                    ];
                }

                // Collect all uninstalled versions
                if (version_compare($version, $currentVersion, '>')) {
                    $uninstalledVersions[$version] = [
                        'version' => $version,
                        'size' => $file['size'],
                        'changelog' => self::getChangelog($files, $version)
                    ];
                }
            }
        }

        // Sort uninstalled versions (oldest to newest)
        uksort($uninstalledVersions, 'version_compare');

        // Aggregate size and changelogs from all uninstalled versions
        $totalSize = 0;
        $combinedChangelog = '';
        $changelogParts = [];

        foreach ($uninstalledVersions as $versionData) {
            $totalSize += $versionData['size'];
            
            if (!empty($versionData['changelog'])) {
                $changelogParts[] = "v{$versionData['version']}: " . $versionData['changelog'];
            }
        }

        $combinedChangelog = implode('<br>', $changelogParts);

        // Get latest version's commit time
        $zipFileData['updated_at'] = self::getCommitTime("updatev$latestVersion.zip");

        return [
            'version' => $zipFileData['version'],
            'zip' => $zipFileData['zip'],
            'size' => $totalSize, // All
            'updated_at' => $zipFileData['updated_at'],
            'changelog' => $combinedChangelog // All
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
        $config = self::getConfig();
        $url = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/commits?path=" . $config['folder'] . "/$filename&page=1&per_page=1";

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
        $config = self::getConfig();
        $apiUrl = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/contents/" . $config['folder'];
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
                    $e = $result['error'];
                    error_log("Update migrations error: {$e}");
                    Response::error("Migration failed.", 400);
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

            // Handle run scripts (execute and delete)
            if (!empty($updateMeta['run'])) {
                self::setStatus('updating', "Running update scripts...");
                
                foreach ($updateMeta['run'] as $runScript) {
                    $scriptPath = self::$extractTo . $runScript;
                    
                    if (file_exists($scriptPath)) {
                        self::setStatus('updating', "Executing script: " . basename($runScript));
                        
                        try {
                            // Execute the script
                            $output = null;
                            $returnCode = null;
                            
                            // Capture output and errors
                            ob_start();
                            $result = include $scriptPath;
                            $scriptOutput = ob_get_clean();
                            
                            // Log script execution
                            error_log("Update script executed: $runScript");
                            if ($scriptOutput) {
                                error_log("Script output: $scriptOutput");
                            }
                            
                            // Delete the script after execution
                            unlink($scriptPath);
                            error_log("Update script deleted: $runScript");
                            
                        } catch (Exception $e) {
                            // Delete script even if it failed
                            if (file_exists($scriptPath)) {
                                unlink($scriptPath);
                            }
                            
                            error_log("Update script error in $runScript: " . $e->getMessage());
                            self::setStatus('error', "Script execution failed: " . basename($runScript));
                            Response::error("Update script execution failed", 400);
                        }
                    } else {
                        error_log("Update script not found: $scriptPath");
                    }
                }
                
                self::setStatus('updating', "Update scripts completed.");
            }

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

    /**
     * Get all changelogs for display on changelog history page
     * Returns formatted changelog data from oldest to newest version
     * Uses caching to reduce GitHub API calls and avoid rate limits
     * 
     * @return array Formatted changelog data for frontend display
     */
    public static function getAllChangelogs(): array
    {
        try {
            $config = self::getConfig();
            
            // Use longer cache for changelog history (1 hour) since it changes less frequently
            $cacheDir = __DIR__ . '/../cache/';
            $cacheKey = 'all_changelogs_' . md5($config['owner'] . $config['repo'] . $config['folder']);
            $cacheFile = $cacheDir . $cacheKey . '.json';
            $cacheTtl = 3600;

            // Check if we have cached data
            if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
                $cached = file_get_contents($cacheFile);
                $cachedData = json_decode($cached, true);
                
                if ($cachedData && isset($cachedData['status']) && $cachedData['status'] === 'success') {
                    return $cachedData;
                }
            }

            // Fetch fresh data with extended cache for API calls
            $apiUrl = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/contents/" . $config['folder'];
            $files = self::fetchJson($apiUrl, 1800); // 30 minutes cache for folder contents

            $versions = [];
            $changelogData = [];

            // Find all version files and collect version info
            foreach ($files as $file) {
                if ($file['type'] === 'file' && preg_match('/updatev(\d+\.\d+\.\d+)\.zip$/', $file['name'], $match)) {
                    $version = $match[1];
                    
                    // Cache commit time requests with longer TTL
                    $commitTime = self::getCachedCommitTime("updatev$version.zip");
                    
                    $versions[$version] = [
                        'version' => $version,
                        'size' => $file['size'],
                        'updated_at' => $commitTime
                    ];
                }
            }

            // Sort versions from oldest to newest
            uksort($versions, 'version_compare');
            
            // Limit to last 20 versions to stay within rate limits (41 API calls total)
            $versions = array_slice($versions, -20, 20, true);

            // Get changelog for each version
            foreach ($versions as $version => $versionData) {
                $changelog = self::getCachedChangelog($files, $version);
                
                $changelogData[] = [
                    'version' => $version,
                    'changelog' => $changelog ?: 'No changelog available for this version.',
                    'size' => $versionData['size'],
                    'size_formatted' => self::formatFileSize($versionData['size']),
                    'release_date' => $versionData['updated_at'] ? date('M j, Y', strtotime($versionData['updated_at'])) : 'Unknown',
                    'release_date_full' => $versionData['updated_at'] ?: '',
                    'download_url' => "https://raw.githubusercontent.com/" . $config['owner'] . "/" . $config['repo'] . "/main/" . $config['folder'] . "/updatev$version.zip"
                ];
            }

            $result = [
                'status' => 'success',
                'total_versions' => count($changelogData),
                'changelogs' => $changelogData,
                'latest_version' => !empty($changelogData) ? end($changelogData)['version'] : '0.0.0',
                'oldest_version' => !empty($changelogData) ? reset($changelogData)['version'] : '0.0.0',
                'cached_at' => date('Y-m-d H:i:s'),
                'cache_expires' => date('Y-m-d H:i:s', time() + $cacheTtl)
            ];

            // Cache the complete result
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            file_put_contents($cacheFile, json_encode($result));

            return $result;

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to fetch changelog data',
                'error' => $e->getMessage(),
                'changelogs' => []
            ];
        }
    }

    /**
     * Get cached commit time with extended cache duration
     */
    private static function getCachedCommitTime(string $filename): string
    {
        $cacheDir = __DIR__ . '/../cache/';
        $cacheKey = 'commit_time_' . md5($filename);
        $cacheFile = $cacheDir . $cacheKey . '.json';
        $cacheTtl = 7200; // 2 hours cache for commit times (they don't change)

        // Check cache first
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = file_get_contents($cacheFile);
            $cachedData = json_decode($cached, true);
            if ($cachedData && isset($cachedData['commit_time'])) {
                return $cachedData['commit_time'];
            }
        }

        // Fetch fresh data
        $commitTime = self::getCommitTime($filename);

        // Cache the result
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($cacheFile, json_encode(['commit_time' => $commitTime, 'cached_at' => time()]));

        return $commitTime;
    }

    /**
     * Get cached changelog content with extended cache duration
     */
    private static function getCachedChangelog(array $files, string $version): string
    {
        $cacheDir = __DIR__ . '/../cache/';
        $cacheKey = 'changelog_' . md5($version);
        $cacheFile = $cacheDir . $cacheKey . '.json';
        $cacheTtl = 7200; // 2 hours cache for changelogs (they don't change)

        // Check cache first
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = file_get_contents($cacheFile);
            $cachedData = json_decode($cached, true);
            if ($cachedData && isset($cachedData['changelog'])) {
                return $cachedData['changelog'];
            }
        }

        // Fetch fresh data
        $changelog = self::getChangelog($files, $version);

        // Cache the result
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($cacheFile, json_encode(['changelog' => $changelog, 'cached_at' => time()]));

        return $changelog;
    }

    /**
     * Format file size in human readable format
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted file size (e.g., "2.5 MB")
     */
    private static function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $size = round($bytes / pow(1024, $power), 2);
        
        return $size . ' ' . $units[$power];
    }

    private static function getConfig(): array
    {
        $config = [
            'o' => 'am9zaGlrZS1jb2Rl',
            'r' => 'aW52ZXN0b2NjLXNvZnR3YXJl',
            'f' => 'dXBkYXRlcw=='
        ];
        
        $expected = md5($config['o'] . $config['r'] . $config['f'] . 'investocc_salt_9x7k2m');
        if ($expected !== 'd7337c3a4cf6e801be70620c49b31371') {
            error_log("Configuration integrity check failed");
            Response::error("System integrity verification failed", 500);
        }
        
        return [
            'owner' => base64_decode($config['o']),
            'repo' => base64_decode($config['r']), 
            'folder' => base64_decode($config['f'])
        ];
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