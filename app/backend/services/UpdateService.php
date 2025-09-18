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
     * Makes 1 API call for files list + commit lookups for latest 20 versions
     * Cached for 1 hour to minimize API calls
     * 
     * @return array Formatted changelog data for frontend display
     */
    public static function getAllChangelogs(): array
    {
        try {
            $config = self::getConfig();

            // API Call #1: Get folder contents (fetchJson handles caching)
            $apiUrl = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/contents/" . $config['folder'];
            $files = self::fetchJson($apiUrl, 3600); // 1 hour cache

            $versions = [];
            $changelogData = [];
            $latestVersion = '0.0.0';

            // Build a lookup map of filenames to their GitHub file data for easy timestamp access
            $fileDataLookup = [];
            foreach ($files as $file) {
                $fileDataLookup[$file['name']] = $file;
            }

            // Process all files in one pass - collect versions, changelogs and extract timestamps
            foreach ($files as $file) {
                // Find version ZIP files
                if ($file['type'] === 'file' && preg_match('/updatev(\d+\.\d+\.\d+)\.zip$/', $file['name'], $match)) {
                    $version = $match[1];
                    
                    // Track latest version
                    if (version_compare($version, $latestVersion, '>')) {
                        $latestVersion = $version;
                    }
                    
                    // Initialize or update version data
                    if (!isset($versions[$version])) {
                        $versions[$version] = [
                            'version' => $version,
                            'size' => 0,
                            'changelog' => ''
                        ];
                    }
                    $versions[$version]['size'] = $file['size'];
                }
                
                // Find changelog files - create entries even if no ZIP exists
                if ($file['type'] === 'file' && preg_match('/changelog-v(\d+\.\d+\.\d+)\.txt$/', $file['name'], $match)) {
                    $version = $match[1];
                    
                    // Track latest version for changelog files too
                    if (version_compare($version, $latestVersion, '>')) {
                        $latestVersion = $version;
                    }
                    
                    // Initialize version entry if it doesn't exist
                    if (!isset($versions[$version])) {
                        $versions[$version] = [
                            'version' => $version,
                            'size' => 0,
                            'changelog' => ''
                        ];
                    }
                    
                    // Get changelog content directly from download URL (not an API call, just HTTP)
                    $changelog = self::fetchRaw($file['download_url']);
                    $versions[$version]['changelog'] = $changelog !== false ? trim($changelog) : '';
                }
            }

            // Sort versions from oldest to newest
            uksort($versions, 'version_compare');

            // Get commit timestamps for the latest 20 versions (most recent ones users care about)
            $sortedVersions = array_keys($versions);
            $latestVersions = array_slice(array_reverse($sortedVersions), 0, 20); // Get last 20 versions
            $commitTimestamps = self::getCommitTimestampsForVersions($latestVersions, $config);

            // Build changelog data for all versions
            foreach ($versions as $version => $versionData) {
                $timestamp = $commitTimestamps[$version] ?? '';
                
                $changelogData[] = [
                    'version' => $version,
                    'changelog' => $versionData['changelog'] ?: 'No changelog available for this version.',
                    'size' => $versionData['size'],
                    'size_formatted' => self::formatFileSize($versionData['size']),
                    'release_date' => $timestamp ? date('M j, Y', strtotime($timestamp)) : 'Unknown',
                    'release_date_full' => $timestamp,
                    'download_url' => "https://raw.githubusercontent.com/" . $config['owner'] . "/" . $config['repo'] . "/main/" . $config['folder'] . "/updatev$version.zip"
                ];
            }

            return [
                'status' => 'success',
                'total_versions' => count($changelogData),
                'changelogs' => $changelogData,
                'latest_version' => $latestVersion,
                'oldest_version' => !empty($changelogData) ? reset($changelogData)['version'] : '0.0.0',
                'timestamps_found' => count($commitTimestamps),
                'method' => 'recent_commits_lookup' // Gets timestamps for latest 20 versions from commit history
            ];

        } catch (Exception $e) {
            error_log("getAllChangelogs failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve changelogs: ' . $e->getMessage(),
                'changelogs' => []
            ];
        }
    }

    /**
     * Get commit timestamps for specific versions by looking up commits that affected those files
     * Uses 1 hour caching to minimize API calls
     * 
     * @param array $versions List of version numbers to get timestamps for
     * @param array $config Repository configuration
     * @return array Map of version => timestamp
     */
    private static function getCommitTimestampsForVersions(array $versions, array $config): array
    {
        $timestamps = [];
        
        if (empty($versions)) {
            return $timestamps;
        }

        try {
            // API Call: Get recent commits for the updates folder (cached for 1 hour)
            $commitsUrl = "https://api.github.com/repos/" . $config['owner'] . "/" . $config['repo'] . "/commits?path=" . $config['folder'] . "&per_page=100";
            $commits = self::fetchJson($commitsUrl, 3600); // 1 hour cache

            // For each commit, get details to see which files were affected
            foreach ($commits as $commit) {
                // API Call: Get individual commit details to see affected files (cached for 1 hour)
                $commitDetails = self::fetchJson($commit['url'], 3600); // 1 hour cache
                
                if (!isset($commitDetails['files'])) {
                    continue;
                }

                // Check if this commit affected any of our target versions
                foreach ($commitDetails['files'] as $file) {
                    // Match files like "updates/updatev1.9.9.zip" or "updates/changelog-v1.9.9.txt"
                    if (preg_match('/(?:updatev|changelog-v)(\d+\.\d+\.\d+)\.(?:zip|txt)$/', $file['filename'], $match)) {
                        $version = $match[1];
                        
                        // If this version is in our target list and we don't have a timestamp yet
                        if (in_array($version, $versions) && !isset($timestamps[$version])) {
                            $timestamps[$version] = $commit['commit']['author']['date'];
                        }
                    }
                }
                
                // Early exit if we found all versions we're looking for
                if (count($timestamps) >= count($versions)) {
                    break;
                }
            }

        } catch (Exception $e) {
            error_log("getCommitTimestampsForVersions failed: " . $e->getMessage());
        }

        return $timestamps;
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