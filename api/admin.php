<?php
session_start();

$admin_password = getenv('ADMIN_PASSWORD');
// 简单的认证检查
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    // 这里使用简单的密码验证，实际应用中应该使用更安全的方式
    if ($password === $admin_password) { // 请修改为您自己的密码
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = '密码错误';
    }
}

// 处理登出
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// 如果未登录，显示登录表单
if (!$is_logged_in) {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台登录 - Vinyl Player</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h1 {
            font-size: 24px;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #718096;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 14px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .button-primary {
            width: 100%;
            padding: 12px 24px;
            background: #6c5ce7;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .button-primary:hover {
            background: #5b4cdb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-compact-disc"></i> Vinyl Player</h1>
            <p>管理后台登录</p>
        </div>

        <?php if (isset($login_error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="password">管理密码</label>
                <input type="password" id="password" name="password" class="form-input" required autofocus>
            </div>
            <button type="submit" class="button-primary">
                <i class="fas fa-sign-in-alt"></i> 登录
            </button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// 已登录，显示管理界面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - Vinyl Player</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
        }

        .header-actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-content h3 {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-content p {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 14px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .form-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }

        /* Buttons */
        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .button-primary {
            background: #6c5ce7;
            color: white;
        }

        .button-primary:hover {
            background: #5b4cdb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }

        .button-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .button-secondary:hover {
            background: #cbd5e0;
        }

        .button-danger {
            background: #f56565;
            color: white;
        }

        .button-danger:hover {
            background: #e53e3e;
        }

        /* Song List */
        .song-list {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .song-item {
            padding: 16px;
            border-bottom: 1px solid #f7fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .song-item:hover {
            background: #f7fafc;
        }

        .song-item:last-child {
            border-bottom: none;
        }

        .song-info {
            flex: 1;
        }

        .song-title {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .song-artist {
            font-size: 14px;
            color: #718096;
        }

        .song-album {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 2px;
        }

        .song-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .song-duration {
            font-size: 14px;
            color: #718096;
            margin-right: 12px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #718096;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: #edf2f7;
            color: #4a5568;
        }

        .btn-icon.btn-danger:hover {
            background: #fee;
            color: #e53e3e;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #718096;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f7fafc;
            color: #4a5568;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 80px;
            right: 24px;
            background: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 3000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            border-left: 4px solid #48bb78;
        }

        .notification.error {
            border-left: 4px solid #f56565;
        }

        .notification.info {
            border-left: 4px solid #4299e1;
        }

        /* Loading */
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f4f6;
            border-top-color: #6c5ce7;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 16px;
            }

            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1><i class="fas fa-compact-disc"></i> Vinyl Player 管理后台</h1>
            <div class="header-actions">
                <a href="./index.html" target="_blank" class="button button-secondary">
                    <i class="fas fa-external-link-alt"></i>
                    查看前台
                </a>
                <a href="?logout" class="button button-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    退出登录
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0e7ff; color: #6c5ce7;">
                    <i class="fas fa-music"></i>
                </div>
                <div class="stat-content">
                    <h3>歌曲总数</h3>
                    <p id="total-songs">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="stat-content">
                    <h3>本地文件</h3>
                    <p id="local-files">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-content">
                    <h3>外部链接</h3>
                    <p id="external-links">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                    <i class="fas fa-cloud"></i>
                </div>
                <div class="stat-content">
                    <h3>Navidrome</h3>
                    <p id="navidrome-songs">0</p>
                </div>
            </div>
        </div>

        <!-- Upload Local Files -->
        <div class="card">
            <h3 class="card-title">
                <i class="fas fa-upload"></i>
                上传本地音乐
            </h3>
            <div class="form-group">
                <input type="file" id="upload-files" accept="audio/*" multiple class="form-input">
                <p class="form-hint">支持批量上传 MP3、WAV、OGG、FLAC、AAC 等格式</p>
            </div>
            <button onclick="uploadFiles()" class="button button-primary">
                <i class="fas fa-cloud-upload-alt"></i>
                开始上传
            </button>
        </div>

        <!-- Add External URL -->
        <div class="card">
            <h3 class="card-title">
                <i class="fas fa-link"></i>
                添加外部链接
            </h3>
            <div class="form-group">
                <label class="form-label">音乐URL</label>
                <input type="url" id="external-url" placeholder="https://example.com/music.mp3" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">歌曲标题</label>
                <input type="text" id="external-title" placeholder="歌曲名称" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">艺术家</label>
                <input type="text" id="external-artist" placeholder="艺术家名称" class="form-input">
            </div>
            <button onclick="addExternalUrl()" class="button button-primary">
                <i class="fas fa-plus"></i>
                添加链接
            </button>
        </div>

        <!-- Navidrome Integration -->
        <div class="card">
            <h3 class="card-title">
                <i class="fas fa-cloud"></i>
                Navidrome 音乐库集成
            </h3>
            <div class="form-group">
                <label class="form-label">Navidrome 服务器设置</label>
                <input type="text" id="navidrome-url" placeholder="服务器地址 (如: https://rome.***.com)" class="form-input">
                <input type="text" id="navidrome-username" placeholder="用户名" class="form-input" style="margin-top: 8px;">
                <input type="password" id="navidrome-password" placeholder="密码" class="form-input" style="margin-top: 8px;">
            </div>
            <div class="form-group">
                <button onclick="testNavidromeConnection()" class="button button-secondary">
                    <i class="fas fa-plug"></i>
                    测试连接
                </button>
                <button onclick="syncNavidromeLibrary()" class="button button-primary">
                    <i class="fas fa-sync"></i>
                    同步音乐库
                </button>
            </div>
            <div id="navidrome-status" class="form-hint"></div>
        </div>

        <!-- Song List -->
        <div class="card">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                歌曲列表
            </h3>
            <div class="song-list" id="song-list">
                <div class="empty-state">
                    <i class="fas fa-music"></i>
                    <p>暂无歌曲</p>
                </div>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 12px;">
                <button onclick="deleteAllSongs()" class="button button-danger">
                    <i class="fas fa-trash-alt"></i>
                    清空所有歌曲
                </button>
                <button onclick="exportPlaylist()" class="button button-secondary">
                    <i class="fas fa-download"></i>
                    导出播放列表
                </button>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>编辑歌曲信息</h2>
                <button onclick="closeEditModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-song-id">
                <div class="form-group">
                    <label class="form-label">歌曲标题</label>
                    <input type="text" id="edit-title" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">艺术家</label>
                    <input type="text" id="edit-artist" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">专辑</label>
                    <input type="text" id="edit-album" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeEditModal()" class="button button-secondary">取消</button>
                <button onclick="saveEditSong()" class="button button-primary">保存</button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container"></div>

    <script>
        // Navidrome API Class
        class NavidromeAPI {
            constructor(baseUrl, username, password) {
                this.baseUrl = baseUrl.replace(/\/$/, '');
                this.username = username;
                this.password = password;
                this.token = null;
                this.salt = null;
                this.authenticated = false;
            }
            
            async authenticate() {
                try {
                    const response = await fetch(`${this.baseUrl}/auth/login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            username: this.username,
                            password: this.password
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`登录失败: HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    this.token = data.subsonicToken;
                    this.salt = data.subsonicSalt;
                    this.authenticated = true;
                    
                    console.log('Navidrome 认证成功');
                    return true;
                } catch (error) {
                    console.error('Navidrome 认证失败:', error);
                    throw error;
                }
            }
            
            async makeRequest(endpoint, params = {}) {
                if (!this.authenticated) {
                    await this.authenticate();
                }
                
                const url = new URL(`${this.baseUrl}/rest/${endpoint}`);
                url.searchParams.append('u', this.username);
                url.searchParams.append('t', this.token);
                url.searchParams.append('s', this.salt);
                url.searchParams.append('v', '1.16.1');
                url.searchParams.append('c', 'VinylPlayer');
                url.searchParams.append('f', 'json');
                
                Object.keys(params).forEach(key => {
                    url.searchParams.append(key, params[key]);
                });
                
                try {
                    const response = await fetch(url.toString());
                    const data = await response.json();
                    
                    if (data['subsonic-response'].status !== 'ok') {
                        throw new Error(data['subsonic-response'].error?.message || 'API请求失败');
                    }
                    
                    return data['subsonic-response'];
                } catch (error) {
                    console.error('Navidrome API 错误:', error);
                    throw error;
                }
            }
            
            async getRandomSongs(count = 500) {
                try {
                    const response = await this.makeRequest('getRandomSongs', { size: count });
                    return response.randomSongs?.song || [];
                } catch (error) {
                    console.error('获取随机歌曲失败:', error);
                    return [];
                }
            }
            
            getStreamUrl(songId) {
                if (!this.authenticated) {
                    throw new Error('需要先进行认证');
                }
                
                const url = new URL(`${this.baseUrl}/rest/stream`);
                url.searchParams.append('u', this.username);
                url.searchParams.append('t', this.token);
                url.searchParams.append('s', this.salt);
                url.searchParams.append('v', '1.16.1');
                url.searchParams.append('c', 'VinylPlayer');
                url.searchParams.append('id', songId);
                
                return url.toString();
            }
            
            getCoverArtUrl(coverArtId, size = 300) {
                if (!coverArtId || !this.authenticated) return null;
                
                const url = new URL(`${this.baseUrl}/rest/getCoverArt`);
                url.searchParams.append('u', this.username);
                url.searchParams.append('t', this.token);
                url.searchParams.append('s', this.salt);
                url.searchParams.append('v', '1.16.1');
                url.searchParams.append('c', 'VinylPlayer');
                url.searchParams.append('id', coverArtId);
                url.searchParams.append('size', size);
                
                return url.toString();
            }
        }

        // Global variables
        let navidromeApi = null;
        let songs = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            fetchSongs();
            loadNavidromeConfig();
        });

        // Fetch songs from backend
        async function fetchSongs() {
            try {
                const response = await fetch('backend.php?action=list');
                if (!response.ok) throw new Error('Failed to fetch songs');
                
                songs = await response.json();
                updateStatistics();
                renderSongList();
            } catch (error) {
                console.error('Error fetching songs:', error);
                showNotification('获取歌曲列表失败', 'error');
            }
        }

        // Update statistics
        function updateStatistics() {
            const totalSongs = songs.length;
            const localFiles = songs.filter(s => s.music_filename).length;
            const externalLinks = songs.filter(s => s.external_url && !s.navidrome_id).length;
            const navidromeSongs = songs.filter(s => s.navidrome_id).length;

            document.getElementById('total-songs').textContent = totalSongs;
            document.getElementById('local-files').textContent = localFiles;
            document.getElementById('external-links').textContent = externalLinks;
            document.getElementById('navidrome-songs').textContent = navidromeSongs;
        }

        // Render song list
        function renderSongList() {
            const songList = document.getElementById('song-list');
            
            if (songs.length === 0) {
                songList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-music"></i>
                        <p>暂无歌曲</p>
                    </div>
                `;
                return;
            }

            songList.innerHTML = songs.map(song => createSongElement(song)).join('');
        }

        // Create song element HTML
        function createSongElement(song) {
            const isLocal = song.music_filename ? true : false;
            const isNavidrome = song.navidrome_id ? true : false;
            
            return `
                <div class="song-item" data-song-id="${song.id}">
                    <div class="song-info">
                        <div class="song-title">${escapeHtml(song.title)}</div>
                        <div class="song-artist">${escapeHtml(song.artist)}</div>
                        ${song.album ? `<div class="song-album">${escapeHtml(song.album)}</div>` : ''}
                    </div>
                    <div class="song-actions">
                        ${isNavidrome ? '<i class="fas fa-cloud" style="color: #3b82f6;" title="Navidrome"></i>' : ''}
                        ${isLocal ? '<i class="fas fa-hdd" style="color: #f59e0b;" title="本地文件"></i>' : ''}
                        ${!isLocal && !isNavidrome ? '<i class="fas fa-link" style="color: #10b981;" title="外部链接"></i>' : ''}
                        <span class="song-duration">${song.duration || '0:00'}</span>
                        <button class="btn-icon" onclick="editSong('${song.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-danger" onclick="deleteSong('${song.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        // Upload files
        async function uploadFiles() {
            const input = document.getElementById('upload-files');
            const files = input.files;

            if (files.length === 0) {
                showNotification('请选择要上传的文件', 'error');
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('music_file[]', files[i]);
            }

            try {
                showNotification(`正在上传 ${files.length} 个文件...`, 'info');
                
                const response = await fetch('backend.php?action=upload', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                showNotification(`成功上传 ${result.uploaded_songs.length} 首歌曲`, 'success');
                input.value = '';
                fetchSongs();
            } catch (error) {
                console.error('Upload error:', error);
                showNotification('上传失败: ' + error.message, 'error');
            }
        }

        // Add external URL
        async function addExternalUrl() {
            const url = document.getElementById('external-url').value.trim();
            const title = document.getElementById('external-title').value.trim();
            const artist = document.getElementById('external-artist').value.trim();

            if (!url) {
                showNotification('请输入音乐URL', 'error');
                return;
            }

            try {
                const response = await fetch('backend.php?action=add_url', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ url, title, artist })
                });

                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                showNotification('外部链接添加成功', 'success');
                document.getElementById('external-url').value = '';
                document.getElementById('external-title').value = '';
                document.getElementById('external-artist').value = '';
                fetchSongs();
            } catch (error) {
                console.error('Add URL error:', error);
                showNotification('添加失败: ' + error.message, 'error');
            }
        }

        // Delete song
        async function deleteSong(songId) {
            if (!confirm('确定要删除这首歌曲吗？')) {
                return;
            }

            try {
                const response = await fetch(`backend.php?action=delete&id=${songId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                showNotification('歌曲已删除', 'success');
                fetchSongs();
            } catch (error) {
                console.error('Delete error:', error);
                showNotification('删除失败: ' + error.message, 'error');
            }
        }

        // Delete all songs
        async function deleteAllSongs() {
            if (!confirm('确定要清空所有歌曲吗？此操作不可恢复！')) {
                return;
            }

            try {
                const response = await fetch('backend.php?action=delete_all', {
                    method: 'POST'
                });

                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                showNotification('所有歌曲已清空', 'success');
                fetchSongs();
            } catch (error) {
                console.error('Delete all error:', error);
                showNotification('清空失败: ' + error.message, 'error');
            }
        }

        // Edit song
        async function editSong(songId) {
            const song = songs.find(s => s.id === songId);
            if (!song) return;

            document.getElementById('edit-song-id').value = song.id;
            document.getElementById('edit-title').value = song.title || '';
            document.getElementById('edit-artist').value = song.artist || '';
            document.getElementById('edit-album').value = song.album || '';

            document.getElementById('edit-modal').classList.add('active');
        }

        // Save edited song
        async function saveEditSong() {
            const songId = document.getElementById('edit-song-id').value;
            const title = document.getElementById('edit-title').value.trim();
            const artist = document.getElementById('edit-artist').value.trim();
            const album = document.getElementById('edit-album').value.trim();

            try {
                const response = await fetch('backend.php?action=update_song', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: songId, title, artist, album })
                });

                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                showNotification('歌曲信息已更新', 'success');
                closeEditModal();
                fetchSongs();
            } catch (error) {
                console.error('Update error:', error);
                showNotification('更新失败: ' + error.message, 'error');
            }
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('active');
        }

        // Export playlist
        function exportPlaylist() {
            const playlistData = {
                name: 'Vinyl Player Playlist',
                created: new Date().toISOString(),
                songs: songs
            };

            const blob = new Blob([JSON.stringify(playlistData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `vinyl-playlist-${new Date().getTime()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            showNotification('播放列表已导出', 'success');
        }

        // Navidrome functions
        function loadNavidromeConfig() {
            const savedConfig = localStorage.getItem('navidrome_config');
            if (savedConfig) {
                try {
                    const config = JSON.parse(savedConfig);
                    document.getElementById('navidrome-url').value = config.url || '';
                    document.getElementById('navidrome-username').value = config.username || '';
                    document.getElementById('navidrome-password').value = config.password || '';
                } catch (e) {
                    console.error('Failed to load Navidrome config:', e);
                }
            }
        }

        async function testNavidromeConnection() {
            const url = document.getElementById('navidrome-url').value.trim();
            const username = document.getElementById('navidrome-username').value.trim();
            const password = document.getElementById('navidrome-password').value.trim();
            const statusDiv = document.getElementById('navidrome-status');
            
            if (!url || !username || !password) {
                showNotification('请填写所有 Navidrome 连接信息', 'error');
                return;
            }
            
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 正在测试连接...';
            
            try {
                navidromeApi = new NavidromeAPI(url, username, password);
                await navidromeApi.authenticate();
                
                const songs = await navidromeApi.getRandomSongs(10);
                
                statusDiv.innerHTML = `<span style="color: #10b981;"><i class="fas fa-check-circle"></i> 连接成功！发现 ${songs.length} 首歌曲</span>`;
                showNotification('Navidrome 连接成功！', 'success');
                
                localStorage.setItem('navidrome_config', JSON.stringify({ url, username, password }));
                
            } catch (error) {
                statusDiv.innerHTML = `<span style="color: #f56565;"><i class="fas fa-times-circle"></i> 连接失败: ${error.message}</span>`;
                showNotification('Navidrome 连接失败: ' + error.message, 'error');
            }
        }

        async function syncNavidromeLibrary() {
            const statusDiv = document.getElementById('navidrome-status');
            
            if (!navidromeApi || !navidromeApi.authenticated) {
                showNotification('请先测试连接', 'error');
                return;
            }
            
            if (!confirm('这将从 Navidrome 导入音乐链接到播放列表。确定继续吗？')) {
                return;
            }
            
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 正在同步音乐库...';
            
            try {
                const songs = await navidromeApi.getRandomSongs(100);
                
                if (songs.length === 0) {
                    statusDiv.innerHTML = '<span style="color: #f59e0b;"><i class="fas fa-exclamation-triangle"></i> 未找到歌曲</span>';
                    return;
                }
                
                statusDiv.innerHTML = `<i class="fas fa-spinner fa-spin"></i> 正在导入 ${songs.length} 首歌曲...`;
                
                let successCount = 0;
                let failCount = 0;
                
                for (let i = 0; i < songs.length; i += 10) {
                    const batch = songs.slice(i, i + 10);
                    const promises = batch.map(async (song) => {
                        try {
                            const response = await fetch('backend.php?action=add_navidrome_url', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    navidrome_id: song.id,
                                    url: navidromeApi.getStreamUrl(song.id),
                                    title: song.title || '未知标题',
                                    artist: song.artist || '未知艺术家',
                                    album: song.album || '',
                                    duration: song.duration || 0,
                                    cover_url: song.coverArt ? navidromeApi.getCoverArtUrl(song.coverArt) : null
                                })
                            });
                            
                            const result = await response.json();
                            if (result.error) {
                                throw new Error(result.error);
                            }
                            
                            successCount++;
                        } catch (error) {
                            console.error(`导入歌曲失败: ${song.title}`, error);
                            failCount++;
                        }
                    });
                    
                    await Promise.all(promises);
                    
                    statusDiv.innerHTML = `<i class="fas fa-spinner fa-spin"></i> 正在导入... (${i + batch.length}/${songs.length})`;
                }
                
                statusDiv.innerHTML = `<span style="color: #10b981;"><i class="fas fa-check-circle"></i> 同步完成！成功: ${successCount}, 失败: ${failCount}</span>`;
                showNotification(`同步完成！成功导入 ${successCount} 首歌曲`, 'success');
                
                await fetchSongs();
                
            } catch (error) {
                statusDiv.innerHTML = `<span style="color: #f56565;"><i class="fas fa-times-circle"></i> 同步失败: ${error.message}</span>`;
                showNotification('同步失败: ' + error.message, 'error');
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                info: 'fas fa-info-circle'
            };
            
            notification.innerHTML = `
                <i class="${icons[type] || icons.info}"></i>
                <span>${message}</span>
            `;
            
            container.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Close modal on click outside
        document.getElementById('edit-modal').addEventListener('click', (e) => {
            if (e.target.id === 'edit-modal') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>