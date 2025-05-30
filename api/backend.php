<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

// 引入 getID3 库
//require_once('getid3/getid3.php');
require_once __DIR__ . '/../vendor/autoload.php';

// 配置常量
const UPLOAD_DIR = '/tmp/music_files/';
const COVERS_DIR = '/tmp/covers/';
const SONGS_FILE = '/tmp/songs.json';
const ALLOWED_EXTENSIONS = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'];
const MAX_DOWNLOAD_SIZE = 100 * 1024 * 1024; // 100 MB

if (!file_exists(SONGS_FILE)) {
    // 创建一个空文件
    touch(SONGS_FILE);
}

// 确保目录存在
function ensureDirsExist() {
    $dirs = [UPLOAD_DIR, COVERS_DIR];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
ensureDirsExist();

// 加载歌曲列表
function loadSongs() {
    if (!file_exists(SONGS_FILE)) {
        return [];
    }
    $json = file_get_contents(SONGS_FILE);
    return json_decode($json, true) ?: [];
}

// 保存歌曲列表
function saveSongs($songs) {
    return file_put_contents(SONGS_FILE, json_encode($songs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// 格式化时间
function formatTime($seconds) {
    $seconds = (int) $seconds;
    if ($seconds < 0) return '0:00';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%d:%02d', $minutes, $secs);
    }
}

// 生成唯一ID
function generateUniqueId() {
    return uniqid('', true) . bin2hex(random_bytes(4));
}

// 发送JSON响应
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 处理请求
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'OPTIONS') {
    exit;
}

switch ($action) {
    case 'list':
        if ($method === 'GET') {
            $songs = loadSongs();
            $response = [];
            
            foreach ($songs as $song) {
                $songData = $song;
                
                if (isset($song['music_filename'])) {
                    $songData['url'] = './backend.php?action=serve_music&file=' . urlencode($song['music_filename']);
                    $songData['cover_url'] = isset($song['cover_filename']) ? './backend.php?action=serve_cover&file=' . urlencode($song['cover_filename']) : null;
                } elseif (isset($song['external_url'])) {
                    $songData['url'] = $song['external_url'];
                    if (isset($song['navidrome_id']) && isset($song['cover_url'])) {
                        $songData['cover_url'] = $song['cover_url'];
                    }
                }
                
                $response[] = $songData;
            }
            
            sendJsonResponse($response);
        }
        break;

    case 'upload':
        if ($method === 'POST') {
            if (empty($_FILES['music_file'])) {
                sendJsonResponse(['error' => 'No files uploaded'], 400);
            }
            
            $files = $_FILES['music_file'];
            if (!is_array($files['name'])) {
                $files = [
                    'name' => [$files['name']],
                    'type' => [$files['type']],
                    'tmp_name' => [$files['tmp_name']],
                    'error' => [$files['error']],
                    'size' => [$files['size']]
                ];
            }
            
            $uploadedSongs = [];
            $songs = loadSongs();
            $getID3 = new getID3;
            
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                $filename = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                    continue;
                }
                
                $uniqueId = generateUniqueId();
                $newFilename = $uniqueId . '.' . $ext;
                $uploadPath = UPLOAD_DIR . $newFilename;
                
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $fileInfo = $getID3->analyze($uploadPath);
                    
                    $title = $fileInfo['tags']['id3v2']['title'][0] ?? 
                            $fileInfo['tags']['id3v1']['title'][0] ?? 
                            pathinfo($filename, PATHINFO_FILENAME);
                    
                    $artist = $fileInfo['tags']['id3v2']['artist'][0] ?? 
                             $fileInfo['tags']['id3v1']['artist'][0] ?? 
                             '未知艺术家';
                    
                    $album = $fileInfo['tags']['id3v2']['album'][0] ?? 
                            $fileInfo['tags']['id3v1']['album'][0] ?? 
                            '';
                    
                    $duration = $fileInfo['playtime_seconds'] ?? 0;
                    
                    // 提取封面
                    $coverFilename = null;
                    if (isset($fileInfo['comments']['picture'][0]['data'])) {
                        $coverData = $fileInfo['comments']['picture'][0]['data'];
                        $coverMime = $fileInfo['comments']['picture'][0]['image_mime'];
                        $coverExt = 'jpg';
                        if (strpos($coverMime, 'png') !== false) $coverExt = 'png';
                        
                        $coverFilename = $uniqueId . '_cover.' . $coverExt;
                        file_put_contents(COVERS_DIR . $coverFilename, $coverData);
                    }
                    
                    $song = [
                        'id' => $uniqueId,
                        'music_filename' => $newFilename,
                        'original_filename' => $filename,
                        'cover_filename' => $coverFilename,
                        'title' => $title,
                        'artist' => $artist,
                        'album' => $album,
                        'duration' => formatTime($duration),
                        'duration_seconds' => $duration
                    ];
                    
                    $songs[] = $song;
                    $uploadedSongs[] = $song;
                }
            }
            
            if (!empty($uploadedSongs)) {
                saveSongs($songs);
                sendJsonResponse(['uploaded_songs' => $uploadedSongs]);
            } else {
                sendJsonResponse(['error' => 'No files were uploaded successfully'], 400);
            }
        }
        break;

    case 'serve_music':
        if ($method === 'GET' || $method === 'HEAD') {
            $filename = basename($_GET['file'] ?? '');
            $filepath = UPLOAD_DIR . $filename;
            
            if (!file_exists($filepath) || !is_file($filepath)) {
                sendJsonResponse(['error' => 'File not found'], 404);
            }
            
            $songs = loadSongs();
            $authorized = false;
            foreach ($songs as $song) {
                if (isset($song['music_filename']) && $song['music_filename'] === $filename) {
                    $authorized = true;
                    break;
                }
            }
            
            if (!$authorized) {
                sendJsonResponse(['error' => 'Unauthorized'], 403);
            }
            
            $filesize = filesize($filepath);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            $mimeTypes = [
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'flac' => 'audio/flac',
                'aac' => 'audio/aac',
                'm4a' => 'audio/mp4'
            ];
            
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
            
            header('Content-Type: ' . $mimeType);
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=3600');
            header('Content-Length: ' . $filesize);
            
            if ($method === 'HEAD') {
                exit;
            }
            
            $range = $_SERVER['HTTP_RANGE'] ?? null;
            
            if ($range && preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                $end = $matches[2] ? intval($matches[2]) : $filesize - 1;
                
                if ($start >= $filesize || $start > $end) {
                    http_response_code(416);
                    header("Content-Range: bytes */$filesize");
                    exit;
                }
                
                $length = $end - $start + 1;
                
                http_response_code(206);
                header("Content-Range: bytes $start-$end/$filesize");
                header("Content-Length: $length");
                
                $fp = fopen($filepath, 'rb');
                fseek($fp, $start);
                echo fread($fp, $length);
                fclose($fp);
            } else {
                readfile($filepath);
            }
            exit;
        }
        break;

    case 'serve_cover':
        if ($method === 'GET') {
            $filename = basename($_GET['file'] ?? '');
            $filepath = COVERS_DIR . $filename;
            
            if (!file_exists($filepath) || !is_file($filepath)) {
                sendJsonResponse(['error' => 'Cover not found'], 404);
            }
            
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ];
            
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
            
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: public, max-age=3600');
            readfile($filepath);
            exit;
        }
        break;

    case 'delete':
        if ($method === 'DELETE') {
            $songId = $_GET['id'] ?? '';
            if (empty($songId)) {
                sendJsonResponse(['error' => 'Missing song ID'], 400);
            }
            
            $songs = loadSongs();
            $found = false;
            $newSongs = [];
            
            foreach ($songs as $song) {
                if ($song['id'] === $songId) {
                    $found = true;
                    
                    // 删除本地文件
                    if (isset($song['music_filename'])) {
                        $musicPath = UPLOAD_DIR . $song['music_filename'];
                        if (file_exists($musicPath)) {
                            unlink($musicPath);
                        }
                    }
                    
                    if (isset($song['cover_filename'])) {
                        $coverPath = COVERS_DIR . $song['cover_filename'];
                        if (file_exists($coverPath)) {
                            unlink($coverPath);
                        }
                    }
                } else {
                    $newSongs[] = $song;
                }
            }
            
            if ($found) {
                saveSongs($newSongs);
                sendJsonResponse(['message' => 'Song deleted successfully']);
            } else {
                sendJsonResponse(['error' => 'Song not found'], 404);
            }
        }
        break;

    case 'add_url':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $url = $input['url'] ?? '';
            $title = $input['title'] ?? '';
            $artist = $input['artist'] ?? '';
            
            if (empty($url)) {
                sendJsonResponse(['error' => 'URL is required'], 400);
            }
            
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                sendJsonResponse(['error' => 'Invalid URL'], 400);
            }
            
            $songs = loadSongs();
            $uniqueId = generateUniqueId();
            
            $title = $title ?: basename(parse_url($url, PHP_URL_PATH) ?: '未知歌曲');
            $artist = $artist ?: '未知艺术家';
            
            $newSong = [
                'id' => $uniqueId,
                'external_url' => $url,
                'title' => $title,
                'artist' => $artist,
                'album' => '',
                'duration' => '0:00',
                'duration_seconds' => 0
            ];
            
            $songs[] = $newSong;
            
            if (saveSongs($songs)) {
                sendJsonResponse(['message' => 'URL added successfully', 'song' => $newSong]);
            } else {
                sendJsonResponse(['error' => 'Failed to save'], 500);
            }
        }
        break;

    case 'add_navidrome_url':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $navidromeId = $input['navidrome_id'] ?? '';
            $url = $input['url'] ?? '';
            $title = $input['title'] ?? '';
            $artist = $input['artist'] ?? '';
            $album = $input['album'] ?? '';
            $duration = intval($input['duration'] ?? 0);
            $coverUrl = $input['cover_url'] ?? null;
            
            if (empty($navidromeId) || empty($url)) {
                sendJsonResponse(['error' => 'Navidrome ID and URL are required'], 400);
            }
            
            $songs = loadSongs();
            
            // 检查是否已存在
            foreach ($songs as $song) {
                if (isset($song['navidrome_id']) && $song['navidrome_id'] === $navidromeId) {
                    sendJsonResponse(['error' => 'Song already exists'], 400);
                }
            }
            
            $uniqueId = generateUniqueId();
            
            $newSong = [
                'id' => $uniqueId,
                'navidrome_id' => $navidromeId,
                'external_url' => $url,
                'title' => $title,
                'artist' => $artist,
                'album' => $album,
                'duration' => formatTime($duration),
                'duration_seconds' => $duration,
                'cover_url' => $coverUrl
            ];
            
            $songs[] = $newSong;
            
            if (saveSongs($songs)) {
                sendJsonResponse(['message' => 'Navidrome song added successfully', 'song' => $newSong]);
            } else {
                sendJsonResponse(['error' => 'Failed to save'], 500);
            }
        }
        break;

    case 'update_song':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $songId = $input['id'] ?? '';
            $title = $input['title'] ?? null;
            $artist = $input['artist'] ?? null;
            $album = $input['album'] ?? null;
            
            if (empty($songId)) {
                sendJsonResponse(['error' => 'Missing song ID'], 400);
            }
            
            $songs = loadSongs();
            $updated = false;
            
            foreach ($songs as &$song) {
                if ($song['id'] === $songId) {
                    if ($title !== null) $song['title'] = $title;
                    if ($artist !== null) $song['artist'] = $artist;
                    if ($album !== null) $song['album'] = $album;
                    $updated = true;
                    break;
                }
            }
            
            if ($updated) {
                if (saveSongs($songs)) {
                    sendJsonResponse(['message' => 'Song updated successfully']);
                } else {
                    sendJsonResponse(['error' => 'Failed to save'], 500);
                }
            } else {
                sendJsonResponse(['error' => 'Song not found'], 404);
            }
        }
        break;

    case 'delete_all':
        if ($method === 'POST') {
            $songs = loadSongs();
            
            // 删除所有本地文件
            foreach ($songs as $song) {
                if (isset($song['music_filename'])) {
                    $musicPath = UPLOAD_DIR . $song['music_filename'];
                    if (file_exists($musicPath)) {
                        unlink($musicPath);
                    }
                }
                
                if (isset($song['cover_filename'])) {
                    $coverPath = COVERS_DIR . $song['cover_filename'];
                    if (file_exists($coverPath)) {
                        unlink($coverPath);
                    }
                }
            }
            
            if (saveSongs([])) {
                sendJsonResponse(['message' => 'All songs deleted successfully']);
            } else {
                sendJsonResponse(['error' => 'Failed to clear song list'], 500);
            }
        }
        break;

    default:
        sendJsonResponse(['error' => 'Invalid action'], 400);
}
?>