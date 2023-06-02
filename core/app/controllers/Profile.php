<?php
namespace App\Controllers;
use Zoomx\Controllers;

class Profile extends \Zoomx\Controllers\Controller
{
    /**
     * 
     */
    public function get()
    {
        $user = $this->modx->user;
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }
        
        $data = $user->getOne('Profile')->toArray();
        $ext = $data['extended'] ?? [];
        $data = array_merge($data, $ext);

        return jsonx(['success' => true, 'data' => $data]);
    }


    /**
     * 
     */
    public function update()
    {
        $user = $this->modx->user;
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }
        
        $profile = $user->getOne('Profile');

        $ext = json_decode($profile->extended, true) ?? [];

        foreach ($_POST as $key => $val) {
            if (in_array($key, ['fullname', 'phone', 'city', 'country'])) {
                $profile->set($key, trim($val));
            } else if (in_array($key, ['surname', 'middlename', 'birthdate', 'work_phone', 'tg', 'vk', 'resume_link', 'portfolio_link', 'description'])) {
                $ext[$key] = trim($val);
            }
        }

        $profile->set('extended', json_encode($ext));
        
        if ($_FILES && $_FILES["avatar"]["error"] === UPLOAD_ERR_OK) {
            $path = "assets/userfiles/{$this->modx->user->id}";
            if (!file_exists(MODX_BASE_PATH . $path)) {
                mkdir($path, 0777, true);
            }
            $time = time();
            $path_info = pathinfo($_FILES["avatar"]["name"]);
            $file = "{$path}/avatar-{$time}.{$path_info['extension']}";
            move_uploaded_file($_FILES["avatar"]["tmp_name"], MODX_BASE_PATH . $file);
            $profile->set('photo', $file);
        }

        $save = $profile->save();

        return jsonx(['success' => !!$save, 'data' => []]);
    }


    /**
     * 
     */
    public function upload_files()
    {
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }

        if (!in_array(($_POST['key'] ?? ''), ['avatar', 'portfolio', 'resume'])) {
            return jsonx(['success' => false, 'message' => 'wrong key']);
        }

        $files = [];
        $total_count = count($_FILES[$_POST['key']]['name']);

        $dir = "assets/userfiles/{$this->modx->user->id}/";
        $uploadPath = MODX_BASE_PATH . $dir;
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        for ($i=0 ; $i < $total_count ; $i++) {
            $tmpFilePath = $_FILES[$_POST['key']]['tmp_name'][$i];
            if ($tmpFilePath != "") {
                $hash = md5_file($_FILES[$_POST['key']]['tmp_name'][$i]);
                $pathinfo = pathinfo($_FILES[$_POST['key']]['name'][$i]);
                $filename = "{$pathinfo['filename']}-$hash.{$pathinfo['extension']}";
                $newFilePath = $uploadPath . $filename;

                if (!file_exists($newFilePath)) {
                    move_uploaded_file($tmpFilePath, $newFilePath);
                }

                $files[] = /*$this->modx->getOption('site_url') . */$dir . $filename;
            }
        }

        $user = $this->modx->user;
        $profile = $user->getOne('Profile');

        if ($_POST['key'] == 'avatar') {
            $profile->set('photo', $files[0] ?? '');
        } else {
            $ext = json_decode($profile->extended, true) ?? [];
            $ext[$_POST['key']] = $files;
            $profile->set('extended', json_encode($ext));
        }

        
        $save = $profile->save();
        return jsonx(['success' => !!$save, 'data' => $files]);
    }


    /**
     * 
     */
    public function change_password()
    {
        if (!$this->modx->user->isAuthenticated('web')) {
            return abortx(401);
        }

        $errors = [];

        $username = trim($_POST['username'] ?? '');
        $old_password = trim($_POST['old_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($username !== $this->modx->user->username) {
            $errors['username'] = 'Неверный логин';
        }

        if (mb_strlen($new_password) < 8) {
            $errors['new_password'] = 'Пароль должен содержать не менее 8 символов';
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }

        if (count($errors)) {
            return jsonx([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        $success = $this->modx->user->changePassword($new_password, $old_password);
        if (!$success) {
            $errors['old_password'] = 'Неверный старый пароль';
        }

        if (count($errors)) {
            return jsonx([
                'success' => false,
                'errors' => $errors,
            ]);
        }
        

        return jsonx(['success' => true, 'data' => []]);
    }
}
