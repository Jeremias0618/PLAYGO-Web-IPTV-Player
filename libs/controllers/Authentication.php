<?php

function handleLoginRequest() {
    if (isset($_COOKIE['xuserm']) && isset($_COOKIE['xpwdm'])) {
        header("Location: home.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['op'] === 'login') {
        $username = trim($_POST['usuario']);
        $password = trim($_POST['senha']);

        if (validar_usuario($username, $password)) {
            $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $username);
            $storageDir = __DIR__ . '/../../storage/users/' . $safeUser;
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $userDataFile = $storageDir . '/user_data.json';
            
            $sessions = [];
            if (file_exists($userDataFile)) {
                $content = file_get_contents($userDataFile);
                $data = json_decode($content, true);
                
                if (is_array($data)) {
                    if (isset($data[0]) && is_array($data[0])) {
                        $sessions = $data;
                    } else if (isset($data['date'])) {
                        $sessions = [[
                            'id' => 1,
                            'user' => $data['user'] ?? $username,
                            'date' => $data['date']
                        ]];
                    }
                }
            }
            
            $nextId = count($sessions) + 1;
            $newSession = [
                'id' => $nextId,
                'user' => $username,
                'date' => date('Y-m-d H:i:s')
            ];
            
            $sessions[] = $newSession;
            file_put_contents($userDataFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            setcookie('xuserm', $username, time() + (7 * 24 * 60 * 60), "/");
            setcookie('xpwdm', $password, time() + (7 * 24 * 60 * 60), "/");
            header("Location: home.php");
            exit;
        } else {
            header("Location: login.php?sess=erro");
            exit;
        }
    }
}

